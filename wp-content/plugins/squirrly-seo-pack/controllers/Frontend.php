<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Controllers_Frontend extends SQP_Classes_FrontController {

	public function __construct() {

		parent::__construct();

		if ( SQP_Classes_Helpers_Tools::getOption( 'sq_redirects' ) ) {

			//disable the redirect service from Squirrly SEO
			add_filter( 'sq_option_sq_auto_redirects', '__return_false', 99 );

			// Cache support
			add_action( 'sq_redirect_matched_item', array(
				SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_UrlCache' ),
				'cacheRedirect'
			), 10, 2 );

			add_action( 'sq_redirect_after_redirects', array(
				SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_UrlCache' ),
				'cacheFailRedirect'
			), 10, 2 );

			// Count the redirect visits
			add_action( 'sq_redirect_visit', array( $this, 'countLastVisit' ), 10, 3 );
			// Prevent infinite loop
			add_action( 'sq_redirect_visit', array( $this, 'loopHandle' ), 10, 3 );

			// Check the template redirect and log
			// redirect only when is 404 and no other plugin redirects the URL
			add_action( 'template_redirect', array( $this, 'logPageNotFound' ), PHP_INT_MAX );
			add_action( 'template_redirect', array( $this, 'checkPageNotFoundRedirect' ), PHP_INT_MAX );
		}
	}

	/**
	 * Check if there is set a Page Not Found redirect set
	 *
	 * @return void
	 */
	public function checkPageNotFoundRedirect() {

		$patterns = SQP_Classes_Helpers_Tools::getOption( 'patterns' );

		//If 404 page
		if ( is_404() && isset( $_SERVER['REQUEST_URI'] ) ) {

			if ( isset( $patterns[404]['do_redirects'] ) && $patterns[404]['do_redirects'] && SQP_Classes_Helpers_Tools::getOption( '404_url_redirect' ) ) {

				header( 'X-Redirect-By: Squirrly SEO' );

				//check the default redirect URL and prevent loop redirects
				if ( parse_url( SQP_Classes_Helpers_Tools::getOption( '404_url_redirect' ), PHP_URL_PATH ) <> $_SERVER['REQUEST_URI'] ) {
					header( 'Location: ' . SQP_Classes_Helpers_Tools::getOption( '404_url_redirect' ), true, 301 );
				} else {
					header( 'Location: ' . home_url(), true, 301 );
				}
				exit();
			}
		}
	}

	/**
	 * Hook the frontend init
	 *
	 * @return void
	 */
	public function hookFrontinit() {

		$redirect_tables = SQP_Classes_Helpers_Tools::getOption( 'sq_redirects_tables' );
		$redirect        = SQP_Classes_Helpers_Tools::getOption( 'sq_redirects' );

		//If redirects are active
		if ( $redirect_tables && $redirect ) {

			try {

				//check the current URL and redirects
				$this->checkRedirects();

			} catch ( Exception $e ) {
			}

		}

	}

	/**
	 * Check the redirect
	 *
	 * @return void
	 */
	public function checkRedirects() {

		/**
		 * @var SQP_Models_Redirects_Request $url
		 */
		$url = SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_Request' )->getRequestUrl();

		/**
		 * @var SQP_Models_Redirects_UrlHandle $url_handle
		 */
		$url_handle = SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_UrlHandle' );
		$url_handle->setUrl( $url );

		// Make sure we don't try and redirect something essential
		if ( $url_handle->isValid() && ! $url_handle->isProtectedUrl() ) {
			do_action( 'sq_redirect_before_redirects', $url_handle->getDecodedUrl(), $this );

			/**
			 * Get all redirects that match the URL
			 *
			 * @var SQP_Models_Domain_Redirect $redirect
			 */
			$redirect      = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Redirect' );
			$redirect->url = $url_handle->getDecodedUrl();

			/**
			 * @var SQP_Models_Redirects_Database $database
			 */
			$database  = SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_Database' );
			$redirects = (array) $database->getMatchedUrls( $redirect->url );

			// Redirects will be ordered by position. Run through the list until one fires
			foreach ( $redirects as $item ) {

				$action = $item->checkMatch( $url_handle->getDecodedUrl(), $url_handle->getOriginalUrl() );

				if ( $action ) {

					do_action( 'sq_redirect_matched_item', $url_handle->getDecodedUrl(), $item );

					if ( $item->isSlugRedirect() ) {
						add_action( 'template_redirect', function() use ( $action ) {
							if ( function_exists( 'is_404' ) && is_404() ) {
								$action->run();
							}
						}, 1 );
					} else {
						$action->run();

					}
					break;
				}
			}

			do_action( 'sq_redirect_after_redirects', $url_handle->getDecodedUrl(), $redirects );

		}
	}

	/**
	 * Count the redirect visit
	 *
	 * @param $redirect
	 * @param $url
	 * @param $target
	 *
	 * @return void
	 */
	public function countLastVisit( $redirect, $url, $target ) {
		$redirect->visit( $url, $target );
	}


	/**
	 * Handle the infinite loop
	 *
	 * @param $redirect
	 * @param $url
	 * @param $target
	 *
	 * @return void
	 */
	public function loopHandle( $redirect, $url, $target ) {

		//Prevent infinite loop and redirect to home page
		if ( $url === $target ) {
			$redirect->action->setTarget( '/' );
		}
	}

	/**
	 * Check if there is a 404 error
	 *
	 * @return bool
	 */
	public function logPageNotFound() {

		$log_tables = SQP_Classes_Helpers_Tools::getOption( 'sq_redirects_log_tables' );
		$log_404    = SQP_Classes_Helpers_Tools::getOption( 'sq_redirects_log_404' );
		$log_header = SQP_Classes_Helpers_Tools::getOption( 'sq_redirects_log_header' );

		if ( $log_tables && $log_404 && is_404() ) {

			$file_ext = array(
				'jpg',
				'jpeg',
				'png',
				'bmp',
				'gif',
				'jp2',
				'webp',
				'css',
				'scss',
				'js',
				'woff',
				'woff2',
				'map',
				'ttf',
				'otf',
				'pfb',
				'pfm',
				'tfil',
				'eot',
				'svg',
				'pdf',
				'doc',
				'docx',
				'csv',
				'xls',
				'xslx',
				'mp2',
				'mp3',
				'mp4',
				'mpeg',
				'zip',
				'rar',
				'map',
				'txt'
			);

			/**
			 * @var SQP_Models_Redirects_Request $request
			 */
			$request = SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_Request' );

			$ext = substr( $request->getRequestUrl(), strrpos( $request->getRequestUrl(), '.' ) + 1 );
			if ( in_array( $ext, $file_ext ) ) {
				return false;
			}

			//
			$data = [
				'url'            => $request->getRequestUrl(),
				'sent_to'        => '',
				'domain'         => $request->getServer(),
				'agent'          => $request->getUserAgent(),
				'referrer'       => $request->getReferrer(),
				'http_code'      => 404,
				'request_method' => $request->getRequestMethod(),
				'redirect_by'    => 'wordpress',
				'redirection_id' => false,
				'ip'             => $request->getIp(),
			];

			if ( $log_header ) {
				$data['request_data'] = [
					'headers' => $request->getRequestHeaders(),
				];
			}

			/**
			 * @var SQP_Models_Redirects_Log $log
			 */
			$log  = SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_Log' );
			$item = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Log', $data );
			$log->addLogRow( $item );

			return true;

		}

		return false;

	}
}
