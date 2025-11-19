<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Redirects_Log {


	/** @var int $count Count redirects */
	private $count;
	/** @var string $log_table log table */
	private $log_table = 'qss_redirects_log';

	////////////////////////////////////////////////////////// LOG

	/**
	 * Get the rule rows from database
	 *
	 * @param $page
	 * @param $limit
	 *
	 * @return array
	 */
	public function getLogRows( $args ) {

		extract( $args );

		global $wpdb;
		$items       = array();
		$query_where = array();
		$query_limit = '';

		$query_select = "SELECT * FROM {$wpdb->prefix}{$this->log_table}";
		$query_count  = "SELECT COUNT(id) FROM {$wpdb->prefix}{$this->log_table}";
		$query_order  = "ORDER BY created DESC";

		if ( isset( $start ) && isset( $num ) ) {
			$query_limit = ' LIMIT ' . (int) $start . ', ' . (int) $num;
		}

		if ( isset( $search ) && trim( $search ) <> '' ) {
			$search        = sanitize_text_field( $search );
			$query_where[] = "`url` like '%$search%'";
		}

		if ( isset( $http_code ) && (int) $http_code > 0 ) {
			//if there is a code search
			$query_where[] = "`http_code` = " . (int) $http_code;
		} else {
			//make sure it's a redirect log and not a 404
			$query_where[] = "`redirection_id` > 0";
		}


		if ( ! empty( $query_where ) ) {
			$query_where = 'WHERE' . ' ' . join( ' AND ', $query_where );
		}

		$query       = $query_count . ' ' . $query_where;
		$this->count = $wpdb->get_var( $query );

		$query = $query_select . ' ' . $query_where . ' ' . $query_order . ' ' . $query_limit;
		$rows  = $wpdb->get_results( $query );

		if ( is_array( $rows ) ) {

			foreach ( $rows as $row ) {
				$items[] = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Log', $row );
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
	 * Get the number of visits from log for a specific redirection
	 *
	 * @param $id
	 *
	 * @return int
	 */
	public function countVisits( $id ) {
		global $wpdb;

		if ( $row = $wpdb->get_row( $wpdb->prepare( "SELECT count(id) as count FROM {$wpdb->prefix}{$this->log_table} WHERE redirection_id=%d", $id ) ) ) {
			return $row->count;
		}

		return 0;
	}

	public function addLogRow( $item ) {
		global $wpdb;

		$item = apply_filters( 'sq_log_create', $item );

		// Create
		if ( $wpdb->insert( $wpdb->prefix . $this->log_table, $item->toArray() ) !== false ) {
			$redirect = $this->getLogById( $wpdb->insert_id );

			if ( $redirect ) {
				do_action( 'sq_log_updated', $wpdb->insert_id, $redirect );

				return $redirect;
			}

			return new WP_Error( 'log_create_failed', 'Unable to get newly added redirect' );
		}

		return new WP_Error( 'log_create_failed', 'Unable to add new redirect' );
	}

	/**
	 * Get a redirect by ID
	 *
	 * @param integer $id Redirect ID.
	 *
	 * @return false|SQP_Models_Domain_Log
	 */
	public function getLogById( $id ) {
		global $wpdb;

		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}{$this->log_table} WHERE id=%d", $id ) );

		if ( $row ) {
			return SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Log', $row );
		}

		return false;
	}

	/**
	 * Delete row from database
	 *
	 * @param $id
	 * @param $type
	 *
	 * @return true
	 */
	public function deleteLogRow( $id ) {
		global $wpdb;

		return $wpdb->delete( $wpdb->prefix . $this->log_table, [ 'id' => $id ] );
	}

	/**
	 * Delete log older than set
	 *
	 * @param $days
	 *
	 * @return void
	 */
	public function deleteOldLog( $days ) {
		global $wpdb;

		$days = (int) $days;

		if ( $days > 0 ) {
			$wpdb->query( "DELETE FROM {$wpdb->prefix}{$this->log_table} WHERE `created` < DATE_SUB(CURDATE(),INTERVAL $days DAY)" );
		}

	}

	/**
	 * Delete all matching log entries
	 *
	 * @param array $params Array of filter parameters.
	 *
	 */
	public function deleteLogAll( $params = array() ) {
		global $wpdb;

		$wpdb->query( "DELETE FROM {$wpdb->prefix}{$this->log_table}" );
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

		$query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->prefix . $this->log_table );

		if ( $wpdb->get_var( $query ) !== $wpdb->prefix . $this->log_table ) {
			if ( ! $this->createLogTable() ) {
				SQP_Classes_Error::setError( esc_html__( "Can't create the redirects table. Please check the database permissions.", "squirrly-seo-pack" ) );
				SQP_Classes_Helpers_Tools::saveOptions( 'sq_redirects_log_tables', false );

				return false;
			}
		}

		SQP_Classes_Helpers_Tools::saveOptions( 'sq_redirects_log_tables', true );

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
	 * Create the log database table
	 *
	 * @return bool|int
	 */
	private function createLogTable() {

		global $wpdb;

		$collate = $this->getCharsetCollate();

		$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}{$this->log_table}` (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`created` datetime NOT NULL,
			`url` MEDIUMTEXT NOT NULL,
			`domain` VARCHAR(255) DEFAULT NULL,
			`sent_to` MEDIUMTEXT,
			`agent` MEDIUMTEXT,
			`referrer` MEDIUMTEXT,
			`http_code` INT(11) unsigned NOT NULL DEFAULT '0',
			`request_method` VARCHAR(10) DEFAULT NULL,
			`request_data` MEDIUMTEXT,
			`redirect_by` VARCHAR(50) DEFAULT NULL,
			`redirection_id` INT(11) unsigned DEFAULT NULL,
			`ip` VARCHAR(45) DEFAULT NULL,
			PRIMARY KEY (`id`),
			KEY `created` (`created`),
			KEY `redirection_id` (`redirection_id`),
			KEY `ip` (`ip`)
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

		$wpdb->query( "DROP TABLE IF EXISTS `{$wpdb->prefix}{$this->log_table}`" );
		SQP_Classes_Helpers_Tools::saveOptions( 'sq_redirects_log_tables', false );

	}


}
