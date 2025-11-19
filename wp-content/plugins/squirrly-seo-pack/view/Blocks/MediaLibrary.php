<?php defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' ); ?>
<script type="text/template" id="tmpl-sq-image-skeleton">
    <div class="sq-image__skeleton-wrap">
        <div class="sq-image__skeleton-inner-wrap">
            <div class="sq-image__skeleton">
            </div>
            <div class="sq-image__preview-skeleton">
            </div>
        </div>
    </div>
    <div class="sq-image__loader-wrap">
        <div class="sq-image__loader-1"></div>
        <div class="sq-image__loader-2"></div>
        <div class="sq-image__loader-3"></div>
    </div>
</script>

<script type="text/template" id="tmpl-sq-image-list">

    <# var count = 0; #>
    <# for ( key in data ) { count++; #>
    <# var is_imported = _.includes( sqMediaLibrary.saved_images, data[key]['id'] ); #>
    <# var imported_class = ( is_imported ) ? 'imported' : ''; #>
    <div class="sq-image__list-wrap loading" data-id="{{data[key]['id']}}" data-url="{{data[key]['pageURL']}}">
        <div class="sq-image__list-inner-wrap {{imported_class}}">
            <div class="sq-image__list-img-wrap">
                <img src="{{data[key]['webformatURL']}}" alt="{{data[key]['tags']}}"/>
                <div class="sq-image__list-img-overlay" data-img-url={{data[key]['largeImageURL']}} data-img-id={{data[key]['id']}}>
                    <span>{{data[key]['tags']}}</span>
                    <# if ( '' === imported_class ) { #>
                    <span class="sq-image__download-icon dashicons-arrow-down-alt dashicons" data-import-status={{is_imported}}></span>
                    <# } #>
                </div>
            </div>
        </div>
    </div>
    <# } #>
    <# if ( 0 === count ) { #>
    <div class="sq-sites-no-sites">
        <h3><?php esc_html_e( 'Sorry No Results Found.', 'squirrly-seo-pack' ); ?></h3>
    </div>
    <# } #>
</script>

<script type="text/template" id="tmpl-sq-image-filters">
    <div class="sq-image__filter-wrap">
        <ul class="sq-image__filter">
            <li class="sq-image__filter-category">
                <select>
                    <# for ( key in sqMediaLibrary.pixabay_category ) { #>
                    <option value="{{key}}">{{sqMediaLibrary.pixabay_category[key]}}</option>
                    <# } #>
                </select>
            </li>
            <li class="sq-image__filter-orientation">
                <select>
                    <# for ( key in sqMediaLibrary.pixabay_orientation ) { #>
                    <option value="{{key}}">{{sqMediaLibrary.pixabay_orientation[key]}}</option>
                    <# } #>
                </select>
            </li>
            <li class="sq-image__filter-order">
                <select>
                    <# for ( key in sqMediaLibrary.pixabay_order ) { #>
                    <option value="{{key}}">{{sqMediaLibrary.pixabay_order[key]}}</option>
                    <# } #>
                </select>
            </li>
            <li class="sq-image__filter-safesearch">
                <label><input type="checkbox" checked value="1"/><?php esc_html_e( 'SafeSearch', 'squirrly-seo-pack' ); ?>
                </label>
            </li>
        </ul>
    </div>
    <div class="sq-powered-by-pixabay-wrap">
        <span><?php esc_html_e( 'Powered by', 'squirrly-seo-pack' ); ?></span><img src="<?php echo esc_url( _SQP_ASSETS_URL_ . 'img/pixabay-logo.png' ); ?>" alt="">
    </div>
</script>

<script type="text/template" id="tmpl-sq-image-no-result">
    <div class="sq-sites-no-sites">
        <h3><?php esc_html_e( 'Sorry No Results Found.', 'squirrly-seo-pack' ); ?></h3>
    </div>
</script>

<script type="text/template" id="tmpl-sq-image-single">
    <# var is_imported = _.includes( sqMediaLibrary.saved_images, data.id.toString() ); #>
    <# var disable_class = ( is_imported ) ? 'disabled': ''; #>
    <# var image_type = data.largeImageURL.substring( data.largeImageURL.lastIndexOf( "." ) + 1 ); #>
    <div class="single-site-wrap">
        <div class="single-site">
            <div class="single-site-preview-wrap">
                <div class="single-site-preview">
                    <img class="theme-screenshot" src="{{data.largeImageURL}}" alt="">
                </div>
            </div>
        </div>
    </div>
</script>

<script type="text/template" id="tmpl-sq-image-go-back">
    <div class="sq-image__go-back">
        <i class="sq-icon-chevron-left"></i>
        <span class="sq-image__go-back-text"><?php esc_html_e( 'Back to Images', 'squirrly-seo-pack' ); ?></span>
    </div>
</script>

<script type="text/template" id="tmpl-sq-image-save">
    <# var is_imported = _.includes( sqMediaLibrary.saved_images, data.id.toString() ); #>
    <# var disable_class = ( is_imported ) ? 'disabled': ''; #>
    <div class="sq-image__save-wrap">
        <button type="button" class="sq-image__save button media-button button-primary button-large media-button-select {{disable_class}}" data-import-status={{is_imported}}>
            <# if ( is_imported ) { #>
			<?php esc_html_e( 'Already Saved', 'squirrly-seo-pack' ); ?>
            <# } else { #>
			<?php esc_html_e( 'Save & Insert', 'squirrly-seo-pack' ); ?>
            <# } #>
        </button>
    </div>
</script>
