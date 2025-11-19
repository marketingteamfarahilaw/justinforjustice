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
                <div class="font-weight-bold"><?php echo esc_html__( "Backup Redirects", 'squirrly-seo-pack' ); ?>:
                </div>
                <div class="small text-black-50 my-1 pr-3"><?php echo esc_html__( "Download all the Squirrly SEO Redirects.", 'squirrly-seo-pack' ); ?></div>
            </div>
            <div class="col-7 p-0 input-group">
				<?php SQP_Classes_Helpers_Tools::setNonce( 'sq_redirects_backup', 'sq_nonce' ); ?>
                <input type="hidden" name="action" value="sq_redirects_backup"/>
                <button type="submit" class="btn rounded-0 btn-primary px-2 m-0 noloading" style="min-width: 175px"><?php echo esc_html__( "Download Backup", 'squirrly-seo-pack' ); ?></button>
            </div>
        </div>
    </form>
</div>