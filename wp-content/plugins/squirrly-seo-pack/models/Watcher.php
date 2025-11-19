<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Watcher {
	public function __construct() {

		//add listener on Squirrly SEO save
		if ( SQP_Classes_Helpers_Tools::getOption( 'sq_redirects' ) ) {

			//hook the 301 redirect from Squirrly
			//hook the slug change in posts/pages if activated
			add_filter( 'sq_url_before_save', array( $this, 'listenSquirrlyRedirect' ), 11, 2 );
			add_action( 'post_updated', array( $this, 'listenSlugChanged' ), 99, 3 );
			add_action( 'attachment_updated', array( $this, 'listenSlugChanged' ), 99, 3 );

			if ( SQP_Classes_Helpers_Tools::getOption( 'sq_redirects_log_hits' ) || SQP_Classes_Helpers_Tools::getOption( 'sq_redirects_log_404' ) ) {
				//delete old log
				add_action( 'sq_redirects_log_clear', array( $this, 'deleteRedirectLog' ) );
			}

		}
	}

	/**
	 * Listen any 301 redirect from Squirrly SEO
	 *
	 * @param $url
	 * @param $sq_hash
	 *
	 * @return mixed
	 */
	public function listenSquirrlyRedirect( $url, $sq_hash ) {

		if ( class_exists( 'SQ_Classes_ObjController' ) ) {

			$redirect = SQP_Classes_Helpers_Tools::getValue( 'sq_redirect', '' );

			/** @var SQP_Controllers_Redirects $redirects */
			$redirects = SQP_Classes_ObjController::getClass( 'SQP_Controllers_Redirects' );
			$group_id  = $redirects->getGroupIds( 'post' ); //post redirect

			$post = SQ_Classes_ObjController::getClass( 'SQ_Models_Qss' )->getSqPost( $sq_hash );

			//sanitize the redirect and prevent current URLs
			if ( strpos( $redirect, '//' ) === false || $redirect === $url ) {
				$redirect = '';
			}

			//check if there is a redirect in SEO Snippet
			if ( $redirect <> '' ) {

				$data['flag_post']      = $post->ID;
				$data['flag_post_type'] = $post->post_type;
				$data['flag_term']      = $post->term_id;
				$data['flag_taxonomy']  = $post->taxonomy;
				$data['group_id']       = $group_id; //post redirect

				//add the post if exists
				$data['url']         = rtrim( $url, '/' );
				$data['action_data'] = $redirect;

				/** @var SQP_Models_Redirects_Admin $database */
				$database = SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_Admin' );

				/** @var SQP_Models_Domain_Redirect $item */
				$item = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Redirect', $database->sanitizeData( $data ) );

				//Create Redirects table if not exists
				$database->checkTablesExist();

				//add the redirect in database
				$database->addRedirectRow( $item );

			} else {

				//remove the redirect is removed from Squirrly SEO

				/** @var SQP_Models_Redirects_Admin $database */
				$database = SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_Admin' );

				/** @var SQP_Models_Domain_Redirect $item */
				$args = array(
					'flag_post'      => $post->ID,
					'flag_post_type' => $post->post_type,
					'flag_term'      => $post->term_id,
					'flag_taxonomy'  => $post->taxonomy,
					'group_id'       => $group_id, //post redirect
				);

				//Create Redirects table if not exists
				$database->checkTablesExist();

				//if there is a post redirect through snippet
				if ( $item = $database->getRedirectByPost( $args ) ) {
					if ( $item->id > 0 ) {
						$database->deleteRedirectRow( $item->id );
					}
				}

			}

		}

		return $url;
	}

	/**
	 * Check the URL change
	 *
	 * @param int $post_id
	 * @param WP_Post $post The Post Object
	 * @param WP_Post $post_before The Previous Post Object
	 *
	 * @return void
	 */
	public function listenSlugChanged( $post_id, $post, $post_before ) {

		// Don't bother if it hasn't changed.
		if ( $post->post_name == $post_before->post_name ) {
			return;
		}

		// We're only concerned with published, non-hierarchical objects.
		if ( ! ( 'publish' === $post->post_status || ( 'attachment' === get_post_type( $post ) && 'inherit' === $post->post_status ) ) ) {
			return;
		}

		// If we haven't added this old slug before, add it now.
		if ( ! empty( $post_before->post_name ) ) {

			/** @var SQP_Controllers_Redirects $redirects */
			$redirects = SQP_Classes_ObjController::getClass( 'SQP_Controllers_Redirects' );
			$group_id  = $redirects->getGroupIds( 'slug' ); //slug change redirect

			//set the post data
			$data['flag_post']      = $post->ID;
			$data['flag_post_type'] = $post->post_type;
			$data['flag_term']      = 0;
			$data['flag_taxonomy']  = '';
			$data['group_id']       = $group_id;

			$data['url']         = rtrim( $post_before->post_name, '/' );
			$data['action_data'] = wp_parse_url( get_permalink( $post->ID ), PHP_URL_PATH );

			/** @var SQP_Models_Redirects_Admin $database */
			$database = SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_Admin' );

			/** @var SQP_Models_Domain_Redirect $item */
			$item = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Redirect', $database->sanitizeData( $data ) );

			//Create Redirects table if not exists
			$database->checkTablesExist();

			//add the redirect in database
			$database->addRedirectRow( $item );
		}


	}

	public function listenPostTrashed( $post_id ) {

		/** @var SQP_Controllers_Redirects $redirects */
		$redirects = SQP_Classes_ObjController::getClass( 'SQP_Controllers_Redirects' );
		$group_id  = $redirects->getGroupIds( 'post' ); //post redirect

		$data = array(
			'flag_post' => $post_id,
			'group_id'  => $group_id,

			'url'         => wp_parse_url( get_permalink( $post_id ), PHP_URL_PATH ),
			'action_data' => array( 'url' => '/' ),
		);

		// Create a new redirect for this post, but only if not draft
		if ( $data['url'] !== '/' ) {

			/** @var SQP_Models_Redirects_Admin $database */
			$database = SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_Admin' );

			/** @var SQP_Models_Domain_Redirect $item */
			$item = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Redirect', $database->sanitizeData( $data ) );

			//Create Redirects table if not exists
			$database->checkTablesExist();

			//add the redirect in database
			$database->addRedirectRow( $item );
		}
	}

	/**
	 * Listen daily for log removal
	 *
	 * @return void
	 */
	public function deleteRedirectLog() {

		//Delete the old log
		if ( SQP_Classes_Helpers_Tools::getOption( 'sq_redirects_log_hits' ) || SQP_Classes_Helpers_Tools::getOption( 'sq_redirects_log_404' ) ) {
			//get the log days and delete the log from database
			$log_days = SQP_Classes_Helpers_Tools::getOption( 'sq_redirects_log_days' );
			SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_Log' )->deleteOldLog( $log_days );
		}

	}


}
