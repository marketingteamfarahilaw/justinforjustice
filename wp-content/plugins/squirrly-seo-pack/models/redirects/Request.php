<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Redirects_Request {

	/**
	 * URL friendly sanitize_text_fields which lets encoded characters through and doesn't trim
	 *
	 * @param string $value Value.
	 *
	 * @return string
	 */
	public function sanitizeUrl( $value ) {
		// Remove invalid UTF
		$url = wp_check_invalid_utf8( $value, true );

		// No new lines
		$url = preg_replace( "/[\r\n\t].*?$/s", '', $url );

		// Clean control codes
		$url = preg_replace( '/[^\PC\s]/u', '', $url );

		return $url;
	}

	/**
	 * Get HTTP headers
	 *
	 * @return array
	 */
	public function getRequestHeaders() {
		$ignore  = apply_filters( 'sq_request_headers_ignore', [
			'cookie',
			'host',
		] );
		$headers = array();

		foreach ( $_SERVER as $name => $value ) {
			$value = sanitize_text_field( $value );
			$name  = sanitize_text_field( $name );

			if ( substr( $name, 0, 5 ) === 'HTTP_' ) {
				$name = strtolower( substr( $name, 5 ) );
				$name = str_replace( '_', ' ', $name );
				$name = ucwords( $name );
				$name = str_replace( ' ', '-', $name );

				if ( ! in_array( strtolower( $name ), $ignore, true ) ) {
					$headers[ $name ] = $value;
				}
			}
		}

		return apply_filters( 'sq_request_headers', $headers );
	}

	/**
	 * Get request method
	 *
	 * @return string
	 */
	public function getRequestMethod() {
		$method = '';

		if ( isset( $_SERVER['REQUEST_METHOD'] ) && is_string( $_SERVER['REQUEST_METHOD'] ) ) {
			$method = sanitize_text_field( $_SERVER['REQUEST_METHOD'] );
		}

		return apply_filters( 'sq_request_method', $method );
	}

	/**
	 * Get the server name (from SERVER_NAME), or use the request name (HTTP_HOST) if not present
	 *
	 * @return string
	 */
	public function getServerName() {
		$host = $this->getRequestServerName();

		if ( isset( $_SERVER['SERVER_NAME'] ) && is_string( $_SERVER['SERVER_NAME'] ) ) {
			$host = sanitize_text_field( $_SERVER['SERVER_NAME'] );
		}

		return apply_filters( 'sq_request_server', $host );
	}

	/**
	 * Get the request server name (HTTP_HOST)
	 *
	 * @return string
	 */
	public function getRequestServerName() {
		$host = '';

		if ( isset( $_SERVER['HTTP_HOST'] ) && is_string( $_SERVER['HTTP_HOST'] ) ) {
			$host = sanitize_text_field( $_SERVER['HTTP_HOST'] );
		}

		return apply_filters( 'sq_request_server_host', $host );
	}

	/**
	 * Get server name + protocol
	 *
	 * @return string
	 */
	public function getServer() {
		return $this->getProtocol() . '://' . $this->getServerName();
	}

	/**
	 * Get protocol
	 *
	 * @return string
	 */
	public function getProtocol() {
		return is_ssl() ? 'https' : 'http';
	}

	/**
	 * Get request protocol
	 *
	 * @return string
	 */
	public function getRequestUrl() {
		$url = '';

		if ( isset( $_SERVER['REQUEST_URI'] ) && is_string( $_SERVER['REQUEST_URI'] ) ) {
			$url = $this->sanitizeUrl( $_SERVER['REQUEST_URI'] );
		}

		return apply_filters( 'sq_request_url', stripslashes( $url ) );
	}

	/**
	 * Get user agent
	 *
	 * @return string
	 */
	public function getUserAgent() {
		$agent = '';

		if ( isset( $_SERVER['HTTP_USER_AGENT'] ) && is_string( $_SERVER['HTTP_USER_AGENT'] ) ) {
			$agent = sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] );
		}

		return apply_filters( 'sq_request_agent', $agent );
	}

	/**
	 * Get referrer
	 *
	 * @return string
	 */
	public function getReferrer() {
		$referrer = '';

		if ( isset( $_SERVER['HTTP_REFERER'] ) && is_string( $_SERVER['HTTP_REFERER'] ) ) {
			$referrer = $this->sanitizeUrl( $_SERVER['HTTP_REFERER'] );
		}

		return apply_filters( 'sq_request_referrer', $referrer );
	}

	/**
	 * Get standard IP header names
	 *
	 * @return string[]
	 */
	public function getIpHeaders() {
		return [
			'HTTP_CF_CONNECTING_IP',
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'HTTP_VIA',
			'REMOTE_ADDR',
		];
	}

	/**
	 * Get browser IP
	 *
	 * @return string
	 */
	public function getIp() {
		$ip = '';

		foreach ( $this->getIpHeaders() as $var ) {
			if ( ! empty( $_SERVER[ $var ] ) && is_string( $_SERVER[ $var ] ) ) {
				$ip = sanitize_text_field( $_SERVER[ $var ] );
				$ip = explode( ',', $ip );
				$ip = array_shift( $ip );
				break;
			}
		}

		// Convert to binary
		// phpcs:ignore
		$ip = @inet_pton( trim( $ip ) );
		if ( $ip !== false ) {
			// phpcs:ignore
			$ip = @inet_ntop( $ip );  // Convert back to string
		}

		return apply_filters( 'sq_request_ip', $ip ? $ip : '' );
	}

	/**
	 * Get a cookie
	 *
	 * @param string $cookie Name.
	 *
	 * @return string|false
	 */
	public function getCookie( $cookie ) {
		if ( isset( $_COOKIE[ $cookie ] ) && is_string( $_COOKIE[ $cookie ] ) ) {
			return apply_filters( 'sq_request_cookie', sanitize_text_field( $_COOKIE[ $cookie ] ), $cookie );
		}

		return false;
	}

	/**
	 * Get a HTTP header
	 *
	 * @param string $name Header name.
	 *
	 * @return string|false
	 */
	public function getHeader( $name ) {
		$name = 'HTTP_' . strtoupper( $name );
		$name = str_replace( '-', '_', $name );

		if ( isset( $_SERVER[ $name ] ) && is_string( $_SERVER[ $name ] ) ) {
			return apply_filters( 'sq_request_header', sanitize_text_field( $_SERVER[ $name ] ), $name );
		}

		return false;
	}

	/**
	 * Get browser accept language
	 *
	 * @return string[]
	 */
	public function getAcceptLanguage() {
		if ( isset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) && is_string( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) {
			$languages = preg_replace( '/;.*$/', '', sanitize_text_field( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) );
			$languages = str_replace( ' ', '', $languages );

			return apply_filters( 'sq_request_accept_language', explode( ',', $languages ) );
		}

		return array();
	}

}
