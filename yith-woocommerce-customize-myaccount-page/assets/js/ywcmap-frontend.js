jQuery(document).ready(function($) {
    "use strict";

    if( typeof $.prettyPhoto != 'undefined' ) {
        $('a[data-rel="prettyPhoto[yith-wcmap-avatar-form]"]').prettyPhoto({
            hook              : 'data-rel',
            social_tools      : false,
            theme             : 'pp_woocommerce yith-wcmap-avatar-form-popup',
            horizontal_padding: 20,
            opacity           : 0.8,
            deeplinking       : false,
            gallery_markup    : '',
            default_height    : 200
        });
    }
});