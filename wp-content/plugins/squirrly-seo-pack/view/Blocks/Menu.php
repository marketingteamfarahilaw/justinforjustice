<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );
if ( ! isset( $view ) ) {
	return;
}

/**
 * Side Menu Block view
 *
 */
?>
<div class="sqp_col_menu <?php if ( ! isset( $_COOKIE['sqp_menu'] ) || $_COOKIE['sqp_menu'] == 'open' ) { ?>sqp_col_menu_big<?php } ?>">
    <div class="sqp_nav d-flex flex-column bd-highlight mb-3 sqp_sticky">
        <div class="p-0 m-0 p-3">
            <a href="<?php echo esc_url( SQP_Classes_Helpers_Tools::getAdminUrl( 'sqp_settings' ) ) ?>" class="sqp_nav_item_logo"><span class="sqp_logo sqp_logo_30 float-left"></span></a>
            <a href="<?php echo esc_url( SQP_Classes_Helpers_Tools::getAdminUrl( 'sqp_settings' ) ) ?>" class="sqp_nav_item_logo_text"><span class="sqp_logo_toolbar_text ml-2 text-dark float-left"><?php echo esc_html( _SQP_MENU_NAME_ ) ?></span></a>
        </div>

		<?php if ( SQP_Classes_Helpers_Tools::getValue( 'page' ) !== 'sqp_settings' ) { ?>
            <div class="m-0 p-3 font-dark sqp_nav_item bg-default sqp_nav_item_home">
                <a href="<?php echo esc_url( SQP_Classes_Helpers_Tools::getAdminUrl( 'sqp_settings' ) ); ?>" class="text-dark"><i class="fa-solid fa-house text-dark"></i><span class="sqp_nav_item_text ml-2"><?php echo esc_html__( 'Back To Home', 'squirrly-seo-pack' ) ?></span></a>
            </div>
		<?php } ?>

		<?php
		$tabs = SQP_Classes_ObjController::getClass( 'SQP_Models_Menu' )->getTabs( SQP_Classes_Helpers_Tools::getValue( 'page' ) );

		if ( ! empty( $tabs ) ) {
			$current = SQP_Classes_Helpers_Tools::getValue( 'tab', SQP_Classes_Helpers_Tools::arrayKeyFirst( $tabs ) );

			foreach ( $tabs as $menuid => $item ) {

				if ( isset( $item['show'] ) && ! $item['show'] ) {
					continue;
				}

				if ( ! SQP_Classes_Helpers_Tools::userCan( $item['capability'] ) ) {
					continue;
				}

				$class = 'bg-white';
				if ( $current == $menuid || $current == substr( $menuid, strpos( $menuid, '/' ) + 1 ) ) {
					$class = 'bg-light';
				}

				if ( '' === $menuid || false !== strpos( $menuid, '/' ) ) {
					list( $menuid, $tab ) = explode( '/', $menuid );
				}

				?>
                <a href="<?php echo esc_url( SQP_Classes_Helpers_Tools::getAdminUrl( $menuid, $tab ) ); ?>" class="m-0 p-3 text-dark align-middle sqp_nav_item <?php echo esc_attr( $class ) ?>" data-tab="level"><i class="<?php echo esc_attr( $item['icon'] ) ?> text-dark"></i><span class="sqp_nav_item_text text-dark ml-2 <?php echo esc_attr( $class ) ?>"><?php echo esc_html( $item['title'] ) ?></span></a>
				<?php
			}
		}
		?>
        <div class="m-0 p-3 font-dark sqp_nav_item sqp_nav_item_collapse">
            <i class="dashicons-before dashicons-arrow-left-alt text-dark"></i><span class="sqp_nav_item_text text-dark ml-2"><?php echo esc_html__( 'Collapse', 'squirrly-seo-pack' ) ?></span>
        </div>
        <div class="m-0 p-3 font-dark sqp_nav_item bg-default sqp_nav_item_open">
            <i class="dashicons-before dashicons-arrow-right-alt text-dark"></i></div>
    </div>
    <script>
        (function ($) {
            $('.sqp_nav_item_open').on('click', function () {
                $('.sqp_col_menu').addClass('sqp_col_menu_big');
                $.sqp_setCookie('sqp_menu', 'open');
            });
            $('.sqp_nav_item_collapse').on('click', function () {
                $('.sqp_col_menu').removeClass('sqp_col_menu_big');
                $.sqp_setCookie('sqp_menu', 'collapse');
            });
        })(jQuery);
    </script>
</div>
