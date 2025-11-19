<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

/**
 * Handles the parameters and url
 *
 * @author Squirrly
 */
class SQP_Classes_Helpers_Tools {

	/**
	 * Declare the static variables in Tools
	 *
	 * @var array|mixed|object
	 */
	public static $options, $usermeta, $allplugins = array();

	public function __construct() {
		self::$options = $this->getOptions();

		SQP_Classes_ObjController::getClass( 'SQP_Classes_HookController' )->setHooks( $this );
	}

	/**
	 * This hook will save the current version in database
	 *
	 * @return void
	 */
	function hookInit() {
		//Check the updates
		$this->checkUpdate();

		//Load the languages pack
		$this->loadMultilanguage();
	}


	/**
	 * Load the Options from user option table in DB
	 *
	 * @return array
	 */
	public static function getOptions() {
		$default = array(
			'sqp_version'                => '0',
			'sq_posts_per_page'          => get_option( 'posts_per_page' ),
			//nocategry path
			'sq_nocategory'              => 0,
			'sq_nocategory_woocommerce'  => 0,
			'sq_noproduct_woocommerce'   => 0,
			'sq_noslug_woocommerce'      => 0,
			// Free Images
			'sq_media_library_images'    => false,
			// Redirects
			'sq_redirects'               => true,
			'sq_redirects_tables'        => false,
			'sq_redirects_cache'         => false,
			'sq_redirects_posts'         => true,
			'sq_redirects_track_hits'    => true,
			/// Logging
			'sq_redirects_log_hits'      => false,
			'sq_redirects_log_404'       => false,
			'sq_redirects_log_header'    => true,
			'sq_redirects_log_days'      => 7,
			'sq_redirects_log_tables'    => false,
			/// Flags
			'sq_redirects_action_code'   => '301',
			'sq_redirect_flags'          => array(
				'flag_query'    => 'pass',
				'flag_regex'    => false,
				'flag_case'     => true,
				'flag_trailing' => true,
			),
			// Rich Snippets
			'sq_rich_snippets'           => true,
			'sq_jsonld_schema_cache'     => true,
			'sq_jsonld_reusable_schemas' => false,
		);

		$options = json_decode( get_option( SQP_OPTION ), true );

		if ( is_array( $options ) ) {
			$options = @array_merge( $default, $options );
		} else {
			$options = $default;
		}

		return $options;
	}

	/**
	 * Install the required data on upgrade
	 *
	 * @return void
	 */
	public static function checkUpdate() {

		$current_version = self::getOption( 'sqp_version' );
		//do the upgrade
		if ( version_compare( $current_version, SQP_VERSION, '<' ) ) {
			//Check the nocategory plugin
			if ( get_option( 'sq_nocategory', false ) ) {
				self::$options = @array_merge( self::$options, get_option( 'sq_nocategory' ) );
				delete_option( 'sq_nocategory' );
			}

			//import from Squirrly SEO on update
			//first time it's loaded
			if ( (int) $current_version == 0 && SQP_Classes_Helpers_Tools::isSquirrlySeo() ) {
				SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_Import' )->importSquirrly();
			}

			//update the version
			self::saveOptions( 'sqp_version', SQP_VERSION );
		}

	}


	/**
	 * Get the option from database
	 *
	 * @param  $key
	 *
	 * @return mixed
	 */
	public static function getOption( $key ) {
		if ( ! isset( self::$options[ $key ] ) ) {
			self::$options = self::getOptions();

			if ( ! isset( self::$options[ $key ] ) ) {
				return false;
			}
		}

		return apply_filters( 'sqp_option_' . $key, self::$options[ $key ] );
	}


	/**
	 * Save the Options in user option table in DB
	 *
	 * @param null $key
	 * @param string $value
	 */
	public static function saveOptions( $key = null, $value = '' ) {
		if ( isset( $key ) ) {
			self::$options[ $key ] = $value;
		}

		update_option( SQP_OPTION, wp_json_encode( self::$options ) );
	}

