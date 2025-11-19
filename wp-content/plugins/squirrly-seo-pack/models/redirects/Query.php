<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Redirects_Query {

	/**
	 * @type Integer
	 */
	const RECURSION_LIMIT = 10;

	/**
	 * Original query parameters (used when passing)
	 *
	 * @var array
	 */
	private $original_query = array();

	/**
	 * Match query parameters (used only for matching, and maybe be lowercased)
	 *
	 * @var array
	 */
	private $match_query = array();

	/**
	 * Is this an exact match?
	 *
	 * @var boolean|string
	 */
	private $match_exact = false;

	/**
	 * Url Handles
	 *
	 * @var SQP_Models_Redirects_UrlHandle
	 */
	private $url_handler;

	/**
	 * Constructor
	 *
	 * @param string $url URL.
	 * @param SQP_Models_Redirects_Flags $flags URL flags.
	 */
	public function init( $url, $flags ) {

		$this->original_query = $this->getUrlQuery( $url );
		$this->match_query    = $this->original_query;

		/**
		 * @var SQP_Models_Redirects_UrlHandle $url_handler
		 */
		$this->url_handler = SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_UrlHandle' );

		if ( $flags->isIgnoreCase() ) {
			$url               = $this->url_handler->toLower( $url );
			$this->match_query = $this->getUrlQuery( $url );
		}

	}

	/**
	 * Does this object match the URL?
	 *
	 * @param String $url URL to match.
	 * @param SQP_Models_Redirects_Flags $flags Source flags.
	 *
	 * @return boolean
	 */
	public function isMatch( $url, SQP_Models_Redirects_Flags $flags ) {

		if ( $flags->isIgnoreCase() ) {
			$url = $this->url_handler->toLower( $url );
		}

		// If we can't parse the query params then match the params exactly
		if ( $this->match_exact !== false ) {
			return $this->isStringMatch( $this->getQueryAfter( $url ), $this->match_exact, $flags->isIgnoreCase() );
		}

		$target = $this->getUrlQuery( $url );

		// All params in the source have to exist in the request, but in any order
		$matched = $this->getQuerySame( $this->match_query, $target, $flags->isIgnoreCase() );

		if ( count( $matched ) !== count( $this->match_query ) ) {
			// Source params aren't matched exactly
			return false;
		}

		// Get list of whatever is left over
		$query_diff = $this->getQueryDiff( $this->match_query, $target );
		$query_diff = array_merge( $query_diff, $this->getQueryDiff( $target, $this->match_query ) );

		if ( $flags->isIgnoreCase() || $flags->isQueryPass() ) {
			return true;  // This ignores all other query params
		}

		// In an exact match there shouldn't be anymore params
		return count( $query_diff ) === 0;
	}

	/**
	 * Return true if the two strings match, false otherwise. Pays attention to case sensitivity
	 *
	 * @param string $first First string.
	 * @param string $second Second string.
	 * @param boolean $case Case sensitivity.
	 *
	 * @return boolean
	 */
	private function isStringMatch( $first, $second, $case ) {
		if ( $case ) {
			return $this->url_handler->toLower( $first ) === $this->url_handler->toLower( $second );
		}

		return $first === $second;
	}

	/**
	 * Pass query params from one URL to another URL, ignoring any params that already exist on the target.
	 *
	 * @param string $target_url The target URL to add params to.
	 * @param string $requested_url The source URL to pass params from.
	 * @param SQP_Models_Redirects_Flags $flags Any URL flags.
	 *
	 * @return string URL, modified or not.
	 */
	public function addToTarget( $target_url, $requested_url, SQP_Models_Redirects_Flags $flags ) {

		//if set to pass the params to target
		if ( $flags->isQueryPass() && $target_url ) {
			$source_query  = new SQP_Models_Redirects_Query();
			$request_query = new SQP_Models_Redirects_Query();

			$source_query->init( $target_url, $flags );
			$request_query->init( $requested_url, $flags );

			// Now add any remaining params
			$query_diff   = $source_query->getQueryDiff( $source_query->original_query, $request_query->original_query );
			$request_diff = $request_query->getQueryDiff( $request_query->original_query, $source_query->original_query );

			foreach ( $request_diff as $key => $value ) {
				$query_diff[ $key ] = $value;
			}

			// Remove any params from $source that are present in $request - we don't allow
			// predefined params to be overridden
			foreach ( array_keys( $query_diff ) as $key ) {
				if ( isset( $source_query->original_query[ $key ] ) ) {
					unset( $query_diff[ $key ] );
				}
			}

			return $this->buildUrl( $target_url, $query_diff );
		}

		return $target_url;
	}

	/**
	 * Build a URL from a base and query parameters
	 *
	 * @param String $url Base URL.
	 * @param Array $query_array Query parameters.
	 *
	 * @return String
	 */
	public function buildUrl( $url, $query_array ) {
		$query = http_build_query( array_map( function( $value ) {
			if ( $value === null ) {
				return '';
			}

			return $value;
		}, $query_array ) );

		$query = preg_replace( '@%5B\d*%5D@', '[]', $query );  // Make these look like []

		foreach ( $query_array as $key => $value ) {
			if ( $value === null ) {
				$search  = str_replace( '%20', '+', rawurlencode( $key ) . '=' );
				$replace = str_replace( '%20', '+', rawurlencode( $key ) );

				$query = str_replace( $search, $replace, $query );
			}
		}

		$query = str_replace( '%252B', '+', $query );

		if ( $query ) {
			// Get any fragment
			$target_fragment = wp_parse_url( $url, PHP_URL_FRAGMENT );

			// If we have a fragment we need to ensure it comes after the query parameters, not before
			if ( $target_fragment ) {
				// Remove fragment
				$url = str_replace( '#' . $target_fragment, '', $url );

				// Add to the end of the query
				$query .= '#' . $target_fragment;
			}

			return $url . ( strpos( $url, '?' ) === false ? '?' : '&' ) . $query;
		}

		return $url;
	}

	/**
	 * Get a URL with the given base and query parameters from this Url_Query
	 *
	 * @param String $url Base URL.
	 *
	 * @return String
	 */
	public function getUrlWithQuery( $url ) {
		return $this->buildUrl( $url, $this->original_query );
	}

	/**
	 * Get the query parameters
	 *
	 * @return Array
	 */
	public function get() {
		return $this->original_query;
	}

	/**
	 * Does the URL and the query params contain no parameters?
	 *
	 * @param String $url URL.
	 * @param Array $params Query params.
	 *
	 * @return boolean
	 */
	private function isExactMatch( $url, $params ) {
		// No parsed query params, but we have query params on the URL - some parsing error with wp_parse_str
		if ( count( $params ) === 0 && $this->hasQueryParams( $url ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get query parameters from a URL
	 *
	 * @param String $url URL.
	 *
	 * @return array
	 */
	private function getUrlQuery( $url ) {
		$params   = array();
		$query    = $this->getQueryAfter( $url );
		$internal = $this->parseStr( $query );

		wp_parse_str( $query ? $query : '', $params );

		// For exactness and due to the way parse_str works we go through and check any query param without a value
		foreach ( $params as $key => $value ) {
			if ( is_string( $value ) && strlen( $value ) === 0 && strpos( $url, $key . '=' ) === false ) {
				$params[ $key ] = null;
			}
		}

		// A work-around until we replace parse_str with internal function
		foreach ( $internal as $pos => $internal_param ) {
			if ( $internal_param['parse_str'] !== $internal_param['name'] ) {
				foreach ( $params as $key => $value ) {
					if ( $key === $internal_param['parse_str'] ) {
						unset( $params[ $key ] );
						unset( $internal[ $pos ] );
						$params[ $internal_param['name'] ] = $value;
					}
				}
			}
		}

		if ( $this->isExactMatch( $url, $params ) ) {
			$this->match_exact = $query;
		}

		return $params;
	}

	/**
	 * A replacement for parse_str, which behaves oddly in some situations (spaces and no param value)
	 *
	 * @param string $query Query.
	 *
	 * @return array
	 */
	private function parseStr( $query ) {
		$params = array();

		if ( strlen( $query ) === 0 ) {
			return $params;
		}

		$parts = explode( '&', $query ? $query : '' );

		foreach ( $parts as $part ) {
			$param     = explode( '=', $part );
			$parse_str = array();

			wp_parse_str( $part, $parse_str );

			$params[] = array(
				'name'      => str_replace( [ '[', ']', '%5B', '%5D' ], '', str_replace( '+', ' ', $param[0] ) ),
				'value'     => isset( $param[1] ) ? str_replace( '+', ' ', $param[1] ) : null,
				'parse_str' => implode( '', array_keys( $parse_str ) ),
			);
		}

		return $params;
	}

	/**
	 * Does the URL contain query parameters?
	 *
	 * @param String $url URL.
	 *
	 * @return boolean
	 */
	public function hasQueryParams( $url ) {
		$qpos = strpos( $url, '?' );

		if ( $qpos === false ) {
			return false;
		}

		return true;
	}

	/**
	 * Get parameters after the ?
	 *
	 * @param String $url URL.
	 *
	 * @return String
	 */
	public function getQueryAfter( $url ) {
		$qpos  = strpos( $url, '?' );
		$qrpos = strpos( $url, '\\?' );

		// No ? anywhere - no query
		if ( $qpos === false ) {
			return '';
		}

		// Found an escaped ? and it comes before the non-escaped ?
		if ( $qrpos !== false && $qrpos < $qpos ) {
			return substr( $url, $qrpos + 2 );
		}

		// Standard query param
		return substr( $url, $qpos + 1 );
	}

	private function getQueryCase( array $query ) {
		$keys = array();
		foreach ( array_keys( $query ) as $key ) {
			$keys[ $this->url_handler->toLower( $key ) ] = $key;
		}

		return $keys;
	}

	/**
	 * Get query parameters that are the same in both query arrays
	 *
	 * @param array $source_query Source query params.
	 * @param array $target_query Target query params.
	 * @param bool $is_ignore_case Ignore case.
	 * @param integer $depth Current recursion depth.
	 *
	 * @return array
	 */
	public function getQuerySame( array $source_query, array $target_query, $is_ignore_case, $depth = 0 ) {
		if ( $depth > self::RECURSION_LIMIT ) {
			return array();
		}

		$source_keys = $this->getQueryCase( $source_query );
		$target_keys = $this->getQueryCase( $target_query );

		$same = array();
		foreach ( $source_keys as $key => $original_key ) {
			// Does the key exist in the target
			if ( isset( $target_keys[ $key ] ) ) {
				// Key exists. Now match the value
				$source_value = $source_query[ $original_key ];
				$target_value = $target_query[ $target_keys[ $key ] ];
				$add          = false;

				if ( is_array( $source_value ) && is_array( $target_value ) ) {
					$add = $this->getQuerySame( $source_value, $target_value, $is_ignore_case, $depth + 1 );

					if ( count( $add ) !== count( $source_value ) ) {
						$add = false;
					}
				} elseif ( is_string( $source_value ) && is_string( $target_value ) ) {
					$add = $this->isStringMatch( $source_value, $target_value, $is_ignore_case ) ? $source_value : false;
				} elseif ( $source_value === null && $target_value === null ) {
					$add = null;
				}

				if ( ! empty( $add ) || is_numeric( $add ) || $add === '' || $add === null ) {
					$same[ $original_key ] = $add;
				}
			}
		}

		return $same;
	}

	/**
	 * Get the difference in query parameters
	 *
	 * @param array $source_query Source query params.
	 * @param array $target_query Target query params.
	 * @param integer $depth Current recursion depth.
	 *
	 * @return array
	 */
	public function getQueryDiff( array $source_query, array $target_query, $depth = 0 ) {
		if ( $depth > self::RECURSION_LIMIT ) {
			return array();
		}

		$diff = array();
		foreach ( $source_query as $key => $value ) {
			if ( array_key_exists( $key, $target_query ) && is_array( $value ) && is_array( $target_query[ $key ] ) ) {
				$add = $this->getQueryDiff( $source_query[ $key ], $target_query[ $key ], $depth + 1 );

				if ( ! empty( $add ) ) {
					$diff[ $key ] = $add;
				}
			} elseif ( ! array_key_exists( $key, $target_query ) || ! $this->isValue( $value ) || ! $this->isValue( $target_query[ $key ] ) || $target_query[ $key ] !== $source_query[ $key ] ) {
				$diff[ $key ] = $value;
			}
		}

		return $diff;
	}

	/**
	 * Check if string value
	 *
	 * @param $value
	 *
	 * @return bool
	 */
	private function isValue( $value ) {
		return is_string( $value ) || $value === null;
	}
}
