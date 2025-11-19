<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Domain_Jsonld extends SQP_Models_Abstract_Post {

	/** @var int|string Database JsonLD id or Reusable id */
	protected $_id;

	/** @var string The Schema Type */
	protected $_jsonld_type;

	/** @var SQP_Models_Domain_Schema */
	protected $_schema;

	/** @var array Values of schema */
	protected $_schema_map;

	/** @var array of Reusable data */
	protected $_reusables;

	/**
	 * Get all reusable jsonld types
	 *
	 * @return array
	 */
	public function getReusables() {

		if ( ! isset( $this->_reusables ) ) {
			$this->_reusables = array();

			/** @var SQP_Models_Jsonld_Database $database */
			$database = SQP_Classes_ObjController::getClass( 'SQP_Models_Jsonld_Database' );

			//get all schemas from db
			$this->_reusables = $database->getReusableRows( array() );

		}

		return $this->_reusables;
	}

	/**
	 * Get the reusable schema jsonld type
	 *
	 * @param $jsonld_id
	 *
	 * @return false|mixed
	 */
	public function getReusablePostType( $jsonld_id ) {

		if ( $this->reusables ) {
			//if the reusable schema is set
			if ( isset( $this->reusables[ $jsonld_id ] ) ) {
				return $this->reusables[ $jsonld_id ]->jsonld_type;
			}
		}

		return false;
	}

	/**
	 * Check if current ID is reusable schema
	 *
	 * @param $jsonld_id
	 *
	 * @return boolean
	 */
	public function isReusable( $jsonld_id ) {

		if ( $this->reusables ) {
			//if the reusable schema is set
			if ( isset( $this->reusables[ $jsonld_id ] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Set the schema object based on Jsonld type
	 *
	 * @param $array
	 *
	 * @return void
	 */
	public function setSchema( $array ) {
		$this->_schema = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Schema' );

		if ( $this->jsonld_type ) {

			/** @var SQP_Models_Jsonld_Sanitize $sanitize */
			$sanitize = SQP_Classes_ObjController::getClass( 'SQP_Models_Jsonld_Sanitize' );

			//sanitize jsonld type
			$jsonld_type = $sanitize->sanitizeJsonldType( $this->jsonld_type );

			//Check if current schema exists
			if ( SQP_Classes_ObjController::getClassPath( 'SQP_Models_Domain_Schemas_' . ucfirst( $jsonld_type ) ) ) {
				//set the post type schema
				$this->_schema = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Schemas_' . ucfirst( $jsonld_type ), $array );

			} elseif ( is_numeric( $this->jsonld_type ) && $reusable_jsonld_type = $this->getReusablePostType( $this->jsonld_type ) ) {
				//If jsonld_type is a reusable schema ID

				//sanitize jsonld type
				$reusable_jsonld_type = $sanitize->sanitizeJsonldType( $reusable_jsonld_type );

				if ( SQP_Classes_ObjController::getClassPath( 'SQP_Models_Domain_Schemas_' . ucfirst( $reusable_jsonld_type ) ) ) {
					//set the post type schema
					$this->_schema = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Schemas_' . ucfirst( $reusable_jsonld_type ), $array );
				}

			}

			//pass the current post
			$this->_schema->setPost( $this->post );

			//apply filters on schema object
			$this->_schema = apply_filters( 'sqp_jsonld_schema_object', $this->_schema, $this );
		}

	}

	/**
	 * Get all post schema types
	 * Sanitize the schema from Squirrly
	 *
	 * @return mixed|null
	 */
	public function getJsonldTypes() {

		$jsonld_types = array();

		//get saved jsonld types
		if ( $this->post && isset( $this->post->sq->jsonld_types ) ) {
			$jsonld_types = (array) $this->post->sq->jsonld_types;
		}

		return apply_filters( 'sqp_jsonld_types', $jsonld_types );
	}

	/**
	 * Get current schema
	 *
	 * @return SQP_Models_Domain_Schema
	 */
	public function getSchema() {
		return $this->_schema;
	}

	/**
	 * Process the schema for frontend MAP + DB + MODEL
	 *
	 * @return mixed|null
	 */
	public function processSchema() {
		$data = array();

		$jsonld_types = $this->getJsonldTypes();

		if ( ! empty( $jsonld_types ) ) {
			foreach ( $jsonld_types as $jsonld_type ) {

				//set the jsonld type
				$this->_jsonld_type = $jsonld_type;

				//get all schemas from schema map
				$schema_map = $this->getSchemaMap();
				$schema     = array();

				/** @var SQP_Models_Jsonld_Sanitize $sanitize */
				$sanitize    = SQP_Classes_ObjController::getClass( 'SQP_Models_Jsonld_Sanitize' );
				$jsonld_type = $sanitize->sanitizeJsonldType( $jsonld_type );

				//check if reusable schema ID
				if ( is_numeric( $jsonld_type ) && ! isset( $schema_map[ $jsonld_type ] ) ) {

					//if the schema is set
					if ( $reusable_jsonld_type = $this->getReusablePostType( $jsonld_type ) ) {
						//sanitize jsonld type
						$reusable_jsonld_type = $sanitize->sanitizeJsonldType( $reusable_jsonld_type );

						//if schema map exists
						if ( isset( $schema_map[ $reusable_jsonld_type ] ) ) {
							$schema_map[ $jsonld_type ] = $schema_map[ $reusable_jsonld_type ];
						}
					}

				}

				//Check if the current jsonld type exists in the schema map
				if ( isset( $schema_map[ $jsonld_type ] ) ) {

					//set the schema map
					$schema_map = $schema_map[ $jsonld_type ];

					//filter the schema map
					$schema_map = apply_filters( 'sqp_jsonld_schema_map', $schema_map, $this->jsonld_type, $this );

					//sanitize schema map into schema
					$schema = $this->sanitizeSchemaMap( $schema_map );

					//filter the sanitized schema
					$schema = apply_filters( 'sqp_jsonld_schema_sanitize', $schema, $this->jsonld_type, $this );
				}

				//set current jsonld for the correct schema domain
				$this->setSchema( $schema );

				try {
					//change schema into array of values
					$schema_array = $this->_schema->toArray();
					$schema_array = $sanitize->arrayChangeKey( array( 'type' => '@type' ), $schema_array );

					if ( ! empty( $schema_array ) ) {
						$data[ $jsonld_type ] = $schema_array;
					}
				} catch ( Exception $e) {
				}
			}
		}

		return apply_filters( 'sqp_jsonld_schema', $data, $this );
	}

	/**
	 * Process the schema for the popup form
	 *
	 * @return mixed|null
	 */
	public function processHtml() {

		$html = false;

		// get the schema map structure
		$schema_map = $this->getSchemaMap();

		/** @var SQP_Models_Jsonld_Sanitize $sanitize */
		$sanitize    = SQP_Classes_ObjController::getClass( 'SQP_Models_Jsonld_Sanitize' );
		$jsonld_type = $sanitize->sanitizeJsonldType( $this->jsonld_type );

		//check if reusable schema
		if ( is_numeric( $jsonld_type ) && ! isset( $schema_map[ $jsonld_type ] ) ) {

			if ( $reusable_jsonld_type = $this->getReusablePostType( $jsonld_type ) ) {

				add_filter( 'sqp_jsonld_schema_form_title', function( $title ) use ( $reusable_jsonld_type ) {
					return $reusable_jsonld_type;
				} );

				//get the current jsonld type from reusable schema
				$reusable_jsonld_type = $sanitize->sanitizeJsonldType( $reusable_jsonld_type );

				//if schema map exists
				if ( isset( $schema_map[ $reusable_jsonld_type ] ) ) {
					$schema_map[ $jsonld_type ] = $schema_map[ $reusable_jsonld_type ];
				}

			}
		}

		//Check if the current jsonld type exists in the schema map
		if ( isset( $schema_map[ $jsonld_type ] ) ) {

			//hook the form html
			do_action( 'sqp_jsonld_before_form_html', $schema_map[ $jsonld_type ], $this );

			// filter the schema map with the current jsonld data saved
			// send the current schema map and SQP_Models_Domain_Jsonld
			$schema_map = apply_filters( 'sqp_jsonld_schema_map', $schema_map[ $jsonld_type ], $this->jsonld_type, $this );

			//add the hook for getting the schema form and modal
			$html = apply_filters( 'sqp_jsonld_schema_form', $schema_map, $this->jsonld_type, $this );

		}

		// filter the dialog form and send the current html and SQP_Models_Domain_Jsonld
		return apply_filters( 'sqp_jsonld_html', $html, $this );

	}

	/**
	 * Get the schema map from the map file
	 *
	 * @return array|false
	 */
	public function getSchemaMap() {

		if ( ! isset( $this->_schema_map ) ) {

			$json_file = dirname( __FILE__ ) . '/SchemaMap.json';

			//don't cache on debug
			if ( ! apply_filters( 'sq_jsonld_schema_cache', false ) || ! $schema_map = get_transient( md5( $json_file . SQP_VERSION ) ) ) {

				$wp_filesystem = SQP_Classes_Helpers_Tools::initFilesystem();

				if ( $wp_filesystem->exists( $json_file ) ) {
					if ( ! $schema_map = $wp_filesystem->get_contents( $json_file ) ) {
						return false;
					}

					if ( apply_filters( 'sq_jsonld_schema_cache', true ) ) {
						set_transient( md5( $json_file . SQP_VERSION ), $schema_map );
					}
				}

			}

			if ( ! empty( $schema_map ) ) {
				if ( $schema_map = json_decode( $schema_map, true ) ) {
					$schema_map = array_change_key_case( $schema_map );
					//faq is prior to question schema
					if ( ! isset( $schema_map['faqpage'] ) ) {
						$schema_map['faqpage'] = $schema_map['question'];
					}

					$this->_schema_map = $schema_map;
				}
			}

		}

		return $this->_schema_map;
	}

	/**
	 * Add the values from database to SchemaMap
	 *
	 * @param array $schema_map
	 * @param array $values
	 *
	 * @return mixed
	 */
	public function addSchemaMapValues( $schema_map, $values ) {

		$result_array = array();

		foreach ( $schema_map as $key => $value ) {

			//if the current schema has array as values
			if ( ! isset( $values[ $key ] ) && isset( $schema_map[ $key ]['field'] ) ) {

				$result_array[ $key ]                   = $schema_map[ $key ];
				$result_array[ $key ]['field']['value'] = $values;

				continue;
			}

			//if array of schemas
			if ( isset( $values[ $key ] ) && is_array( $values[ $key ] ) ) {

				//For PHP < 8, recursive on an indexed array
				if ( ! count( array_filter( array_keys( $values[ $key ] ), 'is_string' ) ) && ! count( array_filter( array_keys( $schema_map[ $key ] ), 'is_string' ) ) ) {

					foreach ( $values[ $key ] as $array ) {
						$result_array[ $key ][] = $this->addSchemaMapValues( $schema_map[ $key ][0], $array );
					}

				} else {
					$result_array[ $key ] = $this->addSchemaMapValues( $schema_map[ $key ], $values[ $key ] );
				}

				continue;

			}

			$result_array[ $key ] = $schema_map[ $key ];

			if ( isset( $result_array[ $key ]['item']['field'] ) && isset( $values[ $key ] ) ) {
				$result_array[ $key ]['item']['field']['value'] = stripslashes( $values[ $key ] );
			} elseif ( isset( $result_array[ $key ]['value'] ) && isset( $values[ $key ] ) ) {
				$result_array[ $key ]['value'] = stripslashes( $values[ $key ] );
			}
		}

		return $result_array;
	}

	/**
	 * Sanitize the mapped schema for Squirrly form
	 *
	 * @param $schema
	 *
	 * @return array|false|int|string
	 */
	public function sanitizeSchemaMap( $schema ) {
		$array = array();

		if ( ! empty( $schema ) ) {

			foreach ( $schema as $name => $rows ) {

				if ( $name == 'item' ) {

					if ( ! isset( $rows['field'] ) && isset( $rows['isGroup'] ) && $rows['isGroup'] ) {
						continue;
					}

					$value = false;

					if ( isset( $rows['field'] ) ) {
						if ( isset( $rows['field']['value'] ) ) {
							$value = $rows['field']['value'];
						} elseif ( isset( $rows['field']['default'] ) ) {
							$value = $rows['field']['default'];
						} elseif ( isset( $rows['field']['placeholder'] ) ) {
							$value = $rows['field']['placeholder'];
						} elseif ( isset( $rows['field']['options'] ) ) {
							$value = current( array_keys( $rows['field']['options'] ) );
						}

					} elseif ( isset( $rows['value'] ) ) {
						$value = $rows['value'];
					}

					if ( isset( $rows['field']['type'] ) && $rows['field']['type'] == 'number' && $value ) {
						$value = (int) $value;
					}

					return $value;
				} elseif ( is_array( $rows ) ) {

					//For PHP < 8, recursive on indexed array
					if ( ! count( array_filter( array_keys( $rows ), 'is_string' ) ) ) {
						foreach ( $rows as $index => $row ) {
							$array[ $name ][ $index ] = $this->sanitizeSchemaMap( $row );
						}

					} else {
						$array[ $name ] = $this->sanitizeSchemaMap( $rows );
					}

				}

			}
		}

		return $array;
	}

	/**
	 * Generate the html form for the current schema
	 *
	 * @param array $schema
	 * @param string $jsonld_type
	 * @param SQP_Models_Domain_Jsonld $jsonldDomain
	 *
	 * @return string
	 */
	public function generateFormHtml( $schema, $jsonld_type, $jsonldDomain ) {

		/** @var SQP_Models_Html $htmlModel */
		$htmlModel = SQP_Classes_ObjController::getClass( 'SQP_Models_Html' );

		//hook the form title
		$title = apply_filters( 'sqp_jsonld_schema_form_title', ucfirst( $jsonldDomain->jsonld_type ), $jsonldDomain );

		//Create the model form
		$form = $jsonldDomain->generateFields( $schema, $title );

		//hook the form fields
		$form = apply_filters( 'sqp_jsonld_schema_form_html', $form, $jsonldDomain );

		//set the jsonld_type as the ID if reusable schema
		$form = $htmlModel->createForm( $form, 'sqp_jsonld_form', 'sqp_jsonld_update', $jsonldDomain );

		return $htmlModel->generateModal( $form, esc_html__( "Edit Schema", 'squirrly-seo-pack' ) );

	}

	/**
	 * Generate html fields by array keys
	 *
	 * @param array $schema
	 * @param string $key
	 * @param $index
	 *
	 * @return false|string
	 */
	public function generateNewFieldsByKey( $schema, $key, $index ) {

		if ( ! empty( $schema ) ) {

			if ( isset( $schema[ $key ] ) ) {

				if ( ! count( array_filter( array_keys( $schema[ $key ] ), 'is_string' ) ) ) {
					return $this->generateFields( current( $schema[ $key ] ), ucfirst( $key ), array( $key ), $index );
				}
			}
		}

		return false;
	}

	/**
	 * Generate Form Fields based on schema array
	 *
	 * @param array $schema
	 * @param string $title
	 * @param array $parents
	 * @param false|integer $index When the field is an array of values
	 *
	 * @return string
	 */
	public function generateFields( $schema, $title, $parents = array(), $index = false ) {
		$array = $group = array();

		/** @var SQP_Models_Html $htmlModel */
		$htmlModel = SQP_Classes_ObjController::getClass( 'SQP_Models_Html' );

		if ( ! empty( $schema ) ) {

			if ( ! count( array_filter( array_keys( $schema ), 'is_string' ) ) ) {

				foreach ( $schema as $key => $schema_array ) {
					$array[] = $this->generateFields( $schema_array, $title, $parents, $key );
				}

				if ( ! empty( $parents ) ) {
					$attributes   = array();
					$attributes[] = 'data-name=' . end( $parents );
					$attributes[] = $htmlModel->getClassAttribute( [ 'sq_jsonld_add_item sq-btn sq-btn-sm sq-btn-light sq-text-dark sq-border sq-m-1 sq-mx-3 sq-px-3' ] );

					$array[] = $htmlModel->createButton( esc_html__( 'Add', 'squirrly-seo-pack' ) . ' ' . $title, 'button', join( " ", $attributes ) );
				}

			} else {
				foreach ( $schema as $key => $rows ) {
					$current = $parents;
					if ( $index !== false ) {
						$current[] = $index;
					} //if array of fields

					if ( $key == 'item' ) {

						if ( isset( $rows['isGroup'] ) && $rows['isGroup'] ) {
							//initiate group details for later use
							$group            = $rows;
							$group['title']   = ( ( isset( $rows['value'] ) ) ? $rows['value'] : $title );
							$group['parents'] = $parents;
							$group['index']   = $index;

						}

						if ( isset( $rows['field'] ) ) {
							//get the current field
							$array[] = $this->getField( $rows, $current );
						}

					} else {

						$current[] = $key;
						$array[]   = $this->generateFields( $rows, ucfirst( $key ), $current );
					}

				}
			}

		}

		$html = join( "\n", $array );

		if ( ! empty( $group ) ) {
			$group['html'] = $html;
			$html          = $htmlModel->createGroup( $group );
		}

		return $html;

	}

	public function generateAddOption( $html, $title, $classes, $attributes = '' ) {

		/** @var SQP_Models_Html $htmlModel */
		$htmlModel = SQP_Classes_ObjController::getClass( 'SQP_Models_Html' );

		$header = $htmlModel->createFieldDiv( $title, $htmlModel->getClassAttribute( [ 'sq_group_header  sq-text-left' ] ) );

		return $htmlModel->createFieldDiv( $header . $html, $htmlModel->getClassAttribute( $classes ) . $attributes );

	}

	/**
	 * @param array $parents
	 * @param array $row Schema row wit inputs
	 *
	 * @return string
	 */
	public function getField( $row, $parents ) {

		// hook the current field row based on parent structure
		$row = apply_filters( 'sqp_get_field_before', $row, $parents, $this );

		$row['field']['id']   = join( '_', $parents );
		$row['field']['name'] = 'schema[' . join( '][', $parents ) . ']';

		/** @var SQP_Models_Html $htmlModel */
		$htmlModel = SQP_Classes_ObjController::getClass( 'SQP_Models_Html' );
		$field     = $htmlModel->createField( $row );

		// filter the current field
		// return the array key name and the schema row array
		return apply_filters( 'sqp_get_field_after', $field, $row );
	}

	/**
	 * Get array
	 *
	 * @return array
	 */
	public function toArray() {

		$json = array(
			'id'          => $this->id,
			'url_hash'    => $this->post->hash,
			'post'        => $this->post->toArray(),
			'jsonld_type' => $this->jsonld_type,
			'schema'      => $this->schema->toArray()
		);

		// filter the jsonld array
		// return the current jsonld array and  SQP_Models_Domain_Jsonld
		return apply_filters( 'sqp_jsonld_array', $json, $this );

	}

}
