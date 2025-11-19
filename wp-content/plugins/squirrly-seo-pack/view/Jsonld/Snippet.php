<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );
if ( ! isset( $view ) ) {
	return;
}

/**
 * Snippet view
 *
 */
?>
<div class="sq-card sq-border-0">
    <div class="sq-card-body sq_tab_meta sq_tabcontent sq-m-0 sq-p-0 sq-py-4 <?php echo ( $view->post->sq_adm->doseo == 0 ) ? 'sq-d-none' : ''; ?>">

		<?php if ( ! $view->post->sq->do_jsonld ) { ?>
            <div class="sq_deactivated_label sq-col-12 sq-row sq-m-0 sq-p-2 sq-pr-3 sq_save_ajax">
                <div class="sq-col-12 sq-p-0 sq-text-center sq-small">
					<?php echo sprintf( esc_html__( "JSON-LD is disable for this Post Type (%s). See %s Squirrly > Automation > Configuration %s.", 'squirrly-seo-pack' ), esc_attr( $view->post->post_type ), '<a href="' . esc_url( SQP_Classes_Helpers_Tools::getAdminUrl( 'sq_automation', 'automation' ) . '#tab=sq_' . esc_attr( $view->post->post_type ) ) . '" target="_blank"><strong>', '</strong></a>' ) ?>
                </div>
            </div>
		<?php } ?>

        <div class="sq-row sq-mx-0 sq-px-0 <?php echo( ( ! $view->post->sq->do_jsonld ) ? 'sq_deactivated' : '' ); ?>">

            <div class="sq-col-sm sq-text-right sq-m-0 sq-p-0 sq-mb-2 sq-pb-2">
                <input type="button" class="sq_snippet_btn_refresh sq-btn sq-btn-sm sq-btn-link sq-px-3 sq-rounded-0 sq-font-weight-bold" value="<?php echo esc_attr__( "Refresh", 'squirrly-seo-pack' ) ?>"/>
                <input type="button" class="sq_snippet_btn_save sq-btn sq-btn-sm sq-btn-primary sq-px-5 sq-mx-5 sq-rounded-0" value="<?php echo esc_attr__( "Save", 'squirrly-seo-pack' ) ?>"/>
            </div>

            <div class="sq_snippet_submenu sq-col-12 sq-p-0 sq-m-0 sq-bg-nav">
                <ul class="sq-col-12 sq-p-0 sq-m-0 sq-nav sq-nav-tabs">
                    <li class="sq-nav-item">
                        <a href="#sqtabjson<?php echo esc_attr( $view->post->hash ) ?>1" class="sq-nav-item sq-nav-link sq-py-3 sq-text-dark sq-font-weight-bold active" data-toggle="sqtab"><?php echo esc_html__( "Schemas", 'squirrly-seo-pack' ) ?></a>
                    </li>
                    <li class="sq-nav-item">
                        <a href="#sqtabjson<?php echo esc_attr( $view->post->hash ) ?>2" class="sq-nav-item sq-nav-link sq-py-3 sq-text-dark sq-font-weight-bold" data-toggle="sqtab"><?php echo esc_html__( "JSON-LD Code", 'squirrly-seo-pack' ) ?></a>
                    </li>
                </ul>
            </div>

			<?php
			$sq_jsonld_types = array();
			$patterns        = SQP_Classes_Helpers_Tools::getOption( 'patterns' );

			if ( ! isset( $patterns[ $view->post->post_type ] ) && isset( $patterns['custom'] ) ) {
				$patterns[ $view->post->post_type ] = $patterns['custom'];
			}

			if ( isset( $patterns[ $view->post->post_type ]['jsonld_types'] ) ) {
				$sq_jsonld_types = $patterns[ $view->post->post_type ]['jsonld_types'];
			}

			//get all reusable schemas
			$reusable_jsonld_types = SQP_Classes_ObjController::getClass( 'SQP_Models_Jsonld' )->getReusableJsonLdTypes();
			$jsonld_types          = apply_filters( 'sq_jsonld_types', array(), false );
			?>

            <div class="sq-tab-content sq-d-flex sq-flex-column sq-flex-grow-1 sq-bg-white sq-p-3">
                <div id="sqtabjson<?php echo esc_attr( $view->post->hash ) ?>1" class="sq-tab-panel" role="tabpanel">
                    <div class="sq-row sq-mx-0 sq-px-0">
                        <div class="sq-col-12 sq-p-0 sq-input-group">
                            <ul class="sq-col-12 sq-p-0 sq-m-0">
								<?php foreach ( $view->post->sq->jsonld_types as $jsonld_type ) {
									//set the current type id
									$name = $jsonld_type;

									//check if reusable schema
									if ( isset( $reusable_jsonld_types[ $jsonld_type ] ) ) {
										$name = $reusable_jsonld_types[ $jsonld_type ];
									}

									?>
                                    <li class="sq_dropdown_parent sq-py-2 sq-px-3 sq-my-2 sq-border sq-rounded" data-jsonld-type="<?php echo esc_attr( $jsonld_type ) ?>" style="position: relative;">
                                        <div class="sq-col-12 sq-row sq-p-0 sq-m-0">
                                            <div class="sq-col">
												<?php echo esc_attr( ucfirst( $name ) ); ?>
												<?php
												if ( empty( $view->post->sq_adm->jsonld_types ) && ! empty( $sq_jsonld_types ) && in_array( $jsonld_type, $sq_jsonld_types ) ) {
													echo '(' . esc_html__( "Automation", 'squirrly-seo-pack' ) . ')';
												}
												?>
                                            </div>
                                            <div class="sq-col sq-text-right">
                                                <i class="sq_dropdown_icon sq-col sq-p-1 sq-m-0 sq-cursor fa-solid fa-edit" style="cursor: pointer"></i>
                                                <i class="sq_jsonld_type_delete sq-col sq-p-1 sq-m-0 fa-solid fa-close" style="cursor: pointer"></i>
                                            </div>
                                        </div>
                                        <div class="sq_dropdown_list sq-col-12 sq-row sq-p-0" style="display: none"></div>
                                    </li>
								<?php } ?>
                            </ul>


                            <select class="sq_jsonld_types" multiple name="sq_jsonld_types[]" style="display: none">
                                <option value=""></option>
								<?php foreach ( $jsonld_types as $post_type => $jsonld_type ) { ?>
                                    <option <?php echo( in_array( $jsonld_type, (array) $view->post->sq_adm->jsonld_types ) ? 'selected="selected"' : '' ) ?> value="<?php echo esc_attr( $jsonld_type ) ?>">
										<?php echo esc_attr( ucwords( $jsonld_type ) ) ?>
                                    </option>
								<?php } ?>
								<?php
								if ( ! empty( $reusable_jsonld_types ) ) {
									foreach ( $reusable_jsonld_types as $jsonld_id => $reusable_jsonld_type ) { ?>
                                        <option <?php echo( in_array( $jsonld_id, (array) $view->post->sq_adm->jsonld_types ) ? 'selected="selected"' : '' ) ?> value="<?php echo esc_attr( $jsonld_id ) ?>">
											<?php echo esc_html( ucfirst( $reusable_jsonld_type ) ) ?>
                                        </option>
									<?php }
								} ?>

                            </select>

                            <button type="button" class="sq_jsonld_show_types sq-btn sq-btn-sm sq-btn-primary sq-mx-2"><?php echo esc_html__( "Add New Schema", 'squirrly-seo-pack' ) ?></button>
							<?php if ( SQP_Classes_Helpers_Tools::getOption( 'sq_jsonld_reusable_schemas' ) ) { ?>
                                <button type="button" class="sq_jsonld_show_reusable_types sq-btn sq-btn-sm sq-btn-light sq-text-dark sq-border sq-mx-2"><?php echo esc_html__( "Add Reusable Schema", 'squirrly-seo-pack' ) ?></button>
							<?php } ?>
                        </div>
                    </div>
                </div>
                <div id="sqtabjson<?php echo esc_attr( $view->post->hash ) ?>2" class="sq-tab-panel" role="tabpanel">

                    <div class="sq-col-12 sq-p-0 sq-m-0 sq-small">

                        <button type="button" id="validateRichResults"
                                class="sq-btn sq-btn-light sq-btn-sm sq-text-dark sq-border sq-px-4 sq-mx-2 sq-float-right">
                            <i class="fasq-brands fa-google"></i> <?php echo esc_html__( "Validate JSON-LD", 'squirrly-seo-pack' ); ?>
                        </button>

                        <textarea class="code_snippet" style="display:none"></textarea>

                        <script>
                            document.getElementById('validateRichResults').addEventListener('click', function() {
                                const code = document.querySelector('.code_snippet').value;

                                const form = document.createElement('form');
                                form.method = 'post';
                                form.action = 'https://search.google.com/test/rich-results';
                                form.target = '_blank';

                                const input = document.createElement('textarea');
                                input.name = 'code_snippet';
                                input.value = code;

                                form.appendChild(input);
                                document.body.appendChild(form);
                                form.submit();
                                form.remove();
                            });
                        </script>


                    </div>

                    <div class="sq-pretty-json">
						<pre class="code-output">
							<code id="code_snippet_<?php echo esc_attr( $view->post->hash ) ?>" class="code_snippet"></code>
						</pre>
                    </div>

                </div>
            </div>

			<?php if ( SQP_Classes_Helpers_Tools::getOption( 'sq_jsonld_breadcrumbs' ) && $view->post->ID ) {
				/** @var SQ_Models_Domain_Categories $categories */
				$categories    = SQ_Classes_ObjController::getClass( 'SQ_Models_Domain_Categories' );
				$allcategories = $categories->getAllCategories( $view->post->ID );
				if ( ! empty( $allcategories ) && count( $allcategories ) > 1 ) {
					?>
                    <div class="sq-col-12 sq-row sq-my-2 sq-px-0 sq-mx-0 sq-py-1 sq-px-3">

                        <div class="sq-col-4 sq-p-0 sq-pr-3 sq-font-weight-bold">
							<?php echo esc_html__( "Primary Category", "squirrly-seo" ); ?>
                            <a href="https://howto12.squirrly.co/kb/json-ld-structured-data/#breadcrumbs_schema" target="_blank"><i class="fa-solid fa-question-circle sq-m-0 sq-px-1 sq-d-inline"></i></a>
                            <div class="sq-small sq-text-black-50 sq-my-0 sq-pr-4"><?php echo esc_html__( "Set the Primary Category for Breadcrumbs.", 'squirrly-seo' ); ?></div>
                        </div>

                        <div class="sq-col-8 sq-p-0 sq-input-group">
                            <select name="sq_primary_category" class="sq_primary_category sq-form-control sq-bg-input sq-mb-1">
								<?php

								foreach ( $allcategories as $id => $category ) { ?>
                                    <option <?php echo( ( $id == $view->post->sq_adm->primary_category ) ? 'selected="selected"' : '' ) ?> value="<?php echo (int) $id ?>">
										<?php echo esc_html( ucwords( $category ) ) ?>
                                    </option>
								<?php }
								?>
                            </select>
                        </div>

                    </div>
				<?php }
			} ?>
        </div>

    </div>

    <div class="sq-card-footer sq-border-0 sq-py-0 sq-my-0 <?php echo ( $view->post->sq_adm->doseo == 0 ) ? 'sq-mt-5' : ''; ?>">
        <div class="sq-row sq-mx-0 sq-px-0">
            <div class="sq-text-center sq-col-12 sq-my-4 sq-mx-0 sq-px-0 sq-text-danger" style="font-size: 18px; <?php echo ( $view->post->sq_adm->doseo == 1 ) ? 'display: none' : ''; ?>">
				<?php echo esc_html__( "To edit the snippet, you have to activate Squirrly SEO for this page first", 'squirrly-seo-pack' ) ?>
            </div>
        </div>

    </div>
</div>




