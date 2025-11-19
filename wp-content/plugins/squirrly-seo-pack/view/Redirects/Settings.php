<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );
if ( ! isset( $view ) ) {
	return;
}

/**
 * Redirects Settings view
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

                <div class="sq_breadcrumbs my-4"><?php echo apply_filters( 'sqp_menu_breadcrumbs', SQP_Classes_Helpers_Tools::getValue( 'page' ) . '/' . SQP_Classes_Helpers_Tools::getValue( 'tab', 'settings' ) ) ?></div>

                <form method="POST">
					<?php SQP_Classes_Helpers_Tools::setNonce( 'sqp_settings_update', 'sq_nonce' ); ?>
                    <input type="hidden" name="action" value="sqp_settings_update"/>

					<?php $settings = SQP_Classes_Helpers_Tools::getOption( 'sq_redirect_flags' ); ?>

                    <h3 class="mt-4 card-title">
						<?php echo esc_html__( "Redirects Settings", 'squirrly-seo-pack' ); ?>
                        <div class="sq_help_question d-inline">
                            <a href="https://howto12.squirrly.co/kb/advanced-redirects-module/#settings" target="_blank"><i class="fa-solid fa-question-circle"></i></a>
                        </div>
                    </h3>

                    <div id="sqp_settings" class="col-12 p-0 m-0">
                        <div class="col-12 m-0 p-0">
                            <div class="col-12 m-0 p-0">

                                <div class="col-12 m-0 p-0">

                                    <div class="col-12 row m-0 p-0">
                                        <div class="checker col-12 row my-4 p-0 mx-0">
                                            <div class="col-12 p-0 m-0 sq-switch sq-switch-sm">
                                                <input type="hidden" name="sq_redirects_cache" value="0"/>
                                                <input type="checkbox" id="sq_redirects_cache" name="sq_redirects_cache" class="sq-switch" <?php echo( SQP_Classes_Helpers_Tools::getOption( 'sq_redirects_cache' ) ? 'checked="checked"' : '' ) ?> value="1"/>
                                                <label for="sq_redirects_cache" class="ml-1"><?php echo esc_html__( "Use Redirect Cache", 'squirrly-seo-pack' ); ?>
                                                    <a href="https://howto12.squirrly.co/kb/advanced-redirects-module/#redirect_cache" target="_blank"><i class="fa-solid fa-question-circle m-0 px-2" style="display: inline;"></i></a>
                                                </label>
                                                <div class="small text-black-50 ml-5"><?php echo sprintf( esc_html__( "Load redirects from cache and improve the redirect speed.", 'squirrly-seo-pack' ), '<strong>', '</strong>' ); ?></div>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="col-12 row m-0 p-0">
                                        <div class="checker col-12 row my-4 p-0 mx-0">
                                            <div class="col-12 p-0 m-0 sq-switch sq-switch-sm">
                                                <input type="hidden" name="sq_redirects_track_hits" value="0"/>
                                                <input type="checkbox" id="sq_redirects_track_hits" name="sq_redirects_track_hits" class="sq-switch" <?php echo( SQP_Classes_Helpers_Tools::getOption( 'sq_redirects_track_hits' ) ? 'checked="checked"' : '' ) ?> value="1"/>
                                                <label for="sq_redirects_track_hits" class="ml-1"><?php echo esc_html__( "Track Redirect Hits", 'squirrly-seo-pack' ); ?>
                                                    <a href="https://howto12.squirrly.co/kb/advanced-redirects-module/#track_redirects" target="_blank"><i class="fa-solid fa-question-circle m-0 px-2" style="display: inline;"></i></a>
                                                </label>
                                                <div class="small text-black-50 ml-5"><?php echo sprintf( esc_html__( "Track redirect hits and date of last access. Contains no user information.", 'squirrly-seo-pack' ), '<strong>', '</strong>' ); ?></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 row m-0 p-0 my-4 sq_advanced">
                                        <div class="col-4 p-0 pr-3 font-weight-bold">
                                            <div class="font-weight-bold"><?php echo esc_html__( "Default URL Matching", 'squirrly-seo-pack' ); ?>
                                                :
                                                <a href="https://howto12.squirrly.co/kb/advanced-redirects-module/#url_matching" target="_blank"><i class="fa-solid fa-question-circle m-0 px-2" style="display: inline;"></i></a>
                                            </div>
                                            <div class="small text-black-50 my-1 pr-3"><?php echo esc_html__( "Select the default URL matching when adding new rules.", 'squirrly-seo-pack' ); ?></div>
                                        </div>
                                        <div class="col-8 p-0 input-group">
                                            <select name="flag_query" class="form-control bg-input mb-1">
												<?php
												foreach ( SQP_Classes_ObjController::getClass( 'SQP_Controllers_Redirects' )->getMatchTypes() as $value => $option ) { ?>
                                                    <option <?php selected( $settings['flag_query'], $value ) ?> value="<?php echo esc_attr( $value ) ?>"><?php echo esc_html( $option ); ?></option>
													<?php
												}
												?>
                                            </select>

                                            <div class="text-black-50 small my-2 p-0">
                                                <ul>
                                                    <li>Exact - matches the query parameters exactly defined in your
                                                        source, in any order
                                                    </li>
                                                    <li>Ignore - as exact, but ignores any query parameters not in your
                                                        source
                                                    </li>
                                                    <li>Pass - as ignore, but also copies the query parameters to the
                                                        target
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="col-12 row m-0 p-0 my-4 sq_advanced">
                                        <div class="col-4 p-0 pr-3 font-weight-bold">
                                            <div class="font-weight-bold"><?php echo esc_html__( "Default Redirect Type", 'squirrly-seo-pack' ); ?>
                                                :
                                                <a href="https://howto12.squirrly.co/kb/advanced-redirects-module/#redirect_type" target="_blank"><i class="fa-solid fa-question-circle m-0 px-2" style="display: inline;"></i></a>
                                            </div>
                                            <div class="small text-black-50 my-1 pr-3"><?php echo esc_html__( "Select the code that will be added in the header of the redirect.", 'squirrly-seo-pack' ); ?></div>
                                        </div>
                                        <div class="col-8 p-0 input-group">
                                            <select name="sq_redirects_action_code" class="form-control bg-input mb-1 border">
												<?php foreach ( SQP_Classes_ObjController::getClass( 'SQP_Controllers_Redirects' )->getRedirectCodes() as $code => $title ) { ?>
                                                    <option value="<?php echo esc_attr( $code ) ?>" <?php selected( SQP_Classes_Helpers_Tools::getOption( 'sq_redirects_action_code' ), $code ) ?> ><?php echo esc_html( $title ) ?></option>
												<?php } ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-12 row m-0 p-0 sq_advanced">
                                        <div class="checker col-12 row my-4 p-0 mx-0">
                                            <div class="col-12 p-0 m-0 sq-switch sq-switch-sm">
                                                <input type="hidden" name="flag_case" value="0"/>
                                                <input type="checkbox" id="flag_case" name="flag_case" class="sq-switch" <?php echo( $settings['flag_case'] ? 'checked="checked"' : '' ) ?> value="1"/>
                                                <label for="flag_case" class="ml-1"><?php echo esc_html__( "Default Ignore Case", 'squirrly-seo-pack' ); ?>
                                                    <a href="https://howto12.squirrly.co/kb/advanced-redirects-module/#ignore_case" target="_blank"><i class="fa-solid fa-question-circle m-0 px-2" style="display: inline;"></i></a>
                                                </label>
                                                <div class="small text-black-50 ml-5"><?php echo sprintf( esc_html__( "Case insensitive matches (i.e. /Source-URL will match /source-post)", 'squirrly-seo-pack' ), '<strong>', '</strong>' ); ?></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 row m-0 p-0 sq_advanced">
                                        <div class="checker col-12 row my-4 p-0 mx-0">
                                            <div class="col-12 p-0 m-0 sq-switch sq-switch-sm">
                                                <input type="hidden" name="flag_trailing" value="0"/>
                                                <input type="checkbox" id="flag_trailing" name="flag_trailing" class="sq-switch" <?php echo( $settings['flag_trailing'] ? 'checked="checked"' : '' ) ?> value="1"/>
                                                <label for="flag_trailing" class="ml-1"><?php echo esc_html__( "Default Ignore Slash", 'squirrly-seo-pack' ); ?>
                                                    <a href="https://howto12.squirrly.co/kb/advanced-redirects-module/#ignore_slash" target="_blank"><i class="fa-solid fa-question-circle m-0 px-2" style="display: inline;"></i></a>
                                                </label>
                                                <div class="small text-black-50 ml-5"><?php echo sprintf( esc_html__( "Ignore trailing slashes (i.e. /source-post/ will match /source-post)", 'squirrly-seo-pack' ), '<strong>', '</strong>' ); ?></div>
                                            </div>
                                        </div>
                                    </div>

                                    <h3 class="mt-5 card-title">
										<?php echo esc_html__( "Post Types Settings", 'squirrly-seo-pack' ); ?>
                                    </h3>

									<?php
									$patterns = (array) SQP_Classes_Helpers_Tools::getOption( 'patterns' );
									$types    = (array) get_post_types( array( 'public' => true ) );
									?>

									<?php if ( ! empty( $types ) ) { ?>
                                        <div class="col-12 row m-0 p-0 my-5">
                                            <div class="col-4 p-0 font-weight-bold">
												<?php echo esc_html__( "Redirect Changed Paths", 'squirrly-seo-pack' ); ?>
                                                :
                                                <a href="https://howto12.squirrly.co/kb/advanced-redirects-module/#redirect_changed_paths" target="_blank"><i class="fa-solid fa-question-circle m-0 px-2" style="display: inline;"></i></a>
                                                <div class="small text-black-50 pr-3 my-1"><?php echo esc_html__( "Redirect to the new path when it's changed with a new path in Post Editor.", 'squirrly-seo-pack' ); ?></div>
                                            </div>
                                            <div class="col-8 p-0 m-0 form-group">
                                                <input type="hidden" name="sq_post_type_redirects[]" value="0"/>
                                                <select multiple name="sq_post_type_redirects[]" class="selectpicker form-control bg-input mb-3" data-live-search="true">
													<?php
													if ( ! empty( $types ) && ! empty( $patterns ) ) {
														foreach ( $types as $type ) {

															if ( ! in_array( $type, array_keys( $patterns ) ) || ! isset( $patterns[ $type ]['do_redirects'] ) ) {
																continue;
															}

															$itemname = ucwords( str_replace( array(
																'-',
																'_'
															), ' ', esc_attr( $type ) ) );

															?>
                                                            <option value="<?php echo esc_attr( $type ) ?>" <?php selected( $patterns[ $type ]['do_redirects'], true ) ?>><?php echo esc_html( $itemname ) ?></option><?php
														}
													} ?>
                                                </select>
                                                <div class="small text-black-50 pr-3 my-1"><?php echo esc_html__( "Select the post types you want to monitor when a path is changed.", 'squirrly-seo-pack' ); ?></div>

                                            </div>
                                        </div>
									<?php } ?>

									<?php if ( isset( $patterns[404] ) ) { ?>
                                        <div class="col-12 row m-0 p-0 my-4">
                                            <div class="col-4 p-0 m-0">
                                                <div class="checker col-12 row p-0 mx-0">
                                                    <div class="col-12 p-0 m-0 sq-switch sq-switch-sm">
                                                        <input type="hidden" name="sq_404_redirects" value="0"/>
                                                        <input type="checkbox" id="sq_404_redirects" name="sq_404_redirects" class="sq-switch" <?php echo( $patterns[404]['do_redirects'] ? 'checked="checked"' : '' ) ?> value="1"/>
                                                        <label for="sq_404_redirects" class="ml-1"><?php echo esc_html__( "Redirect Broken URLs", 'squirrly-seo-pack' ); ?>
                                                        </label>
                                                        <div class="small text-black-50 ml-5"><?php echo sprintf( esc_html__( "Redirect all the not found posts to a specific URL and prevent 404 pages.", 'squirrly-seo-pack' ), '<strong>', '</strong>' ); ?></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-8 p-0 m-0">
                                                <input type="text" class="form-control bg-input" name="404_url_redirect" value="<?php echo esc_attr( SQP_Classes_Helpers_Tools::getOption( '404_url_redirect' ) ) ?>"/>
                                            </div>
                                        </div>
									<?php } ?>


                                    <h3 class="mt-5 card-title">
										<?php echo esc_html__( "Logging", 'squirrly-seo-pack' ); ?>
                                    </h3>

                                    <div class="col-12 row m-0 p-0">
                                        <div class="checker col-12 row my-4 p-0 mx-0">
                                            <div class="col-12 p-0 m-0 sq-switch sq-switch-sm">
                                                <input type="hidden" name="sq_redirects_log_404" value="0"/>
                                                <input type="checkbox" id="sq_redirects_log_404" name="sq_redirects_log_404" class="sq-switch" <?php echo( SQP_Classes_Helpers_Tools::getOption( 'sq_redirects_log_404' ) ? 'checked="checked"' : '' ) ?> value="1"/>
                                                <label for="sq_redirects_log_404" class="ml-1"><?php echo esc_html__( "404 Monitor", 'squirrly-seo-pack' ); ?>
                                                    <a href="https://howto12.squirrly.co/kb/advanced-redirects-module/#404_monitor" target="_blank"><i class="fa-solid fa-question-circle m-0 px-2" style="display: inline;"></i></a>
                                                </label>
                                                <div class="small text-black-50 ml-5"><?php echo sprintf( esc_html__( "Monitor & keep a log with all the 404 errors.", 'squirrly-seo-pack' ), '<strong>', '</strong>' ); ?></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 row m-0 p-0">
                                        <div class="checker col-12 row my-4 p-0 mx-0">
                                            <div class="col-12 p-0 m-0 sq-switch sq-switch-sm">
                                                <input type="hidden" name="sq_redirects_log_hits" value="0"/>
                                                <input type="checkbox" id="sq_redirects_log_hits" name="sq_redirects_log_hits" class="sq-switch" <?php echo( SQP_Classes_Helpers_Tools::getOption( 'sq_redirects_log_hits' ) ? 'checked="checked"' : '' ) ?> value="1"/>
                                                <label for="sq_redirects_log_hits" class="ml-1"><?php echo esc_html__( "Log Redirects", 'squirrly-seo-pack' ); ?>
                                                    <a href="https://howto12.squirrly.co/kb/advanced-redirects-module/#log_redirects" target="_blank"><i class="fa-solid fa-question-circle m-0 px-2" style="display: inline;"></i></a>
                                                </label>
                                                <div class="small text-black-50 ml-5"><?php echo sprintf( esc_html__( "Keep a log with all the redirects.", 'squirrly-seo-pack' ), '<strong>', '</strong>' ); ?></div>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="col-12 row m-0 p-0 ml-5 sq_redirects_log_hits">
                                        <div class="col-4 p-0 pr-3 font-weight-bold">
                                            <div class="font-weight-bold"><?php echo esc_html__( "Log days", 'squirrly-seo-pack' ); ?>
                                                :
                                            </div>
                                            <div class="small text-black-50 my-1 pr-3"><?php echo esc_html__( "Select the number of days you want to keep the log for.", 'squirrly-seo-pack' ); ?></div>
                                        </div>
                                        <div class="col-8 p-0 input-group">
                                            <select name="sq_redirects_log_days" class="form-control bg-input mb-1">
												<?php

												$days = array( '1', '7', '30', '60', '90', '365' );
												foreach ( $days as $row ) { ?>
                                                    <option <?php selected( SQP_Classes_Helpers_Tools::getOption( 'sq_redirects_log_days' ), $row ) ?> value="<?php echo esc_attr( $row ) ?>"><?php echo esc_html( $row ); ?><?php echo esc_html__( "days", 'squirrly-seo-pack' ); ?></option>
													<?php
												}
												?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-12 row m-0 p-0 ml-5 sq_redirects_log_hits sq_advanced">
                                        <div class="checker col-12 row my-4 p-0 mx-0">
                                            <div class="col-12 p-0 m-0 sq-switch sq-switch-sm">
                                                <input type="hidden" name="sq_redirects_log_header" value="0"/>
                                                <input type="checkbox" id="sq_redirects_log_header" name="sq_redirects_log_header" class="sq-switch" <?php echo( SQP_Classes_Helpers_Tools::getOption( 'sq_redirects_log_header' ) ? 'checked="checked"' : '' ) ?> value="1"/>
                                                <label for="sq_redirects_log_header" class="ml-1"><?php echo esc_html__( "Log HTTP Header", 'squirrly-seo-pack' ); ?>
                                                    <a href="https://howto12.squirrly.co/kb/advanced-redirects-module/#log_http_header" target="_blank"><i class="fa-solid fa-question-circle m-0 px-2" style="display: inline;"></i></a>
                                                </label>
                                                <div class="small text-black-50 ml-5"><?php echo sprintf( esc_html__( "Capture HTTP header information with logs (except cookies). It may include user information, and could increase your log size.", 'squirrly-seo-pack' ), '<strong>', '</strong>' ); ?></div>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                            </div>

                        </div>
                    </div>

                    <div class="col-12 my-3 p-0">
                        <button type="submit" class="btn rounded-0 btn-primary btn-lg m-0 px-5 "><?php echo esc_html__( "Save Settings", 'squirrly-seo-pack' ); ?></button>
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
