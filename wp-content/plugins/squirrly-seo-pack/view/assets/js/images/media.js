(function($){

    $scope = {};

    SQImageCommon = {

        images: {},
        image: {},
        action: '',
        offset: 0,
        loadingStatus: true,
        config: {
            q              : '',
            lang           : 'en',
            image_type     : 'all',
            orientation    : 'all',
            category       : '',
            min_width      : 0,
            min_height     : 0,
            colors         : '',
            editors_choice : false,
            safesearch     : true,
            order          : 'popular',
            page           : $( 'body' ).data( 'page' ),
            per_page       : 30,
            callback       : '',
            pretty         : true
        },
        canSave: false,
        infiniteLoad: false,
        uploader: {},
        file: {},
        frame: {},
        isPreview: false,
        apiStatus: true,
        id : '',
        isValidating: false,
        scopeSet: false,

        init: function() {
            this._bind();
        },

        /**
         * Binds events for the SQ Sites.
         *
         * @since 1.0.0
         * @access private
         * @method _bind
         */
        _bind: function() {

            // Triggers.
            $( document ).on( "sq-image__refresh", SQImageCommon._initImages );
            $( document ).on( "sq-image__set-scope", SQImageCommon._setScope );
            $( document ).on( "click", ".sq-image__list-img-overlay", SQImageCommon._preview );
            $( document ).on( "click", ".sq-image__go-back-text", SQImageCommon._goBack );
            $( document ).on( "click", ".sq-image__save", SQImageCommon._save );
            $( document ).on( "click", ".sq-image__filter-safesearch input", SQImageCommon._filter );
            $( document ).on( "change", ".sq-image__filter select", SQImageCommon._filter );
            $( document ).on( "click", ".sq-image__edit-api", SQImageCommon._editAPI );
            $( document ).on( "click", ".sq-image__browse-images", SQImageCommon._browse );
            $( document ).on( "click", ".sq-image__download-icon", SQImageCommon._saveFromScreen );
        },

        _saveFromScreen: function() {

            let saveIcon = $(this);
            let source = saveIcon.closest('.sq-image__list-img-overlay');

            saveIcon.addClass( 'installing' );

            SQImageCommon.image = {
                'largeImageURL': source.data( 'img-url' ),
                'tags' : source.find( 'span:first-child' ).html(),
                'id' : source.data( 'img-id' ),
            };

            SQImageCommon._saveAjax( function ( data ) {
                if ( undefined == data.data ) {
                    return;
                }
                sqMediaLibrary.saved_images = data.data['updated-saved-images'];
                wp.media.view.SQAttachmentsBrowser.object.photoUploadComplete( data.data );
                saveIcon.text( 'Done' );
                saveIcon.removeClass( 'installing' );
                SQImageCommon._empty();
            } );
        },

        _browse: function() {
            $scope.find( '.sq-image__search' ).trigger( 'keyup' );
        },

        _editAPI: function( event ) {
            event.stopPropagation();
            wp.media.view.SQAttachmentsBrowser.images = [];
            $scope.find( '.sq-image__loader-wrap' ).show();
            $scope.find( '.sq-image__skeleton' ).html( '' );
            $scope.find( '.sq-image__skeleton' ).attr( 'style', '' );
            $scope.find( '.sq-image__search' ).trigger( 'keyup' );
            $scope.find( '.sq-image__loader-wrap' ).hide();
        },

        _filter: function() {
            let safesearch = $scope.find( '.sq-image__filter-safesearch input:checked' ).length ? true : false;
            let category = $scope.find( '.sq-image__filter-category select' ).val();
            let orientation = $scope.find( '.sq-image__filter-orientation select' ).val();
            let order = $scope.find( '.sq-image__filter-order select' ).val();

            SQImageCommon.config.safesearch = safesearch;
            SQImageCommon.config.orientation = orientation;
            SQImageCommon.config.category = category;
            SQImageCommon.config.order = order;

            $scope.find( '.sq-image__search' ).trigger( 'keyup' );
            $scope.find( '.sq-image__loader-wrap' ).show();
        },

        _save: function() {

            if ( ! SQImageCommon.canSave ) {
                return;
            }

            let thisBtn = $( this )

            if ( thisBtn.data( 'import-status' ) ) {
                return;
            }
            thisBtn.removeClass( 'updating-message' );

            thisBtn.text( sqMediaLibrary.downloading );
            thisBtn.addClass( 'installing' );

            SQImageCommon.canSave = false;

            SQImageCommon._saveAjax( function ( data ) {
                if ( undefined == data.data ) {
                    return;
                }
                sqMediaLibrary.saved_images = data.data['updated-saved-images'];
                wp.media.view.SQAttachmentsBrowser.object.photoUploadComplete( data.data );
                thisBtn.text( 'Done' );
                thisBtn.removeClass( 'installing' );
                SQImageCommon._empty();
            } );

        },

        _saveAjax: function( callback ) {

            // Work with JSON page here
            $.ajax({
                url: sqQuery.ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: {
                    'action' : 'sqp_medialibrary_create_image',
                    'url' : SQImageCommon.image.largeImageURL,
                    'name' : SQImageCommon.image.tags,
                    'id' : SQImageCommon.image.id,
                    'sq_nonce' : sqQuery.nonce,
                },
            })
                .fail(function( jqXHR ){
                    console.log( jqXHR );
                })
                .done( callback );
        },

        _empty: function() {

            SQImageCommon.image = {};
            SQImageCommon.canSave = false;
            SQImageCommon.uploader = {};
            SQImageCommon.file = {};
            SQImageCommon.isPreview = false;
        },

        _goBack: function() {

            SQImageCommon._empty();

            $( document ).trigger( 'sq-image__refresh' );

            $scope.find( '.sq-image__skeleton' ).show();
            $scope.removeClass( 'preview-mode' );
            $scope.find( '.sq-attachments-search-wrap' ).children().show();
            $scope.find( '.sq-image__go-back' ).remove();
            $scope.find( '.sq-image__save-wrap' ).remove();
            $scope.find( '.sq-image__preview-skeleton' ).hide();
            $scope.find( '.sq-image__preview-skeleton' ).html( '' );

            let wrapHeight = ( SQImageCommon.offset - 210 );
            $scope.find( '.sq-image__skeleton-inner-wrap' ).css( 'height', wrapHeight );
        },

        _preview: function(event) {

            if( event && event.target.classList.contains( 'sq-image__download-icon' ) ) {
                return;
            }

            SQImageCommon.isPreview = true;

            let height = ( SQImageCommon.offset - 190 );
            $scope.find( '.sq-image__skeleton-inner-wrap' ).css( 'height', height );

            setTimeout( function() {
                $scope.find( '.sq-image__loader-wrap' ).hide();
            }, 200 );

            SQImageCommon.image = {
                'largeImageURL': $( this ).data( 'img-url' ),
                'tags' : $( this ).find( 'span:first-child' ).html(),
                'id' : $( this ).data( 'img-id' ),
            };

            let preview = wp.template( 'sq-image-single' );
            let single_html = preview( SQImageCommon.image );

            let save_btn = wp.template( 'sq-image-save' );
            let single_btn = save_btn( SQImageCommon.image );

            let wrapHeight = $scope.find( '.sq-image__skeleton-inner-wrap' ).outerHeight();
            wrapHeight = ( wrapHeight - 60 );

            $scope.find( '.sq-image__skeleton' ).hide();
            $scope.addClass( 'preview-mode' );
            $scope.find( '.sq-attachments-search-wrap' ).children().hide();
            $scope.find( '.sq-image__search-wrap' ).before( $( '#tmpl-sq-image-go-back' ).text() );
            $scope.find( '.sq-image__search-wrap' ).after( single_btn );
            $scope.find( '.sq-image__preview-skeleton' ).html( single_html );
            $scope.find( '.sq-image__preview-skeleton' ).show();
            $scope.find( '.single-site-preview' ).css( 'max-height', wrapHeight );

            SQImageCommon.canSave = true;
        },

        _setScope: function() {

            SQImageCommon.frame = wp.media.view.SQAttachmentsBrowser.object.$el.closest( '.media-frame' );
            $scope = SQImageCommon.frame.find( '.sq-attachments-browser' );

            if ( undefined == $scope ) {
                return;
            }

            $( 'body' ).data( 'page', 1 );
            let skeleton = $( '#tmpl-sq-image-skeleton' ).text();
            $scope.append( skeleton );

            let pixabay_filter = wp.template( 'sq-image-filters' );
            if ( ! $scope.find( '.sq-image__filter-wrap' ).length ) {
                $scope.find( '.sq-attachments-search-wrap' ).append( pixabay_filter() );
            }

            SQImageCommon.offset = SQImageCommon.frame.outerHeight();
            let wrapHeight = ( SQImageCommon.offset - 210 );
            $scope.find( '.sq-image__skeleton-inner-wrap' ).css( 'height', wrapHeight );
            $scope.find( '.sq-image__search' ).trigger( 'keyup' );
            $scope.find( '.sq-image__loader-wrap' ).show();
            $scope.find( '.sq-image__skeleton-inner-wrap' ).scroll( SQImageCommon._loadMore );

            SQImageCommon.scopeSet = true;
        },

        _initImages: function() {

            let loop = wp.template( 'sq-image-list' );
            let list_html = loop( wp.media.view.SQAttachmentsBrowser.images );
            let container = document.querySelector( '.sq-image__skeleton' );
            $scope.find( '.sq-image__loader-wrap' ).show();

            if ( SQImageCommon.infiniteLoad ) {
                SQImageCommon.images.push( wp.media.view.SQAttachmentsBrowser.images );
                $scope.find( '.sq-image__skeleton' ).append( list_html );
            } else {
                SQImageCommon.images = wp.media.view.SQAttachmentsBrowser.images;
                $scope.find( '.sq-image__skeleton' ).html( list_html );
            }
            SQImageCommon.loadingStatus = true;
            if ( $scope.find( '.sq-image__list-wrap' ).length ) {
                imagesLoaded( container, function() {
                    $scope.find( '.sq-image__list-wrap' ).each( function( index ) {
                        $( this ).removeClass( 'loading' );
                        $( this ).addClass( 'loaded' );
                    } );
                    $scope.find( '.sq-image__loader-wrap' ).hide();
                });
            } else {
                $scope.find( '.sq-image__loader-wrap' ).hide();
            }
        },

        _loadMore: function() {

            if( SQImageCommon.isPreview ) {
                return;
            }

            let page = $( 'body' ).data( 'page' );
            page = ( undefined == page ) ? 2 : ( page + 1 );

            if ( undefined != $scope.find( '.sq-image__list-wrap:last' ).offset() ) {

                if( ( $( window ).scrollTop() + SQImageCommon.offset ) >= ( $scope.find( '.sq-image__list-wrap:last' ).offset().top ) ) {

                    if ( SQImageCommon.loadingStatus ) {

                        $scope.find( '.sq-image__loader-wrap' ).show();

                        SQImageCommon.loadingStatus = false;
                        SQImageCommon.infiniteLoad = true;
                        SQImageCommon.config.page = page;

                        $( 'body' ).data( 'page', page );

                        $scope.find( '.sq-image__search' ).trigger( 'infinite' );
                    }
                }
            }
        },
    };

    /**
     * Initialize SQImageCommon
     */
    $( function(){

        SQImageCommon.init();

    });

})(jQuery);