<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Controllers_Backend extends SQP_Classes_FrontController {

	public function __construct() {

		parent::__construct();

		//Load free images in Media Library
		SQP_Classes_ObjController::getClass( 'SQP_Models_Images' );
		//Load no category
		SQP_Classes_ObjController::getClass( 'SQP_Models_Category' );

		//Show Backup, Restore and Import options for Redirects
		$this->showBackupRestoreRedirects();
		//Show Backup, Restore and Import options for Redirects
		$this->showBackupRestoreJsonLd();
	}

	/**
	 * Load the restore options in Squirrly Import & Data
	 *
	 * @return void
	 */
	private function showBackupRestoreJsonLd() {
		if ( SQP_Classes_Helpers_Tools::getOption( 'sq_rich_snippets' ) ) {

			add_action( 'sq_backup_import_after', function() {
				add_filter( 'sq_jsonld_import', array(
					SQP_Classes_ObjController::getClass( 'SQP_Models_Jsonld_Import' ),
					'getAllPlugins'
				) );
				$this->show_view( 'Blocks/Jsonld/Import' );
			} );

			add_action( 'sq_backup_backup_after', function() {
				$this->show_view( 'Blocks/Jsonld/Backup' );
			} );

			add_action( 'sq_backup_restore_after', function() {
				$this->show_view( 'Blocks/Jsonld/Restore' );
			} );

		}
	}

	/**
	 * Load the restore options in Squirrly Import & Data
	 *
	 * @return void
	 */
	private function showBackupRestoreRedirects() {
		if ( SQP_Classes_Helpers_Tools::getOption( 'sq_redirects' ) ) {

			add_action( 'sq_backup_import_after', function() {
				add_filter( 'sq_redirects_import', array(
					SQP_Classes_ObjController::getClass( 'SQP_Models_Redirects_Import' ),
					'getAllPlugins'
				) );
				$this->show_view( 'Blocks/Redirects/Import' );
			} );

			add_action( 'sq_backup_backup_after', function() {
				$this->show_view( 'Blocks/Redirects/Backup' );
			} );

			add_action( 'sq_backup_restore_after', function() {
				$this->show_view( 'Blocks/Redirects/Restore' );
			} );

		}
	}

}
