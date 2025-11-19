<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Jsonld_Sanitize {
	/**
	 * Make sure the type matches the schema map keys
	 *
	 * @param $jsonld_type
	 *
	 * @return array|mixed|string|string[]
	 */
	public function sanitizeJsonldType( $jsonld_type ) {

		if ( $jsonld_type <> '' ) {

			//if it's an ID, return
			if ( is_numeric( $jsonld_type ) ) {
				return $jsonld_type;
			}

			//Prepare the jsonld for schema map
			$jsonld_type = strtolower( $jsonld_type );
			$jsonld_type = str_replace( ' ', '', $jsonld_type );

			switch ( $jsonld_type ) {
				case 'localstore':
				case 'localrestaurant':
					$jsonld_type = 'store';
					break;
				case 'question':
					$jsonld_type = 'faqpage';
					break;
				case 'blogposting':
					$jsonld_type = 'newsarticle';
					break;
				case 'musicalbum':
				case 'musicgroup':
					$jsonld_type = 'music';
					break;
				case 'videoobject':
					$jsonld_type = 'video';
					break;

			}

		}

		return apply_filters( 'sqp_jsonld_type', $jsonld_type );

	}

	public function cleanText( $text ) {
		$text = str_replace( array( '&#034;', '&#8220;', '&#8221;' ), '"', $text );

		return $text;
	}

	/**
	 * Check if current value is a pattern
	 *
	 * @param $value
	 *
	 * @return bool
	 */
	public function isPattern( $value ) {
		return preg_match( '/{{([^\%]+)}}/s', $value ) || preg_match( '/%([^\%]+)%/s', $value ) || preg_match( '/{{([^\%]+)}}/s', $value );
	}

	/**
	 * Change the array key. Used for ID and Type
	 *
	 * @param $keys
	 *
	 * @return array|false
	 */
	public function arrayChangeKey( $keys, $array ) {

		if ( ! array_intersect_key( $keys, $array ) ) {
			return $array;
		}

		$array_keys = array_keys( $array );
		foreach ( $keys as $old => $new ) {
			$array_keys[ array_search( $old, $array_keys ) ] = $new;

		}

		return array_combine( $array_keys, $array );
	}

	/**
	 * Change the array values
	 *
	 * @param $values
	 *
	 * @return array|false
	 */
	public function arrayChangeValue( $values, $array ) {

		foreach ( $values as $old => $new ) {
			$array = array_map( function( $val ) use ( $old, $new, $values ) {
				if ( is_array( $val ) ) {
					$val = $this->arrayChangeValue( $values, $val );
				} else {
					$val = strtolower( $val ) == strtolower( $old ) ? $new : $val;
				}

				return $val;
			}, $array );
		}

		return $array;
	}


	/**
	 * @param array $originalArray
	 * @param array $replacementArray
	 * @param bool $dynamic Add missing keys
	 *
	 * @return void
	 */
	public function replaceArrayValuesRecursive( &$originalArray, $replacementArray, $dynamic = false ) {
		foreach ( $replacementArray as $key => $value ) {
			if ( isset( $originalArray[ $key ] ) ) {
				if ( is_array( $value ) && is_array( $originalArray[ $key ] ) ) {
					$this->replaceArrayValuesRecursive( $originalArray[ $key ], $value, $dynamic );
				} elseif ( ! empty( $value ) && $originalArray[ $key ] == '' ) {
					$originalArray[ $key ] = $value;
				}
			} elseif ( $dynamic && ! empty( $value ) && strpos( $key, '@' ) === false ) {
				$originalArray[ $key ] = $value;
			}
		}
	}

}