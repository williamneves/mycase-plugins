<?php
/**
 * Plugin Name: Recover Abandoned Cart
 * Plugin URI:
 * Description: Recover Abandoned Cart is a WooCommerce Extension Plugin which will help you Recover the Abandoned Carts and bring more sales.
 * Version: 13.3
 * Author: Fantastic Plugins
 * Author URI:
 */
/*
  Copyright 2014 Fantastic Plugins. All Rights Reserved.
  This Software should not be used or changed without the permission
  of Fantastic Plugins.
 */

require_once 'inc/fp_rac_counter.php';
require_once 'inc/fp_rac_coupon.php';
include 'inc/class_list_table_fp_rac_recovered_order.php';
include 'inc/fp_rac_previous_order.php';

class RecoverAbandonCart {

    public static function fprac_check_woo_active() {

        if (is_multisite()) {
// This Condition is for Multi Site WooCommerce Installation
            if (!is_plugin_active_for_network('woocommerce/woocommerce.php') && (!is_plugin_active('woocommerce/woocommerce.php'))) {
                if (is_admin()) {
                    $variable = "<div class='error'><p> Recover Abandoned Cart will not work until WooCommerce Plugin is Activated. Please Activate the WooCommerce Plugin. </p></div>";
                    echo $variable;
                }
                return;
            }
        } else {
// This Condition is for Single Site WooCommerce Installation
            if (!is_plugin_active('woocommerce/woocommerce.php')) {
                if (is_admin()) {
                    $variable = "<div class='error'><p> Recover Abandoned Cart will not work until WooCommerce Plugin is Activated. Please Activate the WooCommerce Plugin. </p></div>";
                    echo $variable;
                }
                return;
            }
        }
    }

    public static function fprac_header_problems() {
        ob_start();
    }

    public static function fprac_settings_tabs($tabs) {
        $tabs['fpracgenral'] = __('General Settings', 'recoverabandoncart');
        $tabs['fpracemail'] = __('Email Settings', 'recoverabandoncart');
        $tabs['fpractable'] = __('Cart List', 'recoverabandoncart');
        $tabs['fpracupdate'] = __('Check Previous Orders', 'recoverabandoncart');
        $tabs['fpraccoupon'] = __('Coupon In Mail', 'recoverabandoncart');
        $tabs['fpracmailog'] = __('Mail Log', 'recoverabandoncart');
        $tabs['fpracdebug'] = __('Troubleshoot', 'recoverabandoncart');
        $tabs['fpracreport'] = __('Reports', 'recoverabandoncart');
        $tabs['fpracrecoveredorderids'] = __('Recovered Orders', 'recoverabandoncart');
        $tabs['fpracshortocde'] = __('Shortcodes', 'recoverabandoncart');
        $tabs['fpracsupport'] = __('Support', 'recoverabandoncart');
        return $tabs;
    }

    public static function fprac_access_woo_script($array_screens) {
        $newscreenids = get_current_screen();
        $array_screens[] = $newscreenids->id;
        return $array_screens;
    }

    public static function fprac_admin_submenu() {
        add_submenu_page('woocommerce', 'Recover Abandoned Cart', 'Recover Abandoned Cart', 'manage_woocommerce', 'fprac_slug', array('RecoverAbandonCart', 'fprac_admin_settings'));
    }

//Load Email Template
    public static function email_template($subject, $message) {
        global $woocommerce, $woocommerce_settings;

// load the mailer class
        $mailer = WC()->mailer();
        $email_heading = $subject;
        $message;
        $abstractClass = new ReflectionClass('WC_Email');
        if (!$abstractClass->isAbstract()) {
            $email = new WC_Email();
// wrap the content with the email template and then add styles
            $fetch_data = $email->style_inline($mailer->wrap_message($email_heading, $message));
        } else {
            $fetch_data = $mailer->wrap_message($email_heading, $message);
        }
        return $fetch_data;
    }

//Load Email Template
    public static function template_ready($message, $link) {
        global $woocommerce, $woocommerce_settings;
        $data = $link . $message;
        return $data;
    }

    public static function remove_action_hook() {
        remove_action('woocommerce_cart_updated', array('RecoverAbandonCart', 'fp_rac_insert_entry'));
    }

