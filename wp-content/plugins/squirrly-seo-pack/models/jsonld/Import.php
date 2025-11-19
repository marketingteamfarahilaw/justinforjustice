<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Jsonld_Import {
	private $_plugins;

	public function __construct() {
		$this->_plugins = array(
			'seo-by-rank-math' => array(
				'title'    => esc_html__( 'Rank Math', 'squirrly-seo-pack' ),
				'function' => 'importRankMath',
			),
			//			'wordpress-seo' => array(
			//				'title' => esc_html__('Yoast SEO','squirrly-seo-pack'),
			//				'function' => 'importYoast',
			//			),
		);
	}

	public function getAllPlugins() {
		return $this->_plugins;
	}

	/**
	 * Filter only the active plugins
	 *
	 * @return array
	 */
	public function getActivePlugins( $plugins ) {
		$found = array();

		if ( empty( $plugins ) ) {
			return $plugins;
		}

		$all_plugins = (array) get_option( 'active_plugins', array() );

		if ( is_multisite() ) {
			$all_plugins = array_merge( $all_plugins, array_keys( (array) get_site_option( 'active_sitewide_plugins' ) ) );
		}

		foreach ( $all_plugins as $plugin ) {
			if ( strpos( $plugin, '/' ) !== false ) {
				$plugin = substr( $plugin, 0, strpos( $plugin, '/' ) );
			}

			if ( in_array( $plugin, array_keys( $plugins ) ) ) {
				$found[ $plugin ] = $plugins[ $plugin ];
			}
		}

		return $found;
	}

	/**
	 * Import schemas
	 *
	 * @param $plugin
	 *
	 * @return false|mixed
	 */
	public function importJsonld( $plugin ) {
		if ( isset( $this->_plugins[ $plugin ] ) ) {
			if ( is_callable( array( $this, $this->_plugins[ $plugin ]['function'] ) ) ) {
				return call_user_func( array( $this, $this->_plugins[ $plugin ]['function'] ) );
			}
		}

		return false;
	}

	/**
	 * Import from Rank Math
	 *
	 * @return int|void
	 */
	public function importRankMath() {
		global $wpdb;
		$imported = 0;

		$table_name = 'postmeta';

		//replace patterns
		$replace = array(
			'%seo_title%'               => '{{title}}',
			'%seo_description%'         => '{{description}}',
			'%date(Y-m-d\TH:i:sP)%'     => '{{date}}',
			'%date(Y-m-d)%'             => '{{date}}',
			'%modified(Y-m-d\TH:i:sP)%' => '{{modified}}',
			'%name%'                    => '{{name}}',
			'%post_thumbnail%'          => '{{image}}',
			'%org_name%'                => '{{org_name}}',
			'%org_url%'                 => '{{org_url}}',
			'%org_logo%'                => '{{org_logo}}',
		);

		/** @var SQP_Models_Jsonld_Database $database */
		$database = SQP_Classes_ObjController::getClass( 'SQP_Models_Jsonld_Database' );
		/** @var SQP_Models_Jsonld_Sanitize $sanitize */
		$sanitize = SQP_Classes_ObjController::getClass( 'SQP_Models_Jsonld_Sanitize' );

		$wpdb->hide_errors();
		$query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->prefix . $table_name );

		if ( $wpdb->get_var( $query ) === $wpdb->prefix . $table_name ) {

			if ( $rows = $wpdb->get_results( $wpdb->prepare( "SELECT post_id, meta_key as jsonld_type,meta_value as data FROM `{$wpdb->prefix}{$table_name}` WHERE `meta_key` LIKE %s ", 'rank_math_schema_%' ), ARRAY_A ) ) {
				foreach ( $rows as $row ) {

					$schema_array = unserialize( $row['data'] );

					if ( is_array( $schema_array ) && ! empty( $schema_array ) ) {
						$post = get_post( $row['post_id'] );

						if ( $post ) {
							$data = array();

							$schema_array = $sanitize->arrayChangeKey( array( '@type' => 'type' ), $schema_array );
							$schema_array = $sanitize->arrayChangeValue( $replace, $schema_array );

							if ( isset( $schema_array['review'] ) ) {
								if ( count( array_filter( array_keys( $schema_array['review'] ), 'is_string' ) ) ) {
									$schema_array['review'] = array( $schema_array['review'] );
								}
							}
							if ( isset( $schema_array['hasPart'] ) ) {
								if ( ! count( array_filter( array_keys( $schema_array['hasPart'] ), 'is_string' ) ) ) {
									$schema_array['hasPart'] = current( $schema_array['hasPart'] );
								}
							}

							$data['post_id']     = $post->ID;
							$data['post_type']   = $post->post_type;
							$data['jsonld_type'] = $schema_array['type'];
							$data['schema']      = $schema_array;

							$data = $database->sanitizeData( $data );

							/** @var SQP_Models_Domain_Jsonld $item */
							$item = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Jsonld', $data );

							if(class_exists('SQ_Classes_ObjController')) {
								/** @var @var SQ_Models_Snippet $snippet $snippet */
								$snippet = SQ_Classes_ObjController::getClass( 'SQ_Models_Snippet' );

								/** @var SQ_Models_Qss $qss */
								$qss = SQ_Classes_ObjController::getClass( 'SQ_Models_Qss' );


								$response = $database->addRow( $item, $data );

								if ( ! is_wp_error( $response ) ) {

									if ( $post = $snippet->getCurrentSnippet( $post->ID ) ) {

										//check the optimizations and save them locally
										add_filter( 'sq_seo_before_update', function( $sq ) use ( $item ) {
											$jsonld_types     = (array) $sq->jsonld_types;
											$jsonld_types[]   = $item->jsonld_type;
											$sq->jsonld_types = array_unique( $jsonld_types );

											return $sq;
										}, 11, 1 );

										$qss->updateSqSeo( $post );

									}

									$imported ++;
								}
							}
						}

					}


				}
			}
		}

		return $imported;
	}


}
