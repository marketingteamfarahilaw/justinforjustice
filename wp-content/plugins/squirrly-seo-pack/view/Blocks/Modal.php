<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );
if ( ! isset( $view ) ) {
	return;
}

/**
 * Modal Block view
 *
 */
?>
<?php // Template for the squirrly frame: used both in the squirrly grid and in the squirrly modal. ?>
    <script type="text/html" id="tmpl-squirrly-frame">
        <div class="squirrly-frame-title" id="squirrly-frame-title"></div>
        <h2 class="squirrly-frame-menu-heading"><?php _ex( 'Actions', 'squirrly modal menu actions' ); ?></h2>
        <button type="button" class="button button-link squirrly-frame-menu-toggle" aria-expanded="false">
			<?php _ex( 'Menu', 'squirrly modal menu' ); ?>
            <span class="dashicons dashicons-arrow-down" aria-hidden="true"></span>
        </button>
        <div class="squirrly-frame-menu"></div>
        <div class="squirrly-frame-tab-panel">
            <div class="squirrly-frame-router"></div>
            <div class="squirrly-frame-content"></div>
        </div>
        <h2 class="squirrly-frame-actions-heading screen-reader-text">
			<?php
			/* translators: Accessibility text. */
			_e( 'Selected squirrly actions' );
			?>
        </h2>
        <div class="squirrly-frame-toolbar"></div>
        <div class="squirrly-frame-uploader"></div>
    </script>

<?php // Template for the squirrly modal. ?>
    <script type="text/html" id="tmpl-squirrly-modal">
        <div tabindex="0" class="squirrly-modal wp-core-ui" role="dialog" aria-labelledby="squirrly-frame-title">
            <# if ( data.hasCloseButton ) { #>
            <button type="button" class="squirrly-modal-close">
                <span class="squirrly-modal-icon"><span class="screen-reader-text"><?php _e( 'Close dialog' ); ?></span></span>
            </button>
            <# } #>
            <div class="squirrly-modal-content" role="document"></div>
        </div>
        <div class="squirrly-modal-backdrop"></div>
    </script>

<?php

/**
 * Fires when the custom Backbone modal templates are printed.
 *
 * @since 3.5.0
 */
do_action( 'print_squirrly_modal_templates' );