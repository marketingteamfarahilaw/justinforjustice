(function ($) {
    'use strict';

    $.fn.sq_Redirects = function () {
        var $this = this;

        $this.init = function(){

            //check if regex in the form
            $('form').each(function(){
                var form = $(this);
                form.find('input[name=url]').on('keyup', function(){
                    if($(this).val().match(/[\*\(\)\^\$]/) && -1===$(this).val().indexOf(".?")){
                        form.find('input[name=flag_regex]').prop('checked', true);
                    }
                });
            });

            //listen bulk actions
            $this.bulkAction();

        };

        $this.bulkAction = function () {

            $this.find('.sq_bulk_select_input').on('click', function () {
                if(!$(this).is(":checked")) {
                    $this.find('.sq_bulk_input').prop('checked', false);
                }else{
                    $this.find('.sq_bulk_input').prop('checked', true);
                }
            });

            //submit bulk action
            $this.find('.sq_bulk_submit').on('click', function () {
                var $button = $(this);

                if ($this.find('.sq_bulk_action').find(':selected').val() !== '') {

                    //only if confirmation needed
                    if ($this.find('.sq_bulk_action').find(':selected').data('confirm')) {
                        if (!confirm($this.find('.sq_bulk_action').find(':selected').data('confirm'))) {
                            return;
                        }
                    }

                    var $sq_bulk_input = [];
                    $($this.find('.sq_bulk_input').serializeArray()).each(function () {
                        $sq_bulk_input.push($(this).attr('value'));
                    });

                    $button.addClass('sq_minloading');
                    $.post(
                        sqQuery.ajaxurl,
                        {
                            action: $this.find('.sq_bulk_action').find(':selected').val(),
                            inputs: $sq_bulk_input,
                            sq_nonce: sqQuery.nonce
                        }
                    ).done(function (response) {
                        if (typeof response.success !== 'undefined' && typeof response.data !== 'undefined') {
                            if(response.success ){
                                $.sq_showMessage(response.data).addClass('sq_success');

                                if ($this.find('.sq_bulk_action').find(':selected').val() === 'sq_ajax_rules_bulk_delete') {
                                    $this.find('.sq_bulk_input').each(function () {
                                        if ($(this).is(":checked")) {
                                            $(this).parents('tr:last').remove();
                                        }
                                    });
                                    location.reload();
                                } else {
                                    location.reload();
                                }
                            }else{
                                $.sq_showMessage(response.data);
                            }
                        }

                        $button.removeClass('sq_minloading');
                    }).fail(function () {
                        $button.removeClass('sq_minloading');
                    }, 'json');

                }
            });
        };

        return $this;

    }


    $(document).ready(function () {

        //listen form
        $('#sq_wrap').sq_Redirects().init();

    });
})(jQuery);
