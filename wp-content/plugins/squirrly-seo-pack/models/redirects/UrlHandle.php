<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Redirects_UrlHandle {

	/**
	 * Original URL
	 *
	 * @var String
	 */
	private $url;

	/**
	 * Decoded URL
	 *
	 * @var String
	 */
	private $decoded_url;

	public function setUrl( $url ) {
		$this->url         = apply_filters( 'sq_url_source', $url );
		$this->decoded_url = rawurldecode( $this->url );

		// Replace the decoded query params with the original ones
		$this->url = $this->replaceQueryParams( $this->url, $this->decoded_url );
	}

	/**
	 * Take the decoded path part, but keep the original query params. This ensures any redirects keep the encoding.
	 *
	 * @param string $url Original decoded URL.
	 * @param string $decoded_url Decoded URL.
	 *
	 * @return string
	 */
	private function replaceQueryParams( $url, $decoded_url ) {
		$decoded = explode( '?', $decoded_url );

		if ( count( $decoded ) > 1 ) {
			$original = explode( '?', $url );

			if ( count( $original ) > 1 ) {
				return $decoded[0] . '?' . $original[1];
			}
		}

		return $decoded_url;
	}

	/**
	 * Get the original URL
	 *
	 * @return String
	 */
	public function getOriginalUrl() {
		return $this->url;
	}

	/**
	 * Get the decoded URL
	 *
	 * @return String
	 */
	public function getDecodedUrl() {
		return $this->decoded_url;
	}

	/**
	 * Is this a valid URL?
	 *
	 * @return boolean
	 */
	public function isValid() {
		return strlen( $this->getDecodedUrl() ) > 0;
	}

	/**
	 * Protect certain URLs from being redirected. Note we don't need to protect wp-admin, as this code doesn't run there
	 *
	 * @return boolean
	 */
	public function isProtectedUrl() {
		$rest     = wp_parse_url( SQP_Classes_Helpers_Tools::getRestApi() );
		$rest_api = $rest['path'] . ( isset( $rest['query'] ) ? '?' . $rest['query'] : '' );

		if ( substr( $this->getDecodedUrl(), 0, strlen( $rest_api ) ) === $rest_api ) {
			// Never redirect the REST API
			return true;
		}

		return false;
	}

	/**
	 * Get the plain 'matched' URL:
	 *
	 * - Lowercase
	 * - No trailing slashes
	 *
	 * @return string URL
	 */
	public function getPlainUrl() {
		// Remove query params, and decode any encoded characters
		$path = $this->getPath( $this->url );
		$path = $this->removeTrailingSlash( $path );

		// URL encode
		$decode = [
			'/',
			':',
			'[',
			']',
			'@',
			'~',
			',',
			'(',
			')',
			';',
		];

		// URL encode everything - this converts any i10n to the proper encoding
		$path = rawurlencode( $path );

		// We also converted things we don't want encoding, such as a /. Change these back
		foreach ( $decode as $char ) {
			$path = str_replace( rawurlencode( $char ), $char, $path );
		}

		// Lowercase everything
		$path = $this->toLower( $path );

		return $path ? $path : '/';
	}

	/**
	 * `parse_url` doesn't handle 'incorrect' URLs, such as those with double slashes
	 * These are often used in redirects, so we fall back to our own parsing
	 *
	 * @param String $url URL.
	 *
	 * @return String
	 */
	public function getPath( $url ) {
		$path = $url;

		if ( preg_match( '@^https?://@', $url, $matches ) > 0 ) {
			$parts = explode( '://', $url );

			if ( count( $parts ) > 1 ) {
				$rest = explode( '/', $parts[1] );
				$path = '/' . implode( '/', array_slice( $rest, 1 ) );
			}
		}

		return urldecode( $this->getQuery( $path ) );
	}

	/**
	 * Get the path component up to the query string
	 *
	 * @param String $url URL.
	 *
	 * @return String
	 */
	private function getQuery( $url ) {
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

	public function removeTrailingSlash( $path ) {
		// Return / or // as-is
		if ( $path === '/' ) {
			return $path;
		}

		// Anything else remove the last /
		return preg_replace( '@/$@', '', $path );
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
