<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

//Initiate Squirrly SEO class
if ( ! class_exists( 'SQ_Classes_ObjController' ) ) {
	return;
}
SQ_Classes_ObjController::getClass( 'SQ_Models_Domain_Patterns' );

class SQP_Models_Domain_Patterns extends SQ_Models_Domain_Patterns {

	protected $_url;
	protected $_image;
	protected $_video;

	//Organization patterns
	protected $_org_name;
	protected $_org_description;
	protected $_org_url;
	protected $_org_logo;
	protected $_org_phone;
	//////////////////////
	protected $_publisher;

	public function getImage() {

		if ( ! isset( $this->_image ) ) {
			/** @var SQP_Models_Domain_Jsonld_Image $imageDomain */
			$imageDomain = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Jsonld_Image' );

			$this->_image = $imageDomain->url;
		}

		return $this->_image;
	}

	/**
	 * @throws Exception
	 */
	public function getVideo() {

		if ( ! isset( $this->_video ) ) {
			/** @var SQP_Models_Domain_Jsonld_Video $videoDomain */
			$videoDomain = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Jsonld_Video' );

			if ( $video = $videoDomain->toArray() ) {
				if ( isset( $video['url'] ) ) {
					$this->_video = $video['url'];
				}
				if ( isset( $video['image'] ) ) {
					$this->_image = $video['image'];
				}
			}
		}

		return $this->_video;
	}

	public function getPublisher() {

		$publisher = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Jsonld_Publisher' );

		if ( isset( $this->_publisher ) ) {
			$publisher = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Jsonld_Publisher', $this->_publisher );
		}

		return $publisher;
	}

	public function getOrg_name() {

		$jsonld = SQP_Classes_Helpers_Tools::getOption( 'sq_jsonld' );
		$type   = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Schemas_Organization' )->type;

		if ( isset( $jsonld[ $type ]['name'] ) ) {
			return $jsonld[ $type ]['name'];
		}

		return false;
	}

	public function getOrg_description() {

		$jsonld = SQP_Classes_Helpers_Tools::getOption( 'sq_jsonld' );
		$type   = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Schemas_Organization' )->type;

		if ( isset( $jsonld[ $type ]['description'] ) ) {
			return $jsonld[ $type ]['description'];
		}

		return false;
	}

	public function getOrg_url() {

		$jsonld = SQP_Classes_Helpers_Tools::getOption( 'sq_jsonld' );
		$type   = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Schemas_Organization' )->type;

		if ( isset( $jsonld[ $type ]['url'] ) && $jsonld[ $type ]['url'] <> '' ) {
			return $jsonld[ $type ]['url'];
		}

		return home_url();
	}

	public function getOrg_logo() {

		$jsonld = SQP_Classes_Helpers_Tools::getOption( 'sq_jsonld' );
		$type   = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Schemas_Organization' )->type;

		if ( isset( $jsonld[ $type ]['logo']['url'] ) ) {
			return $jsonld[ $type ]['logo']['url'];
		}

		return '';

	}

	public function getOrg_phone() {

		$jsonld = SQP_Classes_Helpers_Tools::getOption( 'sq_jsonld' );
		$type   = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Schemas_Organization' )->type;

		if ( isset( $jsonld[ $type ]['contactPoint']['telephone'] ) ) {
			return $jsonld[ $type ]['contactPoint']['telephone'];
		}

		return '';

	}

	/**
	 * Add the values from automation patterns to SchemaMap
	 *
	 * @param array $schema_map
	 * @param string $jsonld_type
	 * @param SQP_Models_Domain_Jsonld $post
	 *
	 * @return array|mixed|void
	 */
	public function addAutomationPatterns( $schema_map, $jsonld_type, $post ) {

		$patterns = SQP_Classes_Helpers_Tools::getOption( 'patterns' );

		//if the post type exists in the patterns
		if ( $patterns && $post->post_type && isset( $patterns[ $post->post_type ] ) ) {

			//replace empty fields with automation patterns
			foreach ( $schema_map as $key => $value ) {
				switch ( $key ) {
					case 'headline':
						if ( isset( $value['item']['field']['placeholder'] ) && isset( $patterns[ $post->post_type ]['title'] ) ) {
							if ( isset( $value['item']['isPattern'] ) && $value['item']['isPattern'] ) {
								$schema_map[ $key ]['item']['field']['placeholder'] = $patterns[ $post->post_type ]['title'];
							}
						}
						break;
					case 'description':
						if ( isset( $value['item']['field']['placeholder'] ) && isset( $patterns[ $post->post_type ]['description'] ) ) {
							if ( isset( $value['item']['isPattern'] ) && $value['item']['isPattern'] ) {
								$schema_map[ $key ]['item']['field']['placeholder'] = $patterns[ $post->post_type ]['description'];
							}
						}
						break;
				}

			}

		}

		return $schema_map;

	}