	/**
	 * Get user metas
	 *
	 * @param null $user_id
	 *
	 * @return array
	 */
	public static function getUserMetas( $user_id = null ) {
		if ( ! isset( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		$default = array( 'sqp_auto_sticky' => 0, );

		$usermeta    = get_user_meta( $user_id );
		$usermetatmp = array();
		if ( is_array( $usermeta ) ) {
			foreach ( $usermeta as $key => $values ) {
				$usermetatmp[ $key ] = $values[0];
			}
		}
		$usermeta = $usermetatmp;

		if ( is_array( $usermeta ) ) {
			$usermeta = array_merge( (array) $default, (array) $usermeta );
		} else {
			$usermeta = $default;
		}
		self::$usermeta = $usermeta;

		return $usermeta;
	}

	/**
	 * Get use meta
	 *
	 * @param  $value
	 *
	 * @return bool
	 */
	public static function getUserMeta( $value ) {
		if ( ! isset( self::$usermeta[ $value ] ) ) {
			self::getUserMetas();
		}

		if ( isset( self::$usermeta[ $value ] ) ) {
			return apply_filters( 'sqp_usermeta_' . $value, self::$usermeta[ $value ] );
		}

		return false;
	}

	/**
	 * Save user meta
	 *
	 * @param $key
	 * @param $value
	 * @param null $user_id
	 */
	public static function saveUserMeta( $key, $value, $user_id = null ) {
		if ( ! isset( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		self::$usermeta[ $key ] = $value;
		update_user_meta( $user_id, $key, $value );
	}

	/**
	 * Delete User meta
	 *
	 * @param $key
	 * @param null $user_id
	 */
	public static function deleteUserMeta( $key, $user_id = null ) {
		if ( ! isset( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		unset( self::$usermeta[ $key ] );
		delete_user_meta( $user_id, $key );
	}

	/**
	 * Get the option from database
	 *
	 * @param  $key
	 *
	 * @return mixed
	 */
	public static function getMenuVisible( $key ) {

		if ( self::getOption( 'sqp_auto_devkit' ) ) {
			if ( ! isset( self::$options['menu'][ $key ] ) ) {
				self::$options = self::getOptions();

				if ( ! isset( self::$options['menu'][ $key ] ) ) {
					self::$options['menu'][ $key ] = false;
				}
			}

			return apply_filters( 'sqp_menu_visible', self::$options['menu'][ $key ], $key );
		}

		return true;
	}

	/**
	 * Set the header type
	 *
	 * @param string $type
	 */
	public static function setHeader( $type ) {
		if ( self::getValue( 'sq_debug' ) == 'on' ) {
			return;
		}

		switch ( $type ) {
			case 'json':
				header( 'Content-Type: application/json' );
				break;
			case 'ico':
				header( 'Content-Type: image/x-icon' );
				break;
			case 'png':
				header( 'Content-Type: image/png' );
				break;
			case'text':
				header( "Content-type: text/plain" );
				break;
			case'html':
				header( "Content-type: text/html" );
				break;
		}
	}

	/**
	 * Get a value from $_POST / $_GET
	 * if unavailable, take a default value
	 *
	 * @param string $key Value key
	 * @param mixed $defaultValue (optional)
	 * @param bool $keep_newlines
	 *
	 * @return mixed Value
	 */
	public static function getValue( $key, $defaultValue = false, $keep_newlines = false ) {
		if ( ! isset( $key ) || ( $key == '' ) ) {
			return $defaultValue;
		}

		//Get the params from forms
		if ( isset( $_SERVER['REQUEST_METHOD'] ) && $_SERVER['REQUEST_METHOD'] === 'POST' ) {
			$ret = ( isset( $_POST[ $key ] ) ? $_POST[ $key ] : ( isset( $_GET[ $key ] ) ? $_GET[ $key ] : '' ) );
		} else {
			$ret = ( isset( $_GET[ $key ] ) ? $_GET[ $key ] : '' );
		}

		//Start sanitization of each param
		//based on the type
		if ( is_array( $ret ) ) { //if it's array, sanitize each value from the array
			if ( ! empty( $ret ) ) {
				foreach ( $ret as &$row ) {
					if ( ! is_array( $row ) ) {
						$row = sanitize_text_field( $row ); //sanitize
					}
				}
			}
		} elseif ( is_string( $ret ) && $ret <> '' && $keep_newlines && function_exists( 'sanitize_textarea_field' ) ) {
			$ret = sanitize_textarea_field( $ret );
		} else {
			if(in_array($key, array('spage', 'snum', 'stype', 'sstatus', 'squery', 'skeyword', 'ssort', 'sorder'))){
				$ret = SQP_Classes_Helpers_Sanitize::sanitizeSearch( $ret );
			}
			$ret = sanitize_text_field( $ret );
		}

		if ( ! $ret ) {
			return $defaultValue;
		} else {
			return wp_unslash( $ret );
		}

	}

	/**
	 * Set the Nonce action
	 *
	 * @param  $action
	 * @param string $name
	 * @param bool $referer
	 * @param bool $echo
	 *
	 * @return string
	 */
	public static function setNonce( $action, $name = '_wpnonce', $referer = true, $echo = true ) {
		$nonce_field = '<input type="hidden" name="' . esc_attr( $name ) . '" value="' . esc_attr( wp_create_nonce( $action ) ) . '" />';

		if ( $referer ) {
			$nonce_field .= wp_referer_field( false );
		}

		if ( $echo ) {
			echo $nonce_field;
		}

		return $nonce_field;
	}

	/**
	 * Check if the parameter is set
	 *
	 * @param string $key
	 *
	 * @return boolean
	 */
	public static function getIsset( $key ) {
		return ( isset( $_GET[ $key ] ) || isset( $_POST[ $key ] ) );
	}

	/**
	 * Load the multilanguage support from .mo
	 */
	private function loadMultilanguage() {
		load_plugin_textdomain( _SQP_NAME_, false, _SQP_NAME_ . '/languages/' );
	}

	/**
	 * Hook the activate process
	 */
	public function sqp_activate() {
		//Refresh the rules for the category feature
		set_transient( 'sqp_flush_rules', true );

		//Schedule log clearing
		if ( ! wp_next_scheduled( 'sq_redirects_log_clear' ) ) {
			wp_schedule_event( time(), 'daily', 'sq_redirects_log_clear' );
		}

		/** @var SQP_Controllers_Redirects $redirects */
		$redirects = SQP_Classes_ObjController::getClass( 'SQP_Controllers_Redirects' );
		$redirects->checkTables();
	}

	/**
	 * Hook the deactivate process
	 */
	public function sqp_deactivate() {
		//remove rules
		SQP_Classes_ObjController::getClass( 'SQP_Models_Category' )->removeCategoryRules();
		set_transient( 'sqp_flush_rules', true );

		//clear the cron hook
		wp_clear_scheduled_hook( 'sq_redirects_log_clear' );
	}

	/**
	 * Check if it's Squirrly SEO installed and has the compatible version
	 *
	 * @return bool
	 */
	public static function isSquirrlySeo() {
		return ( defined( 'SQ_VERSION' ) && version_compare( SQ_VERSION, SQ_VERSION_MIN, '>=' ) );
	}

	/**
	 * Check if it's Ajax Call
	 *
	 * @return bool
	 */
	public static function isAjax() {
		return ( defined( 'DOING_AJAX' ) && DOING_AJAX );
	}

	/**
	 * Check if a plugin is installed
	 *
	 * @param  $name
	 *
	 * @return bool
	 */
	public static function isPluginInstalled( $name ) {
		if ( empty( self::$allplugins ) ) {
			self::$allplugins = (array) get_option( 'active_plugins', array() );

			if ( is_multisite() ) {
				self::$allplugins = array_merge( array_values( self::$allplugins ), array_keys( get_site_option( 'active_sitewide_plugins' ) ) );
			}
		}

		if ( ! empty( self::$allplugins ) ) {
			return in_array( $name, self::$allplugins, true );
		}

		return false;
	}

	/**
	 * Check whether the theme is active.
	 *
	 * @param string $theme Theme folder/main file.
	 *
	 * @return boolean
	 */
	public static function isThemeActive( $theme ) {
		if ( function_exists( 'wp_get_theme' ) ) {
			$themes = wp_get_theme();

			if ( isset( $themes->name ) && stripos( $themes->name, $theme ) !== false ) {
				return true;
			}

			if ( isset( $themes->template ) && stripos( $themes->template, $theme ) !== false ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if frontend and user is logged in
	 *
	 * @return bool
	 */
	public static function isFrontAdmin() {
		return ( ! is_admin() && ( function_exists( 'is_user_logged_in' ) && is_user_logged_in() ) );
	}

	/**
	 * Check if user is in dashboard
	 *
	 * @return bool
	 */
	public static function isBackedAdmin() {
		return ( is_admin() || is_network_admin() );
	}

	/**
	 * Check if the current website is an E-commerce website
	 *
	 * @return bool
	 */
	public static function isEcommerce() {

		if ( self::isPluginInstalled( 'woocommerce/woocommerce.php' ) ) {
			return true;
		}

		$products   = array( 'product', 'wpsc-product' );
		$post_types = get_post_types( array( 'public' => true ) );

		foreach ( $products as $type ) {
			if ( in_array( $type, array_keys( $post_types ) ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if it's an AMP Endpoint
	 *
	 * @return bool|void
	 */
	public static function isAMPEndpoint() {
		if ( defined( 'AMPFORWP_AMP_QUERY_VAR' ) ) {
			$url_path     = trim( parse_url( add_query_arg( array() ), PHP_URL_PATH ), '/' );
			$explode_path = explode( '/', $url_path );
			if ( AMPFORWP_AMP_QUERY_VAR === end( $explode_path ) ) {
				return true;
			}
		}

		if ( function_exists( 'is_amp_endpoint' ) ) {
			return is_amp_endpoint();
		}

		if ( function_exists( 'is_amp' ) && is_amp() ) {
			return is_amp();
		}

		if ( function_exists( 'ampforwp_is_amp_endpoint' ) ) {
			return ampforwp_is_amp_endpoint();
		}

		return false;
	}

	/**
	 * Get the current REST API
	 *
	 * @param boolean $type Override with a specific API type.
	 *
	 * @return string
	 */
	public static function getRestApi( $type = false ) {
		return get_rest_url();
	}

	/**
	 * Check the user capability for the roles attached
	 *
	 * @param  $cap
	 * @param mixed ...$args
	 *
	 * @return bool
	 */
	public static function userCan( $cap, ...$args ) {

		if ( current_user_can( $cap, ...$args ) ) {
			return true;
		}

		$user = wp_get_current_user();
		if ( count( (array) $user->roles ) > 1 ) {
			foreach ( $user->roles as $role ) {
				$role_object = get_role( $role );
				if ( $role_object->has_cap( $cap ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Get the admin url for the specific age
	 *
	 * @param string $page
	 * @param string $tab
	 * @param array $args
	 *
	 * @return string
	 */
	public static function getAdminUrl( $page, $tab = null, $args = array() ) {
		if ( strpos( $page, '.php' ) ) {
			$url = admin_url( $page );
		} else {
			$url = admin_url( 'admin.php?page=' . $page );
		}

		if ( isset( $tab ) && $tab <> '' ) {
			$url .= '&tab=' . $tab;
		}

		if ( ! empty( $args ) ) {
			if ( strpos( $url, '?' ) !== false ) {
				$url .= '&';
			} else {
				$url .= '?';
			}
			$url .= join( '&', $args );
		}

		return apply_filters( 'sq_menu_url', $url, $page, $tab, $args );
	}

	/**
	 * Add pagination to tables
	 *
	 * @param $max_page
	 *
	 * @return void
	 */
	public static function pagination( $max_page = 1 ) {
		$pagination = '';
		$paged      = (int) SQP_Classes_Helpers_Tools::getValue( 'spage', 1 );

		$pages = paginate_links( array(
			'base'      => '%_%',
			'format'    => '?spage=%#%',
			'current'   => $paged,
			'total'     => $max_page,
			'mid_size'  => 1,
			'prev_text' => esc_html__( "Previous", 'squirrly-seo-pack' ),
			'next_text' => esc_html__( "Next", 'squirrly-seo-pack' ),
			'type'      => 'array'
		) );

		if ( is_array( $pages ) && ! empty( $pages ) ) {

			$pagination = '<ul class="pagination">';

			foreach ( $pages as $page ) {
				$pagination .= "<li class='page-item nav-links'>$page</li>";
			}

			$pagination .= '</ul>';

		}

		echo $pagination;
	}

	/**
	 * Add the sorting option to Squirrly SEO tables
	 *
	 * @param $title
	 * @param $sort
	 * @param $order
	 *
	 * @return void
	 */
	public static function sort( $title, $sort, $order = 'asc' ) {

		$icon = '';

		if ( class_exists( 'SQ_Classes_Helpers_Tools' ) && $sort == SQ_Classes_Helpers_Tools::getValue( 'ssort' ) ) {
			$order = ( SQ_Classes_Helpers_Tools::getValue( 'sorder' ) == 'asc' ? 'desc' : 'asc' );
			$icon  = '<i class="ml-1 fa-solid ' . ( $order == 'asc' ? 'fa-sort-down' : 'fa-sort-up' ) . '" style="font-size: 10px !important;"></i>';
		}

		$sorting = '<a href="' . esc_url( add_query_arg( array(
				'ssort'  => $sort,
				'sorder' => $order
			) ) ) . '" class="text-white">';
		$sorting .= $title . $icon;
		$sorting .= '</a>';

		echo $sorting;
	}

	/**
	 * Instantiates the WordPress filesystem.
	 *
	 * @static
	 * @access public
	 * @return object
	 */
	public static function initFilesystem() {
		// The WordPress filesystem.
		global $wp_filesystem;

		if ( ! function_exists( 'WP_Filesystem' ) ) {
			include_once ABSPATH . 'wp-admin/includes/file.php';
		}

		WP_Filesystem();

		if ( ! $wp_filesystem->connect() ) {
			add_filter( 'filesystem_method', function( $method ) {
				return 'direct';
			}, 1 );
			WP_Filesystem();
		}

		return $wp_filesystem;
	}

	/**
	 * Get the first key of the given array without affecting
	 * the internal array pointer
	 *
	 * @param array $arr
	 *
	 * @return int|string|void
	 */
	public static function arrayKeyFirst( array $arr ) {
		foreach ( $arr as $key => $value ) {
			return $key;
		}
	}
}
