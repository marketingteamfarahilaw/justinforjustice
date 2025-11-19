<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );
if ( ! isset( $view ) ) {
	return;
}

/**
 * No Category view
 *
 */
?>
<div id="sq_wrap">
	<?php echo apply_filters( 'sqp_menu_toolbar', false ); ?>
	<?php do_action( 'sq_notices' ); ?>
    <div id="sq_content" class="d-flex flex-row bg-white my-0 p-0 m-0">
		<?php
		if ( ! SQP_Classes_Helpers_Tools::userCan( 'sq_manage_settings' ) ) {
			echo '<div class="col-12 alert alert-success text-center m-0 p-3">' . esc_html__( "You do not have permission to access this page. You need Squirrly SEO Admin role.", 'squirrly-seo-pack' ) . '</div>';

			return;
		}
		?>
		<?php echo apply_filters( 'sqp_menu_tabs', false, SQP_Classes_Helpers_Tools::getValue( 'page' ), SQP_Classes_Helpers_Tools::getValue( 'tab' ) ); ?>
        <div class="d-flex flex-row flex-nowrap flex-grow-1 bg-light m-0 p-0">
            <div class="flex-grow-1 sq_flex m-0 p-0 px-4 pb-4">

                <div class="sq_breadcrumbs my-4"><?php echo apply_filters( 'sqp_menu_breadcrumbs', SQP_Classes_Helpers_Tools::getValue( 'page' ) . '/' . SQP_Classes_Helpers_Tools::getValue( 'tab', 'category' ) ) ?></div>

                <form method="POST">
					<?php echo apply_filters( 'sqp_menu_form_nonce', 'sqp_settings_update' ); ?>

                    <div class="sq_breadcrumbs my-4"><?php echo apply_filters( 'sqp_menu_breadcrumbs', 'sq_advanced/category' ) ?></div>
                    <h3 class="mt-4 card-title">
						<?php echo esc_html__( "No Category Base", 'squirrly-seo-pack' ); ?>
                        <div class="sq_help_question d-inline">
                            <a href="https://howto12.squirrly.co/ht_kb/how-to-remove-the-category-base-from-wordpress/" target="_blank"><i class="fa-solid fa-question-circle m-0 p-0"></i></a>
                        </div>
                    </h3>
                    <div class="col-7 small m-0 p-0">
						<?php echo esc_html__( "This can make your category URLs more aesthetically appealing, more intuitive, as well as easier to understand and remember by site visitors.", 'squirrly-seo-pack' ); ?>
                    </div>

                    <div id="sq_seosettings" class="col-12 p-0 m-0">
                        <div class="col-12 m-0 p-0">
                            <div class="col-12 m-0 p-0">

                                <div class="col-12 m-0 p-0">
                                    <div class="col-12 row m-0 p-0">
                                        <div class="checker col-12 row my-4 p-0 mx-0">
                                            <div class="col-12 p-0 m-0 sq-switch sq-switch-sm">
                                                <input type="hidden" name="sq_nocategory" value="0"/>
                                                <input type="checkbox" id="sq_nocategory" name="sq_nocategory" class="sq-switch" <?php echo( SQP_Classes_Helpers_Tools::getOption( 'sq_nocategory' ) ? 'checked="checked"' : '' ) ?> value="1"/>
                                                <label for="sq_nocategory" class="ml-2"><?php echo esc_html__( "Hide Category Base", 'squirrly-seo-pack' ); ?></label>
                                                <div class="small text-black-50 ml-5"><?php echo esc_html__( "Remove the category base from all post categories.", 'squirrly-seo-pack' ); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

								<?php if ( SQP_Classes_Helpers_Tools::isPluginInstalled( 'woocommerce/woocommerce.php' ) ) { ?>
                                    <div class="col-12 m-0 p-0">
                                        <div class="col-12 row m-0 p-0">
                                            <div class="checker col-12 row my-4 p-0 mx-0">
                                                <div class="col-12 p-0 m-0 sq-switch sq-switch-sm">
                                                    <input type="hidden" name="sq_nocategory_woocommerce" value="0"/>
                                                    <input type="checkbox" id="sq_nocategory_woocommerce" name="sq_nocategory_woocommerce" class="sq-switch" <?php echo( SQP_Classes_Helpers_Tools::getOption( 'sq_nocategory_woocommerce' ) ? 'checked="checked"' : '' ) ?> value="1"/>
                                                    <label for="sq_nocategory_woocommerce" class="ml-2"><?php echo esc_html__( "Hide Category Base in Woocommerce", 'squirrly-seo-pack' ); ?></label>
                                                    <div class="small text-black-50 ml-5"><?php echo esc_html__( "Remove the category base from all products categories.", 'squirrly-seo-pack' ); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 m-0 p-0">
                                        <div class="col-12 row m-0 p-0">
                                            <div class="checker col-12 row my-4 p-0 mx-0">
                                                <div class="col-12 p-0 m-0 sq-switch sq-switch-sm">
                                                    <input type="hidden" name="sq_noslug_woocommerce" value="0"/>
                                                    <input type="checkbox" id="sq_noslug_woocommerce" name="sq_noslug_woocommerce" class="sq-switch" <?php echo( SQP_Classes_Helpers_Tools::getOption( 'sq_noslug_woocommerce' ) ? 'checked="checked"' : '' ) ?> value="1"/>
                                                    <label for="sq_noslug_woocommerce" class="ml-2"><?php echo esc_html__( "Hide Parent Category Base in Woocommerce", 'squirrly-seo-pack' ); ?></label>
                                                    <div class="small text-black-50 ml-5"><?php echo esc_html__( "Hide parent category base when there are categories with subcategories.", 'squirrly-seo-pack' ); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 m-0 p-0">
                                        <div class="col-12 row m-0 p-0">
                                            <div class="checker col-12 row my-4 p-0 mx-0">
                                                <div class="col-12 p-0 m-0 sq-switch sq-switch-sm">
                                                    <input type="hidden" name="sq_noproduct_woocommerce" value="0"/>
                                                    <input type="checkbox" id="sq_noproduct_woocommerce" name="sq_noproduct_woocommerce" class="sq-switch" <?php echo( SQP_Classes_Helpers_Tools::getOption( 'sq_noproduct_woocommerce' ) ? 'checked="checked"' : '' ) ?> value="1"/>
                                                    <label for="sq_noproduct_woocommerce" class="ml-2"><?php echo esc_html__( "Hide Product Base in Woocommerce", 'squirrly-seo-pack' ); ?></label>
                                                    <div class="small text-black-50 ml-5"><?php echo esc_html__( "Remove the product base from all Woocommerce products.", 'squirrly-seo-pack' ); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
								<?php } ?>

                            </div>
                        </div>
                    </div>

                    <div class="col-12 my-3 p-0">
                        <button type="submit" class="btn rounded-0 btn-primary btn-lg m-0 px-5"><?php echo esc_html__( "Save Settings", 'squirrly-seo-pack' ); ?></button>
                    </div>
                </form>
            </div>

            <div class="sq_col_side bg-white">
                <div class="col-12 m-0 p-0 sq_sticky">
                </div>
            </div>

        </div>
    </div>
</div>
