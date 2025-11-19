<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Redirects_ActionHandle {

	/**
	 * The action code (i.e. HTTP code)
	 *
	 * @var integer
	 */
	protected $code = 0;

	/**
	 * The action type
	 *
	 * @var string
	 */
	protected $type = '';

	/**
	 * Target URL, if any
	 *
	 * @var String|null
	 */
	protected $target = null;

	/**
	 * Initiate the action class
	 *
	 * @param string $type .
	 * @param int $code .
	 */
	public function init( $type, $code ) {
		$this->type = $type;
		$this->code = $code;
	}

	/**
	 * Redirect to a URL
	 *
	 * @param string $target Target URL.
	 *
	 * @return void
	 */
	protected function redirectTo( $target ) {

		// This is a known redirect, possibly extenal
		// phpcs:ignore
		$redirect = wp_redirect( $target, $this->getCode(), 'Squirrly' );

		if ( $redirect ) {
			die();
		}
	}

	/**
	 * Run this action. May not return from this function.
	 *
	 * @return void
	 */
	public function run() {

		switch ( $this->type ) {
			case 'url':
				$target = $this->getTarget();

				if ( $target !== null ) {
					$this->redirectTo( $target );
				}

				break;
			case 'error':

				wp_reset_query();

				// Set the query to be a 404
				set_query_var( 'is_404', true );

				// Return the 404 page
				add_filter( 'template_include', function() {
					return get_404_template();
				} );

				// Clear any posts if this is actually a valid URL
				add_filter( 'pre_handle_404', function() {
					global $wp_query;

					// Page comments plugin interferes with this
					$wp_query->posts = array();

					return false;
				} );

				// Ensure the appropriate http code is returned
				add_action( 'wp', function() {
					status_header( $this->code );
					nocache_headers();

					global $wp_version;

					if ( version_compare( $wp_version, '5.1', '<' ) ) {
						header( 'X-Redirect-Agent: Squirrly' );
					} else {
						header( 'X-Redirect-By: Squirrly' );
					}
				} );

				break;
		}
	}

	/**
	 * Does this action need a target?
	 *
	 * @return boolean
	 */
	public function needsTarget() {
		switch ( $this->type ) {
			case 'url':
				return true;
			case 'error':
				return false;
		}

		return true;
	}

	/**
	 * Get the action code
	 *
	 * @return integer
	 */
	public function getCode() {
		return $this->code;
	}

	/**
	 * Get action type
	 *
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Set the target for this action
	 *
	 * @param String $target_url The original URL from the client.
	 *
	 * @return void
	 */
	public function setTarget( $target_url ) {
		$this->target = $target_url;
	}

	/**
	 * Get the target for this action
	 *
	 * @return String|null
	 */
	public function getTarget() {
		return $this->target;
	}


}
