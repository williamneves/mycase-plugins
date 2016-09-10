/**
 * Get the coupon and add it to cart
 */
function get_coupon() {

    var $social = jQuery('.ywsfd-social');

    if ($social.is('.processing')) {
        return false;
    }

    $social.addClass('processing');

    var form_data = $social.data();

    if (form_data["blockUI.isBlocked"] != 1) {
        $social.block({
            message   : null,
            overlayCSS: {
                background: '#fff',
                opacity   : 0.6
            }
        });
    }

    jQuery.ajax({
        type    : 'POST',
        url     : ywsfd.ajax_social_url,
        data    : {
            post_id: ywsfd.post_id
        },
        success : function (code) {
            var result = '';

            try {
                // Get the valid JSON only from the returned string
                if (code.indexOf('<!--WC_START-->') >= 0)
                    code = code.split('<!--WC_START-->')[1]; // Strip off before after WC_START

                if (code.indexOf('<!--WC_END-->') >= 0)
                    code = code.split('<!--WC_END-->')[0]; // Strip off anything after WC_END

                // Parse
                result = jQuery.parseJSON(code);

                if (result.status === 'success') {

                    setTimeout(function () {

                        if (result.redirect.indexOf("https://") != -1 || result.redirect.indexOf("http://") != -1) {
                            window.location = result.redirect;
                        } else {
                            window.location = decodeURI(result.redirect);
                        }

                    }, 10000);


                } else if (result.status === 'failure') {
                    throw 'Result failure';
                } else {
                    throw 'Invalid response';
                }
            }

            catch (err) {

                // Remove old errors
                jQuery('.woocommerce-error, .woocommerce-message').remove();

                // Add new errors
                if (result.messages) {
                    $social.prepend(result.messages);
                } else {
                    $social.prepend(code);
                }

                // Cancel processing
                $social.removeClass('processing').unblock();

                // Scroll to top
                jQuery('html, body').animate({
                    scrollTop: ( jQuery('.ywsfd-social').offset().top - 100 )
                }, 1000);

            }
        },
        dataType: 'html'
    });

    return false;

}

/**
 * If Facebook active
 */
if (ywsfd.facebook == 'yes') {

    window.fbAsyncInit = function () {

        FB.init({
            appId  : ywsfd.fb_app_id,
            xfbml  : true,
            version: 'v2.5'
        });

        FB.Event.subscribe("edge.create", function (href, widget) {

            get_coupon();

        });

        jQuery('body').trigger('facebook_button');
        jQuery('body').trigger('facebook_share');

    };

    function fbShare(url) {

        FB.ui({
            method : 'feed',
            link   : url,
            caption: '',
            display: 'popup'
        }, function (response) {

            if (response && response.post_id) {

                get_coupon();

            }

        });
    }

    (function (d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) {
            return;
        }
        js = d.createElement(s);
        js.id = id;
        js.src = '//connect.facebook.net/' + ywsfd.locale + '/sdk.js';
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));

}

/**
 * If Twitter active
 */
if (ywsfd.twitter == 'yes') {

    window.twttr = (function (d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0],
            t = window.twttr || {};
        if (d.getElementById(id)) return t;
        js = d.createElement(s);
        js.id = id;
        js.src = 'https://platform.twitter.com/widgets.js';
        fjs.parentNode.insertBefore(js, fjs);
        t._e = [];
        t.ready = function (f) {
            t._e.push(f);
        };
        return t;
    }(document, 'script', 'twitter-wjs'));

    twttr.ready(function (twttr) {

        twttr.events.bind('tweet', function (event) {

            get_coupon();

        });

        jQuery('body').trigger('twitter_button');

    });

}

/**
 * If Google+ active
 */
if (ywsfd.google == 'yes') {

    var share_timer = null,
        counter = 0;

    window.___gcfg = {
        lang: ywsfd.locale.substring(0, 2)
    };

    (function (d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) {
            return;
        }
        js = d.createElement(s);
        js.id = id;
        js.src = '//apis.google.com/js/plusone.js?onload=onGoogleLoad';
        js.async = true;
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'google-sdk'));

    function onGoogleLoad() {

        jQuery('body').trigger('google_button');
        jQuery('body').trigger('google_share');

    }

    /**
     * Callback for Google+ button
     * @param jsonParam
     */
    function gpCallback(jsonParam) {

        if (jsonParam.state == 'on') {

            get_coupon();

        }

    }

    /**
     * Callback for Google+ Share button
     * @param jsonParam
     */
    function gpShareCallback(jsonParam) {

        share_timer = setInterval(function () {
            counter++;
            if (counter == 4) {
                get_coupon();
                clearInterval(share_timer);
            }

        }, 1000);

        //console.log('share state ' + JSON.stringify(jsonParam));
        //if (jsonParam.type == 'confirm') { get_coupon(); }

    }

    function gpStopShareCallback(jsonParam) {

        //console.log('share state ' + JSON.stringify(jsonParam));

        if (share_timer != null) {
            counter = 0;
            clearInterval(share_timer);

        }


    }

}

