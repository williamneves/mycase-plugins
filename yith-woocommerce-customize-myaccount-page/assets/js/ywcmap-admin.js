jQuery(document).ready(function($) {
    "use strict";

    var endpoints_list = $( ".yith_wcmap_endpoint_container .endpoints"),
        update_list_endpoint = function(){

            var fields = new Array();

            $('input[type="hidden"].yith-wcmap-order-endpoints', endpoints_list).each(function (i) {
                if( $(this).val() )
                    fields[i] = $(this).val();
            });

            $( 'input.endpoints-order' ).val( fields.join(',') );
        };

    /*################################
         SORT ENDPOINTS
     #################################*/

    endpoints_list.sortable({
        cursor: "move",
        scrollSensitivity: 10,
        items: "> .endpoint:not('adding')",
        tolerance: "pointer",
        axis: "y",
        stop: function(event, ui) {

            update_list_endpoint();
        }
    });

    /*################################
        OPEN ENDPOINT OPTIONS
    #################################*/

    $(document).on('click', '.open-options', function() {

        $(this).toggleClass( 'fa-chevron-down' ).toggleClass( 'fa-chevron-up' );
        $(this).closest('.endpoint').find('.options').slideToggle();
    });

    /*##############################
        ADD ENDPOINTS
    ###############################*/

    var ind = 0;

    $(document).on('click', '.add-endpoint', function(ev){

        ev.stopPropagation();

        var new_endpoint = $(this).find('.new-endpoint-form').clone( false ),
            new_id = 'yith-wcmap-new-endpoint-' + ind++,
            label = new_endpoint.find( 'label[for="yith-wcmap-new-endpoint"]').attr( 'for', new_id),
            name = new_endpoint.find( '#yith-wcmap-new-endpoint').attr( 'id', new_id );

        // move elem
        $(this).before( new_endpoint );
        // wrap and show
        endpoints_list.find('> .new-endpoint-form').wrap( '<li class="endpoint adding"></li>').show();

        // add listener
        name.on( 'keyup', check_endpoint_key );

    });

    var xhr = false,
        check_endpoint_key = function( ev ){

        var input           = $(this),
            value           = input.val(),
            li_adding       = input.closest( '.adding' ),
            error           = li_adding.find( '.error-msg'),
            current_list    = $( 'input.endpoints-order').val().split(','),
            hidden_order    = li_adding.find( '.yith-wcmap-order-endpoints' ),
            add_button      = li_adding.find( '.add-endpoint-button' );

        // abort prev ajax request
        if( xhr ) {
            xhr.abort();
        }
        li_adding.find('.checking' ).html('');
        add_button.attr( 'disabled', 'disabled' );

        if( value.length < 3 ){
            return false;
        }

        // first check js
        if( $.inArray( value, current_list ) !== -1 ) {
            li_adding.find('.checking').html( ywcmap.error_icon );
            error.html( ywcmap.error_msg );
            return false;
        }

        // else check ajax
        xhr = $.ajax({
            url: ywcmap.ajaxurl,
            data: {
                endpoint_name: value,
                action: ywcmap.action_check
            },
            dataType: 'json',
            beforeSend: function(){
                hidden_order.val( '' );
                li_adding.find('.checking' ).html( ywcmap.loading );
            },
            success: function(res){

                if( res.error ) {
                    li_adding.find('.checking').html( ywcmap.error_icon );
                    error.html( ywcmap.error_msg );
                    return false;
                }
                else {
                    // remove err
                    error.html( '' );
                    li_adding.find('.checking').html( ywcmap.checked );
                    add_button.removeAttr( 'disabled' );
                    hidden_order.val( res.endpoint );
                }
            }
        });
    };

    $(document).on( 'click', '.add-endpoint-button', function(ev){
        // update list
        update_list_endpoint();
    });


    /*##############################
        HIDE / SHOW ENDPOINT
     ##############################*/

    var check = $(document).find('.hide-show-check');

    check.on('change', function(){
        var link = $(this).closest('.endpoint').find('.hide-show-link');

        if( $(this).is(':checked') ) {
            link.html( ywcmap.hide_lbl );
        }
        else {
            link.html( ywcmap.show_lbl );
        }
    });

    $(document).on( 'click', '.hide-show-link', function(ev){
        ev.preventDefault();

        var check = $(this).closest('.endpoint').find('.hide-show-check');

        if( check.is(':checked') ) {
            check.prop( 'checked', false );
            $(this).html( ywcmap.show_lbl );
        }
        else {
            check.prop( 'checked', true );
            $(this).html( ywcmap.hide_lbl );
        }
    });

    /*##############################
        REMOVE ENDPOINT
     ##############################*/

    $(document).on('click', '.remove-link', function(ev){
        ev.preventDefault();

        var t = $(this),
            endpoint = t.data('endpoint');

        if( typeof endpoint == 'undefined' || ! endpoint ) {
            return false;
        }

        var r = confirm( ywcmap.remove_alert );
        if ( r == true ) {
            // remove action
            $.ajax({
                url: ywcmap.ajaxurl,
                data: {
                    endpoint: endpoint,
                    action: ywcmap.action_remove
                },
                dataType: 'json',
                success: function( res ){

                    if( res.success ){
                        t.closest('.endpoint').remove();
                        // update list
                        update_list_endpoint();
                    }
                }
            });
        } else {
            return false;
        }


    });
});