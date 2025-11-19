<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );
if ( ! isset( $view ) ) {
	return;
}

/**
 * Reusable View
 *
 */
?>
<div id="sq_wrap">
	<?php echo apply_filters( 'sqp_menu_toolbar', false ); ?>
	<?php do_action( 'sqp_notices' ); ?>

    <div id="sq_content" class="d-flex flex-row bg-white my-0 p-0 m-0">
		<?php
		if ( ! SQP_Classes_Helpers_Tools::userCan( 'sq_manage_settings' ) ) {
			echo '<div class="col-12 alert alert-success text-center m-0 p-3">' . esc_html__( "You do not have permission to access this page.", 'squirrly-seo-pack' ) . '</div>';

			return;
		}
		?>
		<?php echo apply_filters( 'sqp_menu_tabs', false, SQP_Classes_Helpers_Tools::getValue( 'page' ), SQP_Classes_Helpers_Tools::getValue( 'tab' ) ); ?>
        <div class="d-flex flex-row flex-nowrap flex-grow-1 bg-light m-0 p-0">
            <div class="flex-grow-1 sq_flex m-0 py-0 px-4 pb-4">
				<?php echo apply_filters( 'sqp_menu_form_nonce', 'sqp_settings_update' ); ?>
                <div class="sq_breadcrumbs my-4"><?php echo apply_filters( 'sqp_menu_breadcrumbs', SQP_Classes_Helpers_Tools::getValue( 'page' ) . '/' . SQP_Classes_Helpers_Tools::getValue( 'tab', 'reusable' ) ) ?></div>

                <h3 class="mt-4 card-title">
					<?php echo esc_html__( "Rich Snippets - Reusable Schemas", 'squirrly-seo-pack' ); ?>
                    <div class="sq_help_question d-inline">
                        <a href="https://howto12.squirrly.co/kb/json-ld-structured-data/" target="_blank"><i class="fa-solid fa-question-circle"></i></a>
                    </div>
                </h3>
                <div class="col-7 small m-0 p-0">
					<?php echo esc_html__( "The list of reusable schemas from Rich Snippets.", 'squirrly-seo-pack' ); ?>
                </div>

                <div id="sq_reusable" class="col-12 m-0 p-0 border-0">

                    <div class="col-12 m-0 p-0 my-4">

                        <div class="row col-12 p-0 m-0 my-3">
                            <button class="btn btn-lg btn-primary text-white sq_jsonld_show_types">
								<?php echo esc_html__( "Create Reusable Schema", 'squirrly-seo-pack' ); ?>
                                <i class="fa-solid fa-plus-square"></i>
                            </button>
                        </div>

                        <div class="row col-12 m-0 p-0 py-3">

                            <div class="row col-6 p-0 m-0">
                                <div class="p-0 m-0 pr-3">
                                    <select name="sq_bulk_action" class="sq_bulk_action">
                                        <option value=""><?php echo esc_html__( "Bulk Actions", 'squirrly-seo-pack' ) ?></option>
                                        <option value="sq_ajax_reusable_jsonld_bulk_delete" data-confirm="<?php echo esc_attr__( "Are you sure you want to delete reusable schemas?", 'squirrly-seo-pack' ) ?>"><?php echo esc_html__( "Delete" ) ?></option>
                                    </select>
                                    <button class="sq_bulk_submit btn btn-primary"><?php echo esc_html__( "Apply" ); ?></button>
                                </div>

                            </div>

                            <div class="col-6 p-0 text-right">
                                <form method="get" class="d-flex flex-row justify-content-end p-0 m-0">
                                    <input type="hidden" name="page" value="<?php echo esc_attr( SQP_Classes_Helpers_Tools::getValue( 'page' ) ) ?>">
                                    <input type="hidden" name="tab" value="<?php echo esc_attr( SQP_Classes_Helpers_Tools::getValue( 'tab' ) ) ?>">
                                    <input type="hidden" name="stype" value="<?php echo esc_attr( SQP_Classes_Helpers_Tools::getValue( 'stype' ) ) ?>">
                                    <input type="hidden" name="snum" value="<?php echo esc_attr( SQP_Classes_Helpers_Tools::getValue( 'snum' ) ) ?>">
                                    <input type="search" class="d-inline-block align-middle col-7 py-0 px-2 mr-0 rounded-0" id="post-search-input" name="squery" value="<?php echo esc_attr( SQP_Classes_Helpers_Tools::getValue( 'squery' ) ) ?>" placeholder="<?php echo esc_attr__( "Write the name you want to search for", 'squirrly-seo-pack' ) ?>"/>
                                    <input type="submit" class="btn btn-primary" value="<?php echo esc_attr__( "Search", 'squirrly-seo-pack' ) ?> >"/>
									<?php if ( SQp_Classes_Helpers_Tools::getIsset( 'squery' ) ) { ?>
                                        <button type="button" class="btn btn-link text-primary ml-1" onclick="location.href = '<?php echo esc_url( SQP_Classes_Helpers_Tools::getAdminUrl( 'sq_jsonld', 'reusable' ) ) ?>';" style="cursor: pointer"><?php echo esc_html__( "Show All", 'squirrly-seo-pack' ) ?></button>
									<?php } ?>
                                </form>
                            </div>
                        </div>

                        <div class="p-0">
                            <table class="table table-striped table-hover">
                                <thead>
                                <tr>
                                    <th style="width: 10px;"><input type="checkbox" class="sq_bulk_select_input"/></th>
                                    <th style="width: 30%;"><?php SQP_Classes_Helpers_Tools::sort( esc_html__( "Name", 'squirrly-seo-pack' ), "name" ) ?></th>
                                    <th style="width: 30%;"><?php SQP_Classes_Helpers_Tools::sort( esc_html__( "Schema Type", 'squirrly-seo-pack' ), "jsonld_type" ) ?></th>
                                    <th style="width: 20px;"></th>
                                </tr>
                                </thead>
                                <tbody>
								<?php
								if ( ! empty( $view->schemas ) ) {
									foreach ( $view->schemas as $row ) { ?>
                                        <tr id="sq_row_<?php echo esc_attr( $row->id ) ?>">
                                            <td style="width: 10px;">
												<?php if ( SQP_Classes_Helpers_Tools::userCan( 'sq_manage_settings' ) ) { ?>
                                                    <input type="checkbox" name="sq_edit[]" class="sq_bulk_input" value="<?php echo esc_attr( $row->id ) ?>"/>
												<?php } ?>
                                            </td>
                                            <td style="width: 50%;" class="text-left">
												<?php echo esc_html( $row->name ) ?>
                                            </td>
                                            <td style="width: 45%;" class="text-left">
												<?php echo esc_html( $row->jsonld_type ) ?>
                                            </td>

                                            <td class="px-0 py-2" style="width: 20px">
                                                <div class="sq_sm_menu">
                                                    <div class="sm_icon_button sm_icon_options">
                                                        <i class="fa-solid fa-ellipsis-v"></i>
                                                    </div>
                                                    <div class="sq_sm_dropdown">
                                                        <ul class="text-left p-2 m-0">
															<?php if ( SQP_Classes_Helpers_Tools::userCan( 'sq_manage_settings' ) ) { ?>
                                                                <li class="sq_jsonld_edit_type m-0 p-1 py-2" data-jsonld-type="<?php echo esc_attr( $row->jsonld_type ) ?>" data-jsonld-id="<?php echo esc_attr( $row->id ) ?>">
                                                                    <i class="sq_icons_small fa-solid fa-tag"></i>
																	<?php echo esc_html__( "Edit Schema", 'squirrly-seo-pack' ) ?>
                                                                </li>
                                                                <li class="sq_delete_type m-0 p-1 py-2">
                                                                    <form method="post" class="p-0 m-0" onSubmit="return confirm('<?php echo esc_html__( "Do you want to delete Schema?", 'squirrly-seo-pack' ) ?>') ">
																		<?php SQP_Classes_Helpers_Tools::setNonce( 'sqp_jsonld_reusable_delete', 'sq_nonce' ); ?>
                                                                        <input type="hidden" name="action" value="sqp_jsonld_reusable_delete"/>
                                                                        <input type="hidden" name="jsonld_id" value="<?php echo esc_attr( $row->id ) ?>"/>
                                                                        <i class="sq_icons_small fa-solid fa-trash" style="padding: 2px"></i>
                                                                        <button type="submit" class="btn btn-sm bg-transparent font-weight-normal p-0 m-0">
																			<?php echo esc_html__( "Delete Schema", 'squirrly-seo-pack' ) ?>
                                                                        </button>
                                                                    </form>
                                                                </li>
															<?php } ?>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
										<?php
									}
								} else { ?>
                                    <tr>
                                        <td colspan="7" class="text-center">
											<?php echo esc_html__( "No reusable schemas found.", 'squirrly-seo-pack' ); ?>
                                        </td>
                                    </tr>
								<?php } ?>

                                </tbody>
                            </table>
                            <div class="alignleft mr-3">
                                <label>
                                    <select name="snum" onchange="location.href = '<?php echo esc_url( add_query_arg( array(
										'spage' => 1,
										'snum'  => "'+jQuery(this).find('option:selected').val()+'"
									) ) ); ?>'">
										<?php
										$post_on_page = array( 10, 20, 50, 100, 500 );
										foreach ( $post_on_page as $num ) {
											?>
                                            <option value="<?php echo esc_attr( $num ) ?>" <?php selected( $num, SQP_Classes_Helpers_Tools::getValue( 'snum' ) ) ?> ><?php echo esc_html( $num ) . ' ' . esc_html__( 'records', 'squirrly-seo-pack' ) ?></option><?php
										}
										?>
                                    </select>
                                </label>
                            </div>

                            <div class="nav-previous alignright">
								<?php SQP_Classes_Helpers_Tools::pagination( $view->max_num_pages ); ?>
                            </div>
                        </div>

                    </div>

                </div>

            </div>
            <div class="sq_col_side bg-white">
                <div class="col-12 m-0 p-0 sq_sticky">
                </div>
            </div>
        </div>
    </div>

</div>