	/**
	 * Process all patterns for current schema array
	 *
	 * @param array $schema
	 *
	 * @return array
	 */
	public function processPatterns( $schema ) {

		if ( ! empty( $schema ) && ! is_string( $schema ) ) {
			foreach ( $schema as $name => &$value ) {

				if ( $name == '' ) {
					continue;
				}

				if ( is_array( $value ) ) {

					//For PHP < 8, recursive on indexed array
					if ( ! count( array_filter( array_keys( $value ), 'is_string' ) ) ) {
						foreach ( $value as $index => $row ) {
							$value[ $index ] = $this->processPatterns( $row );
						}
					} else {
						$value = $this->processPatterns( $value );
					}

				} elseif ( is_string( $value ) && $value <> '' ) {

					if ( strpos( $value, '%%' ) !== false ) { //if there are still patterns from Yoast
						$value = preg_replace( '/%%([^\%\s]+)%%/s', '{{$1}}', $value );
					}

					if ( strpos( $value, '%' ) !== false ) { //if there are still patterns from Rank Math
						$value = preg_replace( '/%([^\%\s]+)%/s', '{{$1}}', $value );
					}

					if ( is_string( $value ) && $value <> '' ) {
						$value = preg_replace_callback( '/\{\{([^\}\s]+)\}\}/s', array(
							$this,
							'processPattern'
						), $value );
					}

				}
			}
		}

		return $schema;

	}

	/**
	 * Replace the found pattern with the value
	 *
	 * @param array $match Found patterns
	 *
	 * @return string
	 */
	public function processPattern( $match ) {

		$value = '';
		$found_pattern = $match[0];

		//get the patterns
		$patterns = array_flip( $this->getPatterns() );

		if ( isset( $patterns[ $found_pattern ] ) ) {

			//Set the key
			$key = $patterns[ $found_pattern ];

			//return value if the pattern is set for this key
			$value = $this->processPatternKey( $key );

		} elseif ( strpos( $found_pattern, 'customfields' ) !== false ) {

			//check custom field pattern
			preg_match( '/\(([^\)]+)\)/si', $found_pattern, $custom_match );

			if ( ! empty( $custom_match[1] ) && $this->currentpost->ID ) {
				$fields = explode( '|', $custom_match[1] );

				if ( ! empty( $fields ) && count( $fields ) == 2 ) {
					//get the custom field from post-meta is set
					if ( $values = get_post_meta( $this->currentpost->ID, $fields[0], true ) ) {

						if ( is_array( $values ) && ! empty( $values ) ) {
							if ( isset( $values[ $fields[1] ] ) && is_string( $values[ $fields[1] ] ) && $values[ $fields[1] ] <> '' ) {
								return wp_strip_all_tags( $values[ $fields[1] ] );
							}
						} elseif ( $values = json_decode( $fields[0], true ) ) {
							if ( isset( $values[ $fields[1] ] ) && is_string( $values[ $fields[1] ] ) && $values[ $fields[1] ] <> '' ) {
								return wp_strip_all_tags( $values[ $fields[1] ] );
							}
						}

						return false;
					}
				}


			}

		} elseif ( strpos( $found_pattern, 'customfield' ) !== false ) {

			//check custom field pattern
			preg_match( '/\(([^\)]+)\)/si', $found_pattern, $custom_match );

			if ( ! empty( $custom_match[1] ) && $this->currentpost->ID ) {

				//get custom field from user meta if profile
				if( get_post_type($this->currentpost->ID) == 'profile' ){
					if ( $value = get_user_meta( $this->currentpost->ID, $custom_match[1], true ) ) {
						if ( is_string( $value ) && $value <> '' ) {
							return wp_strip_all_tags( $value );
						}
					}
				}elseif ( $value = get_post_meta( $this->currentpost->ID, $custom_match[1], true ) ) {
					//get the custom field from post meta is set
					if ( is_string( $value ) && $value <> '' ) {
						return wp_strip_all_tags( $value );
					}
				}

			}
		}

		return $value;
	}

	/**
	 * Return the value of the key
	 *
	 * @param $key
	 *
	 * @return mixed
	 */
	public function processPatternKey( $key ) {

		$value = '';

		//if the pattern is set
		if ( $this->$key ) {
			$value = $this->$key;
		}

		return $value;
	}
}
