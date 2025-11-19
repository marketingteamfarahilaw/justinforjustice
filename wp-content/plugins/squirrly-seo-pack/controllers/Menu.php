<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Controllers_Menu extends SQP_Classes_FrontController {

	public function __construct() {
		parent::__construct();

		add_action( 'init', array( $this, 'loadPackMenu' ) );
		add_filter( 'sq_features', array( $this->model, 'loadPackFeatures' ) );
		add_filter( 'sqp_badge_new', array( $this, 'getNewFeatureBadge' ) );

	}

	/**
	 * Load the Menu and Tabs in Squirrly SEO plugin
	 *
	 * @return void
	 */
	public function loadPackMenu() {

		if ( ! SQP_Classes_Helpers_Tools::isSquirrlySeo() ) {
			add_action( 'admin_notices', function() {
				echo '<div class="notice notice-warning is-dismissible"><p>' . esc_html( _SQP_MENU_NAME_ ) . ' ' . sprintf( esc_html__( 'requires Squirrly SEO plugin version %s to be activate.', 'squirrly-seo-pack' ), esc_html( SQ_VERSION_MIN ) ) . '</p></div>';
			} );

			return;
		}

		$menus = $this->model->getMainMenu();

		add_filter( 'sq_menu', function( $menu ) use ( $menus ) {
			return array_replace_recursive( $menu, $menus );
		} );

		//Add the tabs in the Squirrly Menu
		foreach ( $menus as $page => $menu ) {
			add_filter( 'sq_menu_' . $page, function( $tabs, $category ) {
				return array_merge( $tabs, $this->model->getTabs( $category ) );
			}, 11, 2 );
		}

		global $sq_fullscreen, $sq_setting_page;

		//run compatibility check on Squirrly settings
		if ( SQP_Classes_Helpers_Tools::getIsset( 'page' ) ) {

			//Get current accessed page
			$page = preg_replace( "/[^a-zA-Z0-9_]/", "", SQP_Classes_Helpers_Tools::getValue( 'page' ) );

			if ( in_array( $page, array_keys( $menus ) ) ) {
				//Set if it's a Squirrly SEO Page
				$sq_setting_page = true;

				//Check if the menu requires full screen window
				if ( isset( $menus[ $page ]['fullscreen'] ) && $menus[ $page ]['fullscreen'] ) {
					$sq_fullscreen = true;
				}

			}
		}

		add_filter( 'sqp_menu_toolbar', function( $html ) {
			if ( class_exists( 'SQ_Classes_ObjController' ) ) {
				return SQ_Classes_ObjController::getClass( 'SQ_Controllers_Menu' )->show_view( 'Blocks/Toolbar' );
			}

			return $html;
		} );

		add_filter( 'sqp_menu_tabs', function( $html, $page, $tab ) {
			if ( class_exists( 'SQ_Classes_ObjController' ) ) {
				return SQ_Classes_ObjController::getClass( 'SQ_Models_Menu' )->getAdminTabs( $tab, 'sq_advanced' );
			}

			return $html;
		}, 11, 3 );

		add_filter( 'sqp_submenumenu_header', function( $html, $page, $tab ) {
			if ( class_exists( 'SQ_Classes_ObjController' ) ) {
				$tabs = SQ_Classes_ObjController::getClass( 'SQ_Models_Menu' )->getTabs( $page );
				if ( ! empty( $tabs ) ) {
					$current = ( $tab ? $page . '/' . $tab : SQP_Classes_Helpers_Tools::arrayKeyFirst( $tabs ) );

					if ( isset( $tabs[ $current ]['tabs'] ) && ! empty( $tabs[ $current ]['tabs'] ) ) {
						$cnt = 0;
						foreach ( $tabs[ $current ]['tabs'] as $tab ) {
							?>
                            <div class="bg-primary mt-5 p-3 tab-panel <?php echo( $cnt == 0 ? 'tab-panel-first' : '' ) ?> <?php echo esc_attr( $tab['tab'] ) ?>"><?php echo wp_kses_post( $tab['title'] ) ?></div>
							<?php
							$cnt ++;
						}
					}
				}
			}

			return $html;
		}, 11, 3 );

		add_filter( 'sqp_submenumenu_tabs', function( $html, $page, $tab ) {
			if ( class_exists( 'SQ_Classes_ObjController' ) ) {
				?>
                <div class="sq_sub_nav d-flex flex-column bd-highlight m-0 p-0 border-right"><?php
				$tabs = SQ_Classes_ObjController::getClass( 'SQ_Models_Menu' )->getTabs( $page );

				if ( ! empty( $tabs ) ) {
					$current = ( $tab ? $page . '/' . $tab : SQP_Classes_Helpers_Tools::arrayKeyFirst( $tabs ) );

					if ( isset( $tabs[ $current ]['tabs'] ) && ! empty( $tabs[ $current ]['tabs'] ) ) {
						foreach ( $tabs[ $current ]['tabs'] as $index => $tab ) {
							if ( isset( $tab['show'] ) && ! $tab['show'] ) {
								continue;
							}
							?>
                            <a href="#<?php echo esc_attr( $tab['tab'] ) ?>" class="m-0 pl-3 pr-1 py-3 font-dark sq_sub_nav_item <?php echo esc_attr( $tab['tab'] ) ?> <?php echo( $index == 0 ? 'active' : '' ) ?>" data-tab="<?php echo esc_attr( $tab['tab'] ) ?>"><?php echo wp_kses_post( $tab['title'] ) ?></a>
							<?php
						}
					}
				}
				?></div><?php
			}

			return $html;
		}, 11, 3 );

		add_filter( 'sqp_menu_form_nonce', function( $action ) {
			if ( class_exists( 'SQ_Classes_ObjController' ) ) {
				return SQP_Classes_Helpers_Tools::setNonce( $action, 'sq_nonce', true, false ) . '<input type="hidden" name="action" value="sqp_settings_update"/>';
			}

			return $action;
		} );

		add_filter( 'sqp_menu_breadcrumbs', function( $page ) {
			if ( class_exists( 'SQ_Classes_ObjController' ) ) {
				return SQ_Classes_ObjController::getClass( 'SQ_Models_Menu' )->getBreadcrumbs( $page );
			}

			return $page;
		} );

	}


	/**
	 * Hook the header
	 *
	 * @return void
	 */
	public function hookHead() {
		SQP_Classes_ObjController::getClass( 'SQP_Classes_DisplayController' )->loadMedia( 'rules' );

		if ( ! SQP_Classes_Helpers_Tools::getOption( 'sq_seoexpert' ) ) {
			SQP_Classes_ObjController::getClass( 'SQP_Classes_DisplayController' )->loadMedia( 'beginner' );
		}
	}

	/**
	 * Add the new badge for new features
	 *
	 * @return string
	 */
	public function getNewFeatureBadge() {
		return '<span class="sq_new_feature_badge" style="color: #c094ff;  font-size: 10px; font-weight: normal; padding: 0 5px;">' . esc_html__( 'NEW' ) . '</span>';
	}

}
