<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Menu {

	public function getMainMenu() {
		return array(
			'sq_seosettings' => array(),
			'sq_redirects'   => array(
				'title'      => esc_html__( "Redirects", 'squirrly-seo-pack' ),
				'parent'     => 'sq_dashboard',
				'capability' => 'edit_posts',
				'function'   => array( SQP_Classes_ObjController::getClass( 'SQP_Controllers_Redirects' ), 'init' ),
				'href'       => false,
				'icon'       => 'dashicons-before dashicons-leftright',
				'topmenu'    => ( SQP_Classes_Helpers_Tools::getOption( 'sq_redirects' ) && SQP_Classes_Helpers_Tools::getMenuVisible( 'show_redirects' ) ),
				'leftmenu'   => ( SQP_Classes_Helpers_Tools::getOption( 'sq_redirects' ) && SQP_Classes_Helpers_Tools::getMenuVisible( 'show_redirects' ) ),
				'fullscreen' => true
			),
			'sq_jsonld'      => array(
				'title'      => esc_html__( "Reusable Schemas", 'squirrly-seo-pack' ),
				'parent'     => 'sq_dashboard',
				'capability' => 'edit_posts',
				'function'   => array( SQP_Classes_ObjController::getClass( 'SQP_Controllers_Jsonld' ), 'init' ),
				'href'       => false,
				'icon'       => 'fa-solid fa-barcode-read',
				'topmenu'    => ( SQP_Classes_Helpers_Tools::getOption( 'sq_rich_snippets' ) && SQP_Classes_Helpers_Tools::getOption( 'sq_jsonld_reusable_schemas' ) && SQP_Classes_Helpers_Tools::getMenuVisible( 'show_reusables' ) ),
				'leftmenu'   => ( SQP_Classes_Helpers_Tools::getOption( 'sq_rich_snippets' ) && SQP_Classes_Helpers_Tools::getOption( 'sq_jsonld_reusable_schemas' ) && SQP_Classes_Helpers_Tools::getMenuVisible( 'show_reusables' ) ),
				'fullscreen' => true
			),
		);

	}

	/**
	 * Get the admin Menu Tabs
	 *
	 * @param string $category
	 *
	 * @return array
	 */
	public function getTabs( $category ) {
		$tabs = array();

		$tabs['sq_seosettings'] = array(
			'sq_seosettings/category' => array(
				'title'      => esc_html__( "No Categories", 'squirrly-seo-pack' ),
				'capability' => 'sq_manage_settings',
				'show'       => true,
				'icon'       => 'fa-solid fa-folder'
			),
		);

		$tabs['sq_redirects'] = array(
			'sq_redirects/rules'    => array(
				'title'      => esc_html__( "Rules", 'squirrly-seo-pack' ),
				'capability' => 'sq_manage_settings',
				'show'       => true,
				'icon'       => 'dashicons-before dashicons-leftright'
			),
			'sq_redirects/log'      => array(
				'title'      => esc_html__( "Logs", 'squirrly-seo-pack' ),
				'capability' => 'sq_manage_settings',
				'show'       => ( SQP_Classes_Helpers_Tools::getOption( 'sq_redirects_log_hits' ) || SQP_Classes_Helpers_Tools::getOption( 'sq_redirects_log_404' ) ),
				'icon'       => 'dashicons-before dashicons-backup'
			),
			'sq_seosettings/backup' => array(
				'title'      => esc_html__( "Import & Data", 'squirrly-seo-pack' ),
				'capability' => 'sq_manage_settings',
				'show'       => true,
				'icon'       => 'fa-solid fa-arrow-up-from-bracket'
			),
			'sq_redirects/settings' => array(
				'title'      => esc_html__( "Settings", 'squirrly-seo-pack' ),
				'capability' => 'sq_manage_settings',
				'show'       => true,
				'icon'       => 'dashicons-before dashicons-admin-settings',
			),
		);

		$tabs['sq_jsonld'] = array(
			'sq_jsonld/reusable'    => array(
				'title'      => esc_html__( "Schemas", 'squirrly-seo-pack' ),
				'capability' => 'sq_manage_settings',
				'show'       => true,
				'icon'       => 'fa-solid fa-barcode-read'
			),
			'sq_seosettings/backup' => array(
				'title'      => esc_html__( "Import & Data", 'squirrly-seo-pack' ),
				'capability' => 'sq_manage_settings',
				'show'       => true,
				'icon'       => 'fa-solid fa-arrow-up-from-bracket'
			),
			'sq_seosettings/jsonld' => array(
				'title'      => esc_html__( "Settings", 'squirrly-seo-pack' ),
				'capability' => 'sq_manage_settings',
				'show'       => true,
				'icon'       => 'dashicons-before dashicons-admin-settings',
			),
		);

		return $tabs[ $category ];

	}

	public function loadPackFeatures( $features ) {

		if ( ! in_array( "Redirects", array_column( $features, 'title' ) ) ) {
			$features[] = array(
				'title'       => "Redirects" ,
				'description' => "Take control of your website's redirects by managing all of your 301, 302, and 307 redirects for both posts and pages. Keep track of the hits on your redirects with monitoring capabilities.",
				'category'    => "Miscellaneous Features",
				'mainfeature' => false,
				'option'      => 'sq_redirects',
				'active'      => SQP_Classes_Helpers_Tools::getOption( 'sq_redirects' ),
				'optional'    => true,
				'connection'  => false,
				'logo'        => 'dashicons-before dashicons-leftright',
				'link'        => SQP_Classes_Helpers_Tools::getAdminUrl( 'sq_redirects', 'rules' ),
				'details'     => '',
				'show'        => true,
				'keywords'    => 'redirect,301,404,broken,links,path'
			);
		}

		if ( ! in_array( "404 Monitor", array_column( $features, 'title' ) ) ) {
			$features[] = array(
				'title'       => "404 Monitor"  ,
				'description' => "Keep a record of the URLs that visitors and search engines encounter 404 errors on. Additionally, you have the option to enable redirections to redirect the URLs causing the errors to other URLs.",
				'category'    => "Miscellaneous Features",
				'mainfeature' => false,
				'option'      => 'sq_redirects_log_404',
				'active'      => SQP_Classes_Helpers_Tools::getOption( 'sq_redirects_log_404' ),
				'optional'    => true,
				'connection'  => false,
				'logo'        => 'fa-solid fa-bolt',
				'link'        => SQP_Classes_Helpers_Tools::getAdminUrl( 'sq_redirects', 'log' ),
				'details'     => '',
				'show'        => true,
				'keywords'    => 'redirect,301,404,broken,links,monitor'
			);
		}

		if ( ! in_array( "No Category Base", array_column( $features, 'title' ) ) ) {
			$features[] = array(
				'title'       => "No Category Base",
				'description' => "Make your category URLs more aesthetically appealing, more intuitive, as well as easier to understand and remember by site visitors.",
				'category'    => "Miscellaneous Features",
				'mainfeature' => false,
				'option'      => 'sq_nocategory',
				'active'      => SQP_Classes_Helpers_Tools::getOption( 'sq_nocategory' ),
				'optional'    => true,
				'connection'  => false,
				'logo'        => 'fa-solid fa-bolt',
				'link'        => SQP_Classes_Helpers_Tools::getAdminUrl( 'sq_seosettings', 'category' ),
				'details'     => 'https://howto12.squirrly.co/ht_kb/how-to-remove-the-category-base-from-wordpress/',
				'show'        => true,
				'keywords'    => 'categories,category,path'
			);
		}

		if ( ! in_array( "Custom Rich Snippets", array_column( $features, 'title' ) ) ) {
			$features[] = array(
				'title'       => "Custom Rich Snippets" ,
				'description' => "Elevate visibility and clicks with personalized Custom Rich Snippets in search results.",
				'category'    => "Miscellaneous Features",
				'mainfeature' => false,
				'option'      => 'sq_rich_snippets',
				'active'      => SQP_Classes_Helpers_Tools::getOption( 'sq_rich_snippets' ),
				'optional'    => true,
				'connection'  => false,
				'logo'        => 'fa-solid fa-barcode-read',
				'link'        => SQP_Classes_Helpers_Tools::getAdminUrl( 'sq_seosettings', 'jsonld' ),
				'details'     => '',
				'show'        => true,
				'keywords'    => 'json,jsonld,snippet,rich,snippets,schema,schemas'
			);
		}

		if ( ! in_array( "Reusable Schemas", array_column( $features, 'title' ) ) ) {
			$features[] = array(
				'title'       => "Reusable Schemas" ,
				'description' => "The <strong>Reusable Schema</strong> simplifies Rich Snippets schemas to website content by enabling the creation of templates that match custom fields and post types, which can then be automatically applied to enhance search engine results.",
				'category'    => "Miscellaneous Features",
				'mainfeature' => false,
				'option'      => 'sq_jsonld_reusable_schemas',
				'active'      => SQP_Classes_Helpers_Tools::getOption( 'sq_jsonld_reusable_schemas' ),
				'optional'    => true,
				'connection'  => false,
				'logo'        => 'fa-solid fa-barcode-read',
				'link'        => SQP_Classes_Helpers_Tools::getAdminUrl( 'sq_jsonld', 'reusable' ),
				'details'     => '',
				'show'        => true,
				'keywords'    => 'json,jsonld,snippet,rich,snippets,schema,schemas,reusable'
			);
		}


		if ( false !== $found = array_search( "Copyright Free Images", array_column( $features, 'title' ) ) ) {
			unset( $features[ $found ] );
		}

		$features[] = array(
			'title'       => "Copyright Free Images" ,
			'description' => "Search <strong>Copyright Free Images</strong> in Media Library and download them directly on your content.",
			'category'    => "Unique SEO Features",
			'mainfeature' => false,
			'option'      => 'sq_media_library_images',
			'active'      => SQP_Classes_Helpers_Tools::getOption( 'sq_media_library_images' ),
			'optional'    => true,
			'connection'  => true,
			'logo'        => 'fa-solid fa-image',
			'link'        => SQP_Classes_Helpers_Tools::getAdminUrl( 'sq_assistant', 'settings' ),
			'details'     => 'https://howto12.squirrly.co/kb/squirrly-live-assistant/#copyright_free_images',
			'show'        => SQP_Classes_Helpers_Tools::getMenuVisible( 'show_assistant' ),
			'keywords'    => 'image,pixabay,free,media'
		);


		return $features;
	}
}
