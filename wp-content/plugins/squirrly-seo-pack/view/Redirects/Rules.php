<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );
if ( ! isset( $view ) ) {
	return;
}

/**
 * Rules view
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
                <div class="sq_breadcrumbs my-4"><?php echo apply_filters( 'sqp_menu_breadcrumbs', SQP_Classes_Helpers_Tools::getValue( 'page' ) . '/' . SQP_Classes_Helpers_Tools::getValue( 'tab', 'rules' ) ) ?></div>

                <h3 class="mt-4 card-title">
					<?php echo esc_html__( "Redirects Rules", 'squirrly-seo-pack' ); ?>
                    <div class="sq_help_question d-inline">
                        <a href="https://howto12.squirrly.co/kb/advanced-redirects-module/" target="_blank"><i class="fa-solid fa-question-circle"></i></a>
                    </div>
                </h3>
                <div class="col-7 small m-0 p-0">
					<?php echo esc_html__( "The list of redirect and rules from Squirrly SEO.", 'squirrly-seo-pack' ); ?>
                </div>

                <div id="sq_redirects" class="col-12 m-0 p-0 border-0">

                    <div class="col-12 m-0 p-0 my-4">

                        <div class="row col-12 p-0 m-0 my-3">
                            <button class="btn btn-lg btn-primary text-white" onclick="jQuery('.sq_add_redirect_dialog').modal('show')" data-dismiss="modal">
								<?php echo esc_html__( "Add Custom Redirect", 'squirrly-seo-pack' ); ?>
                                <i class="fa-solid fa-plus-square"></i>
                            </button>
                        </div>

                        <div class="row col-12 m-0 p-0 py-3">

                            <div class="row col-6 p-0 m-0">
                                <div class="p-0 m-0 pr-3">
                                    <select name="sq_bulk_action" class="sq_bulk_action">
                                        <option value=""><?php echo esc_html__( "Bulk Actions", 'squirrly-seo-pack' ) ?></option>
                                        <option value="sq_ajax_rules_bulk_delete" data-confirm="<?php echo esc_attr__( "Are you sure you want to delete the rules?", 'squirrly-seo-pack' ) ?>"><?php echo esc_html__( "Delete" ) ?></option>
                                    </select>
                                    <button class="sq_bulk_submit btn btn-primary"><?php echo esc_html__( "Apply" ); ?></button>
                                </div>

								<?php if ( ! SQP_Classes_Helpers_Tools::getValue( 'squery' ) ) { ?>
                                    <form id="sq_type_form" method="get" class="form-inline col p-0 m-0">
                                        <input type="hidden" name="page" value="<?php echo esc_attr( SQP_Classes_Helpers_Tools::getValue( 'page' ) ) ?>">
                                        <input type="hidden" name="tab" value="<?php echo esc_attr( SQP_Classes_Helpers_Tools::getValue( 'tab' ) ) ?>">
                                        <input type="hidden" name="stype" value="<?php echo esc_attr( SQP_Classes_Helpers_Tools::getValue( 'stype' ) ) ?>">
                                        <input type="hidden" name="snum" value="<?php echo esc_attr( SQP_Classes_Helpers_Tools::getValue( 'snum' ) ) ?>">

                                        <div class="col-12 row p-0 m-0">
                                            <select name="stype" class="w-100 m-0 p-1" onchange="jQuery('form#sq_type_form').submit();">
                                                <option value=""><?php echo esc_html__( "All redirects" ); ?></option>
												<?php
												foreach ( $view->getRedirectTypes() as $code => $title ) {
													?>
                                                    <option value="<?php echo esc_attr( $code ) ?>" <?php echo selected( SQP_Classes_Helpers_Tools::getValue( 'stype' ), $code ) ?> ><?php echo esc_html( ucwords( $title ) ); ?></option>
													<?php
												}
												?>
                                            </select>
                                        </div>
                                    </form>
								<?php } ?>

                            </div>

                            <div class="col-6 p-0 text-right">
                                <form method="get" class="d-flex flex-row justify-content-end p-0 m-0">
                                    <input type="hidden" name="page" value="<?php echo esc_attr( SQP_Classes_Helpers_Tools::getValue( 'page' ) ) ?>">
                                    <input type="hidden" name="tab" value="<?php echo esc_attr( SQP_Classes_Helpers_Tools::getValue( 'tab' ) ) ?>">
                                    <input type="hidden" name="stype" value="<?php echo esc_attr( SQP_Classes_Helpers_Tools::getValue( 'stype' ) ) ?>">
                                    <input type="hidden" name="snum" value="<?php echo esc_attr( SQP_Classes_Helpers_Tools::getValue( 'snum' ) ) ?>">
                                    <input type="search" class="d-inline-block align-middle col-7 py-0 px-2 mr-0 rounded-0" id="post-search-input" name="squery" value="<?php echo esc_attr( SQP_Classes_Helpers_Tools::getValue( 'squery' ) ) ?>" placeholder="<?php echo esc_attr__( "Write the redirect you want to search for", 'squirrly-seo-pack' ) ?>"/>
                                    <input type="submit" class="btn btn-primary" value="<?php echo esc_attr__( "Search", 'squirrly-seo-pack' ) ?> >"/>
									<?php if ( SQp_Classes_Helpers_Tools::getIsset( 'squery' ) ) { ?>
                                        <button type="button" class="btn btn-link text-primary ml-1" onclick="location.href = '<?php echo esc_url( SQP_Classes_Helpers_Tools::getAdminUrl( 'sq_redirects', 'rules' ) ) ?>';" style="cursor: pointer"><?php echo esc_html__( "Show All", 'squirrly-seo-pack' ) ?></button>
									<?php } ?>
                                </form>
                            </div>
                        </div>

                        <div class="p-0">
                            <table class="table table-striped table-hover">
                                <thead>
                                <tr>
                                    <th style="width: 10px;"><input type="checkbox" class="sq_bulk_select_input"/></th>
                                    <th style="width: 30%;"><?php SQP_Classes_Helpers_Tools::sort( esc_html__( "URL From", 'squirrly-seo-pack' ), "url" ) ?></th>
                                    <th style="width: 30%;"><?php SQP_Classes_Helpers_Tools::sort( esc_html__( "URL To", 'squirrly-seo-pack' ), "action_data" ) ?></th>
                                    <th scope="col"><?php SQP_Classes_Helpers_Tools::sort( esc_html__( "Code", 'squirrly-seo-pack' ), "action_code" ) ?></th>
                                    <th scope="col"><?php SQP_Classes_Helpers_Tools::sort( esc_html__( "Hits", 'squirrly-seo-pack' ), "last_count" ) ?></th>
                                    <th scope="col"><?php SQP_Classes_Helpers_Tools::sort( esc_html__( "Last Access", 'squirrly-seo-pack' ), "last_access" ) ?></th>
                                    <th style="width: 20px;"></th>
                                </tr>
                                </thead>
                                <tbody>
								<?php
								if ( ! empty( $view->rewrite_rules ) ) {
									foreach ( $view->rewrite_rules as $key => $row ) { ?>
                                        <tr id="sq_row_<?php echo (int) $row->id ?>" class="<?php if ( $row->status == 'disabled' ) { ?>bg-light text-black-50<?php } ?>">
                                            <td style="width: 10px;">
												<?php if ( SQP_Classes_Helpers_Tools::userCan( 'sq_manage_settings' ) ) { ?>
                                                    <input type="checkbox" name="sq_edit[]" class="sq_bulk_input" value="<?php echo (int) $row->id ?>"/>
												<?php } ?>
                                            </td>
                                            <td style="width: 30%;" class="text-left">
												<?php
												$edit_link = false;
												$flags     = $row->getSource_flags()->getJson();

												if ( (int) $flags['flag_post'] > 0 ) {
													if ( $flags['flag_post_type'] <> 'profile' ) {
														$edit_link = get_edit_post_link( (int) $flags['flag_post'], false );
													}

												} elseif ( (int) $flags['flag_term'] > 0 ) {
													$term = get_term_by( 'term_id', (int) $flags['flag_term'], $flags['flag_taxonomy'] );
													if ( ! is_wp_error( $term ) ) {
														$edit_link = get_edit_term_link( (int) $flags['flag_term'], $flags['flag_taxonomy'] );
													}
												} ?>

												<?php if ( $edit_link ) { ?>
                                                    <a href="<?php echo esc_url( $row->url ) ?>" target="_blank">
														<?php echo esc_url( $row->url ) ?>
                                                    </a>

                                                    <a href="<?php echo esc_url( $edit_link ) ?>" target="_blank">
                                                        <i class="fa-solid fa-edit small" style="color: gray;"></i>
                                                    </a>
												<?php } elseif ( preg_match( '/[\*\.\)\(]/', $row->url ) ) { ?>
													<?php echo esc_url( $row->url ) ?>
												<?php } else { ?>
                                                    <a href="<?php echo esc_url( $row->url ) ?>" target="_blank">
														<?php echo esc_url( $row->url ) ?>
                                                    </a>
												<?php } ?>

                                            </td>
                                            <td style="width: 30%;" class="text-left">
                                                <a href="<?php echo esc_url( $row->action_data ) ?>" target="_blank">
													<?php echo esc_url( $row->action_data ) ?>
                                                </a>
                                            </td>
                                            <td style="width: 10%; white-space: nowrap;">
												<?php echo esc_html( $row->action_code ) ?>
                                            </td>
                                            <td style="width: 10%; white-space: nowrap;">
												<?php echo esc_html( $row->last_count ) ?>
                                            </td>
                                            <td style="width: 15%; white-space: nowrap;">
												<?php
												$row->last_access = ( $row->last_access === '1970-01-01 00:00:00' || $row->last_access === '0000-00-00 00:00:00' ) ? 0 : mysql2date( 'U', $row->last_access );
												if ( $row->last_access > 0 ) {
													$row->last_access = date_i18n( get_option( 'date_format' ), intval( $row->last_access, 10 ) );
												} else {
													$row->last_access = '-';
												}
												echo esc_html( $row->last_access ) ?>
                                            </td>

                                            <td class="px-0 py-2" style="width: 20px">
                                                <div class="sq_sm_menu">
                                                    <div class="sm_icon_button sm_icon_options">
                                                        <i class="fa-solid fa-ellipsis-v"></i>
                                                    </div>
                                                    <div class="sq_sm_dropdown">
                                                        <ul class="text-left p-2 m-0">
															<?php if ( SQP_Classes_Helpers_Tools::userCan( 'sq_manage_settings' ) ) { ?>
                                                                <li class="sq_edit_rule m-0 p-1 py-2" onclick="jQuery('#sq_edit_rule<?php echo (int) $row->id ?>').modal('show')">
                                                                    <i class="sq_icons_small fa-solid fa-tag"></i>
																	<?php echo esc_html__( "Edit Redirect", 'squirrly-seo-pack' ) ?>
                                                                </li>
                                                                <li class="m-0 p-1 py-2">
																	<?php if ( $row->status == 'enabled' ) { ?>
                                                                        <form method="post" class="p-0 m-0">
																			<?php SQP_Classes_Helpers_Tools::setNonce( 'sqp_redirects_disable', 'sq_nonce' ); ?>
                                                                            <input type="hidden" name="action" value="sqp_redirects_disable"/>
                                                                            <input type="hidden" name="id" value="<?php echo (int) $row->id ?>"/>
                                                                            <i class="sq_icons_small fa-solid fa-pause" style="padding: 2px"></i>
                                                                            <button type="submit" class="btn btn-sm bg-transparent font-weight-normal p-0 m-0">
																				<?php echo esc_html__( "Disable Redirect", 'squirrly-seo-pack' ) ?>
                                                                            </button>
                                                                        </form>
																	<?php } else { ?>
                                                                        <form method="post" class="p-0 m-0">
																			<?php SQP_Classes_Helpers_Tools::setNonce( 'sqp_redirects_enable', 'sq_nonce' ); ?>
                                                                            <input type="hidden" name="action" value="sqp_redirects_enable"/>
                                                                            <input type="hidden" name="id" value="<?php echo (int) $row->id ?>"/>
                                                                            <i class="sq_icons_small fa-solid fa-play" style="padding: 2px"></i>
                                                                            <button type="submit" class="btn btn-sm bg-transparent font-weight-normal p-0 m-0">
																				<?php echo esc_html__( "Enable Redirect", 'squirrly-seo-pack' ) ?>
                                                                            </button>
                                                                        </form>
																	<?php } ?>
                                                                </li>
                                                                <li class="sq_delete_rule m-0 p-1 py-2">
                                                                    <form method="post" class="p-0 m-0" onSubmit="return confirm('<?php echo esc_html__( "Do you want to delete the Redirect?", 'squirrly-seo-pack' ) ?>') ">
																		<?php SQP_Classes_Helpers_Tools::setNonce( 'sqp_redirects_delete', 'sq_nonce' ); ?>
                                                                        <input type="hidden" name="action" value="sqp_redirects_delete"/>
                                                                        <input type="hidden" name="id" value="<?php echo (int) $row->id ?>"/>
                                                                        <i class="sq_icons_small fa-solid fa-trash" style="padding: 2px"></i>
                                                                        <button type="submit" class="btn btn-sm bg-transparent font-weight-normal p-0 m-0">
																			<?php echo esc_html__( "Delete Redirect", 'squirrly-seo-pack' ) ?>
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
											<?php echo esc_html__( "No redirects found", 'squirrly-seo-pack' ); ?>
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

                <form method="post" class="p-0 m-0">
					<?php SQP_Classes_Helpers_Tools::setNonce( 'sqp_redirects_export', 'sq_nonce' ); ?>
                    <input type="hidden" name="action" value="sqp_redirects_export"/>
                    <input type="hidden" name="stype" value="<?php echo esc_attr( SQP_Classes_Helpers_Tools::getValue( 'stype' ) ) ?>">
                    <button type="submit" class="btn btn-link text-primary p-0 m-0 noloading">
						<?php echo esc_html__( "Export Redirects", 'squirrly-seo-pack' ) ?>
                    </button>
                </form>

            </div>
            <div class="sq_col_side bg-white">
                <div class="col-12 m-0 p-0 sq_sticky">
                </div>
            </div>
        </div>
    </div>

    <div class="sq_add_redirect_dialog modal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content bg-white rounded-0">
                <div class="modal-header">
                    <h4 class="modal-title"><?php echo esc_html__( "Add New Redirect", 'squirrly-seo-pack' ); ?>
                        <a href="https://howto12.squirrly.co/kb/advanced-redirects-module/#add" target="_blank"><i class="fa-solid fa-question-circle m-0 px-2" style="display: inline;"></i></a>
                    </h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form method="post" class="modal-content bg-white rounded-0">
                    <div class="modal-body">
						<?php SQP_Classes_Helpers_Tools::setNonce( 'sqp_redirects_update', 'sq_nonce' ); ?>
                        <input type="hidden" name="action" value="sqp_redirects_update"/>
                        <input type="hidden" name="action_type" value="url"/>
                        <input type="hidden" name="match_type" value="url"/>
                        <input type="hidden" name="stype" value="url"/>

                        <div class="form-group">
                            <label for="sq_add_url"><?php echo esc_html__( "Redirect From", 'squirrly-seo-pack' ); ?></label>
                            <input type="text" class="form-control" name="url" id="sq_add_url" maxlength="255"/>
                        </div>
                        <div class="form-group">
                            <label for="sq_action_data"><?php echo esc_html__( "Redirect To", 'squirrly-seo-pack' ); ?></label>
                            <input type="text" class="form-control" name="action_data" id="sq_action_data" maxlength="255"/>
                        </div>

                        <div class="col-12 row m-0 p-0 my-4">
                            <div class="col-4 p-0 pr-3  font-weight-bold">
                                <div class="font-weight-bold"><?php echo esc_html__( "URL Matching", 'squirrly-seo-pack' ); ?>
                                    :
                                </div>
                                <div class="small text-black-50 my-1 pr-3"><?php echo esc_html__( "Select the URL matching type.", 'squirrly-seo-pack' ); ?></div>
                            </div>
                            <div class="col-8 p-0 input-group">
                                <select name="flag_query" class="form-control bg-input mb-1 border">
									<?php
									$settings = SQP_Classes_Helpers_Tools::getOption( 'sq_redirect_flags' );

									foreach ( $view->getMatchTypes() as $value => $option ) { ?>
                                        <option <?php selected( $settings['flag_query'], $value ) ?> value="<?php echo esc_attr( $value ) ?>"><?php echo esc_html( $option ); ?></option>
									<?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 row m-0 p-0 my-4">
                            <div class="col-4 p-0 pr-3 font-weight-bold">
                                <div class="font-weight-bold"><?php echo esc_html__( "Redirect Type", 'squirrly-seo-pack' ); ?>
                                    :
                                </div>
                                <div class="small text-black-50 my-1 pr-3"><?php echo esc_html__( "Select the code that will be added in the header of the redirect.", 'squirrly-seo-pack' ); ?></div>
                            </div>
                            <div class="col-8 p-0 input-group">
                                <select name="action_code" class="form-control bg-input mb-1 border">
									<?php foreach ( $view->getRedirectCodes() as $code => $title ) { ?>
                                        <option value="<?php echo esc_attr( $code ) ?>" <?php selected( SQP_Classes_Helpers_Tools::getOption( 'sq_redirects_action_code' ), $code ) ?> ><?php echo esc_html( $title ) ?></option>
									<?php } ?>
                                </select>
                            </div>
                        </div>

						<?php if ( ! SQP_Classes_Helpers_Tools::getOption( 'sq_seoexpert' ) ) { ?>
                            <div class="col-12 row m-0 p-0">
                                <div class="checker col-12 row my-2 p-0 mx-0">
                                    <div class="col-12 p-0 m-0">
                                        <input type="checkbox" id="sq_advanced" class="sq-switch" value="1" style="display: none"/>
                                        <label for="sq_advanced" class="ml-1"><?php echo esc_html__( "More Options", 'squirrly-seo-pack' ); ?>
                                            ></label>
                                    </div>
                                </div>
                            </div>
						<?php } ?>

                        <div class="col-12 row m-0 p-0 sq_advanced">
                            <div class="col-12 row m-0 p-0">
                                <div class="checker col-12 row my-2 p-0 mx-0">
                                    <div class="col-12 p-0 m-0 sq-switch sq-switch-sm">
                                        <input type="checkbox" id="flag_regex" name="flag_regex" class="sq-switch" value="1"/>
                                        <label for="flag_regex" class="ml-1"><?php echo esc_html__( "Regex Match", 'squirrly-seo-pack' ); ?></label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 row m-0 p-0">
                                <div class="checker col-12 row my-2 p-0 mx-0">
                                    <div class="col-12 p-0 m-0 sq-switch sq-switch-sm">
                                        <input type="hidden" name="flag_case" value="0"/>
                                        <input type="checkbox" id="flag_case" name="flag_case" class="sq-switch" <?php echo( $settings['flag_case'] ? 'checked="checked"' : '' ) ?> value="1"/>
                                        <label for="flag_case" class="ml-1"><?php echo esc_html__( "Ignore Case", 'squirrly-seo-pack' ); ?></label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 row m-0 p-0">
                                <div class="checker col-12 row my-2 p-0 mx-0">
                                    <div class="col-12 p-0 m-0 sq-switch sq-switch-sm">
                                        <input type="hidden" name="flag_trailing" value="0"/>
                                        <input type="checkbox" id="flag_trailing" name="flag_trailing" class="sq-switch" <?php echo( $settings['flag_trailing'] ? 'checked="checked"' : '' ) ?> value="1"/>
                                        <label for="flag_trailing" class="ml-1"><?php echo esc_html__( "Ignore Slash", 'squirrly-seo-pack' ); ?></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-bottom: 1px solid #ddd;">
                        <button type="submit" class="btn btn-primary"><?php echo esc_html__( "Add Redirect", 'squirrly-seo-pack' ); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

	<?php foreach ( $view->rewrite_rules as $key => $row ) {

		/** @var SQP_Models_Redirects_Flags $flags */
		$flags = $row->getSource_flags()->getJson();

		?>
        <div id="sq_edit_rule<?php echo (int) $row->id ?>" class="modal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg">
                <div class="modal-content bg-white rounded-0">
                    <form method="post" class="p-0 m-0 bg-white rounded-0">
						<?php SQP_Classes_Helpers_Tools::setNonce( 'sqp_redirects_update', 'sq_nonce' ); ?>
                        <input type="hidden" name="action" value="sqp_redirects_update"/>
                        <input type="hidden" name="id" value="<?php echo (int) $row->id ?>"/>
                        <input type="hidden" name="action_type" value="<?php echo esc_attr( $row->action_type ) ?>"/>
                        <input type="hidden" name="match_type" value="<?php echo esc_attr( $row->match_type ) ?>"/>
                        <input type="hidden" name="group_id" value="<?php echo (int) $row->group_id ?>"/>

						<?php
						/** @var SQP_Controllers_Redirects $redirects */
						$redirects = SQP_Classes_ObjController::getClass( 'SQP_Controllers_Redirects' );

						if ( $row->group_id == $redirects->getGroupIds( 'post' ) || $row->group_id == $redirects->getGroupIds( 'slug' ) ) { ?>
                            <input type="hidden" name="flag_post" value="<?php echo (int) $flags['flag_post'] ?>"/>
                            <input type="hidden" name="flag_post_type" value="<?php echo esc_attr( $flags['flag_post_type'] ) ?>"/>
                            <input type="hidden" name="flag_term" value="<?php echo (int) $flags['flag_term'] ?>"/>
                            <input type="hidden" name="flag_taxonomy" value="<?php echo esc_attr( $flags['flag_taxonomy'] ) ?>"/>
						<?php } ?>

                        <div class="modal-header">
                            <h4 class="modal-title"><?php echo esc_html__( "Edit Redirect", 'squirrly-seo-pack' ); ?></h4>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">

                            <div class="form-group">
                                <label for="sq_edit_url<?php echo (int) $row->id ?>"><?php echo esc_html__( "Redirect From", 'squirrly-seo-pack' ); ?></label>
                                <input type="text" class="form-control" name="url" id="sq_edit_url<?php echo (int) $row->id ?>" maxlength="255" value="<?php echo esc_attr( $row->url ) ?>"/>
                            </div>
                            <div class="form-group">
                                <label for="sq_action_data<?php echo (int) $row->id ?>"><?php echo esc_html__( "Redirect To", 'squirrly-seo-pack' ); ?></label>
                                <input type="text" class="form-control" name="action_data" id="sq_action_data<?php echo (int) $row->id ?>" maxlength="255" value="<?php echo esc_attr( $row->action_data ) ?>"/>
                            </div>

                            <div class="col-12 row m-0 p-0 my-4">
                                <div class="col-4 p-0 pr-3  font-weight-bold">
                                    <div class="font-weight-bold"><?php echo esc_html__( "URL Matching", 'squirrly-seo-pack' ); ?>
                                        :
                                    </div>
                                    <div class="small text-black-50 my-1 pr-3"><?php echo esc_html__( "Select the URL matching type.", 'squirrly-seo-pack' ); ?></div>
                                </div>
                                <div class="col-8 p-0 input-group">
                                    <select name="flag_query" class="form-control bg-input mb-1 border">
										<?php
										foreach ( $view->getMatchTypes() as $value => $option ) { ?>
                                            <option <?php selected( $flags['flag_query'], $value ) ?> value="<?php echo esc_attr( $value ) ?>"><?php echo esc_html( $option ); ?></option>
										<?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 row m-0 p-0 my-4">
                                <div class="col-4 p-0 pr-3 font-weight-bold">
                                    <div class="font-weight-bold"><?php echo esc_html__( "Redirect Type", 'squirrly-seo-pack' ); ?>
                                        :
                                    </div>
                                    <div class="small text-black-50 my-1 pr-3"><?php echo esc_html__( "Select the code that will be added in the header of the redirect.", 'squirrly-seo-pack' ); ?></div>
                                </div>
                                <div class="col-8 p-0 input-group">
                                    <select name="action_code" class="form-control bg-input mb-1 border">
										<?php foreach ( $view->getRedirectCodes() as $code => $title ) { ?>
                                            <option <?php selected( $row->action_code, $code ) ?> value="<?php echo esc_attr( $code ) ?>"><?php echo esc_html( $title ) ?></option>
										<?php } ?>
                                    </select>
                                </div>
                            </div>

							<?php if ( ! SQP_Classes_Helpers_Tools::getOption( 'sq_seoexpert' ) ) { ?>
                                <div class="col-12 row m-0 p-0">
                                    <div class="checker col-12 row my-2 p-0 mx-0">
                                        <div class="col-12 p-0 m-0">
                                            <input type="checkbox" id="sq_advanced<?php echo (int) $row->id ?>" class="sq-switch" value="1" style="display: none"/>
                                            <label for="sq_advanced<?php echo (int) $row->id ?>" class="ml-1"><?php echo esc_html__( "More Options", 'squirrly-seo-pack' ); ?>
                                                ></label>
                                        </div>
                                    </div>
                                </div>
							<?php } ?>

                            <div class="col-12 row m-0 p-0 sq_advanced<?php echo (int) $row->id ?>">

                                <div class="col-12 row m-0 p-0">
                                    <div class="checker col-12 row my-2 p-0 mx-0">
                                        <div class="col-12 p-0 m-0 sq-switch sq-switch-sm">
                                            <input type="hidden" name="flag_regex" value="0"/>
                                            <input type="checkbox" id="flag_regex<?php echo (int) $row->id ?>" name="flag_regex" class="sq-switch" <?php echo( $flags['flag_regex'] ? 'checked="checked"' : '' ) ?> value="1"/>
                                            <label for="flag_regex<?php echo (int) $row->id ?>" class="ml-1"><?php echo esc_html__( "Regex Redirect Match", 'squirrly-seo-pack' ); ?></label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 row m-0 p-0">
                                    <div class="checker col-12 row my-2 p-0 mx-0">
                                        <div class="col-12 p-0 m-0 sq-switch sq-switch-sm">
                                            <input type="hidden" name="flag_case" value="0"/>
                                            <input type="checkbox" id="flag_case<?php echo (int) $row->id ?>" name="flag_case" class="sq-switch" <?php echo( $flags['flag_case'] ? 'checked="checked"' : '' ) ?> value="1"/>
                                            <label for="flag_case<?php echo (int) $row->id ?>" class="ml-1"><?php echo esc_html__( "Ignore Case", 'squirrly-seo-pack' ); ?></label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 row m-0 p-0">
                                    <div class="checker col-12 row my-2 p-0 mx-0">
                                        <div class="col-12 p-0 m-0 sq-switch sq-switch-sm">
                                            <input type="hidden" name="flag_trailing" value="0"/>
                                            <input type="checkbox" id="flag_trailing<?php echo (int) $row->id ?>" name="flag_trailing" class="sq-switch" <?php echo( $flags['flag_trailing'] ? 'checked="checked"' : '' ) ?> value="1"/>
                                            <label for="flag_trailing<?php echo (int) $row->id ?>" class="ml-1"><?php echo esc_html__( "Ignore Slash", 'squirrly-seo-pack' ); ?></label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 row m-0 p-0 my-3">
                                    <div class="m-0 p-1 font-weight-bold">
                                        <label for="sq_position<?php echo (int) $row->id ?>"><?php echo esc_html__( "Redirect Order", 'squirrly-seo-pack' ); ?></label>
                                    </div>
                                    <div class="col-1 p-0">
                                        <input type="text" class="form-control" name="position" id="sq_position<?php echo (int) $row->id ?>" maxlength="255" value="<?php echo esc_attr( $row->position ) ?>"/>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary"><?php echo esc_html__( "Save Redirect", 'squirrly-seo-pack' ); ?></button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
	<?php } ?>
</div>

