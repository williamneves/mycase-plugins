/**
 * Created by kinhdon on 10/13/2015.
 */


(function($){
    "use strict";
    var text_add_button;
    var text_cancel_button;

    function nth_resUnload(){
        return "Dummy database is importing..."
    }

    $(document).ready(function(){

        $('#videos_product_data #button_add_video').on('click', function(event){
            event.preventDefault();
            var v_source = $('#videos_product_data [name=nth_vsource_type]:checked').val();
            var thmb_url = $('#videos_product_data .nth_thumbnail img').attr('src');
            var thmb_id = $('#videos_product_data .nth_upload_image .nth_image_id').val();
            var v_size_w = $('#videos_product_data [name=nth_video_size_w]').val();
            var v_size_h = $('#videos_product_data [name=nth_video_size_h]').val();

            if( thmb_id.length == 0 || v_size_w == 0 || v_size_h == 0 ) return false;

            var i = $("#videos_product_data #table_videos_html tbody tr:last-child").data('index')+1 || 0;
            var hidden_val = "<input type=\"hidden\" name=\"nth_woovideos_thumb["+i+"]\" value='"+thmb_id+"' />";
            hidden_val += "<input type=\"hidden\" name=\"nth_woovideos_size["+i+"][width]\" value='"+v_size_w+"' />";
            hidden_val += "<input type=\"hidden\" name=\"nth_woovideos_size["+i+"][height]\" value='"+v_size_h+"' />";

            if( v_source == "embedded" ) {
                var emb_code = $('#videos_product_data [name=nth_embcode]').val();
                if( emb_code.length > 0 ) {
                    hidden_val += "<input type=\"hidden\" name=\"nth_woovideos["+i+"]\" value='"+emb_code+"' />";
                } else return false;
            } else {
                var mp4 = $('#videos_product_data [name=nth_media_video_mp4]').val();
                var webm = $('#videos_product_data [name=nth_media_video_webm]').val();
                var ogv = $('#videos_product_data [name=nth_media_video_ogv]').val();
                var poster = $('#videos_product_data [name=nth_media_video_poster]').val();
                var autoplay = $('#videos_product_data [name=nth_media_video_autoplay]').val();
                var loop = $('#videos_product_data [name=nth_media_video_loop]').val();

                if( mp4.length > 0 || webm.length > 0 || ogv.length > 0 ) {
                    var emb_code = '<ul>';
                    if(mp4.length > 0) {
                        emb_code += '<li>'+mp4+'</li>';
                        hidden_val += "<input type=\"hidden\" name=\"nth_woovideos["+i+"][mp4]\" value='"+mp4+"' />";
                    }
                    if(webm.length > 0) {
                        emb_code += '<li>'+webm+'</li>';
                        hidden_val += "<input type=\"hidden\" name=\"nth_woovideos["+i+"][webm]\" value='"+webm+"' />";
                    }
                    if(ogv.length > 0) {
                        emb_code += '<li>'+ogv+'</li>';
                        hidden_val += "<input type=\"hidden\" name=\"nth_woovideos["+i+"][ogv]\" value='"+ogv+"' />";
                    }
                    if(poster.length > 0)
                        hidden_val += "<input type=\"hidden\" name=\"nth_woovideos["+i+"][poster]\" value='"+poster+"' />";
                    if(autoplay.length > 0)
                        hidden_val += "<input type=\"hidden\" name=\"nth_woovideos["+i+"][autoplay]\" value='"+autoplay+"' />";
                    if(loop.length > 0)
                        hidden_val += "<input type=\"hidden\" name=\"nth_woovideos["+i+"][loop]\" value='"+loop+"' />";
                    emb_code += '</ul>';

                   // $('#table_videos_html tr td.code_review').html(emb_code);
                } else return false;
            }

            var tr_video = '<tr data-index="'+i+'" id="nth_woovideo_item_'+i+'">';
            tr_video += '<td><img src="'+thmb_url+'" width="50" /></td>';
            tr_video += "<td class=\"code_review\"></td>";
            tr_video += '<td><a href="#" class="dashicons-before dashicons-trash remove"></a></td>';
            tr_video += '</tr>';
            $('#table_videos_html').append(tr_video);

            if($('#table_videos_html tr#no_item').length) $('#table_videos_html tr#no_item').hide();

            if( v_source == "embedded" ) $('#table_videos_html tr td.code_review').text(emb_code);
            else $('#table_videos_html tr td.code_review').html(emb_code);


            $('#table_videos_html tr td.code_review').append(hidden_val);

            $('#videos_product_data .nth_upload_image .nth_remove_image_button').trigger('click');
            $('#videos_product_data [name=nth_embcode]').val('');

            $('#videos_product_data .nth-media-video-upload .nth_media_remove_button').trigger('click');

            return false;

        })

        $('#videos_product_data').on('click', 'table#table_videos_html .remove', function(event){
            event.preventDefault();
            var conf = confirm("Do you want to remove this video?");
            if(!conf) return false;
            var $tbody = $(this).parents('tbody');
            if( $tbody.find('tr').length <= 2 ) {
                $tbody.find('tr#no_item').removeClass('hidden');
            }
            $(this).parents('tr').remove();
        });

        var $tab_panel = $('#custom_tab_product_data');
        $tab_panel.on('click', '#button_add_customtab', function (event) {
            event.preventDefault();
            var i = $tab_panel.find('#table_customtabs_html tbody tr:last-child').data('index')+1 || 0;

            var tab_title = $tab_panel.find('#nth_prod_tab_title_adding').val();
            var tab_content = $tab_panel.find('#nth_prod_tab_content_adding').val();

            if( tab_title.length > 0 && tab_content.length > 0 ) {
                var show_xs = $tab_panel.find('#nth_prod_tab_hidden_xs:checked').val() || '';
                var show_sm = $tab_panel.find('#nth_prod_tab_hidden_sm:checked').val() || '';
                var show_lg = $tab_panel.find('#nth_prod_tab_hidden_lg:checked').val() || '';
                var tab_hidden = show_xs + ' ' + show_sm + ' ' + show_lg;

                var hidden_val = '<input type="hidden" name="nth_prod_tab_title['+i+']" value="'+tab_title+'" />';
                hidden_val += '<input type="hidden" name="nth_prod_tab_content['+i+']" value="'+tab_content+'" />';
                hidden_val += '<input type="hidden" name="nth_prod_tab_hidden['+i+']" value="'+tab_hidden+'" />';
                var tr_video = '<tr data-index="'+i+'">';
                tr_video += '<td>'+tab_title+'</td>';
                tr_video += '<td>'+tab_content+hidden_val+'</td>';
                tr_video += '<td>'+tab_hidden+'</td>';
                tr_video += '<td><a href="#" title="remove item" class="dashicons-before dashicons-trash remove"></a></td>';
                tr_video += '</tr>';
                if($tab_panel.find('tr#no_item').length) $tab_panel.find('tr#no_item').hide();
                $tab_panel.find('#table_customtabs_html').append(tr_video);
                $tab_panel.find('#nth_prod_tab_title_adding').val('');
                $tab_panel.find('#nth_prod_tab_content_adding').val('');
                $tab_panel.find('#nth_prod_tab_hidden_xs').attr('checked', false);
                $tab_panel.find('#nth_prod_tab_hidden_sm').attr('checked', false);
                $tab_panel.find('#nth_prod_tab_hidden_lg').attr('checked', false);
            } else {
                alert("Adding failed!");
            }
        });

        $tab_panel.on('click', 'table#table_customtabs_html .remove', function(event){
            event.preventDefault();
            var conf = confirm("Do you want to remove this row?");
            if(conf) {
                var $tbody = $(this).parents('tbody');
                if( $tbody.find('tr').length <= 2 ) {
                    $tbody.find('tr#no_item').removeClass();
                }

                $(this).parents('tr').remove();

            }
        });


        var gallery_frame;
        var $image_gallery_ids = $( '#nth_gallery_image_id' );
        var $gallery_images    = $( '#nth_galleries_container' ).find( 'ul.gallery-images' );

        $('.add_gallery_images').on('click', 'a', function( event ){
            var $el = $( this );
            event.preventDefault();

            // If the media frame already exists, reopen it.
            if ( gallery_frame ) {
                gallery_frame.open();
                return;
            }

            // Create the media frame.
            gallery_frame = wp.media.frames.nth_gallery = wp.media({
                // Set the title of the modal.
                title: $el.data( 'choose' ),
                button: {
                    text: $el.data( 'update' )
                },
                states: [
                    new wp.media.controller.Library({
                        title: $el.data( 'choose' ),
                        filterable: 'all',
                        multiple: true
                    })
                ]
            });

            gallery_frame.on( 'select', function() {
                var selection = gallery_frame.state().get( 'selection' );
                var attachment_ids = $image_gallery_ids.val();

                selection.map( function( attachment ) {
                    attachment = attachment.toJSON();

                    if ( attachment.id ) {
                        attachment_ids   = attachment_ids ? attachment_ids + ',' + attachment.id : attachment.id;
                        var attachment_image = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;

                        $gallery_images.append( '<li class="image" data-attachment_id="' + attachment.id + '"><img src="' + attachment_image + '" /><ul class="actions"><li><a href="#" class="delete" title="' + $el.data('delete') + '">' + $el.data('text') + '</a></li></ul></li>' );
                    }
                });

                $image_gallery_ids.val( attachment_ids );
            });

            // Finally, open the modal.
            gallery_frame.open();

        });

        // Remove images
        $( '#nth_galleries_container' ).on( 'click', 'a.delete', function() {
            $( this ).closest( 'li.image' ).remove();

            var attachment_ids = '';

            $( '#nth_galleries_container' ).find( 'ul li.image' ).each( function() {
                var attachment_id = $(this).data( 'attachment_id' );
                attachment_ids = attachment_ids + attachment_id + ',';
            });

            $image_gallery_ids.val( attachment_ids );

            // remove any lingering tooltips
            $( '#tiptip_holder' ).removeAttr( 'style' );
            $( '#tiptip_arrow' ).removeAttr( 'style' );

            return false;
        });

        $('#nth_galleries_container ul.gallery-images').sortable({
            placeholder: 'nth-sortable-placeholder',
            revert: true,
            cursor: "move",
            distance: 5
        });
        $('#nth_galleries_container ul.gallery-images').disableSelection();

        $(document).on('sortupdate', '#nth_galleries_container ul.gallery-images', function (e, ui) {
            var _val = '';
            $(this).find('li.image').each(function (i) {
                _val += $(this).data('attachment_id') + ',';
            });
            $image_gallery_ids.val(_val);
        });

        var dialog, form,
            link = $( "#nth-dialog-form #link"),
            pos = $( "#nth-dialog-form #pos"),
            thumb = $( "#nth-dialog-form #thumbnail_id"),
            allFields = $([]).add(link).add(pos),
            btn_text = $('#nth-add-gallery-video').data('btn');


        $('#nth_galleries_container').on('click', '.gallery-videos .remove', function( event ){
            event.preventDefault();
            if( $(this).parents('tbody').find('tr').length <= 2 ) {
                $(this).parents('tbody').find('tr.no_item').removeClass('hidden');
            }
            $(this).parents('tr').remove();
        });


        $('body').on('click', '#nth-add-gallery-video', function( event ){
            event.preventDefault();
            var link = $( "#nth-dialog-form #link"),
                pos = $( "#nth-dialog-form #pos"),
                thumb = $( "#nth-dialog-form #thumbnail_id");
            if(link.val().length > 0 && thumb.val().length > 0) {
                var _val = link.val() + '|' + thumb.val();
                if( pos.val().length > 0 ) _val += '|'+ pos.val();
                var hidden = '<input type="hidden" name="nth_gallery_video[]" value="'+_val+'" />'
                $('#nth_galleries_container table.gallery-videos tbody').append(
                    '<tr>' +
                    '<td>' + thumb.parents('.nth_upload_image').find('.nth_thumbnail').html() + '</td>' +
                    '<td>' + link.val()+ hidden + '</td>' +
                    '<td>' + pos.val() + '</td>' +
                    '<td><a href="#" class="dashicons-before dashicons-trash remove"></a></td>' +
                    '</tr>'
                );
                $('#nth_galleries_container table.gallery-videos .no_item').addClass('hidden');
            }
        });

        var tiptip_args = {
            'attribute': 'data-tip',
            'fadeIn': 50,
            'fadeOut': 50,
            'delay': 200
        };
        $( '.nth-help-tip' ).tipTip( tiptip_args );


        /****    AJAX BUTTON     ****/

        $(document).on('click', '.nth-ajax-call', function(e){
            e.preventDefault();
            var param = $(this).data('param');
            var json = $(this).data('json') || 0;
            var $this = $(this);
            if( typeof param == 'undefined') return false;
            var args = {
                type: 'POST',
                url: ajaxurl,
                data: param,
                beforeSend: function(XMLHttpRequest){
                    $this.trigger('_beforeSend');
                },
                error: function (o) {
                    $this.trigger('_error', [o]);
                    console.log(o);
                },
                success: function (o) {
                    $this.trigger('_success', [o]);
                }
            }
            if( json && json !== 0 ) args.dataType = 'json';
            $.ajax(args);
        });

        var button_html = '';

        $(document).on('_beforeSend', '.theme-importer-wrapper .main-dummydata .nth-ajax-call', function () {
            button_html = $(this).html();
            $(this).text($(this).data('progress_text')).removeClass('button-primary');
            window.onbeforeunload = function(e){
                return 'Dummy database is being imported, please wait a moment!';
            };
            $(this).prop('disabled', true);
            $(this).parents('.nth-theme-import-wrapper').addClass('importing');
        });
        $(document).on('_success', '.theme-importer-wrapper .main-dummydata .nth-ajax-call', function (e, o) {
            if(o == 'connect_error') {
                alert("Couldn't connect to server, please check your connection or contact with us");
            } else if(o == 'wp_import_exist'){
                alert("Please deactivate \"WordPress Importer\" plugin before doing this step");
            }
            window.onbeforeunload = null;
            window.location.reload(true);
        });

        var __home_item = '.theme-importer-wrapper .homepage-import .homepage-item-wrapper .nth-ajax-call';

        $(document).on('_beforeSend', '.theme-importer-wrapper .homepage-import .homepage-item-wrapper .nth-ajax-call', function () {
            $(this).text('Importing...').removeClass('button-primary');
            $(this).parents('.homepage-item-inner').addClass('loading');
            $(this).parents('.homepage-import').find('.homepage-item-inner').addClass('disabled');
            $(this).prop('disabled', true);
            window.onbeforeunload = function (e) {
                return "Homepage is being setting, please wait a moment!";
            }
        });

        $(document).on('_error', __home_item, function (e, o) {
            alert('[Error]');
            window.onbeforeunload = null;
            window.location.reload(true);
        })

        $(document).on('_success', '.theme-importer-wrapper .homepage-import .homepage-item-wrapper .nth-ajax-call', function (e, o) {
            window.onbeforeunload = null;

            if(o.status == 'connect_error') {
                alert("Couldn't connect to server, please check your connection or contact with us");
                window.location.reload(true);
                return false;
            } else if(o.status == 'wp_import_exist') {
                alert("Please deactivate \"WordPress Importer\" plugin before doing this step");
                window.location.reload(true);
                return false;
            } else if(o.status == 'rev_error') {
                alert("Couldn't download main slideshow for this home! please try again or contact with our support team.");
                window.location.reload(true);
                return false;
            } else if(o.status == 'res_plugin_error') {
                alert("Please make sure you were fully installed required Plugins, then come back here and try it again!");
                window.location.reload(true);
                return false;
            } else if( typeof $(this).data('ref') !== 'undefined' && $(this).data('ref').length > 0 ) {
                window.location.href = $(this).data('ref');
            } else {
                window.location.reload(true);
            }
        });

        $(document).on('click', '.nth-theme-import-wrapper .imported-current-home-wrapper .imported-item-list li.imported-item', function (e) {
            var __id = $(this).data('id');
            $(this).parent().find('li.open').removeClass('open');
            $(this).addClass('open');
            var img_wrap = $(this).parents('.imported-current-home-wrapper').find('.imported-current-home-image');
            img_wrap.find('.image-item:not(.hidden)').addClass('hidden');
            img_wrap.find(__id).removeClass('hidden');
        });

        $(document).on('click', 'body .nth-validate-api .nth-validate-submit', function (e) {
            e.preventDefault();
            var $_root      = $(this).parents('.nth-validate-api'),
                $_this      = $(this),
                action      = $_root.data('action'),
                ajax_action = $_root.data('ajax_action'),
                token       = $_root.find('.nth-validate-data').val(),
                section     = $_root.data('section');

            if(token.length == 0) return false;
            $.ajax({
                url: ajaxurl,
                dataType: 'json',
                type: 'POST',
                data: {
                    action: ajax_action,
                    token: token
                },
                beforeSend: function () {
                    $_this.attr('disabled', 'disabled');
                },
                success: function (o) {
                    $_this.removeAttr('disabled');
                    if( typeof o.meta !== 'undefined' && o.meta.code == '200') {
                        $_root.find('.nth-validate-userid').val(o.data.id);
                        $_root.find('.nth-validate-username').val(o.data.full_name);
                        $_root.find('.nth-validate-useravata').val(o.data.profile_picture);
                        $_root.find('.nth-validate-usercount').val(o.data.counts.media);
                        $_root.find('.user-meta img').attr('src', o.data.profile_picture);
                        $_root.find('.user-meta .instagram-name').html(o.data.full_name);
                        $_root.find('.user-meta .instagram-userid').html('<strong>User ID:</strong> ' + o.data.id);
                        $_root.find('.user-meta .instagram-count').html('<strong>Media:</strong> ' + o.data.counts.media);
                    }
                }
            });
        })

    });



})(jQuery);