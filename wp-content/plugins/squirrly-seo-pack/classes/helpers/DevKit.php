<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Classes_Helpers_DevKit {

	public static $plugin;
	public static $options;
	public static $package;

	public function __construct() {
	}

	public static function getOptions() {
		if ( is_multisite() ) {
			self::$options = json_decode( get_blog_option( get_main_site_id(), SQP_OPTION ), true );
		} else {
			self::$options = json_decode( get_option( SQP_OPTION ), true );
		}

		return self::$options;
	}

}
