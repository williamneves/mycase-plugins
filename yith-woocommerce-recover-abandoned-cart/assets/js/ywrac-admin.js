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
                ajax_loader    = ( typeof yith_ywrac_admin !== 'undefined' ) ? yith_ywrac_admin.block_loader : false,
                cart_id = $t.data('id');

            $t.after( ' <img src="'+ajax_loader+'" >' );

            $.post(yith_ywrac_admin.ajaxurl, {
                action : 'ywrac_email_send',
                cart_id: cart_id,
                security:  yith_ywrac_admin.send_email_nonce
            }, function (resp) {
                if( resp.email_sent !='no' ){
                    var label = yith_ywrac_admin.sent_label+' ('+ resp.email_sent +')';
                    if( $t.parents('.yith-ywrac-info-cart').length > 0 ){
                        $t.parents('.yith-ywrac-info-cart').find('.ywrac_email_status').html( label );
                    }else{
                        $('.email_status[data-id="' + cart_id + '"]').html( label );
                    }
                    $t.next().remove();
                }
            });
        });
    };

    send_email_func();

})(jQuery)