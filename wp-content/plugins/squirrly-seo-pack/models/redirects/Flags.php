<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Redirects_Flags {

	const QUERY_IGNORE = 'ignore';
	const QUERY_EXACT = 'exact';
	const QUERY_PASS = 'pass';
	const QUERY_EXACT_ORDER = 'exactorder';

	const FLAG_QUERY = 'flag_query';
	const FLAG_CASE = 'flag_case';
	const FLAG_TRAILING = 'flag_trailing';
	const FLAG_REGEX = 'flag_regex';

	const FLAG_POST = 'flag_post';
	const FLAG_POST_TYPE = 'flag_post_type';
	const FLAG_TERM = 'flag_term';
	const FLAG_TAXONOMY = 'flag_taxonomy';

	/**
	 * Case-insensitive matching
	 *
	 * @var boolean
	 */
	private $flag_case = false;

	/**
	 * Ignored trailing slashes
	 *
	 * @var boolean
	 */
	private $flag_trailing = false;

	/**
	 * Regular expression
	 *
	 * @var boolean
	 */
	private $flag_regex = false;

	/**
	 * Query parameter matching
	 *
	 * @var self::QUERY_EXACT|self::QUERY_IGNORE|self::QUERY_PASS|self::QUERY_EXACT_ORDER
	 */
	private $flag_query = self::QUERY_EXACT;

	/**
	 * Regular expression
	 *
	 * @var boolean
	 */
	private $flag_post = 0;
	private $flag_post_type = 'post';
	private $flag_term = 0;
	private $flag_taxonomy = 0;

	/**
	 * Values that have been set
	 *
	 * @var array
	 */
	private $values_set = array();

	/**
	 * Get list of valid query types as an array
	 *
	 * @return string[]
	 */
	private function getAllowedQuery() {
		return [
			self::QUERY_IGNORE,
			self::QUERY_EXACT,
			self::QUERY_PASS,
			self::QUERY_EXACT_ORDER,
		];
	}

	/**
	 * Parse flag data.
	 *
	 * @param array $option Flag data.
	 *
	 * @return void
	 */
	public function setFlags( array $option ) {
		$option = array_merge( SQP_Classes_Helpers_Tools::getOption( 'sq_redirect_flags' ), $option );

		if ( isset( $option[ self::FLAG_QUERY ] ) && in_array( $option[ self::FLAG_QUERY ], $this->getAllowedQuery(), true ) ) {
			$this->flag_query = $option[ self::FLAG_QUERY ];
		} else {
			$this->flag_query = self::QUERY_EXACT;
		}

		if ( isset( $option[ self::FLAG_CASE ] ) ) {
			$this->flag_case = ( $option[ self::FLAG_CASE ] ? true : false );
		} else {
			$this->flag_case = false;
		}

		if ( isset( $option[ self::FLAG_TRAILING ] ) ) {
			$this->flag_trailing = ( $option[ self::FLAG_TRAILING ] ? true : false );
		} else {
			$this->flag_trailing = false;
		}

		if ( isset( $option[ self::FLAG_POST ] ) ) {
			$this->flag_post = $option[ self::FLAG_POST ];
		} else {
			$this->flag_post = 0;
		}

		if ( isset( $option[ self::FLAG_POST_TYPE ] ) ) {
			$this->flag_post_type = $option[ self::FLAG_POST_TYPE ];
		} else {
			$this->flag_post_type = false;
		}

		if ( isset( $option[ self::FLAG_TERM ] ) ) {
			$this->flag_term = $option[ self::FLAG_TERM ];
		} else {
			$this->flag_term = 0;
		}

		if ( isset( $option[ self::FLAG_TAXONOMY ] ) ) {
			$this->flag_taxonomy = $option[ self::FLAG_TAXONOMY ];
		} else {
			$this->flag_taxonomy = false;
		}

		if ( isset( $option[ self::FLAG_REGEX ] ) ) {
			$this->flag_regex = ( $option[ self::FLAG_REGEX ] ? true : false );

			if ( $this->flag_regex ) {
				// Regex auto-disables other things
				$this->flag_query = self::QUERY_EXACT;
			}
		} else {
			$this->flag_regex = false;
		}

		// Keep track of what values have been set, so we know what to override with defaults later
		$this->values_set = array_intersect( array_keys( $option ), array_keys( $this->getJson() ) );

	}

	/**
	 * Return `true` if ignore trailing slash, `false` otherwise
	 *
	 * @return boolean
	 */
	public function isIgnoreTrailing() {
		return $this->flag_trailing;
	}

	/**
	 * Return `true` if ignore case, `false` otherwise
	 *
	 * @return boolean
	 */
	public function isIgnoreCase() {
		return $this->flag_case;
	}

	/**
	 * Return `true` if ignore trailing slash, `false` otherwise
	 *
	 * @return boolean
	 */
	public function isRegex() {
		return $this->flag_regex;
	}

	/**
	 * Return `true` if exact query match, `false` otherwise
	 *
	 * @return boolean
	 */
	public function isQueryExact() {
		return $this->flag_query === self::QUERY_EXACT;
	}

	/**
	 * Return `true` if exact query match in set order, `false` otherwise
	 *
	 * @return boolean
	 */
	public function isQueryExactOrder() {
		return $this->flag_query === self::QUERY_EXACT_ORDER;
	}

	/**
	 * Return `true` if ignore query params, `false` otherwise
	 *
	 * @return boolean
	 */
	public function isQueryIgnore() {
		return $this->flag_query === self::QUERY_IGNORE;
	}

	/**
	 * Return `true` if ignore and pass query params, `false` otherwise
	 *
	 * @return boolean
	 */
	public function isQueryPass() {
		return $this->flag_query === self::QUERY_PASS;
	}

	/**
	 * Return the flags as a JSON object
	 *
	 * @return array
	 */
	public function getJson() {
		return [
			self::FLAG_QUERY     => $this->flag_query,
			self::FLAG_CASE      => $this->isIgnoreCase(),
			self::FLAG_TRAILING  => $this->isIgnoreTrailing(),
			self::FLAG_REGEX     => $this->isRegex(),
			//post details
			self::FLAG_POST      => $this->flag_post,
			self::FLAG_POST_TYPE => $this->flag_post_type,
			self::FLAG_TERM      => $this->flag_term,
			self::FLAG_TAXONOMY  => $this->flag_taxonomy,
		];
	}

	/**
	 * Return flag data, with defaults removed from the data.
	 *
	 * @param array $defaults Defaults to remove.
	 *
	 * @return array
	 */
	public function getJsonWithoutDefaults( $defaults ) {
		$json = $this->getJson();

		if ( count( $defaults ) > 0 ) {
			foreach ( $json as $key => $value ) {
				if ( isset( $defaults[ $key ] ) && $value === $defaults[ $key ] ) {
					unset( $json[ $key ] );
				}
			}
		}

		return $json;
	}

	/**
	 * Return flag data, with defaults filling in any gaps not set.
	 *
	 * @return array
	 */
	public function getJsonWithDefaults() {
		$settings = SQP_Classes_Helpers_Tools::getOption( 'sq_redirect_flags' );
		$json     = $this->getJson();

		$defaults = [
			self::FLAG_QUERY    => $settings[ self::FLAG_QUERY ],
			self::FLAG_CASE     => $settings[ self::FLAG_CASE ],
			self::FLAG_TRAILING => $settings[ self::FLAG_TRAILING ],
			self::FLAG_REGEX    => $settings[ self::FLAG_REGEX ],
		];

		foreach ( $this->values_set as $key ) {
			if ( ! isset( $json[ $key ] ) ) {
				$json[ $key ] = $defaults[ $key ];
			}
		}

		return $json;
	}
}
