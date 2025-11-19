<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Redirects_PathHandle {

	/**
	 * URL path
	 *
	 * @var String
	 */
	private $path;

	/**
	 * Init
	 *
	 * @param String $path URL.
	 */
	public function init( $path ) {
		$this->path = $this->getPathComponent( $path );
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
		$target = new SQP_Models_Redirects_PathHandle();
		$target->init( $url );

		$target_path = $target->get();
		$source_path = $this->get();

		if ( $flags->isIgnoreTrailing() ) {
			// Ignore trailing slashes
			$source_path = $this->getWithoutTrailingSlash();
			$target_path = $target->getWithoutTrailingSlash();
		}

		if ( $flags->isIgnoreCase() ) {
			// Case-insensitive match
			$source_path = $this->toLower( $source_path );
			$target_path = $this->toLower( $target_path );
		}

		return $target_path === $source_path;
	}


	/**
	 * Get the path value
	 *
	 * @return String
	 */
	public function get() {
		return $this->path;
	}

	/**
	 * Get the path value without trailing slash, or `/` if home
	 *
	 * @return String
	 */
	public function getWithoutTrailingSlash() {
		// Return / or // as-is
		if ( $this->path === '/' ) {
			return $this->path;
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
	private function getPathComponent( $url ) {
		$path = $url;

		if ( preg_match( '@^https?://@', $url, $matches ) > 0 ) {
			$parts = explode( '://', $url );

			if ( count( $parts ) > 1 ) {
				$rest = explode( '/', $parts[1] );
				$path = '/' . implode( '/', array_slice( $rest, 1 ) );
			}
		}

		return urldecode( $this->getQueryBefore( $path ) );
	}

	/**
	 * Get the path component up to the query string
	 *
	 * @param String $url URL.
	 *
	 * @return String
	 */
	private function getQueryBefore( $url ) {
		$qpos  = strpos( $url, '?' );
		$qrpos = strpos( $url, '\\?' );

		// Have we found an escaped query and it occurs before a normal query?
		if ( $qrpos !== false && $qrpos < $qpos ) {
			// Yes, the path is everything up to the escaped query
			return substr( $url, 0, $qrpos );
		}

		// No query - return everything as path
		if ( $qpos === false ) {
			return $url;
		}

		// Query found - return everything up to it
		return substr( $url, 0, $qpos );
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
