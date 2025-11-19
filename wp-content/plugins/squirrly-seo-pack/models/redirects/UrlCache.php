<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Redirects_UrlCache {

	const CACHE_TIME = 3600;

	/**
	 * Array of URLs that have been cached
	 *
	 * @var array
	 */
	private $cached = array();

	/**
	 * Get the current cache key
	 *
	 * @param String $url URL we are looking at.
	 *
	 * @return string
	 */
	private function getKey( $url ) {
		return apply_filters( 'sq_cache_key', md5( $url ) );
	}

	/**
	 * Get the cache entry for a URL
	 *
	 * @param String $url Requested URL.
	 *
	 * @return SQP_Models_Domain_Redirect[]|bool
	 */
	public function get( $url ) {
		if ( ! SQP_Classes_Helpers_Tools::getOption( 'sq_redirects_cache' ) ) {
			return false;
		}

		$cache_key = $this->getKey( $url );

		// Look in cache
		$result = get_transient( $cache_key );

		// If a result was found then remember we are using the cache, so we don't need to re-save it later
		if ( $result !== false ) {
			$this->cached[ $url ] = true;
		}

		return $result;
	}


	/**
	 * Set the cache for a URL
	 *
	 * @param string $url
	 * @param array|false $value
	 *
	 * @return bool
	 */
	public function set( $url, $value ) {
		if ( ! SQP_Classes_Helpers_Tools::getOption( 'sq_redirects_cache' ) || isset( $this->cached[ $url ] ) ) {
			return false;
		}

		$cache_key = $this->getKey( $url );

		if ( ! $value ) {
			$value = array();
		}

		set_transient( $cache_key, $value, self::CACHE_TIME );

		return true;
	}

	/**
	 * Clear the cache
	 *
	 * @param $url
	 *
	 * @return bool
	 */
	public function clear( $url ) {
		$cache_key = $this->getKey( $url );

		delete_transient( $cache_key );

		return true;
	}


	/**
	 * Cache the redirect into database
	 *
	 * @param string $url
	 * @param SQP_Models_Domain_Redirect $item
	 *
	 * @return bool
	 */
	public function cacheRedirect( $url, $item ) {
		return $this->set( $url, array( $item ) );
	}

	/**
	 * Cache the fail redirect into database
	 *
	 * @param string $url
	 * @param SQP_Models_Domain_Redirect[] $redirects
	 *
	 * @return bool
	 */
	public function cacheFailRedirect( $url, $redirects ) {

		if ( empty( (array) $redirects ) ) {
			return $this->set( $url, array() );
		}

		return false;
	}


}
