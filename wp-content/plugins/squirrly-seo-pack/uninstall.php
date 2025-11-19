<?php defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );


/**
 * Called on plugin uninstall
 */
if ( ! defined( 'ABSPATH' ) && ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {

	try {

		/* Call config files */
		include dirname( __FILE__ ) . '/config/config.php';
		include dirname( __FILE__ ) . '/config/paths.php';
		include_once _SQP_CLASSES_DIR_ . 'ObjController.php';

		add_action( 'wp_loaded', function() {
			global $wp_rewrite;
			$wp_rewrite->flush_rules();
		} );

		/* Delete the record from database */
		SQP_Classes_ObjController::getClass( 'SQP_Classes_Helpers_Tools' );
		if ( SQP_Classes_Helpers_Tools::getOption( 'sq_complete_uninstall' ) ) {
			delete_option( SQP_IMAGES );

			SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_Admin' )->deleteTable();
			SQP_Classes_ObjController::getClass( 'SQP_Models_Jsonld_Database' )->deleteTable();
			SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_Log' )->deleteTable();
		}

	} catch ( Exception $e ) {
	}

}
