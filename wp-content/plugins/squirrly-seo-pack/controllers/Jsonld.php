<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Controllers_Jsonld extends SQP_Classes_FrontController {

	/**
	 * @var int Total number of results
	 */
	public $max_num_pages = 0;

	/**
	 * @var array List of reusable schemas
	 */
	public $schemas = array();

	function init() {

		$tab = preg_replace( "/[^a-zA-Z0-9]/", "", SQP_Classes_Helpers_Tools::getValue( 'tab', 'reusable' ) );

		//Create Redirects table if not exists
		$this->checkTables();

		if ( $tab && method_exists( $this, $tab ) ) {
			call_user_func( array( $this, $tab ) );
		}

		//Load view
		$this->show_view( 'Jsonld/' . esc_attr( ucfirst( $tab ) ) );

	}

	public function reusable() {

		//get the reusable schemas from database
		$page   = SQP_Classes_Helpers_Tools::getValue( 'spage', 1 );
		$search = SQP_Classes_Helpers_Tools::getValue( 'squery' );
		$num    = SQP_Classes_Helpers_Tools::getValue( 'snum', SQP_Classes_Helpers_Tools::getOption( 'sq_posts_per_page' ) );
		$sort   = SQP_Classes_Helpers_Tools::getValue( 'ssort' );
		$order  = SQP_Classes_Helpers_Tools::getValue( 'sorder' );

		$args = array(
			'start'  => ( $page - 1 ) * $num,
			'num'    => $num,
			'sort'   => $sort,
			'order'  => $order,
			'search' => $search
		);

		/** @var SQP_Models_Jsonld_Database $database */
		$database = SQP_Classes_ObjController::getClass( 'SQP_Models_Jsonld_Database' );

		//get all schemas from db
		$this->schemas = $database->getReusableRows( $args );

		if ( $database->getCount() > 0 ) {
			$this->max_num_pages = ceil( $database->getCount() / $num );
		}

		/////////////////

		$data = array(
			'wrap' => '#sq_wrap'
		);

		$handles = array(
			'dependencies' => array( 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker', 'clipboard', 'wp-i18n' )
		);

		//load Squirrly styles
		if(class_exists('SQ_Classes_ObjController')) {
			if ( is_rtl() ) {
				$handles[] = SQ_Classes_ObjController::getClass( 'SQ_Classes_DisplayController' )->loadMedia( 'sqbootstrap.rtl' );
				$handles[] = SQ_Classes_ObjController::getClass( 'SQ_Classes_DisplayController' )->loadMedia( 'rtl' );
			} else {
				$handles[] = SQ_Classes_ObjController::getClass( 'SQ_Classes_DisplayController' )->loadMedia( 'sqbootstrap' );
			}
			$handles[] = SQ_Classes_ObjController::getClass( 'SQ_Classes_DisplayController' )->loadMedia( 'highlight' );
			$handles[] = SQ_Classes_ObjController::getClass( 'SQ_Classes_DisplayController' )->loadMedia( 'patterns' );
		}

		if ( ! SQP_Classes_Helpers_Tools::getOption( 'sq_seoexpert' ) ) {
			SQP_Classes_ObjController::getClass( 'SQP_Classes_DisplayController' )->loadMedia( 'beginner' );
		}

		SQP_Classes_ObjController::getClass( 'SQP_Classes_DisplayController' )->loadMedia( 'jquery/datetimepicker' );
		SQP_Classes_ObjController::getClass( 'SQP_Classes_DisplayController' )->loadMedia( 'jsonld/schemas', $handles );

		$handle = SQP_Classes_ObjController::getClass( 'SQP_Classes_DisplayController' )->loadMedia( 'jsonld/reusable', $handles );

		wp_localize_script( $handle, 'sqp_jsonld', $data );


	}

	/**
	 * Load the JsonLd on backend
	 *
	 * @return void
	 * @throws Exception
	 */
	public function hookInit() {

		if ( SQP_Classes_Helpers_Tools::getOption( 'sq_rich_snippets' ) && SQP_Classes_Helpers_Tools::getOption( 'sq_auto_jsonld' ) ) {

			//Load Jsonld types
			add_filter( 'sq_jsonld_types', array( $this, 'loadJsonLdTypes' ), 10, 2 );

			//Load Reusable Jsonld types
			if ( SQP_Classes_Helpers_Tools::getOption( 'sq_jsonld_reusable_schemas' ) ) {
				add_filter( 'sq_reusable_jsonld_types', array( $this, 'loadReusableJsonLdTypes' ) );
			}

			//Sanitize schema types from Squirrly
			add_filter( 'sq_option_patterns', array( $this, 'sanitizePatternsJsonLdTypes' ), 11, 1 );

			//Check the schema classes and add them in the schema
			add_action( 'save_post', array( $this->model, 'checkSchemaClasses' ), 12, 2 );

			//process the global author if set like this
			if ( SQP_Classes_Helpers_Tools::getOption( 'sq_jsonld_personal' ) && SQP_Classes_Helpers_Tools::getOption( 'sq_jsonld_global_person' ) ) {
				$this->processGlobalAuthor();
			}

			//Create Jsonld table if not exists
			$this->checkTables();

		}

	}

	/**
	 * Load the Jsonld on Frontend
	 *
	 * @return void
	 * @throws Exception
	 */
	public function hookFrontinit() {

		if ( SQP_Classes_Helpers_Tools::getOption( 'sq_rich_snippets' ) && SQP_Classes_Helpers_Tools::getOption( 'sq_auto_jsonld' ) ) {

			//hook Squirrly SEO Jsonld before Squirrly
			add_action( 'sq_json_ld', array( $this, 'processJsonld' ), 9 );

			//process the global author if set like this
			if ( SQP_Classes_Helpers_Tools::getOption( 'sq_jsonld_personal' ) && SQP_Classes_Helpers_Tools::getOption( 'sq_jsonld_global_person' ) ) {
				$this->processGlobalAuthor();
			}

		}

	}

	/**
	 * Process the custom Jsonld from Advanced Pack
	 * Hooks Squirrly SEO Jsonld "sq_json_ld", "sq_structured_data_type_for_page", "sq_json_ld_data"
	 *
	 * @return void
	 */
	public function processJsonld() {

		//get the post from Squirrly SEO when JsonLD is loaded
		if ( class_exists('SQ_Classes_ObjController') && $post = SQ_Classes_ObjController::getClass( 'SQ_Models_Frontend' )->getPost() ) {

			$post = $post->toArray();

			//set the post_id for domain mapping
			$post['post_id'] = $post['ID'];

			/** @var SQP_Models_Domain_Jsonld $jsonldDomain */
			$jsonldDomain = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Jsonld', $post );

			add_filter( 'sqp_jsonld_types', function( $jsonld_types ) use ( $jsonldDomain ) {

				//for Home Page, add Person or Organization
				if ( $jsonldDomain->post->post_type == 'home' ) {

					if ( SQP_Classes_Helpers_Tools::getOption( 'sq_jsonld_personal' ) ) {
						$jsonld_types[] = 'Person';
					} else {
						$jsonld_types[] = 'Organization';
					}

				} elseif ( SQP_Classes_Helpers_Tools::getOption( 'sq_jsonld_breadcrumbs' ) ) {
					//if active, add breadcrumbs on all pages
					//except home page
					$jsonld_types[] = 'Breadcrumblist';
				}

				return $jsonld_types;

			} );

			add_filter( 'sq_structured_data_type_for_page', function( $types ) use ( $jsonldDomain ) {
				$jsonld_types = $jsonldDomain->getJsonldTypes();

				if ( ! empty( $jsonld_types ) ) {

					//prepare for Squirrly schema to avoid integer types
					//transform any numeric key into string for Squirrly graph validation
					$jsonld_types = array_map( function( $jsonld_type ) {

						//if reusable
						if ( is_numeric( $jsonld_type ) ) {
							return md5( $jsonld_type );
						}

						/** @var SQP_Models_Jsonld_Sanitize $sanitize */
						$sanitize    = SQP_Classes_ObjController::getClass( 'SQP_Models_Jsonld_Sanitize' );

						//return sanitized
						return $sanitize->sanitizeJsonldType( $jsonld_type );

					}, $jsonld_types );

					$types = array_merge( $types, $jsonld_types );
				}

				if ( ! empty( $types ) ) {
					$types = array_filter( array_unique( $types ) );
				}

				return $types;

			} );

			add_filter( 'sq_json_ld_data', function( $data ) use ( $jsonldDomain ) {

				//Get all JsonLD schemas
				$data = $jsonldDomain->processSchema();

				//sanitize the data for Squirrly SEO schema
				//transform any numeric key into string for Squirrly graph validation
				//match the keys from the hook "sq_structured_data_type_for_page"
				foreach ( $data as $key => $row ) {
					if ( is_numeric( $key ) ) {
						$data[ md5( $key ) ] = $row;
						unset( $data[ $key ] );
					}
				}

				$data = array_map( function( $row ) {
					if (is_array($row)){
						if (isset($row['type'])){
							$row['@type'] = $row['type'];
							unset($row['type']);
						}
						
						$row = array_map(function($row){
							if (is_array($row)){
								if (isset($row['type'])){
									$row['@type'] = $row['type'];
									unset($row['type']);
								}

								$row = array_map(function($row){
									if (isset($row['type'])){
										$row['@type'] = $row['type'];
										unset($row['type']);
									}
									return $row;
								}, $row);
							}

							return $row;
						}, $row);
					}

					return $row;
				}, $data);

				return $data;

			} );

			/** @var SQP_Models_Domain_Patterns $patterns */
			$patterns = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Patterns' );

			//Add the patterns from Squirrly SEO automation for this post type is set
			//for headline and description
			add_filter( 'sqp_jsonld_schema_map', array( $patterns, 'addAutomationPatterns' ), 11, 3 );

			/** @var SQP_Models_Jsonld_Database $database */
			$database = SQP_Classes_ObjController::getClass( 'SQP_Models_Jsonld_Database' );
			add_filter( 'sqp_jsonld_schema_map', array( $database, 'changeSchemaMapWithValues' ), 13, 3 );

			if ( SQP_Classes_Helpers_Tools::getOption( 'sq_jsonld_reusable_schemas' ) ) {
				add_filter( 'sqp_jsonld_schema_map', array( $database, 'changeSchemaMapWithReusableValues' ), 12, 3 );
			}

			//Change the patterns with the values in frontend
			add_filter( 'sqp_jsonld_schema_sanitize', function( $schema, $jsonld_type, $jsonldDomain ) {

				//Get all data from the current post and send it to patterns for mapping
				$post_array = array_merge( $jsonldDomain->post->toArray(), $jsonldDomain->post->sq->toArray() );

				/** @var SQP_Models_Domain_Patterns $patterns */
				$patterns = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Patterns', $post_array );

				//Process all patterns
				return $patterns->processPatterns( $schema );

			}, 20, 3 );

			//Add the correct format to the time
			add_filter( 'sqp_jsonld_schema_datetime', function( $value ) {
				/** @var SQP_Models_Jsonld_Sanitize $sanitize */
				$sanitize = SQP_Classes_ObjController::getClass( 'SQP_Models_Jsonld_Sanitize' );

				if ( isset( $value ) && $value <> '' && ! $sanitize->isPattern( $value ) ) {
					$value = date_i18n( 'c', strtotime( $value ) );
				}

				return $value;
			} );

			//transform string into array of values
			add_filter( 'sqp_jsonld_schema_array', function( $value ) {
				if ( is_string( $value ) && $value <> '' ) {
					return explode( ',', $value );
				}

				return $value;
			} );

		}

	}

	/**
	 * Handle the global author option
	 *
	 * @return void
	 * @throws Exception
	 */
	public function processGlobalAuthor() {
		$jsonld = SQP_Classes_Helpers_Tools::getOption( 'sq_jsonld' );

		if ( isset( $jsonld['Person'] ) ) {
			add_action( 'sqp_jsonld_author_id', function( $id, $post ) use ( $jsonld ) {
				return trailingslashit( $post->url ) . strtolower( preg_replace( '/[^a-zA-Z1-9]/', '', $jsonld['Person']['name'] ) ) . '/' . "#" . strtolower( substr( md5( $jsonld['Person']['name'] ), 0, 10 ) );
			}, 20, 2 );
			add_action( 'sqp_jsonld_author_name', function( $name ) use ( $jsonld ) {
				return $jsonld['Person']['name'];
			}, 20 );
			add_action( 'sqp_jsonld_author_url', '__return_false', 20 );
			add_action( 'sqp_jsonld_publisher_id', function( $id ) {
				return home_url() . '#Person';
			}, 20 );
			add_action( 'sqp_jsonld_author_job', function( $job ) use ( $jsonld ) {
				return $jsonld['Person']['jobTitle'];
			}, 20 );
			add_action( 'sqp_jsonld_author_phone', function( $telephone ) use ( $jsonld ) {
				return $jsonld['Person']['telephone'];
			}, 20 );
			add_action( 'sqp_jsonld_author_image', function( $image ) use ( $jsonld ) {
				if ( isset( $jsonld['Person']['image']['url'] ) && $jsonld['Person']['image']['url'] <> '' ) {
					/** @var SQP_Models_Domain_Jsonld_Image $image */
					$image = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Jsonld_Image', $jsonld['Person']['image'] );

					return $image->toArray();
				}

				return false;
			}, 20 );
			add_action( 'sqp_jsonld_author_socials', function( $socials ) use ( $jsonld ) {
				$socials = SQP_Classes_Helpers_Tools::getOption( 'socials' );

				//Load the social media
				$jsonld_socials = array();
				if ( isset( $socials['facebook_site'] ) && $socials['facebook_site'] <> '' ) {
					$jsonld_socials[] = $socials['facebook_site'];
				}
				if ( isset( $socials['twitter_site'] ) && $socials['twitter_site'] <> '' ) {
					$jsonld_socials[] = $socials['twitter_site'];
				}
				if ( isset( $socials['instagram_url'] ) && $socials['instagram_url'] <> '' ) {
					$jsonld_socials[] = $socials['instagram_url'];
				}
				if ( isset( $socials['linkedin_url'] ) && $socials['linkedin_url'] <> '' ) {
					$jsonld_socials[] = $socials['linkedin_url'];
				}
				if ( isset( $socials['pinterest_url'] ) && $socials['pinterest_url'] <> '' ) {
					$jsonld_socials[] = $socials['pinterest_url'];
				}
				if ( isset( $socials['youtube_url'] ) && $socials['youtube_url'] <> '' ) {
					$jsonld_socials[] = $socials['youtube_url'];
				}

				if ( ! empty( $jsonld_socials ) ) {
					return $jsonld_socials;
				}

				return false;
			}, 20 );
		}
	}


	/**
	 * Check if the required tables exist
	 *
	 * @return void
	 */
	public function checkTables() {
		SQP_Classes_ObjController::getClass( 'SQP_Models_Jsonld_Database' )->checkTablesExist();
	}

	/**
	 * Hook Squirrly SEO and load the JsonLd Types from the Advance pack
	 *
	 * @param $jsonld_types
	 * @param $pattern
	 *
	 * @return mixed
	 */
	public function loadJsonLdTypes( $jsonld_types, $pattern ) {

		//get the Advanced Pack schema types
		return $this->model->getJsonLdTypes();

	}

	/**
	 * Hook Squirrly SEO and load the JsonLd Reusable Types from the Advance pack
	 *
	 * @param $jsonld_types
	 *
	 * @return mixed
	 */
	public function loadReusableJsonLdTypes( $jsonld_types ) {

		//get the Advanced Pack reusable schema types
		return $this->model->getReusableJsonLdTypes();
	}

	/**
	 * Sanitize the old squirrly schema types
	 *
	 * @param $patterns
	 *
	 * @return mixed
	 */
	public function sanitizePatternsJsonLdTypes( $patterns ) {

		/** @var SQP_Models_Jsonld_Sanitize $sanitize */
		$sanitize = SQP_Classes_ObjController::getClass( 'SQP_Models_Jsonld_Sanitize' );

		if ( ! empty( $patterns ) ) {
			foreach ( $patterns as &$pattern ) {
				if ( isset( $pattern['jsonld_types'] ) && ! empty( $pattern['jsonld_types'] ) ) {

					//sanitize all jsonld types
					$pattern['jsonld_types'] = array_map( function( $jsonld_type ) use ( $sanitize ) {

						switch ( $jsonld_type ) {
							case 'local store':
							case 'local restaurant':
								$jsonld_type = 'store';
								break;
							case 'question':
								$jsonld_type = 'faq page';
								break;
						}

						return $jsonld_type;

					}, $pattern['jsonld_types'] );

					$pattern['jsonld_types'] = array_unique( $pattern['jsonld_types'] );

				}
			}
		}

		return $patterns;
	}


	/**
	 * Called when Post action is triggered
	 *
	 * @return void
	 */
	public function action() {

		if ( ! SQP_Classes_Helpers_Tools::userCan( 'sq_manage_snippet' ) ) {
			return;
		}

		parent::action();
		switch ( SQP_Classes_Helpers_Tools::getValue( 'action' ) ) {

			case 'sqp_jsonld_get_jsonld_types':
				$jsonld_types = apply_filters( 'sq_jsonld_types', array(), false );

				/** @var SQP_Models_Html $htmlModel */
				$htmlModel = SQP_Classes_ObjController::getClass( 'SQP_Models_Html' );

				$html = $htmlModel->createSearchBar();

				$html .= '<ul class="sq-col-12 sq-row sq-p-0 sq-m-3">';
				foreach ( $jsonld_types as $jsonld_type ) {
					$html .= '<li class="sq_dropdown_parent sq-col-5 sq-py-2 sq-px-3 sq-m-2 sq-mx-3 sq-border sq-rounded" style="position: relative;" >
                                <div class="sq-col-12 sq-row sq-p-0 sq-m-0">
                                    <div class="sq-col">' . esc_attr( ucfirst( $jsonld_type ) ) . '</div>
                                    <div class="sq-text-right">
                                        <i class="sq_jsonld_type_add sq-col sq-p-1 sq-m-0 fa-solid fa-plus" data-jsonld-type="' . esc_attr( $jsonld_type ) . '" style="cursor: pointer"></i>
                                    </div>
                                </div>
                            </li>';
				}
				$html .= '</ul>';
				$html = $htmlModel->generateModal( $html, esc_html__( "Add New Schema", 'squirrly-seo-pack' ) );
				wp_send_json_success( $html );
				break;

			case 'sqp_jsonld_add_key':

				//show only the current field
				add_action( 'sqp_jsonld_before_form_html', function( $schema_map, $jsonldDomain ) {

					//don't add database values
					remove_all_filters( 'sqp_jsonld_schema_map' );

					//get the current name and index
					$name  = SQP_Classes_Helpers_Tools::getValue( 'name' );
					$index = SQP_Classes_Helpers_Tools::getValue( 'index' );

					if ( $form = $jsonldDomain->generateNewFieldsByKey( $schema_map, $name, $index ) ) {
						wp_send_json_success( $form );
					} else {
						wp_send_json_error( esc_html__( "Can't be customized.", 'squirrly-seo-pack' ) );
					}

				}, 11, 2 );

                //continue with edit
			case 'sqp_jsonld_edit':

				if ( SQP_Classes_Helpers_Tools::getValue( 'jsonld_type' ) ) {

					/** @var SQP_Models_Jsonld_Database $database */
					$database = SQP_Classes_ObjController::getClass( 'SQP_Models_Jsonld_Database' );

					/** @var SQP_Models_Domain_Patterns $patterns */
					$patterns = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Patterns' );

					$data = $database->sanitizeData( $_POST );

					/** @var SQP_Models_Domain_Jsonld $jsonldDomain */
					$jsonldDomain = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Jsonld', $data );

					//Add the patterns from Squirrly SEO automation for this post type is set
					//for headline and description
					add_filter( 'sqp_jsonld_schema_map', array( $patterns, 'addAutomationPatterns' ), 11, 3 );
					add_filter( 'sqp_jsonld_schema_map', array( $database, 'changeSchemaMapWithValues' ), 13, 3 );

					if ( SQP_Classes_Helpers_Tools::getOption( 'sq_jsonld_reusable_schemas' ) ) {
						add_filter( 'sqp_jsonld_schema_map', array(
							$database,
							'changeSchemaMapWithReusableValues'
						), 12, 3 );
						add_filter( 'sqp_jsonld_schema_form_html', function( $form, $jsonldDomain ) {

							/** @var SQP_Models_Html $htmlModel */
							$htmlModel = SQP_Classes_ObjController::getClass( 'SQP_Models_Html' );

							if ( ! $jsonldDomain->isReusable( $jsonldDomain->jsonld_type ) ) {

								$fields   = array();
								$field    = array(
									'isRequired' => true,
									'field'      => array(
										'label'       => esc_html__( 'Reusable Name', 'squirrly-seo-pack' ),
										'placeholder' => esc_html__( 'Reusable', 'squirrly-seo-pack' ) . ' ' . ucfirst( $jsonldDomain->jsonld_type ),
										'id'          => 'reusable_name',
										'name'        => 'name',
									)
								);
								$fields[] = $htmlModel->createField( $field );

								$help     = $htmlModel->createFieldDiv( esc_html__( "You'll need to add it manually from reusable schemas after you save.", 'squirrly-seo' ), $htmlModel->getClassAttribute( [ 'sq-float-left sq-p-3 sq-text-black-50' ] ) );
								$button   = $htmlModel->createButton( esc_html__( 'Cancel', 'squirrly-seo-pack' ), 'button', $htmlModel->getClassAttribute( [ 'sqp_jsonld_reusable_cancel sq-btn sq-btn-light sq-text-dark sq-border sq-m-1 sq-px-5' ] ) );
								$button   .= $htmlModel->createButton( esc_html__( 'Save Reusable', 'squirrly-seo-pack' ), 'button', $htmlModel->getClassAttribute( [ 'sqp_jsonld_reusable_save sq-btn sq-btn-primary sq-m-1 sq-px-5' ] ) );
								$fields[] = $htmlModel->createFieldDiv( $help . $button, $htmlModel->getClassAttribute( [ 'sq-text-right sq-row' ] ) );

								$form .= $htmlModel->createFieldDiv( join( '', $fields ), $htmlModel->getClassAttribute( [ 'sq-hidden sqp_jsonld_reusable_name sq-p-4' ] ) );
								$form .= $htmlModel->createButton( esc_html__( 'Save As Reusable', 'squirrly-seo-pack' ), 'button', $htmlModel->getClassAttribute( [ 'sqp_jsonld_reusable_prepare sq-btn sq-btn-light sq-text-dark sq-border sq-m-3 sq-px-5 sq-float-right' ] ) );

							} else {
								$form .= $htmlModel->createFieldDiv( esc_html__( "It's already a reusable.", 'squirrly-seo' ), $htmlModel->getClassAttribute( [ 'sq-float-right sq-p-3 sq-text-black-50' ] ) );
							}

							return $form;
						}, 11, 2 );
					}

					//Create the html based on the current schema map
					add_filter( 'sqp_jsonld_schema_form', array( $jsonldDomain, 'generateFormHtml' ), 12, 3 );

					if ( $html = $jsonldDomain->processHtml() ) {
						wp_send_json_success( $html );
					} else {
						wp_send_json_error( esc_html__( "Can't be customized.", 'squirrly-seo-pack' ) );
					}

				}
				break;

			case 'sqp_jsonld_update':

				/** @var SQP_Models_Jsonld_Database $database */ $database = SQP_Classes_ObjController::getClass( 'SQP_Models_Jsonld_Database' );
				$data                                                      = $database->sanitizeData( $_POST );

				if ( $data ) {
					/** @var SQP_Models_Domain_Jsonld $item */
					$item = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Jsonld', $data );

					//Add the current schema in database
					$response = $database->addRow( $item, $data );

					if ( SQP_Classes_Helpers_Tools::isAjax() ) {
						if ( ! is_wp_error( $response ) ) {
							wp_send_json_success( esc_html__( 'Saved!', 'squirrly-seo-pack' ) );
						} else {
							wp_send_json_error( $response );
						}
					} else {
						if ( ! is_wp_error( $response ) ) {
							SQP_Classes_Error::setMessage( esc_html__( 'Saved!', 'squirrly-seo-pack' ) );
						} else {
							SQP_Classes_Error::setError( $response->get_error_message() );
						}
					}

				} else {
					wp_send_json_error( esc_html__( 'Invalid data', 'squirrly-seo-pack' ) );
				}

				break;

			case 'sqp_jsonld_preview':

				/** @var SQP_Models_Jsonld_Database $database */ $database = SQP_Classes_ObjController::getClass( 'SQP_Models_Jsonld_Database' );
				$data                                                      = $database->sanitizeData( $_POST );

				/** @var SQP_Models_Domain_Jsonld $jsonldDomain */
				$jsonldDomain = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Jsonld', $data );

				if ( $jsonldDomain->getPost() ) {

					//Initiate Squirrly SEO class
					if ( ! class_exists( 'SQ_Classes_ObjController' ) ) {
						return;
					}
					SQ_Classes_ObjController::getClass( 'SQ_Models_Services_JsonLD' );

					add_action( 'sq_json_ld', array( $this, 'processJsonld' ), 9 );
					remove_filter( 'sq_json_ld', array(
						SQ_Classes_ObjController::getClass( 'SQ_Models_Services_JsonLD' ),
						'packJsonLd'
					), 99 );

					//Hook the json_ld
					$jsonld = apply_filters( 'sq_json_ld', false );

					//return JSON-LD to preview
					$jsonld_data = wp_json_encode( $jsonld, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
					$jsonld_data = SQP_Classes_Helpers_Sanitize::normalizeChars( $jsonld_data );

					wp_send_json_success( $jsonld_data );

				}

				wp_send_json_error( esc_html__( 'Invalid data', 'squirrly-seo-pack' ) );

				break;

			case 'sqp_jsonld_delete':

				/** @var SQP_Models_Jsonld_Database $database */ $database = SQP_Classes_ObjController::getClass( 'SQP_Models_Jsonld_Database' );

				//sanitize data
				$data = $database->sanitizeData( $_POST );

				/** @var SQP_Models_Domain_Jsonld $jsonldDomain */
				$jsonldDomain = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Jsonld', $data );

				SQP_Classes_ObjController::getClass( 'SQP_Models_Jsonld_Database' )->deleteRow( $jsonldDomain );

				wp_send_json_success( esc_html__( 'Deleted!', 'squirrly-seo-pack' ) );

				break;

			case 'sq_jsonld_import':

				if ( ! SQP_Classes_Helpers_Tools::userCan( 'sq_manage_settings' ) ) {
					return;
				}

				$import   = false;
				$platform = SQP_Classes_Helpers_Tools::getValue( 'sq_import_platform' );

				//Import schemas from the selected plugin
				if ( $platform ) {
					$import = SQP_Classes_ObjController::getClass( 'SQP_Models_Jsonld_Import' )->importJsonld( $platform );
				}

				if ( $import ) {
					SQP_Classes_Error::setMessage( sprintf( esc_html__( 'Successfully imported %d schemas(s)!', 'squirrly-seo-pack' ), $import ) );
				} else {
					SQP_Classes_Error::setError( esc_html__( 'No schemas found to import.', 'squirrly-seo-pack' ) );
				}

				break;

			case 'sq_jsonld_backup':

				if ( ! SQP_Classes_Helpers_Tools::userCan( 'sq_manage_settings' ) ) {
					return;
				}

				header( 'Content-Type: application/octet-stream' );
				header( "Content-Transfer-Encoding: Binary" );
				header( "Content-Disposition: attachment; filename=squirrly-schemas-" . gmdate( 'Y-m-d' ) . ".sql" );

				/**
				 * @var SQP_Models_Jsonld_Database $database
				 */
				$database = SQP_Classes_ObjController::getClass( 'SQP_Models_Jsonld_Database' );
				echo base64_encode( $database->createTableBackup() );

				exit();

			case 'sq_jsonld_restore':

				if ( ! SQP_Classes_Helpers_Tools::userCan( 'sq_manage_settings' ) ) {
					return;
				}

				if ( ! empty( $_FILES['sq_jsonld'] ) && $_FILES['sq_jsonld']['tmp_name'] <> '' ) {
					$fp       = fopen( $_FILES['sq_jsonld']['tmp_name'], 'rb' );
					$sql_file = '';

					while ( ( $line = fgets( $fp ) ) !== false ) {
						$sql_file .= $line;
					}

					$sql_file = @base64_decode( $sql_file );

					if ( $sql_file <> '' && strpos( $sql_file, 'INSERT INTO' ) !== false ) {
						try {

							$queries = explode( "INSERT INTO", $sql_file );

							/**
							 * @var SQP_Models_Jsonld_Database $database
							 */
							$database = SQP_Classes_ObjController::getClass( 'SQP_Models_Jsonld_Database' );
							$database->restoreTableBackup( $queries );
							SQP_Classes_Error::setMessage( esc_html__( "Great! The backup is restored.", 'squirrly-seo-pack' ) . " <br /> " );

						} catch ( Exception $e) {
							SQP_Classes_Error::setError( esc_html__( "Error! The backup is not valid.", 'squirrly-seo-pack' ) . " <br /> " );
						}
					} else {
						SQP_Classes_Error::setError( esc_html__( "Error! The backup is not valid.", 'squirrly-seo-pack' ) . " <br /> " );
					}
				} else {
					SQP_Classes_Error::setError( esc_html__( "Error! You have to enter a previously saved backup file.", 'squirrly-seo-pack' ) . " <br /> " );
				}
				break;

			case 'sqp_jsonld_get_reusable_jsonld_types':

				/** @var SQP_Models_Domain_Jsonld $jsonldDomain */ $jsonldDomain = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Jsonld' );

				//get the saved reusable schemas
				$reusable_schemas = $jsonldDomain->getReusables();

				if ( ! empty( $reusable_schemas ) ) {
					/** @var SQP_Models_Html $htmlModel */
					$htmlModel = SQP_Classes_ObjController::getClass( 'SQP_Models_Html' );

					$html = $htmlModel->createSearchBar();

					$html .= '<ul class="sq-col-12 sq-row sq-p-0 sq-m-3">';
					foreach ( $reusable_schemas as $row ) {
						$html .= '<li class="sq_dropdown_parent sq-col-5 sq-py-2 sq-px-3 sq-m-2 sq-mx-3 sq-border sq-rounded" style="position: relative;" >
                                <div class="sq-col-12 sq-row sq-p-0 sq-m-0">
                                    <div class="sq-col">' . esc_attr( ucfirst( $row->name ) ) . '</div>
                                    <div class="sq-text-right">
                                        <i class="sq_jsonld_type_add sq-col sq-p-1 sq-m-0 fa-solid fa-plus" data-jsonld-type="' . esc_attr( $row->id ) . '" style="cursor: pointer"></i>
                                    </div>
                                </div>
                            </li>';
					}
					$html .= '</ul>';
				} else {
					$html = '<div class="sq-text-center sq-p-3">' . esc_html__( 'No reusable schemas found.', 'squirrly-seo-pack' ) . ' ' . sprintf( esc_html__( '%s Add reusable schema %s.', 'squirrly-seo-pack' ), '<a href="' . SQP_Classes_Helpers_Tools::getAdminUrl( 'sq_jsonld', 'reusable' ) . '" >', '</a>' ) . '</div>';
				}

				$html = $htmlModel->generateModal( $html, esc_html__( "Add Reusable Schema", 'squirrly-seo-pack' ) );

				wp_send_json_success( $html );
				break;

			case 'sqp_jsonld_reusable_edit':

				if ( SQP_Classes_Helpers_Tools::getValue( 'jsonld_type' ) ) {

					/** @var SQP_Models_Jsonld_Database $database */
					$database = SQP_Classes_ObjController::getClass( 'SQP_Models_Jsonld_Database' );
					/** @var SQP_Models_Domain_Patterns $patterns */
					$patterns = SQP_Classes_ObjController::getClass( 'SQP_Models_Domain_Patterns' );

					$data = $database->sanitizeData( $_POST );

					/** @var SQP_Models_Domain_Jsonld $jsonldDomain */
					$jsonldDomain = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Jsonld', $data );

					//Add the patterns from Squirrly SEO automation for this post type is set
					//for headline and description
					add_filter( 'sqp_jsonld_schema_map', array( $patterns, 'addAutomationPatterns' ), 11, 3 );

					//check if this is a reusable jsonld
					if ( $jsonld_id = SQP_Classes_Helpers_Tools::getValue( 'jsonld_id' ) ) {
						//Hook the schema with the reusable database details
						add_filter( 'sqp_jsonld_schema_map', function( $schema_map, $jsonld_type, $jsonldDomain ) use ( $database, $jsonld_id ) {
							return $database->changeSchemaMapWithReusableValues( $schema_map, $jsonld_id, $jsonldDomain );
						}, 12, 3 );
					}

					//Create the html based on the current schema map
					add_filter( 'sqp_jsonld_schema_form', function( $schema, $jsonld_type, $jsonldDomain ) {
						$jsonld_type = SQP_Classes_Helpers_Tools::getValue( 'jsonld_type' );

						/** @var SQP_Models_Html $htmlModel */
						$htmlModel = SQP_Classes_ObjController::getClass( 'SQP_Models_Html' );

						//Schemas from database
						$this->schemas = $this->model->getReusableJsonLdTypes();

						//Create the model form
						/** @var SQP_Models_Domain_Jsonld $jsonldDomain $form */
						$form   = $jsonldDomain->generateFields( $schema, $jsonld_type );
						$name   = esc_html__( 'Reusable', 'squirrly-seo-pack' ) . ' ' . ucfirst( $jsonld_type );
						$inputs = array();

						if ( $jsonld_id = SQP_Classes_Helpers_Tools::getValue( 'jsonld_id' ) ) {
							if ( isset( $this->schemas[ $jsonld_id ] ) && ! empty( $this->schemas[ $jsonld_id ] ) ) {

								$field = array(
									'field' => array(
										'value' => $jsonld_id,
										'type'  => 'hidden',
										'name'  => 'jsonld_id',
									)
								);

								$inputs[] = $htmlModel->createField( $field );

								$name = $this->schemas[ $jsonld_id ];

							}
						}

						$field = array(
							'isRequired' => true,
							'field'      => array(
								'label'       => esc_html__( 'Reusable Name', 'squirrly-seo-pack' ),
								'placeholder' => $name,
								'id'          => 'reusable_name',
								'name'        => 'name',
								'help'        => esc_html__( 'Enter a name for this reusable field', 'squirrly-seo-pack' ),
							)
						);

						$inputs[] = $htmlModel->createFieldDiv( $htmlModel->createField( $field ) );

						$form = $htmlModel->createForm( join( '', $inputs ) . $form, 'sqp_jsonld_form', 'sqp_jsonld_reusable_update', $jsonldDomain );

						return $htmlModel->generateModal( $form, esc_html__( "Edit Reusable Schema", 'squirrly-seo-pack' ) );

					}, 12, 3 );

					if ( $html = $jsonldDomain->processHtml() ) {
						wp_send_json_success( $html );
					} else {
						wp_send_json_error( esc_html__( "Can't be customized.", 'squirrly-seo-pack' ) );
					}
				}

				wp_send_json_error( esc_html__( "Can't be customized.", 'squirrly-seo-pack' ) );

				break;

			case 'sqp_jsonld_reusable_update':

				/** @var SQP_Models_Jsonld_Database $database */ $database = SQP_Classes_ObjController::getClass( 'SQP_Models_Jsonld_Database' );
				$data                                                      = $database->sanitizeData( $_POST );

				if ( $data ) {
					/** @var SQP_Models_Domain_Jsonld $item */
					$item = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Jsonld', $data );

					//Set the reusable name
					if ( ! isset( $data['name'] ) ) {
						$data['name'] = esc_html__( 'Reusable', 'squirrly-seo-pack' ) . ' ' . ucfirst( $item->jsonld_type );
					}

					//Add the reusable row
					$response = $database->addReusableRow( $item, $data );

					//check if ajax call
					if ( SQP_Classes_Helpers_Tools::isAjax() ) {
						if ( ! is_wp_error( $response ) ) {
							wp_send_json_success( esc_html__( 'Saved!', 'squirrly-seo-pack' ) );
						} else {
							wp_send_json_error( $response );
						}
					} else {
						SQP_Classes_Error::setMessage( esc_html__( 'Saved!', 'squirrly-seo-pack' ) );
					}

				} else {
					wp_send_json_error( esc_html__( 'Invalid data', 'squirrly-seo-pack' ) );
				}

				break;

			case 'sqp_jsonld_reusable_delete':

				/** @var SQP_Models_Jsonld_Database $database */ $database = SQP_Classes_ObjController::getClass( 'SQP_Models_Jsonld_Database' );
				$data                                                      = $database->sanitizeData( $_POST );

				if ( $data ) {
					/** @var SQP_Models_Domain_Jsonld $item */
					$item = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Jsonld', $data );

					//Delete the reusable row
					$response = $database->deleteReusableRow( $item );

					//check if ajax call
					if ( SQP_Classes_Helpers_Tools::isAjax() ) {
						if ( ! is_wp_error( $response ) ) {
							wp_send_json_success( esc_html__( 'Saved!', 'squirrly-seo-pack' ) );
						} else {
							wp_send_json_error( $response );
						}
					} else {
						if ( ! is_wp_error( $response ) ) {
							SQP_Classes_Error::setMessage( esc_html__( 'Saved!', 'squirrly-seo-pack' ) );
						} else {
							SQP_Classes_Error::setError( $response->get_error_message() );
						}
					}

				} else {
					wp_send_json_error( esc_html__( 'Invalid data', 'squirrly-seo-pack' ) );
				}

				break;

			case 'sq_ajax_reusable_jsonld_bulk_delete':

				if ( ! SQP_Classes_Helpers_Tools::userCan( 'sq_manage_settings' ) ) {
					wp_send_json_error( esc_html__( "You do not have permission to perform this action!", 'squirrly-seo-pack' ) );
				}

				/** @var SQP_Models_Jsonld_Database $database */
				$database = SQP_Classes_ObjController::getClass( 'SQP_Models_Jsonld_Database' );

				$ids = SQP_Classes_Helpers_Tools::getValue( 'inputs', array() );

				if ( ! empty( $ids ) ) {
					foreach ( $ids as $id ) {

						/**
						 * @var SQP_Models_Domain_Jsonld $item
						 */
						$item = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Jsonld', array( 'id' => $id ) );

						//Delete the reusable row
						$database->deleteReusableRow( $item );

					}

					wp_send_json_success( esc_html__( "Saved!", 'squirrly-seo-pack' ) );
				} else {
					wp_send_json_error( esc_html__( "Invalid Rule!", 'squirrly-seo-pack' ) );
				}

				exit();

		}
	}


}