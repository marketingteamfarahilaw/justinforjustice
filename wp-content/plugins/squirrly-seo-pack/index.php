<?php defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

/*
 * Copyright (c) 2025, Squirrly.
 * The copyrights to the software code in this file are licensed under the (revised) BSD open source license.

 * Plugin Name: Squirrly SEO - Advanced Pack
 * Plugin URI: https://wordpress.org/plugins/squirrly-seo/
 * Description: Advanced Pack for Squirrly SEO
 * Author: Squirrly
 * Author URI: https://plugin.squirrly.co
 * Version: 2.4.03
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain: squirrly-seo-pack
 * Domain Path: /languages
 * Requires at least: 5.3
 * Tested up to: 6.8
 * Requires PHP: 7.0
 */

if ( ! defined( 'SQP_VERSION' ) ) {

	/* SET THE CURRENT VERSION ABOVE AND BELOW */
	define( 'SQP_VERSION', '2.4.03' );
	define( 'SQ_VERSION_MIN', '12.3.02' );

	try {
		// Call config files
		include_once dirname( __FILE__ ) . '/config/config.php';

		/* important to check the PHP version */
		// import main classes
		include_once _SQP_CLASSES_DIR_ . 'ObjController.php';

		// Load helpers
		SQP_Classes_ObjController::getClass( 'SQP_Classes_Helpers_Tools' );
		SQP_Classes_ObjController::getClass( 'SQP_Classes_Helpers_Sanitize' );
		// Load the Front and Block controller
		SQP_Classes_ObjController::getClass( 'SQP_Classes_FrontController' );
		// Load no category
		SQP_Classes_ObjController::getClass( 'SQP_Controllers_Category' );
		// Load JsonLd support
		SQP_Classes_ObjController::getClass( 'SQP_Controllers_Jsonld' );
		// Listen to other plugins, listen cron
		SQP_Classes_ObjController::getClass( 'SQP_Models_Watcher' );

		if ( SQP_Classes_Helpers_Tools::isBackedAdmin() ) {

			//Run the backend classes from Advanced Pack
			SQP_Classes_ObjController::getClass( 'SQP_Classes_FrontController' )->runAdmin();

			// Upgrade Squirrly call.
			register_activation_hook( __FILE__, array(
				SQP_Classes_ObjController::getClass( 'SQP_Classes_Helpers_Tools' ),
				'sqp_activate'
			) );
			register_deactivation_hook( __FILE__, array(
				SQP_Classes_ObjController::getClass( 'SQP_Classes_Helpers_Tools' ),
				'sqp_deactivate'
			) );

			//Request the plugin update when a new version is released
			require dirname( __FILE__ ) . '/update.php';

		} else {

			if ( defined( 'WP_CLI' ) && WP_CLI ) {
				return;
			}

			SQP_Classes_ObjController::getClass( 'SQP_Classes_FrontController' )->runFrontend();
		}


	} catch ( Exception $e ) {
	}

}