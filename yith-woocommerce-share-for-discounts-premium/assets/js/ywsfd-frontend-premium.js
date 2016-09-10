jQuery(function ($) {

    if (ywsfd.email == 'yes') {

        var ywsfd_mail_form = {

            body          : $('body'),
            email_form    : $('.ywsfd-email-form-wrapper'),
            email_submit  : $('#ywsfd_email'),
            init          : function () {

                // Form submission
                this.email_submit.on('click', this.submit);

                // Inline validation
                this.email_form.on('blur input change', '.input-text', this.validate_field);

            },
            validate_field: function (e) {
                var $this = $(this),
                    $parent = $this.closest('.form-row'),
                    validated = true;

                if ($parent.is('.ywsfd-validate-required')) {
                    if ($this.val() === '') {
                        $parent.removeClass('ywsfd-validated').addClass('ywsfd-invalid');
                        validated = false;
                    }
                }

                if ($parent.is('.ywsfd-validate-email')) {
                    if ($this.val()) {

                        /* http://stackoverflow.com/questions/2855865/jquery-validate-e-mail-address-regex */
                        var pattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);

                        if (!pattern.test($this.val())) {
                            $parent.removeClass('ywsfd-validated').addClass('ywsfd-invalid');
                            validated = false;
                        }
                    }
                }

                if (validated) {
                    $parent.removeClass('ywsfd-invalid').addClass('ywsfd-validated');
                }
            },
            submit        : function (e) {
                var $form = $('.ywsfd-email-form-wrapper'),
                    $social = $('.ywsfd-social');


                if ($social.is('.processing')) {
                    return false;
                }

                $social.addClass('processing');
                $form.addClass('opened');

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

                $.ajax({
                    type    : 'POST',
                    url     : ywsfd.ajax_email_url,
                    data    : {
                        ywsfd_wpnonce     : $('#ywsfd_wpnonce').val(),
                        ywsfd_friend_email: $('#ywsfd_friend_email').val(),
                        ywsfd_user_email  : $('#ywsfd_user_email').val(),
                        ywsfd_message     : $('#ywsfd_message').val(),
                        ywsfd_sharing_url : $('#ywsfd_sharing_url').val(),
                        ywsfd_post_id     : $('#ywsfd_post_id').val()
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
                            result = $.parseJSON(code);

                            if (result.status === 'success') {

                                if (result.redirect.indexOf("https://") != -1 || result.redirect.indexOf("http://") != -1) {
                                    window.location = result.redirect;
                                } else {
                                    window.location = decodeURI(result.redirect);
                                }

                            } else if (result.status === 'failure') {
                                throw 'Result failure';
                            } else {
                                throw 'Invalid response';
                            }
                        }

                        catch (err) {

                            // Remove old errors
                            $('.woocommerce-error, .woocommerce-message').remove();

                            // Add new errors
                            $form.addClass('opened');
                            if (result.messages) {
                                $form.prepend(result.messages);
                            } else {
                                $form.prepend(code);
                            }

                            // Cancel processing
                            $social.removeClass('processing').unblock();

                            // Lose focus for all fields
                            $form.find('.input-text, select').blur();

                            // Scroll to top
                            $('html, body').animate({
                                scrollTop: ( $('.ywsfd-email-form-wrapper').offset().top - 100 )
                            }, 1000);

                        }
                    },
                    dataType: 'html'
                });

                return false;
            }
        };

        ywsfd_mail_form.init();

        $('.ywsfd-email').click(function () {
            $('.ywsfd-email-form-wrapper').toggleClass('opened')
        });

    }

    if (ywsfd.custom_url == 'no') {

        var queryString = '';

        function twitter_button() {

            if (queryString != '') {

                var tweet = $('.ywsfd-social-button.ywsfd-twitter');

                tweet.html('<a href="https://twitter.com/share" class="twitter-share-button" data-count="none" data-url="' + ywsfd.sharing.url + '?' + queryString + '" data-text="' + ywsfd.sharing.message + '" data-via="' + ywsfd.sharing.twitter_username + '">Tweet</a>');

                twttr.widgets.load();

            }

        }

        function facebook_button() {

            if (queryString != '') {

                var like = $('.ywsfd-social-button.ywsfd-facebook');

                like.html('<div class="fb-like" data-layout="button" data-action="like" data-show-faces="false" data-share="false" data-href="' + ywsfd.sharing.url + '?' + queryString + '"></div>');

                FB.XFBML.parse();

            }

        }

        function facebook_share() {

            if (queryString != '') {

                var share = $('.ywsfd-social-button .ywsfd-facebook-share');

                share.attr('href', "javascript:fbShare('" + ywsfd.sharing.url + "?" + queryString + "')");

            }

        }

        function google_button() {

            if (queryString != '') {

                var plusone = $('.ywsfd-social-button.ywsfd-google');

                plusone.html('<div class="g-plusone" data-size="medium" data-annotation="none" data-callback="gpCallback" data-href="' + ywsfd.sharing.url + '?' + queryString + '"></div>');

                gapi.plusone.go();

            }

        }

        function google_share() {

            if (queryString != '') {

                var gshare = $('.ywsfd-social-button.ywsfd-google-share');

                gshare.html('<div class="g-plus" data-action="share" data-size="medium" data-annotation="none" data-href="' + ywsfd.sharing.url + '?' + queryString + '" data-onendinteraction="gpShareCallback"></div>');

                gapi.plus.go();

            }

        }

        $(document).ready(function () {

            $('body').bind('twitter_button', twitter_button);
            $('body').bind('facebook_button', facebook_button);
            $('body').bind('facebook_share', facebook_share);
            $('body').bind('google_button', google_button);
            $('body').bind('google_share', google_share);

        });

        $(document).on('woocommerce_variation_has_changed', function () {

            var params = [];

            $('.variations select').each(function () {

                if ($('option:selected', this).val()) {

                    params.push('attribute_' + $(this).attr('id') + '=' + $('option:selected', this).val());
                }

            });

            queryString = params.join('&');

            if (ywsfd.facebook == 'yes') {

                $('body').trigger('facebook_button');
                $('body').trigger('facebook_share');

            }

            if (ywsfd.twitter == 'yes') {

                $('body').trigger('twitter_button');

            }

            if (ywsfd.google == 'yes') {

                $('body').trigger('google_button');
                $('body').trigger('google_share');

            }

            if (ywsfd.email == 'yes') {

                if (queryString != '') {

                    var email = $('#ywsfd_sharing_url');

                    email.val(ywsfd.sharing.url + '?' + queryString);

                }

            }

        })

    }

});
