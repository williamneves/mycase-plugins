jQuery( function ( $ ) {
    $( '.yith-wccos-color-picker' ).wpColorPicker();

    // hide preview button and View order status button
    $( '#edit-slug-box' ).hide();
    $( '#preview-action' ).hide();

    var slug                               = function ( str ) {
            str = str.replace( /^\s+|\s+$/g, '' ); // trim
            str = str.toLowerCase();

            var cod = 0;
            for ( var i = 0, l = str.length; i < l; i++ ) {
                cod += str.charCodeAt( i );
            }
            cod = cod % 1000;

            // remove accents, swap ñ for n, etc
            var from = "ãàáäâẽèéëêìíïîõòóöôùúüûñç·/_,:;";
            var to   = "aaaaaeeeeeiiiiooooouuuunc------";
            for ( var i = 0, l = from.length; i < l; i++ ) {
                str = str.replace( new RegExp( from.charAt( i ), 'g' ), to.charAt( i ) );
            }

            str = str.replace( /[^a-z0-9 -]/g, '' ) // remove invalid chars
                .replace( /\s+/g, '' ) // collapse whitespace and replace by -
                .replace( /-+/g, '' ); // collapse dashes

            var delta = 0;

            if ( str.length >= 1 ) {
                if ( cod < 1 ) {
                    delta = 3;
                    cod   = '';
                } else if ( cod < 10 ) {
                    delta = 2;
                } else if ( cod < 100 ) {
                    delta = 1;
                }
                return str.substr( 0, (14 + delta) ) + cod;
            }
            return str;
        },
        slug_field                         = $( '#slug' ),
        title                              = $( '#title' ),
        status_type                        = $( '#status_type' ),
        create_slug                        = true,
        sendmail                           = $( '#sendmail' ),
        sendmail_total_container           = $( '#sendmail-container' ).parent(),
        mail_settings_info_total_container = $( '#mail-settings-info' ).parent(),
        tab_mail_settings                  = $( 'li.tabs' ).next(),
        custom_recipient_container         = $( '#custom_recipient-container' ).parent(),
        field_can_pay                      = $( '#can-pay' ),
        field_can_cancel                   = $( '#can-cancel' ),
        field_download_permitted           = $( '#downloads-permitted' ),
        field_display_in_reports           = $( '#display-in-reports' ),
        field_next_actions                 = $( '#nextactions' ),
        check_mail_settings_visibility     = function () {
            if ( status_type.val() != 'custom' ) {
                tab_mail_settings.hide();
                mail_settings_info_total_container.show();
                sendmail.val( '0' );
            } else {
                tab_mail_settings.show();
                mail_settings_info_total_container.hide();
            }

            if ( sendmail.val() == '4' ) {
                custom_recipient_container.show();
            } else {
                custom_recipient_container.hide();
            }
        },
        reset_fields                       = function () {
            field_can_pay.attr( 'checked', false );
            field_can_cancel.attr( 'checked', false );
            field_download_permitted.attr( 'checked', false );
            field_display_in_reports.attr( 'checked', false );
        },
        check_wc_status_selected           = function () {
            reset_fields();
            switch ( slug_field.val() ) {
                case 'pending':
                case 'failed':
                    field_can_pay.attr( 'checked', true );
                    field_can_cancel.attr( 'checked', true );
                    break;

                case 'completed':
                case 'processing':
                    field_download_permitted.attr( 'checked', true );
                    field_display_in_reports.attr( 'checked', true );
                    break;
                
                case 'on-hold':
                    field_display_in_reports.attr( 'checked', true );
                    break;

                default:

            }
        };

    slug_field.prop( 'readonly', true );

    if ( slug_field.val().length < 1 ) {
        // Fix for drafted statuses
        if ( title.val().length > 0 ) {
            slug_field.val( slug( title.val() ) );
        }
        title.on( 'keyup', function () {
            if ( create_slug ) {
                slug_field.val( slug( title.val() ) );
            }
        } );
    } else {
        create_slug = false;
        status_type.prop( 'readonly', true );
        status_type.hide();
    }

    status_type.on( 'change', function () {
        if ( $( this ).val() == 'custom' ) {
            create_slug = true;
            slug_field.val( slug( title.val() ) );
        } else {
            create_slug = false;
            slug_field.val( $( this ).val() );
        }
        check_mail_settings_visibility();
        check_wc_status_selected();
    } );

    if ( status_type.val() != 'custom' ) {
        sendmail_total_container.hide();
    } else {
        mail_settings_info_total_container.hide();
    }

    // MAIL Fields control
    check_mail_settings_visibility();

    sendmail.on( 'change', function () {
        //alert($(this).val());
        check_mail_settings_visibility();
    } );
} );