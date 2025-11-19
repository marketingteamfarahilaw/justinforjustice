<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Redirects_Import {
	private $_plugins;

	public function __construct() {
		$this->_plugins = array(
			'eps-301-redirects'              => array(
				'title'    => esc_html__( 'Eps 301 Redirects', 'squirrly-seo-pack' ),
				'function' => 'importEpsRedirects',
			),
			'all-in-one-seo-pack'            => array(
				'title'    => esc_html__( 'All In One SEO', 'squirrly-seo-pack' ),
				'function' => 'importAllInOneSEO',
			),
			'redirection'                    => array(
				'title'    => esc_html__( 'Redirection', 'squirrly-seo-pack' ),
				'function' => 'importRedirection',
			),
			'seo-by-rank-math'               => array(
				'title'    => esc_html__( 'Rank Math', 'squirrly-seo-pack' ),
				'function' => 'importRankMath',
			),
			'quick-pagepost-redirect-plugin' => array(
				'title'    => esc_html__( 'Quick Redirects', 'squirrly-seo-pack' ),
				'function' => 'importQuickRedirects',
			),
			'squirrly-seo'                   => array(
				'title'    => esc_html__( 'Squirrly SEO', 'squirrly-seo-pack' ),
				'function' => 'importSquirrly',
			),
			'autodescription'                => array(
				'title'    => esc_html__( 'SEO Framework', 'squirrly-seo-pack' ),
				'function' => 'importSeoFramework',
			),

			'wp-seopress'   => array(
				'title'    => esc_html__( 'SEO Press', 'squirrly-seo-pack' ),
				'function' => 'importSeoPress',
			),
			'wordpress-seo' => array(
				'title'    => esc_html__( 'Yoast SEO', 'squirrly-seo-pack' ),
				'function' => 'importYoast',
			),
			'wordpress'     => array(
				'title'    => esc_html__( 'WordPress Core (Slug Changes)', 'squirrly-seo-pack' ),
				'function' => 'importWordPressOldSlugs',
			),

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
	 * Import the redirect
	 *
	 * @param $plugin
	 *
	 * @return false|mixed
	 */
	public function importRedirects( $plugin ) {
		if ( isset( $this->_plugins[ $plugin ] ) ) {
			if ( is_callable( array( $this, $this->_plugins[ $plugin ]['function'] ) ) ) {
				return call_user_func( array( $this, $this->_plugins[ $plugin ]['function'] ) );
			}
		}

		return false;
	}

	/**
	 * Import from Redirections
	 *
	 * @return int|void
	 */
	public function importRedirection() {
		global $wpdb;
		$imported = 0;

		$table_name = 'redirection_items';

		/** @var SQP_Models_Redirects_Admin $database */
		$database = SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_Admin' );

		$wpdb->hide_errors();
		$query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->prefix . $table_name );

		if ( $wpdb->get_var( $query ) === $wpdb->prefix . $table_name ) {
			if ( $rows = $wpdb->get_results( $wpdb->prepare( "SELECT url,match_url,match_data,regex,status,action_code,action_data,match_type,title FROM `{$wpdb->prefix}{$table_name}` WHERE `status` = %s AND `match_type` = %s AND (`action_type` = %s OR `action_type` = %s ) ", 'enabled', 'url', 'url', 'error' ), ARRAY_A ) ) {

				foreach ( $rows as $row ) {

					/** @var SQP_Models_Domain_Redirect $item */
					$item = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Redirect', $database->sanitizeData( $row ) );

					//add the redirect in database
					$redirect = $database->addRedirectRow( $item );

					if ( ! is_wp_error( $redirect ) ) {
						$imported ++;
					}
				}
			}
		}

		return $imported;
	}

	/**
	 * Import from Seopress
	 *
	 * @return int
	 */
	public function importSeoPress() {

		$meta_keys = array(
			'action_data' => '_seopress_redirections_value',
			'action_code' => '_seopress_redirections_type',
		);

		return $this->importPostMetas( $meta_keys );

	}

	/**
	 * Import from SeoFramework
	 *
	 * @return int
	 */
	public function importSeoFramework() {

		$meta_keys = array(
			'action_data' => 'redirect',
		);

		return $this->importPostMetas( $meta_keys );
	}

	/**
	 * Import from Rank Math
	 *
	 * @return int|void
	 */
	public function importRankMath() {
		global $wpdb;
		$imported = 0;

		$table_name = 'rank_math_redirections';

		/** @var SQP_Models_Redirects_Admin $database */
		$database = SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_Admin' );

		$wpdb->hide_errors();
		$query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->prefix . $table_name );

		if ( $wpdb->get_var( $query ) === $wpdb->prefix . $table_name ) {
			if ( $rows = $wpdb->get_results( $wpdb->prepare( "SELECT sources,`url_to` as action_data, `header_code` as action_code FROM `{$wpdb->prefix}{$table_name}` WHERE `status` = %s ", 'active' ), ARRAY_A ) ) {

				foreach ( $rows as $row ) {

					$sources  = unserialize( $row['sources'] );
					$settings = SQP_Classes_Helpers_Tools::getOption( 'sq_redirect_flags' );

					if ( is_array( $sources ) && ! empty( $sources ) ) {
						foreach ( $sources as $source ) {

							if ( ! isset( $source['ignore'] ) ) {
								$source['ignore'] = false;
							}

							if ( isset( $source['comparison'] ) ) {
								switch ( $source['comparison'] ) {
									case 'contains':
										$row['url']           = '(.*)' . trim( $source['pattern'], '/' ) . '(.*)';
										$row['flag_regex']    = true;
										$row['flag_query']    = $settings['flag_query'];
										$row['flag_case']     = ( $source['ignore'] === 'case' );
										$row['flag_trailing'] = true;
										break;
									case 'start':
										$row['url']           = '^' . trim( $source['pattern'], '/' );
										$row['flag_regex']    = true;
										$row['flag_query']    = $settings['flag_query'];
										$row['flag_case']     = ( $source['ignore'] === 'case' );
										$row['flag_trailing'] = true;
										break;
									case 'end':
										$row['url']           = trim( $source['pattern'], '/' ) . '$';
										$row['flag_regex']    = true;
										$row['flag_query']    = $settings['flag_query'];
										$row['flag_case']     = ( $source['ignore'] === 'case' );
										$row['flag_trailing'] = true;
										break;
									case 'exact':
										$row['url']           = '/' . trim( $source['pattern'], '/' ) . '/';
										$row['flag_regex']    = false;
										$row['flag_query']    = 'exact';
										$row['flag_case']     = ( $source['ignore'] === 'case' );
										$row['flag_trailing'] = true;
										break;
									case 'regex':
										$row['url']        = $source['pattern'];
										$row['flag_regex'] = true;
										$row['flag_query'] = 'regex';
										$row['flag_case']  = ( $source['ignore'] === 'case' );
										break;

								}
							}

							/** @var SQP_Models_Domain_Redirect $item */
							$item = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Redirect', $database->sanitizeData( $row ) );

							//add the redirect in database
							$redirect = $database->addRedirectRow( $item );

							if ( ! is_wp_error( $redirect ) ) {
								$imported ++;
							}
						}
					}


				}
			}
		}

		return $imported;
	}


	/**
	 * Import from Yoast
	 *
	 * @return false|int
	 */
	public function importYoast() {

		$meta_keys = array(
			'action_data' => '_yoast_wpseo_redirect',
		);

		$imported = $this->importPostMetas( $meta_keys );

		$this->importYoastPRO();

		return $imported;
	}

	public function importYoastPRO() {
		$imported = 0;

		// Import the premium redirects
		if ( ! $redirections = get_option( 'wpseo-premium-redirects-base' ) ) {
			return $imported;
		}

		/** @var SQP_Models_Redirects_Admin $database */
		$database = SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_Admin' );

		foreach ( $redirections as $redirection ) {

			if ( ! isset( $redirection['origin'] ) || empty( $redirection['origin'] ) ) {
				return false;
			}

			if ( strpos( $redirection['origin'], home_url() ) !== false ) {
				$redirection['origin'] = wp_parse_url( $redirection['origin'], PHP_URL_PATH );
			}

			$data                = array();
			$data['url']         = $redirection['origin'];
			$data['action_data'] = isset( $redirection['url'] ) ? $redirection['url'] : '';
			$data['action_code'] = isset( $redirection['type'] ) ? $redirection['type'] : '301';
			$data['flag_query']  = isset( $redirection['format'] ) && 'regex' === $redirection['format'] ? 'regex' : 'exact';

			/** @var SQP_Models_Domain_Redirect $item */
			$item = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Redirect', $database->sanitizeData( $data ) );

			//add the redirect in database
			$redirect = $database->addRedirectRow( $item );

			if ( ! is_wp_error( $redirect ) ) {
				$imported ++;
			}
		}

		return $imported;
	}

	/**
	 * impirt from Squirrly
	 *
	 * @return int count imports
	 */
	public function importSquirrly() {

		$imported = 0;

		//Import the old slug redirects from Squirrly
		$imported += $this->importSquirrlyOldSlugs();

		//Import the snippet redirects from Squirrly
		$imported += $this->importSquirrlySnippetRedirects();

		return $imported;
	}

	/**
	 * Import the old slug redirects from Squirrly
	 *
	 * @return int
	 */
	public function importWordPressOldSlugs() {

		$meta_keys = array(
			'url' => '_wp_old_slug'
		);

		return $this->importPostMetas( $meta_keys, 'slug' );
	}

	/**
	 * Import the old slug redirects from Squirrly
	 *
	 * @return int
	 */
	public function importSquirrlyOldSlugs() {

		$meta_keys = array(
			'url' => '_sq_old_slug'
		);

		return $this->importPostMetas( $meta_keys, 'slug' );

	}

	/**
	 * Import the snippet redirects from Squirrly
	 *
	 * @return int
	 */
	public function importSquirrlySnippetRedirects() {
		global $wpdb;
		$table_name = 'qss';
		$imported   = 0;

		/** @var SQP_Controllers_Redirects $redirects */
		$redirects = SQP_Classes_ObjController::getClass( 'SQP_Controllers_Redirects' );
		$group_id  = $redirects->getGroupIds( 'post' ); //post redirect

		if ( $rows = $wpdb->get_results( $wpdb->prepare( "SELECT seo, post, URL as url FROM `{$wpdb->prefix}{$table_name}` WHERE `blog_id` = %d ", get_current_blog_id() ) ) ) {
			foreach ( $rows as $row ) {
				$seo = unserialize( $row->seo );
				if ( $seo['redirect'] <> '' ) {
					//Identify the post
					$post = unserialize( $row->post );

					$data                   = array();
					$data['flag_post']      = (int) $post['ID'];
					$data['flag_post_type'] = $post['post_type'];
					$data['flag_term']      = (int) $post['term_id'];
					$data['flag_taxonomy']  = $post['taxonomy'];
					$data['group_id']       = $group_id; //post redirect

					if ( class_exists( 'SQ_Classes_ObjController' ) ) {
						/**
						 * @var SQ_Models_Snippet $snippet
						 */
						$snippet = SQ_Classes_ObjController::getClass( 'SQ_Models_Snippet' );
						if ( method_exists( $snippet, 'getCurrentSnippet' ) ) {

							if ( $page = $snippet->getCurrentSnippet( $post['ID'], $post['term_id'], $post['taxonomy'], $post['post_type'] ) ) {
								//add the post if exists
								$data['url']         = rtrim( $page->url, '/' );
								$data['action_data'] = $seo['redirect'];

								/** @var SQP_Models_Redirects_Admin $database */
								$database = SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_Admin' );

								/** @var SQP_Models_Domain_Redirect $item */
								$item = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Redirect', $database->sanitizeData( $data ) );

								//add the redirect in database
								$redirect = $database->addRedirectRow( $item );

								if ( ! is_wp_error( $redirect ) ) {
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

	/**
	 * Import All In One SEO Redirects
	 *
	 * @return int
	 */
	public function importAllInOneSEO() {
		global $wpdb;
		$table_name = 'aioseo_redirects';
		$imported   = 0;

		if ( $rows = $wpdb->get_results( $wpdb->prepare( "SELECT `source_url` as url, `target_url` as action_data,`type` as action_code, `ignore_case` as flag_case, `source_url_match` as flag_query FROM `{$wpdb->prefix}{$table_name}` WHERE `enabled` = %s", 'active' ), ARRAY_A ) ) {
			foreach ( $rows as $data ) {
				$redirect_codes = SQP_Classes_ObjController::getClass( 'SQP_Controllers_Redirects' )->getRedirectCodes();

				if ( strpos( $data['url'], home_url() ) !== false ) {
					$data['url'] = wp_parse_url( $data['url'], PHP_URL_PATH );
				}

				$data['flag_query']  = isset( $data['flag_query'] ) && 'regex' === $data['flag_query'] ? 'regex' : 'exact';
				$data['action_code'] = in_array( $data['action_code'], array_keys( $redirect_codes ) ) ? $data['action_code'] : '301';

				/** @var SQP_Models_Redirects_Admin $database */
				$database = SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_Admin' );

				/** @var SQP_Models_Domain_Redirect $item */
				$item = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Redirect', $database->sanitizeData( $data ) );

				//add the redirect in database
				$redirect = $database->addRedirectRow( $item );

				if ( ! is_wp_error( $redirect ) ) {
					$imported ++;
				}
			}
		}

		return $imported;
	}

	/**
	 * Import from Quick Redirects
	 *
	 * @return int
	 */
	public function importQuickRedirects() {
		$imported = 0;

		// Import the premium redirects
		if ( ! $redirections = get_option( 'quickppr_redirects' ) ) {
			return $imported;
		}

		/** @var SQP_Models_Redirects_Admin $database */
		$database = SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_Admin' );

		foreach ( $redirections as $source_url => $action_data ) {

			if ( strpos( $source_url, home_url() ) !== false ) {
				$source_url = wp_parse_url( $source_url, PHP_URL_PATH );
			}

			$data                = array();
			$data['url']         = $source_url;
			$data['action_data'] = $action_data;
			$data['action_code'] = 301;
			$data['flag_query']  = 'exact';

			/** @var SQP_Models_Domain_Redirect $item */
			$item = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Redirect', $database->sanitizeData( $data ) );

			//add the redirect in database
			$redirect = $database->addRedirectRow( $item );

			if ( ! is_wp_error( $redirect ) ) {
				$imported ++;
			}
		}

		return $imported;
	}

	/**
	 * Import from 301 Redirects
	 *
	 * @return int
	 */
	public function import301Redirects() {
		global $wpdb;
		$table_name = 'ts_redirects';
		$imported   = 0;

		if ( $rows = $wpdb->get_results( "SELECT `old_link` as url, `new_link` as action_data FROM `{$wpdb->prefix}{$table_name}`", ARRAY_A ) ) {
			foreach ( $rows as $data ) {

				/** @var SQP_Models_Redirects_Admin $database */
				$database = SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_Admin' );

				/** @var SQP_Models_Domain_Redirect $item */
				$item = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Redirect', $database->sanitizeData( $data ) );

				//add the redirect in database
				$redirect = $database->addRedirectRow( $item );

				if ( ! is_wp_error( $redirect ) ) {
					$imported ++;
				}
			}
		}

		return $imported;
	}

	/**
	 * Import from EPS 301 Redirects
	 *
	 * @return int
	 */
	public function importEpsRedirects() {
		global $wpdb;
		$table_name = 'redirects';
		$imported   = 0;

		/** @var SQP_Controllers_Redirects $redirects */
		$redirects = SQP_Classes_ObjController::getClass( 'SQP_Controllers_Redirects' );

		if ( $rows = $wpdb->get_results( "SELECT `url_from` as url, `url_to` as action_data,`status` as action_code, `type` FROM `{$wpdb->prefix}{$table_name}`", ARRAY_A ) ) {
			foreach ( $rows as $data ) {
				$redirect_codes = SQP_Classes_ObjController::getClass( 'SQP_Controllers_Redirects' )->getRedirectCodes();

				if ( $data['type'] == 'post' ) {

					$post_id = (int) $data['action_data'];

					if ( $post_id == 0 ) {
						continue;
					}

					//check if the permalink source is valid
					$source_url = get_permalink( $post_id );

					if ( is_wp_error( $source_url ) ) { // phpcs:ignore
						continue;
					}

					$data['action_data'] = wp_parse_url( $source_url, PHP_URL_PATH );

					$data['flag_post']      = $post_id;
					$data['flag_post_type'] = get_post_type( $post_id );
					$data['flag_term']      = 0;
					$data['flag_taxonomy']  = '';
					$data['group_id']       = $redirects->getGroupIds( 'post' );
				}

				$data['action_code'] = in_array( $data['action_code'], array_keys( $redirect_codes ) ) ? $data['action_code'] : '301';

				/** @var SQP_Models_Redirects_Admin $database */
				$database = SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_Admin' );

				/** @var SQP_Models_Domain_Redirect $item */
				$item = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Redirect', $database->sanitizeData( $data ) );

				//add the redirect in database
				$redirect = $database->addRedirectRow( $item );

				if ( ! is_wp_error( $redirect ) ) {
					$imported ++;
				}
			}
		}

		return $imported;
	}

	/**
	 * Import from Post Meta table based on the meta keys
	 *
	 * @param array $meta_keys
	 * @param string $redirect_type
	 *
	 * @return int
	 */
	public function importPostMetas( $meta_keys, $redirect_type = 'post' ) {
		global $wpdb;
		$metas    = array();
		$imported = 0;

		// Don't bother if it hasn't changed.
		$patterns = SQP_Classes_Helpers_Tools::getOption( 'patterns' );

		if ( ! $patterns || ! is_array( $patterns ) ) {
			return $imported;
		}

		/** @var SQP_Models_Redirects_Admin $database */
		$database = SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_Admin' );

		/** @var SQP_Controllers_Redirects $redirects */
		$redirects = SQP_Classes_ObjController::getClass( 'SQP_Controllers_Redirects' );
		$group_id  = $redirects->getGroupIds( $redirect_type ); //redirect type

		$placeholders = array_fill( 0, count( $meta_keys ), '%s' );
		$meta_keys    = array_flip( $meta_keys );

		$query = "SELECT * FROM `$wpdb->postmeta` WHERE meta_key IN (" . join( ",", $placeholders ) . ");";
		if ( $rows = $wpdb->get_results( $wpdb->prepare( $query, array_keys( $meta_keys ) ) ) ) {
			foreach ( $rows as $row ) {
				if ( isset( $meta_keys[ $row->meta_key ] ) && $row->meta_value <> '' ) {
					//check the post
					if ( $post = get_post( $row->post_id ) ) {
						//if the post exists
						if ( ! is_wp_error( $post ) && isset( $post->ID ) && $post->ID > 0 ) {
							//add all the meta values in array
							//used for old slug import
							$metas[ $post->ID ][ $meta_keys[ $row->meta_key ] ][] = $row->meta_value;
						}
					}

				}
			}
		}

		//if there is data to import
		if ( ! empty( $metas ) ) {
			//keep a deleted queue
			$deleted = array();

			//for every post id, get the data from postmeta
			foreach ( $metas as $post_id => $datas ) {

				$datas = $this->groupArrayKeys( $datas );

				foreach ( $datas as $data ) {
					if ( ! isset( $data['url'] ) ) {
						print_r( $data );
						exit();
					}

					//get the post type by post id
					$post_type = get_post_type( $post_id );

					if ( is_wp_error( $post_type ) ) { // phpcs:ignore
						continue;
					}

					//check if the post type has redirects option active
					if ( isset( $patterns[ $post_type ]['do_redirects'] ) && $patterns[ $post_type ]['do_redirects'] ) {
						//check if the permalink source is valid
						$source_url = get_permalink( $post_id );

						if ( is_wp_error( $source_url ) ) { // phpcs:ignore
							continue;
						}

						//Delete all slug redirects from the current post on this group
						if ( ! in_array( $post_id, $deleted ) ) {
							$database->deleteRedirects( array( 'group_id' => $group_id, 'flag_post' => $post_id ) );
							$deleted[] = $post_id;
						}

						$data['flag_post']      = $post_id;
						$data['flag_post_type'] = $post_type;
						$data['flag_term']      = 0;
						$data['flag_taxonomy']  = '';
						$data['group_id']       = $group_id;

						//add the redirect based on the metas (old slug redirects or post redirects)
						if ( $redirect_type == 'url' || $redirect_type == 'post' ) {
							$data['url'] = wp_parse_url( $source_url, PHP_URL_PATH );
						} else {
							$data['action_data'] = wp_parse_url( $source_url, PHP_URL_PATH );
						}

						/** @var SQP_Models_Domain_Redirect $item */
						$item = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Redirect', $database->sanitizeData( $data ) );

						//add the redirect in database
						$redirect = $database->addRedirectRow( $item );

						if ( ! is_wp_error( $redirect ) ) {
							$imported ++;
						}
					}
				}
			}
		}

		return $imported;
	}

	/**
	 * Group the array by keys and value index
	 *
	 * @param array $array
	 *
	 * @return array
	 */
	private function groupArrayKeys( $array ) {
		// Initialize output array
		$output_array = array();

		// Iterate through the sub-arrays
		foreach ( $array as $key => $sub_array ) {
			// Iterate through the values of the sub-array
			foreach ( $sub_array as $index => $value ) {
				// Add a new array to the output array if it doesn't exist yet
				if ( ! isset( $output_array[ $index ] ) ) {
					$output_array[ $index ] = array();
				}
				// Add the value to the output array at the current index
				$output_array[ $index ][ $key ] = $value;
			}
		}

		return $output_array;
	}
}
