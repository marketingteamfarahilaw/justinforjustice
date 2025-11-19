<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Redirects_SlugHandle {

	/**
	 * URL slug
	 *
	 * @var String
	 */
	private $slug;

	/**
	 * Init
	 *
	 * @param String $path URL.
	 */
	public function init( $path ) {
		$this->slug = $this->getSlugComponent( $path );
	}

	/**
	 * Is the supplied `url` a match for this object?
	 *
	 * @param String $url URL to match against.
	 * @param SQP_Models_Redirects_Flags $flags Source flags to use in match.
	 *
	 * @return boolean
	 */
	public function isMatch( $url, SQP_Models_Redirects_Flags $flags ) {
		$target = SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_PathHandle' );
		$target->init( $url );

		$target_slug = '/' . basename( $target->get() );
		$source_slug = $this->get();

		if ( $flags->isIgnoreTrailing() ) {
			// Ignore trailing slashes
			$source_slug = $this->getWithoutTrailingSlash();
			$target_slug = $target->getWithoutTrailingSlash();
		}

		if ( $flags->isIgnoreCase() ) {
			// Case-insensitive match
			$source_slug = $this->toLower( $source_slug );
			$target_slug = $this->toLower( $target_slug );
		}

		//get the slug component of this url
		$target_slug = $this->getSlugComponent( $target_slug );

		return $target_slug === $source_slug;
	}


	/**
	 * Get the path value
	 *
	 * @return String
	 */
	public function get() {
		return $this->slug;
	}

	/**
	 * Get the path value without trailing slash, or `/` if home
	 *
	 * @return String
	 */
	public function getWithoutTrailingSlash() {
		// Return / or // as-is
		if ( $this->slug === '/' ) {
			return $this->slug;
		}

		// Anything else remove the last /
		return preg_replace( '@/$@', '', $this->get() );
	}

	/**
	 * `parse_url` doesn't handle 'incorrect' URLs, such as those with double slashes
	 * These are often used in redirects, so we fall back to our own parsing
	 *
	 * @param String $url URL.
	 *
	 * @return String
	 */
	private function getSlugComponent( $url ) {
		$slug = basename( $url );

		return urldecode( '/' . $slug );
	}

	/**
	 * Convert a URL to lowercase
	 *
	 * @param String $url URL.
	 *
	 * @return String
	 */
	public function toLower( $url ) {
		if ( function_exists( 'mb_strtolower' ) ) {
			return mb_strtolower( $url, 'UTF-8' );
		}

		return strtolower( $url );
	}

}
