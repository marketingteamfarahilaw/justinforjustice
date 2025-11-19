<?php
defined('ABSPATH') || die('Cheatin\' uh?');

class SQP_Models_Redirects_Database
{

    private $redirects_table = 'qss_redirects';
    private $log_table = 'qss_log';

    /**
     * Get a redirect that matches a URL
     *
     * @param string $url URL to match.
     * @return SQP_Models_Domain_Redirect[]
     */
    public function getMatchedUrls( $url ) {
        global $wpdb;
        $max_redirects = apply_filters('sq_max_redirects', 1000);

        /**
         * Check the cache
         * @var SQP_Models_Redirects_UrlCache $cache
         */
        $cache = SQP_Classes_ObjController::getClass('SQP_Models_Redirects_UrlCache');
        $items = $cache->get( $url );

        if($items !== false ){
            return $items;
        }

        /**
         * @var SQP_Models_Redirects_UrlHandle $url_handler
         */
        $url_handler = SQP_Classes_ObjController::getClass('SQP_Models_Redirects_UrlHandle');
        $url_handler->setUrl( $url );
        $plain_url = $url_handler->getPlainUrl();

        /**
         * @var SQP_Models_Redirects_Flags $flags
         */
        $flags = SQP_Classes_ObjController::getClass('SQP_Models_Redirects_Flags');
        $flags->setFlags( array($flags::FLAG_CASE => true) );

        /**
         * @var SQP_Models_Redirects_Query $query
         */
        $query = SQP_Classes_ObjController::getClass('SQP_Models_Redirects_Query');
        $query->init( $url, $flags );
        $full_url = $query->getUrlWithQuery( $url );

        $rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}{$this->redirects_table} WHERE match_url IN (%s, %s, 'regex') AND status='enabled' LIMIT %d", $plain_url, $full_url, $max_redirects));

        $items = array();

        if ( is_array( $rows ) ) {
            foreach ( $rows as $row ) {
                $items[] = SQP_Classes_ObjController::getDomain('SQP_Models_Domain_Redirect', $row );
            }

            usort( $items, [ $this, 'sortUrls' ] );

            if ( count( $items ) >= $max_redirects ) {
                // Something has gone pretty wrong at this point
                error_log( 'Squirrly: maximum redirect limit exceeded' );
            }
        }

	    /** @var SQP_Controllers_Redirects $redirects */
	    $redirects = SQP_Classes_ObjController::getClass( 'SQP_Controllers_Redirects' );
	    $group_id = $redirects->getGroupIds('slug'); //slug change redirect

		//check the slug changes
	    $rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}{$this->redirects_table} WHERE match_url = %s AND status='enabled' AND group_id = %d LIMIT %d", '/' . basename($plain_url), $group_id,  $max_redirects));

	    if ( is_array( $rows ) ) {

		    foreach ( $rows as $row ) {
			    $items[] = SQP_Classes_ObjController::getDomain('SQP_Models_Domain_Redirect', $row );
		    }

		    usort( $items, [ $this, 'sortUrls' ] );

		    if ( count( $items ) >= $max_redirects ) {
			    // Something has gone pretty wrong at this point
			    error_log( 'Squirrly: maximum redirect limit exceeded' );
		    }
	    }

        return $items;
    }


	public function updateVisits($id, $count){
		global $wpdb;

		$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}{$this->redirects_table} SET last_count=%d, last_access=NOW() WHERE id=%d", $count, $id ) );
	}

    /**
     * Sort URLs
     *
     * @param object $first First URL.
     * @param object $second Second URL.
     * @return integer
     */
    public static function sortUrls( $first, $second ) {
        if ( $first->position === $second->position ) {
            // Fall back to which redirect was created first
            return ( $first->id < $second->id ) ? -1 : 1;
        }

        return ( $first->position < $second->position ) ? -1 : 1;
    }


}
