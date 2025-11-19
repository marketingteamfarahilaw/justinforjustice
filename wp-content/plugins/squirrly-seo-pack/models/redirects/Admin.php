<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Redirects_Admin {

	/** @var int $count Count redirects */
	private $count;
	/** @var string $log_table log table */
	private $redirects_table = 'qss_redirects';

	public function __construct() {

		//hook and clear the redirect cache
		add_filter( 'sq_redirect_updated', array( $this, 'clearURLCache' ), 11, 2 );
	}

	/**
	 * Clear the URL cache on redirect update
	 *
	 * @param int $id
	 * @param SQP_Models_Domain_Redirect $redirect
	 *
	 * @return void
	 */
	public function clearURLCache( $id, $redirect ) {
		/**
		 * Check the cache
		 *
		 * @var SQP_Models_Redirects_UrlCache $cache
		 */
		$cache = SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_UrlCache' );
		$cache->clear( $redirect->match_url );
	}

	/**
	 * Get the rule rows from database
	 *
	 * @param int $start
	 * @param int $limit
	 * @param int $group_id
	 * @param int $search
	 * the group_id can be 1 or 2 based on the redirection group
	 * 1 = a normal URL redirect
	 * 2 = a post redirect
	 *
	 * @return array
	 */
	public function getRedirectRows( $args ) {

		extract( $args );

		global $wpdb;
		$items       = array();
		$query_where = array();
		$query_limit = '';

		$query_select = "SELECT * FROM {$wpdb->prefix}{$this->redirects_table}";
		$query_count  = "SELECT COUNT(id) FROM {$wpdb->prefix}{$this->redirects_table}";

		if ( isset( $sort ) && trim( $sort ) <> '' && isset( $order ) && trim( $order ) <> '' ) {
			$sort        = sanitize_text_field( $sort );
			$order       = sanitize_text_field( $order );
			$query_order = sprintf( "ORDER BY %s %s", $sort, $order );
		} else {
			$query_order = "ORDER BY position ASC";
		}

		if ( isset( $start ) && isset( $num ) ) {
			$query_limit = ' LIMIT ' . (int) $start . ', ' . (int) $num;
		}

		if ( isset( $search ) && trim( $search ) <> '' ) {
			$search        = sanitize_text_field( $search );
			$query_where[] = "(`match_url` like '%$search%' OR `action_data` like '%$search%')";
		} elseif ( isset( $group_id ) ) {
			$query_where[] = "`group_id` = " . (int) $group_id;
		}

		$query       = $query_count . ( ! empty( $query_where ) ? ' WHERE' . ' ' . join( ' AND ', $query_where ) : '' );
		$this->count = $wpdb->get_var( $query );

		$query = $query_select . ( ! empty( $query_where ) ? ' WHERE' . ' ' . join( ' AND ', $query_where ) : '' ) . ' ' . $query_order . ' ' . $query_limit;
		$rows  = $wpdb->get_results( $query );

		if ( is_array( $rows ) ) {

			foreach ( $rows as $row ) {
				$items[] = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Redirect', $row );
			}

		}

		return $items;
	}

	/**
	 * Get total results of the current query
	 *
	 * @return int
	 */
	public function getCount() {
		return $this->count;
	}

	/**
	 * Add/Update the redirect in database
	 *
	 * @param SQP_Models_Domain_Redirect $item
	 *
	 * @return SQP_Models_Domain_Redirect|WP_Error
	 */
	public function addRedirectRow( $item ) {
		global $wpdb;

		if ( ! $item instanceof SQP_Models_Domain_Redirect ) {
			return new WP_Error( 'invalid_redirect', 'Invalid redirect' );
		}

		//If no position given
		if ( $item->position === 0 ) {
			$item->position = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}{$this->redirects_table} WHERE `group_id` = %d", $item->group_id ) );
		}

		//Check if the path already exists
		if ( $item->group_id == 1 ) {
			if ( $redirect_id = $wpdb->get_var( $wpdb->prepare( "SELECT `id` FROM {$wpdb->prefix}{$this->redirects_table} WHERE `url` = %s", $item->url ) ) ) {
				$item->id = $redirect_id;
			}
		} else {
			$option = $item->getSource_flags()->getJson();
			$args   = array(
				'flag_post'      => $option['flag_post'],
				'flag_post_type' => $option['flag_post_type'],
				'flag_term'      => $option['flag_term'],
				'flag_taxonomy'  => $option['flag_taxonomy'],
				'group_id'       => 2,
				'match_url'      => $item->match_url,
			);
			//Check if the redirect exists
			if ( $term_db = $this->getRedirectByPost( $args ) ) {
				$item->id = $term_db->id;
			}
		}

		//Add the hook before adding the item
		$item = apply_filters( 'sq_redirect_create', $item );

		if ( ! empty( $item->match_data ) ) {
			$item->match_data = wp_json_encode( $item->match_data, JSON_UNESCAPED_SLASHES );
		}

		if ( ! $item->id ) {
			// Create
			if ( $wpdb->insert( $wpdb->prefix . $this->redirects_table, $item->toArray() ) !== false ) {
				$redirect = $this->getRedirectById( $wpdb->insert_id );

				if ( $redirect ) {
					do_action( 'sq_redirect_updated', $wpdb->insert_id, $redirect );

					return $redirect;
				}

				return new WP_Error( 'redirect_create_failed', 'Unable to get newly added redirect' );
			}
		} else {
			// Update
			if ( $wpdb->update( $wpdb->prefix . $this->redirects_table, $item->toArray(), array( 'id' => $item->id ) ) !== false ) {

				$redirect = $this->getRedirectById( $item->id );

				if ( $redirect ) {
					do_action( 'sq_redirect_updated', $item->id, $redirect );

					return $redirect;
				}

				return new WP_Error( 'redirect_create_failed', 'Unable to get newly added redirect' );
			}
		}

		return new WP_Error( 'redirect_create_failed', 'Unable to add new redirect' );
	}

	/**
	 * Get a redirect by ID
	 *
	 * @param integer $id Redirect ID.
	 *
	 * @return false|SQP_Models_Domain_Redirect
	 */
	public function getRedirectById( $id ) {
		global $wpdb;

		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}{$this->redirects_table} WHERE id=%d", $id ) );

		if ( $row ) {
			return SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Redirect', $row );
		}

		return false;
	}

	/**
	 * Enable a specific row from redirects
	 *
	 * @param $id
	 *
	 * @return void
	 */
	public function enableRedirectRow( $id ) {
		$item         = $this->getRedirectById( $id );
		$item->status = 'enabled';

		$this->addRedirectRow( $item );
	}

	/**
	 * Disable a specific row from redirects
	 *
	 * @param $id
	 *
	 * @return void
	 */
	public function disableRedirectRow( $id ) {
		$item         = $this->getRedirectById( $id );
		$item->status = 'disabled';

		$this->addRedirectRow( $item );
	}

	/**
	 * @param array $args of database fields and values
	 *
	 * @return false|SQP_Models_Domain_Redirect
	 */
	public function getRedirectByPost( $args ) {
		global $wpdb;

		extract( $args );

		if ( version_compare( $wpdb->db_version(), '5.5', '>' ) ) {

			$where = array();
			if ( isset( $flag_post ) && (int) $flag_post > 0 ) {
				$where[] = "JSON_EXTRACT(JSON_EXTRACT(`match_data`,'$.source'),'$.flag_post') = " . (int) $flag_post;
			}
			if ( isset( $flag_post_type ) && $flag_post_type <> '' ) {
				$where[] = "JSON_EXTRACT(JSON_EXTRACT(`match_data`,'$.source'),'$.flag_post_type') = '$flag_post_type'";
			}
			if ( isset( $flag_term ) && (int) $flag_term > 0 ) {
				$where[] = "JSON_EXTRACT(JSON_EXTRACT(`match_data`,'$.source'),'$.flag_term') = " . (int) $flag_term;
			}
			if ( isset( $flag_taxonomy ) && $flag_taxonomy <> '' ) {
				$where[] = "JSON_EXTRACT(JSON_EXTRACT(`match_data`,'$.source'),'$.flag_taxonomy') = '$flag_taxonomy'";
			}
			if ( isset( $group_id ) && (int) $group_id > 0 ) {
				$where[] = "group_id = " . (int) $group_id;
			}
			if ( isset( $match_url ) && $match_url <> '' ) {
				$where[] = "match_url = '$match_url'";
			}

			$row = $wpdb->get_row( "SELECT *  FROM {$wpdb->prefix}{$this->redirects_table} WHERE " . join( ' AND ', $where ) );

			if ( $row ) {
				return SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Redirect', $row );
			}
		}

		return false;

	}

	/**
	 * Delete row from database
	 *
	 * @param $id
	 *
	 * @return true
	 */
	public function deleteRedirectRow( $id ) {
		global $wpdb;

		$redirect = $this->getRedirectById( $id );
		do_action( 'sq_redirect_updated', $id, $redirect );

		return $wpdb->delete( $wpdb->prefix . $this->redirects_table, array( 'id' => $id ) );

	}

	/**
	 * Delete all redirects for a group_id
	 *
	 * @param array $args
	 *
	 */
	public function deleteRedirects( $args = array() ) {
		global $wpdb;

		extract( $args );

		$where = array();

		if ( isset( $group_id ) && $group_id > 0 ) {
			$where[] = "group_id = " . (int) $group_id;
		}

		if ( isset( $flag_post ) && (int) $flag_post > 0 ) {
			$where[] = "JSON_EXTRACT(JSON_EXTRACT(`match_data`,'$.source'),'$.flag_post') = " . (int) $flag_post;
		}

		$wpdb->query( "DELETE FROM `{$wpdb->prefix}{$this->redirects_table}`" . ( ! empty( $where ) ? ' WHERE ' . join( ' AND ', $where ) : '' ) );

	}

	/**
	 * Prepare the item from params to database add
	 *
	 * @param array $details
	 *
	 * @return array|false
	 */
	public function sanitizeData( array $details ) {
		$data    = array();
		$details = $this->cleanArray( $details );

		//If there is an update on redirect
		if ( isset( $details['id'] ) ) {
			$data['id'] = $details['id'];
		}

		// source url is mandatory
		if ( ! isset( $details['url'] ) ) {
			return false;
		}

		/**
		 * @var SQP_Models_Redirects_Flags $flags
		 */
		$flags = SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_Flags' );

		// Parse the source flags
		$flags->setFlags( $details );

		// Remove defaults
		$data['match_data']['source'] = $flags->getJsonWithDefaults();
		$data['match_data']           = array_filter( $data['match_data'] );

		if ( empty( $data['match_data'] ) ) {
			$data['match_data'] = null;
		}

		//Set regex
		$data['regex'] = $flags->isRegex() ? 1 : 0;

		// Parse URL
		if ( strpos( $details['url'], 'http:' ) !== false || strpos( $details['url'], 'https:' ) !== false ) {
			$details = array_merge( $details, $this->checkServer( $details ) );
		}

		//sanitize matching and url
		$data['match_type'] = isset( $details['match_type'] ) ? sanitize_text_field( $details['match_type'] ) : 'url';
		$data['url']        = $this->getUrl( $details['url'], $data['regex'] );

		//sanitize redirect status
		$data['status'] = ( isset( $details['status'] ) && in_array( $details['status'], array(
			'enabled',
			'disabled'
		) ) ? $details['status'] : 'enabled' );

		//if last count is set
		if ( isset( $details['last_count'] ) ) {
			$data['last_count'] = intval( $details['last_count'] );
		} else {
			$data['last_count'] = 0;
		}

		//if last access is set
		if ( isset( $details['last_access'] ) ) {
			$data['last_access'] = date( 'Y-m-d H:i:s', strtotime( sanitize_text_field( $details['last_access'] ) ) );
		} else {
			$data['last_access'] = '0000-00-00 00:00:00';
		}

		if ( ! is_wp_error( $data['url'] ) ) {
			/**
			 * @var SQP_Models_Redirects_UrlHandle $url_handler
			 */
			$url_handler = SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_UrlHandle' );
			$url_handler->setUrl( $data['url'] );
			$data['match_url'] = $url_handler->getPlainUrl();

			// If 'exact order' then save the match URL with query params
			if ( $flags->isQueryExactOrder() ) {
				/**
				 * @var SQP_Models_Redirects_Query $query
				 */
				$query = SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_Query' );
				$query->init( $details['url'], $flags );
				$data['match_url'] = $query->getUrlWithQuery( $details['url'] );
			}
		}

		$data['group_id'] = $this->getGroup( isset( $details['group_id'] ) ? $details['group_id'] : 1 );
		$data['position'] = $this->getPosition( $details );

		// Set match_url to 'regex'
		if ( $data['regex'] ) {
			$data['match_url'] = 'regex';
		}

		$matcher = SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_MatchHandle' );
		$matcher->init( $data['url'] );

		$action = SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_ActionHandle' );

		//Default redirect actions
		$action_type = 'url';
		$action_code = 301;

		if ( isset( $details['action_code'] ) ) {
			$action_code = (int) $details['action_code'];

			if ( $action_code == 404 ) {
				$action_type = 'error';
			}
		}

		$action->init( $action_type, $action_code );

		$data['action_type'] = sanitize_text_field( $action_type );
		$data['action_code'] = $this->getCode( $action_type, $action_code );

		if ( isset( $details['action_data'] ) ) {
			$data['action_data'] = $matcher->process( $details, ! $this->isUrlType( $data['action_type'] ) );
		}

		//prevent infinite loop
		if ( $data['match_url'] === $data['action_data'] ) {
			SQP_Classes_Error::setError( esc_html__( "You can't set the same path on redirect.", 'squirrly-seo-pack' ) );
		}

		// Any errors?
		foreach ( $data as $value ) {
			if ( is_wp_error( $value ) ) {
				return false;
			}
		}

		return apply_filters( 'sq_redirect_prepare', $data );
	}


	/**
	 * Sort URLs
	 *
	 * @param object $first First URL.
	 * @param object $second Second URL.
	 *
	 * @return integer
	 */
	public static function sortUrls( $first, $second ) {
		if ( $first->position === $second->position ) {
			// Fall back to which redirect was created first
			return ( $first->id < $second->id ) ? - 1 : 1;
		}

		return ( $first->position < $second->position ) ? - 1 : 1;
	}


	///////////////////////////////////////////////////////// CHECK / CREATE

	/**
	 * Check if the database tables exists
	 *
	 * @return bool
	 */
	public function checkTablesExist() {
		global $wpdb;
		$wpdb->hide_errors();
		$query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->prefix . $this->redirects_table );

		if ( $wpdb->get_var( $query ) !== $wpdb->prefix . $this->redirects_table ) {
			if ( ! $this->createRedirectsTable() ) {
				SQP_Classes_Error::setError( esc_html__( "Can't create the redirects table. Please check the database permissions.", "squirrly-seo-pack" ) );
				SQP_Classes_Helpers_Tools::saveOptions( 'sq_redirects_tables', false );

				return false;
			}
		}

		SQP_Classes_Helpers_Tools::saveOptions( 'sq_redirects_tables', true );

		return true;

	}

	/**
	 * Returns the current database charset
	 *
	 * @return string Database charset
	 */
	public function getCharsetCollate() {
		global $wpdb;

		$charset_collate = '';
		if ( ! empty( $wpdb->charset ) ) {
			// Fix some common invalid charset values
			$fixes = [
				'utf-8',
				'utf',
			];

			$charset = $wpdb->charset;
			if ( in_array( strtolower( $charset ), $fixes, true ) ) {
				$charset = 'utf8';
			}

			$charset_collate = "DEFAULT CHARACTER SET $charset";
		}

		if ( ! empty( $wpdb->collate ) ) {
			$charset_collate .= " COLLATE=$wpdb->collate";
		}

		return $charset_collate;
	}

	/**
	 * Create the redirects database table
	 *
	 * @return bool|int
	 */
	private function createRedirectsTable() {
		global $wpdb;

		$collate = $this->getCharsetCollate();

		$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}{$this->redirects_table}` (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`url` mediumtext NOT NULL,
			`match_url` VARCHAR(2000) DEFAULT NULL,
  			`match_data` TEXT,
			`regex` INT(11) unsigned NOT NULL DEFAULT '0',
			`position` INT(11) unsigned NOT NULL DEFAULT '0',
			`last_count` INT(10) unsigned NOT NULL DEFAULT '0',
			`last_access` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
			`group_id` INT(11) NOT NULL DEFAULT '0',
			`status` enum('enabled','disabled') NOT NULL DEFAULT 'enabled',
			`action_type` VARCHAR(20) NOT NULL,
			`action_code` INT(11) unsigned NOT NULL,
			`action_data` MEDIUMTEXT,
			`match_type` VARCHAR(20) NOT NULL,
			PRIMARY KEY (`id`),
			KEY `url` (`url`(191)),
			KEY `status` (`status`),
			KEY `regex` (`regex`),
			KEY `group_idpos` (`group_id`,`position`),
			KEY `group` (`group_id`),
			KEY `match_url` (`match_url`(191))
	  ) $collate";

		$sql = preg_replace( '/[ \t]{2,}/', '', $sql );

		return $wpdb->query( $sql );
	}

	/**
	 * Delete the redirects database table
	 *
	 * @return void
	 */
	public function deleteTable() {
		global $wpdb;

		$wpdb->query( "DROP TABLE IF EXISTS `{$wpdb->prefix}{$this->redirects_table}`" );
		SQP_Classes_Helpers_Tools::saveOptions( 'sq_redirects_tables', false );
	}

	/**
	 * Create a Table Backup
	 *
	 * @return string
	 */
	function createTableBackup() {
		global $wpdb;

		$tables = $wpdb->get_col( 'SHOW TABLES' );
		$output = '';
		foreach ( $tables as $table ) {
			if ( $table == $wpdb->prefix . $this->redirects_table ) {
				$result  = $wpdb->get_results( "SELECT * FROM `$table`", ARRAY_N );
				$columns = $wpdb->get_results( "SHOW COLUMNS FROM `$table`", ARRAY_N );
				for ( $i = 0; $i < count( (array) $result ); $i ++ ) {
					$row    = $result[ $i ];
					$output .= "INSERT INTO `$table` (";
					for ( $col = 0; $col < count( (array) $columns ); $col ++ ) {
						$output .= ( isset( $columns[ $col ][0] ) ? $columns[ $col ][0] : "''" );
						if ( $col < ( count( (array) $columns ) - 1 ) ) {
							$output .= ',';
						}
					}
					$output .= ') VALUES(';
					for ( $j = 0; $j < count( (array) $result[0] ); $j ++ ) {
						$row[ $j ] = str_replace( array( "\'", "'" ), array( "'", "\'" ), $row[ $j ] );
						$output    .= ( isset( $row[ $j ] ) ? "'" . $row[ $j ] . "'" : "''" );
						if ( $j < ( count( (array) $result[0] ) - 1 ) ) {
							$output .= ',';
						}
					}
					$output .= ")\n";
				}
				$output .= "\n";
				break;
			}
		}
		$wpdb->flush();

		return $output;
	}

	/**
	 * Restore Table Backup
	 *
	 * @param  $queries
	 * @param bool $overwrite
	 *
	 * @return bool
	 */
	public function restoreTableBackup( $queries, $overwrite = true ) {
		global $wpdb;

		if ( is_array( $queries ) && ! empty( $queries ) ) {
			//create the table with the last updates
			$this->checkTablesExist();

			foreach ( (array) $queries as $query ) {
				$query = trim( $query, PHP_EOL );
				if ( ! empty( $query ) && strlen( $query ) > 1 ) {

					if ( strpos( $query, 'CREATE TABLE' ) !== false ) {
						continue;
					}

					//get each row from query
					if ( strpos( $query, '(' ) !== false && strpos( $query, ')' ) !== false && strpos( $query, 'VALUES' ) !== false ) {
						$fields = substr( $query, strpos( $query, '(' ) + 1 );
						$fields = substr( $fields, 0, strpos( $fields, ')' ) );
						$fields = explode( ",", trim( $fields ) );

						$values = substr( $query, strpos( $query, 'VALUES' ) + 6 );
						if ( strpos( $query, 'ON DUPLICATE' ) !== false ) {
							$values = substr( $values, 0, strpos( $values, 'ON DUPLICATE' ) );
						}

						$values = explode( "','", trim( trim( $values ), '();' ) );
						$values = array_map( function( $value ) {
							return trim( $value, "'" );
						}, $values );

						//Make sure the values match with the fields
						if ( ! empty( $fields ) && ! empty( $values ) && count( $fields ) == count( $values ) ) {

							$placeholders = array_fill( 0, count( $values ), '%s' );

							if ( $overwrite ) {
								$query = "INSERT INTO `{$wpdb->prefix}{$this->redirects_table}` (`" . join( "`,`", $fields ) . "`) 
                                          VALUES (" . join( ",", $placeholders ) . ") ON DUPLICATE KEY 
                                          UPDATE `" . join( "` = %s,`", $fields ) . "` = %s";
							} else {
								$query = "INSERT INTO `{$wpdb->prefix}{$this->redirects_table}` (`" . join( "`,`", $fields ) . "`) 
                                          VALUES (" . join( ",", $placeholders ) . ") ";
							}
							$wpdb->query( $wpdb->prepare( $query, array_merge( $values, $values ) ) );

						}

					}

				}
			}

			return true;
		}

		return false;
	}

	/**
	 * @param $array
	 *
	 * @return mixed
	 */
	private function cleanArray( $array ) {
		foreach ( $array as $name => $value ) {
			if ( is_array( $value ) ) {
				$array[ $name ] = $this->cleanArray( $value );
			} elseif ( is_string( $value ) ) {
				$value          = trim( $value );
				$array[ $name ] = $value;
			} else {
				$array[ $name ] = $value;
			}
		};

		return $array;
	}

	/**
	 * @param $details
	 *
	 * @return mixed
	 */
	private function checkServer( $details ) {

		$url    = $details['url'];
		$domain = wp_parse_url( $url, PHP_URL_HOST );

		/**
		 * @var SQP_Models_Redirects_Request $request
		 */
		$request = SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_Request' );

		// Auto-convert an absolute URL to relative
		if ( $domain && $domain == $request->getRequestServerName() ) {

			$url = wp_parse_url( $details['url'], PHP_URL_PATH );

			if ( is_wp_error( $url ) || $url === null ) {
				$url = '/';
			}

		}

		$details['url'] = $url;

		return $details;
	}

	protected function getUrl( $url, $regex ) {
		$url = self::sanitizeUrl( $url, $regex );

		if ( $url === '' ) {
			return new WP_Error( 'redirect', 'Invalid source URL' );
		}

		return $url;
	}

	public function sanitizeUrl( $url, $regex = false ) {
		$url = wp_kses( $url, 'strip' );
		$url = str_replace( '&amp;', '&', $url );

		// Make sure that the old URL is relative
		$url = preg_replace( '@^https?://(.*?)/@', '/', $url );
		$url = preg_replace( '@^https?://(.*?)$@', '/', $url );

		// No new lines
		$url = preg_replace( "/[\r\n\t].*?$/s", '', $url );

		// Clean control codes
		$url = preg_replace( '/[^\PC\s]/u', '', $url );

		// Ensure a slash at start
		if ( substr( $url, 0, 1 ) !== '/' && (bool) $regex === false ) {
			$url = '/' . $url;
		}

		// Try and URL decode any i10n characters
		$decoded = $this->removeBadEncoding( rawurldecode( $url ) );

		// Was there any invalid characters?
		if ( $decoded === false ) {
			// Yes. Use the url as an encoded URL, and check for invalid characters
			$decoded = $this->removeBadEncoding( $url );

			// Was there any invalid characters?
			if ( $decoded === false ) {
				// Yes, it's still a problem. Use the URL as-is and hope for the best
				return $url;
			}
		}

		// Return the URL
		return $decoded;
	}

	/**
	 * Remove any bad encoding, where possible
	 *
	 * @param string $text Text.
	 *
	 * @return string|false
	 */
	private function removeBadEncoding( $text ) {
		// Try and remove bad decoding
		if ( function_exists( 'iconv' ) ) {
			return @iconv( 'UTF-8', 'UTF-8//IGNORE', sanitize_text_field( $text ) );
		}

		return sanitize_text_field( $text );
	}

	protected function getGroup( $group_id ) {
		return intval( $group_id, 10 );
	}

	protected function getPostID( $post_id ) {
		return intval( $post_id, 20 );
	}

	protected function getPosition( $details ) {
		if ( isset( $details['position'] ) ) {
			return max( 0, intval( $details['position'], 10 ) );
		}

		return 0;
	}

	public function isValidRedirectCode( $code ) {
		return in_array( $code, array( 301, 302, 303, 304, 307, 308 ), true );
	}

	public function isValidErrorCode( $code ) {
		return in_array( $code, array( 400, 401, 403, 404, 410, 418, 451, 500, 501, 502, 503, 504 ), true );
	}

	protected function getCode( $action_type, $code ) {
		if ( $action_type === 'url' || $action_type === 'random' ) {
			if ( $this->isValidRedirectCode( $code ) ) {
				return $code;
			}

			return 301;
		}

		if ( $action_type === 'error' ) {
			if ( $this->isValidErrorCode( $code ) ) {
				return $code;
			}

			return 404;
		}

		return 0;
	}

	protected function isUrlType( $type ) {
		if ( $type === 'url' || $type === 'pass' ) {
			return true;
		}

		return false;
	}


}
