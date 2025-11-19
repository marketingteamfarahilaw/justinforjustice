<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Images {

	public function __construct() {
		if ( SQP_Classes_Helpers_Tools::getOption( 'sq_media_library_images' ) ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'hookEnqueueScrips' ), 99 );
			add_action( 'wp_enqueue_scripts', array( $this, 'hookMediaLibraryFrontend' ) );
			add_action( 'elementor/editor/footer', array( $this, 'hookMediaLibrary' ) );
			add_action( 'elementor/editor/footer', array( $this, 'addImageTemplates' ) );
			add_action( 'admin_footer', array( $this, 'addImageTemplates' ) );
			add_action( 'customize_controls_print_footer_scripts', array( $this, 'addImageTemplates' ) );
		}

		add_action( 'sq_live_assistant_settings', function() {
			return SQP_Classes_ObjController::getClass( 'SQP_Controllers_MediaLibrary' )->show_view( 'SeoSettings/Images' );
		} );

	}

	public function hookEnqueueScrips( $hook = '' ) {

		// Image Search assets.
		if ( 'post-new.php' === $hook || 'post.php' === $hook || 'widgets.php' === $hook ) {
			$this->hookMediaLibrary();
		}

	}

	/**
	 * Enqueue Image Search scripts into Beaver Builder Editor.
	 *
	 * @return void
	 * @since  2.0.0
	 */
	public function hookMediaLibraryFrontend() {

		if ( class_exists( 'FLBuilderModel' ) && FLBuilderModel::is_builder_active() // BB Builder is on?
		     || ( class_exists( 'Brizy_Editor_Post' ) && // Brizy Builder is on?
		          ( isset( $_GET['brizy-edit'] ) || isset( $_GET['brizy-edit-iframe'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Fetching GET parameter, no nonce associated with this action.
		     ) || is_customize_preview() // Is customizer on?
		) {
			// Image Search assets.
			$this->hookMediaLibrary();
		}
	}

	/**
	 * Insert Template
	 *
	 * @return void
	 */
	public function addImageTemplates() {
		if ( defined( '_SQ_NONCE_ID_' ) ) {
			echo '<script>var sqQuery = {"adminurl": "' . esc_url( admin_url() ) . '","ajaxurl": "' . esc_url( admin_url( 'admin-ajax.php' ) ) . '","adminposturl": "' . esc_url( admin_url( 'post.php' ) ) . '","adminlisturl": "' . esc_url( admin_url( 'edit.php' ) ) . '","nonce": "' . esc_attr( wp_create_nonce( _SQ_NONCE_ID_ ) ) . '"}</script>';
			SQP_Classes_ObjController::getClass( 'SQP_Controllers_MediaLibrary' )->show_view( 'Blocks/ImageTemplates' );
		}

	}

	/**
	 * Load Free Image search in Media Library
	 *
	 * @return void
	 */
	public function hookMediaLibrary() {

		wp_enqueue_script( 'masonry' );
		wp_enqueue_script( 'imagesloaded' );

		$data = apply_filters( 'sqp_jsonld_media_library', array(
				'is_astra_active'     => ( SQP_Classes_Helpers_Tools::isPluginInstalled( 'astra-sites/astra-sites.php' ) ),
				'is_bb_active'        => ( SQP_Classes_Helpers_Tools::isPluginInstalled( 'beaver-builder-lite-version/fl-builder.php' ) || SQP_Classes_Helpers_Tools::isPluginInstalled( 'beaver-builder/fl-builder.php' ) ),
				'is_brizy_active'     => ( SQP_Classes_Helpers_Tools::isPluginInstalled( 'brizy/brizy.php' ) ),
				'is_elementor_active' => ( SQP_Classes_Helpers_Tools::isPluginInstalled( 'elementor/elementor.php' ) ),
				'is_elementor_editor' => ( did_action( 'elementor/loaded' ) ) ? ( class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::instance()->editor->is_edit_mode() ? true : false ) : false,
				'is_bb_editor'        => ( ( SQP_Classes_Helpers_Tools::isPluginInstalled( 'beaver-builder-lite-version/fl-builder.php' ) || SQP_Classes_Helpers_Tools::isPluginInstalled( 'beaver-builder/fl-builder.php' ) ) && class_exists( 'FLBuilderModel' ) ) ? ( FLBuilderModel::is_builder_active() ) : false,
				'is_brizy_editor'     => ( SQP_Classes_Helpers_Tools::isPluginInstalled( 'brizy/brizy.php' ) && class_exists( 'Brizy_Editor_Post' ) ) ? ( isset( $_GET['brizy-edit'] ) || isset( $_GET['brizy-edit-iframe'] ) ) : false,
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Fetching GET parameter, no nonce associated with this action.
				'saved_images'        => get_option( SQP_IMAGES, array() ),
				'pixabay_category'    => array(
					'all'            => __( 'All', 'squirrly-seo-pack' ),
					'animals'        => __( 'Animals', 'squirrly-seo-pack' ),
					'buildings'      => __( 'Architecture/Buildings', 'squirrly-seo-pack' ),
					'backgrounds'    => __( 'Backgrounds/Textures', 'squirrly-seo-pack' ),
					'fashion'        => __( 'Beauty/Fashion', 'squirrly-seo-pack' ),
					'business'       => __( 'Business/Finance', 'squirrly-seo-pack' ),
					'computer'       => __( 'Computer/Communication', 'squirrly-seo-pack' ),
					'education'      => __( 'Education', 'squirrly-seo-pack' ),
					'feelings'       => __( 'Emotions', 'squirrly-seo-pack' ),
					'food'           => __( 'Food/Drink', 'squirrly-seo-pack' ),
					'health'         => __( 'Health/Medical', 'squirrly-seo-pack' ),
					'industry'       => __( 'Industry/Craft', 'squirrly-seo-pack' ),
					'music'          => __( 'Music', 'squirrly-seo-pack' ),
					'nature'         => __( 'Nature/Landscapes', 'squirrly-seo-pack' ),
					'people'         => __( 'People', 'squirrly-seo-pack' ),
					'places'         => __( 'Places/Monuments', 'squirrly-seo-pack' ),
					'religion'       => __( 'Religion', 'squirrly-seo-pack' ),
					'science'        => __( 'Science/Technology', 'squirrly-seo-pack' ),
					'sports'         => __( 'Sports', 'squirrly-seo-pack' ),
					'transportation' => __( 'Transportation/Traffic', 'squirrly-seo-pack' ),
					'travel'         => __( 'Travel/Vacation', 'squirrly-seo-pack' ),
				),
				'pixabay_order'       => array(
					'popular'  => __( 'Popular', 'squirrly-seo-pack' ),
					'latest'   => __( 'Latest', 'squirrly-seo-pack' ),
					'upcoming' => __( 'Upcoming', 'squirrly-seo-pack' ),
					'ec'       => __( 'Editor\'s Choice', 'squirrly-seo-pack' ),
				),
				'pixabay_orientation' => array(
					'any'        => __( 'Any Orientation', 'squirrly-seo-pack' ),
					'vertical'   => __( 'Vertical', 'squirrly-seo-pack' ),
					'horizontal' => __( 'Horizontal', 'squirrly-seo-pack' ),
				),
				'title'               => __( 'Squirrly Free Images', 'squirrly-seo-pack' ),
				'search_placeholder'  => __( 'Search - Ex: flowers', 'squirrly-seo-pack' ),
				'downloading'         => __( 'Downloading...', 'squirrly-seo-pack' ),
				'validating'          => __( 'Validating...', 'squirrly-seo-pack' ),
				'empty_api_key'       => __( 'Please enter an API key.', 'squirrly-seo-pack' ),
				'error_api_key'       => __( 'An error occurred with code ', 'squirrly-seo-pack' ),
			) );

		$handle = SQP_Classes_ObjController::getClass( 'SQP_Classes_DisplayController' )->loadMedia( 'images/media', array(
			'dependencies' => array(
				'jquery',
				'wp-util',
				'wp-blocks',
				'wp-i18n',
				'wp-element',
				'wp-components',
				'wp-api-fetch'
			)
		) );

		SQP_Classes_ObjController::getClass( 'SQP_Classes_DisplayController' )->loadMedia( 'images/index', array( 'dependencies' => array( $handle ) ) );
		SQP_Classes_ObjController::getClass( 'SQP_Classes_DisplayController' )->loadMedia( 'images/images' );

		wp_localize_script( $handle, 'sqMediaLibrary', $data );

		if ( is_rtl() ) {
			SQP_Classes_ObjController::getClass( 'SQP_Classes_DisplayController' )->loadMedia( 'images/media-rtl' );
			SQP_Classes_ObjController::getClass( 'SQP_Classes_DisplayController' )->loadMedia( 'images/images-rtl' );
		}

	}

	public function getElementorData( $image ) {

		if ( ! empty( $image ) && class_exists( '\Elementor\Utils' ) ) {
			return array(
				'content' => array(
					array(
						'id'       => \Elementor\Utils::generate_random_string(),
						'elType'   => 'section',
						'settings' => array(),
						'isInner'  => false,
						'elements' => array(
							array(
								'id'       => \Elementor\Utils::generate_random_string(),
								'elType'   => 'column',
								'elements' => array(
									array(
										'id'         => \Elementor\Utils::generate_random_string(),
										'elType'     => 'widget',
										'settings'   => array(
											'image'      => array(
												'url' => wp_get_attachment_url( $image ),
												'id'  => $image,
											),
											'image_size' => 'full',
										),
										'widgetType' => 'image',
									),
								),
								'isInner'  => false,
							),
						),
					),
				),
			);
		}

		return array();
	}

}
