(function ($) {
    'use strict';

    const { __, _x, _n, _nx } = wp.i18n;

    $.fn.sq_listenSubmit = function (){
        var $form = this;

        $form.on('submit', function (){
            var $button = $form.find('button[type=submit]');
            $button.addClass('sq_minloading');

            $.post(
                sqQuery.ajaxurl,
                $form.serialize()
            ).done(function (response) {
                if(typeof response.success !== 'undefined' && typeof response.data !== 'undefined'){
                    if (response.success) {
                        $.sq_showMessage(response.data).addClass('sq_success');

                        $form.trigger('sqd_jsonld_form_submit');

                    } else {
                        $.sq_showMessage(response.data).addClass('sq_error');
                    }
                }

            }).fail(function () {
                $button.removeClass('sq_minloading');
                $.sq_showMessage(__('An error occurred, please refresh the page.','hide-my-wp')).addClass('sq_error');
            });

            return false;
        });
    }

    /**
     * Get the Snippet For a Post Type
     *
     * @param jsonld_type
     * @param jsonld_id
     */
    $.fn.sq_getJsonType = function (jsonld_type, jsonld_id) {
        var $this = this;

        $this.addClass('sq_minloading');

        return $.post(
            sqQuery.ajaxurl,
            {
                action: 'sqp_jsonld_reusable_edit',
                jsonld_type: jsonld_type,
                jsonld_id: jsonld_id,
                sq_nonce: sqQuery.nonce
            }
        ).done(function (response) {
            $this.removeClass('sq_minloading');
        }).fail(function () {
            $this.removeClass('sq_minloading');
            $.sq_showMessage(__('An error occurred, please refresh the page.','hide-my-wp')).addClass('sq_error');
        });
    };

    $.fn.sqp_ReusableSchemas = function () {
        var $this = this; //snippet wrap

        $this.init = function(){

            //listen adding new schema
            $this.snippetListenTypes();

            //listen schema type edit
            $this.snippetListenTypeEdit();

            //Add patterns id needed
            $('body').on('sqd_jsonld_modal_show', function (e, $modal) {
                //Add the pattens on the right side of the input/textarea
                if ($.isFunction($.fn.sq_patterns)) {
                    //call the patterns after save
                    $modal.find('.sq_pattern_field').each(function () {
                        $(this).sq_patterns().init();
                    });
                }
            });
        }

        /**
         * Listen all jsonld types
         */
        $this.snippetListenTypes = function(){
            $this.find('.sq_jsonld_show_types').on('click', function () {
                var $button = $(this);

                $button.addClass('sq_minloading');

                $.post(
                    sqQuery.ajaxurl,
                    {
                        action: 'sqp_jsonld_get_jsonld_types',
                        sq_nonce: sqQuery.nonce
                    }
                ).done(function (response) {
                    if (typeof response.success !== 'undefined' && typeof response.data !== 'undefined') {
                        if (response.success) {
                            var $modal = $(response.data);
                            $modal.sq_Modal('show');

                            $modal.find('.sq_jsonld_type_add').on('click', function (){

                                var $jsonld_type = $(this).data('jsonld-type');

                                //Load the modal schema editor
                                $(this).sq_getJsonType(
                                    $jsonld_type
                                ).done(function (response) {
                                    if (typeof response.success !== 'undefined' && typeof response.data !== 'undefined') {
                                        if (response.success) {

                                            //hide previous modal
                                            $modal.sq_Modal('hide');

                                            //create schema editor modal
                                            $modal = $(response.data);
                                            $modal.sq_Modal('show');

                                            //listen modal form actions
                                            $modal.find('form').sq_listenForm();

                                            //close modal and load preview
                                            $modal.find('form').on('sqd_jsonld_form_submit', function(){
                                                location.reload();
                                            });

                                        }else{
                                            $.sq_showMessage(response.data).addClass('sq_error');
                                        }
                                    }
                                });

                            });
                        } else {
                            $.sq_showMessage(response.data).addClass('sq_error');
                        }
                    }

                    $button.removeClass('sq_minloading');
                }).fail(function () {
                    $button.removeClass('sq_minloading');
                    $.sq_showMessage(__('An error occurred, please refresh the page.','hide-my-wp')).addClass('sq_error');
                });
            });
        }

        /**
         * Listen all jsonld edit buttons
         */
        $this.snippetListenTypeEdit = function(){

            $this.find('.sq_jsonld_edit_type').on('click', function () {
                var $button =  $(this);

                $button.sq_getJsonType(
                    $button.data('jsonld-type'),
                    $button.data('jsonld-id')
                ).done(function (response) {
                    if (typeof response.success !== 'undefined' && typeof response.data !== 'undefined') {
                        if (response.success) {

                            var $modal = $(response.data);
                            $modal.sq_Modal('show');

                            //listen modal form actions
                            $modal.find('form').sq_listenForm();

                            //close modal and load preview
                            $modal.find('form').on('sqd_jsonld_form_submit', function(){
                                //load snippet preview
                                $modal.sq_Modal('hide');
                                location.reload();
                            });


                        }else{
                            $.sq_showMessage(response.data).addClass('sq_error');
                        }
                    }
                });
            });

        }

        return $this;

    }


    $(document).ready(function () {
        if($(sqp_jsonld.wrap).length > 0){
            $(sqp_jsonld.wrap).each(function () {
                //init & listen each snippet
                $(this).sqp_ReusableSchemas().init();
            });
        }
    });
})(jQuery);


