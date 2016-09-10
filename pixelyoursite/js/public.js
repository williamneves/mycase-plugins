jQuery(document).ready(function( $ ) {

    // AddToCart button
    $(".ajax_add_to_cart").click(function(e){

        var code = jQuery(this).attr('data-pixelcode') || false;

        if( code != false && code != '' ) {
            console.log( code );
            eval( code );
        }

    });

});