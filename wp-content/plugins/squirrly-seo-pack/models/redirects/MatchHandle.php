<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Redirects_MatchHandle {
	/**
	 * URL
	 *
	 * @var String
	 */
	public $url = '';

	/**
	 * Initiate the match class
	 *
	 * @param $url
	 *
	 * @return void
	 */
	public function init( $url ) {
		$this->url = $url;
	}

	/**
	 * Get the action data
	 *
	 * @param array $details
	 * @param $no_target_url
	 *
	 * @return String|null
	 */
	public function process( array $details, $no_target_url = false ) {

		if ( ! isset( $details['action_data'] ) ) {
			return null;
		}

		//check if URL
		if ( is_string( $details['action_data'] ) ) {

			//set the URL redirect
			$url = $details['action_data'];

			//for no url, add a slash
			if ( strlen( $url ) === 0 ) {
				$url = '/';
			}

			//Remove the current domain from URL and prevent errors when the domain name is changed
			if ( stripos( $url, home_url() ) !== false ) {
				$url = str_replace( home_url(), '', $url );
			}

			//if there is no slash, add one before the url
			if ( stripos( $url, '/' ) === false ) {
				$url = '/' . $url;
			}

			//if no target url ... 404 case
			if ( $no_target_url ) {
				return null;
			}

			//return sanitized url
			return $this->sanitizeUrl( $url );

		}

		return null;

	}

	public function getTargetUrl( $original_url, $matched_url, SQP_Models_Redirects_Flags $flag ) {
		$target = $this->url;

		if ( $flag->isRegex() ) {
			$target = $this->getTargetRegexUrl( $matched_url, $target, $original_url, $flag );
		}

		return $target;
	}

	public function getData() {
		if ( $this->url ) {
			return [
				'url' => $this->url,
			];
		}

		return null;
	}

	/**
	 * Sanitize a match URL
	 *
	 * @param String $url URL.
	 *
	 * @return String
	 */
	private function sanitizeUrl( $url ) {
		// No new lines
		$url = preg_replace( "/[\r\n\t].*?$/s", '', $url );

		// Clean control codes
		$url = preg_replace( '/[^\PC\s]/u', '', $url );

		return $url;
	}

	/**
	 * Apply a regular expression to the target URL, replacing any values.
	 *
	 * @param string $source_url Redirect source URL.
	 * @param string $target_url Target URL.
	 * @param string $requested_url The URL being requested (decoded).
	 * @param SQP_Models_Redirects_Flags $flags Source URL flags.
	 *
	 * @return string
	 */
	protected function getTargetRegexUrl( $source_url, $target_url, $requested_url, SQP_Models_Redirects_Flags $flags ) {
		$regex = SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_Regex' );
		$regex->init( $source_url, $flags->isIgnoreCase() );

		return $regex->replace( $target_url, $requested_url );
	}


}
