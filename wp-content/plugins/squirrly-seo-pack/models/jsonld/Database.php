<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Jsonld_Database {

	/** @var int $count Count Jsonld */
	private $count;

	/** @var string $jsonld_table log table */
	private $jsonld_table = 'qss_jsonld';

	/**
	 * Get the schema rows from database
	 *
	 * @param int $start
	 * @param int $limit
	 * @param int $schema
	 * @param int $search
	 *
	 * @return array
	 */
	public function getRows( $args ) {

		extract( $args );

		global $wpdb;
		$items       = array();
		$query_where = array();
		$query_limit = '';

		$query_select = "SELECT * FROM {$wpdb->prefix}{$this->jsonld_table}";
		$query_count  = "SELECT COUNT(id) FROM {$wpdb->prefix}{$this->jsonld_table}";
		$query_order  = "ORDER BY id DESC";

		if ( isset( $start ) && isset( $num ) ) {
			$query_limit = ' LIMIT ' . (int) $start . ', ' . (int) $num;
		}

		if ( isset( $url_hash ) && $url_hash <> '' ) {
			$query_where[] = "(`url_hash` = '$url_hash')";
		}

		if ( isset( $search ) && trim( $search ) <> '' ) {
			$search        = sanitize_text_field( $search );
			$query_where[] = "(`schema` like '%$search%')";
		}

		if ( isset( $jsonld_type ) && trim( $jsonld_type ) <> '' ) {

			/** @var SQP_Models_Jsonld_Sanitize $sanitize */
			$sanitize              = SQP_Classes_ObjController::getClass( 'SQP_Models_Jsonld_Sanitize' );
			$sanitized_jsonld_type = $sanitize->sanitizeJsonldType( $jsonld_type );

			$query_where[] = "(`jsonld_type` = '$jsonld_type' OR `jsonld_type` = '$sanitized_jsonld_type')";
		}

		$query       = $query_count . ' ' . 'WHERE' . ' ' . join( ' AND ', $query_where );
		$this->count = $wpdb->get_var( $query );

		$query = $query_select . ' ' . ( ! empty( $query_where ) ? 'WHERE' : '' ) . ' ' . join( ' AND ', $query_where ) . ' ' . $query_order . ' ' . $query_limit;
		$rows  = $wpdb->get_results( $query );

		if ( is_array( $rows ) ) {

			foreach ( $rows as $row ) {
				//unpack the post and schema
				$row->post   = json_decode( $row->post, true );
				$row->schema = json_decode( $row->schema, true );

				$items[ strtolower( $row->jsonld_type ) ] = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Jsonld', $row );
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
	 * Add/Update the jsonld in database
	 *
	 * @param SQP_Models_Domain_Jsonld $item
	 * @param array $params
	 *
	 * @return SQP_Models_Domain_Jsonld|WP_Error
	 */
	public function addRow( $item, $params ) {
		global $wpdb;

		if ( ! $item instanceof SQP_Models_Domain_Jsonld || ! isset( $params['schema'] ) ) {
			return new WP_Error( 'invalid_jsonld', 'Invalid Schema' );
		}

		//Check if the path already exists
		$schema_db = $this->getRows( array( 'url_hash' => $item->post->hash, 'jsonld_type' => $item->jsonld_type ) );

		if ( ! empty( $schema_db ) ) {
			$item = current( $schema_db );
		}

		//Add the hook before adding the item
		$data = apply_filters( 'sq_jsonld_create', $item->toArray() );

		//prepare for database insert
		$data['post']        = wp_json_encode( $data['post'], JSON_UNESCAPED_SLASHES );
		$data['jsonld_type'] = $item->jsonld_type;
		$data['schema']      = wp_json_encode( $params['schema'], JSON_UNESCAPED_SLASHES );

		if ( ! $item->id ) {
			// Create
			if ( $wpdb->insert( $wpdb->prefix . $this->jsonld_table, $data ) !== false ) {
				$row = $this->getById( $wpdb->insert_id );

				if ( $row ) {
					do_action( 'sq_jsonld_updated', $wpdb->insert_id, $row );

					return $row;
				}

				return new WP_Error( 'jsonld_create_failed', 'Unable to get newly added jsonld' );
			}
		} else {
			// Update
			if ( $wpdb->update( $wpdb->prefix . $this->jsonld_table, $data, array( 'id' => $item->id ) ) !== false ) {

				$row = $this->getById( $item->id );

				if ( $row ) {
					do_action( 'sq_jsonld_updated', $item->id, $row );

					return $row;
				}

				return new WP_Error( 'jsonld_create_failed', 'Unable to get newly added jsonld' );
			}
		}

		return new WP_Error( 'jsonld_create_failed', 'Unable to add new jsonld' );
	}


	/**
	 * Get a jsonld by ID
	 *
	 * @param integer $id JsonLD ID.
	 *
	 * @return false|SQP_Models_Domain_Jsonld
	 */
	public function getById( $id ) {
		global $wpdb;

		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}{$this->jsonld_table} WHERE id=%d", $id ) );

		if ( $row ) {

			//unpack the post and schema
			$row->post   = json_decode( $row->post );
			$row->schema = json_decode( $row->schema );

			return SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Jsonld', $row );
		}

		return false;
	}

	/**
	 * Delete row from database
	 *
	 * @param SQP_Models_Domain_Jsonld $item
	 *
	 * @return bool
	 */
	public function deleteRow( $item ) {
		global $wpdb;

		//Check if the path already exists
		$schema_db = $this->getRows( array( 'url_hash' => $item->post->hash, 'jsonld_type' => $item->jsonld_type ) );

		if ( ! empty( $schema_db ) ) {
			$item = current( $schema_db );

			return $wpdb->delete( $wpdb->prefix . $this->jsonld_table, array( 'id' => $item->id ) );
		}

		return false;
	}

	/**
	 * Get all reusable schemas
	 *
	 * @param $args
	 *
	 * @return array
	 */
	public function getReusableRows( $args ) {

		extract( $args );

		global $wpdb;
		$items       = array();
		$query_where = array();
		$query_limit = '';

		$query_select = "SELECT * FROM {$wpdb->prefix}{$this->jsonld_table}";
		$query_count  = "SELECT COUNT(id) FROM {$wpdb->prefix}{$this->jsonld_table}";

		if ( isset( $sort ) && trim( $sort ) <> '' && isset( $order ) && trim( $order ) <> '' ) {
			$sort  = sanitize_text_field( $sort );
			$order = sanitize_text_field( $order );
			if ( $sort == 'name' ) {
				$query_order = sprintf( "ORDER BY LCASE(JSON_EXTRACT(`post`,'$.%s')) %s", $sort, $order );
			} else {
				$query_order = sprintf( "ORDER BY %s %s", $sort, $order );
			}
		} else {
			$query_order = "ORDER BY LCASE(JSON_EXTRACT(`post`,'$.name')) ASC";
		}

		if ( isset( $start ) && isset( $num ) ) {
			$query_limit = ' LIMIT ' . (int) $start . ', ' . (int) $num;
		}

		//If reusable data
		$query_where[] = "JSON_EXTRACT(`post`,'$.reusable') = true";

		if ( isset( $id ) && (int) $id > 0 ) {
			$query_where[] = "(`id` = '$id')";
		}

		if ( isset( $search ) && trim( $search ) <> '' ) {
			$search        = sanitize_text_field( $search );
			$search        = $this->toLower( $search );
			$query_where[] = "LCASE(JSON_EXTRACT(`post`,'$.name')) LIKE '%$search%'";
		}

		if ( isset( $jsonld_type ) && trim( $jsonld_type ) <> '' ) {
			$query_where[] = "(`jsonld_type` = '$jsonld_type')";
		}

		$query       = $query_count . ' ' . 'WHERE' . ' ' . join( ' AND ', $query_where );
		$this->count = $wpdb->get_var( $query );

		$query = $query_select . ' ' . ( ! empty( $query_where ) ? 'WHERE' : '' ) . ' ' . join( ' AND ', $query_where ) . ' ' . $query_order . ' ' . $query_limit;
		$rows  = $wpdb->get_results( $query );

		if ( is_array( $rows ) ) {

			foreach ( $rows as $row ) {
				//unpack the post
				$post = json_decode( $row->post );
				//get reusable name
				$row->name         = $post->name;
				$items[ $row->id ] = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Reusable', $row );
			}

		}

		return $items;
	}

	/**
	 * Add/Update reusable schema
	 *
	 * @param SQP_Models_Domain_Jsonld $item
	 * @param array $params
	 *
	 * @return false|string|WP_Error
	 */
	public function addReusableRow( $item, $params ) {
		global $wpdb;

		if ( ! $item instanceof SQP_Models_Domain_Jsonld || ! isset( $params['schema'] ) ) {
			return new WP_Error( 'invalid_jsonld', 'Invalid Schema' );
		}

		//Add the hook before adding the item
		$data = apply_filters( 'sq_jsonld_reusable_create', $item->toArray() );

		//add reusable signal
		$data['post']['reusable'] = true;
		$data['post']['name']     = $params['name'];

		//prepare for database insert
		$data['post']     = wp_json_encode( $data['post'], JSON_UNESCAPED_SLASHES );
		$data['url_hash'] = md5( 'reusable' );
		$data['schema']   = wp_json_encode( $params['schema'], JSON_UNESCAPED_SLASHES );

		//check if there is an existing reusable schema
		if ( ! $item->id ) {
			// Create
			if ( $wpdb->insert( $wpdb->prefix . $this->jsonld_table, $data ) !== false ) {
				$row = $this->getById( $wpdb->insert_id );

				if ( $row ) {
					do_action( 'sq_jsonld_reusable_updated', $wpdb->insert_id, $row );

					return $row;
				}

				return new WP_Error( 'jsonld_create_failed', 'Unable to get newly added jsonld' );
			}
		} else {
			// Update
			if ( $wpdb->update( $wpdb->prefix . $this->jsonld_table, $data, array( 'id' => $item->id ) ) !== false ) {

				$row = $this->getById( $item->id );

				if ( $row ) {
					do_action( 'sq_jsonld_reusable_updated', $item->id, $row );

					return $row;
				}

				return new WP_Error( 'jsonld_create_failed', 'Unable to get newly added jsonld' );
			}
		}


		return new WP_Error( 'jsonld_create_failed', 'Unable to add reusable schema' );
	}

	/**
	 * Delete reusable schemas
	 *
	 * @param $item
	 *
	 * @return mixed|WP_Error
	 */
	public function deleteReusableRow( $item ) {

		global $wpdb;

		//check if there is an existing reusable schema
		//check if the reusable schema is used on website
		if ( empty( $this->getRows( array( 'jsonld_type' => $item->id ) ) ) ) {

			if ( $item->id > 0 ) {
				return $wpdb->delete( $wpdb->prefix . $this->jsonld_table, array( 'id' => $item->id ) );
			}

		}

		return new WP_Error( 'jsonld_deleted_failed', "Unable to delete reusable schema. Check if it's in use." );
	}


	/**
	 * Change the map with values from database if exists
	 *
	 * @param array $schema_map
	 * @param string $jsonld_type
	 * @param SQP_Models_Domain_Jsonld $jsonldDomain
	 *
	 * @return mixed
	 */
	public function changeSchemaMapWithValues( $schema_map, $jsonld_type, $jsonldDomain ) {

		$schema_db = $this->getRows( array( 'url_hash' => $jsonldDomain->post->hash, 'jsonld_type' => $jsonld_type ) );

		if ( ! empty( $schema_db ) ) {

			$schema_db = current( $schema_db );

			if ( $schema_db instanceof SQP_Models_Domain_Jsonld ) {
				//Get the schema array from database
				$values = $schema_db->schema->allArray();
				//replace the current schema with values
				$schema_map = $schema_db->addSchemaMapValues( $schema_map, $values );
			}
		}

		return $schema_map;

	}

	/**
	 * Change the map with values from reusable schema if exists
	 *
	 * @param array $schema_map
	 * @param string $jsonld_id Reusable jsonld id
	 * @param SQP_Models_Domain_Jsonld $jsonldDomain
	 *
	 * @return mixed
	 */
	public function changeSchemaMapWithReusableValues( $schema_map, $jsonld_id, $jsonldDomain ) {

		if ( is_numeric( $jsonld_id ) && $jsonldDomain->isReusable( $jsonld_id ) ) {

			$schema_db = $this->getReusableRows( array( 'id' => $jsonld_id ) );

			if ( ! empty( $schema_db ) ) {

				$schema_db = current( $schema_db );

				if ( $schema_db instanceof SQP_Models_Domain_Reusable ) {

					//Get the schema array from database
					$values = json_decode( $schema_db->schema, true );

					//replace the current schema with values
					$schema_map = $jsonldDomain->addSchemaMapValues( $schema_map, $values );

				}
			}
		}

		return $schema_map;

	}


	/**
	 * Prepare the item from params to database add
	 *
	 * @param array $details
	 *
	 * @return array|false
	 */
	public function sanitizeData( array $details ) {

		$allowed = array( 'post_id', 'post_type', 'term_id', 'taxonomy' );
		$allowed = array_flip( $allowed );

		add_filter( 'sqp_jsonld_array', function( $jsonld ) use ( $allowed ) {

			if ( isset( $jsonld['post']['ID'] ) ) {
				$jsonld['post']['post_id'] = $jsonld['post']['ID'];
			}

			$jsonld['post'] = array_intersect_key( $jsonld['post'], $allowed );

			return $jsonld;
		} );

		//serialize the schema
		if ( count( array_intersect_key( $details, $allowed ) ) > 0 && count( array_intersect_key( $details, $allowed ) ) <> count( $allowed ) ) {
			$details = array_merge( $allowed, $details );
		}

		//sanitize reusable jsonld ID
		if ( isset( $details['jsonld_id'] ) ) {
			$details['id'] = $details['jsonld_id'];
		}

		//sanitize all data against SQL Injection and Script Injection
		$data = SQP_Classes_Helpers_Sanitize::sanitizeArrayFields( 'sanitizeField', $details );

		return apply_filters( 'sq_jsonld_prepare', $data );
	}

	///////////////////////////////////////////////////////// CHECK / CREATE

	/**
	 * Check if the database tables exists
	 *
	 * @return void
	 */
	public function checkTablesExist() {
		global $wpdb;
		$wpdb->hide_errors();
		$query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->prefix . $this->jsonld_table );

		if ( $wpdb->get_var( $query ) !== $wpdb->prefix . $this->jsonld_table ) {
			if ( ! $this->createTable() ) {
				SQP_Classes_Error::setError( esc_html__( "Can't create the table. Please check the database permissions.", "squirrly-seo-pack" ) );
			}
		}

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
	 * Create the jsonld database table
	 *
	 * @return bool|int
	 */
	private function createTable() {
		global $wpdb;

		$collate = $this->getCharsetCollate();

		$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}{$this->jsonld_table}` (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`post` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
			`jsonld_type` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
            `url_hash` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
			`schema` MEDIUMTEXT,
			PRIMARY KEY (`id`),
            INDEX url_hash(url_hash) USING BTREE,
            INDEX jsonld_type(jsonld_type) USING BTREE
	  ) $collate";

		$sql = preg_replace( '/[ \t]{2,}/', '', $sql );

		return $wpdb->query( $sql );
	}

	/**
	 * Delete the jsonld database table
	 *
	 * @return void
	 */
	public function deleteTable() {
		global $wpdb;
		$wpdb->query( "DROP TABLE IF EXISTS `{$wpdb->prefix}{$this->jsonld_table}`" );
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
			if ( $table == $wpdb->prefix . $this->jsonld_table ) {
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
								$query = "INSERT INTO `{$wpdb->prefix}{$this->jsonld_table}` (`" . join( "`,`", $fields ) . "`) 
                                          VALUES (" . join( ",", $placeholders ) . ") ON DUPLICATE KEY 
                                          UPDATE `" . join( "` = %s,`", $fields ) . "` = %s";
							} else {
								$query = "INSERT INTO `{$wpdb->prefix}{$this->jsonld_table}` (`" . join( "`,`", $fields ) . "`) 
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
	 * Convert a string to lowercase
	 *
	 * @param String $string search.
	 *
	 * @return String
	 */
	public function toLower( $string ) {
		if ( function_exists( 'mb_strtolower' ) ) {
			return mb_strtolower( $string, 'UTF-8' );
		}

		return strtolower( $string );
	}
}
