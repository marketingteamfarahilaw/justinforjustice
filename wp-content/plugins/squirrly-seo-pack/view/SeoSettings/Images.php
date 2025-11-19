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
<div class="col-12 m-0 p-0">
    <div class="col-12 row m-0 p-0">
        <div class="checker col-12 row my-4 p-0 mx-0">
            <div class="col-12 p-0 m-0 sq-switch sq-switch-sm">
                <input type="hidden" name="sq_media_library_images" value="0"/>
                <input type="checkbox" id="sq_media_library_images" name="sq_media_library_images" class="sq-switch" <?php echo( SQ_Classes_Helpers_Tools::getOption( 'sq_media_library_images' ) ? 'checked="checked"' : '' ) ?> value="1"/>
                <label for="sq_media_library_images" class="ml-2"><?php echo esc_html__( "Squirrly Free Images in Media Library", 'squirrly-seo-pack' ); ?></label>
                <div class="small text-black-50 ml-5"><?php echo esc_html__( "Load Free Images tab in Media Library from Pixabay.", 'squirrly-seo-pack' ); ?></div>
            </div>
        </div>
    </div>
</div>