<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Domain_Redirect extends SQP_Models_Abstract_Domain {

	protected $_id;
	protected $_url;
	protected $_match_url;
	protected $_match_data;
	protected $_regex;
	protected $_action_data;
	protected $_action_code;
	protected $_action_type;
	protected $_match_type;
	protected $_last_access;
	protected $_last_count;
	protected $_status;
	protected $_position;
	protected $_group_id;
	protected $_source_options;
	protected $_match;
	protected $_action;

	/**
	 * @var SQP_Models_Redirects_Flags
	 */
	protected $_source_flags;

	public function setId( $id ) {
		$this->_id = $id;
	}

	public function setUrl( $url ) {
		$this->_url = $url;
	}

	/**
	 * Determine if a requested URL matches this URL
	 *
	 * @param string $requested_url The URL being requested (decoded).
	 * @param string|false $original_url The URL being requested (not decoded).
	 *
	 * @return SQP_Models_Redirects_ActionHandle|false true if matched, false otherwise
	 */
	public function checkMatch( $requested_url, $original_url = false ) {

		if ( ! $this->isEnabled() || ! $this->action ) {
			return false;
		}

		if ( $original_url === false ) {
			$original_url = $requested_url;
		}

		//If there is a slug change, match the basename of the redirect
		if ( $this->isSlugRedirect() ) {

			// Does the URL match? This may not be the case for regular expressions
			if ( ! $this->isOldSlugMatch( $requested_url, $this->source_flags ) ) {
				return false;
			}

		} elseif ( ! $this->isMatch( $requested_url, $this->source_flags ) ) {
			// Does the URL match? This may not be the case for regular expressions
			return false;
		};
		// Does the action need a target (URL)?
		if ( $this->action->needsTarget() ) {

			//if there is a slug redirect
			if ( $this->isSlugRedirect() && $this->getCurrentPostId() > 0 ) {
				//get the current post URL together with the permalink setup
				$target_url = get_permalink( $this->getCurrentPostId() );
			} else {
				// Get the target from the action and the match status - some matches have a matched/unmatched target
				$target_url = $this->match->getTargetUrl( $original_url, $this->url, $this->source_flags );
			}

			if ( $target_url ) {
				/**
				 * @var SQP_Models_Redirects_Query $query
				 */
				$query = SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_Query' );

				$target_url = $query->addToTarget( $target_url, $original_url, $this->source_flags );
			}


			// Allow plugins a look
			$target_url = apply_filters( 'sq_url_target', $target_url, $this->url );

			// Do we still have a target?
			if ( ! $target_url ) {
				// No, return early and move on to the next redirect. This could be a matched/unmatched target that has no value
				return false;
			}

			// Set this in the action
			$this->action->setTarget( $target_url );

			// Fire an action to let people know
			do_action( 'sq_redirect_visit', $this, $original_url, $target_url );
		}

		// Return the action for processing
		// SQP_Models_Redirects_ActionHandle
		return $this->action;
	}

	public function getCurrentPostId() {
		$options = $this->source_flags->getJson();

		if ( (int) $options['flag_post'] > 0 ) {
			return (int) $options['flag_post'];
		}

		return 0;
	}

	/**
	 * Check if there is a slug redirect or a post redirect
	 *
	 * @return bool
	 */
	public function isSlugRedirect() {
		/** @var SQP_Controllers_Redirects $redirects */
		$redirects = SQP_Classes_ObjController::getClass( 'SQP_Controllers_Redirects' );
		$group_id  = $redirects->getGroupIds( 'slug' ); //slug change redirect

		return $this->group_id == $group_id;
	}

	/**
	 * Register a visit against this redirect
	 *
	 * @param String $url Full URL that is visited, including query parameters.
	 * @param String|true $target Target URL, if appropriate.
	 *
	 * @return void
	 */
	public function visit( $url, $target ) {

		$track_hits = SQP_Classes_Helpers_Tools::getOption( 'sq_redirects_track_hits' );
		$log_hits   = SQP_Classes_Helpers_Tools::getOption( 'sq_redirects_log_hits' );
		$log_header = SQP_Classes_Helpers_Tools::getOption( 'sq_redirects_log_header' );

		// Update the counters
		if ( apply_filters( 'sq_redirect_counter', $track_hits, $url ) ) {
			if ( $log_hits && SQP_Classes_Helpers_Tools::getOption( 'sq_redirects_cache' ) ) {
				$count = SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_Log' )->countVisits( $this->id );
				SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_Database' )->updateVisits( $this->id, ( $count + 1 ) );
			} else {
				SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_Database' )->updateVisits( $this->id, ( $this->last_count + 1 ) );
			}
		}

		if ( $target && $log_hits ) {

			if ( $target === true && $this->match ) {
				$target = $this->action_type === 'pass' ? $this->match->getData()['url'] : '';
			}

			/**
			 * @var SQP_Models_Redirects_Request $request
			 */
			$request = SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_Request' );

			$data = [
				'url'            => $url,
				'sent_to'        => $target,
				'domain'         => $request->getServer(),
				'agent'          => $request->getUserAgent(),
				'referrer'       => $request->getReferrer(),
				'http_code'      => $this->getActionCode(),
				'request_method' => $request->getRequestMethod(),
				'redirect_by'    => 'squirrly',
				'redirection_id' => $this->id,
				'ip'             => $request->getIp(),
			];

			if ( $log_header ) {
				$data['request_data'] = [
					'headers' => $request->getRequestHeaders(),
				];
			}

			/**
			 * @var SQP_Models_Redirects_Log $log
			 */
			$log  = SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_Log' );
			$item = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Log', $data );
			$log->addLogRow( $item );
		}
	}

	/**
	 * Load the match library for URL matching
	 *
	 * @return SQP_Models_Redirects_MatchHandle
	 */
	public function getMatch() {

		$this->_match = SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_MatchHandle' );
		$this->_match->init( $this->action_data );

		return $this->_match;
	}

	/**
	 * Load the  object
	 *
	 * @return SQP_Models_Redirects_ActionHandle
	 */
	public function getAction() {

		$this->_action = SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_ActionHandle' );
		$this->_action->init( $this->action_type, $this->action_code );

		return $this->_action;
	}

	/**
	 * Get action code
	 *
	 * @return integer
	 */
	public function getActionCode() {
		return intval( $this->action_code, 10 );
	}

	/**
	 * Load the SQP_Models_Redirects_Flags.
	 *
	 * @return SQP_Models_Redirects_Flags
	 */
	public function getSource_flags() {

		// Default regex flag to regex column. This will be removed once the regex column has been migrated
		$this->_source_flags = SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_Flags' );
		$this->_source_flags->setFlags( array( 'flag_regex' => $this->regex ) );

		if ( isset( $this->match_data ) ) {

			if ( is_string( $this->match_data ) ) {
				$option = json_decode( $this->match_data, true );
			} else {
				$option = $this->match_data;
			}

			if ( $option && isset( $option['source'] ) ) {
				// Merge redirect flags with default flags
				$this->_source_flags->setFlags( $option['source'] );
			}

		}

		return $this->_source_flags;
	}

	/**
	 * Match a target URL against the current URL, using any match flags
	 *
	 * @param string $requested_url Target URL.
	 * @param SQP_Models_Redirects_Flags $flags Match flags.
	 *
	 * @return boolean
	 */
	public function isMatch( $requested_url, $flags ) {

		if ( $flags->isRegex() ) {
			/**
			 * @var SQP_Models_Redirects_Regex $regex
			 */
			$regex = SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_Regex' );
			$regex->init( $this->_url, $flags->isIgnoreCase() );

			return $regex->isMatch( $requested_url );
		}

		/**
		 * @var SQP_Models_Redirects_PathHandle $path
		 */
		$path = SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_PathHandle' );
		$path->init( $this->_url );

		/**
		 * @var SQP_Models_Redirects_Query $query
		 */
		$query = SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_Query' );
		$query->init( $this->url, $flags );

		//check the path and queries
		return $path->isMatch( $requested_url, $flags ) && $query->isMatch( $requested_url, $flags );

	}

	public function isOldSlugMatch( $requested_url, $flags ) {

		/**
		 * @var SQP_Models_Redirects_SlugHandle $slug
		 */
		$slug = SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_SlugHandle' );
		$slug->init( $this->_url );

		return $slug->isMatch( $requested_url, $flags );

	}

	/**
	 * Check if the url status is enabled
	 *
	 * @return bool
	 */
	public function isEnabled() {
		return $this->status === 'enabled';
	}

	public function toArray() {
		return array(
			'id'          => $this->id,
			'url'         => $this->url,
			'match_url'   => $this->match_url,
			'match_data'  => $this->match_data,
			'regex'       => $this->regex,
			'position'    => $this->position,
			'last_count'  => (int) $this->last_count,
			'last_access' => $this->last_access,
			'group_id'    => (int) $this->group_id,
			'status'      => $this->status,
			'action_type' => $this->action_type,
			'action_code' => (int) $this->action_code,
			'action_data' => $this->action_data,
			'match_type'  => $this->match_type
		);
	}

}
