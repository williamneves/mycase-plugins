(function($){

    'use strict'

    /****
     * Send email
     */

    var send_email_btn = $('.ywrac_send_email'),
    send_email_func = function() {
        send_email_btn.on('click', function (e) {
            e.stopPropagation();
            var $t = $(this),
                $select_template = $t.prev('select'),
                ajax_loader    = ( typeof yith_ywrac_admin !== 'undefined' ) ? yith_ywrac_admin.block_loader : false,
                cart_id = $t.data('id');

            if( $select_template.length == 0){
                $select_template = $('#ywrac-email-template');
            }
            $t.after( ' <img src="'+ajax_loader+'" >' );

            $.post(yith_ywrac_admin.ajaxurl, {
                action : 'ywrac_email_send',
                cart_id: cart_id,
                email_template: $select_template.val(),
                security:  yith_ywrac_admin.send_email_nonce
            }, function (resp) {

                if( resp.email_sent !='no' ){
                    var label = yith_ywrac_admin.sent_label + ' ' + resp.email_name + ' (' + resp.email_sent + ')';
                    if ($t.parents('.yith-ywrac-info-cart').length > 0) {
                        $t.parents('.yith-ywrac-info-cart').find('.ywrac_email_status').html(label);
                    } else {
                        $('.email_status[data-id="' + cart_id + '"]').html(label);
                    }
                    $t.next().remove();
                }
            });
        });
    };

    send_email_func();


    /****
     * Send email test
     */

    var send_email_test_btn = $('.ywrac-button-sent-email'),
        send_email_test_func = function() {
            send_email_test_btn.on('click', function (e) {
                e.stopPropagation();
                var $t = $(this),
                    $select_template = $t.data('id'),
                    ajax_loader    = ( typeof yith_ywrac_admin !== 'undefined' ) ? yith_ywrac_admin.block_loader : false,
                    email_to_sent = $('#_ywrac_email_to_send').val();

                $t.after( ' <img src="'+ajax_loader+'" >' );
                $('.email-sent-label').remove();

                $.post(yith_ywrac_admin.ajaxurl, {
                    action : 'ywrac_email_test_send',
                    email_to_sent: email_to_sent,
                    email_template: $select_template,
                    security:  yith_ywrac_admin.send_email_nonce
                }, function (resp) {
                    if(resp.email_sent == 1){
                        $t.next().remove();
                        send_email_test_btn.after(' <span class="email-sent-label">'+yith_ywrac_admin.sent_label_test +'</span>');
                    }

                });
            });
        };

    send_email_test_func();



    /****
     * Add chosen in setting panel
     */
    var select          = $( document).find( '.ywrac-chosen' );

    select.each( function() {
        $(this).chosen({
            width: '350px',
            disable_search: true,
            multiple: true
        })
    });


})(jQuery)