    public static function fprac_admin_settings() {
        global $woocommerce, $woocommerce_settings, $current_section, $current_tab;
        $tabs = "";
        do_action('woocommerce_fprac_settings_start');
        $current_tab = ( empty($_GET['tab']) ) ? 'fpracgenral' : sanitize_text_field(urldecode($_GET['tab']));
        $current_section = ( empty($_REQUEST['section']) ) ? '' : sanitize_text_field(urldecode($_REQUEST['section']));
        if (!empty($_POST['save'])) {
            if (empty($_REQUEST['_wpnonce']) || !wp_verify_nonce($_REQUEST['_wpnonce'], 'woocommerce-settings'))
                die(__('Action failed. Please refresh the page and retry.', 'woocommercecustomtext'));

            if (!$current_section) {
//include_once('settings/settings-save.php');
                switch ($current_tab) {
                    default :
                        if (isset($woocommerce_settings[$current_tab]))
                            woocommerce_update_options($woocommerce_settings[$current_tab]);

// Trigger action for tab
                        do_action('woocommerce_update_options_' . $current_tab);
                        break;
                }

                do_action('woocommerce_update_options');

// Handle Colour Settings
                if ($current_tab == 'general' && get_option('woocommerce_frontend_css') == 'yes') {
                    
                }
            } else {
// Save section onlys
                do_action('woocommerce_update_options_' . $current_tab . '_' . $current_section);
            }

// Clear any unwanted data
// $woocommerce->clear_product_transients();
            delete_transient('woocommerce_cache_excluded_uris');
// Redirect back to the settings page
            $redirect = esc_url_raw(add_query_arg(array('saved' => 'true')));
//  $redirect .= add_query_arg('noheader', 'true');

            if (isset($_POST['subtab'])) {
                wp_safe_redirect($redirect);
                exit;
            }
        }
// Get any returned messages
        $error = ( empty($_GET['wc_error']) ) ? '' : urldecode(stripslashes($_GET['wc_error']));
        $message = ( empty($_GET['wc_message']) ) ? '' : urldecode(stripslashes($_GET['wc_message']));

        if ($error || $message) {

            if ($error) {
                echo '<div id="message" class="error fade"><p><strong>' . esc_html($error) . '</strong></p></div>';
            } else {
                echo '<div id="message" class="updated fade"><p><strong>' . esc_html($message) . '</strong></p></div>';
            }
        } elseif (!empty($_GET['saved'])) {

            echo '<div id="message" class="updated fade"><p><strong>' . __('Your settings have been saved.', 'recoverabandoncart') . '</strong></p></div>';
        } elseif (!empty($_GET['resetted'])) {
            echo '<div id="message" class="updated fade"><p><strong>' . __('Your settings have been Restored.', 'recoverabandoncart') . '</strong></p></div>';
        }
        ?>
        <div class="wrap woocommerce">
            <form method="post" id="mainform" action="" enctype="multipart/form-data">
                <div class="icon32 icon32-woocommerce-settings" id="icon-woocommerce"><br /></div><h2 class="nav-tab-wrapper woo-nav-tab-wrapper">
                    <?php
                    $tabs = apply_filters('woocommerce_fprac_settings_tabs_array', $tabs);
                    foreach ($tabs as $name => $label)
                        echo '<a href="' . admin_url('admin.php?page=fprac_slug&tab=' . $name) . '" class="nav-tab ' . ( $current_tab == $name ? 'nav-tab-active' : '' ) . '">' . $label . '</a>';

                    do_action('woocommerce_fprac_settings_tabs');
                    ?>
                </h2>

                <?php
                switch ($current_tab) {
                    case "fpractable":
                        RecoverAbandonCart::fp_rac_adandoncart_admin_display();
                        break;
                    case "fpracemail":
                        if (!isset($_GET['rac_new_email']) && !isset($_GET['rac_edit_email']) && !isset($_GET['rac_send_email'])) {
                            do_action('woocommerce_fprac_settings_tabs_' . $current_tab); // @deprecated hook
                            do_action('woocommerce_fprac_settings_' . $current_tab);
                            ?>
                            <p class="submit" style="margin-left: 25px;">
                                <?php if (!isset($GLOBALS['hide_save_button'])) : ?>
                                    <input name="save" class="button-primary" type="submit" value="<?php _e('Save', 'recoverabandoncart'); ?>" />
                                <?php endif; ?>
                                <input type="hidden" name="subtab" id="last_tab" />
                                <?php wp_nonce_field('woocommerce-settings'); ?>
                            </p>
                            <p><h3><?php _e('Mail Template Settings', 'recoverabandoncart'); ?></h3></p>
                            <?php
                        }
                        //email template lists

                        global $wpdb;
                        $table_name = $wpdb->prefix . 'rac_templates_email';
                        $templates = $wpdb->get_results("SELECT * FROM $table_name", OBJECT);

                        if (isset($_GET['rac_new_email'])) {
                            $editor_id = "rac_email_template_new";
                            //$content = get_option('rac_email_template');
                            $settings = array('textarea_name' => 'rac_email_template_new');
                            $admin_url = admin_url('admin.php');
                            $template_list_url = esc_url_raw(add_query_arg(array('page' => 'fprac_slug', 'tab' => 'fpracemail'), $admin_url));
                            $content = "Hi {rac.firstname},<br><br>We noticed you have added the following Products in your Cart, but haven't completed the purchase. {rac.Productinfo}<br><br>We have captured the Cart for your convenience. Please use the following link to complete the purchase {rac.cartlink}<br><br>Thanks.";
                            echo '<table class="widefat"><tr><td>';
                            echo '<tr><td colspan="2"><span><strong>Use {rac.cartlink} to insert the Cart Link in the mail</strong></span></td></tr>';
                            echo '<tr><td colspan="2"><span><strong>Use {rac.date} to insert the Abandoned Cart Date in the mail</strong></span></td></tr>';
                            echo '<tr><td colspan="2"><span><strong>Use {rac.time} to insert the Abandoned Cart Time in the mail</strong></span></td></tr>';
                            echo '<tr><td colspan="2"><span><strong>Use {rac.firstname} to insert Reciever First Name in the mail</strong></span></td></tr>';
                            echo '<tr><td colspan="2"><span><strong>Use {rac.lastname} to insert Receiver Last Name in the mail</strong></span></td></tr>';
                            echo '<tr><td colspan="2"><span><strong>Use {rac.Productinfo} to insert Product Information in the mail</strong></span></td></tr>';
                            echo '<tr><td colspan="2"><span><strong>Use {rac.coupon} to insert Coupon Code in the mail</strong></span></td></tr><tr><td>';
                            echo '<tr><td>' . __('Template Name', 'recoverabandoncart') . ': </td><td><input type="text" name="rac_template_name" id="rac_template_name"></td></tr>';
                            echo '<tr><td>' . __('Template Status', 'recoverabandoncart') . ':</td><td> <select name="rac_template_status" id="rac_template_status">
                                <option value="ACTIVE">Activated</option>
                                <option value="NOTACTIVE">Deactivated</option>
                                </select></td></tr>';
                            // mail plain or html
                            echo '<tr><td>' . __('Email Template Type', 'recoverabandoncart') . ':</td><td><select name="rac_template_mail" class="rac_template_mail">
                                 <option value="HTML">Woocommerce Template</option>
                                 <option value="PLAIN">HTML Template</option>
                                 </select></td></tr>';
                            // mail plain or html
                            // mail logo upload
                            echo '<tr class="rac_logo_link"><td>' . __('Header Image For HTML Template', 'recoverabandoncart') . ':</td><td><input type="text" size="40" name="rac_logo_mail" id="rac_logo_mail"><input class="upload_button" id="image_uploader" type="submit" value="Media Uploader" /></td></tr>';
                            // mail logo upload
                            echo '<tr class = "rac_email_sender"><td>' . __('Email Sender Option', 'recoverabandoncart') . ': </td><td><input type="radio" name="rac_sender_opt" id="rac_sender_woo" value="woo" class="rac_sender_opt">woocommerce <input type="radio" name="rac_sender_opt" id="rac_sender_local" value="local" class="rac_sender_opt">local</td></tr>';
                            echo '<tr class="rac_local_senders"><td>' . __('From Name', 'recoverabandoncart') . ': </td><td><input type="text" name="rac_from_name"  id="rac_from_name"></td></tr>';
                            echo '<tr class="rac_local_senders"><td>' . __('From Email', 'recoverabandoncart') . ': </td><td><input type="text" name="rac_from_email"  id="rac_from_email"></td></tr>';

                            echo '<tr><td>' . __('Bcc', 'recoverabandoncart') . ':</td><td> <input type="textarea" name="rac_blind_carbon_copy" id="rac_blind_carbon_copy"></td></tr>';

                            echo '<tr><td>' . __('Subject', 'recoverabandoncart') . ':</td><td> <input type="text" name="rac_subject" id="rac_subject"></td></tr>';
                            echo '<tr><td>' . __('Duration to Send Mail After Abandoned Cart', 'recoverabandoncart') . ':<select name="rac_duration_type" id="rac_duration_type">
                                <option value="minutes">Minutes</option>
                                <option value="hours">Hours</option>
                                <option value="days">Days</option
                                </select></td>';
                            echo '<td><span><input type="text" name="rac_mail_duration" id="rac_duration"></span></td></tr>';
                            echo '<tr><td>' . __('Cart Link Anchor Text', 'recoverabandoncart') . ': </td><td><input type="text" name="rac_anchor_text" value="Cart Link" id="rac_anchor_text"></td></tr>';
                            echo '<tr><td> ' . __('Message', 'recoverabandoncart') . ':</td>';
                            echo '<td>';
                            wp_editor($content, $editor_id, $settings);
                            echo '</td></tr>';
                            echo '<tr><td><input type="button" name="rac_save_new_template" class="button button-primary button-large" id="rac_save_new_template" value="Save">&nbsp;';
                            echo '<a href="' . $template_list_url . '"><input type="button" class="button" name="returntolist" value="Return to Mail Templates"></a>&nbsp;';
                            echo '</td></tr>';
                            echo '</table>';
                            ?>
                            <script>
                                function get_tinymce_content() {
                                    if (jQuery("#wp-rac_email_template_new-wrap").hasClass("tmce-active")) {
                                        //rac_email_template_new
                                        return tinyMCE.get('rac_email_template_new').getContent();
                                        //return tinyMCE.activeEditor.getContent();
                                    } else {
                                        return jQuery("#rac_email_template_new").val();
                                    }
                                }
                                jQuery(document).ready(function () {
                                    jQuery("#rac_template_name").val("Default");
                                    jQuery("#rac_from_name").val("Admin");
                                    jQuery("#rac_sender_woo").attr("checked", "checked");
                                    jQuery(".rac_sender_opt").change(function () {
                                        if (jQuery("#rac_sender_woo").is(":checked")) {
                                            jQuery(".rac_local_senders").css("display", "none");
                                        } else {
                                            jQuery(".rac_local_senders").css("display", "table-row");
                                        }
                                    });
                                    jQuery("#rac_subject").val("Recover Abandon Cart");
                                    jQuery("#rac_from_email").val("<?php echo get_option('admin_email'); ?>");
                                    jQuery("#rac_duration_type").val("days");
                                    jQuery("#rac_template_status").val("ACTIVE");
                                    jQuery(".rac_template_mail").val("HTML");
                                    jQuery("#rac_logo_mail").val("<?php echo $admin_url; ?>");
                                    jQuery("#rac_duration").val("1");
                                    var content = "Hi {rac.firstname},<br><br>We noticed you have added the following Products in your Cart, but haven't completed the purchase. {rac.Productinfo}<br><br>We have captured the Cart for your convenience. Please use the following link to complete the purchase {rac.cartlink}<br><br>Thanks.";
                                    jQuery("#rac_email_template_new").val(content);
                                    jQuery("#rac_duration_type").change(function () {
                                        jQuery("span#rac_duration").html(jQuery("#rac_duration_type").val());
                                    });
                                    jQuery("#rac_save_new_template").click(function () {
                                        jQuery(this).prop("disabled", true);
                                        var rac_template_name = jQuery("#rac_template_name").val();
                                        var rac_template_status = jQuery("#rac_template_status").val();
                                        var rac_sender_option = jQuery("input:radio[name=rac_sender_opt]:checked").val();
                                        var rac_from_name = jQuery("#rac_from_name").val();
                                        var rac_from_email = jQuery("#rac_from_email").val();
                                        var rac_blind_carbon_copy = jQuery("#rac_blind_carbon_copy").val();
                                        var rac_subject = jQuery("#rac_subject").val();
                                        var rac_anchor_text = jQuery("#rac_anchor_text").val();
                                        var rac_message = get_tinymce_content();
                                        var rac_duration_type = jQuery("#rac_duration_type").val();
                                        var rac_mail_duration = jQuery("span #rac_duration").val();
                                        var rac_template_mail = jQuery(".rac_template_mail").val(); // mail plain or html
                                        var rac_logo_mail = jQuery("#rac_logo_mail").val(); // mail logo upload

                                        console.log(jQuery("#rac_email_template_new").val());
                                        var data = {
                                            action: "rac_new_template",
                                            rac_sender_option: rac_sender_option,
                                            rac_template_name: rac_template_name,
                                            rac_template_status: rac_template_status,
                                            rac_from_name: rac_from_name,
                                            rac_from_email: rac_from_email,
                                            rac_blind_carbon_copy: rac_blind_carbon_copy,
                                            rac_subject: rac_subject,
                                            rac_anchor_text: rac_anchor_text,
                                            rac_message: rac_message,
                                            rac_duration_type: rac_duration_type,
                                            rac_mail_duration: rac_mail_duration,
                                            rac_template_mail: rac_template_mail, // mail plain or html
                                            rac_logo_mail: rac_logo_mail  // mail logo upload
                                        };
                                        jQuery.ajax({
                                            type: "POST",
                                            url: ajaxurl,
                                            data: data
                                        }).done(function (response) {
                                            jQuery("#rac_save_new_template").prop("disabled", false);
                                            window.location.replace("<?php echo $template_list_url; ?>");
                                        });
                                        console.log(data);
                                    });
                                    // mail logo upload
                                    var uploader_open;
                                    jQuery('.upload_button').click(function (e) {
                                        e.preventDefault();
                                        if (uploader_open) {
                                            uploader_open.open();
                                            return;
                                        }

                                        uploader_open = wp.media.frames.uploader_open = wp.media({
                                            title: 'Media Uploader',
                                            button: {
                                                text: 'Media Uploader'
                                            },
                                            multiple: false
                                        });
                                        //When a file is selected, grab the URL and set it as the text field's value
                                        uploader_open.on('select', function () {
                                            attachment = uploader_open.state().get('selection').first().toJSON();
                                            jQuery('#rac_logo_mail').val(attachment.url);
                                        });
                                        uploader_open.open();
                                    });
                                    //mail logo upload
                                });</script>
                            <style>
                                .rac_local_senders{
                                    display:none;
                                }
                                #image_uploader {
                                    color: blueviolet;
                                }
                            </style>
                            <?php
                        } else if (isset($_GET['rac_edit_email']) && !isset($_GET['preview'])) {
                            $template_id = $_GET['rac_edit_email'];
                            $edit_templates = $wpdb->get_results("SELECT * FROM $table_name WHERE id=$template_id", OBJECT);
                            $edit_templates = $edit_templates[0]; // get array 0 value mutidimensional method
                            $admin_url = admin_url('admin.php');
                            $template_list_url = esc_url_raw(add_query_arg(array('page' => 'fprac_slug', 'tab' => 'fpracemail'), $admin_url));
                            $editor_id = "rac_email_template_edit";
                            $content = $edit_templates->message;
                            $settings = array('textarea_name' => 'rac_email_template_edit');
                            echo '<table class="widefat"><tr><td>';
                            echo '<tr><td colspan="2"><span><strong>Use {rac.cartlink} to insert the Cart Link in the mail</strong></span></td></tr>';
                            echo '<tr><td colspan="2"><span><strong>Use {rac.date} to insert the Abandoned Cart Date in the mail</strong></span></td></tr>';
                            echo '<tr><td colspan="2"><span><strong>Use {rac.time} to insert the Abandoned Cart Time in the mail</strong></span></td></tr>';
                            echo '<tr><td colspan="2"><span><strong>Use {rac.firstname} to insert Reciever First Name in the mail</strong></span></td></tr>';
                            echo '<tr><td colspan="2"><span><strong>Use {rac.lastname} to insert Reciever Last Name in the mail</strong></span></td></tr>';
                            echo '<tr><td colspan="2"><span><strong>Use {rac.Productinfo} to insert Product Information in the mail</strong></span></td></tr>';
                            echo '<tr><td colspan="2"><span><strong>Use {rac.coupon} to insert Coupon Code in the mail</strong></span></td></tr><tr><td>';
                            echo __('Template Name', 'recoverabandoncart') . ':</td>';
                            echo '<td><input type="text" name="rac_template_name" id="rac_template_name" value="' . $edit_templates->template_name . '"></td></tr>';
                            $template_active = selected($edit_templates->status, 'ACTIVE', false);
                            $template_not_active = selected($edit_templates->status, 'NOTACTIVE', false);
                            echo '<tr><td>' . __('Template Status', 'recoverabandoncart') . ':</td><td> <select name="rac_template_status" id="rac_template_status">
                                <option value="ACTIVE" ' . $template_active . '>Activated</option>
                                <option value="NOTACTIVE" ' . $template_not_active . '>Deactivated</option>
                                </select></td></tr>';
                            // mail plain or html
                            $template_html = selected($edit_templates->mail, 'HTML', false);
                            $template_plain = selected($edit_templates->mail, 'PLAIN', false);
                            echo '<tr><td>' . __('Email Template Type', 'recoverabandoncart') . ':</td><td><select name="rac_template_mail" class="rac_template_mail">
                                 <option value="HTML" ' . $template_html . '>Woocommerce Template</option>
                                 <option value="PLAIN" ' . $template_plain . '>HTML Template</option>
                                  </select></td></tr>';
                            // mail plain or html
                            // mail logo upload
                            echo '<tr class="rac_logo_link"><td>' . __('Header Image For HTML Template', 'recoverabandoncart') . ':</td><td><input type="text" size="40" name="rac_logo_mail" id="rac_logo_mail" value="' . $edit_templates->link . '"><input class="upload_button" id="image_uploader" type="submit" value="Media Uploader" /></td></tr>';
                            // mail logo upload
                            $woo_selected = checked($edit_templates->sender_opt, 'woo', false);
                            $local_selected = checked($edit_templates->sender_opt, 'local', false);
                            echo '<tr class = "rac_email_sender"><td>' . __('Email Sender Option', 'recoverabandoncart') . ': </td><td><input type="radio" name="rac_sender_opt" id="rac_sender_woo" value="woo" ' . $woo_selected . ' class="rac_sender_opt">woocommerce
                                <input type="radio" name="rac_sender_opt" id="rac_sender_local" value="local" ' . $local_selected . ' class="rac_sender_opt">local</td></tr>';
                            echo '<tr class="rac_local_senders"><td>' . __('From Name', 'recoverabandoncart') . ':</td>';
                            echo '<td><input type="text" name="rac_from_name" id="rac_from_name" value="' . $edit_templates->from_name . '"></td></tr>';
                            echo '<tr class="rac_local_senders"><td>' . __('From Email', 'recoverabandoncart') . ':</td>';
                            echo '<td><input type="text" name="rac_from_email" id="rac_from_email" value="' . $edit_templates->from_email . '"></td></tr>';
                            echo '<tr><td>' . __('Bcc', 'recoverabandoncart') . ':</td>';
                            if (isset($edit_templates->rac_blind_carbon_copy)) {
                                $bcc = $edit_templates->rac_blind_carbon_copy;
                            } else {
                                $bcc = '';
                            }
                            echo '<td><input type="textarea" name="rac_blind_carbon_copy" id="rac_blind_carbon_copy" value="' . $bcc . '"></td></tr>';
                            echo '<tr><td>' . __('Subject', 'recoverabandoncart') . ':</td>';
                            echo '<td><input type="text" name="rac_subject" id="rac_subject" value="' . $edit_templates->subject . '"></td></tr>';
                            $duration_type = $edit_templates->sending_type;
                            echo '<tr><td>' . __('Send Mail Duration', 'recoverabandoncart') . ':<select name="rac_duration_type" id="rac_duration_type">
                                <option value="minutes" ' . selected($duration_type, "minutes", false) . '>Minutes</option>
                                <option value="hours" ' . selected($duration_type, "hours", false) . '>Hours</option>
                                <option value="days" ' . selected($duration_type, "days", false) . '>Days</option
                                </select>';
                            echo '</td><td><span><input type="text" name="rac_mail_duration" id="rac_duration" value="' . $edit_templates->sending_duration . '"></span></td></tr>';
                            echo '<tr><td>' . __('Cart Link Anchor Text', 'recoverabandoncart') . ': </td><td><input type="text" name="rac_anchor_text" id="rac_anchor_text" value="' . $edit_templates->anchor_text . '"></td></tr>';
                            echo '<tr><td> ' . __('Message', 'recoverabandoncart') . ':</td>';
                            echo '<td>';
                            wp_editor($content, $editor_id, $settings);
                            echo '</td></tr>';
                            echo '<tr><td>';
                            echo '<input type="button" class="button button-primary button-large" name="rac_save_new_template" id="rac_save_new_template" value="' . __('Save Changes', 'recoverabandoncart') . '">&nbsp;';
                            echo '<a href="' . $template_list_url . '"><input type="button" class="button" name="returntolist" value="' . __('Return to Mail Templates', 'recoverabandoncart') . '"></a>&nbsp;';
                            echo '</td></tr>';
                            echo '<tr><td>';
                            echo '<div id="rac_mail_result" style="display:none"> Settings Saved</div>';
                            echo '</td></tr>';

                            echo '<tr><td>' . __("Test Email", "recoverabandoncart") . ' : ';
                            echo '<input type="email" class=rac_send_test_email_for_this_template></td></tr>';
                            echo '<tr><td><input type="button" class="button button-primary button-large" name="rac_send_template_preview" id="rac_send_template_preview" value="' . __('Send Test', 'recoverabandoncart') . '">&nbsp;';
                            echo '<div id="rac_test_mail_sent" style="display:none"> Mail Sent</div>'
                            . '<div class="rac_hide_this_message">' . __('Shortcodes replaced by Sample Data', 'recoverabandoncart') . '</div></td></tr>';

                            echo '</table>';
                            echo '<script>
function get_tinymce_content() {
                                    if (jQuery("#wp-rac_email_template_edit-wrap").hasClass("tmce-active")) {
                                        return tinyMCE.get("rac_email_template_edit").getContent();
                                    } else {
                                        return jQuery("#rac_email_template_edit").val();
                                    }
                                }
jQuery(document).ready(function(){
                                jQuery("#rac_duration_type").change(function(){
                                     jQuery("span#rac_duration").html(jQuery("#rac_duration_type").val());
                                });
                                //normal ready event
                                   if(jQuery("#rac_sender_woo").is(":checked")){
                                jQuery(".rac_local_senders").css("display","none");
                                }else{
                                jQuery(".rac_local_senders").css("display","table-row");
                                }

                                    jQuery(".rac_sender_opt").change(function(){
                                if(jQuery("#rac_sender_woo").is(":checked")){
                                jQuery(".rac_local_senders").css("display","none");
                                }else{
                                jQuery(".rac_local_senders").css("display","table-row");
                                }
                                });
                                jQuery("#rac_save_new_template").click(function(){
                                 jQuery(this).prop("disabled",true);
                                var rac_template_name = jQuery("#rac_template_name").val();
                                 var rac_template_status = jQuery("#rac_template_status").val();
                                var rac_sender_option = jQuery("input:radio[name=rac_sender_opt]:checked").val();
                                var rac_from_name = jQuery("#rac_from_name").val();
                                 var rac_from_email = jQuery("#rac_from_email").val();
                                 var rac_blind_carbon_copy = jQuery("#rac_blind_carbon_copy").val();
                                 var rac_subject = jQuery("#rac_subject").val();
                                 var rac_anchor_text = jQuery("#rac_anchor_text").val();
                                 var rac_message = get_tinymce_content();
                                 var rac_duration_type = jQuery("#rac_duration_type").val();
                                 var rac_mail_duration = jQuery("span #rac_duration").val();
                                 var rac_template_mail = jQuery(".rac_template_mail").val();  // mail plain or html
                                 var rac_logo_mail = jQuery("#rac_logo_mail").val(); //  mail logo upload
                                 var rac_template_id = ' . $template_id . '
                                console.log(jQuery("#rac_email_template_edit").val());


                                var data = {
                                action:"rac_edit_template",
                                rac_sender_option:rac_sender_option,
                                rac_template_name:rac_template_name,
                                rac_template_status:rac_template_status,
                                rac_from_name:rac_from_name,
                                rac_from_email:rac_from_email,
                                rac_blind_carbon_copy: rac_blind_carbon_copy,
                                rac_subject:rac_subject,
                                rac_anchor_text:rac_anchor_text,
                                rac_message:rac_message,
                                rac_duration_type:rac_duration_type,
                                rac_mail_duration:rac_mail_duration,
                                rac_template_id:rac_template_id,
                                rac_template_mail:rac_template_mail, // mail plain or html
                                rac_logo_mail: rac_logo_mail  // mail logo upload
                                };

                                jQuery.ajax({
                                type:"POST",
                                url:ajaxurl,
                                data:data
                                }).done(function(response){
                                 jQuery("#rac_save_new_template").prop("disabled",false);
                                 jQuery("#rac_mail_result").css("display","block");
                                });
                                console.log(data);
                                });

                                jQuery("#rac_send_template_preview").click(function(){
                                    var rac_to_email = jQuery(".rac_send_test_email_for_this_template").val();
                                    var atpos=rac_to_email.indexOf("@");
                                    var dotpos=rac_to_email.lastIndexOf(".");
                                    if (rac_to_email !== "") {
                                        if (atpos<1 || dotpos<atpos+2 || dotpos+2>=rac_to_email.length){
                                            alert("' . __("Please enter valid email id", "recoverabandoncart") . '");
                                            return false;
                                        }
                                    }
                                    else{
                                        alert("' . __("Please enter email id", "recoverabandoncart") . '");
                                        return false;
                                    }
                                    var rac_template_name = jQuery("#rac_template_name").val();
                                    var rac_template_status = jQuery("#rac_template_status").val();
                                    var rac_sender_option = jQuery("input:radio[name=rac_sender_opt]:checked").val();
                                    var rac_from_name = jQuery("#rac_from_name").val();
                                    var rac_from_email = jQuery("#rac_from_email").val();
                                    var rac_blind_carbon_copy = jQuery("#rac_blind_carbon_copy").val();
                                    var rac_subject = jQuery("#rac_subject").val();
                                    var rac_anchor_text = jQuery("#rac_anchor_text").val();
                                    var rac_message = get_tinymce_content();
                                    var rac_duration_type = jQuery("#rac_duration_type").val();
                                    var rac_mail_duration = jQuery("span #rac_duration").val();
                                    var rac_template_mail = jQuery(".rac_template_mail").val();  // mail plain or html
                                    var rac_logo_mail = jQuery("#rac_logo_mail").val(); //  mail logo upload
                                    var rac_template_id = ' . $template_id . '
                                    console.log(jQuery("#rac_email_template_edit").val());


                                    var data = {
                                    action:"rac_send_template_preview_email",
                                    rac_to_email : rac_to_email,
                                    rac_sender_option:rac_sender_option,
                                    rac_template_name:rac_template_name,
                                    rac_template_status:rac_template_status,
                                    rac_from_name:rac_from_name,
                                    rac_from_email:rac_from_email,
                                    rac_blind_carbon_copy: rac_blind_carbon_copy,
                                    rac_subject:rac_subject,
                                    rac_anchor_text:rac_anchor_text,
                                    rac_message:rac_message,
                                    rac_duration_type:rac_duration_type,
                                    rac_mail_duration:rac_mail_duration,
                                    rac_template_id:rac_template_id,
                                    rac_template_mail:rac_template_mail, // mail plain or html
                                    rac_logo_mail: rac_logo_mail  // mail logo upload
                                };

                                jQuery.ajax({type:"POST",url:ajaxurl,data:data}).done(function(response){

                                        jQuery("#rac_test_mail_sent").css("display","block");
                                        jQuery(".rac_hide_this_message").css("display","none");

                                });
                                console.log(data);
                                });


                                });</script>
                               <style>
                               #image_uploader {
                                     color: blueviolet;
                                }
                                </style>
                                ';
                        } else if (isset($_GET['rac_send_email'])) {
                            ?>
                            <table class="widefat">
                                <tr>
                                    <td><?php _e('Load Message from existing Template'); ?></td>
                                    <td><select id="rac_load_mail">
                                            <?php
                                            foreach ($templates as $key => $each_template) {
                                                if ($key == 0) {
                                                    $template_name = $each_template->template_name . '( #' . $each_template->id . ')';
                                                    echo '<option value=' . $each_template->id . ' selected>' . $template_name . '</option>';
                                                } else {
                                                    $template_name = $each_template->template_name . '( #' . $each_template->id . ')';
                                                    echo '<option value=' . $each_template->id . '>' . $template_name . '</option>';
                                                }
                                            }
                                            if (isset($templates[0]->rac_blind_carbon_copy)) {
                                                $bcc = $templates[0]->rac_blind_carbon_copy;
                                            } else {
                                                $bcc = '';
                                            }
                                            ?></select></td>
                                </tr>
                                <!--mail plain or html-->
                                <tr>
                                    <td><?php _e('Email Template Type'); ?></td>
                                    <td><select name="rac_template_mail" class="rac_template_mail">
                                            <option value="HTML"<?php selected('HTML', $templates[0]->mail); ?>>Woocommerce Template</option>
                                            <option value="PLAIN"<?php selected('PLAIN', $templates[0]->mail); ?>>HTML Template</option>
                                        </select></td>
                                </tr>
                                <!-- mail plain or html-->

                                <!--   mail logo upload -->
                                <tr class="rac_logo_link">
                                    <td><?php _e('Header Image For HTML Template', 'recoverabandoncart'); ?>:</td>
                                    <td><input type="text" size="40" name="rac_logo_mail" id="rac_logo_mail" value="<?php echo $templates[0]->link ?>"><input class="upload_button" id="image_uploader" type="submit" value="Media Uploader" /></td>
                                </tr>
                                <!-- mail logo upload-->

                                <tr class = "rac_email_sender">
                                    <td><?php _e('Email Sender Option', 'recoverabandoncart'); ?>: </td>
                                    <td>
                                        <input type="radio" name="rac_sender_opt" id="rac_sender_woo" value="woo" <?php checked('woo', $templates[0]->sender_opt); ?>  class="rac_sender_opt">woocommerce
                                        <input type="radio" name="rac_sender_opt" id="rac_sender_local" value="local" <?php checked('local', $templates[0]->sender_opt); ?>  class="rac_sender_opt">local
                                    </td>
                                </tr>


                                <tr class="rac_local_senders">
                                    <td> <?php _e('From Name', 'recoverabandoncart'); ?>:</td>
                                    <td><input type="text" name="rac_from_name" id="rac_from_name" value="<?php echo $templates[0]->from_name; ?>"></td>
                                </tr>
                                <tr class="rac_local_senders">
                                    <td><?php _e('From Email', 'recoverabandoncart'); ?>:</td>
                                    <td><input type="text" name="rac_from_email" id="rac_from_email" value="<?php echo $templates[0]->from_email; ?>"></td>
                                </tr>
                                <tr>
                                    <td>Bcc:</td>
                                    <td><input type="textarea" id="rac_blind_carbon_copy" name="rac_blind_carbon_copy" value="<?php echo $bcc; ?>"></td>
                                </tr>
                                <tr>
                                    <td>Subject:</td>
                                    <td><input type="text" id="rac_mail_subject" name="rac_manual_mail_subject" value="<?php echo $templates[0]->subject; ?>"></td>
                                </tr>
                                <tr>
                                    <td>Cart Link Anchor Text:</td>
                                    <td><input type="text" id="rac_anchor_text" name="rac_anchor_text" value="<?php echo $templates[0]->anchor_text; ?>"></td>
                                </tr>
                                <tr>
                                    <td><?php _e('Message', 'recoverabandoncart'); ?>:</td>
                                    <?php
                                    $content = $templates[0]->message;
                                    $editor_id = "rac_manual_mail";
                                    $settings = array('textarea_name' => 'rac_manual_mail');
                                    ?>
                                    <td><?php wp_editor($content, $editor_id, $settings); ?></td>
                                </tr>
                                <tr>
                                    <td>
                                        <input type="hidden" name="rac_cart_row_ids" id="rac_cart_row_ids" value="<?php echo $_GET['rac_send_email']; ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <td><input type="button" class="button-primary" name="rac_mail" id="rac_mail" value="Send Mail Now"> <span id="rac_mail_result" style="display: none;">Mail Sent Successfully</span></td>
                                </tr>
                            </table>
                            <script type="text/javascript">
                                function set_tinymce_content(value) {
                                    if (jQuery("#wp-rac_manual_mail-wrap").hasClass("tmce-active")) {
                                        return tinyMCE.activeEditor.setContent(value);
                                    } else {
                                        return jQuery("#rac_manual_mail").val(value);
                                    }
                                }
                                function get_tinymce_content_value() {
                                    if (jQuery("#wp-rac_manual_mail-wrap").hasClass("tmce-active")) {
                                        return tinyMCE.get('rac_manual_mail').getContent();
                                        //return tinyMCE.activeEditor.getContent();
                                    } else {
                                        return jQuery("#rac_manual_mail").val();
                                    }
                                }
                                jQuery(document).ready(function () {
                                    var template_id;
                                    template_id = jQuery('#rac_load_mail').val();
                                    jQuery('#rac_load_mail').change(function () {
                                        if (jQuery('#rac_load_mail').val() != 'no') {
                                            console.log(jQuery('#rac_load_mail').val());
                                            var row_id = jQuery('#rac_load_mail').val();
                                            var data = {
                                                action: 'rac_load_mail_message',
                                                row_id: row_id
                                            }


                                            jQuery.post(ajaxurl, data,
                                                    function (response) {
                                                        //alert(response);
                                                        var template = JSON.parse(response);
                                                        console.log(template.message);
                                                        //var k = jQuery("select option:selected").text();

                                                        set_tinymce_content(template.message);
                                                        console.log(jQuery('#rac_manual_mail').val());
                                                        jQuery("input[name=rac_sender_opt][value=" + template.mail_send_opt + "]").attr('checked', true);
                                                        jQuery("select option[value=" + template.mail + "]").prop('selected', true); // mail plain or html
                                                        jQuery("#rac_from_name").val(template.from_name);
                                                        jQuery("#rac_logo_mail").val(template.link); //mail logo upload
                                                        jQuery("#rac_from_email").val(template.from_email);
                                                        jQuery("#rac_blind_carbon_copy").val(template.rac_blind_carbon_copy);
                                                        jQuery("#rac_mail_subject").val(template.subject);
                                                        jQuery("#rac_anchor_text").val(template.cart_link_text);
                                                        template_id = row_id;
                                                    });
                                        }
                                    });
                                    //event for sender opt
                                    if (jQuery('#rac_sender_woo').is(':checked'))
                                    {
                                        jQuery('.rac_local_senders').hide();
                                    } else {
                                        jQuery('.rac_local_senders').show();
                                    }
                                    jQuery('input[name=rac_sender_opt]').change(function () {
                                        if (jQuery('#rac_sender_woo').is(':checked'))
                                        {
                                            jQuery('.rac_local_senders').hide();
                                        } else {
                                            jQuery('.rac_local_senders').show();
                                        }
                                    });
                                    jQuery('#rac_mail').click(function () {     // send mail now button when you cick trigger this function
                                        jQuery("#rac_mail").prop("disabled", true);
                                        var rac_message = get_tinymce_content_value();
                                        var data = {
                                            action: 'rac_manual_mail_ajax',
                                            rac_mail_row_ids: jQuery('#rac_cart_row_ids').val(),
                                            rac_sender_option: jQuery('input[name=rac_sender_opt]:radio:checked').val(),
                                            rac_template_mail: jQuery('select[name=rac_template_mail]').val(), // mail plain or html
                                            rac_logo_mail: jQuery('#rac_logo_mail').val(), //mail logo upload
                                            rac_anchor_text: jQuery('#rac_anchor_text').val(),
                                            rac_message: rac_message,
                                            rac_from_name: jQuery('#rac_from_name').val(),
                                            rac_from_email: jQuery('#rac_from_email').val(),
                                            rac_blind_carbon_copy: jQuery('#rac_blind_carbon_copy').val(),
                                            rac_mail_subject: jQuery('#rac_mail_subject').val(),
                                            template_id: template_id,
                                        }
                                        console.log(data);
                                        jQuery.post(ajaxurl, data,
                                                function (response) {
                                                    jQuery("#rac_mail").prop("disabled", false);
                                                    jQuery("#rac_mail_result").css("display", "inline-block");
                                                    //alert(response);
                                                    //jQuery('#rac_manual_mail').val(response);
                                                    // tinyMCE.get('rac_manual_mail').setContent(response);
                                                    // console.log(jQuery('#rac_manual_mail').val());
                                                });
                                    });
                                });</script>
                            <?php
                        } elseif (isset($_GET['preview'])) {
                            global $wpdb;
                            $table_name = $wpdb->prefix . 'rac_templates_email';
                            $id = $_GET['rac_edit_email'];
                            $templates = $wpdb->get_results("SELECT * FROM $table_name WHERE id= $id", ARRAY_A);
                            foreach ($templates as $each_template) {
                                $mail_logo_added = $each_template['link'];
                                $view_template = $each_template['mail'];
                                $logo = '<p style="margin-top:0;"><img src="' . esc_url($mail_logo_added) . '" width="100" height="100"/></a></p>';
                                $subject = $each_template['subject'];
                                $message = $each_template['message'];

                                if ($view_template == "HTML") {
                                    echo self::email_template($subject, $message);
                                } else {
                                    ?>
                                    <style type="text/css">
                                        div.block {
                                            background: #ffffff;
                                            border-radius: 10px;
                                        }
                                        div.centered {
                                            display: inline-block;
                                            width: 2px;
                                            height: 350px;
                                            padding: 10px 15px;
                                            background:#ffffff;
                                        }
                                    </style>
                                    <div class="block" style="height: 400px;width: 100%;">
                                        <div class="centered" style="float:left;">  </div>  </br>
                                        <p> <?php echo self::template_ready($message, $logo); ?> </p>
                                    </div>
                                    <?php
                                }
                            }
                        } else {
                            $admin_url = admin_url('admin.php');
                            $new_template_url = esc_url_raw(add_query_arg(array('page' => 'fprac_slug', 'tab' => 'fpracemail', 'rac_new_email' => 'template'), $admin_url));
                            $edit_template_url = esc_url_raw(add_query_arg(array('page' => 'fprac_slug', 'tab' => 'fpracemail', 'rac_edit_email' => 'template'), $admin_url));

                            echo '<a href=' . $new_template_url . '>';
                            echo '<input type="button" name="rac_new_email_template" id="rac_new_email_template" class="button" value="New Template">';
                            echo '</a>';
                            echo '&nbsp<span><select id="rac_pagination">';
                            for ($k = 1; $k <= 20; $k++) {

                                if ($k == 10) {
                                    echo '<option value="' . $k . '" selected="selected">' . $k . '</option>';
                                } else {
                                    echo '<option value="' . $k . '">' . $k . '</option>';
                                }
                            }
                            echo '</select></span>';
                            echo '&nbsp<label>Search</label><input type="text" name="rac_temp_search" id="rac_temp_search">';

                            echo '<table class="rac_email_template_table table" data-page-size="10" data-filter="#rac_temp_search" data-filter-minimum="1">
	<thead>
		<tr>
			<th data-type="numeric">' . __('ID', 'recoverabandoncart') . '</th>
			<th>' . __('Template Name', 'recoverabandoncart') . '</th>
			<th>' . __('From Name', 'recoverabandoncart') . '</th>
                        <th>' . __('From Email', 'recoverabandoncart') . '</th>
                        <th>' . __('Subject', 'recoverabandoncart') . '</th>
                        <th data-hide="phone">' . __('Message', 'recoverabandoncart') . '</th>
                        <th>' . __('Status', 'recoverabandoncart') . '</th>
                        <th>' . __('Emails Sent', 'recoverabandoncart') . '</th>
                        <th>' . __('Carts Recovered', 'recoverabandoncart') . '</th>
                        <th>' . __('Email Preview', 'recoverabandoncart') . '</th>
                        <th>' . __('Duplicate', 'recoverabandoncart') . '</th>
		</tr>
	</thead>';
                            foreach ($templates as $each_template) {
                                echo '<tr><td data-value=' . $each_template->id . ' >';
                                echo $each_template->id;
                                $edit_template_url = esc_url_raw(add_query_arg(array('page' => 'fprac_slug', 'tab' => 'fpracemail', 'rac_edit_email' => $each_template->id), $admin_url));
                                $email_template_url = esc_url_raw(add_query_arg(array('page' => 'fprac_slug', 'tab' => 'fpracemail', 'rac_edit_email' => $each_template->id, 'preview' => 'true'), $admin_url));
                                echo '&nbsp;<span><a href="' . $edit_template_url . '">' . __('Edit', 'recoverabandoncart') . ' </a></span>&nbsp; <span><a href="" class="rac_delete" data-id="' . $each_template->id . '">' . __('Delete', 'recoverabandoncart') . '</a></span>';
                                echo '</td><td>';
                                echo $each_template->template_name;
                                echo '</td><td>';
                                if ("local" == $each_template->sender_opt) {
                                    echo $each_template->from_name;
                                    echo '</td><td>';
                                    echo $each_template->from_email;
                                } else {
                                    echo get_option('woocommerce_email_from_name');
                                    echo '</td><td>';
                                    echo get_option('woocommerce_email_from_address');
                                }
                                echo '</td><td>';
                                echo $each_template->subject;
                                echo '</td><td>';
                                $message = strip_tags($each_template->message);
                                if (strlen($message) > 80) {
                                    echo substr($message, 0, 80);
                                    echo '.....';
                                } else {
                                    echo $message;
                                }
                                echo '</td>';
                                echo '<td>';
                                $mail_id = $each_template->id;
                                $status = $each_template->status;
                                if ($status == 'ACTIVE') {
                                    echo ' <a href="#" class="button rac_mail_active" data-racmailid="' . $mail_id . '" data-currentstate="ACTIVE">Deactivate</a>';
                                } else {
                                    echo ' <a href="#" class="button rac_mail_active" data-racmailid="' . $mail_id . '" data-currentstate="NOTACTIVE">Activate</a>';
                                }
                                echo '</td>';
                                echo '<td>';
                                $emails_sent = get_option('email_count_of_' . $each_template->id);
                                if ($emails_sent > 0) {
                                    echo $emails_sent;
                                } else {
                                    echo 0;
                                }
                                echo '</td>';

                                echo '<td>';
                                $carts_recovered = get_option('rac_recovered_count_of_' . $each_template->id);
                                if ($carts_recovered > 0) {
                                    echo $carts_recovered;
                                } else {
                                    echo 0;
                                }
                                echo '</td>';
                                echo '<td>';
                                echo ' <a href="' . $email_template_url . ' "target=_blank"> View </a>';
                                echo '</td>';

                                echo '<td>';
                                echo '<input type="button" name="rac_copy_email_template" data-value="' . $each_template->id . '" id="rac_copy_email_template' . $each_template->id . '" class="button rac_copy_email_template" value="Duplicate">';
                                echo '</td></tr>';
                            }
                            echo '</tbody>
            <tfoot>
		<tr>
			<td colspan="11">
				<div class="pagination pagination-centered hide-if-no-paging"></div>
			</td>
		</tr>
	</tfoot></table>';
                        }
                        ?>
                        <script type="text/javascript">
                            jQuery(document).ready(function () {
                                jQuery('.rac_copy_email_template').click(function () {
                                    var row_id = (jQuery(this).attr("data-value"));
                                    var dataparam = ({
                                        action: 'copy_this_template',
                                        row_id: row_id,
                                    });
                                    jQuery.post("<?php echo admin_url('admin-ajax.php'); ?>", dataparam, function (response) {
                                        console.log(response);
                                        window.setTimeout(function () {
                                            location.reload();
                                        }, 1000);
                                    });
                                });
                            });</script>
                        <?php
                        break;
                    case "fpracupdate":
                        echo '<table class="form-table"><tr>
                            <th>Add WC Order which are <td><p><input type="checkbox" name="order_status[]" value="wc-on-hold">on-hold</p>
                            <p><input type="checkbox" name="order_status[]" value="wc-pending">Pending</p>
                            <p><input type="checkbox" name="order_status[]" value="wc-failed" checked>Failed</p>
                            <p><input type="checkbox" name="order_status[]" value="wc-cancelled">Cancelled</p></td>
                            </tr><tr>
                            <th>With</th><td><select id="order_time">
                            <option value="all">All time</option>
                            <option value="specific">Specific</option>
                            </td>
                            </tr>
                            <tr style="display: none" id="specific_row">
                            <th>Specific Time</th>
                            <td>From <input type="text" name="from_date" id="from_time" class="rac_date"> To <input type="text" id="to_time" name="to_date" class="rac_date"></td>
                            </tr>
                            <tr>
                          <td><input type="button" class="button button-primary" name="update_order" id="update_order" value="Check for Abandoned Cart"></td>
                          <td><img style="width: 30px;height: 30px;display: none;" class="perloader_image" src="' . WP_PLUGIN_URL . '/rac/images/update.gif"/><p id="update_response"></p></td>
                            </tr>
                            </table>';
                        //ajax call
                        echo '<script>jQuery(document).ready(function(){
                              jQuery("#specific_row").css("display","none");
                            jQuery("#order_time").change(function(){
                            if(jQuery(this).val() == "specific"){
                            jQuery("#specific_row").css("display","table-row");
                            }else{
                            jQuery("#specific_row").css("display","none");
                            }
                            });



                            });</script>';
                        break;
                    case "fpracmailog":
                        RecoverAbandonCart::fp_rac_mail_logs_display();
                        break;
                    case "fpraccoupon":
                        do_action('woocommerce_fprac_settings_tabs_' . $current_tab); // @deprecated hook
                        do_action('woocommerce_fprac_settings_' . $current_tab);
                        ?>
                        <p>Use {rac.coupon} to include a coupon code in mail</p>
                        <span class="submit" style="margin-left: 25px;">
                            <?php if (!isset($GLOBALS['hide_save_button'])) : ?>
                                <input name="save" class="button-primary" style="margin-top:15px;" type="submit" value="<?php _e('Save', 'recoverabandoncart'); ?>" />
                            <?php endif; ?>
                            <input type="hidden" name="subtab" id="last_tab" />
                            <?php wp_nonce_field('woocommerce-settings'); ?>
                        </span>

                        <?php
                        break;
                    case "fpracdebug":
                        do_action('woocommerce_fprac_settings_tabs_' . $current_tab); // @deprecated hook
                        do_action('woocommerce_fprac_settings_' . $current_tab);
                        ?>
                        <h3>Test Mail</h3>
                        <table class="form-table">
                            <tr>
                                <th>Test Mail Format</th>
                                <td>
                                    <select name="rac_test_mail_format" id="rac_test_mail_format">
                                        <option value="1">Plain Text</option>
                                        <option value="2">HTML</option>
                                    </select>
                                </td>
                            </tr>

                            <tr>
                                <th>Send Test Email to </th>
                                <td><input type="text" id="testemailto" name="testemailto" value="">
                                    <input type="button" id="senttestmail" class="button button-primary" value="Send Test Email"></td>
                            </tr>
                            <tr>
                                <td colspan="2"><p id="test_mail_result" style="display:none;"></p></td>
                            </tr>
                        </table>
                        <script type="text/javascript">
                            jQuery(document).ready(function () {
                                jQuery("#senttestmail").click(function () {
                                    var data = {
                                        action: "rac_send_test_mail",
                                        rac_test_mail_to: jQuery("#testemailto").val(),
                                        rac_plain_or_html: jQuery('#rac_test_mail_format').val(),
                                    };
                                    console.log(data);
                                    var cur_button = jQuery(this);
                                    jQuery(this).prop("disabled", true);
                                    jQuery.ajax({
                                        type: "POST",
                                        url: ajaxurl,
                                        data: data
                                    }).done(function (response) {
                                        jQuery("#test_mail_result").css("display", "block");
                                        if (response == "sent") {
                                            jQuery("#test_mail_result").html("Mail has been Sent, but this doesn't mean mail will be delivered Successfully. Check Wordpress Codex for More info on Mail.");
                                        } else {
                                            jQuery("#test_mail_result").html("Mail not Sent.");
                                        }
                                        //jQuery("#update_response").text(response);
                                        cur_button.prop("disabled", false);
                                    });
                                });
                            });</script>
                        <h3>Cron Schedules</h3>
                        <table class="widefat">
                            <thead>
                                <tr>
                                    <th>Mail Job hook</th>
                                    <th>Next Mail job</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        rac_cron_job
                                    </td>
                                    <td>
                                        <?php
                                        if (wp_next_scheduled('rac_cron_job')) {
                                            date_default_timezone_set('UTC');
                                            echo "UTC time = " . date(get_option('date_format'), wp_next_scheduled('rac_cron_job')) . ' / ' . date(get_option('time_format'), wp_next_scheduled('rac_cron_job')) . '</br>';
                                            @date_default_timezone_set(get_option('timezone_string'));
                                            echo "Local time = " . date(get_option('date_format'), wp_next_scheduled('rac_cron_job')) . ' / ' . date(get_option('time_format'), wp_next_scheduled('rac_cron_job')) . '</br>';
                                        } else {
                                            echo "Cron is not set";
                                        }
                                        ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <h1><?php _e('Troubleshoot for Unsubscription Link in Footer', 'recoverabandoncart'); ?></h1>
                        <h3>
                            <?php _e('If Unsubscribe Email Link is not visible footer of email, then kindly consider to use this shortcode {rac.unsubscribe} in text editor of each email templates to make it work.'); ?>
                        </h3>
                        <span class="submit" style="margin-left: 25px;">
                            <?php if (!isset($GLOBALS['hide_save_button'])) : ?>
                                <input name="save" class="button-primary" style="margin-top:15px;" type="submit" value="<?php _e('Save', 'recoverabandoncart'); ?>" />
                            <?php endif; ?>
                            <input type="hidden" name="subtab" id="last_tab" />
                            <?php wp_nonce_field('woocommerce-settings'); ?>
                        </span>

                        <?php
                        break;
                    case "fpracreport" :
                        RecoverAbandonCart::fp_rac_reports();
                        break;

                    case "fpracrecoveredorderids":
                        FPRacCounter::add_list_table();
                        break;
                    case "fpracshortocde":
                        RecoverAbandonCart::fp_rac_shortcodes_info();
                        break;
                    case "fpracsupport";
                        woocommerce_admin_fields(RecoverAbandonCart::fp_rac_support_admin_fields());
                        break;
                    default :
                        do_action('woocommerce_fprac_settings_tabs_' . $current_tab); // @deprecated hook
                        do_action('woocommerce_fprac_settings_' . $current_tab);
                        $admin_url = admin_url('admin.php');
                        $reset_url = esc_url_raw(add_query_arg(array('page' => 'fprac_slug', 'rac_reset' => 'reset'), $admin_url));
                        echo '<input class="button-secondary" id="rac_reset" type="button" name="rac_reset" value="Reset">';
                        echo '<script type="text/javascript">
                                       jQuery(document).ready(function(){
                                       jQuery("#rac_reset").click(function(){
                                       window.location.replace("' . $reset_url . '");
                                       });
                                       jQuery("#rac_admin_cart_recovered_noti").change(function(){
                                       if(jQuery(this).is(":checked")){
                                       jQuery(".admin_notification").parent().parent().show();
                                       jQuery(".admin_notifi_sender_opt").closest("tr").show();
                                       }else{
                                        jQuery(".admin_notification").parent().parent().hide();
                                        jQuery(".admin_notifi_sender_opt").closest("tr").hide();
                                       }
                                       //sender option should be refereshed as it is inside this
                                       var sender_opt = jQuery("[name=\'rac_recovered_sender_opt\']:checked").val();
                                       console.log(sender_opt);
                                       if(sender_opt == "woo"){
                                       jQuery(".local_senders").parent().parent().hide();
                                       }else{
                                        jQuery(".local_senders").parent().parent().show();
                                       }
                                       });
                                       jQuery("[name=\'rac_recovered_sender_opt\']").change(function(){
                                       var sender_opt = jQuery("[name=\'rac_recovered_sender_opt\']:checked").val();
                                       if(sender_opt == "woo"){
                                       jQuery(".local_senders").parent().parent().hide();
                                       }else{
                                        jQuery(".local_senders").parent().parent().show();
                                       }
                                       });
                                       //on ready event
                                       var sender_opt = jQuery("[name=\'rac_recovered_sender_opt\']:checked").val();
                                       console.log(sender_opt);
                                       if(sender_opt == "woo"){
                                       jQuery(".local_senders").parent().parent().hide();
                                       }else{
                                        jQuery(".local_senders").parent().parent().show();
                                       }
                                       //enable notification event
                                       if(jQuery("#rac_admin_cart_recovered_noti").is(":checked")){
                                       jQuery(".admin_notification").parent().parent().show();
                                       jQuery(".admin_notifi_sender_opt").closest("tr").show();
                                       }else{
                                        jQuery(".admin_notification").parent().parent().hide();
                                        jQuery(".admin_notifi_sender_opt").closest("tr").hide();
                                       }
                                       });</script>';
                        ?>
                        <span class="submit" style="margin-left: 25px;">
                            <?php if (!isset($GLOBALS['hide_save_button'])) : ?>
                                <input name="save" class="button-primary" type="submit" value="<?php _e('Save', 'recoverabandoncart'); ?>" />
                            <?php endif; ?>
                            <input type="hidden" name="subtab" id="last_tab" />
                            <?php wp_nonce_field('woocommerce-settings'); ?>
                        </span>
                        <?php
                        break;
                }
                ?>


            </form>
        </div>

        <script type="text/javascript">

            jQuery(document).ready(function ()
            {
                if (jQuery('#rac_remove_carts').is(":checked")) {

                    jQuery('#rac_remove_new').parent().parent().parent().parent().css("display", "table-row");
                    jQuery('#rac_remove_abandon').parent().parent().parent().parent().css("display", "table-row");
                }
                else {
                    jQuery('#rac_remove_new').parent().parent().parent().parent().css("display", "none");
                    jQuery('#rac_remove_abandon').parent().parent().parent().parent().css("display", "none");
                }

                jQuery('#rac_remove_carts').change(function ()
                {

                    if (this.checked) {


                        jQuery('#rac_remove_new').parent().parent().parent().parent().css("display", "table-row");
                        jQuery('#rac_remove_abandon').parent().parent().parent().parent().css("display", "table-row");
                    }
                    else {
                        jQuery('#rac_remove_new').parent().parent().parent().parent().css("display", "none");
                        jQuery('#rac_remove_abandon').parent().parent().parent().parent().css("display", "none");
                    }

                });
            });</script>


        <?php
    }

    public static function fp_rac_menu_options_general() {
        global $wp_roles;
        foreach ($wp_roles->role_names as $key => $value) {
            $userrole[] = $key;
            $username[] = $value;
        }

        $user_role = array_combine((array) $userrole, (array) $username);
        $admin_mail = get_option('admin_email');
        global $woocommerce, $product, $wpdb;
        if (function_exists('wc_get_order_statuses')) {
            $order_list_keys = array_keys(wc_get_order_statuses());
            $order_list_values = array_values(wc_get_order_statuses());
            $orderlist_replace = str_replace('wc-', '', $order_list_keys);
            $orderlist_combine = array_combine($orderlist_replace, $order_list_values);
        } else {
            $order_status = (array) get_terms('shop_order_status', array('hide_empty' => 0, 'orderby' => 'id'));
            foreach ($order_status as $value) {
                $status_name[] = $value->name;
                $status_slug[] = $value->slug;
            }
            $orderlist_combine = array_combine($status_slug, $status_name);
        }
        return apply_filters('woocommerce_fpwcctsingle_settings', array(
            array(
                'name' => __('Time Settings', 'recoverabandoncart'),
                'type' => 'title',
                'desc' => '',
                'id' => 'rac_time_settings',
                'clone_id' => '',
            ),
            array(
                'name' => __('Abandon Cart Time Type for Members', 'recoverabandoncart'),
                'desc' => __('Please Select whether the time should be in Minutes/Hours/Days for Members', 'recoverabandoncart'),
                'tip' => '',
                'id' => 'rac_abandon_cart_time_type',
                'css' => 'min-width:150px;',
                'type' => 'select',
                'desc_tip' => true,
                'options' => array('minutes' => 'Minutes', 'hours' => 'Hours', 'days' => 'Days'),
                'std' => 'hours',
                'default' => 'hours',
                'clone_id' => 'rac_abandon_cart_time_type',
            ),
            array(
                'name' => __('Abandon Cart Time for Members', 'recoverabandoncart'),
                'desc' => __('Please Enter time after which the cart should be considered as abandon for Members', 'recoverabandoncart'),
                'tip' => '',
                'id' => 'rac_abandon_cart_time',
                'css' => 'min-width:150px;',
                'type' => 'text',
                'desc_tip' => true,
                'std' => '1',
                'default' => '1',
                'clone_id' => 'rac_abandon_cart_time',
            ),
            array(
                'name' => __('Abandon Cart Time Type for Guest', 'recoverabandoncart'),
                'desc' => __('Please Select whether the time should be in Minutes/Hours/Days for Guest', 'recoverabandoncart'),
                'tip' => '',
                'id' => 'rac_abandon_cart_time_type_guest',
                'css' => 'min-width:150px;',
                'type' => 'select',
                'desc_tip' => true,
                'options' => array('minutes' => 'Minutes', 'hours' => 'Hours', 'days' => 'Days'),
                'std' => 'hours',
                'default' => 'hours',
                'clone_id' => 'rac_abandon_cart_time_type_guest',
            ),
            array(
                'name' => __('Abandon Cart Time for Guest', 'woocommercecustomtext'),
                'desc' => __('Please Enter time after which the cart should be considered as abandon for guest', 'woocommercecustomtext'),
                'tip' => '',
                'id' => 'rac_abandon_cart_time_guest',
                'css' => 'min-width:150px;',
                'type' => 'text',
                'desc_tip' => true,
                'std' => '1',
                'default' => '1',
                'clone_id' => 'rac_abandon_cart_time_guest',
            ),
            array('type' => 'sectionend', 'id' => 'rac_time_settings'), //Time Settings END
            array(
                'name' => __('Mail Cron Settings', 'recoverabandoncart'),
                'type' => 'title',
                'desc' => '',
                'id' => 'rac_cron_settings',
                'clone_id' => '',
            ),
            array(
                'name' => __('Mail Cron Time Type', 'recoverabandoncart'),
                'desc' => __('Please Select whether the time should be in Minutes/Hours/Days', 'recoverabandoncart'),
                'tip' => '',
                'id' => 'rac_abandon_cart_cron_type',
                'css' => 'min-width:150px;',
                'type' => 'select',
                'desc_tip' => true,
                'options' => array('minutes' => 'Minutes', 'hours' => 'Hours', 'days' => 'Days'),
                'std' => 'hours',
                'default' => 'hours',
                'clone_id' => 'rac_abandon_cart_cron_type',
            ),
            array(
                'name' => __('Mail Cron Time', 'recoverabandoncart'),
                'desc' => __('Please Enter time after which Email cron job should run', 'recoverabandoncart'),
                'tip' => '',
                'id' => 'rac_abandon_cron_time',
                'css' => 'min-width:150px;',
                'type' => 'text',
                'desc_tip' => true,
                'std' => '12',
                'default' => '12',
                'clone_id' => 'rac_abandon_cron_time',
            ),
            array('type' => 'sectionend', 'id' => 'rac_cron_settings'), //Cron Settings END
            array(
                'name' => __('Recover Status Settings', 'recoverabandoncart'),
                'type' => 'title',
                'desc' => '',
                'id' => 'rac_mailcontrol_settings',
                'clone_id' => '',
            ),
            array(
                'name' => __('When User\'s place the order change all the New/Abandon Cart List to Recovered Status', 'recoverabandoncart'),
                'desc' => __('Recover Cart List based on Order Status', 'recoverabandoncart'),
                'tip' => '',
                'id' => 'rac_cartlist_new_abandon_recover',
                'class' => 'rac_cartlist_new_abandon_recover',
                'css' => '',
                'type' => 'checkbox',
                'std' => 'yes',
                'default' => 'yes',
                'newids' => 'rac_cartlist_new_abandon_recover',
                'clone_id' => 'rac_cartlist_new_abandon_recover',
                'desc_tip' => true,
            ),
            array(
                'name' => __('Allow Manual Orders to Recover Cart List', 'recoverabandoncart'),
                'desc' => __('Enable this Option will help to recover cart list based on manually created orders.', 'recoverabandoncart'),
                'tip' => '',
                'id' => 'rac_cartlist_new_abandon_recover_by_manual_order',
                'class' => 'rac_cart_depends_parent_new_abandon_option',
                'css' => '',
                'type' => 'checkbox',
                'std' => 'yes',
                'default' => 'yes',
                'newids' => 'rac_cartlist_new_abandon_recover_by_manual_order',
                'clone_id' => 'rac_cartlist_new_abandon_recover_by_manual_order',
                'desc_tip' => true,
            ),
            array(
                'name' => __('New Status to Recovered Status', 'recoverabandoncart'),
                'desc' => __('Based on Order Status change New Status to Recovered Status', 'recoverabandoncart'),
                'tip' => '',
                'id' => 'rac_cartlist_change_from_new_to_recover',
                'class' => 'rac_cart_depends_parent_new_abandon_option',
                'css' => '',
                'type' => 'checkbox',
                'std' => 'no',
                'default' => 'no',
                'newids' => 'rac_cartlist_change_from_new_to_recover',
                'clone_id' => 'rac_cartlist_change_from_new_to_recover',
                'desc_tip' => true,
            ),
            array(
                'name' => __('Abandon Status to Recovered Status', 'recoverabandoncart'),
                'desc' => __('Based on Order Status change Abandon Status to Recovered Status', 'recoverabandoncart'),
                'tip' => '',
                'id' => 'rac_cartlist_change_from_abandon_to_recover',
                'class' => 'rac_cart_depends_parent_new_abandon_option',
                'css' => '',
                'type' => 'checkbox',
                'std' => 'yes',
                'default' => 'yes',
                'newids' => 'rac_cartlist_change_from_abandon_to_recover',
                'clone_id' => 'rac_cartlist_change_from_abandon_to_recover',
                'desc_tip' => true,
            ),
            array(
                'name' => __('Cart List become Recovered when Order Status', 'recoverabandoncart'),
                'desc' => __('Based on these Order Status Cart List become Recovered', 'recoverabandoncart'),
                'tip' => '',
                'id' => 'rac_mailcartlist_change',
                'class' => 'rac_mailcartlist_change',
                'css' => 'min-width:153px',
                'type' => 'multiselect',
                'options' => $orderlist_combine,
                'std' => array('completed', 'processing'),
                'default' => array('completed', 'processing'),
                'newids' => 'rac_mailcartlist_change',
                'desc_tip' => true,
            ),
            array('type' => 'sectionend', 'id' => 'rac_mailcontrol_settings'), // Settings END
            array(
                'name' => __('Notification Settings', 'recoverabandoncart'),
                'type' => 'title',
                'desc' => '',
                'id' => 'rac_notification_settings',
                'clone_id' => '',
            ),
            array(
                'name' => __('Enable Email Notification for Admin when Cart is Recovered', 'recoverabandoncart'),
                'desc' => __(''),
                'id' => 'rac_admin_cart_recovered_noti',
                'std' => 'no',
                'default' => 'no',
                'type' => 'checkbox',
                'newids' => 'rac_admin_cart_recovered_noti',
            ),
            array(
                'name' => __('Admin Email ID', 'recoverabandoncart'),
                'desc' => __(''),
                'id' => 'rac_admin_email',
                'std' => $admin_mail,
                'default' => $admin_mail,
                'type' => 'text',
                'newids' => 'rac_admin_email',
                'class' => 'admin_notification'
            ),
            array(
                'name' => __('Notification Sender Option', 'recoverabandoncart'),
                'desc' => __(''),
                'id' => 'rac_recovered_sender_opt',
                'std' => "woo",
                'default' => "woo",
                'type' => 'radio',
                'newids' => 'rac_recovered_sender_opt',
                'class' => 'admin_sender_opt',
                'options' => array('woo' => 'WooCommerce', 'local' => 'Local (HTML Email will be sent)'),
                'class' => 'admin_notifi_sender_opt'
            ),
            array(
                'name' => __('Notification From Name', 'recoverabandoncart'),
                'desc' => __(''),
                'id' => 'rac_recovered_from_name',
                'std' => "",
                'default' => "",
                'type' => 'text',
                'newids' => 'rac_recovered_from_name',
                'class' => 'local_senders admin_notification'
            ),
            array(
                'name' => __('Notification From Email', 'recoverabandoncart'),
                'desc' => __(''),
                'id' => 'rac_recovered_from_email',
                'std' => "",
                'default' => "",
                'type' => 'text',
                'newids' => 'rac_recovered_from_email',
                'class' => 'local_senders admin_notification'
            ),
            array(
                'name' => __('Notification Email Subject', 'recoverabandoncart'),
                'desc' => __(''),
                'id' => 'rac_recovered_email_subject',
                'std' => "A cart has been Recovered",
                'default' => "A cart has been Recovered",
                'type' => 'text',
                'newids' => 'rac_recovered_email_subject',
                'class' => 'admin_notification'
            ),
            array(
                'name' => __('Notification Email Message', 'recoverabandoncart'),
                'desc' => __(''),
                'css' => 'min-height:250px;min-width:400px;',
                'id' => 'rac_recovered_email_message',
                'std' => "A cart has been Recovered. Here is the order ID {rac.recovered_order_id} for Reference and Line Items is here {rac.order_line_items}.",
                'default' => "A cart has been Recovered. Here is the order ID {rac.recovered_order_id} for Reference and Line Items is here {rac.order_line_items}.",
                'type' => 'textarea',
                'newids' => 'rac_recovered_email_message',
                'class' => 'admin_notification'
            ),
            array('type' => 'sectionend', 'id' => 'rac_notification_settings'), //Notification Settings END
            array(
                'name' => __('Carts List Settings', 'recoverabandoncart'),
                'type' => 'title',
                'desc' => '',
                'id' => 'rac_cartlist_settings',
                'clone_id' => '',
            ),
            array(
                'name' => __('Remove NEW and ABANDON Carts Previously by same Users', 'recoverabandoncart'),
                'desc' => __('Enabling this option will remove New and Abandon Carts by same Users', 'recoverabandoncart'),
                'type' => 'checkbox',
                'default' => 'yes',
                'std' => 'yes',
                'id' => 'rac_remove_carts',
                'clone_id' => 'rac_remove_carts',
            ),
            array(
                'name' => __('Remove Carts with "NEW" Status', 'recoverabandoncart'),
                'desc' => __('Enabling this option will remove New Carts by same Users', 'recoverabandoncart'),
                'type' => 'checkbox',
                'default' => 'yes',
                'std' => 'yes',
                'id' => 'rac_remove_new',
                'clone_id' => 'rac_remove_new',
            ),
            array(
                'name' => __('Remove Carts with "ABANDON" Status', 'recoverabandoncart'),
                'desc' => __('Enabling this option will remove Abandon Carts by same Users', 'recoverabandoncart'),
                'type' => 'checkbox',
                'default' => 'yes',
                'std' => 'yes',
                'id' => 'rac_remove_abandon',
                'clone_id' => 'rac_remove_abandon',
            ),
            array(
                'name' => __('Remove Carts with "ABANDON" Status after x Days', 'recoverabandoncart'),
                'desc' => __('Enabling this option will delete Abandon Carts after Days which You Select', 'recoverabandoncart'),
                'tip' => '',
                'id' => 'enable_remove_abandon_after_x_days',
                'css' => 'min-width:153px',
                'type' => 'select',
                'options' => array('yes' => 'Yes', 'no' => 'No'),
                'std' => 'no',
                'default' => 'no',
                'clone_id' => 'enable_remove_abandon_after_x_days',
                'desc_tip' => true,
            ),
            array(
                'name' => __('Days to Delete Abandon Carts', 'recoverabandoncart'),
                'desc' => __('Enter Days to delete Abandon Carts in Cart List', 'recoverabandoncart'),
                'id' => 'rac_remove_abandon_after_x_days',
                'clone_id' => 'rac_remove_abandon_after_x_days',
                'type' => 'number',
                'std' => '30',
                'default' => '30',
                'desc_tip' => true,
            ),
            array(
                'name' => __('Custom Restrict Settings', 'recoverabandoncart'),
                'desc' => __('Selected User Roles, Names and Email ID To Restrict Entry in Cart List', 'recoverabandoncart'),
                'tip' => '',
                'id' => 'custom_restrict',
                'css' => 'min-width:153px',
                'type' => 'select',
                'options' => array('user_role' => 'User Role', 'name' => 'Name', 'mail_id' => 'Mail ID'),
                'std' => 'user_role',
                'default' => 'user_role',
                'clone_id' => 'custom_restrict',
                'desc_tip' => true,
            ),
            array(
                'name' => __('Select User Role', 'recoverabandoncart'),
                'desc' => __('Enter the First Three Characters of User Role', 'recoverabandoncart'),
                'id' => 'custom_user_role_for_restrict_in_cart_list',
                'css' => 'min-width:150px',
                'type' => 'multiselect',
                'std' => '',
                'options' => $user_role,
                'clone_id' => 'custom_user_role_for_restrict_in_cart_list',
                'desc_tip' => true,
            ),
            array(
                'name' => __('User Name Selected', 'recoverabandoncart'),
                'desc' => __('Enter the First Three Characters of User Name', 'recoverabandoncart'),
                'id' => 'custom_user_name_select_for_restrict_in_cart_list',
                'css' => 'min-width:400px',
                'std' => '',
                'type' => 'rac_exclude_users_list_for_restrict_in_cart_list',
                'clone_id' => 'custom_user_name_select_for_restrict_in_cart_list',
                'desc_tip' => true,
            ),
            array(
                'name' => __('Custom Mail ID Selected', 'recoverabandoncart'),
                'desc' => __('Enter Mail ID per line which will be restricted to includes an entry in Cart List', 'recoverabandoncart'),
                'id' => 'custom_mailid_for_restrict_in_cart_list',
                'clone_id' => 'custom_mailid_for_restrict_in_cart_list',
                'type' => 'textarea',
                'css' => 'min-width:500px;min-height:200px',
                'std' => '',
                'desc_tip' => true,
            ),
            array(
                'name' => __('Insert Entry in Cart List with Abandon Status When Order Status is Cancelled', 'recoverabandoncart'),
                'desc' => __('Enabling this option will Insert Entry in Cart List with Abandon Status When Order Status is Cancelled', 'recoverabandoncart'),
                'type' => 'checkbox',
                'default' => 'yes',
                'std' => 'yes',
                'id' => 'rac_insert_abandon_cart_when_os_cancelled',
                'clone_id' => 'rac_insert_abandon_cart_when_os_cancelled',
            ),
            array(
                'name' => __('Prevent adding "New" cart when order cancelled in cart page', 'recoverabandoncart'),
                'desc' => __('Enabling this option will Prevent adding "New" cart when order cancelled in cart page', 'recoverabandoncart'),
                'type' => 'checkbox',
                'default' => 'no',
                'std' => 'no',
                'id' => 'rac_prevent_entry_in_cartlist_while_order_cancelled_in_cart_page',
                'clone_id' => 'rac_prevent_entry_in_cartlist_while_order_cancelled_in_cart_page',
            ),
            array('type' => 'sectionend', 'id' => 'rac_cartlist_settings'), //Carts List Settings END
            array(
                'name' => __('Guest Cart Settings', 'recoverabandoncart'),
                'type' => 'title',
                'desc' => '',
                'id' => 'rac_guestcart_settings',
                'clone_id' => '',
            ),
            array(
                'name' => __('Remove Guest Cart when the Order Status Changes to Pending', 'recoverabandoncart'),
                'desc' => __('Guest Cart Captured on place order will be in cart list, it will be removed when order become Pending'),
                'id' => 'rac_guest_abadon_type_pending',
                'std' => 'no',
                'default' => 'no',
                'type' => 'checkbox',
                'newids' => 'rac_guest_abadon_type_pending',
            ),
            array(
                'name' => __('Remove Guest Cart when the Order Status Changes to Failed', 'recoverabandoncart'),
                'desc' => __('Guest Cart Captured on place order will be in cart list, it will be removed when order become Failed'),
                'id' => 'rac_guest_abadon_type_failed',
                'std' => 'no',
                'default' => 'no',
                'type' => 'checkbox',
                'newids' => 'rac_guest_abadon_type_failed',
            ),
            array(
                'name' => __('Remove Guest Cart when the Order Status Changes to On-Hold', 'recoverabandoncart'),
                'desc' => __('Guest Cart Captured on place order will be in cart list, it will be removed when order become On-Hold'),
                'id' => 'rac_guest_abadon_type_on-hold',
                'std' => 'no',
                'default' => 'no',
                'type' => 'checkbox',
                'newids' => 'rac_guest_abadon_type_on-hold',
            ),
            array(
                'name' => __('Remove Guest Cart when the Order Status Changes to Processing', 'recoverabandoncart'),
                'desc' => __('Guest Cart Captured on place order will be in cart list, it will be removed when order become Processing'),
                'id' => 'rac_guest_abadon_type_processing',
                'std' => 'yes',
                'default' => 'yes',
                'type' => 'checkbox',
                'newids' => 'rac_guest_abadon_type_processing',
            ),
            array(
                'name' => __('Remove Guest Cart when the Order Status Changes to Completed', 'recoverabandoncart'),
                'desc' => __('Guest Cart Captured on place order will be in cart list, it will be removed when order become Completed'),
                'id' => 'rac_guest_abadon_type_completed',
                'std' => 'yes',
                'default' => 'yes',
                'type' => 'checkbox',
                'newids' => 'rac_guest_abadon_type_completed',
            ),
            array(
                'name' => __('Remove Guest Cart when the Order Status Changes to Refunded', 'recoverabandoncart'),
                'desc' => __('Guest Cart Captured on place order will be in cart list, it will be removed when order become Refunded'),
                'id' => 'rac_guest_abadon_type_refunded',
                'std' => 'no',
                'default' => 'no',
                'type' => 'checkbox',
                'newids' => 'rac_guest_abadon_type_refunded',
            ),
            array(
                'name' => __('Remove Guest Cart when the Order Status Changes to Cancelled', 'recoverabandoncart'),
                'desc' => __('Guest Cart Captured on place order will be in cart list, it will be removed when order become Cancelled'),
                'id' => 'rac_guest_abadon_type_cancelled',
                'std' => 'no',
                'default' => 'no',
                'type' => 'checkbox',
                'newids' => 'rac_guest_abadon_type_cancelled',
            ),
            array('type' => 'sectionend', 'id' => 'rac_guestcart_settings'), //Cart Abadoned Guest Settings END
            array(
                'name' => __('My Account Settings', 'recoverabandoncart'),
                'type' => 'title',
                'id' => '_rac_myaccount_settings',
            ),
            array(
                'name' => __('Show Unsubscription Option in My Account Page', 'recoverabandoncart'),
                'desc' => __('Turn On to make it visible in My Account Page', 'recoverabandoncart'),
                'id' => 'rac_unsub_myaccount_option',
                'std' => 'no',
                'default' => 'no',
                'type' => 'checkbox',
                'clone_id' => 'rac_unsub_myaccount_option',
                'newids' => 'rac_unsub_myaccount_option',
            ),
            array(
                'name' => __('Customize Unsubscription Heading in My Account Page', 'recoverabandoncart'),
                'desc' => __('Customize the heading appeared in My Account Page', 'recoverabandoncart'),
                'id' => 'rac_unsub_myaccount_heading',
                'std' => 'Unsubscription Settings',
                'default' => 'Unsubscription Settings',
                'type' => 'text',
                'clone_id' => 'rac_unsub_myaccount_heading',
                'newids' => 'rac_unsub_myaccount_heading',
            ),
            array(
                'name' => __('Customize Unsubscription Text in My Account Page', 'recoverabandoncart'),
                'desc' => __('Customize the Message appeared in My Account Page for Subscription', 'recoverabandoncart'),
                'id' => 'rac_unsub_myaccount_text',
                'std' => 'Unsubscribe Here To Receive Email from Abandon Cart',
                'default' => 'Unsubscribe Here to Receiver Email from Abandon Cart',
                'type' => 'textarea',
                'clone_id' => 'rac_unsub_myaccount_text',
                'newids' => 'rac_unsub_myaccount_text',
            ),
            array('type' => 'sectionend', 'id' => '_rac_myaccount_settings'),
        ));
    }

    public static function fprac_default_settings() {
        global $woocommerce;
        foreach (RecoverAbandonCart::fp_rac_menu_options_general() as $setting)
            if (isset($setting['id']) && isset($setting['std'])) {
                add_option($setting['id'], $setting['std']);
            }
        foreach (RecoverAbandonCart::fp_rac_menu_options_email() as $setting)
            if (isset($setting['id']) && isset($setting['std'])) {
                add_option($setting['id'], $setting['std']);
            }
        foreach (RecoverAbandonCart::fp_rac_menu_options_troubleshoot() as $setting)
            if (isset($setting['id']) && isset($setting['std'])) {
                add_option($setting['id'], $setting['std']);
            }
        foreach (RecoverAbandonCart::fp_rac_menu_options_coupon_gen() as $setting)
            if (isset($setting['id']) && isset($setting['std'])) {
                add_option($setting['id'], $setting['std']);
            }
    }

    public static function fp_rac_menu_options_email() {
        global $woocommerce;

        global $wp_roles;
        foreach ($wp_roles->role_names as $key => $value) {
            $userrole[] = $key;
            $username[] = $value;
        }

        $user_role = array_combine((array) $userrole, (array) $username);

        return apply_filters('woocommerce_fpracemail_settings', array(
            array(
                'name' => __('Email Settings', 'recoverabandoncart'),
                'type' => 'title',
                'desc' => '',
                'id' => 'rac_email_gen_settings',
                'clone_id' => '',
            ),
//            array(
//                'name' => __('Use Plain RTF Email', 'recoverabandoncart'),
//                'desc' => __('Enabling this option will send mail in Plain RTF', 'recoverabandoncart'),
//                'tip' => '',
//                'id' => 'rac_email_use_temp_plain',
//                'css' => '',
//                'type' => 'checkbox',
//                'desc_tip' => true,
//                'std' => 'no',
//                'default' => 'no',
//                'clone_id' => 'rac_email_use_temp_plain',
//            ),
            array(
                'name' => __('Send Email to Members', 'recoverabandoncart'),
                'desc' => __('Enabling this option will send an Email to Members only', 'recoverabandoncart'),
                'type' => 'checkbox',
                'default' => 'yes',
                'std' => 'yes',
                'id' => 'rac_email_use_members',
                'clone_id' => 'rac_email_use_members',
            ),
            array(
                'name' => __('Send Email to Guests', 'recoverabandoncart'),
                'desc' => __('Enabling this option will send an Email to Guests only', 'recoverabandoncart'),
                'type' => 'checkbox',
                'default' => 'yes',
                'std' => 'yes',
                'id' => 'rac_email_use_guests',
                'clone_id' => 'rac_email_use_guests',
            ),
            array(
                'name' => __('Custom Exclude Settings', 'recoverabandoncart'),
                'desc' => __('Select User Roles, Names and Email ID To Stop EMail Sending', 'recoverabandoncart'),
                'tip' => '',
                'id' => 'custom_exclude',
                'css' => 'min-width:153px',
                'type' => 'select',
                'options' => array('user_role' => 'User Role', 'name' => 'Name', 'mail_id' => 'Mail ID'),
                'std' => 'user_role',
                'default' => 'user_role',
                'clone_id' => 'custom_exclude',
                'desc_tip' => true,
            ),
            array(
                'name' => __('Select User Role', 'recoverabandoncart'),
                'desc' => __('Enter the First Three Characters of User Role', 'recoverabandoncart'),
                'id' => 'custom_user_role',
                'css' => 'min-width:150px',
                'type' => 'multiselect',
                'std' => '',
                'options' => $user_role,
                'clone_id' => 'custom_user_role',
                'desc_tip' => true,
            ),
            array(
                'name' => __('User Name Selected', 'recoverabandoncart'),
                'desc' => __('Enter the First Three Character of User Name', 'recoverabandoncart'),
                'id' => 'custom_user_name_select',
                'css' => 'min-width:400px',
                'std' => '',
                'type' => 'rac_exclude_users_list',
                'clone_id' => 'custom_user_name_select',
                'desc_tip' => true,
            ),
            array(
                'name' => __('Custom Mail ID Selected', 'recoverabandoncart'),
                'desc' => __('Enter Mail ID per line which will be excluded to receive a mail from Recover Abandon Cart', 'recoverabandoncart'),
                'id' => 'custom_mailid_edit',
                'clone_id' => 'custom_mailid_edit',
                'type' => 'textarea',
                'css' => 'min-width:500px;min-height:200px',
                'std' => '',
                'desc_tip' => true,
            ),
            array('type' => 'sectionend', 'id' => 'rac_email_gen_settings'), //Email Settings END
            array(
                'name' => __('Email Template Cart Link Settings', 'recoverabandoncart'),
                'type' => 'title',
                'desc' => '',
                'id' => 'rac_cart_link_customization',
            ),
            array(
                'name' => __('Cart Link', 'recoverabandoncart'),
                'desc' => __('Customize the Cart Link in Email Template', 'recoverabandoncart'),
                'tip' => '',
                'id' => 'rac_cart_link_options',
                'css' => '',
                'type' => 'select',
                'desc_tip' => true,
                'std' => '1',
                'default' => '1',
                'options' => array(
                    '1' => __('Hyperlink', 'recoverabandoncart'),
                    '2' => __('URL', 'recoverabandoncart'),
                    '3' => __('Button', 'recoverabandoncart'),
                ),
                'clone_id' => 'rac_cart_link_options',
            ),
            array(
                'name' => __('Button Background Color', 'recoverabandoncart'),
                'desc' => __('Customize Button Background Color in Email', 'recoverabandoncart'),
                'tip' => '',
                'id' => 'rac_cart_button_bg_color',
                'class' => 'color racbutton',
                'css' => '',
                'type' => 'text',
                'desc_tip' => true,
                'std' => '000091',
                'default' => '000091',
                'clone_id' => 'rac_cart_button_bg_color',
            ),
            array(
                'name' => __('Button Link Color', 'recoverabandoncart'),
                'desc' => __('Customize Button Link Color', 'recoverabandoncart'),
                'tip' => '',
                'id' => 'rac_cart_button_link_color',
                'class' => 'color racbutton',
                'css' => '',
                'type' => 'text',
                'desc_tip' => true,
                'std' => 'ffffff',
                'default' => 'ffffff',
                'clone_id' => 'rac_cart_button_link_color',
            ),
            array(
                'name' => __('Link Color', 'recoverabandoncart'),
                'desc' => __('Customize Link Color in Email Template', 'recoverabandoncart'),
                'tip' => '',
                'id' => 'rac_email_link_color',
                'class' => 'color raclink',
                'css' => '',
                'type' => 'text',
                'desc_tip' => true,
                'std' => '1919FF',
                'default' => '1919FF',
                'clone_id' => 'rac_email_link_color',
            ),
            array(
                'name' => __('Redirect after click cartlink in email', 'recoverabandoncart'),
                'desc' => __('Please Select the page that you want to redirect after clicking the cartlink in an email', 'recoverabandoncart'),
                'tip' => '',
                'id' => 'rac_cartlink_redirect',
                'css' => '',
                'std' => '1',
                'type' => 'radio',
                'options' => array('1' => 'Cart Page', '2' => 'Checkout Page'),
                'newids' => 'rac_cartlink_redirect',
                'desc_tip' => true,
            ),
            array('type' => 'sectionend', 'id' => 'rac_cart_link_customization'),
            array(
                'name' => __('Unsubscribe Settings', 'recoverabandoncart'),
                'type' => 'title',
                'desc' => '',
                'id' => 'rac_email_unsubscription',
                'clone_id' => '',
            ),
            array(
                'name' => __('Unsubscription Link in Email', 'recoverabandoncart'),
                'desc' => __('Enable', 'recoverabandoncart'),
                'id' => 'fp_unsubscription_link_in_email',
                'clone_id' => 'fp_unsubscription_link_in_email',
                'type' => 'checkbox',
                'default' => 'no',
                'std' => 'no',
                'desc_tip' => '',
            ),
            array(
                'name' => __("Unsubscription Link Text", 'recoverabandoncart'),
                'desc' => __('Enter Unsubscription Anchor Text in Email Footer', 'recoverabandoncart'),
                'id' => 'fp_unsubscription_footer_link_text',
                'clone_id' => 'fp_unsubscription_footer_link_text',
                'type' => 'text',
                'default' => 'Unsubscribe',
                'std' => 'Unsubscribe',
                'desc_tip' => true,
            ),
            array(
                'name' => __('Unsubscription Message', 'recoverabandoncart'),
                'desc' => __('Enter Unsubscription Message which is visible in Email Footer', 'recoverabandoncart'),
                'id' => 'fp_unsubscription_footer_message',
                'clone_id' => 'fp_unsubscription_footer_message',
                'type' => 'textarea',
                'css' => 'height: 60px; width: 320px',
                'default' => 'You can {rac_unsubscribe} to stop Receiving Abandon Cart Mail from {rac_site}',
                'std' => 'You can {rac_unsubscribe} to stop Receiving Abandon Cart Mail from {rac_site}',
                'desc_tip' => true,
            ),
            array(
                'name' => __('Unsubscription Type', 'recoverabandoncart'),
                'desc' => __('Please Select the Unsubscription Type', 'recoverabandoncart'),
                'tip' => '',
                'id' => 'rac_unsubscription_type',
                'class' => 'rac_unsubscription_type',
                'css' => '',
                'std' => '1',
                'default' => '1',
                'type' => 'radio',
                'options' => array('1' => 'Automatic Unsubscription', '2' => 'Manual Unsubscription'),
                'newids' => 'rac_unsubscription_type',
                'desc_tip' => true,
            ),
            array(
                'name' => __("Redirect Url for Automatic Unsubscription", 'recoverabandoncart'),
                'desc' => __('Enter Redirect Url to redirect when click the Automatic unsubscription link', 'recoverabandoncart'),
                'id' => 'rac_unsubscription_redirect_url',
                'clone_id' => 'rac_unsubscription_redirect_url',
                'type' => 'text',
                'default' => get_permalink(wc_get_page_id('myaccount')),
                'std' => get_permalink(wc_get_page_id('myaccount')),
                'desc_tip' => true,
            ),
            array(
                'name' => __("Redirect Url for Manual Unsubscription", 'recoverabandoncart'),
                'desc' => __('Enter Redirect Url to redirect when click the Manual unsubscription link', 'recoverabandoncart'),
                'id' => 'rac_manual_unsubscription_redirect_url',
                'clone_id' => 'rac_manual_unsubscription_redirect_url',
                'type' => 'text',
                'default' => get_permalink(wc_get_page_id('myaccount')),
                'std' => get_permalink(wc_get_page_id('myaccount')),
                'desc_tip' => true,
            ),
            array(
                'name' => __("Already Unsubscribed Text", 'recoverabandoncart'),
                'desc' => __('Enter Already Unsubscribed Text', 'recoverabandoncart'),
                'id' => 'rac_already_unsubscribed_text',
                'clone_id' => 'rac_already_unsubscribed_text',
                'type' => 'text',
                'default' => 'You have already unsubscribed.',
                'std' => 'You have already unsubscribed.',
                'desc_tip' => true,
            ),
            array(
                'name' => __("Unsubscribed Successfully Text", 'recoverabandoncart'),
                'desc' => __('Enter Unsubscribed Successfully Text', 'recoverabandoncart'),
                'id' => 'rac_unsubscribed_successfully_text',
                'clone_id' => 'rac_unsubscribed_successfully_text',
                'type' => 'text',
                'default' => 'You have successfully unsubscribed from Abandoned cart Emails.',
                'std' => 'You have successfully unsubscribed from Abandoned cart Emails.',
                'desc_tip' => true,
            ),
            array(
                'name' => __("Confirm Unsubscription Text", 'recoverabandoncart'),
                'desc' => __('Enter Confirm Unsubscription Text', 'recoverabandoncart'),
                'id' => 'rac_confirm_unsubscription_text',
                'clone_id' => 'rac_confirm_unsubscription_text',
                'type' => 'text',
                'default' => 'To stop receiving Abandoned Cart Emails, Click the Unsubscribe button below',
                'std' => 'To stop receiving Abandoned Cart Emails, Click the Unsubscribe button below',
                'desc_tip' => true,
            ),
            array(
                'name' => __("Unsubscription Message Text color", 'recoverabandoncart'),
                'desc' => __('Choose Unsubscription Message Text color', 'recoverabandoncart'),
                'id' => 'rac_unsubscription_message_text_color',
                'clone_id' => 'rac_unsubscription_message_text_color',
                'type' => 'text',
                'default' => 'fff',
                'std' => 'fff',
                'class' => 'color',
                'desc_tip' => true,
            ),
            array(
                'name' => __("Background color for Unsubscription Message", 'recoverabandoncart'),
                'desc' => __('Choose Background color for Unsubscription Message', 'recoverabandoncart'),
                'id' => 'rac_unsubscription_message_background_color',
                'clone_id' => 'rac_unsubscription_message_background_color',
                'type' => 'text',
                'default' => 'a46497',
                'std' => 'a46497',
                'class' => 'color',
                'desc_tip' => true,
            ),
            array(
                'name' => __("Unsubscription Email Text color", 'recoverabandoncart'),
                'desc' => __('Choose Unsubscription Email Text color', 'recoverabandoncart'),
                'id' => 'rac_unsubscription_email_text_color',
                'clone_id' => 'rac_unsubscription_email_text_color',
                'type' => 'text',
                'default' => '000000',
                'std' => '000000',
                'class' => 'color',
                'desc_tip' => true,
            ),
            array(
                'name' => __("Confirm Unsubscription Text color", 'recoverabandoncart'),
                'desc' => __('Choose Confirm Unsubscription Text color', 'recoverabandoncart'),
                'id' => 'rac_confirm_unsubscription_text_color',
                'clone_id' => 'rac_confirm_unsubscription_text_color',
                'type' => 'text',
                'default' => 'ff3f12',
                'std' => 'ff3f12',
                'class' => 'color',
                'desc_tip' => true,
            ),
            array('type' => 'sectionend', 'id' => 'rac_email_unsubscription'),
            array(
                'name' => __('Customize Caption and Visibility for Product Info in Email Template', 'recoverabandoncart'),
                'type' => 'title',
                'desc' => 'Following Customization options works with the shortcode {rac.Productinfo} in Email Template',
                'id' => 'rac_customize_caption_in_product_info',
                'clone_id' => '',
            ),
            array(
                'name' => __('Enable Border for Product Info in Email Template', 'recoverabandoncart'),
                'desc' => __('Enable Border for Product Info in Email Template', 'recoverabandoncart'),
                'id' => 'rac_enable_border_for_productinfo_in_email',
                'clone_id' => 'rac_enable_border_for_productinfo_in_email',
                'type' => 'checkbox',
                'default' => 'yes',
                'std' => 'yes',
                'desc_tip' => '',
            ),
            array(
                'name' => __('Customize Product Name Caption in Email Template', 'recoverabandoncart'),
                'desc' => __('Customize Product Name Caption in Email Template', 'recoverabandoncart'),
                'type' => 'text',
                'default' => 'Product Name',
                'std' => 'Product Name',
                'id' => 'rac_product_info_product_name',
                'clone_id' => 'rac_product_info_product_name',
            ),
            array(
                'name' => __('Customize Product Image Caption in Email Template', 'recoverabandoncart'),
                'desc' => __('Customize Product Image Caption in Email Template', 'recoverabandoncart'),
                'type' => 'text',
                'default' => 'Product Image',
                'std' => 'Product Image',
                'id' => 'rac_product_info_product_image',
                'clone_id' => 'rac_product_info_product_image',
            ),
            array(
                'name' => __('Customize Product Quantity in Email Template', 'recoverabandoncart'),
                'desc' => __('Customize Product Quantity in Email Template', 'recoverabandoncart'),
                'type' => 'text',
                'default' => 'Quantity',
                'std' => 'Quantity',
                'id' => 'rac_product_info_quantity',
                'clone_id' => 'rac_product_info_quantity',
            ),
            array(
                'name' => __('Customize Product Price Caption in Email Template', 'recoverabandoncart'),
                'desc' => __('Customize Product Price Caption in Email Template', 'recoverabandoncart'),
                'type' => 'text',
                'default' => 'Product Price',
                'std' => 'Product Price',
                'id' => 'rac_product_info_product_price',
                'clone_id' => 'rac_product_info_product_price',
            ),
            array(
                'name' => __('Customize Subtotal Caption in Email Template', 'recoverabandoncart'),
                'desc' => __('Customize Subtotal Caption in Email Template', 'recoverabandoncart'),
                'type' => 'text',
                'default' => 'Subtotal',
                'std' => 'Subtotal',
                'id' => 'rac_product_info_subtotal',
                'clone_id' => 'rac_product_info_subtotal',
            ),
            array(
                'name' => __('Customize Tax Caption in Email Template', 'recoverabandoncart'),
                'desc' => __('Customize Tax Caption in Email Template', 'recoverabandoncart'),
                'type' => 'text',
                'default' => 'Tax',
                'std' => 'Tax',
                'id' => 'rac_product_info_tax',
                'clone_id' => 'rac_product_info_tax',
            ),
            array(
                'name' => __('Customize Total Caption in Email Template', 'recoverabandoncart'),
                'desc' => __('Customize Total Caption in Email Template', 'recoverabandoncart'),
                'type' => 'text',
                'default' => 'Total',
                'std' => 'Total',
                'id' => 'rac_product_info_total',
                'clone_id' => 'rac_product_info_total',
            ),
            array(
                'name' => __('Hide Product Name Column in Product Info Shortcode', 'recoverabandoncart'),
                'desc' => __('Hide Product Name Column in Product Info Shortcode', 'recoverabandoncart'),
                'type' => 'checkbox',
                'default' => 'no',
                'std' => 'no',
                'id' => 'rac_hide_product_name_product_info_shortcode',
                'clone_id' => 'rac_hide_product_name_product_info_shortcode',
            ),
            array(
                'name' => __("Hide Product Image Column in Product Info Shortcode", 'recoverabandoncart'),
                'desc' => __('Hide Product Image Column in Product Info Shortcode', 'recoverabandoncart'),
                'type' => 'checkbox',
                'default' => 'no',
                'std' => 'no',
                'id' => 'rac_hide_product_image_product_info_shortcode',
                'clone_id' => 'rac_hide_product_image_product_info_shortcode',
            ),
            array(
                'name' => __('Hide Quantity Column in Product Info Shortcode', 'recoverabandoncart'),
                'desc' => __('Hide Quantity Column in Product Info Shortcode', 'recoverabandoncart'),
                'type' => 'checkbox',
                'default' => 'no',
                'std' => 'no',
                'id' => 'rac_hide_product_quantity_product_info_shortcode',
                'clone_id' => 'rac_hide_product_quantity_product_info_shortcode',
            ),
            array(
                'name' => __('Hide Product Price Column in Product Info Shortcode', 'recoverabandoncart'),
                'desc' => __('Hide Product Price Column in Product Info Shortcode', 'recoverabandoncart'),
                'type' => 'checkbox',
                'default' => 'no',
                'std' => 'no',
                'id' => 'rac_hide_product_price_product_info_shortcode',
                'clone_id' => 'rac_hide_product_price_product_info_shortcode',
            ),
            array(
                'name' => __('Hide Subtotal , Tax , Total Rows in Product Info Shortcode', 'recoverabandoncart'),
                'desc' => __('Hide Subtotal , Tax , Total Rows in Product Info Shortcode', 'recoverabandoncart'),
                'type' => 'checkbox',
                'default' => 'no',
                'std' => 'no',
                'id' => 'rac_hide_tax_total_product_info_shortcode',
                'clone_id' => 'rac_hide_tax_total_product_info_shortcode',
            ),
            array(
                'name' => __('Clear the Cart Content When Cart Link is Clicked', 'recoverabandoncart'),
                'desc' => __('', 'recoverabandoncart'),
                'type' => 'checkbox',
                'default' => 'yes',
                'std' => 'yes',
                'id' => 'rac_cart_content_when_cart_link_is_clicked',
                'clone_id' => 'rac_cart_content_when_cart_link_is_clicked',
            ),
            array(
                'name' => __('Display Product Price Including Tax in Emails', 'recoverabandoncart'),
                'desc' => __('Display Product Price Including Tax in Emails', 'recoverabandoncart'),
                'type' => 'checkbox',
                'default' => 'no',
                'std' => 'no',
                'id' => 'rac_inc_tax_with_product_price_product_info_shortcode',
                'clone_id' => 'rac_inc_tax_with_product_price_product_info_shortcode',
            ),
            array('type' => 'sectionend', 'id' => 'rac_email_gen_settings'),
        ));
    }

    public static function fp_rac_menu_options_troubleshoot() {
        $defaultval = "webmaster@" . $_SERVER['SERVER_NAME'];
        return apply_filters('woocommerce_fpwcctsingle_settings', array(
            array(
                'name' => __('Troubleshooting', 'recoverabandoncart'),
                'type' => 'title',
                'desc' => '',
                'id' => 'rac_troubleshoot',
                'clone_id' => '',
            ),
            array(
                'name' => __('Use Mail Function', 'recoverabandoncart'),
                'desc' => __('Please Select which mail function to use while sending notification', 'recoverabandoncart'),
                'tip' => '',
                'id' => 'rac_trouble_mail',
                'css' => 'min-width:150px;',
                'type' => 'select',
                'desc_tip' => true,
                'options' => array('mail' => 'mail()', 'wp_mail' => 'wp_mail()'),
                'std' => 'wp_mail',
                'default' => 'wp_mail',
                'clone_id' => 'rac_trouble_mail',
            ),
            array(
                'name' => __('Use Mail Troubleshoot', 'recoverabandoncart'),
                'desc' => __('Please enable this option if you want to send Emails using Fifth Parameter ', 'recoverabandoncart'),
                'tip' => '',
                'id' => 'rac_webmaster_mail',
                'css' => 'min-width:150px;',
                'type' => 'select',
                'desc_tip' => true,
                'options' => array('webmaster1' => 'Enable', 'webmaster2' => 'Disable'),
                'std' => 'webmaster2',
                'default' => 'webmaster2',
                'clone_id' => 'rac_webmaster_mail',
            ),
            array(
                'name' => __('Use Email as Fifth Parameter', 'recoverabandoncart'),
                'desc' => __(''),
                'id' => 'rac_textarea_mail',
                'std' => $defaultval,
                'default' => $defaultval,
                'type' => 'text',
                'newids' => 'rac_textarea_mail',
                'class' => ''
            ),
            array('type' => 'sectionend', 'id' => 'rac_troubleshoot'), //Time Settings END
            array(
                'name' => __('Troubleshoot Performance', 'recoverabandoncart'),
                'desc' => __(''),
                'type' => 'title',
                'id' => 'rac_troubleshoot_performance',
            ),
            array(
                'name' => __('Load Recover Abandon Cart Script/Styles in ', 'recoverabandoncart'),
                'desc' => __('For Footer of the Site Option is experimental why because if your theme doesn\'t contain wp_footer hook then it won\'t work', 'recoverabandoncart'),
                'tip' => '',
                'id' => 'rac_load_script_styles',
                'css' => 'min-width:150px;',
                'type' => 'select',
                'desc_tip' => false,
                'options' => array('wp_head' => 'Header of the Site', 'wp_footer' => 'Footer of the Site (Experimental)'),
                'std' => 'wp_head',
                'default' => 'wp_head',
                'clone_id' => 'rac_load_script_styles',
            ),
            array('type' => 'sectionend', 'id' => 'rac_troubleshoot_performance'),
            array(
                'name' => __('Troubleshoot Ajax Chunking', 'recoverabandoncart'),
                'desc' => __(''),
                'type' => 'title',
                'id' => 'rac_troubleshoot_ajax_chunking',
            ),
            array(
                'name' => __('Chunk Count per Ajax call', 'recoverabandoncart'),
                'desc' => __('Applicable for "Check Previous Orders" tab'),
                'id' => 'rac_chunk_count_per_ajax',
                'std' => 10,
                'default' => 10,
                'type' => 'number',
                'step' => 1,
                'newids' => 'rac_chunk_count_per_ajax',
                'desc_tip' => stripslashes("Don't Change the Value unless you need")
            ),
            array('type' => 'sectionend', 'id' => 'rac_troubleshoot_ajax_chunking'),
        ));
    }

    public static function fp_rac_support_admin_fields() {
        global $woocommerce;
        return apply_filters('woocommerce_fpracsupport_settings', array(
            array(
                'name' => __('Help & Support', 'recoverabandoncart'),
                'type' => 'title',
                'desc' => __('For support, feature request or any help, please <a href="http://support.fantasticplugins.com/">register and open a support ticket on our site.</a> <br> '),
                'id' => 'rac_support_settings'
            ),
            array(
                'name' => __('Documentation', 'recoverabandoncart'),
                'type' => 'title',
                'desc' => 'Please check the documentation as we have lots of information there. The documentation file can be found inside the documentation folder which you will find when you unzip the downloaded zip file.',
                'id' => 'rac_support_documentation',
            ),
            array('type' => 'sectionend', 'id' => 'rac_support_settings'),
        ));
    }

    public static function fp_rac_troubleshoot_mailsend() {
        global $woocommerce;
        if (isset($_GET['tab'])) {

            if ($_GET['tab'] == 'fpracdebug') {
                ?>

                <script type="text/javascript">

                    jQuery(document).ready(function () {
                <?php if ((float) $woocommerce->version > (float) ('2.2.0')) { ?>
                            var troubleemail = jQuery('#rac_trouble_mail').val();
                            if (troubleemail === 'mail') {
                                jQuery('.prependedrc').remove();
                                jQuery('#rac_trouble_mail').parent().append('<span class="prependedrc">For WooCommerce 2.3 or higher version mail() function will not load the woocommerce default template. This option will be deprecated </span>');
                            } else {
                                jQuery('.prependedrc').remove();
                            }
                            jQuery('#rac_trouble_mail').change(function () {
                                if (jQuery(this).val() === 'mail') {
                                    jQuery('.prependedrc').remove();
                                    jQuery('#rac_trouble_mail').parent().append('<span class="prependedrc">For WooCommerce 2.3 or higher version mail() function will not load the woocommerce default template. This option will be deprecated </span>');
                                } else {
                                    jQuery('.prependedrc').remove();
                                }
                            });
                <?php } ?>

                        if (jQuery('#rac_webmaster_mail').val() == 'webmaster1') {
                            jQuery("#rac_textarea_mail").parent().parent().show();
                        }
                        else {
                            //Hide text box here
                            jQuery("#rac_textarea_mail").parent().parent().hide();
                        }
                        jQuery("#rac_webmaster_mail").change(function () {
                            if (jQuery(this).val() == 'webmaster1') {
                                jQuery("#rac_textarea_mail").parent().parent().show();
                            }
                            else {
                                //Hide text box here
                                jQuery("#rac_textarea_mail").parent().parent().hide();
                            }
                        });
                    });</script>
                <?php
            }
        }
    }

    public static function rac_selected_users_exclude_option() {
        global $woocommerce;
        ?>
        <script type="text/javascript">
        <?php if ((float) $woocommerce->version <= (float) ('2.2.0')) { ?>
                jQuery(function () {
                    jQuery('select.custom_user_name_select').ajaxChosen({
                        method: 'GET',
                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
                        dataType: 'json',
                        afterTypeDelay: 100,
                        data: {
                            action: 'woocommerce_json_search_customers',
                            security: '<?php echo wp_create_nonce("search-customers"); ?>'
                        }
                    }, function (data) {
                        var terms = {};
                        jQuery.each(data, function (i, val) {
                            terms[i] = val;
                        });
                        return terms;
                    });
                });
        <?php } ?>
        </script>


        <?php if ((float) $woocommerce->version <= (float) ('2.2.0')) { ?>
            <tr valign="top">
                <th class="titledesc" scope="row">
                    <label for="custom_user_name_select"><?php _e('User Name Selected', 'recoverabandoncart'); ?></label>
                </th>
                <td>
                    <select name="custom_user_name_select[]" multiple="multiple" id="custom_user_name_select" class="short custom_user_name_select">
                        <?php
                        $json_ids = array();
                        $getuser = get_option('custom_user_name_select');
                        if ($getuser != "") {
                            $listofuser = $getuser;
                            if (!is_array($listofuser)) {
                                $userids = array_filter(array_map('absint', (array) explode(',', $listofuser)));
                            } else {
                                $userids = $listofuser;
                            }

                            foreach ($userids as $userid) {
                                $user = get_user_by('id', $userid);
                                ?>
                                <option value="<?php echo $userid; ?>" selected="selected"><?php echo esc_html($user->display_name) . ' (#' . absint($user->ID) . ' &ndash; ' . esc_html($user->user_email); ?></option>
                                <?php
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>
        <?php } else { ?>
            <tr valign="top">
                <th class="titledesc" scope="row">
                    <label for="custom_user_name_select"><?php _e('User Name Selected', 'recoverabandoncart'); ?></label>
                </th>
                <td>
                    <input type="hidden" class="wc-customer-search" name="custom_user_name_select" id="custom_user_name_select" data-multiple="true" data-placeholder="<?php _e('Search for a customer&hellip;', 'recoverabandoncart'); ?>" data-selected="<?php
                    $json_ids = array();
                    $getuser = get_option('custom_user_name_select');
                    if ($getuser != "") {
                        $listofuser = $getuser;
                        if (!is_array($listofuser)) {
                            $userids = array_filter(array_map('absint', (array) explode(',', $listofuser)));
                        } else {
                            $userids = $listofuser;
                        }

                        foreach ($userids as $userid) {
                            $user = get_user_by('id', $userid);
                            $json_ids[$user->ID] = esc_html($user->display_name) . ' (#' . absint($user->ID) . ' &ndash; ' . esc_html($user->user_email);
                        }echo esc_attr(json_encode($json_ids));
                    }
                    ?>" value="<?php echo implode(',', array_keys($json_ids)); ?>" data-allow_clear="true" />
                </td>
            </tr>
            <?php
        }
    }

    public static function fp_rac_menu_options_coupon_gen() {
        $categorylist = array();
        $categoryname = array();
        $categoryid = array();
        $particularcategory = get_terms('product_cat');
        if (!is_wp_error($particularcategory)) {
            if (!empty($particularcategory)) {
                if (is_array($particularcategory)) {
                    foreach ($particularcategory as $category) {
                        $categoryname[] = $category->name;
                        $categoryid[] = $category->term_id;
                    }
                }
                $categorylist = array_combine((array) $categoryid, (array) $categoryname);
            }
        }
        return apply_filters('woocommerce_fpraccoupon_settings', array(
            array(
                'name' => __('Coupon Code', 'recoverabandoncart'),
                'type' => 'title',
                'desc' => '',
                'id' => 'rac_coupon',
                'clone_id' => '',
            ),
            array(
                'name' => __('Prefix Text of Coupon Code', 'recoverabandoncart'),
                'desc' => __('Select Prefix Text in Coupon Code', 'recoverabandoncart'),
                'tip' => '',
                'id' => 'rac_prefix_coupon',
                'css' => '',
                'desc_tip' => true,
                'type' => 'select',
                'options' => array(
                    '1' => __('Default', 'recoverabandoncart'),
                    '2' => __('Custom', 'recoverabandoncart'),
                ),
                'std' => '1',
                'default' => '1',
                'clone_id' => 'rac_prefix_coupon',
            ),
            array(
                'name' => __('Custom Prefix Text of Coupon Code', 'recoverabandoncart'),
                'desc' => __('Enter Custom Prefix Text for Coupon Code', 'recoverabandoncart'),
                'tip' => '',
                'id' => 'rac_manual_prefix_coupon_code',
                'css' => 'rac_manual_prefix',
                'desc_tip' => true,
                'type' => 'text',
                'std' => '',
                'default' => '',
                'clone_id' => 'rac_manual_prefix_coupon_code',
            ),
            array(
                'name' => __('Type of Discount', 'recoverabandoncart'),
                'desc' => __('Please Select which type of discount should be applied', 'recoverabandoncart'),
                'tip' => '',
                'id' => 'rac_coupon_type',
                'css' => 'min-width:150px;',
                'type' => 'select',
                'desc_tip' => true,
                'options' => array('fixed_cart' => 'Amount', 'percent' => 'Percentage'),
                'std' => 'fixed_cart',
                'default' => 'fixed_cart',
                'clone_id' => 'rac_coupon_type',
            ),
            array(
                'name' => __('Value', 'recoverabandoncart'),
                'desc' => __('Enter the value to reduce in currency or % based on the Type of Discount Selected without any Symbols'),
                'tip' => '',
                'desc_tip' => true,
                'id' => 'rac_coupon_value',
                'std' => "",
                'default' => "",
                'type' => 'text',
                'newids' => 'rac_coupon_value',
                'class' => ''
            ),
            array(
                'name' => __('Validity in Days', 'recoverabandoncart'),
                'desc' => __('Enter a value(days in number) for how long the Coupon should be Active'),
                'desc_tip' => true,
                'id' => 'rac_coupon_validity',
                'std' => "7",
                'default' => "7",
                'type' => 'text',
                'newids' => 'rac_coupon_validity',
                'class' => ''
            ),
            array(
                'name' => __('Minimum Amount for Coupon Usage', 'recoverabandoncart'),
                'id' => 'rac_minimum_spend',
                'std' => '',
                'default' => '',
                'type' => 'text',
                'newids' => 'rac_minimum_spend',
                'class' => '',
            ),
            array(
                'name' => __('Maximum Amount for Coupon Usage', 'recoverabandoncart'),
                'id' => 'rac_maximum_spend',
                'std' => '',
                'default' => '',
                'type' => 'text',
                'newids' => 'rac_maximum_spend',
                'class' => '',
            ),
            array(
                'name' => __('Individual use only', 'recoverabandoncart'),
                'id' => 'rac_individual_use_only',
                'desc' => __('Check this box if the coupon cannot be used in conjunction with other coupons.', 'recoverabandoncart'),
                'std' => 'no',
                'default' => 'no',
                'type' => 'checkbox',
                'newids' => 'rac_individual_use_only',
                'class' => '',
            ),
            array(
                'type' => 'rac_coupon_include_products',
            ),
            array(
                'type' => 'rac_coupon_exclude_products',
            ),
            array(
                'name' => __('Select Category', 'recoverabandoncart'),
                'desc' => __('Select the Categories to which the coupons from abandoned cart emails can be applied', 'recoverabandoncart'),
                'tip' => '',
                'id' => 'rac_select_category_to_enable_redeeming',
                'class' => 'rac_select_category_to_enable_redeeming',
                'css' => 'min-width:350px',
                'std' => '',
                'type' => 'multiselect',
                'newids' => 'rac_select_category_to_enable_redeeming',
                'options' => $categorylist,
                'desc_tip' => true,
            ),
            array(
                'name' => __('Exclude Category', 'recoverabandoncart'),
                'desc' => __('Select the Categories to which the coupons from abandoned cart emails cannot be applied', 'recoverabandoncart'),
                'tip' => '',
                'id' => 'rac_exclude_category_to_enable_redeeming',
                'class' => 'rac_exclude_category_to_enable_redeeming',
                'css' => 'min-width:350px',
                'std' => '',
                'type' => 'multiselect',
                'newids' => 'rac_exclude_category_to_enable_redeeming',
                'options' => $categorylist,
                'desc_tip' => true,
            ),
            array('type' => 'sectionend', 'id' => 'rac_coupon'), //Coupon Settings END
            array(
                'name' => __('Coupon Code Deletion', 'recoverabandoncart'),
                'type' => 'title',
                'desc' => '',
                'id' => 'rac_coupon_deletion',
                'clone_id' => '',
            ),
            array(
                'name' => __('Delete Coupons after Used', 'recoverabandoncart'),
                'desc' => __('Delete Coupons which are automatically created by Recover Abandoned Cart that are Used'),
                'id' => 'rac_delete_coupon_after_use',
                'std' => 'no',
                'default' => 'no',
                'type' => 'checkbox',
                'newids' => 'rac_delete_coupon_after_use',
            ),
            array(
                'name' => __('Delete Coupons after Expired', 'recoverabandoncart'),
                'desc' => __('Delete Coupons which are automatically created by Recover Abandoned Cart that are Expired'),
                'id' => 'rac_delete_coupon_expired',
                'std' => 'no',
                'default' => 'no',
                'type' => 'checkbox',
                'newids' => 'rac_delete_coupon_expired',
            ),
            array('type' => 'sectionend', 'id' => 'rac_coupon_deletion'), //Coupon Settings END
        ));
    }

    public static function fp_rac_admin_setting_general() {
        woocommerce_admin_fields(RecoverAbandonCart::fp_rac_menu_options_general());
    }

    public static function fp_rac_update_options_general() {
        woocommerce_update_options(RecoverAbandonCart::fp_rac_menu_options_general());
    }

    public static function fp_rac_admin_setting_troubleshoot() {
        woocommerce_admin_fields(RecoverAbandonCart::fp_rac_menu_options_troubleshoot());
    }

    public static function fp_rac_update_options_troubleshoot() {
        woocommerce_update_options(RecoverAbandonCart::fp_rac_menu_options_troubleshoot());
    }

    public static function fp_rac_admin_setting_email() {
        woocommerce_admin_fields(RecoverAbandonCart::fp_rac_menu_options_email());
    }

    public static function fp_rac_update_options_email() {
        woocommerce_update_options(RecoverAbandonCart::fp_rac_menu_options_email());
    }

    public static function fp_rac_admin_setting_coupon() {
        woocommerce_admin_fields(self::fp_rac_menu_options_coupon_gen());
    }

    public static function fp_rac_update_options_coupon() {
        woocommerce_update_options(self::fp_rac_menu_options_coupon_gen());
    }

    public static function create_load_table() {
        global $wpdb;
        $currentdbversion = '3.5.6';
        $olddbversion = get_option('rac_db_version');
        if (!$olddbversion) {
            add_option('rac_db_version', '1.0.0');
        }
        if ($currentdbversion != get_option('rac_db_version')) {
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            $table_name = $wpdb->prefix . 'rac_abandoncart';
            $sql = "CREATE TABLE " . $table_name . "(
             id int(9) NOT NULL AUTO_INCREMENT,
             cart_details LONGTEXT NOT NULL,
             user_id LONGTEXT NOT NULL,
              email_id VARCHAR(255),
             cart_abandon_time BIGINT NOT NULL,
             cart_status LONGTEXT NOT NULL,
             mail_template_id LONGTEXT,
             ip_address LONGTEXT,
             link_status LONGTEXT,
             sending_status VARCHAR(15) NOT NULL DEFAULT 'SEND',
             wpml_lang VARCHAR(10),
             placed_order VARCHAR(20),
             completed VARCHAR(20),
              UNIQUE KEY id (id)
            )DEFAULT CHARACTER SET utf8;";

            dbDelta($sql);

//Email Template Table
            $table_name_email = $wpdb->prefix . 'rac_templates_email';
            $sql_email = "CREATE TABLE " . $table_name_email . "(
             id int(9) NOT NULL AUTO_INCREMENT,
             template_name LONGTEXT NOT NULL,
             sender_opt VARCHAR(10) NOT NULL DEFAULT 'woo',
             from_name LONGTEXT NOT NULL,
             from_email LONGTEXT NOT NULL,
             rac_blind_carbon_copy LONGTEXT NOT NULL,
             subject LONGTEXT NOT NULL,
             anchor_text LONGTEXT NOT NULL,
             message LONGTEXT NOT NULL,
             sending_type VARCHAR(20) NOT NULL,
             sending_duration BIGINT NOT NULL,
             status VARCHAR(10) NOT NULL DEFAULT 'ACTIVE',

             UNIQUE KEY id (id)
            )DEFAULT CHARACTER SET utf8;";
            dbDelta($sql_email);

//Email Logs
            $table_name_logs = $wpdb->prefix . 'rac_email_logs';
            $sql_email_logs = "CREATE TABLE " . $table_name_logs . "(
             id int(9) NOT NULL AUTO_INCREMENT,
             email_id LONGTEXT NOT NULL,
             date_time LONGTEXT NOT NULL,
             rac_cart_id LONGTEXT NOT NULL,
             template_used LONGTEXT NOT NULL,
              UNIQUE KEY id (id)
            )DEFAULT CHARACTER SET utf8;";
            dbDelta($sql_email_logs);

            $email_temp_check = $wpdb->get_results("SELECT * FROM $table_name_email", OBJECT);
            if (empty($email_temp_check)) {
                $wpdb->insert($table_name_email, array('template_name' => 'Default',
                    'sender_opt' => 'woo',
                    'from_name' => 'Admin',
                    'from_email' => get_option('admin_email'),
                    'rac_blind_carbon_copy' => '',
                    'subject' => 'Recovering Abandon Cart',
                    'anchor_text' => 'Cart Link',
                    'message' => "Hi {rac.firstname},<br><br>We noticed you have added the following Products in your Cart, but haven't completed the purchase. {rac.Productinfo}<br><br>We have captured the Cart for your convenience. Please use the following link to complete the purchase {rac.cartlink}<br><br>Thanks.",
                    'sending_type' => 'days',
                    'sending_duration' => '1'));
            }
//altering table like below in order to avoid problem with previous table
            $wpdb->query("ALTER TABLE $table_name change mail_sent_to ip_address LONGTEXT");
            $wpdb->query("ALTER TABLE $table_name ADD sending_status varchar(15) NOT NULL DEFAULT 'SEND' AFTER link_status");
            $wpdb->query("ALTER TABLE $table_name ADD email_id VARCHAR(255) AFTER user_id");
            $wpdb->query("ALTER TABLE $table_name ADD wpml_lang varchar(10) NOT NULL DEFAULT 'en' AFTER sending_status");
//altering for email templates
            $wpdb->query("ALTER TABLE $table_name_email ADD sender_opt varchar(10) NOT NULL DEFAULT 'woo' AFTER template_name");
            $wpdb->query("ALTER TABLE $table_name_email ADD status varchar(10) NOT NULL DEFAULT 'ACTIVE' AFTER sending_duration");
            $wpdb->query("ALTER TABLE $table_name_email ADD mail VARCHAR(10) NOT NULL DEFAULT 'HTML' AFTER status");
            $wpdb->query("ALTER TABLE $table_name_email ADD link LONGTEXT NOT NULL AFTER mail");
            $wpdb->query("ALTER TABLE $table_name_email ADD anchor_text LONGTEXT NOT NULL");
            $wpdb->query("ALTER TABLE $table_name_email ADD rac_blind_carbon_copy LONGTEXT NOT NULL");

            $wpdb->query("ALTER TABLE $table_name ADD old_status LONGTEXT NOT NULL");


//update default values to old rows
            $wpdb->query("UPDATE $table_name_email SET anchor_text='Cart Link' WHERE anchor_text=''");
            update_option('rac_db_version', $currentdbversion);
        }
    }

    public static function fp_rac_create_new_email_template() {
        if (isset($_POST['rac_template_name'])) {
            global $wpdb;
            $table_name_email = $wpdb->prefix . 'rac_templates_email';
            $wpdb->insert($table_name_email, array('template_name' => stripslashes($_POST['rac_template_name']),
                'status' => stripslashes($_POST['rac_template_status']),
                'sender_opt' => stripslashes($_POST['rac_sender_option']),
                'from_name' => stripslashes($_POST['rac_from_name']),
                'from_email' => stripslashes($_POST['rac_from_email']),
                'rac_blind_carbon_copy' => stripslashes($_POST['rac_blind_carbon_copy']),
                'subject' => stripslashes($_POST['rac_subject']),
                'anchor_text' => stripslashes($_POST['rac_anchor_text']),
                'message' => stripslashes($_POST['rac_message']),
                'sending_type' => stripslashes($_POST['rac_duration_type']),
                'sending_duration' => stripslashes($_POST['rac_mail_duration']),
                'mail' => stripslashes($_POST['rac_template_mail']), // mail plain or html
                'link' => stripslashes($_POST['rac_logo_mail']))           // mail logo upload
            );
        }
        echo $wpdb->insert_id;
        exit();
    }

    public static function fp_rac_edit_email_template() {
        if (isset($_POST['rac_template_id'])) {
            $template_id = $_POST['rac_template_id'];
            global $wpdb;
            $table_name_email = $wpdb->prefix . 'rac_templates_email';
            $wpdb->update($table_name_email, array('template_name' => stripslashes($_POST['rac_template_name']),
                'status' => stripslashes($_POST['rac_template_status']),
                'sender_opt' => stripslashes($_POST['rac_sender_option']),
                'from_name' => stripslashes($_POST['rac_from_name']),
                'from_email' => stripslashes($_POST['rac_from_email']),
                'rac_blind_carbon_copy' => stripslashes($_POST['rac_blind_carbon_copy']),
                'subject' => stripslashes($_POST['rac_subject']),
                'anchor_text' => stripslashes($_POST['rac_anchor_text']),
                'message' => stripslashes($_POST['rac_message']),
                'sending_type' => stripslashes($_POST['rac_duration_type']),
                'mail' => stripslashes($_POST['rac_template_mail']), // mail plain or html
                'link' => stripslashes($_POST['rac_logo_mail']), // mail logo upload
                'sending_duration' => stripslashes($_POST['rac_mail_duration'])), array('id' => $template_id));
        }
        exit();
    }

    public static function fp_rac_send_email_template_test_mail() {
        global $wpdb, $woocommerce;
        if (isset($_POST['rac_template_id'])) {
            $to = $_POST['rac_to_email'];
            $sender_option_post = stripslashes($_POST['rac_sender_option']);
            $mail_template_post = stripslashes($_POST['rac_template_mail']);  // mail plain or html
            $mail_logo_added = stripslashes($_POST['rac_logo_mail']);   // mail logo uploaded
            $from_name_post = stripslashes($_POST['rac_from_name']);
            $from_email_post = stripslashes($_POST['rac_from_email']);
            $message_post = stripslashes($_POST['rac_message']);
            $bcc_post = stripslashes($_POST['rac_blind_carbon_copy']);
            $subject_post = stripslashes($_POST['rac_subject']);
            $anchor_text_post = stripslashes($_POST['rac_anchor_text']);
            $mail_template_id_post = isset($_POST['rac_template_id']) ? $_POST['rac_template_id'] : '';
            $table_name_email = $wpdb->prefix . 'rac_templates_email';
            $date = date('d:m:y', time());
            $time = date('h:i:s', time());
            ?>
            <style type="text/css">
                table {
                    border-collapse: separate;
                    border-spacing: 0;
                    color: #4a4a4d;
                    font: 14px/1.4 "Helvetica Neue", Helvetica, Arial, sans-serif;
                }
                th,
                td {
                    padding: 10px 15px;
                    vertical-align: middle;
                }
                thead {
                    background: #395870;
                    background: linear-gradient(#49708f, #293f50);
                    color: #fff;
                    font-size: 11px;
                    text-transform: uppercase;
                }
                th:first-child {
                    border-top-left-radius: 5px;
                    text-align: left;
                }
                th:last-child {
                    border-top-right-radius: 5px;
                }
                tbody tr:nth-child(even) {
                    background: #f0f0f2;
                }
                td {
                    border-bottom: 1px solid #cecfd5;
                    border-right: 1px solid #cecfd5;
                }
                td:first-child {
                    border-left: 1px solid #cecfd5;
                }
                .book-title {
                    color: #395870;
                    display: block;
                }
                .text-offset {
                    color: #7c7c80;
                    font-size: 12px;
                }
                .item-stock,
                .item-qty {
                    text-align: center;
                }
                .item-price {
                    text-align: right;
                }
                .item-multiple {
                    display: block;
                }
                tfoot {
                    text-align: right;
                }
                tfoot tr:last-child {
                    background: #f0f0f2;
                    color: #395870;
                    font-weight: bold;
                }
                tfoot tr:last-child td:first-child {
                    border-bottom-left-radius: 5px;
                }
                tfoot tr:last-child td:last-child {
                    border-bottom-right-radius: 5px;
                }

            </style>
            <?php
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            if ($sender_option_post == 'local') {
                $headers .= FPRacCron::rac_formatted_from_address_local($from_name_post, $from_email_post);
                $headers .= "Reply-To: " . $from_name_post . " <" . $from_email_post . ">\r\n";
            } else {
                $headers .= FPRacCron::rac_formatted_from_address_woocommerce();
                $headers .= "Reply-To: " . get_option('woocommerce_email_from_name') . " <" . get_option('woocommerce_email_from_address') . ">\r\n";
            }
            if ($bcc_post) {
                $headers .= "Bcc: " . $bcc_post . "\r\n";
            }
            if (function_exists('wc_get_page_permalink')) {
                $cart_url = wc_get_page_permalink('cart');
            } else {
                $cart_url = WC()->cart->get_cart_url();
            }
            $url_to_click = esc_url_raw(add_query_arg(array('abandon_cart' => '00', 'email_template' => $mail_template_id_post), $cart_url));
            if (get_option('rac_cart_link_options') == '1') {
                $url_to_click = '<a style="color:#' . get_option("rac_email_link_color") . '"  href="' . $url_to_click . '">' . fp_get_wpml_text('rac_template_' . $mail_template_id_post . '_anchor_text', 'en', $anchor_text_post) . '</a>';
            } elseif (get_option('rac_cart_link_options') == '2') {
                $url_to_click = $url_to_click;
            } else {
                $cart_Text = fp_get_wpml_text('rac_template_' . $mail_template_id_post . '_anchor_text', 'en', $anchor_text_post);
                $url_to_click = RecoverAbandonCart::rac_cart_link_button_mode($url_to_click, $cart_Text);
            }

            $message_post = str_replace('{rac.cartlink}', $url_to_click, $message_post);
            $message_post = str_replace('{rac.date}', $date, $message_post);
            $message_post = str_replace('{rac.time}', $time, $message_post);
            $message_post = str_replace('{rac.firstname}', 'First Name', $message_post);
            $message_post = str_replace('{rac.lastname}', 'Last Name', $message_post);
            $message_post = str_replace('{rac.Productinfo}', RecoverAbandonCart::sample_productinfo_shortcode(), $message_post);
            $message_post = str_replace('{rac.coupon}', 'testcoupo.n1234567890', $message_post);

            $logo = '<table><tr><td align="center" valign="top"><p style="margin-top:0;"><img style="max-height:600px;max-width:600px;" src="' . esc_url($mail_logo_added) . '" /></p></td></tr></table>';
// woocommerce template
            if ($mail_template_post == "HTML") {
                $woo_temp_msg = self::email_woocommerce_html($mail_template_post, $subject_post, $message_post, $logo);
                $mailer = WC()->mailer();
                $mailer->send($to, $subject_post, $woo_temp_msg, $headers, '');
                echo 1;
                exit();
            } else {
                $woo_temp_msg = self::email_woocommerce_html($mail_template_post, $subject_post, $message_post, $logo);
                wp_mail($to, $subject_post, $woo_temp_msg, $headers);
                echo 1;
                exit();
            }
        }
    }

    public static function sample_productinfo_shortcode() {
        ob_start();
        ?>
        <table class="<?php echo FPRacCron::enable_border() ?>" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" >
            <thead>
                <?php if (get_option('rac_hide_product_name_product_info_shortcode') != 'yes') { ?>
                <th class="<?php echo FPRacCron::enable_border() ?>" scope="col" style="text-align:left;">
                    <?php echo fp_get_wpml_text('rac_template_product_name', 'en', get_option('rac_product_info_product_name')); ?>
                </th>
            <?php } if (get_option('rac_hide_product_image_product_info_shortcode') != 'yes') { ?>
                <th class="<?php echo FPRacCron::enable_border() ?>" scope="col" style="text-align:left;">
                    <?php echo fp_get_wpml_text('rac_template_product_image', 'en', get_option('rac_product_info_product_image')); ?>
                </th>
            <?php } if (get_option('rac_hide_product_quantity_product_info_shortcode') != 'yes') { ?>
                <th class="<?php echo FPRacCron::enable_border() ?>" scope="col" style="text-align:left;">
                    <!-- For Quantity -->
                    <?php echo fp_get_wpml_text('rac_template_product_quantity', 'en', get_option('rac_product_info_quantity')); ?>
                </th>
            <?php } if (get_option('rac_hide_product_price_product_info_shortcode') != 'yes') { ?>
                <th class="<?php echo FPRacCron::enable_border() ?>" scope="col" style="text-align:left;">
                    <?php echo fp_get_wpml_text('rac_template_product_price', 'en', get_option('rac_product_info_product_price')); ?>
                </th>
            <?php } ?>
        </thead>
        <tbody>
            <?php
            echo fp_split_rac_items_in_cart('Product A', wc_placeholder_img(array(90, 90)), '1', FP_List_Table_RAC::format_price(10));
            ?>
        </tbody>
        <?php if (get_option('rac_hide_tax_total_product_info_shortcode') != 'yes') { ?>
            <tfoot>
                <tr>
                    <th class="<?php echo FPRacCron::enable_border() ?>" scope="row" colspan="3" style="text-align:left; <?php echo 'border-top-width: 4px;'; ?>"><?php echo fp_get_wpml_text('rac_template_subtotal', 'en', get_option('rac_product_info_subtotal')); ?></th>
                    <td class="<?php echo FPRacCron::enable_border() ?>" style="text-align:left; <?php echo 'border-top-width: 4px;'; ?>"><?php echo FP_List_Table_RAC::format_price(10); ?></td>
                </tr>
                <tr>
                    <th class="<?php echo FPRacCron::enable_border() ?>" scope="row" colspan="3" style="text-align:left; <?php echo 'border-top-width: 4px;'; ?>"><?php echo fp_get_wpml_text('rac_template_total', 'en', get_option('rac_product_info_total')); ?></th>
                    <td class="<?php echo FPRacCron::enable_border() ?>" style="text-align:left; <?php echo 'border-top-width: 4px;'; ?>"><?php echo FP_List_Table_RAC::format_price(10); ?></td>
                </tr>

            </tfoot>
        <?php } ?>
        </table>
        <?php
        return ob_get_clean();
    }

    public static function fp_rac_copy_email_template() {
        global $wpdb;
//        if (isset($_POST['copy_this_template'])) {
        $id = $_POST['row_id'];
        $table_name = $wpdb->prefix . 'rac_templates_email';
        $templates = $wpdb->get_results("SELECT * FROM $table_name WHERE id=$id", OBJECT);
        foreach ($templates as $my_template) {
            $id = $my_template->id;
            $template_name = $my_template->template_name;
            $template_name_copy = $template_name . '-copy';
            $sender_opt = $my_template->sender_opt;
            $from_name = $my_template->from_name;
            $from_email = $my_template->from_email;
            $bcc = $my_template->rac_blind_carbon_copy;
            $subject = $my_template->subject;
            $anchor_text = $my_template->anchor_text;
            $message = $my_template->message;
            $sending_type = $my_template->sending_type;
            $sending_duration = $my_template->sending_duration;
            $status = $my_template->status;
            $mail = $my_template->mail;
            $link = $my_template->link;
        }
        $wpdb->insert($table_name, array('template_name' => stripslashes($template_name_copy),
            'status' => stripslashes($status),
            'sender_opt' => stripslashes($sender_opt),
            'from_name' => stripslashes($from_name),
            'from_email' => stripslashes($from_email),
            'rac_blind_carbon_copy' => stripslashes($bcc),
            'subject' => stripslashes($subject),
            'anchor_text' => stripslashes($anchor_text),
            'message' => stripslashes($message),
            'sending_type' => stripslashes($sending_type),
            'sending_duration' => stripslashes($sending_duration),
            'mail' => stripslashes($mail), // mail plain or html
            'link' => stripslashes($link))           // mail logo upload
        );
        exit();
//        }
    }

    public static function fp_rac_edit_mail_update_data() {
        global $wpdb;
        $row_id = $_POST['id'];
        $email_value = $_POST['email'];
        $table_name = $wpdb->prefix . 'rac_abandoncart';
        $last_cart = $wpdb->get_results("SELECT * FROM $table_name WHERE id=$row_id and cart_status NOT IN('trash')", OBJECT);
        $last_cart_key = key($last_cart);
        $user_details = maybe_unserialize($last_cart[$last_cart_key]->cart_details);
        $user_details["visitor_mail"] = $email_value;
        $details = serialize($user_details);
        $wpdb->update($table_name, array('cart_details' => $details), array('id' => $row_id));
        exit();
    }

    public static function fp_rac_delete_email_template() {
        if (isset($_POST['row_id'])) {
            global $wpdb;
            $row_id = $_POST['row_id'];
            $table_name_email = $wpdb->prefix . 'rac_templates_email';
            $wpdb->delete($table_name_email, array('id' => $row_id));
//removing registered WPML strings
            if (function_exists('icl_unregister_string')) {
                icl_unregister_string('RAC', 'rac_template_' . $row_id . '_message');
                icl_unregister_string('RAC', 'rac_template_' . $row_id . '_subject');
            }
        }
        exit();
    }

    public static function restrict_entries_in_cart_list($email) {
        $getrestriction = get_option('custom_restrict');
        if ($getrestriction == 'user_role') {
            $userrolenamemailget = get_option('custom_user_role_for_restrict_in_cart_list');
            $getuserby = get_user_by('email', $email);
            if ($getuserby) {
                $newto = $getuserby->roles[0];
            } else {
                $newto = $email;
            }
        } elseif ($getrestriction == 'name') {
            $userrolenamemailget = get_option('custom_user_name_select_for_restrict_in_cart_list');
            $userrolenamemailget = explode(',', $userrolenamemailget);
            $getuserby = get_user_by('email', $email);
            if ($getuserby) {
                $newto = $getuserby->ID;
            } else {
                $newto = $email;
            }
        } else {
            $userrolenamemailget = get_option('custom_mailid_for_restrict_in_cart_list');
            $userrolenamemailget = explode("\r\n", $userrolenamemailget);
            $newto = $email;
        }
        if (!empty($userrolenamemailget)) {
            if (!in_array($newto, $userrolenamemailget)) {
                return 'proceed';
            } else {
                return 'not proceed';
            }
        } else {
            return 'proceed';
        }
    }

    public static function fp_rac_insert_entry() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'rac_abandoncart';
        $current_time = current_time('timestamp');

        if (function_exists('icl_register_string')) {
            $currentuser_lang = isset($_SESSION['wpml_globalcart_language']) ? $_SESSION['wpml_globalcart_language'] : ICL_LANGUAGE_CODE;
        } else {
            $currentuser_lang = 'en';
        }
        if (is_user_logged_in() && !is_admin()) {

            $user_id = get_current_user_id();
            $user_details = get_userdata($user_id);
            $user_email = $user_details->user_email;

            $last_cart = $wpdb->get_results("SELECT * FROM $table_name WHERE user_id = $user_id and cart_status IN('NEW','ABANDON') and placed_order IS NULL ORDER BY id DESC LIMIT 1", OBJECT);
            if (!empty($last_cart)) {
                $last_cart = $last_cart[0];
            }


            $cart_persistent = (get_user_meta($user_id, '_woocommerce_persistent_cart'));
            if (!empty($cart_persistent[0]['cart'])) {
                $cart_content = maybe_serialize(get_user_meta($user_id, '_woocommerce_persistent_cart'));
                $cut_off_time = get_option('rac_abandon_cart_time');
                if (get_option('rac_abandon_cart_time_type') == 'minutes') {
                    $cut_off_time = $cut_off_time * 60;
                } else if (get_option('rac_abandon_cart_time_type') == 'hours') {
                    $cut_off_time = $cut_off_time * 3600;
                } else if (get_option('rac_abandon_cart_time_type') == 'days') {
                    $cut_off_time = $cut_off_time * 86400;
                }
                if (!empty($last_cart)) {
                    $cut_off_time = $last_cart->cart_abandon_time + $cut_off_time;
                }
//$query = "INSERT INTO $table_name(cart_details,user_id) VALUES($cart_content,$user_id);";
//$wpdb->query($wpdb->prepare("INSERT INTO $table_name(cart_details,user_id) VALUES($cart_content,$user_id)"));
                if ($current_time > $cut_off_time) {
//  if ($last_cart[$last_cart_key]->cart_details != $cart_content) {
                    if ((isset($_COOKIE['rac_cart_id'])) || (isset($_GET['abandon_cart']))) {
//do nothing. Since this cart is from mail
                    } else {
                        if (!empty($last_cart)) {
                            $wpdb->update($table_name, array('cart_status' => 'ABANDON'), array('id' => $last_cart->id));
                            FPRacCounter::rac_do_abandoned_count();
                        }

                        if (get_option('rac_remove_carts') == 'yes') {
                            if (get_option('rac_remove_new') == 'yes') {
                                $wpdb->delete($table_name, array('email_id' => $user_email, 'cart_status' => 'NEW'));
                            }
                            if (get_option('rac_remove_abandon') == 'yes') {
                                $wpdb->delete($table_name, array('email_id' => $user_email, 'cart_status' => 'ABANDON'));
                            }
                        }
                        if (self::restrict_entries_in_cart_list($user_email) == 'proceed') {
                            $wpdb->insert($table_name, array('cart_details' => $cart_content, 'user_id' => $user_id, 'email_id' => $user_email, 'cart_abandon_time' => $current_time, 'cart_status' => 'NEW', 'wpml_lang' => $currentuser_lang));
                        }
                    }
                } else { //Update the cart details if less than or equal to cut off time
                    if (!empty($last_cart)) {
                        $wpdb->update($table_name, array('cart_details' => $cart_content, 'cart_abandon_time' => $current_time), array('id' => $last_cart->id));
                    }
                }
            }
// FOR ALL USER STATUS - - UPDATE ONLY
//Members
            $status_new_list = $wpdb->get_results("SELECT * FROM $table_name WHERE cart_status='NEW' AND user_id != '0'", OBJECT);
            $cut_off_time = get_option('rac_abandon_cart_time');
            if (get_option('rac_abandon_cart_time_type') == 'minutes') {
                $cut_off_time = $cut_off_time * 60;
            } else if (get_option('rac_abandon_cart_time_type') == 'hours') {
                $cut_off_time = $cut_off_time * 3600;
            } else if (get_option('rac_abandon_cart_time_type') == 'days') {
                $cut_off_time = $cut_off_time * 86400;
            }
            foreach ($status_new_list as $status_new) {
                $cut_off_time = $cut_off_time + $status_new->cart_abandon_time;
                if ($current_time > $cut_off_time) {
                    $wpdb->update($table_name, array('cart_status' => 'ABANDON'), array('id' => $status_new->id));
                    FPRacCounter::rac_do_abandoned_count();
                }
            }
//Guest
            $status_new_list = $wpdb->get_results("SELECT * FROM $table_name WHERE cart_status='NEW' AND user_id='0'", OBJECT);
            $cut_off_time = get_option('rac_abandon_cart_time_guest');
            if (get_option('rac_abandon_cart_time_type_guest') == 'minutes') {
                $cut_off_time = $cut_off_time * 60;
            } else if (get_option('rac_abandon_cart_time_type_guest') == 'hours') {
                $cut_off_time = $cut_off_time * 3600;
            } else if (get_option('rac_abandon_cart_time_type_guest') == 'days') {
                $cut_off_time = $cut_off_time * 86400;
            }
            foreach ($status_new_list as $status_new) {
                $cut_off_time = $cut_off_time + $status_new->cart_abandon_time;
                if ($current_time > $cut_off_time) {
                    $wpdb->update($table_name, array('cart_status' => 'ABANDON'), array('id' => $status_new->id));
                    FPRacCounter::rac_do_abandoned_count();
                }
            }
// FOR ALL USER STATUS - UPDATE ONLY END
        } else {
// FOR ALL USER STATUS - UPDATE ONLY
//Members
            $status_new_list = $wpdb->get_results("SELECT * FROM $table_name WHERE cart_status='NEW' AND user_id!='0'", OBJECT);
            $cut_off_time = get_option('rac_abandon_cart_time');
            if (get_option('rac_abandon_cart_time_type') == 'minutes') {
                $cut_off_time = $cut_off_time * 60;
            } else if (get_option('rac_abandon_cart_time_type') == 'hours') {
                $cut_off_time = $cut_off_time * 3600;
            } else if (get_option('rac_abandon_cart_time_type') == 'days') {
                $cut_off_time = $cut_off_time * 86400;
            }
            foreach ($status_new_list as $status_new) {
                $cut_off_time = $cut_off_time + $status_new->cart_abandon_time;
                if ($current_time > $cut_off_time) {
                    $wpdb->update($table_name, array('cart_status' => 'ABANDON'), array('id' => $status_new->id));
                    FPRacCounter::rac_do_abandoned_count();
                }
            }
//guest
            $status_new_list = $wpdb->get_results("SELECT * FROM $table_name WHERE cart_status='NEW' AND user_id='0'", OBJECT);
            $cut_off_time = get_option('rac_abandon_cart_time_guest');
            if (get_option('rac_abandon_cart_time_type_guest') == 'minutes') {
                $cut_off_time = $cut_off_time * 60;
            } else if (get_option('rac_abandon_cart_time_type_guest') == 'hours') {
                $cut_off_time = $cut_off_time * 3600;
            } else if (get_option('rac_abandon_cart_time_type_guest') == 'days') {
                $cut_off_time = $cut_off_time * 86400;
            }
            foreach ($status_new_list as $status_new) {
                $cut_off_time = $cut_off_time + $status_new->cart_abandon_time;
                if ($current_time > $cut_off_time) {
                    $wpdb->update($table_name, array('cart_status' => 'ABANDON'), array('id' => $status_new->id));
                    FPRacCounter::rac_do_abandoned_count();
                }
            }
// FOR ALL USER STATUS - UPDATE ONLY END
        }
    }

    public static function fp_rac_add_abandon_cart() {
        global $woocommerce;
//only perform recover from member mail
        if (isset($_GET['abandon_cart']) && !isset($_GET['guest']) && !isset($_GET['checkout']) && !isset($_GET['old_order'])) {
            $abandon_cart_id = $_GET['abandon_cart'];
            $email_template_id = $_GET['email_template'];
            global $wpdb;
            $table_name = $wpdb->prefix . 'rac_abandoncart';
            $last_cart = $wpdb->get_results("SELECT * FROM $table_name WHERE id = $abandon_cart_id and cart_status IN('NEW','ABANDON') and placed_order IS NULL", OBJECT);
            end($last_cart);

            $last_cart_key = key($last_cart);
            if (isset($last_cart_key)) {
                $user_details = maybe_unserialize($last_cart[$last_cart_key]->cart_details);

                foreach ($user_details as $cart) {
                    $cart_details = $cart['cart'];
                }
                if (!isset($_COOKIE['rac_cart_id'])) {
                    if (function_exists('WC')) {
                        WC()->session->cart = $cart_details;
                    } else {
                        $woocommerce->session->cart = $cart_details;
                    }
                }
                setcookie("rac_cart_id", $abandon_cart_id, time() + 3600, "/");
                if (!empty($last_cart[$last_cart_key]->link_status)) {
                    $email_template_ids_db = maybe_unserialize($last_cart[$last_cart_key]->link_status);
                    if (!in_array($email_template_id, (array) $email_template_ids_db)) { //check for id duplication
                        $email_template_ids_db[] = $email_template_id;
                        $email_template_id_final = $email_template_ids_db;
                    }
                    $email_template_id_final = $email_template_ids_db;
                } else {
                    $email_template_id_final = array($email_template_id);
                }
                $email_template_id_final = maybe_serialize($email_template_id_final);
                $wpdb->update($table_name, array('link_status' => $email_template_id_final), array('id' => $abandon_cart_id));
                FPRacCounter::rac_do_linkc_count($abandon_cart_id, $email_template_id);
            } else {
                wc_add_notice(__('Seems your cart has been already Recovered/Order Placed', 'recoverabandoncart'), 'error');
            }
            if (get_option('rac_cartlink_redirect') == '2') {
                if (function_exists('wc_get_page_permalink')) {
                    $redirect_url = wc_get_page_permalink('checkout');
                } else {
                    $redirect_url = WC()->cart->get_checkout_url();
                }
            } else {
                if (function_exists('wc_get_page_permalink')) {
                    $redirect_url = wc_get_page_permalink('cart');
                } else {
                    $redirect_url = WC()->cart->get_cart_url();
                }
            }
            wp_safe_redirect($redirect_url);
            exit;
        }
    }

    public static function check_is_member_or_guest($to) {

        $get_user_by_email = get_user_by('email', $to);

        if ($get_user_by_email) {
            return true;
        } else {
            return false;
        }
    }

    public static function rac_return_user_id($memberemail) {
        $get_user_by_email = get_user_by('email', $memberemail);

        return $get_user_by_email->ID;
    }

