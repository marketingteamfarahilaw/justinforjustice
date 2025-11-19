<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Controllers_Redirects extends SQP_Classes_FrontController {

	/**
	 * @var int $max_num_pages Total number of results
	 */
	public $max_num_pages = 0;
	/**
	 * @var array Redirect rules from database
	 */
	public $rewrite_rules = array();
	/**
	 * @var array $rewrite_log The lost of log
	 */
	public $rewrite_log = array();

	function init() {

		$tab = preg_replace( "/[^a-zA-Z0-9]/", "", SQP_Classes_Helpers_Tools::getValue( 'tab', 'rules' ) );

		//Create Redirects table if not exists
		$this->checkTables();

		if ( $tab && method_exists( $this, $tab ) ) {
			call_user_func( array( $this, $tab ) );
		}

		//Load view
		$this->show_view( 'Redirects/' . esc_attr( ucfirst( $tab ) ) );

	}

	public function rules() {

		$page   = SQP_Classes_Helpers_Tools::getValue( 'spage', 1 );
		$type   = SQP_Classes_Helpers_Tools::getValue( 'stype' );
		$search = SQP_Classes_Helpers_Tools::getValue( 'squery' );
		$num    = SQP_Classes_Helpers_Tools::getValue( 'snum', SQP_Classes_Helpers_Tools::getOption( 'sq_posts_per_page' ) );
		$sort   = SQP_Classes_Helpers_Tools::getValue( 'ssort' );
		$order  = SQP_Classes_Helpers_Tools::getValue( 'sorder' );

		/**
		 * @var SQP_Models_Redirects_Admin $database
		 */
		$database = SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_Admin' );

		$group_ids = $this->getGroupIds();

		$args = array(
			'start'  => ( $page - 1 ) * $num,
			'num'    => $num,
			'sort'   => $sort,
			'order'  => $order,
			'search' => $search,
		);

		if ( $type ) {
			$group_id         = isset( $group_ids[ $type ] ) ? $group_ids[ $type ] : $group_ids['url']; //set the group redirection id
			$args['group_id'] = $group_id;
		}

		$this->rewrite_rules = $database->getRedirectRows( $args );

		if ( $database->getCount() > 0 ) {
			$this->max_num_pages = ceil( $database->getCount() / $num );
		}

	}

	public function log() {
		$page      = SQP_Classes_Helpers_Tools::getValue( 'spage', 1 );
		$search    = SQP_Classes_Helpers_Tools::getValue( 'squery', '' );
		$http_code = SQP_Classes_Helpers_Tools::getValue( 'scode' );
		$num       = SQP_Classes_Helpers_Tools::getValue( 'snum', SQP_Classes_Helpers_Tools::getOption( 'sq_posts_per_page' ) );

		/**
		 * @var SQP_Models_Redirects_Log $log
		 */
		$log = SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_Log' );


		$args = array(
			'start'     => ( $page - 1 ) * $num,
			'num'       => $num,
			'search'    => $search,
			'http_code' => $http_code,
		);

		$this->rewrite_log = $log->getLogRows( $args );

		if ( $log->getCount() > 0 ) {
			$this->max_num_pages = ceil( $log->getCount() / $num );
		}

		if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
			add_action( 'sq_notices', function() {
				SQP_Classes_Error::showError( esc_html__( 'Cron is disabled in config with DISABLE_WP_CRON declaration. Make sure the cron is working and the log is cleared.', 'squirrly-seo-pack' ) );
			} );
		}
	}

	/**
	 * Check if the required tables exist
	 *
	 * @return void
	 */
	public function checkTables() {

		//Create Qss table if not exists
		if ( SQP_Classes_Helpers_Tools::getOption( 'sq_redirects' ) ) {
			SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_Admin' )->checkTablesExist();
		}

		//Check the log option to create or delete the table
		if ( SQP_Classes_Helpers_Tools::getOption( 'sq_redirects_log_hits' ) || SQP_Classes_Helpers_Tools::getOption( 'sq_redirects_log_404' ) ) {
			SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_Log' )->checkTablesExist();
		} else {
			SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_Log' )->deleteTable();
		}

	}

	/**
	 * Get the available redirect codes
	 *
	 * @return mixed|null
	 */
	public function getRedirectCodes() {

		$redirect_codes = array(
			'301' => esc_attr__( '301 - Moved Permanently', 'squirrly-seo-pack' ),
			'302' => esc_attr__( '302 - Found', 'squirrly-seo-pack' ),
			'307' => esc_attr__( '307 - Temporary Redirect', 'squirrly-seo-pack' ),
			'308' => esc_attr__( '308 - Permanent Redirect', 'squirrly-seo-pack' ),
			'404' => esc_attr__( '404 - Page Not Found', 'squirrly-seo-pack' ),
		);

		return apply_filters( 'sqp_redirect_codes', $redirect_codes );

	}

	/**
	 * Get the available match types
	 *
	 * @return void
	 */
	public function getMatchTypes() {

		$match_types = array(
			'exact'  => esc_html__( "Exact URL match", 'squirrly-seo-pack' ),
			'ignore' => esc_html__( "Ignore all query parameters", 'squirrly-seo-pack' ),
			'pass'   => esc_html__( "Ignore and pass all query parameters", 'squirrly-seo-pack' )
		);

		return apply_filters( 'sqp_match_types', $match_types );

	}

	/**
	 * Get the Redirect Types
	 *
	 * @return array
	 */
	public function getRedirectTypes() {

		$redirect_types = array(
			'url'  => esc_attr__( 'Custom Redirects', 'squirrly-seo-pack' ),
			'post' => esc_attr__( 'Post Redirects', 'squirrly-seo-pack' ),
			'slug' => esc_attr__( 'Slug Changed Redirects', 'squirrly-seo-pack' ),
		);

		return apply_filters( 'sqp_match_types', $redirect_types );

	}

	/**
	 * Get the Group IDs for Redirects
	 * 1 - custom url redirect
	 * 2 - post redirect
	 * 3 - slug change redirect
	 *
	 * @param string $group
	 *
	 * @return mixed|null
	 */
	public function getGroupIds( $group = false ) {

		$group_ids = array(
			'url'  => 1, //custom URL redirect
			'post' => 2, //post redirect
			'slug' => 3, // slug rename with redirect
		);

		$group_ids = apply_filters( 'sqp_group_ids', $group_ids );

		if ( $group ) {
			if ( isset( $group_ids[ $group ] ) ) {
				return $group_ids[ $group ];
			}
		} else {
			return $group_ids;
		}

		return false;

	}

	/**
	 * Hook the plugins for the import option
	 *
	 * @return void
	 */
	public function import() {
		add_filter( 'sq_redirects_import', array(
			SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_Import' ),
			'getAllPlugins'
		) );
	}

	/**
	 * Called when Post action is triggered
	 *
	 * @return void
	 */
	public function action() {

		parent::action();
		switch ( SQP_Classes_Helpers_Tools::getValue( 'action' ) ) {

			case 'sqp_redirects_update':

				if ( ! SQP_Classes_Helpers_Tools::userCan( 'sq_manage_settings' ) ) {
					return;
				}

				/**
				 * @var SQP_Models_Redirects_Admin $data
				 */
				$data = SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_Admin' )->sanitizeData( $_POST );

				if ( $data ) {
					$item = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Redirect', $data );
					SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_Admin' )->addRedirectRow( $item );
				} else {
					SQP_Classes_Error::setError( esc_html__( 'Invalid data', 'squirrly-seo-pack' ) );
				}


				break;

			case 'sqp_redirects_delete':

				if ( ! SQP_Classes_Helpers_Tools::userCan( 'sq_manage_settings' ) ) {
					return;
				}

				$id = SQP_Classes_Helpers_Tools::getValue( 'id' );

				SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_Admin' )->deleteRedirectRow( $id );

				break;

			case 'sqp_redirects_enable':

				if ( ! SQP_Classes_Helpers_Tools::userCan( 'sq_manage_settings' ) ) {
					return;
				}

				$id = SQP_Classes_Helpers_Tools::getValue( 'id' );

				SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_Admin' )->enableRedirectRow( $id );

				break;

			case 'sqp_redirects_disable':

				if ( ! SQP_Classes_Helpers_Tools::userCan( 'sq_manage_settings' ) ) {
					return;
				}

				$id = SQP_Classes_Helpers_Tools::getValue( 'id' );

				SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_Admin' )->disableRedirectRow( $id );

				break;

			case 'sqp_log_delete':

				if ( ! SQP_Classes_Helpers_Tools::userCan( 'sq_manage_settings' ) ) {
					return;
				}

				//delete the redirects log
				SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_Log' )->deleteLogAll();

				break;

			case 'sq_ajax_rules_bulk_delete':

				if ( ! SQP_Classes_Helpers_Tools::userCan( 'sq_manage_settings' ) ) {
					wp_send_json_error( esc_html__( "You do not have permission to perform this action!", 'squirrly-seo-pack' ) );
				}

				$ids = SQP_Classes_Helpers_Tools::getValue( 'inputs', array() );

				if ( ! empty( $ids ) ) {
					foreach ( $ids as $id ) {
						SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_Admin' )->deleteRedirectRow( $id );
					}

					wp_send_json_success( esc_html__( "Saved!", 'squirrly-seo-pack' ) );
				} else {
					wp_send_json_error( esc_html__( "Invalid Rule!", 'squirrly-seo-pack' ) );
				}

				exit();

			case 'sqp_redirects_settings_update':

				if ( ! SQP_Classes_Helpers_Tools::userCan( 'sq_manage_settings' ) ) {
					return;
				}

				//Sanitize and Save values
				$this->saveValues( $_POST );

				break;

			case 'sqp_redirects_export':

				if ( ! SQP_Classes_Helpers_Tools::userCan( 'sq_manage_settings' ) ) {
					return;
				}

				/**
				 * @var SQP_Models_Redirects_Admin $database
				 */
				$database = SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_Admin' );
				$stype    = SQP_Classes_Helpers_Tools::getValue( 'stype' );
				$csv      = '';

				$args = array(
					'start' => 0,
					'num'   => 10000,
				);

				$group_ids = $this->getGroupIds();

				if ( $stype && isset( $group_ids[ $stype ] ) ) {
					$group_id         = $group_ids[ $stype ]; //set the group redirection id
					$args['group_id'] = $group_id;
				}

				$rewrite_rules = $database->getRedirectRows( $args );

				if ( ! empty( $rewrite_rules ) ) {
					$table_head = array(
						'source_url',
						'target_url',
						'action_code',
						'ignore_case',
						'ignore_slash',
						'match'
					);

					foreach ( $rewrite_rules as $row ) {

						/** @var SQP_Models_Redirects_Flags $flags */
						$flags = $row->getSource_flags()->getJson();

						$table_body[] = array(
							$row->url,
							$row->action_data,
							$row->action_code,
							(int) $flags['flag_case'],
							(int) $flags['flag_trailing'],
							$flags['flag_query']
						);
					}


					$csv = join( ',', $table_head );
					$csv .= "\n"; // important! Make sure to use double quotation marks.

					foreach ( $table_body as $row ) {
						$csv .= join( ',', $row );
						$csv .= "\n";
					}
				}

				$filename = 'sq_redirects.csv';

				header( 'Content-Type: text/csv' ); // tells browser to download
				header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
				header( 'Pragma: no-cache' ); // no cache
				header( "Expires: Sat, 26 Jul 1997 05:00:00 GMT" ); // expire date

				echo $csv;
				exit();

			case 'sqp_settings_update':

				if ( ! SQP_Classes_Helpers_Tools::userCan( 'sq_manage_settings' ) ) {
					return;
				}

				$settings = SQP_Classes_Helpers_Tools::getOption( 'sq_redirect_flags' );

				if ( SQP_Classes_Helpers_Tools::getIsset( 'flag_query' ) ) {
					$settings['flag_query'] = SQP_Classes_Helpers_Tools::getValue( 'flag_query' );
				}
				if ( SQP_Classes_Helpers_Tools::getIsset( 'flag_case' ) ) {
					$settings['flag_case'] = SQP_Classes_Helpers_Tools::getValue( 'flag_case' );
				}
				if ( SQP_Classes_Helpers_Tools::getIsset( 'flag_trailing' ) ) {
					$settings['flag_trailing'] = SQP_Classes_Helpers_Tools::getValue( 'flag_trailing' );
				}

				SQP_Classes_Helpers_Tools::saveOptions( 'sq_redirect_flags', $settings );

				$patterns = SQP_Classes_Helpers_Tools::getOption( 'patterns' );
				if ( ! empty( $patterns ) ) {
					if ( SQP_Classes_Helpers_Tools::getIsset( 'sq_post_type_redirects' ) ) {
						$sq_post_type_redirects = SQP_Classes_Helpers_Tools::getValue( 'sq_post_type_redirects' );
						$types                  = get_post_types( array( 'public' => true ) );

						if ( $sq_post_type_redirects ) {
							foreach ( $types as $type ) {

								if ( ! in_array( $type, array_keys( $patterns ) ) ) {
									continue;
								}

								$patterns[ $type ]['do_redirects'] = in_array( $type, $sq_post_type_redirects );

							}
						}
					}

					if ( SQP_Classes_Helpers_Tools::getIsset( 'sq_404_redirects' ) ) {
						$sq_404_redirects              = SQP_Classes_Helpers_Tools::getValue( 'sq_404_redirects' );
						$patterns[404]['do_redirects'] = $sq_404_redirects;
					}

					SQP_Classes_Helpers_Tools::saveOptions( 'patterns', $patterns );
				}

				//Sanitize and Save values
				$this->saveValues( $_POST );

				//trigger the save settings and set the transient for rewrite flush
				do_action( 'sqp_save_settings_after' );
				set_transient( 'sqp_flush_rules', true );

				SQP_Classes_Error::setMessage( esc_html__( 'Saved!', 'squirrly-seo-pack' ) );
				break;

			case 'sq_redirects_import':
				if ( ! SQP_Classes_Helpers_Tools::userCan( 'sq_manage_settings' ) ) {
					return;
				}

				$import   = false;
				$platform = SQP_Classes_Helpers_Tools::getValue( 'sq_import_platform' );

				//Import the redirects from the selected plugin
				if ( $platform ) {
					$import = SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_Import' )->importRedirects( $platform );
				}

				if ( $import ) {
					SQP_Classes_Error::setMessage( sprintf( esc_html__( 'Successfully imported %d redirect(s)!', 'squirrly-seo-pack' ), $import ) );
				} else {
					SQP_Classes_Error::setError( esc_html__( 'No redirects found to import.', 'squirrly-seo-pack' ) );
				}

				break;

			case 'sq_redirects_backup':

				if ( ! SQP_Classes_Helpers_Tools::userCan( 'sq_manage_settings' ) ) {
					return;
				}

				header( 'Content-Type: application/octet-stream' );
				header( "Content-Transfer-Encoding: Binary" );
				header( "Content-Disposition: attachment; filename=squirrly-redirects-" . gmdate( 'Y-m-d' ) . ".sql" );

				/**
				 * @var SQP_Models_Redirects_Admin $data
				 */
				echo base64_encode( SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_Admin' )->createTableBackup() );

				exit();

			case 'sq_redirects_restore':

				if ( ! SQP_Classes_Helpers_Tools::userCan( 'sq_manage_settings' ) ) {
					return;
				}

				if ( ! empty( $_FILES['sq_redirects'] ) && $_FILES['sq_redirects']['tmp_name'] <> '' ) {
					$fp       = fopen( $_FILES['sq_redirects']['tmp_name'], 'rb' );
					$sql_file = '';
					while ( ( $line = fgets( $fp ) ) !== false ) {
						$sql_file .= $line;
					}

					if ( function_exists( 'base64_encode' ) ) {
						$sql_file = @base64_decode( $sql_file );
					}

					if ( $sql_file <> '' && strpos( $sql_file, 'INSERT INTO' ) !== false ) {
						try {

							$queries = explode( "INSERT INTO", $sql_file );
							SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_Admin' )->restoreTableBackup( $queries );
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
		}
	}

	/**
	 * Save values in db options
	 * @param $params
	 *
	 * @return void
	 */
	private function saveValues( $params ) {

		if ( ! empty( $params ) ) {
			foreach ( $params as $key => $value ) {

				if ( isset( SQP_Classes_Helpers_Tools::$options[ $key ] ) ) {
					SQP_Classes_Helpers_Tools::saveOptions( $key, SQP_Classes_Helpers_Tools::getValue( $key ) );
				}
			}
		}
	}


}