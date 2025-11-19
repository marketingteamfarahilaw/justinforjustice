<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );
if ( ! isset( $view ) ) {
	return;
}

/**
 * Images view
 *
 */
?>
<div class="col-12 m-0 p-0 my-5 px-3">
    <form id="sq_inport_form" name="import" action="" method="post" enctype="multipart/form-data">
        <div class="col-12 row m-0 p-0 my-5">
            <div class="col-5 m-0 p-0 pr-2 font-weight-bold">
                <div class="font-weight-bold"><?php echo esc_html__( "Restore Schemas", 'squirrly-seo-pack' ); ?>:</div>
                <div class="small text-black-50 my-1 pr-3"><?php echo esc_html__( "Upload the file with the saved Squirrly Schemas for Rich Snippets.", 'squirrly-seo-pack' ); ?></div>
            </div>
            <div class="col-7 p-0 input-group">
                <div class="form-group m-0 p-0">
                    <input type="file" class="form-control-file border" style="height: 48px; line-height: 35px;" name="sq_jsonld">
                </div>
				<?php SQP_Classes_Helpers_Tools::setNonce( 'sq_jsonld_restore', 'sq_nonce' ); ?>
                <input type="hidden" name="action" value="sq_jsonld_restore"/>
                <button type="submit" class="btn rounded-0 btn-primary px-3 m-0" style="min-width: 160px"><?php echo esc_html__( "Restore Schemas", 'squirrly-seo-pack' ); ?></button>
            </div>
        </div>
    </form>
</div>