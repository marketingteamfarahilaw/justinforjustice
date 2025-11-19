<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );
if ( ! isset( $view ) ) {
	return;
}

/**
 * Redirects Log view
 *
 */
?>
<div id="sq_wrap">
	<?php echo apply_filters( 'sqp_menu_toolbar', false ); ?>
	<?php do_action( 'sq_notices' ); ?>
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

                <div class="sq_breadcrumbs my-4"><?php echo apply_filters( 'sqp_menu_breadcrumbs', SQP_Classes_Helpers_Tools::getValue( 'page' ) . '/' . SQP_Classes_Helpers_Tools::getValue( 'tab', 'log' ) ) ?></div>

                <h3 class="mt-4 card-title">
					<?php echo esc_html__( "Redirects Log", 'squirrly-seo-pack' ); ?>
                    <div class="sq_help_question d-inline">
                        <a href="https://howto12.squirrly.co/kb/advanced-redirects-module/#redirects_log" target="_blank"><i class="fa-solid fa-question-circle"></i></a>
                    </div>
                </h3>

                <div id="sq_redirects" class="col-12 m-0 p-0 border-0">

                    <div class="col-12 m-0 p-0 my-4">

                        <div class="row col-12 m-0 p-0 py-3">

                            <form id="sq_type_form" method="get" class="form-inline col p-0 m-0">
                                <input type="hidden" name="page" value="<?php echo esc_attr( SQP_Classes_Helpers_Tools::getValue( 'page' ) ) ?>">
                                <input type="hidden" name="tab" value="<?php echo esc_attr( SQP_Classes_Helpers_Tools::getValue( 'tab' ) ) ?>">
                                <input type="hidden" name="stype" value="<?php echo esc_attr( SQP_Classes_Helpers_Tools::getValue( 'stype' ) ) ?>">
                                <input type="hidden" name="snum" value="<?php echo esc_attr( SQP_Classes_Helpers_Tools::getValue( 'snum' ) ) ?>">

                                <div class="col-12 row p-0 m-0">
                                    <select name="scode" class="w-100 m-0 p-1" onchange="jQuery('form#sq_type_form').submit();">
										<?php
										foreach ( $view->getRedirectCodes() as $code => $title ) {
											?>
                                            <option value="<?php echo esc_attr( $code ) ?>" <?php echo selected( SQP_Classes_Helpers_Tools::getValue( 'scode' ), $code ) ?> ><?php echo esc_html( ucwords( $title ) ); ?></option>
											<?php
										}
										?>
                                    </select>
                                </div>
                            </form>
                            <div class="col p-0 text-right">
                                <form method="get" class="d-flex flex-row justify-content-end p-0 m-0">
                                    <input type="hidden" name="page" value="<?php echo esc_attr( SQP_Classes_Helpers_Tools::getValue( 'page' ) ) ?>">
                                    <input type="hidden" name="tab" value="<?php echo esc_attr( SQP_Classes_Helpers_Tools::getValue( 'tab' ) ) ?>">
                                    <input type="hidden" name="stype" value="<?php echo esc_attr( SQP_Classes_Helpers_Tools::getValue( 'stype' ) ) ?>">
                                    <input type="hidden" name="snum" value="<?php echo esc_attr( SQP_Classes_Helpers_Tools::getValue( 'snum' ) ) ?>">
                                    <input type="search" class="d-inline-block align-middle col-7 py-0 px-2 mr-0 rounded-0" id="post-search-input" name="squery" value="<?php echo esc_attr( SQP_Classes_Helpers_Tools::getValue( 'squery' ) ) ?>" placeholder="<?php echo esc_attr__( "Write the redirect you want to search for", 'squirrly-seo-pack' ) ?>"/>
                                    <input type="submit" class="btn btn-primary" value="<?php echo esc_attr__( "Search", 'squirrly-seo-pack' ) ?> >"/>
									<?php if ( SQp_Classes_Helpers_Tools::getIsset( 'squery' ) ) { ?>
                                        <button type="button" class="btn btn-link text-primary ml-1" onclick="location.href = '<?php echo esc_url( SQP_Classes_Helpers_Tools::getAdminUrl( 'sq_redirects', 'log' ) ) ?>';" style="cursor: pointer"><?php echo esc_html__( "Show All", 'squirrly-seo-pack' ) ?></button>
									<?php } ?>
                                </form>
                            </div>
                        </div>

                        <div class="p-0">
                            <table class="table table-striped table-hover">
                                <thead>
                                <tr>
                                    <th style="width: 30%;"><?php echo esc_html__( "URL From", 'squirrly-seo-pack' ) ?></th>
									<?php if ( SQP_Classes_Helpers_Tools::getValue( 'scode' ) <> '404' ) { ?>
                                        <th style="width: 30%;"><?php echo esc_html__( "URL To", 'squirrly-seo-pack' ) ?></th>
									<?php } ?>
                                    <th scope="col"><?php echo esc_html__( "Code", 'squirrly-seo-pack' ) ?></th>
                                    <th scope="col"><?php echo esc_html__( "Access", 'squirrly-seo-pack' ) ?></th>
                                </tr>
                                </thead>
                                <tbody>
								<?php
								if ( ! empty( $view->rewrite_log ) ) {
									foreach ( $view->rewrite_log as $key => $row ) { ?>
                                        <tr>

                                            <td style="width: 30%;" class="text-left">
												<?php echo esc_html( $row->url ); ?>
                                            </td>
											<?php if ( SQP_Classes_Helpers_Tools::getValue( 'scode' ) <> '404' ) { ?>
                                                <td style="width: 30%;" class="text-left">
													<?php echo esc_html( $row->sent_to ) ?>
                                                </td>
											<?php } ?>

                                            <td style="width: 10%; white-space: nowrap;">
												<?php echo esc_html( $row->http_code ) ?>
                                            </td>
                                            <td style="width: 15%; white-space: nowrap;" class="text-nowrap">
												<?php echo esc_html( $row->created ) ?>
                                            </td>

                                        </tr>
										<?php
									}
								} else { ?>
                                    <tr>
                                        <td colspan="6" class="text-center">
											<?php echo esc_html__( "No log found", 'squirrly-seo-pack' ); ?>
                                        </td>
                                    </tr>
								<?php } ?>

                                </tbody>
                            </table>

                            <div class="row p-0 m-0 my-2">
                                <div class="col float-left p-0 m-0 mr-3">
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
                                <div class="float-right p-0 m-0">
									<?php echo SQP_Classes_Helpers_Tools::pagination( $view->max_num_pages ); ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-6 p-0 m-0">
                            <form method="post" class="p-0 m-0">
								<?php SQP_Classes_Helpers_Tools::setNonce( 'sqp_log_delete', 'sq_nonce' ); ?>
                                <input type="hidden" name="action" value="sqp_log_delete"/>
                                <input type="submit" class="btn btn-link text-primary p-0 m-0" onclick="return confirm('<?php echo esc_html__( "Are you sure you want to empty the log?", 'squirrly-seo-pack' ) ?>')" value="<?php echo esc_attr__( "Clear All Log", 'squirrly-seo-pack' ) ?>"/>
                            </form>


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