//Footer Record
    public static function unsubscribed_user_from_rac_mail() {
        if (isset($_COOKIE['un_sub_email_auto'])) {
            ?>
            <style type="text/css">
                p.un_sub_email_css {
                    position: fixed;
                    left: 0;
                    right: 0;
                    margin: 0;
                    width: 100%;
                    font-size: 1em;
                    padding: 1em 0;
                    text-align: center;
                    background-color: #<?php echo get_option('rac_unsubscription_message_background_color') ?>;
                    color: #<?php echo get_option('rac_unsubscription_message_text_color') ?>;
                    z-index: 99998;
                    a {
                        color: 0 1px 1em rgba(0, 0, 0, 0.2);
                    }
                }

                .admin-bar {
                    p.un_sub_email_css {
                        top: 32px;
                    }
                }
            </style>
            <?php
            if ($_COOKIE['un_sub_email_auto'] != "") {
                if (isset($_COOKIE['already_unsubscribed'])) {
                    echo '<p class="un_sub_email_css">' . get_option('rac_already_unsubscribed_text') . '</p>';
                } else {
                    echo '<p class="un_sub_email_css">' . get_option('rac_unsubscribed_successfully_text') . '</p>';
                }
            }
            unset($_COOKIE['un_sub_email_auto']);
            setcookie('un_sub_email_auto', null);
        }
        if (isset($_GET['email']) && isset($_GET['action']) && isset($_GET['_mynonce'])) {
            $to = $_GET['email'];
            if (get_option('rac_unsubscription_type') != '2') {

// Automatic Unsubscription
                $check = RecoverAbandonCart::check_is_member_or_guest($to);
                if ($check) {
// For Member
                    $member_userid = RecoverAbandonCart::rac_return_user_id($to);
                    $check_already = get_user_meta($member_userid, 'fp_rac_mail_unsubscribed', true);
                    if ($check_already == 'yes') {
                        setcookie('already_unsubscribed', 'yes', time() + 3600);
                    } else {
                        unset($_COOKIE['already_unsubscribed']);
                        setcookie('already_unsubscribed', null);
                        update_user_meta($member_userid, 'fp_rac_mail_unsubscribed', 'yes');
                    }
                } else {
// For Guest
                    $old_array = array_filter(array_unique((array) get_option('fp_rac_mail_unsubscribed')));
                    $listofemails = (array) $to;
                    $merge_arrays = array_merge($listofemails, $old_array);
                    if (in_array($to, $old_array)) {
                        setcookie('already_unsubscribed', 'yes', time() + 3600);
                    } else {
                        unset($_COOKIE['already_unsubscribed']);
                        setcookie('already_unsubscribed', null);
                        update_option('fp_rac_mail_unsubscribed', $merge_arrays);
                    }
                }
                setcookie('un_sub_email_auto', $to, time() + 3600);
                unset($_COOKIE['un_sub_email_manual']);
                setcookie('un_sub_email_manual', null);
                wp_redirect(get_permalink());
            } else {
// Manual Unsubscription
                setcookie('un_sub_email_manual', $to, time() + 3600);
                unset($_COOKIE['un_sub_email_auto']);
                setcookie('un_sub_email_auto', null);
                wp_redirect(get_permalink());
            }
        }
    }

    public static function rac_footer_email_customization($message) {

        global $to;

        if (get_option('fp_unsubscription_link_in_email') == 'yes') {
            if (get_option('rac_unsubscription_type') != '2') {
                $siteurl = get_option('rac_unsubscription_redirect_url');
                if ($siteurl) {
                    $site_url = $siteurl;
                } else {
                    $site_url = get_permalink(wc_get_page_id('myaccount'));
                }
            } else {
                $siteurl = get_option('rac_manual_unsubscription_redirect_url');
                if ($siteurl) {
                    $site_url = $siteurl;
                } else {
                    $site_url = get_permalink(wc_get_page_id('myaccount'));
                }
            }
            $site_name = get_bloginfo('name'); // Site Name
            $create_nonce = wp_create_nonce('myemail');
            $unsublink = esc_url(add_query_arg(array('email' => $to, 'action' => 'unsubscribe', '_mynonce' => $create_nonce), $site_url));
            $footer_link_text = get_option('fp_unsubscription_footer_link_text');
            $footer_message = get_option('fp_unsubscription_footer_message');
            $find_shortcode = array('{rac_unsubscribe}', '{rac_site}');
            $unsublink = '<a href="' . $unsublink . '">' . $footer_link_text . '</a>';
            $replace_shortcode = array($unsublink, $site_name);
            $footer_message = str_replace($find_shortcode, $replace_shortcode, $footer_message);
            return $footer_message;
        } else {
            return $message;
        }
    }

    public static function rac_unsubscription_shortcode($to, $message) {
        if (get_option('fp_unsubscription_link_in_email') == 'yes') {
            if (get_option('rac_unsubscription_type') != '2') {
                $siteurl = get_option('rac_unsubscription_redirect_url');
                if ($siteurl) {
                    $site_url = $siteurl;
                } else {
                    $site_url = get_permalink(wc_get_page_id('myaccount'));
                }
            } else {
                $siteurl = get_option('rac_manual_unsubscription_redirect_url');
                if ($siteurl) {
                    $site_url = $siteurl;
                } else {
                    $site_url = get_permalink(wc_get_page_id('myaccount'));
                }
            }
            $site_name = get_bloginfo('name'); // Site Name
            $create_nonce = wp_create_nonce('myemail');
            $unsublink = esc_url(add_query_arg(array('email' => $to, 'action' => 'unsubscribe', '_mynonce' => $create_nonce), $site_url));
            $footer_link_text = get_option('fp_unsubscription_footer_link_text');
            $footer_message = get_option('fp_unsubscription_footer_message');
            $find_shortcode = array('{rac_unsubscribe}', '{rac_site}');
            $unsublink = '<a href="' . $unsublink . '">' . $footer_link_text . '</a>';
            $replace_shortcode = array($unsublink, $site_name);
            $footer_message = str_replace($find_shortcode, $replace_shortcode, $footer_message);
            $message = str_replace('{rac.unsubscribe}', $footer_message, $message);
        }
        return $message;
    }

    public static function response_unsubscribe_option_myaccount() {
        if (isset($_POST['getcurrentuser']) && isset($_POST['dataclicked'])) {
            $userid = $_POST['getcurrentuser'];
            $dataclicked = $_POST['dataclicked'];

            if ($dataclicked == 'false') {
                update_user_meta($userid, 'fp_rac_mail_unsubscribed', 'yes');
                echo "1";
            } else {
                delete_user_meta($userid, 'fp_rac_mail_unsubscribed');
                echo "2";
            }
            exit();
        }
    }

    public static function add_undo_unsubscribe_option_myaccount() {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function () {
                jQuery('#fp_rac_unsubscribe_option').click(function () {
                    // alert(jQuery(this).is(':checked'));
                    var getcurrentuser = "<?php echo get_current_user_id(); ?>";
                    var dataclicked = jQuery(this).is(':checked') ? 'false' : 'true';
                    var data = {
                        action: 'fp_rac_undo_unsubscribe',
                        getcurrentuser: getcurrentuser,
                        dataclicked: dataclicked
                    };
                    jQuery.post("<?php echo admin_url('admin-ajax.php'); ?>", data,
                            function (response) {
                                response = response.replace(/\s/g, '');
                                if (response === '1') {
                                    alert("Successfully Unsubscribed...");
                                } else {
                                    alert("Successfully Subscribed...");
                                }
                            });
                });
            });</script>
        <h3><?php echo get_option('rac_unsub_myaccount_heading'); ?></h3>
        <p><input type="checkbox" name="fp_rac_unsubscribe_option" id="fp_rac_unsubscribe_option" value="yes" <?php checked("yes", get_user_meta(get_current_user_id(), 'fp_rac_mail_unsubscribed', true)); ?>/>    <?php echo get_option('rac_unsub_myaccount_text'); ?></p>
        <?php
    }

    public static function manual_unsubscribe_option() {
        if (isset($_COOKIE['un_sub_email_manual'])) {
            $mail_id_to_unsub = $_COOKIE['un_sub_email_manual'];
            if ($mail_id_to_unsub != '') {
                ?>
                <form method="post" id="manual_unsubscibe_form">
                    <input type="hidden" name="email_id_at_session" id="email_id_at_session" value="<?php echo $mail_id_to_unsub ?>">
                    <?php
                    $user = get_user_by('email', $mail_id_to_unsub);
                    if (isset($user->data->ID)) {
                        $user_id = $user->data->ID;
                        $check_already = get_user_meta($user_id, 'fp_rac_mail_unsubscribed', true);
                        if ($check_already == 'yes') {
                            ?>
                            <div class="unsubscribeContent">
                                <div class="mailSubscribe"><strong><?php echo $mail_id_to_unsub ?></strong><br></div>
                                <div class="subsInnerContent"><strong class="msgTitle"><?php echo get_option('rac_already_unsubscribed_text'); ?></strong><br>
                                </div>
                            </div>
                            <?php
                        } else {
                            ?>
                            <div class="unsubscribeContent">
                                <div class="mailSubscribe"><strong><?php echo $mail_id_to_unsub ?></strong><br></div>
                                <?php if (!isset($_POST['email_id_at_session'])) { ?>
                                    <div class="subsInnerContent"><strong class="msgTitle"><?php echo get_option('rac_confirm_unsubscription_text'); ?></strong><br>
                                    </div>
                                    <div style="text-align:center">
                                        <input type="submit" class="unsubscribe_button" id="rac_unsubscribe_manually" value="<?php echo __('Unsubscribe', 'recoverabandoncart') ?>">
                                    </div>
                                <?php } else {
                                    ?>
                                    <div class="subsInnerContent"><strong class="msgTitle"><?php echo get_option('rac_unsubscribed_successfully_text'); ?></strong></div>
                                    <?php
                                    update_user_meta($user_id, 'fp_rac_mail_unsubscribed', 'yes');
                                    unset($_COOKIE['un_sub_email_manual']);
                                    setcookie('un_sub_email_manual', null);
                                }
                                ?>
                            </div>
                            <?php
                        }
                    } else {
                        $old_array = array_filter(array_unique((array) get_option('fp_rac_mail_unsubscribed')));
                        if (in_array($mail_id_to_unsub, $old_array)) {
                            ?>
                            <div class="unsubscribeContent">
                                <div class="mailSubscribe"><strong><?php echo $mail_id_to_unsub ?></strong><br></div>
                                <div class="subsInnerContent"><strong class="msgTitle"><?php echo get_option('rac_already_unsubscribed_text'); ?></strong><br>
                                </div>
                            </div>
                            <?php
                        } else {
                            ?>
                            <div class="unsubscribeContent">
                                <div class="mailSubscribe"><strong><?php echo $mail_id_to_unsub ?></strong><br></div>
                                <?php if (!isset($_POST['email_id_at_session'])) { ?>
                                    <div class="subsInnerContent"><strong class="msgTitle"><?php echo get_option('rac_confirm_unsubscription_text'); ?></strong><br>
                                    </div>
                                    <div style="text-align:center">
                                        <input type="submit" class="unsubscribe_button" id="rac_unsubscribe_manually" value="<?php echo __('Unsubscribe', 'recoverabandoncart') ?>">
                                    </div>
                                    <?php
                                } else {
                                    ?>
                                    <div class="subsInnerContent"><strong class="msgTitle"><?php echo get_option('rac_unsubscribed_successfully_text'); ?></strong></div>
                                    <?php
                                    $old_array = array_filter(array_unique((array) get_option('fp_rac_mail_unsubscribed')));
                                    $listofemails = (array) $mail_id_to_unsub;
                                    $merge_arrays = array_merge($listofemails, $old_array);
                                    update_option('fp_rac_mail_unsubscribed', $merge_arrays);
                                    unset($_COOKIE['un_sub_email_manual']);
                                    setcookie('un_sub_email_manual', null);
                                }
                                ?>
                            </div>
                            <?php
                        }
                    }
                    ?>
                </form>
                <style type="text/css">
                    .unsubscribeContent {
                        border: 1px solid #d6d4d4;
                        border-radius: 10px;
                        box-sizing: border-box;
                        margin: 25px auto;
                        padding: 45px 60px;
                        text-align: center;
                        width: 600px;
                    }
                    .mailSubscribe {
                        border-bottom: 1px solid #dbdedf;
                        font-size: 18px;
                        line-height: 31px;
                        margin-bottom: 30px;
                        padding-bottom: 30px;
                        color: #<?php echo get_option('rac_unsubscription_email_text_color') ?>;
                    }
                    .msgTitle {
                        color: #<?php echo get_option('rac_confirm_unsubscription_text_color') ?>;
                        display: inline-block;
                        font-size: 36px;
                        margin-bottom: 24px;
                    }
                    .unsubscribe_button {
                        background-color: #FF0000;
                        border: medium none;
                        border-radius: 5px;
                        color: #FFFFFF;
                        display: inline-block;
                        font-size: 25px;
                        padding: 10px 24px;
                        text-align: center;
                        text-decoration: none;
                    }
                </style>
                <?php
            }
        }
    }

    public static function fp_rac_guest_cart_recover() {
        global $wpdb;
        global $woocommerce;
        if (isset($_GET['guest'])) {
            $email_template_id_final = '';
            $abandon_cart_id = $_GET['abandon_cart'];
            $email_template_id = $_GET['email_template'];
            $table_name = $wpdb->prefix . 'rac_abandoncart';
            $last_cart = $wpdb->get_results("SELECT * FROM $table_name WHERE id = $abandon_cart_id", OBJECT);

            $last_cart_key = key($last_cart);
            $expected_object = maybe_unserialize($last_cart[$last_cart_key]->cart_details);

            if (is_object($expected_object)) {
//For Object Recover Abandon Cart
                $cart_details = $expected_object->get_items();
                if (get_option('rac_cart_content_when_cart_link_is_clicked') == 'yes') {
                    $woocommerce->cart->empty_cart();
                }
                foreach ($cart_details as $products) {
                    $product = get_product($products['product_id']);
                    if (!empty($products['variation_id'])) {
                        $variations = array();
                        foreach ($products['item_meta'] as $meta_name => $meta_value) {
                            $attributes = $product->get_variation_attributes();
                            $lower_case = array_change_key_case($attributes, CASE_LOWER);
                            if (!empty($lower_case[$meta_name])) {
                                if (!is_null($lower_case[$meta_name])) {
                                    $value_true = in_array(strtolower($meta_value[0]), array_map('strtolower', $lower_case[$meta_name]));
                                } else {
                                    $value_true = false;
                                }
                            }

                            if (in_array(strtolower($meta_name), array_map('strtolower', array_keys($attributes))) && $value_true) {
                                $variations[$meta_name] = $meta_value[0];
                            }
                        }

                        $products['qty'][0];

                        $woocommerce->cart->add_to_cart($products['product_id'], $products['qty'][0], $products['variation_id'], array_filter($variations));
// }
                    } else {
                        $woocommerce->cart->add_to_cart($products['product_id'], $products['qty']);
                    }
                }
                setcookie("rac_cart_id", $abandon_cart_id, time() + 3600, "/");

                if (!empty($last_cart[$last_cart_key]->link_status)) {
                    $email_template_ids_db = maybe_unserialize($last_cart[$last_cart_key]->link_status);
                    if (!in_array($email_template_id, (array) $email_template_ids_db)) { //check for id duplication
                        $email_template_ids_db[] = $email_template_id;
                        $email_template_id_final = $email_template_ids_db;
                    }
                } else {
                    $email_template_id_final = array($email_template_id);
                }
            } elseif (is_array($expected_object)) {

                $expected_object = maybe_unserialize($last_cart[$last_cart_key]->cart_details);
                $cart_details = $expected_object;
                unset($cart_details['visitor_mail']);
                unset($cart_details['first_name']);
                unset($cart_details['last_name']);
                unset($cart_details['visitor_phone']);
                if (get_option('rac_cart_content_when_cart_link_is_clicked') == 'yes') {
                    $woocommerce->cart->empty_cart();
                }
                foreach ($cart_details as $products) {
                    if (!empty($products['variation_id'])) {
                        $variations = array();

                        foreach ($products['variation'] as $attr_name => $attr_val) {
                            $var_name = str_replace("attribute_", '', $attr_name);
                            $variations[$var_name] = $attr_val;
                        }

                        $woocommerce->cart->add_to_cart($products['product_id'], $products['quantity'], $products['variation_id'], $variations, $products);
                    } else {
                        $woocommerce->cart->add_to_cart($products['product_id'], $products['quantity']);
                    }
                }
                setcookie("rac_cart_id", $abandon_cart_id, time() + 3600, "/");
                if (!empty($last_cart[$last_cart_key]->link_status)) {
                    $email_template_ids_db = maybe_unserialize($last_cart[$last_cart_key]->link_status);
                    if (!in_array($email_template_id, (array) $email_template_ids_db)) { //check for id duplication
                        $email_template_ids_db[] = $email_template_id;
                        $email_template_id_final = $email_template_ids_db;
                    }
                } else {
                    $email_template_id_final = array($email_template_id);
                }
            }

            $email_template_id_final = maybe_serialize($email_template_id_final);
            $wpdb->update($table_name, array('link_status' => $email_template_id_final), array('id' => $abandon_cart_id));
            FPRacCounter::rac_do_linkc_count($abandon_cart_id, $email_template_id);
//Redirect again to cart
            if (get_option('rac_cartlink_redirect') == '2') {
                if (function_exists('wc_get_page_permalink')) {
                    $redirect_url = wc_get_page_permalink('checkout');
                } else {
                    $redirect_url = WC()->cart->get_checkout_url();
                }
            } else {
                if (function_exists('wc_get_page_permalink')) {
                    $redirect_url = wc_get_page_permalink('cart');
                } else {
                    $redirect_url = WC()->cart->get_cart_url();
                }
            }
            wp_safe_redirect($redirect_url);
            exit;
        }
    }

    public static function recover_old_order_rac() {
// old order made as abandoned by update button
        if (isset($_GET['old_order'])) {
            $abandon_cart_id = $_GET['abandon_cart'];
            $email_template_id = $_GET['email_template'];
            global $wpdb;
            global $woocommerce;
            $table_name = $wpdb->prefix . 'rac_abandoncart';
            $last_cart = $wpdb->get_results("SELECT * FROM $table_name WHERE id = $abandon_cart_id", OBJECT);
            end($last_cart);
            $last_cart_key = key($last_cart);
            $expected_object = maybe_unserialize($last_cart[$last_cart_key]->cart_details);
            if (is_object($expected_object)) {
                $cart_details = $expected_object->get_items();
                if (get_option('rac_cart_content_when_cart_link_is_clicked') == 'yes') {
                    $woocommerce->cart->empty_cart();
                }

                foreach ($cart_details as $products) {
                    $product = get_product($products['product_id']);
//     if ($product->product_type == 'variation') {
                    if (!empty($products['variation_id'])) {
                        $variations = array();
                        foreach ($products['item_meta'] as $meta_name => $meta_value) {

                            $attributes = $product->get_variation_attributes();
                            $lower_case = array_change_key_case($attributes, CASE_LOWER);
                            if (!is_null($lower_case[$meta_name])) {
                                $value_true = in_array(strtolower($meta_value[0]), array_map('strtolower', $lower_case[$meta_name]));
                            } else {
                                $value_true = false;
                            }
                            if (in_array(strtolower($meta_name), array_map('strtolower', array_keys($attributes))) && $value_true) {
                                $variations[$meta_name] = $meta_value[0];
                            }
                        }
                        $woocommerce->cart->add_to_cart($products['product_id'], $products['qty'], $products['variation_id'], $variations);
// }
                    } else {
                        $woocommerce->cart->add_to_cart($products['product_id'], $products['qty']);
                    }
                }
            }
            setcookie("rac_cart_id", $abandon_cart_id, time() + 3600, "/");
            if (!empty($last_cart[$last_cart_key]->link_status)) {
                $email_template_ids_db = maybe_unserialize($last_cart[$last_cart_key]->link_status);
                if (!in_array($email_template_id, (array) $email_template_ids_db)) { //check for id duplication
                    $email_template_ids_db[] = $email_template_id;
                    $email_template_id_final = $email_template_ids_db;
                }
            } else {
                $email_template_id_final = array($email_template_id);
            }
            $email_template_id_final = maybe_serialize($email_template_id_final);
            $wpdb->update($table_name, array('link_status' => $email_template_id_final), array('id' => $abandon_cart_id));
            FPRacCounter::rac_do_linkc_count($abandon_cart_id, $email_template_id);
            if (get_option('rac_cartlink_redirect') == '2') {
                if (function_exists('wc_get_page_permalink')) {
                    $redirect_url = wc_get_page_permalink('checkout');
                } else {
                    $redirect_url = WC()->cart->get_checkout_url();
                }
            } else {
                if (function_exists('wc_get_page_permalink')) {
                    $redirect_url = wc_get_page_permalink('cart');
                } else {
                    $redirect_url = WC()->cart->get_cart_url();
                }
            }
            wp_safe_redirect($redirect_url);
            exit;
        }
    }

    public static function rac_cart_details($each_list) {
        ob_start();
        $cart_array = maybe_unserialize($each_list->cart_details);
        $total = '';
        if (is_array($cart_array) && is_null($each_list->ip_address)) {

            foreach ($cart_array as $cart) {
                foreach ($cart as $inside) {
                    foreach ($inside as $product) {
                        $total += $product['line_subtotal'];
                        $product_names[] = get_the_title($product['product_id']);
                    }
                }
            }
            echo implode(' , ', $product_names);
            echo " / " . FP_List_Table_RAC::format_price($total);
        } elseif (is_array($cart_array)) {
//for cart captured at checkout(GUEST)
            unset($cart_array['visitor_mail']);
            unset($cart_array['first_name']);
            unset($cart_array['last_name']);
            if (isset($cart_array['visitor_phone'])) {
                unset($cart_array['visitor_phone']);
            }
            foreach ($cart_array as $product) {
                $total += $product['line_subtotal'];
                $product_names[] = get_the_title($product['product_id']);
            }
            echo implode(' , ', $product_names);
            echo " / " . FP_List_Table_RAC::format_price($total);
        } elseif (is_object($cart_array)) { // For Guest
            foreach ($cart_array->get_items() as $item) {
                $total += $item['line_subtotal'];
                $product_names[] = $item['name'];
            }
            echo implode(' , ', $product_names);
            echo " / " . FP_List_Table_RAC::format_price($total);
        }
        return ob_get_clean();
    }

    public static function fp_rac_adandoncart_admin_display() {
        global $wpdb;
        $array = array();
        $table_name = $wpdb->prefix . 'rac_abandoncart';
        $count_trashed = 0;
        $new = $wpdb->get_results("SELECT * FROM $table_name WHERE cart_status IN('trash')", ARRAY_A);
        $count_trashed = count($new);
        $new1 = $wpdb->get_results("SELECT * FROM $table_name WHERE cart_status NOT IN('trash')", ARRAY_A);
        $count_all = count($new1);
        $post_from_date = "";
        $post_to_date = "";
        if (!isset($_GET['extend_cart'])) {
            if (!isset($_GET['section'])) {
                if (isset($_POST['filter_by_date']) || isset($_POST['export_cart_list'])) {
                    $post_from_date = $_POST['rac_from_date'];
                    $post_to_date = $_POST['rac_to_date'];
                    $fromdate = strtotime($post_from_date . ' 00:00:00');
                    $todate = strtotime($post_to_date . ' 23:59:59');
                    if (($post_from_date != "") && ($post_to_date != "")) {
                        $abandon_cart_list = $wpdb->get_results("SELECT * FROM $table_name WHERE cart_abandon_time >=$fromdate AND cart_abandon_time <=$todate AND cart_status NOT IN('trash')", OBJECT);
                        $abandon_count = count($wpdb->get_results("SELECT * FROM $table_name WHERE cart_abandon_time >=$fromdate AND cart_abandon_time <=$todate AND cart_status IN('ABANDON')", OBJECT));
                        $new_count = count($wpdb->get_results("SELECT * FROM $table_name WHERE cart_abandon_time >=$fromdate AND cart_abandon_time <=$todate AND cart_status IN('NEW')", OBJECT));
                        $recovered_count = count($wpdb->get_results("SELECT * FROM $table_name WHERE cart_abandon_time >=$fromdate AND cart_abandon_time <=$todate AND cart_status IN('RECOVERED')", OBJECT));
                    } elseif (($post_from_date != "") && ($post_to_date == "")) {
                        $abandon_cart_list = $wpdb->get_results("SELECT * FROM $table_name WHERE cart_abandon_time >=$fromdate AND cart_status NOT IN('trash')", OBJECT);
                        $abandon_count = count($wpdb->get_results("SELECT * FROM $table_name WHERE cart_abandon_time >=$fromdate AND cart_status IN('ABANDON')", OBJECT));
                        $new_count = count($wpdb->get_results("SELECT * FROM $table_name WHERE cart_abandon_time >=$fromdate AND cart_status IN('NEW')", OBJECT));
                        $recovered_count = count($wpdb->get_results("SELECT * FROM $table_name WHERE cart_abandon_time >=$fromdate AND cart_status IN('RECOVERED')", OBJECT));
                    } elseif (($post_from_date == "") && ($post_to_date != "")) {
                        $abandon_cart_list = $wpdb->get_results("SELECT * FROM $table_name WHERE cart_abandon_time <=$todate AND cart_status NOT IN('trash')", OBJECT);
                        $abandon_count = count($wpdb->get_results("SELECT * FROM $table_name WHERE cart_abandon_time <=$todate AND cart_status IN('ABANDON')", OBJECT));
                        $new_count = count($wpdb->get_results("SELECT * FROM $table_name WHERE cart_abandon_time <=$todate AND cart_status IN('NEW')", OBJECT));
                        $recovered_count = count($wpdb->get_results("SELECT * FROM $table_name WHERE cart_abandon_time <=$todate AND cart_status IN('RECOVERED')", OBJECT));
                    } else {
                        $abandon_cart_list = $wpdb->get_results("SELECT * FROM $table_name WHERE cart_status NOT IN('trash')", OBJECT);
                        $abandon_count = count($wpdb->get_results("SELECT * FROM $table_name WHERE cart_status IN('ABANDON')", OBJECT));
                        $new_count = count($wpdb->get_results("SELECT * FROM $table_name WHERE cart_status IN('NEW')", OBJECT));
                        $recovered_count = count($wpdb->get_results("SELECT * FROM $table_name WHERE cart_status IN('RECOVERED')", OBJECT));
                    }
                } else {
                    $abandon_cart_list = $wpdb->get_results("SELECT * FROM $table_name WHERE cart_status NOT IN('trash')", OBJECT);
                    $abandon_count = count($wpdb->get_results("SELECT * FROM $table_name WHERE cart_status IN('ABANDON')", OBJECT));
                    $new_count = count($wpdb->get_results("SELECT * FROM $table_name WHERE cart_status IN('NEW')", OBJECT));
                    $recovered_count = count($wpdb->get_results("SELECT * FROM $table_name WHERE cart_status IN('RECOVERED')", OBJECT));
                }
            } else {
                if ($_GET['section'] == 'trash') {
                    $abandon_cart_list = $wpdb->get_results("SELECT * FROM $table_name WHERE cart_status IN('trash')", OBJECT);
                }
            }
            echo '&nbsp<span><select id="rac_pagination_cart">';
            for ($k = 1; $k <= 20; $k++) {

                if ($k == 10) {
                    echo '<option value="' . $k . '" selected="selected">' . $k . '</option>';
                } else {
                    echo '<option value="' . $k . '">' . $k . '</option>';
                }
            }
            echo '</select></span>';

            echo '&nbsp<label>Search</label><input type="text" name="rac_temp_search" id="rac_temp_search">';
            ?>
            <br>
            <br>
            <a href="<?php echo esc_url_raw(remove_query_arg('section', get_permalink())); ?>"><?php _e("All ", 'recoverabandoncart'); ?></a><?php echo "($count_all)"; ?>
            |
            <a href="<?php echo esc_url_raw(add_query_arg('section', 'trash', get_permalink())); ?>"><?php _e("Trash ", 'recoverabandoncart'); ?></a><?php echo "($count_trashed)"; ?>
            <style type="text/css">
                .rac_tool_info .tooltip {
                    background: #1496bb;
                    color: #fff;
                    opacity: 0;
                }
                /* .rac_tool_info:hover .tooltip {
                    opacity: 1;
                }*/

            </style>
            <script type='text/javascript'>
                jQuery(function () {
                    jQuery('.rac_tool_info:not(.rac_content_get)').tipTip({'content': 'Click here to Edit Email ID for Guest'});
                });</script>
            <?php
            if (!isset($_GET['section'])) {

                echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label for="fromDate">From date:</label> <input type="text"  class="rac_date" id="rac_from_date"  name="rac_from_date" value="' . $post_from_date . '" />';

                echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label for="toDate">To date:</label> <input type="text" class="rac_date" id="rac_to_date" name="rac_to_date" value="' . $post_to_date . '" />';

                echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" class="filter_by_date button-primary" id="filter_by_date" name="filter_by_date" value=" Filter " />';

                echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" class="export_cart_list button-primary" id="export_cart_list" name="export_cart_list" value=" ' . __("Export CSV", "recoverabandoncart") . ' " />';

                if (isset($_POST['filter_by_date'])) {
                    echo '<br> Results by Date Filter<br>'
                    . ' All (' . count($abandon_cart_list) . ')&nbsp';
                    echo ' Abandon (' . $abandon_count . ')&nbsp';
                    echo ' New (' . $new_count . ')&nbsp';
                    echo ' Recovered (' . $recovered_count . ')';
                }
                $array = array();
                foreach ($abandon_cart_list as $each_cart_list) {
                    $each_cart_list1 = (array) $each_cart_list;
                    $new_array = array();
                    foreach ($each_cart_list1 as $key => $cart_list) {
                        if ($key != 'cart_details') {
                            $new_array[$key] = $cart_list;
                        } else {
                            ob_start();
                            echo RecoverAbandonCart::rac_cart_details($each_cart_list);
                            $string = ob_get_clean();
                            $string1 = str_replace(' ', '', html_entity_decode(strip_tags($string)));
                            $new_array[$key] = $string1;
                        }
                    }
                    array_push($array, $new_array);
                }
                if (isset($_POST['export_cart_list'])) {
                    ob_end_clean();
                    header("Content-type: text/csv");
                    header("Content-Disposition: attachment; filename=rac_cartlist" . date("Y-m-d H:i:s") . ".csv");
                    header("Pragma: no-cache");
                    header("Expires: 0");
                    $output = fopen("php://output", "w");
                    $row_heading = array('id', 'cart_details', 'user_id', 'email_id', 'cart_abandon_time', 'cart_status', 'mail_template_id', 'ip_address', 'link_status', 'sending_status', 'wpml_lang', 'placed_order', 'completed', 'old_status');
                    fputcsv($output, $row_heading); // here you can change delimiter/enclosure
                    foreach ($array as $row) {
                        fputcsv($output, $row); // here you can change delimiter/enclosure
                    }
                    fclose($output);
                    exit();
                }
            }



            echo '<table class="rac_email_template_table_abandon table" data-page-size="10" data-filter="#rac_temp_search" data-filter-minimum="1">';
            echo '<thead>
		<tr>
			<th data-type="numeric">' . __('ID', 'recoverabandoncart') . '</th>
			<th data-hide="phone">' . __('Cart Details / Cart Total', 'recoverabandoncart') . '</th>
			<th>' . __('UserName / First Last Name', 'recoverabandoncart') . '</th>
                        <th>' . __('Email ID / Phone Number', 'recoverabandoncart') . '</th>
                        <th data-type="numeric">' . __('Abandoned Date/Time', 'recoverabandoncart') . '</th>
                        <th>' . __('Status', 'recoverabandoncart') . '</th>
                        <th>' . __('Email Template / Email Status / Cart Link in Email', 'recoverabandoncart') . '</th>
                        <th data-type="numeric">' . __('Recovered Order ID', 'recoverabandoncart') . '</th>
                        <th >' . __('Coupon Used', 'recoverabandoncart') . '</th>
                        <th >' . __('Payment Status', 'recoverabandoncart') . '</th>
                        <th>' . __('Mail Sending', 'recoverabandoncart') . '</th>';

            $main_trash_data = 'trash';
// Check the Status
            if (!isset($_GET['section'])) {
                $main_trash_data = 'trash';
            } else {
                if ($_GET['section'] == 'trash') {
                    $main_trash_data = 'permanent';
                }
            }

            echo '<th class="rac_small_col" data-sort-ignore="true"><a href="#" id="rac_page_select">' . __('Page Select', 'recoverabandoncart') . '</a>&nbsp/&nbsp<a href="#" id="rac_page_deselect">' . __('Page Deselct', 'recoverabandoncart') . '</a>&nbsp</br>&nbsp<a href="#" id="rac_sel">' . __('Select All', 'recoverabandoncart') . '</a>&nbsp/&nbsp <a href="#" id="rac_desel">' . __('Deselct All', 'recoverabandoncart') . '</a>&nbsp'
            . '<a href="#" class="rac_selected_del button" data-deletion=' . $main_trash_data . '>' . __('Delete Selected', 'recoverabandoncart') . '</a>&nbsp';
            if (!isset($_GET['section'])) {
                echo '<a href="#" id="rac_selected_mail" class="button button-primary">' . __('Send Mail for Selected', 'recoverabandoncart') . '</a>';
            } else {
                ?>
                <a href="#" class="rac_selected_del button" data-deletion="restore"><?php echo "Restore Selected"; ?></a>
                <?php
            }
            echo '</th>
		</tr>
	</thead>';
            ?>
            <tbody>
                <?php
                foreach ($abandon_cart_list as $each_list) {
                    ?>
                    <tr>
                        <td data-value="<?php echo $each_list->id; ?>"><?php echo $each_list->id; ?></td>
                        <td>
                            <?php
                            $admin_url = admin_url('admin.php');
                            $new_template_url = esc_url_raw(add_query_arg(array('page' => 'fprac_slug', 'tab' => 'fpractable', 'extend_cart' => $each_list->id), $admin_url));
                            echo '<a style="text-decoration: none;" href="' . $new_template_url . '">' . RecoverAbandonCart::rac_cart_details($each_list) . '</a>';
                            ?>
                        </td>
                        <td>
                            <?php
                            $user_info = get_userdata($each_list->user_id);
                            $cart_array = maybe_unserialize($each_list->cart_details);
                            if (is_object($user_info)) {
                                echo $user_info->user_login;
                                echo " / $user_info->user_firstname $user_info->user_lastname";
                            } elseif ($each_list->user_id == '0') {
                                if (is_array($cart_array)) {
                                    //for cart captured at checkout(GUEST)
                                    $first_name = $cart_array['first_name'];
                                    $last_name = $cart_array['last_name'];
                                    $guest_first_last = " / $first_name $last_name";

                                    unset($cart_array['visitor_mail']);
                                    unset($cart_array['first_name']);
                                    unset($cart_array['last_name']);
                                    if (isset($cart_array['visitor_phone'])) {
                                        unset($cart_array['visitor_phone']);
                                    }
                                } elseif (is_object($cart_array)) { // For Guest
                                    $guest_first_last = " / $cart_array->billing_first_name $cart_array->billing_last_name";
                                }
                                echo 'Guest';
                                echo $guest_first_last;
                            } elseif ($each_list->user_id == 'old_order') {

                                $old_order_cart_ob = maybe_unserialize($each_list->cart_details);
                                if (is_array($old_order_cart_ob)) {
                                    //for cart captured at checkout(GUEST)
                                    $first_name = $old_order_cart_ob['first_name'];
                                    $last_name = $old_order_cart_ob['last_name'];
                                    $guest_first_last = " / $first_name $last_name";

                                    unset($old_order_cart_ob['visitor_mail']);
                                    unset($old_order_cart_ob['first_name']);
                                    unset($old_order_cart_ob['last_name']);
                                    if (isset($old_order_cart_ob['visitor_phone'])) {
                                        unset($old_order_cart_ob['visitor_phone']);
                                    }
                                } elseif (is_object($old_order_cart_ob)) { // For Guest
                                    $guest_first_last = " / $old_order_cart_ob->billing_first_name $old_order_cart_ob->billing_last_name";
                                }
                                $user_inf = get_userdata($old_order_cart_ob->user_id);
                                if (is_object($user_inf)) {
                                    echo $user_inf->user_login;
                                    echo " / $user_inf->user_firstname $user_inf->user_lastname";
                                } else {
                                    echo 'Guest';
                                    echo $guest_first_last;
                                }
                            }
                            ?></td>
                        <td>
                            <?php
                            if ('0' == $each_list->user_id) {
                                $details = maybe_unserialize($each_list->cart_details);
                                if (is_object($details)) {
                                    ?> <div class="rac_tool_info"><p class="rac_edit_option" data-id="<?php echo $each_list->id; ?>" >
                                    <?php
                                    echo $details->billing_email; // Order Object. Works for both old order and rac captured order
                                    ?></p><div class="tooltip">Double Click to Change an Editable</div></div><?php
                                            echo '</br>&nbsp' . $details->billing_phone;
                                        } elseif (is_array($details)) {
                                            ?><div class="rac_tool_info"><p class="rac_edit_option" data-id="<?php echo $each_list->id; ?>">
                                            <?php
                                            // echo $each_list->email_id; //checkout order
                                            echo $details['visitor_mail']; //checkout order
                                            ?></p><div class="tooltip">Double Click to Change an Editable</div></div><?php
                                            echo "</br>&nbsp";
                                            if (isset($details['visitor_phone'])) {
                                                echo $details['visitor_phone'];
                                            } else {
                                                echo '-';
                                            }
                                        }
                                    } elseif ($each_list->user_id == 'old_order') {
                                        $old_order_cart_ob = maybe_unserialize($each_list->cart_details);
                                        $user_inf = get_userdata($old_order_cart_ob->user_id);
                                        if (is_object($user_inf)) {
                                            echo $user_inf->user_email;
                                            echo " / $user_inf->billing_phone";
                                        } else {
                                            echo 'Guest';
                                            if (isset($old_order_cart_ob->billing_email)) {
                                                echo " /" . $old_order_cart_ob->billing_email;
                                            } else {
                                                echo '-';
                                            }
                                        }
                                    } else {
                                        $user_infor = get_userdata($each_list->user_id);

                                        if (is_object($user_infor)) {
                                            echo $user_infor->user_email;
                                            echo '</br> &nbsp' . $user_infor->billing_phone;
                                        }
                                    }
                                    ?>
                        </td>
                        <td data-value="<?php echo $each_list->cart_abandon_time; ?>">
                            <?php echo date(get_option('date_format'), $each_list->cart_abandon_time) . '/' . date(get_option('time_format'), $each_list->cart_abandon_time); ?>
                        </td>
                        <td><?php echo $each_list->cart_status == 'trash' ? 'Trashed' : $each_list->cart_status; ?></td>
                        <td>
                            <?php
                            $mail_sent = maybe_unserialize($each_list->mail_template_id);
                            $email_table_name_clicked = $wpdb->prefix . 'rac_templates_email';
                            $email_template_all = $wpdb->get_results("SELECT * FROM $email_table_name_clicked");
                            foreach ($email_template_all as $check_all_email_temp) {
                                echo $check_all_email_temp->template_name;
                                //Mail Sent
                                if (!is_null($mail_sent)) {
                                    if (in_array($check_all_email_temp->id, (array) $mail_sent)) {
                                        echo ' / Email Sent';
                                    } else {
                                        echo ' / Email Not Sent';
                                    }
                                } else {
                                    echo ' / Email Not Sent';
                                }
                                //Mail Sent END
                                //Link Clicked
                                if (!empty($each_list->link_status)) {
                                    $mails_clicked = maybe_unserialize($each_list->link_status);
                                    if (in_array($check_all_email_temp->id, (array) $mails_clicked)) {
                                        //  echo $check_all_email_temp->template_name;
                                        echo ' / Cart Link Clicked';
                                        echo '<br>';
                                    } else {
                                        // echo $check_all_email_temp->template_name;
                                        echo ' / Cart Link Not Clicked';
                                        echo '<br>';
                                    }
                                } else {
                                    echo ' / Cart Link Not Clicked';
                                    echo '<br>';
                                }
                                //Link Clicked END
                            }
                            ?>
                        </td>
                        <td data-value="<?php echo $each_list->placed_order; ?>"><?php echo (!is_null($each_list->placed_order) ? ' #' . $each_list->placed_order . '' : 'Not Yet'); ?></td>
                        <td>
                            <?php
                            if ($each_list->cart_status == 'RECOVERED') {
                                $coupon_code = get_option('abandon_time_of' . $each_list->id);
                                $order = new WC_Order($each_list->placed_order);
                                $coupons_used = $order->get_used_coupons();
                                if (!empty($coupons_used)) {
                                    if (in_array($coupon_code, $order->get_used_coupons())) {
                                        echo $coupon_code . ' - ';
                                        echo 'Succes';
                                    } else {
                                        echo 'Not Used';
                                    }
                                } else {
                                    echo 'Not Used';
                                }
                            } else {
                                echo 'Not Yet';
                            }
                            ?>
                        </td>
                        <td><?php echo (!empty($each_list->completed) ? 'Completed' : 'Not Yet'); ?></td>
                        <td>
                            <?php
                            if ($each_list->cart_status != 'trash') {
                                if (empty($each_list->completed)) {

                                    //check if order completed,if completed don't show mail sending button'
                                    ?>

                                    <a href="#" class="button rac_mail_sending" data-racmoptid="<?php echo $each_list->id; ?>" data-currentsate="<?php echo $each_list->sending_status == 'SEND' ? 'SEND' : 'DONT' ?>"><?php
                                        if ($each_list->sending_status == 'SEND') {
                                            echo 'Stop Mailing';
                                        } else {
                                            echo 'Start Mailing';
                                        }
                                        echo "</a>";
                                    } else {
                                        echo 'Recovered';
                                    }
                                } else {
                                    echo "Trashed";
                                }
                                ?></td>
                        <td class="bis_mas_small_col">
                            <input type="checkbox" class="rac_checkboxes" data-racid="<?php echo $each_list->id; ?>"/>
                            <a href="#" class="button rac_check_indi" data-deletion="<?php echo $main_trash_data; ?>" data-racdelid="<?php echo $each_list->id; ?>"><?php echo $each_list->cart_status != 'trash' ? "Delete this Row" : "Delete Permanently"; ?></a>
                            <?php if ($each_list->cart_status == 'trash') { ?>
                                <a href="#" class="button rac_check_indi" data-deletion="restore" data-racdelid="<?php echo $each_list->id; ?>"><?php _e('Restore', 'recoverabandoncart'); ?></a>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php
                }
                echo '</tbody>
            <tfoot>
		<tr>
			<td colspan="12">
				<div class="pagination pagination-centered hide-if-no-paging"></div>
			</td>
		</tr>
	</tfoot></table><style>.footable > tbody > tr > td,.footable > thead > tr > th, .footable > thead > tr > td{text-align:center;}</style>';
                ?>
            <script type="text/javascript">
                jQuery(document).ready(function () {
                    //Manual Mail redirection
                    jQuery('#rac_selected_mail').click(function (e) {
                        e.preventDefault();
                        var selection_for_mail = new Array();
                        jQuery('.rac_checkboxes').each(function (num) {
                            if (jQuery(this).prop('checked')) {
                                selection_for_mail.push(jQuery(this).data('racid'));
                            }
                        });
                        // console.log(jQuery('.bis_mas_checkboxes'));
                        console.log(selection_for_mail);
                        var url_without_data = "<?php echo esc_url_raw(add_query_arg(array('page' => 'fprac_slug', 'tab' => 'fpracemail'), admin_url('admin.php'))); ?>";
                        var url_data = url_without_data + "&rac_send_email=" + selection_for_mail;
                        console.log(url_without_data);
                        console.log(url_data);
                        if (selection_for_mail.length > 0) {
                            window.location.replace(url_data);
                        } else {
                            alert("Select a row to mail");
                        }

                        //window.location.replace("<?php echo add_query_arg(array('page' => 'fprac_slug', 'tab' => 'fpracemail', 'rac_send_email' => 'template'), admin_url('admin.php')); ?>");
                    });
                });
                //save editable table
                jQuery(document).ready(function () {
                    jQuery(".rac_edit_option").dblclick(function (e) {
                        jQuery(this).next().remove();
                        jQuery(this).parent().removeAttr('class');
                        var p = jQuery(this).text();
                        var value = jQuery('<div class="raceditemail"><textarea class="rac_content_get" name="one" style="width:200px;height:100px;">' + p + '</textarea></br><input class="rac_save" type="button" value="save"/></div>');
                        var one = jQuery('.rac_content_get').val();
                        var id = jQuery(this).attr('data-id');
                        jQuery('.rac_content_get').parent().html(one);
                        //jQuery('.test').remove();
                        jQuery(this).empty();
                        jQuery(this).append(value);
                        jQuery(".rac_save").click(function () {
                            jQuery(".rac_save").prop("disabled", true);
                            var email = jQuery('.rac_content_get').val();
                            var data = {
                                action: "edit_value_update_now",
                                email: email,
                                id: id
                            }

                            jQuery.ajax({
                                type: "POST",
                                url: ajaxurl,
                                data: data
                            }).done(function (response) {
                                jQuery(".rac_save").prop("disabled", false);
                                var p = jQuery(this).text();
                                var value = jQuery('<div class="raceditemail"><textarea class="rac_content_get" name="one" style="width:200px;height:100px;">' + p + '</textarea></br><input class="rac_save" type="button" value="save"/></div>');
                                var one = jQuery('.rac_content_get').val();
                                var id = jQuery(this).attr('data-id');
                                jQuery('.rac_content_get').parent().html(one);
                                jQuery(this).parent().parent().parent().addClass('rac_tool_info');
                            });
                        });
                    });
                });</script>
            <?php
        } else {
            $cart_id = $_GET['extend_cart'];
            $table_name = $wpdb->prefix . 'rac_abandoncart';
            $cart_list = $wpdb->get_results("SELECT * FROM $table_name WHERE id = $cart_id");
            foreach ($cart_list as $eachlist) {
                echo rac_show_cart_products_brief($eachlist);
            }
            $admin_url = admin_url('admin.php');
            if (isset($_SERVER['HTTP_REFERER'])) {
                if (strpos($_SERVER['HTTP_REFERER'], 'section=trash') !== false) {
                    $new_template_url = esc_url_raw(add_query_arg(array('page' => 'fprac_slug', 'tab' => 'fpractable', 'section' => 'trash'), $admin_url));
                } else {
                    $new_template_url = esc_url_raw(add_query_arg(array('page' => 'fprac_slug', 'tab' => 'fpractable'), $admin_url));
                }
            } else {
                $new_template_url = esc_url_raw(add_query_arg(array('page' => 'fprac_slug', 'tab' => 'fpractable'), $admin_url));
            }
            echo '<br><a href="' . $new_template_url . '" style="text-decoration:none"><input class="button-primary" type="button" value="' . __('Back to Cart List', 'recoverabandoncart') . '"</a>';
        }
    }

    public static function fp_rac_mail_logs_display() {
        global $wpdb;
        $table_name_logs = $wpdb->prefix . 'rac_email_logs';
        $email_template_table = $wpdb->prefix . 'rac_templates_email';
        $rac_log_list = $wpdb->get_results("SELECT * FROM $table_name_logs", OBJECT);
        echo '&nbsp<span><select id="rac_pagination_logs">';
        for ($k = 1; $k <= 20; $k++) {

            if ($k == 10) {
                echo '<option value="' . $k . '" selected="selected">' . $k . '</option>';
            } else {
                echo '<option value="' . $k . '">' . $k . '</option>';
            }
        }
        '</select></span>';

        echo '&nbsp<label>Search</label><input type="text" name="rac_temp_search" id="rac_temp_search">';

        echo '<table class="rac_email_logs_table table" data-page-size="10" data-filter="#rac_temp_search" data-filter-minimum="1">';
        echo '<thead>
		<tr>
                <th>' . __('S.No', 'recoverabandoncart') . '</th>
			<th>' . __('Email Id', 'recoverabandoncart') . '</th>
                        <th>' . __('Date/Time', 'recoverabandoncart') . '</th>
			<th>' . __('Template Used', 'recoverabandoncart') . '</th>
                        <th>' . __('Abandoned Cart ID', 'recoverabandoncart') . '</th>
                        <th class="rac_small_col" data-sort-ignore="true"><a href="#" id="rac_sel">' . __('Select All', 'recoverabandoncart') . '</a>&nbsp/&nbsp <a href="#" id="rac_desel">' . __('Deselct All', 'recoverabandoncart') . '</a>&nbsp<a href="#" id="rac_selected_del_log" class="button">' . __('Delete Selected', 'recoverabandoncart') . '</a></th>
		</tr>
	</thead><tbody>';
        $srno = 1;
        foreach ($rac_log_list as $each_log) {
            echo "<tr>";
            echo "<td data-value=" . $srno . ">";
            echo $srno;
            echo "</td>";
            echo "<td>";
            echo $each_log->email_id;
            echo "</td>";
            echo "<td data-value=" . $each_log->date_time . ">";
            echo date(get_option('date_format'), $each_log->date_time) . '/' . date(get_option('time_format'), $each_log->date_time);
            echo "</td>";
            echo "<td>";
            $template_id = $each_log->template_used;
            $manual_mail = strpos($template_id, "Manual");
            if ($manual_mail !== false) {
                $template_id = explode("-", $template_id);
                $template_name = $wpdb->get_results("SELECT template_name FROM $email_template_table WHERE id=$template_id[0]", OBJECT);
                echo $template_name[0]->template_name . '(#' . $each_log->template_used . ')';
            } else {
                $template_name = $wpdb->get_results("SELECT template_name FROM $email_template_table WHERE id=$template_id", OBJECT);
                echo $template_name[0]->template_name;
            }

            echo "</td>";
            echo "<td data-value=" . $each_log->rac_cart_id . ">";
            echo $each_log->rac_cart_id;
            echo "</td>";
            ?>
            <td class="bis_mas_small_col">
                <input type="checkbox" class="rac_checkboxes" data-raclogid="<?php echo $each_log->id; ?>"/>
                <a href="#" class="button rac_check_indi" data-raclogdelid="<?php echo $each_log->id; ?>">Delete this Row</a>

            </td>
            <?php
            $srno++; //for serial number
        }
        echo '</tbody>
            <tfoot>
		<tr>
			<td colspan="6">
				<div class="pagination pagination-centered hide-if-no-paging"></div>
			</td>
		</tr>
	</tfoot></table><style>.footable > tbody > tr > td,.footable > thead > tr > th, .footable > thead > tr > td{text-align:center;}</style>';
    }

    public static function fp_rac_admin_scritps() {
        wp_register_style('footable_css', plugins_url('/css/footable.core.css', __FILE__));

        wp_enqueue_style('footable_css');
        wp_register_style('footablestand_css', plugins_url('/css/footable.standalone.css', __FILE__));
        wp_enqueue_style('footablestand_css');

        wp_enqueue_script('footable', plugins_url('/js/footable.js', __FILE__), array('jquery'));
        wp_enqueue_script('footable_sorting', plugins_url('/js/footable.sort.js', __FILE__), array('jquery'));
        wp_enqueue_script('footable_paginate', plugins_url('/js/footable.paginate.js', __FILE__), array('jquery'));
        wp_enqueue_script('footable_filter', plugins_url('/js/footable.filter.js', __FILE__), array('jquery'));
        wp_enqueue_script('footable_initialize', plugins_url('/js/footable_initialize.js', __FILE__), array('jquery'));

        wp_enqueue_script('racjscolorpicker', plugins_url('/jscolor/jscolor.js', __FILE__), array('jquery'));
//datepicker
        wp_enqueue_style('jquery_smoothness_ui', plugins_url('/css/jquery_smoothness_ui.css', __FILE__));
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script('date_picker_initialize', plugins_url('/js/rac_datepicker.js', __FILE__), array('jquery', 'jquery-ui-datepicker'));
    }

//Check Additional More Function to cross check whatever order contain cart products
    public static function fp_rac_check_cart_list_manual_recovery($order_id, $orderplaced) {
        if (get_option('rac_cartlist_new_abandon_recover', true) == 'yes') {

            $allow_manual_order = get_option('rac_cartlist_new_abandon_recover_by_manual_order', true);
            if ($allow_manual_order == 'no') {
                if (get_post_meta($order_id, '_created_via', true) == 'checkout') {
// Run
                } else {
                    return false;
                }
            }
            global $wpdb;
            $order = new WC_Order($order_id);
            $billing_email = $order->billing_email;

            $table_name = $wpdb->prefix . 'rac_abandoncart';
//Gather Results
            $user_id = $order->user_id; // Previously it was get_current_user_id(); if admin manually recover the cart by making order completed then admin cart will recover (it is a bug) it should be the person cart.
            $user_details = get_userdata($user_id);
            if ($user_details) {
                $user_email = $user_details->user_email;
            } else {
                $user_email = $billing_email;
            }

            $newstatus = get_option('rac_cartlist_change_from_new_to_recover') == 'yes' ? "1" : "0";
            $abandonstatus = get_option('rac_cartlist_change_from_abandon_to_recover') == 'yes' ? "1" : "0";


            if ($newstatus == '1' && $abandonstatus == '1') {
// If both are true
                $status = array("ABANDON", "NEW");
            } elseif ($newstatus == '1' && $abandonstatus == '0') {
                $status = array("NEW");
            } elseif ($newstatus == '0' && $abandonstatus == '1') {
                $status = array("ABANDON");
            } else {
                $status = array("0");
            }
            $status = '"' . implode('","', $status) . '"';

            if ($user_email != $billing_email) {
                $results = $wpdb->get_results("SELECT * FROM $table_name WHERE `email_id`='$user_email' and `cart_status` IN ($status)", ARRAY_A);
            } else {
                $results = $wpdb->get_results("SELECT * FROM $table_name WHERE `email_id`='$billing_email' and `cart_status` IN ($status)", ARRAY_A);
            }

            if (!empty($results)) {
                foreach ($results as $key => $value) {
                    $rac_order = $value['id'];

                    if ($orderplaced == '1') {
                        $wpdb->update($table_name, array('placed_order' => $order_id), array('id' => $rac_order));
                    }
                    if ($orderplaced == '2') {
                        $order_placed = $value['placed_order'];
                        $order_placed = $order_placed ? $order_placed : $order_id;
                        $wpdb->update($table_name, array('completed' => 'completed'), array('id' => $rac_order));
                        $wpdb->update($table_name, array('cart_status' => 'RECOVERED', 'placed_order' => $order_placed), array('id' => $rac_order));
                        if (get_option('already_recovered' . $order_id) != 'yes') {
                            FPRacCounter::rac_do_recovered_count();
                            $link_status = $value['link_status'];
                            $mail_template_ids = maybe_unserialize($link_status);
                            $mail_template_id = $mail_template_ids[0];
                            FPRacCounter::rac_recovered_count_by_mail_template($mail_template_id);
                            update_option('already_recovered' . $order_id, 'yes');
                        }
//mailing admin on order recover
                        RecoverAbandonCart::fp_rac_mail_admin_cart_recovered($order_id);
                        if (get_option('already_recorded_this_' . $order_id) != 'yes') {
                            FPRacCounter::record_order_id_and_cart_id($order_id);
                            update_option('already_recorded_this_' . $order_id, 'yes');
                        }
                    }
                }
            }
        }
    }

//Updating for recovered cart which placed order
    public static function fp_rac_cookies_for_cart_recover($order_id) {
        if (isset($_COOKIE['rac_cart_id'])) {
            $row_id = $_COOKIE['rac_cart_id'];
            global $wpdb;
            $abandon_cart_table = $wpdb->prefix . 'rac_abandoncart';
            $abandon_cart_list = $wpdb->get_results("SELECT * FROM $abandon_cart_table WHERE cart_status NOT IN('trash') AND id IN ($row_id)", OBJECT);
            $mail_template_ids = maybe_unserialize($abandon_cart_list[0]->link_status);
            $mail_template_id = $mail_template_ids[0];
            $wpdb->update($abandon_cart_table, array('placed_order' => $order_id), array('id' => $row_id));
            update_post_meta($order_id, 'rac_order_placed', $row_id);
//counter
            if (get_option('already_recovered' . $order_id) != 'yes') {
                FPRacCounter::rac_do_recovered_count();
                FPRacCounter::rac_recovered_count_by_mail_template($mail_template_id);
                update_option('already_recovered' . $order_id, 'yes');
            }
            if (get_option('already_recorded_this_' . $order_id) != 'yes') {
                FPRacCounter::record_order_id_and_cart_id($order_id);
                update_option('already_recorded_this_' . $order_id, 'yes');
            }
            RecoverAbandonCart::fp_rac_mail_admin_cart_recovered($order_id); //mailing admin on order recover
//            unset($_COOKIE['rac_cart_id']);
//            setcookie("rac_cart_id", null, -1, "/");
        } else {
            $order_placed = '1';
            self::fp_rac_check_cart_list_manual_recovery($order_id, $order_placed);
        }
    }

    public static function clear_cookie($orderid) {
        if (isset($_COOKIE['rac_cart_id'])) {
            unset($_COOKIE['rac_cart_id']);
            setcookie("rac_cart_id", null, -1, "/");
        }
    }

    public static function fp_rac_mail_admin_cart_recovered($order_id) {

        if (get_option('rac_admin_cart_recovered_noti') == "yes") {
            $to = get_option('rac_admin_email');
            $subject = get_option('rac_recovered_email_subject');
            $message = get_option('rac_recovered_email_message');
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            if (get_option('rac_recovered_sender_opt') == "woo") {
                $headers .= FPRacCron::rac_formatted_from_address_woocommerce();
                $headers .= "Reply-To: " . get_option('woocommerce_email_from_name') . " <" . get_option('woocommerce_email_from_address') . ">\r\n";
                $html_template = 'HTML';
            } else {
                $headers .= FPRacCron::rac_formatted_from_address_local(get_option('rac_recovered_from_name'), get_option('rac_recovered_from_email'));
                $headers .= "Reply-To: " . get_option('rac_recovered_from_name') . " <" . get_option('rac_recovered_from_email') . ">\r\n";
                $html_template = 'PLAIN';
            }
            $message = str_replace('{rac.recovered_order_id}', $order_id, $message); //replacing shortcode for order id
            ob_start();
            $order = new WC_Order($order_id);
            ?>
            <table cellspacing="0" cellpadding="6" style="width: 100%; border: 1px solid #eee;" border="1" bordercolor="#eee">
                <thead>
                    <tr>
                        <th scope="col" style="text-align:left; border: 1px solid #eee;"><?php _e('Product', 'woocommerce'); ?></th>
                        <th scope="col" style="text-align:left; border: 1px solid #eee;"><?php _e('Quantity', 'woocommerce'); ?></th>
                        <th scope="col" style="text-align:left; border: 1px solid #eee;"><?php _e('Price', 'woocommerce'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php echo $order->email_order_items_table(false, true); ?>
                </tbody>
                <tfoot>
                    <?php
                    if ($totals = $order->get_order_item_totals()) {
                        $i = 0;
                        foreach ($totals as $total) {
                            $i++;
                            ?><tr>
                                <th scope="row" colspan="2" style="text-align:left; border: 1px solid #eee; <?php if ($i == 1) echo 'border-top-width: 4px;'; ?>"><?php echo $total['label']; ?></th>
                                <td style="text-align:left; border: 1px solid #eee; <?php if ($i == 1) echo 'border-top-width: 4px;'; ?>"><?php echo $total['value']; ?></td>
                            </tr><?php
                        }
                    }
                    ?>
                </tfoot>
            </table>

            <?php
            $newdata = ob_get_clean();

            ob_start();
            $message = str_replace('{rac.order_line_items}', $newdata, $message);
            if (function_exists('wc_get_template')) {
                wc_get_template('emails/email-header.php', array('email_heading' => $subject));
                echo $message;
                wc_get_template('emails/email-footer.php');
            } else {
                woocommerce_get_template('emails/email-header.php', array('email_heading' => $subject));
                echo $message;
                woocommerce_get_template('emails/email-footer.php');
            }
            $woo_temp_msg = ob_get_clean();
            if ('wp_mail' == get_option('rac_trouble_mail')) {
                FPRacCron::rac_send_wp_mail($to, $subject, $woo_temp_msg, $headers, $html_template);
            } else {
                FPRacCron::rac_send_mail($to, $subject, $woo_temp_msg, $headers);
            }
        }
    }

    public static function fp_rac_check_order_status($order_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'rac_abandoncart';
        $rac_order = get_post_meta($order_id, 'rac_order_placed', true);
        if (!empty($rac_order)) {
            $wpdb->update($table_name, array('completed' => 'completed'), array('id' => $rac_order));
            $wpdb->update($table_name, array('cart_status' => 'RECOVERED'), array('id' => $rac_order));
        }
        $order_placed = '2';
        self::fp_rac_check_cart_list_manual_recovery($order_id, $order_placed);
    }

    public static function rac_translate_file() {
        load_plugin_textdomain('recoverabandoncart', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    public static function fp_rac_insert_guest_entry($order_id) {

        if (!is_user_logged_in()) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'rac_abandoncart';
            if (function_exists('icl_register_string')) {
                $currentuser_lang = isset($_SESSION['wpml_globalcart_language']) ? $_SESSION['wpml_globalcart_language'] : ICL_LANGUAGE_CODE;
            } else {
                $currentuser_lang = 'en';
            }
            if (!isset($_COOKIE['rac_cart_id']) && !isset($_REQUEST['token']) && !isset($_COOKIE['rac_checkout_entry'])) { // We can remove cookie check if we want
                $order = new WC_Order($order_id);

                $user_email = $order->billing_email;

                if (get_option('rac_remove_carts') == 'yes') {


                    if (get_option('rac_remove_new') == 'yes') {

                        $wpdb->delete($table_name, array('email_id' => $user_email, 'cart_status' => 'NEW'));
                    }

                    if (get_option('rac_remove_abandon') == 'yes') {

                        $wpdb->delete($table_name, array('email_id' => $user_email, 'cart_status' => 'ABANDON'));
                    }
                }


                $check_cart = $wpdb->get_results("SELECT * FROM $table_name WHERE user_id=0 ORDER BY id DESC LIMIT 1", OBJECT);
                $db_cart_content = maybe_unserialize($check_cart[0]->cart_details);
                if (empty($db_cart_content)) {// IF no previous entry make a new
                    $user_id = "0";
                    $current_time = current_time('timestamp');
                    $cart_content = maybe_serialize($order);
                    if (self::restrict_entries_in_cart_list($user_email) == 'proceed') {
                        $wpdb->insert($table_name, array('cart_details' => $cart_content, 'user_id' => $user_id, 'email_id' => $user_email, 'cart_abandon_time' => $current_time, 'cart_status' => 'NEW', 'wpml_lang' => $currentuser_lang));
                    }
                    update_post_meta($order->id, 'guest_cart', 'yes');
                } else {
                    if (is_object($db_cart_content)) {
                        if ($db_cart_content->id != $order->id) { // don't allow if they refresh again || if already exist
                            $current_time = current_time('timestamp');
// $order_products = $order->get_items();
                            $cart_content = maybe_serialize($order);
                            $user_id = "0";
                            $wpdb->insert($table_name, array('cart_details' => $cart_content, 'user_id' => $user_id, 'email_id' => $user_email, 'cart_abandon_time' => $current_time, 'cart_status' => 'NEW', 'wpml_lang' => $currentuser_lang));
                            update_post_meta($order->id, 'guest_cart', 'yes');
                        }
                    } else {
//create after checkout cart
                        $current_time = current_time('timestamp');
                        $cart_content = maybe_serialize($order);
                        $user_id = "0";
                        $wpdb->insert($table_name, array('cart_details' => $cart_content, 'user_id' => $user_id, 'email_id' => $user_email, 'cart_abandon_time' => $current_time, 'cart_status' => 'NEW', 'wpml_lang' => $currentuser_lang));
                        update_post_meta($order->id, 'guest_cart', 'yes');
                    }
                }
            } elseif (isset($_COOKIE['rac_checkout_entry']) && !isset($_COOKIE['rac_cart_id'])) {

//Check cookies for deleting cart captured from checkout
//Delete only if it is not recoverd from mail

                $delete_id = $_COOKIE['rac_checkout_entry'];
                $wpdb->delete($table_name, array('id' => $delete_id));
//delete entry
            }
        }
    }

    public static function fp_rac_order_status_guest($order_id, $old, $new_status) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'rac_abandoncart';
        $check_guest_cart = get_post_meta($order_id, 'guest_cart', true);
        if ($check_guest_cart == 'yes') {
            if (get_option('rac_guest_abadon_type_processing') == 'yes') { //option selected by user
                if ($new_status == 'processing') {
                    $get_list = $wpdb->get_results("SELECT * FROM $table_name WHERE user_id='0'", OBJECT);
                    foreach ($get_list as $each_entry) {
                        $expected_object = maybe_unserialize($each_entry->cart_details);
                        if (is_object($expected_object)) {
                            if ($expected_object->id == $order_id) {
                                $wpdb->delete($table_name, array('id' => $each_entry->id));
                            }
                        }
                    }
                }
            } if (get_option('rac_guest_abadon_type_completed') == 'yes') {//option selected by user
                if ($new_status == 'completed') {
                    $get_list = $wpdb->get_results("SELECT * FROM $table_name WHERE user_id='0'", OBJECT);
                    foreach ($get_list as $each_entry) {
                        $expected_object = maybe_unserialize($each_entry->cart_details);
                        if (is_object($expected_object)) {
                            if ($expected_object->id == $order_id) {
                                $wpdb->delete($table_name, array('id' => $each_entry->id));
                            }
                        }
                    }
                }
            }
//rac_guest_abadon_type_pending
            if (get_option('rac_guest_abadon_type_pending') == 'yes') {//option selected by user
                if ($new_status == 'pending') {
                    $get_list = $wpdb->get_results("SELECT * FROM $table_name WHERE user_id='0'", OBJECT);
                    foreach ($get_list as $each_entry) {
                        $expected_object = maybe_unserialize($each_entry->cart_details);
                        if (is_object($expected_object)) {
                            if ($expected_object->id == $order_id) {
                                $wpdb->delete($table_name, array('id' => $each_entry->id));
                            }
                        }
                    }
                }
            }
//failed rac_guest_abadon_type_failed
            if (get_option('rac_guest_abadon_type_failed') == 'yes') {//option selected by user
                if ($new_status == 'failed') {
                    $get_list = $wpdb->get_results("SELECT * FROM $table_name WHERE user_id='0'", OBJECT);
                    foreach ($get_list as $each_entry) {
                        $expected_object = maybe_unserialize($each_entry->cart_details);
                        if (is_object($expected_object)) {
                            if ($expected_object->id == $order_id) {
                                $wpdb->delete($table_name, array('id' => $each_entry->id));
                            }
                        }
                    }
                }
            }
//on-hold  rac_guest_abadon_type_on-hold
            if (get_option('rac_guest_abadon_type_on-hold') == 'yes') {//option selected by user
                if ($new_status == 'on-hold') {
                    $get_list = $wpdb->get_results("SELECT * FROM $table_name WHERE user_id='0'", OBJECT);
                    foreach ($get_list as $each_entry) {
                        $expected_object = maybe_unserialize($each_entry->cart_details);
                        if (is_object($expected_object)) {
                            if ($expected_object->id == $order_id) {
                                $wpdb->delete($table_name, array('id' => $each_entry->id));
                            }
                        }
                    }
                }
            }
//refunded rac_guest_abadon_type_refunded
            if (get_option('rac_guest_abadon_type_refunded') == 'yes') {//option selected by user
                if ($new_status == 'refunded') {
                    $get_list = $wpdb->get_results("SELECT * FROM $table_name WHERE user_id='0'", OBJECT);
                    foreach ($get_list as $each_entry) {
                        $expected_object = maybe_unserialize($each_entry->cart_details);
                        if (is_object($expected_object)) {
                            if ($expected_object->id == $order_id) {
                                $wpdb->delete($table_name, array('id' => $each_entry->id));
                            }
                        }
                    }
                }
            }

// rac_guest_abadon_type_cancelled
            if (get_option('rac_guest_abadon_type_cancelled') == 'yes') {//option selected by user
                if ($new_status == 'cancelled') {
                    $get_list = $wpdb->get_results("SELECT * FROM $table_name WHERE user_id='0'", OBJECT);
                    foreach ($get_list as $each_entry) {
                        $expected_object = maybe_unserialize($each_entry->cart_details);
                        if (is_object($expected_object)) {
                            if ($expected_object->id == $order_id) {
                                $wpdb->delete($table_name, array('id' => $each_entry->id));
                            }
                        }
                    }
                }
            }
        }
    }

    public static function fp_rac_checkout_script() {
        if (!is_user_logged_in()) {
            //keypress change
            echo '<script type = "text/javascript">
                  jQuery(document).ready(function(){
                     jQuery("#billing_email").on("keyup keypress change focusout",function() {
                         var fp_rac_mail = this . value;
                         var atpos=fp_rac_mail.indexOf("@");
                         var dotpos=fp_rac_mail.lastIndexOf(".");
                if (atpos<1 || dotpos<atpos+2 || dotpos+2>=fp_rac_mail.length)
                        {
                            console.log("Not a valid e-mail address");
                            //return false;
                       }
                    else{
                         console . log(fp_rac_mail);
                             var fp_rac_first_name = jQuery("#billing_first_name").val();
                             var fp_rac_last_name = jQuery("#billing_last_name").val();
                             var fp_rac_phone = jQuery("#billing_phone").val();
                      var data = {
              action:"rac_preadd_guest",
              rac_email:fp_rac_mail,
              rac_first_name:fp_rac_first_name,
              rac_last_name:fp_rac_last_name,
              rac_phone:fp_rac_phone
              }
     jQuery.post("' . admin_url("admin-ajax.php") . '", data,
                            function(response) {
                                //alert(response);
                                console.log(response);

                            });
  }

        });
        });
                </script>';
        }
    }

    public static function fp_rac_guest_entry_checkout_ajax() {
        global $woocommerce;
        if (!is_user_logged_in()) {
            if (!isset($_COOKIE['rac_cart_id'])) { //means they didn't come mail
                if (function_exists('icl_register_string')) {
                    $currentuser_lang = isset($_SESSION['wpml_globalcart_language']) ? $_SESSION['wpml_globalcart_language'] : ICL_LANGUAGE_CODE;
                } else {
                    $currentuser_lang = 'en';
                }
                $visitor_mail = $_POST['rac_email'];
                $visitor_first_name = $_POST['rac_first_name'];
                $visitor_last_name = $_POST['rac_last_name'];
                $visitor_phone = $_POST['rac_phone'];
                $ip_address = $_SERVER["REMOTE_ADDR"];
                if (function_exists('WC')) {
                    $visitor_cart = WC()->cart->get_cart();
                } else {
                    $visitor_cart = $woocommerce->cart->get_cart();
                }
                $visitor_details = $visitor_cart;
                $visitor_details['visitor_mail'] = $visitor_mail;
                $visitor_details['first_name'] = $visitor_first_name;
                $visitor_details['last_name'] = $visitor_last_name;
                $visitor_details['visitor_phone'] = $visitor_phone;
                global $wpdb;
                $table_name = $wpdb->prefix . 'rac_abandoncart';
                $cart_content = maybe_serialize($visitor_details);
                $user_id = "000";
                $current_time = current_time('timestamp');
                if (get_option('rac_remove_carts') == 'yes') {


                    if (get_option('rac_remove_new') == 'yes') {

                        $wpdb->delete($table_name, array('email_id' => $visitor_mail, 'cart_status' => 'NEW'));
                    }

                    if (get_option('rac_remove_abandon') == 'yes') {

                        $wpdb->delete($table_name, array('email_id' => $visitor_mail, 'cart_status' => 'ABANDON'));
                    }
                }

//check for duplication
                $check_ip = $wpdb->get_results("SELECT * FROM $table_name WHERE ip_address ='$ip_address' AND cart_status='NEW'");
                if (!is_null(@$check_ip[0]->id) && !empty($check_ip[0]->id)) {//update
                    $wpdb->update($table_name, array('cart_details' => $cart_content, 'user_id' => $user_id, 'email_id' => $visitor_mail), array('id' => $check_ip[0]->id));
                } else {//Insert New entry
                    $wpdb->insert($table_name, array('cart_details' => $cart_content, 'user_id' => $user_id, 'email_id' => $visitor_mail, 'cart_abandon_time' => $current_time, 'cart_status' => 'NEW', 'ip_address' => $ip_address, 'wpml_lang' => $currentuser_lang));
                    setcookie("rac_checkout_entry", $wpdb->insert_id, time() + 3600, "/");
                }
// echo $wpdb->insert_id;
            }
        }
        exit();
    }

    public static function delete_all_rac_list() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'rac_abandoncart';
        $rows_to_delete = $_POST['listids'];
        $status = $_POST['deletion'];
        foreach ($rows_to_delete as $row_id) {
            if ($status == 'trash') {
                $select_rows = $wpdb->get_results("SELECT * FROM $table_name WHERE cart_status NOT IN('trash') and id=$row_id", OBJECT);
                foreach ($select_rows as $each_row) {
                    $cart_status_old = $each_row->cart_status;
                    $wpdb->update($table_name, array('old_status' => $cart_status_old), array('id' => $row_id));
                }
                $wpdb->update($table_name, array('cart_status' => 'trash'), array('id' => $row_id));
            } elseif ($status == 'restore') {

                $select_rows = $wpdb->get_results("SELECT * FROM $table_name WHERE cart_status IN('trash') and id=$row_id", OBJECT);
                if (!empty($select_rows)) {
                    foreach ($select_rows as $each_row) {
                        $cart_status_old = $each_row->cart_status;
                        $wpdb->update($table_name, array('cart_status' => $each_row->old_status), array('id' => $row_id));
                    }
                }
//$wpdb->update($table_name, array('cart_status' => ''), array('id' => $row_id));
            } else {
                $wpdb->delete($table_name, array('id' => $row_id));
            }
        }
        echo "1";
        exit();
    }

    public static function delete_individual_rac_list() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'rac_abandoncart';
        $row_id = $_POST['row_id'];
        $status = $_POST['deletion'];

        if ($status == 'trash') {
            $select_rows = $wpdb->get_results("SELECT * FROM $table_name WHERE cart_status NOT IN('trash') and id=$row_id", OBJECT);
            foreach ($select_rows as $each_row) {
                $cart_status_old = $each_row->cart_status;
                $wpdb->update($table_name, array('old_status' => $cart_status_old), array('id' => $row_id));
            }
            $wpdb->update($table_name, array('cart_status' => 'trash'), array('id' => $row_id));
        } elseif ($status == 'restore') {
            $select_rows = $wpdb->get_results("SELECT * FROM $table_name WHERE cart_status IN('trash') and id=$row_id", OBJECT);
            foreach ($select_rows as $each_row) {
                $cart_status_old = $each_row->old_status;
                $wpdb->update($table_name, array('cart_status' => $cart_status_old), array('id' => $row_id));
            }
        } else {
            $wpdb->delete($table_name, array('id' => $row_id));
        }
        echo "1";
        exit();
    }

    public static function delete_all_rac_log() {
        global $wpdb;
        $table_name_logs = $wpdb->prefix . 'rac_email_logs';
        $rows_to_delete = $_POST['listids'];
        foreach ($rows_to_delete as $row_id) {
            $wpdb->delete($table_name_logs, array('id' => $row_id));
        }
        exit();
    }

    public static function delete_individual_rac_log() {
        global $wpdb;
        $table_name_logs = $wpdb->prefix . 'rac_email_logs';
        $row_id = $_POST['row_id'];
        $wpdb->delete($table_name_logs, array('id' => $row_id));
        exit();
    }

    public static function remove_member_acart_on_orderplaced($order_id) {
        if (is_user_logged_in()) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'rac_abandoncart';
            $order = new WC_Order($order_id);
            $user_id = $order->user_id;
            if (!empty($user_id)) { // order by members
                $part_user_acart = $wpdb->get_results("SELECT * FROM $table_name WHERE user_id='$user_id' AND cart_status='NEW'", OBJECT);
                if (!empty($part_user_acart)) {
                    foreach ($part_user_acart as $each_entry) {
                        $stored_cart = maybe_unserialize($each_entry->cart_details);

                        foreach ($stored_cart as $cart_details) {
                            if (count($cart_details['cart']) <= count($order->get_items())) {
                                $order_item_product_ids = array();
                                $rac_cart_product_ids = array();
                                foreach ($cart_details['cart'] as $product) {
                                    $rac_cart_product_ids[] = $product['product_id'];
                                }
                                foreach ($order->get_items() as $items) {
                                    $order_item_product_ids[] = $items['product_id'];
                                }
                                $check_array = array_diff($rac_cart_product_ids, $order_item_product_ids);
                                if (empty($check_array)) {
                                    $wpdb->delete($table_name, array('id' => $each_entry->id));
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public static function fp_rac_settings_link($links) {
        $setting_page_link = '<a href="admin.php?page=fprac_slug">Settings</a>';
        array_unshift($links, $setting_page_link);
        return $links;
    }

    public static function fp_rac_reset_general() {
        if (isset($_GET['rac_reset'])) {
            $admin_url = admin_url('admin.php');
            $reset_true_url = esc_url_raw(add_query_arg(array('page' => 'fprac_slug', 'tab' => 'fpracgenral', 'resetted' => 'true'), $admin_url));
            update_option('rac_abandon_cart_time_type', 'hours');
            update_option('rac_abandon_cart_time', '1');
            update_option('rac_abandon_cart_time_type_guest', 'hours');
            update_option('rac_abandon_cart_time_guest', '1');
            update_option('rac_abandon_cart_cron_type', 'hours');
            update_option('rac_abandon_cron_time', '12');
            update_option('rac_admin_cart_recovered_noti', 'no');
            update_option('admin_notifi_sender_opt', 'woo');
            update_option('rac_recovered_email_subject', 'A cart has been Recovered');
            update_option('rac_recovered_email_message', 'A cart has been Recovered. Here is the order ID {rac.recovered_order_id} for Reference.');
            wp_redirect($reset_true_url);
            exit;
        }
    }

    public static function set_mail_sending_opt() {
        if (isset($_POST['row_id'])) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'rac_abandoncart';
            $requesting_state = $_POST['current_state'] == 'SEND' ? 'DONT' : 'SEND';
            $wpdb->update($table_name, array('sending_status' => $requesting_state), array('id' => $_POST['row_id']));
            echo $requesting_state;
        }
        exit();
    }

    public static function set_email_template_status() {
        if (isset($_POST['row_id'])) {
            global $wpdb;
            $table_name_email = $wpdb->prefix . 'rac_templates_email';
            $requesting_state = $_POST['status'] == 'ACTIVE' ? 'NOTACTIVE' : 'ACTIVE';
            $wpdb->update($table_name_email, array('status' => $requesting_state), array('id' => $_POST['row_id']));
            echo $requesting_state;
        }
        exit();
    }

    public static function rac_locate_template($template, $template_name, $template_path) {
        global $woocommerce;

        $default_template = $template;
        if (!$template_path) {
            $template_path = $woocommerce->template_url;
        }

        if ("woocommerce" == get_option('rac_email_use_temp_plain')) {
// default
            if (!$default_path) {
                if (function_exists('WC')) {
                    $default_path = WC()->plugin_path() . '/templates/';
                } else {
                    $default_path = $woocommerce->plugin_path() . '/templates/';
                }
            }

            $template = $default_path . $template_name;
        } elseif ("theme" == get_option('rac_email_use_temp_plain')) {
//theme
            $template = locate_template(
                    array(
                        $template_path . $template_name,
                        $template_name
                    )
            );
// default
            if (!$template) {
                $template = $default_template;
            }
        }

// Return what we found
        return $template;
    }

    public static function rac_load_mail_message() {
        if (isset($_POST['row_id'])) {
            global $wpdb;
            $row_id = $_POST['row_id'];
            $table_name = $wpdb->prefix . 'rac_templates_email';
            $templates = $wpdb->get_results("SELECT * FROM $table_name WHERE id=$row_id", OBJECT);
// echo $templates[0]->message;
            $template_send = array("mail_send_opt" => $templates[0]->sender_opt,
                "from_name" => $templates[0]->from_name,
                "from_email" => $templates[0]->from_email,
                "subject" => $templates[0]->subject,
                "message" => $templates[0]->message,
                "cart_link_text" => $templates[0]->anchor_text,
                "mail" => $templates[0]->mail,
                "link" => $templates[0]->link,
            );

            echo json_encode($template_send);
        }
        exit();
    }

    public static function add_styles_in_general_tab() {
        if (isset($_GET['tab'])) {
            if ($_GET['tab'] == 'fpracemail') {
                ?>
                <script type="text/javascript">
                    jQuery(document).ready(function () {
                        jQuery(function () {
                            var currentplainhtml = jQuery('.rac_template_mail').val();
                            if (currentplainhtml === 'PLAIN') {
                                jQuery('.rac_logo_link').show();
                                jQuery('.rac_email_sender').show();
                                if (jQuery('#rac_sender_woo').is(':checked')) {
                                    jQuery('.rac_local_senders').hide();
                                } else {
                                    jQuery('.rac_local_senders').show();
                                }
                                jQuery('input[name=rac_sender_opt]').change(function () {
                                    if (jQuery('#rac_sender_woo').is(':checked')) {
                                        jQuery('.rac_local_senders').hide();
                                    } else {
                                        jQuery('.rac_local_senders').show();
                                    }
                                });
                            } else {
                                jQuery('.rac_logo_link').hide();
                                jQuery('.rac_email_sender').hide();
                                jQuery('.rac_local_senders').hide();
                            }
                            jQuery('.rac_template_mail').change(function () {
                                var currentplainhtml = jQuery(this).val();
                                if (currentplainhtml === 'PLAIN') {
                                    jQuery('.rac_logo_link').show();
                                    jQuery('.rac_email_sender').show();
                                    if (jQuery('#rac_sender_woo').is(':checked')) {
                                        jQuery('.rac_local_senders').hide();
                                    } else {
                                        jQuery('.rac_local_senders').show();
                                    }
                                    jQuery('input[name=rac_sender_opt]').change(function () {
                                        if (jQuery('#rac_sender_woo').is(':checked')) {
                                            jQuery('.rac_local_senders').hide();
                                        } else {
                                            jQuery('.rac_local_senders').show();
                                        }
                                    });
                                } else {
                                    jQuery('.rac_logo_link').hide();
                                    jQuery('.rac_email_sender').hide();
                                    jQuery('.rac_local_senders').hide();
                                }
                            });
                        });
                        if (jQuery('#rac_cart_link_options').val() === '3') {
                            jQuery('.racbutton').parent().parent().show();
                            jQuery('.raclink').parent().parent().hide();
                        } else if (jQuery('#rac_cart_link_options').val() === '2') {
                            jQuery('.raclink').parent().parent().hide();
                            jQuery('.racbutton').parent().parent().hide();
                        } else {
                            jQuery('.racbutton').parent().parent().hide();
                            jQuery('.raclink').parent().parent().show();
                        }

                        jQuery('#rac_cart_link_options').change(function () {
                            if (jQuery(this).val() === '3') {
                                jQuery('.racbutton').parent().parent().show();
                            } else if (jQuery(this).val() === '2') {
                                jQuery('.racbutton').parent().parent().hide();
                                jQuery('.raclink').parent().parent().hide();
                            } else {
                                jQuery('.racbutton').parent().parent().hide();
                                jQuery('.raclink').parent().parent().show();
                            }
                        });
                    });</script>
                <?php
            }

            if ($_GET['tab'] == 'fpraccoupon') {
                ?>
                <script type="text/javascript">
                    jQuery(document).ready(function () {
                        if (jQuery('#rac_prefix_coupon').val() === '1') {
                            jQuery('#rac_manual_prefix_coupon_code').parent().parent().hide();
                        } else {
                            jQuery('#rac_manual_prefix_coupon_code').parent().parent().show();
                        }

                        jQuery('#rac_prefix_coupon').change(function () {
                            if (jQuery(this).val() === '1') {
                                jQuery('#rac_manual_prefix_coupon_code').parent().parent().hide();
                            } else {
                                jQuery('#rac_manual_prefix_coupon_code').parent().parent().show();
                            }
                        });
                    });</script>
                <?php
            }
        }
    }

    public static function rac_cart_link_button_mode($cartlink, $cart_text) {
        ob_start();
        ?>
        <table cellspacing="0" cellpadding="0">
            <tr>
                <td align="center" bgcolor="#<?php echo get_option('rac_cart_button_bg_color'); ?>" style="-webkit-border-radius: 5px; -moz-border-radius: 5px; border-radius: 5px; color: #ffffff; display: block; padding:0px 10px 0px 10px;">
                    <a href="<?php echo $cartlink; ?>" style="text-decoration: none; width:100%; display:inline-block;line-height:40px;"><span style="color: #<?php echo get_option('rac_cart_button_link_color'); ?>"><?php echo $cart_text; ?></span></a>
                </td>
            </tr>
        </table>
        <?php
        $results = ob_get_clean();
        return $results;
    }

    public static function shortcode_in_subject($firstname, $lastname, $subject) {
        $find_array = array('{rac.firstname}', '{rac.lastname}');
        $replace_array = array($firstname, $lastname);
        $subject = str_replace($find_array, $replace_array, $subject);
        return $subject;
    }

    /*
     * Function to save the selected products to exclude
     */

    public static function save_product_to_exclude() {
        update_option('rac_exclude_products_in_coupon', $_POST['rac_exclude_products_in_coupon']);
    }

    /*
     * Function to save select products to include
     */

    public static function save_product_to_include() {
        update_option('rac_include_products_in_coupon', $_POST['rac_include_products_in_coupon']);
    }

    /*
     * Function to select products to exclude
     */

    public static function rac_select_product_to_exclude() {

        global $woocommerce;
        if ((float) $woocommerce->version > (float) ('2.2.0')) {
            ?>
            <tr valign="top">
                <th class="titledesc" scope="row">
                    <label for="rac_exclude_products_in_coupon"><?php _e('Exclude Products from using Coupon', 'recoverabandoncart'); ?></label>
                </th>
                <td class="forminp forminp-select">
                    <input type="hidden" class="wc-product-search" style="width: 350%;" id="rac_exclude_products_in_coupon"  name="rac_exclude_products_in_coupon" data-placeholder="<?php _e('Search for a product&hellip;', 'recoverabandoncart'); ?>" data-action="woocommerce_json_search_products_and_variations" data-multiple="true" data-selected="<?php
                    $json_ids = array();
                    if (get_option('rac_exclude_products_in_coupon') != "") {
                        $list_of_produts = get_option('rac_exclude_products_in_coupon');
                        $product_ids = array_filter(array_map('absint', (array) explode(',', get_option('rac_exclude_products_in_coupon'))));

                        foreach ($product_ids as $product_id) {
                            $product = wc_get_product($product_id);
                            $json_ids[$product_id] = wp_kses_post($product->get_formatted_name());
                        } echo esc_attr(json_encode($json_ids));
                    }
                    ?>" value="<?php echo implode(',', array_keys($json_ids)); ?>" />

                    <script type="text/javascript">
                        jQuery(function () {
                            jQuery('.rac_exclude_category_to_enable_redeeming').select2();
                        });</script>
                </td>
            </tr>
        <?php } else { ?>
            <tr valign="top">
                <th class="titledesc" scope="row">
                    <label for="rac_exclude_products_in_coupon"><?php _e('Exclude Products from using Coupon', 'recoverabandoncart'); ?></label>
                </th>
                <td class="forminp forminp-select">
                    <select multiple name="rac_exclude_products_in_coupon" style='width:350px;' id='rac_exclude_products_in_coupon' class="rac_exclude_products_in_coupon rac_include_exclude_products_coupon">
                        <?php
                        $selected_products_exclude = array_filter((array) get_option('rac_exclude_products_in_coupon'));
                        if ($selected_products_exclude != "") {
                            if (!empty($selected_products_exclude)) {
                                $list_of_produts = (array) get_option('rac_exclude_products_in_coupon');
                                foreach ($list_of_produts as $rs_free_id) {
                                    echo '<option value="' . $rs_free_id . '" ';
                                    selected(1, 1);
                                    echo '>' . ' #' . $rs_free_id . ' &ndash; ' . get_the_title($rs_free_id);
                                }
                            }
                        } else {
                            ?>
                            <option value=""></option>
                            <?php
                        }
                        ?>
                    </select>
                    <script type="text/javascript">
                        jQuery(function () {
                            jQuery('.rac_exclude_category_to_enable_redeeming').chosen();
                        });</script>
                </td>
            </tr>
            <?php
        }
    }

    /*
     * Function to select products to include
     */

    public static function rac_select_product_to_include() {

        global $woocommerce;
        if ((float) $woocommerce->version > (float) ('2.2.0')) {
            ?>
            <tr valign="top">
                <th class="titledesc" scope="row">
                    <label for="rac_include_products_in_coupon"><?php _e('Products', 'recoverabandoncart'); ?></label>
                </th>
                <td class="forminp forminp-select">
                    <input type="hidden" class="wc-product-search" style="width: 350%;" id="rac_include_products_in_coupon"  name="rac_include_products_in_coupon" data-placeholder="<?php _e('Search for a product&hellip;', 'recoverabandoncart'); ?>" data-action="woocommerce_json_search_products_and_variations" data-multiple="true" data-selected="<?php
                    $json_ids = array();
                    if (get_option('rac_include_products_in_coupon') != "") {
                        $list_of_produts = get_option('rac_include_products_in_coupon');
                        $product_ids = array_filter(array_map('absint', (array) explode(',', get_option('rac_include_products_in_coupon'))));

                        foreach ($product_ids as $product_id) {
                            $product = wc_get_product($product_id);
                            $json_ids[$product_id] = wp_kses_post($product->get_formatted_name());
                        } echo esc_attr(json_encode($json_ids));
                    }
                    ?>" value="<?php echo implode(',', array_keys($json_ids)); ?>" />

                    <script type="text/javascript">
                        jQuery(function () {
                            jQuery('.rac_select_category_to_enable_redeeming').select2();
                        });</script>
                </td>
            </tr>
        <?php } else { ?>
            <tr valign="top">
                <th class="titledesc" scope="row">
                    <label for="rac_include_products_in_coupon"><?php _e('Products', 'recoverabandoncart'); ?></label>
                </th>
                <td class="forminp forminp-select">
                    <select multiple name="rac_include_products_in_coupon" style='width:350px;' id='rac_include_products_in_coupon' class="rac_include_products_in_coupon rac_include_exclude_products_coupon">
                        <?php
                        $selected_products_include = array_filter((array) get_option('rac_include_products_in_coupon'));
                        if ($selected_products_include != "") {
                            if (!empty($selected_products_include)) {
                                $list_of_produts = (array) get_option('rac_include_products_in_coupon');
                                foreach ($list_of_produts as $rs_free_id) {
                                    echo '<option value="' . $rs_free_id . '" ';
                                    selected(1, 1);
                                    echo '>' . ' #' . $rs_free_id . ' &ndash; ' . get_the_title($rs_free_id);
                                }
                            }
                        } else {
                            ?>
                            <option value=""></option>
                            <?php
                        }
                        ?>
                    </select>

                    <script type="text/javascript">
                        jQuery(function () {
                            jQuery('.rac_select_category_to_enable_redeeming').chosen();
                        });</script>
                </td>
            </tr>
            <?php
        }
    }

    public static function add_script_on_coupon_tab() {
        if (isset($_GET['tab'])) {
            if ($_GET['tab'] == 'fpraccoupon') {
                echo self::rac_common_ajax_function_to_select_products('rac_include_exclude_products_coupon');
            }
        }
    }

    public static function rac_common_ajax_function_to_select_products($ajaxid) {
        global $woocommerce;
        ob_start();
        ?>
        <script type="text/javascript">
        <?php if ((float) $woocommerce->version <= (float) ('2.2.0')) { ?>
                jQuery(function () {
                    jQuery("select.<?php echo $ajaxid; ?>").ajaxChosen({
                        method: 'GET',
                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
                        dataType: 'json',
                        afterTypeDelay: 100,
                        data: {
                            action: 'woocommerce_json_search_products_and_variations',
                            security: '<?php echo wp_create_nonce("search-products"); ?>'
                        }
                    }, function (data) {
                        var terms = {};
                        jQuery.each(data, function (i, val) {
                            terms[i] = val;
                        });
                        return terms;
                    });
                });
        <?php } ?>
        </script>
        <?php
        $getcontent = ob_get_clean();
        return $getcontent;
    }

    public static function email_woocommerce_html($mail_template_post, $subject, $message, $logo) {

        if (($mail_template_post == 'HTML')) {
            ob_start();
            if (function_exists('wc_get_template')) {
                wc_get_template('emails/email-header.php', array('email_heading' => $subject));
                echo $message;
                wc_get_template('emails/email-footer.php');
            } else {

                woocommerce_get_template('emails/email-header.php', array('email_heading' => $subject));
                echo $message;
                woocommerce_get_template('emails/email-footer.php');
            }
            $woo_temp_msg = ob_get_clean();
        } elseif ($mail_template_post == 'PLAIN') {

            $woo_temp_msg = $logo . $message;
        } else {

            $woo_temp_msg = $message;
        }

        return $woo_temp_msg;
    }

    public static function rac_send_manual_mail() {
        global $wpdb, $woocommerce, $to;
        $table_name = $wpdb->prefix . 'rac_abandoncart';
// $emailtemplate_table_name = $wpdb->prefix . 'rac_templates_email';
        $abandancart_table_name = $wpdb->prefix . 'rac_abandoncart';
        $sender_option_post = stripslashes($_POST['rac_sender_option']);
        $mail_template_post = stripslashes($_POST['rac_template_mail']);  // mail plain or html
        $mail_logo_added = stripslashes($_POST['rac_logo_mail']);   // mail logo uploaded
        $from_name_post = stripslashes($_POST['rac_from_name']);
        $from_email_post = stripslashes($_POST['rac_from_email']);
        $message_post = stripslashes($_POST['rac_message']);
        $bcc_post = stripslashes($_POST['rac_blind_carbon_copy']);
        $subject_post = stripslashes($_POST['rac_mail_subject']);
        $anchor_text_post = stripslashes($_POST['rac_anchor_text']);
        $mail_row_ids = stripslashes($_POST['rac_mail_row_ids']);
        $row_id_array = explode(',', $mail_row_ids);
        $mail_template_id_post = isset($_POST['template_id']) ? $_POST['template_id'] : '';
        $table_name_email = $wpdb->prefix . 'rac_templates_email';

        $logo = '<table><tr><td align="center" valign="top"><p style="margin-top:0;"><img src="' . esc_url($mail_logo_added) . '" /></p></td></tr></table>'; // mail uploaded
        ?>
        <style type="text/css">
            table {
                border-collapse: separate;
                border-spacing: 0;
                color: #4a4a4d;
                font: 14px/1.4 "Helvetica Neue", Helvetica, Arial, sans-serif;
            }
            th,
            td {
                padding: 10px 15px;
                vertical-align: middle;
            }
            thead {
                background: #395870;
                background: linear-gradient(#49708f, #293f50);
                color: #fff;
                font-size: 11px;
                text-transform: uppercase;
            }
            th:first-child {
                border-top-left-radius: 5px;
                text-align: left;
            }
            th:last-child {
                border-top-right-radius: 5px;
            }
            tbody tr:nth-child(even) {
                background: #f0f0f2;
            }
            td {
                border-bottom: 1px solid #cecfd5;
                border-right: 1px solid #cecfd5;
            }
            td:first-child {
                border-left: 1px solid #cecfd5;
            }
            .book-title {
                color: #395870;
                display: block;
            }
            .text-offset {
                color: #7c7c80;
                font-size: 12px;
            }
            .item-stock,
            .item-qty {
                text-align: center;
            }
            .item-price {
                text-align: right;
            }
            .item-multiple {
                display: block;
            }
            tfoot {
                text-align: right;
            }
            tfoot tr:last-child {
                background: #f0f0f2;
                color: #395870;
                font-weight: bold;
            }
            tfoot tr:last-child td:first-child {
                border-bottom-left-radius: 5px;
            }
            tfoot tr:last-child td:last-child {
                border-bottom-right-radius: 5px;
            }

        </style>
        <?php
//$mail_temp_row = $wpdb->get_results("SELECT * FROM $table_name_email WHERE id=$template_id_post", OBJECT);
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        if ($sender_option_post == 'local') {
            $headers .= FPRacCron::rac_formatted_from_address_local($from_name_post, $from_email_post);
            $headers .= "Reply-To: " . $from_name_post . " <" . $from_email_post . ">\r\n";
        } else {
            $headers .= FPRacCron::rac_formatted_from_address_woocommerce();
            $headers .= "Reply-To: " . get_option('woocommerce_email_from_name') . " <" . get_option('woocommerce_email_from_address') . ">\r\n";
        }
        if ($bcc_post) {
            $headers .= "Bcc: " . $bcc_post . "\r\n";
        }
        foreach ($row_id_array as $row_id) {

            $cart_row = $wpdb->get_results("SELECT * FROM $table_name WHERE id=$row_id", OBJECT);
//echo $cart_row[0]->user_id;
//For Member

            $cart_array = maybe_unserialize($cart_row[0]->cart_details);

            $tablecheckproduct = fp_rac_extract_cart_details($cart_row[0]);


            if ($cart_row[0]->user_id != '0' && $cart_row[0]->user_id != 'old_order') {
//echo 'member';
                $sent_mail_templates = maybe_unserialize($cart_row[0]->mail_template_id);
                if (!is_array($sent_mail_templates)) {
                    $sent_mail_templates = array(); // to avoid mail sent/not sent problem for serialization on store
                }


                $current_time = current_time('timestamp');
                if (function_exists('wc_get_page_permalink')) {
                    @$cart_url = wc_get_page_permalink('cart');
                } else {
                    @$cart_url = WC()->cart->get_cart_url();
                }
                $url_to_click = esc_url_raw(add_query_arg(array('abandon_cart' => $cart_row[0]->id, 'email_template' => $mail_template_id_post), $cart_url));

                if (get_option('rac_cart_link_options') == '1') {
                    $url_to_click = '<a style="color:#' . get_option("rac_email_link_color") . '"  href="' . $url_to_click . '">' . fp_get_wpml_text('rac_template_' . $mail_template_id_post . '_anchor_text', $cart_row[0]->wpml_lang, $anchor_text_post) . '</a>';
                } elseif (get_option('rac_cart_link_options') == '2') {
                    $url_to_click = $url_to_click;
                } else {
                    $cart_text = fp_get_wpml_text('rac_template_' . $mail_template_id_post . '_anchor_text', $cart_row[0]->wpml_lang, $anchor_text_post);
                    $url_to_click = self::rac_cart_link_button_mode($url_to_click, $cart_text);
                }

//$url_to_click = '<a href="' . $url_to_click . '">' . fp_get_wpml_text('rac_template_' . $mail_template_id_post . '_anchor_text', $cart_row[0]->wpml_lang, $anchor_text_post) . '</a>';

                $user = get_userdata($cart_row[0]->user_id);
                $to = $user->user_email;
                $firstname = $user->user_firstname;
                $lastname = $user->user_lastname;
                $date = date("d:m:y", $cart_row[0]->cart_abandon_time);
                $time = date("h:i:s", $cart_row[0]->cart_abandon_time);

// $logo = '<p style="float:left; margin-top:0"><img src="' . esc_url( $mail_logo_added ) . '" /></p>'; // mail uploaded
                $subject = fp_get_wpml_text('rac_template_' . $mail_template_id_post . '_subject', $cart_row[0]->wpml_lang, $subject_post);

                $subject = self::shortcode_in_subject($firstname, $lastname, $subject);

                $message = fp_get_wpml_text('rac_template_' . $mail_template_id_post . '_message', $cart_row[0]->wpml_lang, $message_post);
                $message = str_replace('{rac.cartlink}', $url_to_click, $message);
                $message = str_replace('{rac.date}', $date, $message);
                $message = str_replace('{rac.time}', $time, $message);
                $message = str_replace('{rac.firstname}', $firstname, $message);
                $message = str_replace('{rac.lastname}', $lastname, $message);

                $message = str_replace('{rac.Productinfo}', $tablecheckproduct, $message);
                if (strpos($message, "{rac.coupon}")) {
                    $coupon_code = FPRacCoupon::rac_create_coupon($user->user_email, $cart_row[0]->cart_abandon_time);
                    update_option('abandon_time_of' . $cart_row[0]->id, $coupon_code);
                    $message = str_replace('{rac.coupon}', $coupon_code, $message); //replacing shortcode with coupon code
                }
                $message = RecoverAbandonCart::rac_unsubscription_shortcode($to, $message);
                add_filter('woocommerce_email_footer_text', array('RecoverAbandonCart', 'rac_footer_email_customization'));
                $message = do_shortcode($message); //shortcode feature


                $logo = '<table><tr><td align="center" valign="top"><p style="margin-top:0;"><img style="max-height:600px;max-width:600px;" src="' . esc_url($mail_logo_added) . '" /></p></td></tr></table>'; // mail uploaded
// mail send plain or html
                $woo_temp_msg = self::email_woocommerce_html($mail_template_post, $subject, $message, $logo);
// mail send plain or html

                if ('wp_mail' == get_option('rac_trouble_mail')) {
                    if (FPRacCron::rac_send_wp_mail($to, $subject, $woo_temp_msg, $headers, $mail_template_post)) {
                        $sent_mail_templates[] = $mail_template_id_post;
                        $store_template_id = maybe_serialize($sent_mail_templates);
                        $wpdb->update($abandancart_table_name, array('mail_template_id' => $store_template_id), array('id' => $cart_row[0]->id));
//add to mail log
                        $table_name_logs = $wpdb->prefix . 'rac_email_logs';
                        $template_used = $mail_template_id_post . '- Manual';
                        $wpdb->insert($table_name_logs, array("email_id" => $to, "date_time" => $current_time, "rac_cart_id" => $cart_row[0]->id, "template_used" => $template_used));
                        FPRacCounter::rac_do_mail_count();
                        FPRacCounter::email_count_by_template($mail_template_id_post);
                    }
                } else {
                    if (FPRacCron::rac_send_mail($to, $subject, $woo_temp_msg, $headers)) {
                        $sent_mail_templates[] = $mail_template_id_post;
                        $store_template_id = maybe_serialize($sent_mail_templates);
                        $wpdb->update($abandancart_table_name, array('mail_template_id' => $store_template_id), array('id' => $cart_row[0]->id));
//add to mail log
                        $table_name_logs = $wpdb->prefix . 'rac_email_logs';
                        $template_used = $mail_template_id_post . '- Manual';
                        $wpdb->insert($table_name_logs, array("email_id" => $to, "date_time" => $current_time, "rac_cart_id" => $cart_row[0]->id, "template_used" => $template_used));
                        FPRacCounter::rac_do_mail_count();
                        FPRacCounter::email_count_by_template($mail_template_id_post);
                    }
                }
            }
//End Member
//FOR Guest at place order
            if ($cart_row[0]->user_id == '0' && is_null($cart_row[0]->ip_address)) {
// echo 'guest';
                $sent_mail_templates = maybe_unserialize($cart_row[0]->mail_template_id);
                if (!is_array($sent_mail_templates)) {
                    $sent_mail_templates = array(); // to avoid mail sent/not sent problem for serialization on store
                }
                $current_time = current_time('timestamp');
                if (function_exists('wc_get_page_permalink')) {
                    @$cart_url = wc_get_page_permalink('cart');
                } else {
                    @$cart_url = WC()->cart->get_cart_url();
                }
                $url_to_click = esc_url_raw(add_query_arg(array('abandon_cart' => $cart_row[0]->id, 'email_template' => $mail_template_id_post, 'guest' => 'yes'), $cart_url));
//$url_to_click = '<a href="' . $url_to_click . '">' . fp_get_wpml_text('rac_template_' . $mail_template_id_post . '_anchor_text', $cart_row[0]->wpml_lang, $anchor_text_post) . '</a>';

                if (get_option('rac_cart_link_options') == '1') {
                    $url_to_click = '<a style="color:#' . get_option("rac_email_link_color") . '" href="' . $url_to_click . '">' . fp_get_wpml_text('rac_template_' . $mail_template_id_post . '_anchor_text', $cart_row[0]->wpml_lang, $anchor_text_post) . '</a>';
                } elseif (get_option('rac_cart_link_options') == '2') {
                    $url_to_click = $url_to_click;
                } else {
                    $cart_text = fp_get_wpml_text('rac_template_' . $mail_template_id_post . '_anchor_text', $cart_row[0]->wpml_lang, $anchor_text_post);
                    $url_to_click = self::rac_cart_link_button_mode($url_to_click, $cart_text);
                }

//  $user = get_userdata($each_cart->user_id); NOT APPLICABLE
                $order_object = maybe_unserialize($cart_row[0]->cart_details);
                $to = $order_object->billing_email;

                $firstname = $order_object->billing_first_name;
                $lastname = $order_object->billing_last_name;
                $date = date("d:m:y", $cart_row[0]->cart_abandon_time);
                $time = date("h:i:s", $cart_row[0]->cart_abandon_time);

                $subject = self::shortcode_in_subject($firstname, $lastname, $subject_post);
                $message = str_replace('{rac.cartlink}', $url_to_click, $message_post);
                $message = str_replace('{rac.date}', $date, $message);
                $message = str_replace('{rac.time}', $time, $message);
                $message = str_replace('{rac.firstname}', $firstname, $message);
                $message = str_replace('{rac.lastname}', $lastname, $message);
                $message = str_replace('{rac.Productinfo}', $tablecheckproduct, $message);
                if (strpos($message, "{rac.coupon}")) {
                    $coupon_code = FPRacCoupon::rac_create_coupon($order_object->billing_email, $cart_row[0]->cart_abandon_time);
                    update_option('abandon_time_of' . $cart_row[0]->id, $coupon_code);
                    $message = str_replace('{rac.coupon}', $coupon_code, $message); //replacing shortcode with coupon code
                }
                $message = RecoverAbandonCart::rac_unsubscription_shortcode($to, $message);
                $message = do_shortcode($message); //shortcode feature

                add_filter('woocommerce_email_footer_text', array('RecoverAbandonCart', 'rac_footer_email_customization'));

                $logo = '<table><tr><td align="center" valign="top"><p style="margin-top:0;"><img style="max-height:600px;max-width:600px;" src="' . esc_url($mail_logo_added) . '" /></p></td></tr></table>'; // mail uploaded
// mail send plain or html
                $woo_temp_msg = self::email_woocommerce_html($mail_template_post, $subject, $message, $logo);
// mail send plain or html
                if ('wp_mail' == get_option('rac_trouble_mail')) {
                    if (FPRacCron::rac_send_wp_mail($to, $subject, $woo_temp_msg, $headers, $mail_template_post)) {
                        $sent_mail_templates[] = $mail_template_id_post;
                        $store_template_id = maybe_serialize($sent_mail_templates);
                        $wpdb->update($abandancart_table_name, array('mail_template_id' => $store_template_id), array('id' => $cart_row[0]->id));
                        $table_name_logs = $wpdb->prefix . 'rac_email_logs';
                        $template_used = $mail_template_id_post . '- Manual';
                        $wpdb->insert($table_name_logs, array("email_id" => $to, "date_time" => $current_time, "rac_cart_id" => $cart_row[0]->id, "template_used" => $template_used));
                        FPRacCounter::rac_do_mail_count();
                        FPRacCounter::email_count_by_template($mail_template_id_post);
                    }
                } else {
                    if (FPRacCron::rac_send_mail($to, $subject, $woo_temp_msg, $headers)) {
                        $sent_mail_templates[] = $mail_template_id_post;
                        $store_template_id = maybe_serialize($store_template_id);
                        $wpdb->update($abandancart_table_name, array('mail_template_id' => $store_template_id), array('id' => $cart_row[0]->id));
                        $table_name_logs = $wpdb->prefix . 'rac_email_logs';
                        $template_used = $mail_template_id_post . '- Manual';
                        $wpdb->insert($table_name_logs, array("email_id" => $to, "date_time" => $current_time, "rac_cart_id" => $cart_row[0]->id, "template_used" => $template_used));
                        FPRacCounter::rac_do_mail_count();
                        FPRacCounter::email_count_by_template($mail_template_id_post);
                    }
                }
            }
//END Guest
//GUEST Checkout
            if ($cart_row[0]->user_id == '0' && !is_null($cart_row[0]->ip_address)) {
// echo 'checkout';
                $sent_mail_templates = maybe_unserialize($cart_row[0]->mail_template_id);
                if (!is_array($sent_mail_templates)) {
                    $sent_mail_templates = array(); // to avoid mail sent/not sent problem for serialization on store
                }
                $current_time = current_time('timestamp');
                if (function_exists('wc_get_page_permalink')) {
                    @$cart_url = wc_get_page_permalink('cart');
                } else {
                    @$cart_url = WC()->cart->get_cart_url();
                }
                $url_to_click = esc_url_raw(add_query_arg(array('abandon_cart' => $cart_row[0]->id, 'email_template' => $mail_template_id_post, 'guest' => 'yes', 'checkout' => 'yes'), $cart_url));


                if (get_option('rac_cart_link_options') == '1') {
                    $url_to_click = '<a style="color:#' . get_option("rac_email_link_color") . '"  href="' . $url_to_click . '">' . fp_get_wpml_text('rac_template_' . $mail_template_id_post . '_anchor_text', $cart_row[0]->wpml_lang, $anchor_text_post) . '</a>';
                } elseif (get_option('rac_cart_link_options') == '2') {
                    $url_to_click = $url_to_click;
                } else {
                    $cart_text = fp_get_wpml_text('rac_template_' . $mail_template_id_post . '_anchor_text', $cart_row[0]->wpml_lang, $anchor_text_post);
                    $url_to_click = self::rac_cart_link_button_mode($url_to_click, $cart_text);
                }

//  $user = get_userdata($each_cart->user_id); NOT APPLICABLE
                $order_object = maybe_unserialize($cart_row[0]->cart_details);
                $to = $order_object['visitor_mail'];
                $date = date("d:m:y", $cart_row[0]->cart_abandon_time);
                $time = date("h:i:s", $cart_row[0]->cart_abandon_time);

                $firstname = $order_object['first_name'];
                $lastname = $order_object['last_name'];
                $message = str_replace('{rac.cartlink}', $url_to_click, $message_post);
                $subject = self::shortcode_in_subject($firstname, $lastname, $subject_post);
                $message = str_replace('{rac.date}', $date, $message);
                $message = str_replace('{rac.time}', $time, $message);
                $message = str_replace('{rac.firstname}', $firstname, $message);
                $message = str_replace('{rac.lastname}', $lastname, $message);
                $message = str_replace('{rac.Productinfo}', $tablecheckproduct, $message);
                if (strpos($message, "{rac.coupon}")) {
                    $coupon_code = FPRacCoupon::rac_create_coupon($order_object['visitor_mail'], $cart_row[0]->cart_abandon_time);
                    update_option('abandon_time_of' . $cart_row[0]->id, $coupon_code);
                    $message = str_replace('{rac.coupon}', $coupon_code, $message); //replacing shortcode with coupon code
                }
                $message = RecoverAbandonCart::rac_unsubscription_shortcode($to, $message);
                $message = do_shortcode($message); //shortcode feature



                $logo = '<table><tr><td align="center" valign="top"><p style="margin-top:0;"><img style="max-height:600px;max-width:600px;" src="' . esc_url($mail_logo_added) . '" /></p></td></tr></table>'; // mail uploaded
// mail send plain or html
                $woo_temp_msg = self::email_woocommerce_html($mail_template_post, $subject, $message, $logo);
// mail send plain or html
                if ('wp_mail' == get_option('rac_trouble_mail')) {
                    if (FPRacCron::rac_send_wp_mail($to, $subject, $woo_temp_msg, $headers, $mail_template_post)) {
                        $sent_mail_templates[] = $mail_template_id_post;
                        $store_template_id = maybe_serialize($sent_mail_templates);
                        $wpdb->update($abandancart_table_name, array('mail_template_id' => $store_template_id), array('id' => $cart_row[0]->id));
                        $table_name_logs = $wpdb->prefix . 'rac_email_logs';
                        $template_used = $mail_template_id_post . '- Manual';
                        $wpdb->insert($table_name_logs, array("email_id" => $to, "date_time" => $current_time, "rac_cart_id" => $cart_row[0]->id, "template_used" => $template_used));
                        FPRacCounter::rac_do_mail_count();
                        FPRacCounter::email_count_by_template($mail_template_id_post);
                    }
                } else {
                    if (FPRacCron::rac_send_mail($to, $subject, $woo_temp_msg, $headers)) {
                        $sent_mail_templates[] = $mail_template_id_post;
                        $store_template_id = maybe_serialize($sent_mail_templates);
                        $wpdb->update($abandancart_table_name, array('mail_template_id' => $store_template_id), array('id' => $cart_row[0]->id));
                        $table_name_logs = $wpdb->prefix . 'rac_email_logs';
                        $template_used = $mail_template_id_post . '- Manual';
                        $wpdb->insert($table_name_logs, array("email_id" => $to, "date_time" => $current_time, "rac_cart_id" => $cart_row[0]->id, "template_used" => $template_used));
                        FPRacCounter::rac_do_mail_count();
                        FPRacCounter::email_count_by_template($mail_template_id_post);
                    }
                }
            }
//END Checkout
//Order Updated
            if ($cart_row[0]->user_id == 'old_order' && is_null($cart_row[0]->ip_address)) {
// echo 'order';
                $sent_mail_templates = maybe_unserialize($cart_row[0]->mail_template_id);
                $current_time = current_time('timestamp');
                if (function_exists('wc_get_page_permalink')) {
                    @$cart_url = wc_get_page_permalink('cart');
                } else {
                    @$cart_url = WC()->cart->get_cart_url();
                }
                $url_to_click = esc_url_raw(add_query_arg(array('abandon_cart' => $cart_row[0]->id, 'email_template' => $mail_template_id_post, 'old_order' => 'yes'), $cart_url));
//$url_to_click = '<a href="' . $url_to_click . '">' . fp_get_wpml_text('rac_template_' . $mail_template_id_post . '_anchor_text', $cart_row[0]->wpml_lang, $anchor_text_post) . '</a>';

                if (get_option('rac_cart_link_options') == '1') {
                    $url_to_click = '<a style="color:#' . get_option("rac_email_link_color") . '"  href="' . $url_to_click . '">' . fp_get_wpml_text('rac_template_' . $mail_template_id_post . '_anchor_text', $cart_row[0]->wpml_lang, $anchor_text_post) . '</a>';
                } elseif (get_option('rac_cart_link_options') == '2') {
                    $url_to_click = $url_to_click;
                } else {
                    $cart_text = fp_get_wpml_text('rac_template_' . $mail_template_id_post . '_anchor_text', $cart_row[0]->wpml_lang, $anchor_text_post);
                    $url_to_click = self::rac_cart_link_button_mode($url_to_click, $cart_text);
                }


//  $user = get_userdata($each_cart->user_id); NOT APPLICABLE
                $order_object = maybe_unserialize($cart_row[0]->cart_details);
                $to = $order_object->billing_email;
                $date = date("d:m:y", $cart_row[0]->cart_abandon_time);
                $time = date("h:i:s", $cart_row[0]->cart_abandon_time);

                $firstname = $order_object->billing_first_name;
                $lastname = $order_object->billing_last_name;
                $message = str_replace('{rac.cartlink}', $url_to_click, $message_post);
                $subject = self::shortcode_in_subject($firstname, $lastname, $subject_post);
                $message = str_replace('{rac.date}', $date, $message);
                $message = str_replace('{rac.time}', $time, $message);
                $message = str_replace('{rac.firstname}', $firstname, $message);
                $message = str_replace('{rac.lastname}', $lastname, $message);
                $message = str_replace('{rac.Productinfo}', $tablecheckproduct, $message);
                if (strpos($message, "{rac.coupon}")) {
                    $coupon_code = FPRacCoupon::rac_create_coupon($order_object->billing_email, $cart_row[0]->cart_abandon_time);
                    update_option('abandon_time_of' . $cart_row[0]->id, $coupon_code);
                    $message = str_replace('{rac.coupon}', $coupon_code, $message); //replacing shortcode with coupon code
                }
                $message = RecoverAbandonCart::rac_unsubscription_shortcode($to, $message);
                $message = do_shortcode($message); //shortcode feature

                $logo = '<table><tr><td align="center" valign="top"><p style="margin-top:0;"><img style="max-height:600px;max-width:600px;" src="' . esc_url($mail_logo_added) . '" /></p></td></tr></table>'; // mail uploaded
// mail send plain or html
                $woo_temp_msg = self::email_woocommerce_html($mail_template_post, $subject, $message, $logo);
// mail send plain or html
                if ('wp_mail' == get_option('rac_trouble_mail')) {
                    if (FPRacCron::rac_send_wp_mail($to, $subject, $woo_temp_msg, $headers, $mail_template_post)) {
                        $sent_mail_templates[] = $mail_template_id_post;
                        $store_template_id = maybe_serialize($sent_mail_templates);
                        $wpdb->update($abandancart_table_name, array('mail_template_id' => $store_template_id), array('id' => $cart_row[0]->id));
//add to mail log
                        $table_name_logs = $wpdb->prefix . 'rac_email_logs';
                        $template_used = $mail_template_id_post . '- Manual';
                        $wpdb->insert($table_name_logs, array("email_id" => $to, "date_time" => $current_time, "rac_cart_id" => $cart_row[0]->id, "template_used" => $template_used));
                        FPRacCounter::rac_do_mail_count();
                        FPRacCounter::email_count_by_template($mail_template_id_post);
                    }
                } else {
                    if (FPRacCron::rac_send_mail($to, $subject, $woo_temp_msg, $headers)) {
                        $sent_mail_templates[] = $mail_template_id_post;
                        $store_template_id = maybe_serialize($sent_mail_templates);
                        $wpdb->update($abandancart_table_name, array('mail_template_id' => $store_template_id), array('id' => $cart_row[0]->id));
//add to mail log
                        $table_name_logs = $wpdb->prefix . 'rac_email_logs';
                        $template_used = $mail_template_id_post . '- Manual';
                        $wpdb->insert($table_name_logs, array("email_id" => $to, "date_time" => $current_time, "rac_cart_id" => $cart_row[0]->id, "template_used" => $template_used));
                        FPRacCounter::rac_do_mail_count();
                        FPRacCounter::email_count_by_template($mail_template_id_post);
                    }
                }
            }
        }

        exit();
    }

    public static function rac_send_test_mail() {
        $to = $_POST['rac_test_mail_to'];
        $plain_or_html = $_POST['rac_plain_or_html'] == '1' ? "plain" : "html";

        $subject = "Test E-Mail";



        $message = "This is a test E-Mail to Make sure E-Mail are sent successfully from your site.";

        if ($plain_or_html == 'html') {
            ob_start();
            if (function_exists('wc_get_template')) {
                wc_get_template('emails/email-header.php', array('email_heading' => $subject));
                echo $message;
                wc_get_template('emails/email-footer.php');
            } else {
                woocommerce_get_template('emails/email-header.php', array('email_heading' => $subject));
                echo $message;
                woocommerce_get_template('emails/email-footer.php');
            }
            $message = ob_get_clean();
        } else {
            $message = $message;
        }

        $headers = "MIME-Version: 1.0\r\n";
        $headers .= FPRacCron::rac_formatted_from_address_woocommerce();
        $headers .= "Reply-To: " . get_option('woocommerce_email_from_name') . " <" . get_option('woocommerce_email_from_address') . ">\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        if ('wp_mail' == get_option('rac_trouble_mail')) {
            if (FPRacCron::rac_send_wp_mail_test($to, $subject, $message, $headers)) {
                echo "sent";
            }
        } else {
            if (FPRacCron::rac_send_mail_test($to, $subject, $message, $headers)) {
                echo "sent";
            }
        }

        exit();
    }

    public static function fp_rac_reports() {
        if (isset($_POST['rac_clear_reports'])) {
            delete_option('rac_abandoned_count');
            delete_option('rac_mail_count');
            delete_option('rac_link_count');
            delete_option('rac_recovered_count');
            delete_option('fp_rac_recovered_order_ids');
        }
        ?>
        <table class="rac_reports form-table">
            <tr>
                <th>
                    Number of Abandoned Carts Captured
                </th>
                <td>
                    <?php
                    if (get_option('rac_abandoned_count')) {
                        echo get_option('rac_abandoned_count');
                    } else {// if it is boolean false then there is no value. so give 0
                        echo "0";
                    };
                    ?>
                </td>
            </tr>
            <tr>
                <th>
                    Number of total Emails Sent
                </th>
                <td>
                    <?php
                    if (get_option('rac_mail_count')) {
                        echo get_option('rac_mail_count');
                    } else {
                        echo "0";
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th>
                    Number of total Email links clicked
                </th>
                <td>
                    <?php
                    if (get_option('rac_link_count')) {
                        echo get_option('rac_link_count');
                    } else {
                        echo "0";
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th>
                    <?php _e('Number of Carts Recovered', 'recoverabandoncart'); ?>
                </th>
                <td>
                    <?php
                    if (get_option('rac_recovered_count')) {
                        $admin_url = admin_url('admin.php');
                        $fpracrecoveredorderids = esc_url_raw(add_query_arg(array('page' => 'fprac_slug', 'tab' => 'fpracrecoveredorderids'), $admin_url));
                        echo '<a style="text-decoration:none" href="' . $fpracrecoveredorderids . '">' . get_option('rac_recovered_count') . '</a>&nbsp;';
                    } else {
                        echo "0";
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th>
                    <?php _e('Total Sales Amount Recovered', 'recoverabandoncart'); ?>
                </th>
                <td>
                    <?php
                    $get_order_ids = array_filter((array) get_option('fp_rac_recovered_order_ids'));
                    $total_sum = array();
                    if (!empty($get_order_ids)) {
                        foreach ($get_order_ids as $key => $value) {
                            $total_sum[] = $value['order_total'];
                        }
                    }
                    $total_sum = array_sum($total_sum);
                    echo FP_List_Table_RAC::format_price($total_sum);
                    ?>
                </td>
            </tr>
        </table>
        <br>
        <input type="submit" name="rac_clear_reports" id="rac_clear_reports" class="rac_clear_reports button-primary" value="Clear Reports" onclick="return confirm('Are you sure to clear the reports ?')">
        <style type="text/css">.rac_reports {
                width:50%;
                background-color:white;
                border:2px solid #21759b;
                border-collapse:unset;
                border-top: 4px solid #21759b;
                margin-top: 20px !important;

            }
            .rac_reports th{
                padding: 20px;
            }
        </style>
        <?php
    }

    public static function fp_rac_shortcodes_info() {
        $shortcodes_info = array(
            "{rac.cartlink}" => array("mail" => "Abandoned Cart Mail",
                "usage" => "Abandoned Cart can be loaded using this link from mail"
            ),
              "{rac.date}" => array("mail" => "Abandoned Cart Mail",
                "usage" => "Shows Abandoned Cart Date"
            ),
             "{rac.time}" => array("mail" => "Abandoned Cart Mail",
                "usage" => "Shows Abandoned Cart Time"
            ),
            "{rac.firstname}" => array("mail" => "Abandoned Cart Mail",
                "usage" => "Shows Receiver First Name"),
            "{rac.lastname}" => array("mail" => "Abandoned Cart Mail",
                "usage" => "Shows Receiver Last Name"),
            "{rac.recovered_order_id}" => array("mail" => "Admin Order Recovered Notification Mail",
                "usage" => "Order ID can be inserted in the admin notification mail for Reference"),
            "{rac.order_line_items}" => array("mail" => "Admin Order Line Items in Recovered Notification Mail",
                "usage" => "Order Line Items will be displayed in Admin Notification Mail for Information"),
            "{rac.Productinfo}" => array("mail" => "Abandoned Cart Mail",
                "usage" => "Shows Product Information Name Image Amount "),
            "{rac.coupon}" => array("mail" => "Abandoned Cart Mail",
                "usage" => "Copon code will be generated automatically and included in the mail with a Coupon options based on the settings from 'Coupon In Mail' tab"),
            "{rac.unsubscribe}" => array("mail" => "Abandoned Cart Mail",
                "usage" => "Shows Unsubscibe Link"),
            "{rac.unsubscribe_email_manual}" => array("mail" => "Pages",
                "usage" => "Manual Unsubscription of Abandon Cart emails done in this page"
        ));
        ?>
        <table class="rac_shortcodes_info">
            <thead>
                <tr>
                    <th>
                        Shortcode
                    </th>
                    <th>
                        Context where Shortcode is valid
                    </th>
                    <th>
                        Purpose
                    </th>
                </tr>
            </thead>
            <?php foreach ($shortcodes_info as $shortcode => $s_info) { ?>
                <tr>
                    <td>
                        <?php echo $shortcode; ?>
                    </td>
                    <td>
                        <?php echo $s_info['mail']; ?>
                    </td>
                    <td>
                        <?php echo $s_info['usage']; ?>
                    </td>
                </tr>
            <?php } ?>
        </table>
        <style type="text/css">
            .rac_shortcodes_info{
                margin-top:20px;
            }
        </style>
        <?php
    }

    public static function get_user_role_jquery() {
        global $woocommerce;
        ?>
        <script type="text/javascript">
            jQuery(function () {
        <?php if ((float) $woocommerce->version <= (float) ('2.2.0')) { ?>

                    jQuery("#custom_user_role").chosen();
                    jQuery("#custom_user_role_for_restrict_in_cart_list").chosen();
                    jQuery("#custom_user_name_select").chosen();
                    jQuery("#rac_mailcartlist_change").chosen();
        <?php } else {
            ?>

                    jQuery("#custom_user_role").select2();
                    jQuery("#custom_user_role_for_restrict_in_cart_list").select2();
                    jQuery("#rac_mailcartlist_change").select2();
        <?php }
        ?>

                var getselectedvalue = jQuery('#custom_exclude').val() || [];
                if (getselectedvalue === 'user_role') {
                    jQuery('#custom_user_role').parent().parent().css("display", "table-row");
                }
                else {
                    jQuery('#custom_user_role').parent().parent().css("display", "none");
                }

                jQuery('#custom_exclude').change(function () {

                    if (jQuery(this).val() === 'user_role') {

                        jQuery('#custom_user_role').parent().parent().css("display", "table-row");
                    }
                    else {
                        jQuery('#custom_user_role').parent().parent().css("display", "none");
                    }


                });
                var getselecteddvalue = jQuery('#custom_exclude').val() || [];
                if (getselecteddvalue === 'name') {
                    jQuery('#custom_user_name_select').parent().parent().css("display", "table-row");
                } else {
                    jQuery('#custom_user_name_select').parent().parent().css("display", "none");
                }

                jQuery('#custom_exclude').change(function () {

                    if (jQuery(this).val() === 'name') {

                        jQuery('#custom_user_name_select').parent().parent().css("display", "table-row");
                    } else {

                        jQuery('#custom_user_name_select').parent().parent().css("display", "none");
                    }

                });
                var getselecteddvalue = jQuery('#custom_exclude').val() || [];
                if (getselecteddvalue === 'mail_id') {
                    jQuery('#custom_mailid_edit').parent().parent().css("display", "table-row");
                } else {
                    jQuery('#custom_mailid_edit').parent().parent().css("display", "none");
                }

                jQuery('#custom_exclude').change(function () {

                    if (jQuery(this).val() === 'mail_id') {
                        jQuery('#custom_mailid_edit').parent().parent().css("display", "table-row");
                    } else {

                        jQuery('#custom_mailid_edit').parent().parent().css("display", "none");
                    }

                });
                var getselectedvalue_fr_cl = jQuery('#custom_restrict').val() || [];
                if (getselectedvalue_fr_cl === 'user_role') {
                    jQuery('#custom_user_role_for_restrict_in_cart_list').parent().parent().css("display", "table-row");
                }
                else {
                    jQuery('#custom_user_role_for_restrict_in_cart_list').parent().parent().css("display", "none");
                }

                jQuery('#custom_restrict').change(function () {

                    if (jQuery(this).val() === 'user_role') {

                        jQuery('#custom_user_role_for_restrict_in_cart_list').parent().parent().css("display", "table-row");
                    }
                    else {
                        jQuery('#custom_user_role_for_restrict_in_cart_list').parent().parent().css("display", "none");
                    }


                });
                var getselecteddvalue_fr_cl = jQuery('#custom_restrict').val() || [];
                if (getselecteddvalue_fr_cl === 'name') {
                    jQuery('#custom_user_name_select_for_restrict_in_cart_list').parent().parent().css("display", "table-row");
                } else {
                    jQuery('#custom_user_name_select_for_restrict_in_cart_list').parent().parent().css("display", "none");
                }

                jQuery('#custom_restrict').change(function () {

                    if (jQuery(this).val() === 'name') {

                        jQuery('#custom_user_name_select_for_restrict_in_cart_list').parent().parent().css("display", "table-row");
                    } else {

                        jQuery('#custom_user_name_select_for_restrict_in_cart_list').parent().parent().css("display", "none");
                    }

                });
                var getselecteddvalue_fr_cl = jQuery('#custom_restrict').val() || [];
                if (getselecteddvalue_fr_cl === 'mail_id') {
                    jQuery('#custom_mailid_for_restrict_in_cart_list').parent().parent().css("display", "table-row");
                } else {
                    jQuery('#custom_mailid_for_restrict_in_cart_list').parent().parent().css("display", "none");
                }

                jQuery('#custom_restrict').change(function () {

                    if (jQuery(this).val() === 'mail_id') {
                        jQuery('#custom_mailid_for_restrict_in_cart_list').parent().parent().css("display", "table-row");
                    } else {

                        jQuery('#custom_mailid_for_restrict_in_cart_list').parent().parent().css("display", "none");
                    }

                });
                var enable_delete_abandon_carts = jQuery('#enable_remove_abandon_after_x_days').val();
                if (enable_delete_abandon_carts === 'no') {
                    jQuery('#rac_remove_abandon_after_x_days').parent().parent().css("display", "none");
                }
                else {
                    jQuery('#rac_remove_abandon_after_x_days').parent().parent().css("display", "table-row");
                }

                jQuery('#enable_remove_abandon_after_x_days').change(function () {

                    if (jQuery(this).val() === 'no') {
                        jQuery('#rac_remove_abandon_after_x_days').parent().parent().css("display", "none");
                    } else {

                        jQuery('#rac_remove_abandon_after_x_days').parent().parent().css("display", "table-row");
                    }

                });
                var uploader_open;
                jQuery('.upload_button').click(function (e) {
                    e.preventDefault();
                    if (uploader_open) {
                        uploader_open.open();
                        return;
                    }

                    uploader_open = wp.media.frames.uploader_open = wp.media({
                        title: 'Media Uploader',
                        button: {
                            text: 'Media Uploader'
                        },
                        multiple: false
                    });
                    //When a file is selected, grab the URL and set it as the text field's value
                    uploader_open.on('select', function () {
                        attachment = uploader_open.state().get('selection').first().toJSON();
                        jQuery('#rac_logo_mail').val(attachment.url);
                    });
                    uploader_open.open();
                });
                var checked = jQuery('.rac_cartlist_new_abandon_recover').is(':checked') ? "1" : "0";
                if (checked === '1') {
                    jQuery('.rac_cart_depends_parent_new_abandon_option').parent().parent().parent().parent().show();
                    //                    jQuery('.rac_mailcartlist_change').parent().parent().show();
                } else {
                    jQuery('.rac_cart_depends_parent_new_abandon_option').parent().parent().parent().parent().hide();
                    //                    jQuery('.rac_mailcartlist_change').parent().parent().hide();
                }
                jQuery(document).on('change', '.rac_cartlist_new_abandon_recover', function () {
                    if (jQuery(this).is(':checked')) {
                        jQuery('.rac_cart_depends_parent_new_abandon_option').parent().parent().parent().parent().show();
                        //                        jQuery('.rac_mailcartlist_change').parent().parent().show();
                    } else {
                        jQuery('.rac_cart_depends_parent_new_abandon_option').parent().parent().parent().parent().hide();
                        //                        jQuery('.rac_mailcartlist_change').parent().parent().hide();
                    }
                });
                var unsubscription_type = jQuery("input[name='rac_unsubscription_type']:checked").val();
                if (unsubscription_type == '1') {
                    jQuery('#rac_unsubscription_redirect_url').parent().parent().show();
                    jQuery('#rac_unsubscription_message_text_color').closest('tr').show();
                    jQuery('#rac_unsubscription_message_background_color').closest('tr').show();
                    jQuery('#rac_manual_unsubscription_redirect_url').parent().parent().hide();
                    jQuery('#rac_confirm_unsubscription_text').parent().parent().hide();
                    jQuery('#rac_unsubscription_email_text_color').closest('tr').hide();
                    jQuery('#rac_confirm_unsubscription_text_color').parent().parent().hide();
                } else {
                    jQuery('#rac_unsubscription_redirect_url').parent().parent().hide();
                    jQuery('#rac_unsubscription_message_text_color').closest('tr').hide();
                    jQuery('#rac_unsubscription_message_background_color').closest('tr').hide();
                    jQuery('#rac_manual_unsubscription_redirect_url').parent().parent().show();
                    jQuery('#rac_confirm_unsubscription_text').parent().parent().show();
                    jQuery('#rac_unsubscription_email_text_color').closest('tr').show();
                    jQuery('#rac_confirm_unsubscription_text_color').parent().parent().show();
                }
                jQuery("input[name='rac_unsubscription_type']").change(function () {
                    if (jQuery(this).val() == '1') {
                        jQuery('#rac_unsubscription_redirect_url').parent().parent().show();
                        jQuery('#rac_unsubscription_message_text_color').closest('tr').show();
                        jQuery('#rac_unsubscription_message_background_color').closest('tr').show();
                        jQuery('#rac_manual_unsubscription_redirect_url').parent().parent().hide();
                        jQuery('#rac_confirm_unsubscription_text').parent().parent().hide();
                        jQuery('#rac_unsubscription_email_text_color').closest('tr').hide();
                        jQuery('#rac_confirm_unsubscription_text_color').parent().parent().hide();
                    } else {
                        jQuery('#rac_unsubscription_redirect_url').parent().parent().hide();
                        jQuery('#rac_unsubscription_message_text_color').closest('tr').hide();
                        jQuery('#rac_unsubscription_message_background_color').closest('tr').hide();
                        jQuery('#rac_manual_unsubscription_redirect_url').parent().parent().show();
                        jQuery('#rac_confirm_unsubscription_text').parent().parent().show();
                        jQuery('#rac_unsubscription_email_text_color').closest('tr').show();
                        jQuery('#rac_confirm_unsubscription_text_color').parent().parent().show();
                    }
                });
            });</script>
        <?php
    }

    public static function rac_selected_users_restrict_option() {
        global $woocommerce;
        ?>
        <script type="text/javascript">
        <?php if ((float) $woocommerce->version <= (float) ('2.2.0')) { ?>
                jQuery(function () {
                    jQuery('select.custom_user_name_select_for_restrict_in_cart_list').ajaxChosen({
                        method: 'GET',
                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
                        dataType: 'json',
                        afterTypeDelay: 100,
                        data: {
                            action: 'woocommerce_json_search_customers',
                            security: '<?php echo wp_create_nonce("search-customers"); ?>'
                        }
                    }, function (data) {
                        var terms = {};
                        jQuery.each(data, function (i, val) {
                            terms[i] = val;
                        });
                        return terms;
                    });
                });
        <?php } ?>
        </script>


        <?php if ((float) $woocommerce->version <= (float) ('2.2.0')) { ?>
            <tr valign="top">
                <th class="titledesc" scope="row">
                    <label for="custom_user_name_select_for_restrict_in_cart_list"><?php _e('User Name Selected', 'recoverabandoncart'); ?></label>
                </th>
                <td>
                    <select name="custom_user_name_select_for_restrict_in_cart_list[]" multiple="multiple" id="custom_user_name_select_for_restrict_in_cart_list" class="short custom_user_name_select_for_restrict_in_cart_list">
                        <?php
                        $json_ids = array();
                        $getuser = get_option('custom_user_name_select_for_restrict_in_cart_list');
                        if ($getuser != "") {
                            $listofuser = $getuser;
                            if (!is_array($listofuser)) {
                                $userids = array_filter(array_map('absint', (array) explode(',', $listofuser)));
                            } else {
                                $userids = $listofuser;
                            }

                            foreach ($userids as $userid) {
                                $user = get_user_by('id', $userid);
                                ?>
                                <option value="<?php echo $userid; ?>" selected="selected"><?php echo esc_html($user->display_name) . ' (#' . absint($user->ID) . ' &ndash; ' . esc_html($user->user_email); ?></option>
                                <?php
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>
        <?php } else { ?>
            <tr valign="top">
                <th class="titledesc" scope="row">
                    <label for="custom_user_name_select_for_restrict_in_cart_list"><?php _e('User Name Selected', 'recoverabandoncart'); ?></label>
                </th>
                <td>
                    <input type="hidden" class="wc-customer-search" name="custom_user_name_select_for_restrict_in_cart_list" id="custom_user_name_select_for_restrict_in_cart_list" data-multiple="true" data-placeholder="<?php _e('Search for a customer&hellip;', 'recoverabandoncart'); ?>" data-selected="<?php
                           $json_ids = array();
                           $getuser = get_option('custom_user_name_select_for_restrict_in_cart_list');
                           if ($getuser != "") {
                               $listofuser = $getuser;
                               if (!is_array($listofuser)) {
                                   $userids = array_filter(array_map('absint', (array) explode(',', $listofuser)));
                               } else {
                                   $userids = $listofuser;
                               }

                               foreach ($userids as $userid) {
                                   $user = get_user_by('id', $userid);
                                   $json_ids[$user->ID] = esc_html($user->display_name) . ' (#' . absint($user->ID) . ' &ndash; ' . esc_html($user->user_email);
                               }echo esc_attr(json_encode($json_ids));
                           }
                           ?>" value="<?php echo implode(',', array_keys($json_ids)); ?>" data-allow_clear="true" />
                </td>
            </tr>
            <?php
        }
    }

// Unset Cookie in RAC when they process placeorder
}

include_once (ABSPATH . 'wp-admin/includes/plugin.php');
add_action('init', array('RecoverAbandonCart', 'fprac_check_woo_active'));
//add_action('wp_head', array('RecoverAbandonCart', 'wow'));
if (isset($_GET['page'])) {
    if (($_GET['page'] == 'fprac_slug')) {
        add_action('admin_head', array('RecoverAbandonCart', 'get_user_role_jquery'));
    }
}

add_action('admin_menu', array('RecoverAbandonCart', 'fprac_admin_submenu'));
add_action('admin_init', array('RecoverAbandonCart', 'fp_rac_reset_general'));

$fp_rac = plugin_basename(__FILE__);
add_filter("plugin_action_links_$fp_rac", array('RecoverAbandonCart', 'fp_rac_settings_link'));
add_filter('woocommerce_fprac_settings_tabs_array', array('RecoverAbandonCart', 'fprac_settings_tabs'));
if (isset($_GET['page'])) {
    if ($_GET['page'] == 'fprac_slug') {
        add_filter('woocommerce_screen_ids', array('RecoverAbandonCart', 'fprac_access_woo_script'), 9, 1);
    }
}

require_once 'inc/fp_rac_cron.php';
register_activation_hook(__FILE__, array('RecoverAbandonCart', 'create_load_table'));

add_action('wp_login', array('RecoverAbandonCart', 'remove_action_hook'), 1);
add_action('admin_head', array('RecoverAbandonCart', 'add_script_on_coupon_tab'));
add_action('woocommerce_update_options_fpraccoupon', array('RecoverAbandonCart', 'save_product_to_exclude'));
add_action('woocommerce_update_options_fpraccoupon', array('RecoverAbandonCart', 'save_product_to_include'));
add_action('woocommerce_admin_field_rac_coupon_exclude_products', array('RecoverAbandonCart', 'rac_select_product_to_exclude'));
add_action('woocommerce_admin_field_rac_coupon_include_products', array('RecoverAbandonCart', 'rac_select_product_to_include'));

add_action('init', array('RecoverAbandonCart', 'fprac_header_problems'));
add_action('woocommerce_fprac_settings_tabs_fpracgenral', array('RecoverAbandonCart', 'fp_rac_admin_setting_general'));
add_action('woocommerce_update_options_fpracgenral', array('RecoverAbandonCart', 'fp_rac_update_options_general'));
add_action('woocommerce_fprac_settings_tabs_fpracdebug', array('RecoverAbandonCart', 'fp_rac_admin_setting_troubleshoot'));
add_action('woocommerce_update_options_fpracdebug', array('RecoverAbandonCart', 'fp_rac_update_options_troubleshoot'));
add_action('woocommerce_fprac_settings_tabs_fpracemail', array('RecoverAbandonCart', 'fp_rac_admin_setting_email'));
add_action('woocommerce_update_options_fpracemail', array('RecoverAbandonCart', 'fp_rac_update_options_email'));
add_action('woocommerce_fprac_settings_tabs_fpraccoupon', array('RecoverAbandonCart', 'fp_rac_admin_setting_coupon'));
add_action('woocommerce_update_options_fpraccoupon', array('RecoverAbandonCart', 'fp_rac_update_options_coupon'));
add_action('woocommerce_admin_field_rac_exclude_users_list', array('RecoverAbandonCart', 'rac_selected_users_exclude_option'));
add_action('woocommerce_admin_field_rac_exclude_users_list_for_restrict_in_cart_list', array('RecoverAbandonCart', 'rac_selected_users_restrict_option'));
add_action('woocommerce_init', array('RecoverAbandonCart', 'fp_rac_add_abandon_cart'));
add_action('woocommerce_cart_updated', array('RecoverAbandonCart', 'fp_rac_insert_entry'));

add_action('admin_enqueue_scripts', array('RecoverAbandonCart', 'fp_rac_admin_scritps'));
add_action('wp_ajax_rac_new_template', array('RecoverAbandonCart', 'fp_rac_create_new_email_template'));
add_action('wp_ajax_rac_edit_template', array('RecoverAbandonCart', 'fp_rac_edit_email_template'));
add_action('wp_ajax_rac_send_template_preview_email', array('RecoverAbandonCart', 'fp_rac_send_email_template_test_mail'));
add_action('wp_ajax_copy_this_template', array('RecoverAbandonCart', 'fp_rac_copy_email_template'));
add_action('wp_ajax_rac_delete_email_template', array('RecoverAbandonCart', 'fp_rac_delete_email_template'));

add_action('wp_ajax_deletecartlist', array('RecoverAbandonCart', 'delete_all_rac_list'));
add_action('wp_ajax_rac_delete_individual_list', array('RecoverAbandonCart', 'delete_individual_rac_list'));
add_action('wp_ajax_deletemaillog', array('RecoverAbandonCart', 'delete_all_rac_log'));
add_action('wp_ajax_rac_delete_individual_log', array('RecoverAbandonCart', 'delete_individual_rac_log'));
add_action('wp_ajax_rac_start_stop_mail', array('RecoverAbandonCart', 'set_mail_sending_opt'));
add_action('wp_ajax_rac_email_template_status', array('RecoverAbandonCart', 'set_email_template_status'));
add_action('wp_ajax_rac_load_mail_message', array('RecoverAbandonCart', 'rac_load_mail_message'));
add_action('wp_ajax_rac_manual_mail_ajax', array('RecoverAbandonCart', 'rac_send_manual_mail'));
add_action('wp_ajax_rac_send_test_mail', array('RecoverAbandonCart', 'rac_send_test_mail'));

add_action('wp_ajax_nopriv_rac_preadd_guest', array('RecoverAbandonCart', 'fp_rac_guest_entry_checkout_ajax'));

add_action('woocommerce_checkout_order_processed', array('RecoverAbandonCart', 'fp_rac_cookies_for_cart_recover'));
add_action('woocommerce_thankyou', array('RecoverAbandonCart', 'clear_cookie'));

add_action('woocommerce_order_status_completed', array('RecoverAbandonCart', 'fp_rac_check_order_status'));
add_action('woocommerce_order_status_processing', array('RecoverAbandonCart', 'fp_rac_check_order_status'));

$order_list = get_option('rac_mailcartlist_change');
if (is_array($order_list) && (!empty($order_list))) {
    foreach ($order_list as $each_list) {
        add_action('woocommerce_order_status_' . $each_list, array('RecoverAbandonCart', 'fp_rac_check_order_status'));
    }
}

add_action('wp_ajax_edit_value_update_now', array('RecoverAbandonCart', 'fp_rac_edit_mail_update_data'));
//ASN

add_action('woocommerce_checkout_order_processed', array('RecoverAbandonCart', 'fp_rac_insert_guest_entry'));
add_action('woocommerce_order_status_changed', array('RecoverAbandonCart', 'fp_rac_order_status_guest'), 10, 3);
add_action('plugins_loaded', array('RecoverAbandonCart', 'rac_translate_file'));

register_activation_hook(__FILE__, array('RecoverAbandonCart', 'fprac_default_settings'));
register_activation_hook(__FILE__, array('FPRacCron', 'fp_rac_cron_job_setting'));
register_activation_hook(__FILE__, array('RecoverAbandonCart', 'fprac_header_problems'));


add_filter('cron_schedules', array('FPRacCron', 'fp_rac_add_x_hourly'));
add_action('update_option_rac_abandon_cart_cron_type', array('FPRacCron', 'fp_rac_cron_job_setting_savings'));
add_action('update_option_rac_abandon_cron_time', array('FPRacCron', 'fp_rac_cron_job_setting_savings'));

add_action('rac_cron_job', array('FPRacCron', 'fp_rac_cron_job_mailing'));
add_action('wp_head', array('FPRacCron', 'rac_delete_abandon_carts_after_selected_days'));
add_action('admin_head', array('FPRacCron', 'rac_delete_abandon_carts_after_selected_days'));
add_action('wp_head', array('RecoverAbandonCart', 'fp_rac_guest_cart_recover'));
add_action('wp_head', array('RecoverAbandonCart', 'recover_old_order_rac'));



if ((get_option('rac_load_script_styles') == 'wp_head') || !get_option('rac_load_script_styles')) {
    add_action('wp_head', array('RecoverAbandonCart', 'fp_rac_checkout_script'), 999);
} else {
    add_action('wp_footer', array('RecoverAbandonCart', 'fp_rac_checkout_script'));
}




//add_action('woocommerce_payment_complete', array('RecoverAbandonCart', 'remove_member_acart_on_orderplaced'));
add_action('woocommerce_checkout_order_processed', array('RecoverAbandonCart', 'remove_member_acart_on_orderplaced'));

add_action('admin_head', array('RecoverAbandonCart', 'fp_rac_troubleshoot_mailsend'));

//add_action('admin_head', array('RecoverAbandonCart', 'template_ready'));

add_action('wp_head', array('RecoverAbandonCart', 'unsubscribed_user_from_rac_mail'));

if (get_option('rac_unsub_myaccount_option') == 'yes') {
    add_action('woocommerce_before_my_account', array('RecoverAbandonCart', 'add_undo_unsubscribe_option_myaccount'));
}
add_action('wp_ajax_fp_rac_undo_unsubscribe', array('RecoverAbandonCart', 'response_unsubscribe_option_myaccount'));

add_shortcode('rac.unsubscribe_email_manual', array('RecoverAbandonCart', 'manual_unsubscribe_option'));


add_action('admin_head', array('RecoverAbandonCart', 'add_styles_in_general_tab'));


//For Deletion of coupon code
require_once 'inc/fp_rac_coupon_deletion.php';
include 'inc/fp_rac_add_cancelled_order_immediately.php';
//For Backword Compatibility
require_once 'inc/rac_settings_backward_compatibility.php';

//For WPML
function fp_get_wpml_text($option_name, $language, $message) {
    if (function_exists('icl_register_string')) {
        if ($language == 'en') {
            return $message;
        } else {
            global $wpdb;
            $context = 'RAC';

            $res = $wpdb->get_results($wpdb->prepare("
            SELECT s.name, s.value, t.value AS translation_value, t.status
            FROM  {$wpdb->prefix}icl_strings s
            LEFT JOIN {$wpdb->prefix}icl_string_translations t ON s.id = t.string_id
            WHERE s.context = %s
                AND (t.language = %s OR t.language IS NULL)
            ", $context, $language), ARRAY_A);
            foreach ($res as $each_entry) {
                if ($each_entry['name'] == $option_name) {
                    if ($each_entry['translation_value']) {
                        $translated = $each_entry['translation_value'];
                    } else {
                        $translated = $each_entry['value'];
                    }
                }
            }
            return $translated;
        }
    } else {
        return $message;
    }
}

function rac_register_template_for_wpml() {

    if (function_exists('icl_register_string')) {

        global $wpdb;
        $context = 'RAC';
        $template_table = $wpdb->prefix . 'rac_templates_email';
        $re = $wpdb->get_results("SELECT * FROM $template_table");
        foreach ($re as $each_template) {
            $name_msg = 'rac_template_' . $each_template->id . '_message';
            $value_msg = $each_template->message;
            icl_register_string($context, $name_msg, $value_msg); //for registering message
            $name_sub = 'rac_template_' . $each_template->id . '_subject';
            $value_sub = $each_template->subject;
            icl_register_string($context, $name_sub, $value_sub); //for registering subject

            $name_anchortext = 'rac_template_' . $each_template->id . '_anchor_text';
            $getvalue_anchortext = $each_template->anchor_text;
            icl_register_string($context, $name_anchortext, $getvalue_anchortext);

            $productname = 'rac_template_product_name';
            $getvalue_productname = get_option('rac_product_info_product_name');
            icl_register_string($context, $productname, $getvalue_productname);

            $productimage = 'rac_template_product_image';
            $getvalue_productimage = get_option('rac_product_info_product_image');
            icl_register_string($context, $productimage, $getvalue_productimage);


            $productquantity = 'rac_template_product_quantity';
            $getvalue_quantity = get_option('rac_product_info_quantity');
            icl_register_string($context, $productquantity, $getvalue_quantity);

            $productprice = 'rac_template_product_price';
            $getvalue_productprice = get_option('rac_product_info_product_price');
            icl_register_string($context, $productprice, $getvalue_productprice);

            $subtotal = 'rac_template_subtotal';
            $getvalue_subtotal = get_option('rac_product_info_subtotal');
            icl_register_string($context, $subtotal, $getvalue_subtotal);

            $tax = 'rac_template_tax';
            $getvalue_tax = get_option('rac_product_info_tax');
            icl_register_string($context, $tax, $getvalue_tax);

            $total = 'rac_template_total';
            $getvalue_total = get_option('rac_product_info_total');
            icl_register_string($context, $total, $getvalue_total);
        }
    }
}

add_action('admin_init', 'rac_register_template_for_wpml');
