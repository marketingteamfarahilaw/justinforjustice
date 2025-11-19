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
<div class="col-12 m-0 p-0 my-5">
    <form id="sq_inport_form" name="import" action="" method="post" enctype="multipart/form-data">
		<?php SQP_Classes_Helpers_Tools::setNonce( 'sq_redirects_import', 'sq_nonce' ); ?>
        <input type="hidden" name="action" value="sq_redirects_import"/>
        <div class="col-12 row m-0 p-0 my-5">
            <div class="col-5 m-0 p-0 pr-2 font-weight-bold">
                <div class="font-weight-bold"><?php echo esc_html__( "Import Redirect From", 'squirrly-seo-pack' ); ?>:
                </div>
                <div class="small text-black-50 my-1 pr-3"><?php echo esc_html__( "Select the plugin you want to import the Redirect from.", 'squirrly-seo-pack' ); ?></div>
            </div>
            <div class="col-7 m-0 p-0 input-group">
				<?php $platforms = apply_filters( 'sq_redirects_import', array() ); ?>

				<?php
				if ( $platforms && count( (array) $platforms ) > 0 ) {
					?>
                    <select name="sq_import_platform" class="form-control bg-input mb-1 border">
						<?php
						foreach ( $platforms as $path => $platform ) {
							?>
                            <option value="<?php echo esc_attr( $path ) ?>"><?php echo esc_html( $platform['title'] ); ?></option>
						<?php } ?>
                    </select>


                    <button type="submit" class="btn rounded-0 btn-primary px-2 m-0" style="min-width: 140px"><?php echo esc_html__( "Import Redirects", 'squirrly-seo-pack' ); ?></button>
                    <div class="col-12 p-0 m-0">
                        <div class="small text-danger"><?php echo esc_html__( "Note! It will overwrite the redirects you set if there are the same paths.", 'squirrly-seo-pack' ); ?></div>
                    </div>
				<?php } else { ?>
                    <div class="col-12 my-2"><?php echo esc_html__( "We couldn't find any Plugin to import from.", 'squirrly-seo-pack' ); ?></div>
				<?php } ?>
            </div>
        </div>
    </form>
</div>