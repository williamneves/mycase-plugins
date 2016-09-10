<?php

class FPRacCron {

    public static function fp_rac_cron_job_setting() {
        wp_clear_scheduled_hook('rac_cron_job');
        if (wp_next_scheduled('rac_cron_job') == false) {
            wp_schedule_event(time(), 'xhourly', 'rac_cron_job');
        }
    }

//on save clear and set the cron again
    public static function fp_rac_cron_job_setting_savings() {
        wp_clear_scheduled_hook('rac_cron_job');
        if (wp_next_scheduled('rac_cron_job') == false) {
            wp_schedule_event(time(), 'xhourly', 'rac_cron_job');
        }
    }

    public static function fp_rac_add_x_hourly($schedules) {

        $interval = get_option('rac_abandon_cron_time');
        if (get_option('rac_abandon_cart_cron_type') == 'minutes') {
            $interval = $interval * 60;
        } else if (get_option('rac_abandon_cart_cron_type') == 'hours') {
            $interval = $interval * 3600;
        } else if (get_option('rac_abandon_cart_cron_type') == 'days') {
            $interval = $interval * 86400;
        }
        $schedules['xhourly'] = array(
            'interval' => $interval,
            'display' => 'X Hourly'
        );
        return $schedules;
    }

    public static function get_rac_formatprice($price) {
        if (function_exists('woocommerce_price')) {
            return woocommerce_price($price);
        } else {
            if (function_exists('wc_price')) {
                return wc_price($price);
            }
        }
    }

    public static function mailing() {
        foreach ($email_templates as $emails) {
            
        }
    }

    public static function email_woocommerce_html($html_template, $subject, $message, $logo) {
        if (($html_template == 'HTML')) {
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
        } else {

            $woo_temp_msg = $logo . $message;
        }

        return $woo_temp_msg;
    }

    public static function rac_delete_abandon_carts_after_selected_days() {
        global $wpdb;
        $abandancart_table_name = $wpdb->prefix . 'rac_abandoncart';
        $abandon_carts = $wpdb->get_results("SELECT * FROM $abandancart_table_name WHERE cart_status='ABANDON'");
        if (get_option('enable_remove_abandon_after_x_days') == 'yes') {
            foreach ($abandon_carts as $each_cart) {
                $duration = get_option('rac_remove_abandon_after_x_days') * 86400;
                $cut_off_time = $each_cart->cart_abandon_time + $duration;
                $current_time = strtotime(date('Y-m-d h:i:s'));
                if ($current_time >= $cut_off_time) {
                    $wpdb->query("DELETE FROM $abandancart_table_name WHERE id=$each_cart->id");
                }
            }
        }
    }

    public static function fp_rac_cron_job_mailing() {
        global $wpdb;
        $emailtemplate_table_name = $wpdb->prefix . 'rac_templates_email';
        $abandancart_table_name = $wpdb->prefix . 'rac_abandoncart';
        $email_templates = $wpdb->get_results("SELECT * FROM $emailtemplate_table_name"); //all email templates
// For Members
        if (get_option('rac_email_use_members') == 'yes') {
            $abandon_carts = $wpdb->get_results("SELECT * FROM $abandancart_table_name WHERE cart_status='ABANDON' AND user_id NOT IN('0','old_order')  AND placed_order IS NULL AND completed IS NULL"); //Selected only cart which are not completed
            foreach ($abandon_carts as $each_cart) {
                foreach ($email_templates as $emails) {
                    if ($emails->status == "ACTIVE") {
                        $find_user = 'member';
                        self::send_mail_by_mail_sending_option($each_cart, $emails, $find_user);
                    }
                }
            }
        }
// FOR GUEST
        if (get_option('rac_email_use_guests') == 'yes') {
            $abandon_carts = $wpdb->get_results("SELECT * FROM $abandancart_table_name WHERE cart_status='ABANDON' AND user_id='0' AND placed_order IS NULL and ip_address IS NULL AND completed IS NULL"); //Selected only cart which are not completed
            foreach ($abandon_carts as $each_cart) {
                foreach ($email_templates as $emails) {
                    if ($emails->status == "ACTIVE") {
                        $find_user = 'guest1';
                        self::send_mail_by_mail_sending_option($each_cart, $emails, $find_user);
                    }
                }
            }
//FOR Guest Captured in chcekout page
            $abandon_carts = $wpdb->get_results("SELECT * FROM $abandancart_table_name WHERE cart_status='ABANDON' and placed_order IS NULL  AND user_id='0' AND ip_address IS NOT NULL AND completed IS NULL"); //Selected only cart which are not completed
            foreach ($abandon_carts as $each_cart) {
                foreach ($email_templates as $emails) {
                    $cart_array = maybe_unserialize($each_cart->cart_details);
                    if ($emails->status == "ACTIVE") {
                        $find_user = 'guest2';
                        self::send_mail_by_mail_sending_option($each_cart, $emails, $find_user);
                    }
                }
            }
        }
// FOR ORDER UPDATED FROM OLD
        $abandon_carts = $wpdb->get_results("SELECT * FROM $abandancart_table_name WHERE cart_status='ABANDON' AND user_id='old_order' AND placed_order IS NULL AND ip_address IS NULL AND completed IS NULL"); //Selected only cart which are not completed
        foreach ($abandon_carts as $each_cart) {
            foreach ($email_templates as $emails) {
                $cart_array = maybe_unserialize($each_cart->cart_details);
                $id = $cart_array->id;
                $order_obj = new WC_Order($id);
                $main_check = '0';
                if ($order_obj->user_id != '') {
                    if (get_option('rac_email_use_members') == 'yes') {
                        // For Controlling Email Id for Member/Guest
                        $main_check = '1';
                    }
                } else {
                    if (get_option('rac_email_use_guests') == 'yes') {
                        $main_check = '1';
                    }
                }
                if ($emails->status == "ACTIVE") {
                    if ($main_check != '0') {
                        $find_user = 'old_order';
                        self::send_mail_by_mail_sending_option($each_cart, $emails, $find_user);
                    }
                }
            }
        }
    }

    public static function send_mail_by_mail_sending_option($each_cart, $emails, $find_user) {
        global $wpdb, $to;
        $abandancart_table_name = $wpdb->prefix . 'rac_abandoncart';
        $tablecheckproduct = fp_rac_extract_cart_details($each_cart);
        $sent_mail_templates = '';
        if (empty($each_cart->mail_template_id)) { // IF EMPTY IT IS NOT SENT FOR ANY SINGLE TEMPLATE
            if ($emails->sending_type == 'hours') {
                $duration = $emails->sending_duration * 3600;
            } else if ($emails->sending_type == 'minutes') {
                $duration = $emails->sending_duration * 60;
            } else if ($emails->sending_type == 'days') {
                $duration = $emails->sending_duration * 86400;
            }
            //duration is finished
            $cut_off_time = $each_cart->cart_abandon_time + $duration;
            $date = date('d:m:y', $each_cart->cart_abandon_time);
            $time = date('h:i:s', $each_cart->cart_abandon_time);
            $current_time = current_time('timestamp');
            if ($current_time > $cut_off_time) {
                if (function_exists('wc_get_page_permalink')) {
                    @$cart_url = wc_get_page_permalink('cart');
                } else {
                    @$cart_url = WC()->cart->get_cart_url();
                }

                if ($find_user == 'member') {
                    $url_to_click = esc_url_raw(add_query_arg(array('abandon_cart' => $each_cart->id, 'email_template' => $emails->id), $cart_url));
                    $user = get_userdata($each_cart->user_id);
                    $to = $user->user_email;
                    $firstname = $user->user_firstname;
                    $lastname = $user->user_lastname;
                } elseif ($find_user == 'guest1') {
                    $url_to_click = esc_url_raw(add_query_arg(array('abandon_cart' => $each_cart->id, 'email_template' => $emails->id, 'guest' => 'yes'), $cart_url));
                    @$order_object = maybe_unserialize($each_cart->cart_details);
                    $to = $order_object->billing_email;
                    $subject = fp_get_wpml_text('rac_template_' . $emails->id . '_subject', $each_cart->wpml_lang, $emails->subject);
                    $firstname = $order_object->billing_first_name;
                    $lastname = $order_object->billing_last_name;
                } elseif ($find_user == 'guest2') {
                    $url_to_click = esc_url_raw(add_query_arg(array('abandon_cart' => $each_cart->id, 'email_template' => $emails->id, 'guest' => 'yes'), $cart_url));
                    @$order_object = maybe_unserialize($each_cart->cart_details);
                    $to = $order_object['visitor_mail'];
                    $subject = fp_get_wpml_text('rac_template_' . $emails->id . '_subject', $each_cart->wpml_lang, $emails->subject);
                    $firstname = $order_object['first_name'];
                    $lastname = $order_object['last_name'];
                } elseif ($find_user == 'old_order') {
                    $url_to_click = esc_url_raw(add_query_arg(array('abandon_cart' => $each_cart->id, 'email_template' => $emails->id, 'old_order' => 'yes'), $cart_url));
                    @$cart_array = maybe_unserialize($each_cart->cart_details);
                    $id = $cart_array->id;
                    $order_object = new WC_Order($id);
                    $to = $order_object->billing_email;
                    $subject = fp_get_wpml_text('rac_template_' . $emails->id . '_subject', $each_cart->wpml_lang, $emails->subject);
                    $firstname = $order_object->billing_first_name;
                    $lastname = $order_object->billing_last_name;
                }

                if (get_option('rac_cart_link_options') == '1') {
                    $url_to_click = '<a style="color:#' . get_option("rac_email_link_color") . '"  href="' . $url_to_click . '">' . fp_get_wpml_text('rac_template_' . $emails->id . '_anchor_text', $each_cart->wpml_lang, $emails->anchor_text) . '</a>';
                } elseif (get_option('rac_cart_link_options') == '2') {
                    $url_to_click = $url_to_click;
                } else {
                    $cart_Text = fp_get_wpml_text('rac_template_' . $emails->id . '_anchor_text', $each_cart->wpml_lang, $emails->anchor_text);
                    $url_to_click = RecoverAbandonCart::rac_cart_link_button_mode($url_to_click, $cart_Text);
                }

                $subject = fp_get_wpml_text('rac_template_' . $emails->id . '_subject', $each_cart->wpml_lang, $emails->subject);

                $message = fp_get_wpml_text('rac_template_' . $emails->id . '_message', $each_cart->wpml_lang, $emails->message);
                $subject = RecoverAbandonCart::shortcode_in_subject($firstname, $lastname, $subject);
                $message = str_replace('{rac.cartlink}', $url_to_click, $message);
                $message = str_replace('{rac.date}', $date, $message);
                $message = str_replace('{rac.time}', $time, $message);
                $message = str_replace('{rac.firstname}', $firstname, $message);
                $message = str_replace('{rac.lastname}', $lastname, $message);
                $message = str_replace('{rac.Productinfo}', $tablecheckproduct, $message);

                if (strpos($message, "{rac.coupon}")) {
                    if ($find_user == 'member') {
                        $coupon_code = FPRacCoupon::rac_create_coupon($user->user_email, $each_cart->cart_abandon_time);
                    } elseif ($find_user == 'guest1' || $find_user == 'old_order') {
                        $coupon_code = FPRacCoupon::rac_create_coupon($order_object->billing_email, $each_cart->cart_abandon_time);
                    } elseif ($find_user == 'guest2') {
                        $coupon_code = FPRacCoupon::rac_create_coupon($order_object['visitor_mail'], $each_cart->cart_abandon_time);
                    }
                    update_option('abandon_time_of' . $each_cart->id, $coupon_code);
                    $message = str_replace('{rac.coupon}', $coupon_code, $message); //replacing shortcode with coupon code
                }
                $message = RecoverAbandonCart::rac_unsubscription_shortcode($to, $message);
                add_filter('woocommerce_email_footer_text', array('RecoverAbandonCart', 'rac_footer_email_customization'));
                $message = do_shortcode($message); //shortcode feature

                $html_template = $emails->mail; // mail send plain or html

                $logo = '<table><tr><td align="center" valign="top"><p style="margin-top:0;"><img style="max-height:600px;max-width:600px;"  src="' . esc_url($emails->link) . '" /></p></td></tr></table>'; // mail uploaded

                $woo_temp_msg = self::email_woocommerce_html($html_template, $subject, $message, $logo); // mail send plain or html

                $headers = "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
                if ($emails->sender_opt == 'local') {
                    $headers .= self::rac_formatted_from_address_local($emails->from_name, $emails->from_email);
                    $headers .= "Reply-To: " . $emails->from_name . "<" . $emails->from_email . ">\r\n";
                } else {
                    $headers .= self::rac_formatted_from_address_woocommerce();
                    $headers .= "Reply-To: " . get_option('woocommerce_email_from_name') . " <" . get_option('woocommerce_email_from_address') . ">\r\n";
                }
                if ($emails->rac_blind_carbon_copy) {
                    $headers .= "Bcc: " . $emails->rac_blind_carbon_copy . "\r\n";
                }
                if ($each_cart->sending_status == 'SEND') {
                    if ('wp_mail' == get_option('rac_trouble_mail')) {
                        if (self::rac_send_wp_mail($to, $subject, $woo_temp_msg, $headers, $html_template)) {
                            $sent_mail_templates[] = $emails->id;
                            $store_template_id = maybe_serialize($sent_mail_templates);
                            $wpdb->update($abandancart_table_name, array('mail_template_id' => $store_template_id), array('id' => $each_cart->id));
                            $table_name_logs = $wpdb->prefix . 'rac_email_logs';
                            $wpdb->insert($table_name_logs, array("email_id" => $to, "date_time" => $current_time, "rac_cart_id" => $each_cart->id, "template_used" => $emails->id));
                            FPRacCounter::rac_do_mail_count();
                            FPRacCounter::email_count_by_template($emails->id);
                        }
                    } else {
                        if (self::rac_send_mail($to, $subject, $woo_temp_msg, $headers)) {
                            $sent_mail_templates[] = $emails->id;
                            $store_template_id = maybe_serialize($sent_mail_templates);
                            $wpdb->update($abandancart_table_name, array('mail_template_id' => $store_template_id), array('id' => $each_cart->id));
                            $table_name_logs = $wpdb->prefix . 'rac_email_logs';
                            $wpdb->insert($table_name_logs, array("email_id" => $to, "date_time" => $current_time, "rac_cart_id" => $each_cart->id, "template_used" => $emails->id));
                            FPRacCounter::rac_do_mail_count();
                            FPRacCounter::email_count_by_template($emails->id);
                        }
                    }
                }
            }
        }  // IF EMPTY IT IS NOT SENT FOR ANY SINGLE TEMPLATE END
        elseif (!empty($each_cart->mail_template_id)) {
            $sent_mail_templates = maybe_unserialize($each_cart->mail_template_id);
            if (!in_array($emails->id, (array) $sent_mail_templates)) {
                if ($emails->sending_type == 'hours') {
                    $duration = $emails->sending_duration * 3600;
                } else if ($emails->sending_type == 'minutes') {
                    $duration = $emails->sending_duration * 60;
                } else if ($emails->sending_type == 'days') {
                    $duration = $emails->sending_duration * 86400;
                }//duration is finished
                $cut_off_time = $each_cart->cart_abandon_time + $duration;
                $date = date('d:m:y', $each_cart->cart_abandon_time);
                $time = date('h:i:s', $each_cart->cart_abandon_time);
                $current_time = current_time('timestamp');
                if ($current_time > $cut_off_time) {
                    if (function_exists('wc_get_page_permalink')) {
                        @$cart_url = wc_get_page_permalink('cart');
                    } else {
                        @$cart_url = WC()->cart->get_cart_url();
                    }
                    $url_to_click = esc_url_raw(add_query_arg(array('abandon_cart' => $each_cart->id, 'email_template' => $emails->id), $cart_url));
                    if (get_option('rac_cart_link_options') == '1') {
                        $url_to_click = '<a style="color:#' . get_option("rac_email_link_color") . '"  href="' . $url_to_click . '">' . fp_get_wpml_text('rac_template_' . $emails->id . '_anchor_text', $each_cart->wpml_lang, $emails->anchor_text) . '</a>';
                    } elseif (get_option('rac_cart_link_options') == '2') {
                        $url_to_click = $url_to_click;
                    } else {
                        $cart_Text = fp_get_wpml_text('rac_template_' . $emails->id . '_anchor_text', $each_cart->wpml_lang, $emails->anchor_text);
                        $url_to_click = RecoverAbandonCart::rac_cart_link_button_mode($url_to_click, $cart_Text);
                    }
                    if ($find_user == 'member') {
                        $user = get_userdata($each_cart->user_id);
                        $to = $user->user_email;
                        $firstname = $user->user_firstname;
                        $lastname = $user->user_lastname;
                    } elseif ($find_user == 'guest1') {
                        @$order_object = maybe_unserialize($each_cart->cart_details);
                        $to = $order_object->billing_email;
                        $subject = fp_get_wpml_text('rac_template_' . $emails->id . '_subject', $each_cart->wpml_lang, $emails->subject);
                        $firstname = $order_object->billing_first_name;
                        $lastname = $order_object->billing_last_name;
                    } elseif ($find_user == 'guest2') {
                        @$order_object = maybe_unserialize($each_cart->cart_details);
                        $to = $order_object['visitor_mail'];
                        $subject = fp_get_wpml_text('rac_template_' . $emails->id . '_subject', $each_cart->wpml_lang, $emails->subject);
                        $firstname = $order_object['first_name'];
                        $lastname = $order_object['last_name'];
                    } elseif ($find_user == 'old_order') {
                        @$cart_array = maybe_unserialize($each_cart->cart_details);
                        $id = $cart_array->id;
                        $order_object = new WC_Order($id);
                        $to = $order_object->billing_email;
                        $subject = fp_get_wpml_text('rac_template_' . $emails->id . '_subject', $each_cart->wpml_lang, $emails->subject);
                        $firstname = $order_object->billing_first_name;
                        $lastname = $order_object->billing_last_name;
                    }
                    $subject = fp_get_wpml_text('rac_template_' . $emails->id . '_subject', $each_cart->wpml_lang, $emails->subject);
                    $message = fp_get_wpml_text('rac_template_' . $emails->id . '_message', $each_cart->wpml_lang, $emails->message);
                    $subject = RecoverAbandonCart::shortcode_in_subject($firstname, $lastname, $subject);
                    $message = str_replace('{rac.cartlink}', $url_to_click, $message);
                    $message = str_replace('{rac.date}', $date, $message);
                    $message = str_replace('{rac.time}', $time, $message);
                    $message = str_replace('{rac.firstname}', $firstname, $message);
                    $message = str_replace('{rac.lastname}', $lastname, $message);
                    $message = str_replace('{rac.Productinfo}', $tablecheckproduct, $message);
                    if (strpos($message, "{rac.coupon}")) {
                        if ($find_user == 'member') {
                            $coupon_code = FPRacCoupon::rac_create_coupon($user->user_email, $each_cart->cart_abandon_time);
                        } elseif ($find_user == 'guest1' || $find_user == 'old_order') {
                            $coupon_code = FPRacCoupon::rac_create_coupon($order_object->billing_email, $each_cart->cart_abandon_time);
                        } elseif ($find_user == 'guest2') {
                            $coupon_code = FPRacCoupon::rac_create_coupon($order_object['visitor_mail'], $each_cart->cart_abandon_time);
                        }
                        update_option('abandon_time_of' . $each_cart->id, $coupon_code);
                        $message = str_replace('{rac.coupon}', $coupon_code, $message); //replacing shortcode with coupon code
                    }
                    $message = RecoverAbandonCart::rac_unsubscription_shortcode($to, $message);
                    add_filter('woocommerce_email_footer_text', array('RecoverAbandonCart', 'rac_footer_email_customization'));
                    $message = do_shortcode($message); //shortcode feature

                    $html_template = $emails->mail; // mail send plain or html

                    $logo = '<table><tr><td align="center" valign="top"><p style="margin-top:0;"><img style="max-height:600px;max-width:600px;" src="' . esc_url($emails->link) . '" /></p></td></tr></table>'; // mail uploaded

                    $woo_temp_msg = self::email_woocommerce_html($html_template, $subject, $message, $logo); // mail send plain or html

                    $headers = "MIME-Version: 1.0\r\n";
                    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
                    if ($emails->sender_opt == 'local') {
                        $headers .= self::rac_formatted_from_address_local($emails->from_name, $emails->from_email);
                        $headers .= "Reply-To: " . $emails->from_name . " <" . $emails->from_email . ">\r\n";
                    } else {
                        $headers .= self::rac_formatted_from_address_woocommerce();
                        $headers .= "Reply-To: " . get_option('woocommerce_email_from_name') . "<" . get_option('woocommerce_email_from_address') . ">\r\n";
                    }
                    if ($emails->rac_blind_carbon_copy) {
                        $headers .= "Bcc: " . $emails->rac_blind_carbon_copy . "\r\n";
                    }
                    if ($each_cart->sending_status == 'SEND') {//condition to check start/stop mail sending
                        if ('wp_mail' == get_option('rac_trouble_mail')) {
                            if (self::rac_send_wp_mail($to, $subject, $woo_temp_msg, $headers, $html_template)) {
                                $sent_mail_templates[] = $emails->id;
                                $store_template_id = maybe_serialize($sent_mail_templates);
                                $wpdb->update($abandancart_table_name, array('mail_template_id' => $store_template_id), array('id' => $each_cart->id));
                                $table_name_logs = $wpdb->prefix . 'rac_email_logs';
                                $wpdb->insert($table_name_logs, array("email_id" => $to, "date_time" => $current_time, "rac_cart_id" => $each_cart->id, "template_used" => $emails->id));
                                FPRacCounter::rac_do_mail_count();
                                FPRacCounter::email_count_by_template($emails->id);
                            }
                        } else {
                            if (self::rac_send_mail($to, $subject, $woo_temp_msg, $headers)) {
                                $sent_mail_templates[] = $emails->id;
                                $store_template_id = maybe_serialize($sent_mail_templates);
                                $wpdb->update($abandancart_table_name, array('mail_template_id' => $store_template_id), array('id' => $each_cart->id));
                                $table_name_logs = $wpdb->prefix . 'rac_email_logs';
                                $wpdb->insert($table_name_logs, array("email_id" => $to, "date_time" => $current_time, "rac_cart_id" => $each_cart->id, "template_used" => $emails->id));
                                FPRacCounter::rac_do_mail_count();
                                FPRacCounter::email_count_by_template($emails->id);
                            }
                        }
                    }
                }
            }
        }
    }

    public static function rac_send_wp_mail($to, $subject, $woo_temp_msg, $headers, $html_template) {
//$get_user_by = get_user_by('email', $to);


        global $woocommerce;
        $getdesiredoption = get_option('custom_exclude');
        if ($getdesiredoption == 'user_role') {
            $userrolenamemailget = get_option('custom_user_role');
            $getuserby = get_user_by('email', $to);
            if ($getuserby) {
                $newto = $getuserby->roles[0];
            } else {
                $newto = $to;
            }
        } elseif ($getdesiredoption == 'name') {
            $userrolenamemailget = get_option('custom_user_name_select');
            $getuserby = get_user_by('email', $to);
            if ($getuserby) {
                $newto = $getuserby->ID;
            } else {
                $newto = $to;
            }
        } else {
            $userrolenamemailget = get_option('custom_mailid_edit');
            $userrolenamemailget = explode("\r\n", $userrolenamemailget);
            $newto = $to;
        }

        $check_member_guest = RecoverAbandonCart::check_is_member_or_guest($to);
        $proceed = '1';

        if ($check_member_guest) {
// for member
            $userid = RecoverAbandonCart::rac_return_user_id($to);
            $status = get_user_meta($userid, 'fp_rac_mail_unsubscribed', true);

            if ($status != 'yes') {
                $proceed = '1';
            } else {
                $proceed = '2';
            }
        } else {
// for guest
            $needle = $to;
            if (!in_array($needle, (array) get_option('fp_rac_mail_unsubscribed'))) {
                $proceed = '1';
            } else {
                $proceed = '2';
            }
        }

        if ($proceed == '1') {
            if ((float) $woocommerce->version <= (float) ('2.2.0')) {
                if (!in_array($newto, (array) $userrolenamemailget)) {

                    if (get_option('rac_webmaster_mail') == 'webmaster1') {
                        return wp_mail($to, $subject, $woo_temp_msg, $headers, '-f ' . get_option('rac_textarea_mail'));
                    } else {
                        return wp_mail($to, $subject, $woo_temp_msg, $headers);
                    }
                }
            } else {

                if (!in_array($newto, (array) $userrolenamemailget)) {
                    if ($html_template == 'HTML') {
                        $mailer = WC()->mailer();
                        $mailer->send($to, $subject, $woo_temp_msg, $headers, '');
                        return "1";
                    } else {
                        wp_mail($to, $subject, $woo_temp_msg, $headers);
                        return "1";
                    }
                }
            }
        }
    }

    public static function rac_send_mail($to, $subject, $woo_temp_msg, $headers) {
        global $woocommerce;
        $getdesiredoption = get_option('custom_exclude');
        if ($getdesiredoption == 'user_role') {
            $userrolenamemailget = get_option('custom_user_role');
            $getuserby = get_user_by('email', $to);
            if ($getuserby) {
                $newto = $getuserby->roles[0];
            } else {
                $newto = $to;
            }
        } elseif ($getdesiredoption == 'name') {
            $userrolenamemailget = get_option('custom_user_name_select');
            $getuserby = get_user_by('email', $to);
            if ($getuserby) {
                $newto = $getuserby->ID;
            } else {
                $newto = $to;
            }
        } else {
            $userrolenamemailget = get_option('custom_mailid_edit');
            $userrolenamemailget = explode("\r\n", $userrolenamemailget);
            $newto = $to;
        }



        $check_member_guest = RecoverAbandonCart::check_is_member_or_guest($to);
        $proceed = '1';
        if ($check_member_guest) {
// for member
            $userid = RecoverAbandonCart::rac_return_user_id($to);
            $status = get_user_meta($userid, 'fp_rac_mail_unsubscribed', true);
            if ($status != 'yes') {
                $proceed = '1';
            } else {
                $proceed = '2';
            }
        } else {
// for guest
            $needle = $to;
            if (!in_array($needle, (array) get_option('fp_rac_mail_unsubscribed'))) {
                $proceed = '1';
            } else {
                $proceed = '2';
            }
        }
        if ($proceed == '1') {
            if ((float) $woocommerce->version <= (float) ('2.2.0')) {
                if (!in_array($newto, (array) $userrolenamemailget)) {

                    if (get_option('rac_webmaster_mail') == 'webmaster1') {
                        return mail($to, $subject, $woo_temp_msg, $headers, '-f ' . get_option('rac_textarea_mail'));
                    } else {
                        return mail($to, $subject, $woo_temp_msg, $headers);
                    }
                }
            } else {
                if (!in_array($newto, (array) $userrolenamemailget)) {
                    $mailer = WC()->mailer();
                    $mailer->send($to, $subject, $woo_temp_msg, $headers, '');
                    return "1";
                }
            }
        }
    }

// For Test Mail Function

    public static function rac_send_wp_mail_test($to, $subject, $woo_temp_msg, $headers) {
        global $woocommerce;
        if ((float) $woocommerce->version <= (float) ('2.2.0')) {
            if (get_option('rac_webmaster_mail') == 'webmaster1') {
                return wp_mail($to, $subject, $woo_temp_msg, $headers, '-f ' . get_option('rac_textarea_mail'));
            } else {
                return wp_mail($to, $subject, $woo_temp_msg, $headers);
            }
        } else {
            $mailer = WC()->mailer();
            $mailer->send($to, $subject, $woo_temp_msg, $headers, '');
            return "1";
        }
    }

    public static function rac_send_mail_test($to, $subject, $woo_temp_msg, $headers) {
        global $woocommerce;
        if ((float) $woocommerce->version <= (float) ('2.2.0')) {
            if (get_option('rac_webmaster_mail') == 'webmaster1') {
                return mail($to, $subject, $woo_temp_msg, $headers, '-f ' . get_option('rac_textarea_mail'));
            } else {
                return mail($to, $subject, $woo_temp_msg, $headers);
            }
        } else {
            $mailer = WC()->mailer();
            $mailer->send($to, $subject, $woo_temp_msg, $headers, '');
            return "1";
        }
    }

    public static function rac_formatted_from_address_local($fromname, $fromemail) {
        if (get_option('rac_webmaster_mail') == 'webmaster1') {
            return "From: " . $fromname . " <" . $fromemail . ">" . "-f " . get_option('rac_textarea_mail') . "\r\n";
        } else {
            return "From: " . $fromname . " <" . $fromemail . ">\r\n";
        }
    }

    public static function rac_formatted_from_address_woocommerce() {
        if (get_option('rac_webmaster_mail') == 'webmaster1') {
            return "From: " . get_option('woocommerce_email_from_name') . " <" . get_option('woocommerce_email_from_address') . ">" . "-f " . get_option('rac_textarea_mail') . "\r\n";
        } else {
            return "From: " . get_option('woocommerce_email_from_name') . " <" . get_option('woocommerce_email_from_address') . ">\r\n";
        }
    }

    public static function enable_border() {
        $enable_border = get_option('rac_enable_border_for_productinfo_in_email');
        if ($enable_border != 'no') {
            return 'td';
        } else {
            return '';
        }
    }

}

//Polish the Product Info using Cart Details

function fp_rac_extract_cart_details($each_cart) {
    ob_start();
    ?>
    <table class="<?php echo FPRacCron::enable_border() ?>" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" >
        <thead>
            <?php if (get_option('rac_hide_product_name_product_info_shortcode') != 'yes') { ?>
            <th class="<?php echo FPRacCron::enable_border() ?>" scope="col" style="text-align:left;">
                <?php echo fp_get_wpml_text('rac_template_product_name', $each_cart->wpml_lang, get_option('rac_product_info_product_name')); ?>
            </th>
        <?php } if (get_option('rac_hide_product_image_product_info_shortcode') != 'yes') { ?>
            <th class="<?php echo FPRacCron::enable_border() ?>" scope="col" style="text-align:left;">
                <?php echo fp_get_wpml_text('rac_template_product_image', $each_cart->wpml_lang, get_option('rac_product_info_product_image')); ?>
            </th>
        <?php } if (get_option('rac_hide_product_quantity_product_info_shortcode') != 'yes') { ?>
            <th class="<?php echo FPRacCron::enable_border() ?>" scope="col" style="text-align:left;">
                <!-- For Quantity -->
                <?php echo fp_get_wpml_text('rac_template_product_quantity', $each_cart->wpml_lang, get_option('rac_product_info_quantity')); ?>
            </th>
        <?php } if (get_option('rac_hide_product_price_product_info_shortcode') != 'yes') { ?>
            <th class="<?php echo FPRacCron::enable_border() ?>" scope="col" style="text-align:left;">
                <?php echo fp_get_wpml_text('rac_template_product_price', $each_cart->wpml_lang, get_option('rac_product_info_product_price')); ?>
            </th>
        <?php } ?>
    </thead>
    <tbody>
        <?php
        $subtotal = '';
        $tax = '';
        $cart_array = maybe_unserialize($each_cart->cart_details);

        if (is_array($cart_array) && (!empty($cart_array))) {
            if (isset($cart_array[0]['cart'])) {
                foreach ($cart_array[0]['cart'] as $eachproduct) {
                    $product_name = get_the_title($eachproduct['product_id']);
                    if (isset($eachproduct['variation']) && (!empty($eachproduct['variation']))) {
                        $product_name = $product_name . '<br />' . fp_rac_get_formatted_variation($eachproduct['variation']);
                    }
//                $image = get_the_post_thumbnail($eachproduct['product_id'], array(90, 90));
                    $productid = $eachproduct['product_id'];
                    $imageurl = "";
                    if ((get_post_thumbnail_id($eachproduct['variation_id']) != "") || (get_post_thumbnail_id($eachproduct['variation_id']) != 0)) {
                        $image_urls = wp_get_attachment_image_src(get_post_thumbnail_id($eachproduct['variation_id']));
                        $imageurl = $image_urls[0];
                    }
                    if ($imageurl == "") {
                        if ((get_post_thumbnail_id($productid) != "") || (get_post_thumbnail_id($productid) != 0)) {
                            $image_urls = wp_get_attachment_image_src(get_post_thumbnail_id($productid));
                            $imageurl = $image_urls[0];
                        } else {
                            $imageurl = esc_url(wc_placeholder_img_src());
                        }
                    }
                    $image = '<img src="' . $imageurl . '" alt="' . get_the_title($productid) . '" height="90" width="90" />';
                    $quantity = $eachproduct['quantity'];
                    if (get_option('rac_inc_tax_with_product_price_product_info_shortcode') == 'yes') {
                        $price = $eachproduct['line_subtotal'] + $eachproduct['line_subtotal_tax'];
                        $tax = 0;
                        $subtotal += $eachproduct['line_subtotal'] + $eachproduct['line_subtotal_tax'];
                    } else {
                        $price = $eachproduct['line_subtotal'];
                        $tax += $eachproduct['line_subtotal_tax'];
                        $subtotal += $eachproduct['line_subtotal'];
                    }

                    echo fp_split_rac_items_in_cart($product_name, $image, $quantity, FP_List_Table_RAC::format_price($price));
                }
            } elseif (is_array($cart_array) && (!empty($cart_array))) {
                if (isset($cart_array['visitor_mail'])) {
                    unset($cart_array['visitor_mail']);
                }
                if (isset($cart_array['first_name'])) {
                    unset($cart_array['first_name']);
                }
                if (isset($cart_array['last_name'])) {
                    unset($cart_array['last_name']);
                }
                if (isset($cart_array['visitor_phone'])) {
                    unset($cart_array['visitor_phone']);
                }

                foreach ($cart_array as $myproducts) {
                    $product_name = get_the_title($myproducts['product_id']);
                    if (isset($myproducts['variation']) && (!empty($myproducts['variation']))) {
                        $product_name = $product_name . '<br />' . fp_rac_get_formatted_variation($myproducts['variation']);
                    }
                    $productid = $myproducts['product_id'];
                    $imageurl = "";
                    if ((get_post_thumbnail_id($myproducts['variation_id']) != "") || (get_post_thumbnail_id($myproducts['variation_id']) != 0)) {
                        $image_urls = wp_get_attachment_image_src(get_post_thumbnail_id($myproducts['variation_id']));
                        $imageurl = $image_urls[0];
                    }
                    if ($imageurl == "") {
                        if ((get_post_thumbnail_id($productid) != "") || (get_post_thumbnail_id($productid) != 0)) {
                            $image_urls = wp_get_attachment_image_src(get_post_thumbnail_id($productid));
                            $imageurl = $image_urls[0];
                        } else {
                            $imageurl = esc_url(wc_placeholder_img_src());
                        }
                    }
                    $image = '<img src="' . $imageurl . '" alt="' . get_the_title($productid) . '" height="90" width="90" />';
                    $quantity = $myproducts['quantity'];
                    if (get_option('rac_inc_tax_with_product_price_product_info_shortcode') == 'yes') {
                        $price = $myproducts['line_subtotal'] + $myproducts['line_subtotal_tax'];
                        $tax = 0;
                        $subtotal += $myproducts['line_subtotal'] + $myproducts['line_subtotal_tax'];
                    } else {
                        $price = $myproducts['line_subtotal'];
                        $tax += $myproducts['line_subtotal_tax'];
                        $subtotal += $myproducts['line_subtotal'];
                    }
                    echo fp_split_rac_items_in_cart($product_name, $image, $quantity, FP_List_Table_RAC::format_price($price));
                }
            }
        } elseif (is_object($cart_array)) {
            $order = new WC_Order($cart_array->id);

            foreach ($order->get_items() as $products) {
                $product_name = get_the_title($products['product_id']);
                $productid = $products['product_id'];
                $imageurl = "";
                if ((get_post_thumbnail_id($products['variation_id']) != "") || (get_post_thumbnail_id($products['variation_id']) != 0)) {
                    $image_urls = wp_get_attachment_image_src(get_post_thumbnail_id($products['variation_id']));
                    $imageurl = $image_urls[0];
                }
                if ($imageurl == "") {
                    if ((get_post_thumbnail_id($productid) != "") || (get_post_thumbnail_id($productid) != 0)) {
                        $image_urls = wp_get_attachment_image_src(get_post_thumbnail_id($productid));
                        $imageurl = $image_urls[0];
                    } else {
                        $imageurl = esc_url(wc_placeholder_img_src());
                    }
                }
                $image = '<img src="' . $imageurl . '" alt="' . get_the_title($productid) . '" height="90" width="90" />';
                $quantity = $products['qty'];
                if (get_option('rac_inc_tax_with_product_price_product_info_shortcode') == 'yes') {
                    $price = $products['line_subtotal'] + $products['line_subtotal_tax'];
                    $tax = 0;
                    $subtotal += $products['line_subtotal'] + $products['line_subtotal_tax'];
                } else {
                    $price = $products['line_subtotal'];
                    $tax += $products['line_subtotal_tax'];
                    $subtotal += $products['line_subtotal'];
                }
                echo fp_split_rac_items_in_cart($product_name, $image, $quantity, FP_List_Table_RAC::format_price($price));
            }
        }
        ?>
    </tbody>
    <?php if (get_option('rac_hide_tax_total_product_info_shortcode') != 'yes') { ?>
        <tfoot>
            <tr>
                <?php $i = 1; ?>
                <th class="<?php echo FPRacCron::enable_border() ?>" scope="row" colspan="3" style="text-align:left; <?php if ($i == 1) echo 'border-top-width: 4px;'; ?>"><?php echo fp_get_wpml_text('rac_template_subtotal', $each_cart->wpml_lang, get_option('rac_product_info_subtotal')); ?></th>
                <td class="<?php echo FPRacCron::enable_border() ?>" style="text-align:left; <?php if ($i == 1) echo 'border-top-width: 4px;'; ?>"><?php echo FP_List_Table_RAC::format_price($subtotal); ?></td>
            </tr>
            <?php if ($tax > 0) { ?>
                <tr>
                    <?php
                    $i = 1;
//            foreach ( WC()->cart->get_tax_totals() as $code => $tax1 ){
//                $label = $tax1->label;
//            }
                    ?>
                    <th class="<?php echo FPRacCron::enable_border() ?>" scope="row" colspan="3" style="text-align:left; <?php if ($i == 1) echo 'border-top-width: 4px;'; ?>"><?php echo fp_get_wpml_text('rac_template_tax', $each_cart->wpml_lang, get_option('rac_product_info_tax')); ?></th>
                    <td class="<?php echo FPRacCron::enable_border() ?>" style="text-align:left; <?php if ($i == 1) echo 'border-top-width: 4px;'; ?>"><?php echo FP_List_Table_RAC::format_price($tax); ?></td>
                </tr>
            <?php } ?>
            <tr>
                <?php $i = 1; ?>
                <th class="<?php echo FPRacCron::enable_border() ?>" scope="row" colspan="3" style="text-align:left; <?php if ($i == 1) echo 'border-top-width: 4px;'; ?>"><?php echo fp_get_wpml_text('rac_template_total', $each_cart->wpml_lang, get_option('rac_product_info_total')); ?></th>
                <td class="<?php echo FPRacCron::enable_border() ?>" style="text-align:left; <?php if ($i == 1) echo 'border-top-width: 4px;'; ?>"><?php echo FP_List_Table_RAC::format_price($subtotal + $tax); ?></td>
            </tr>

        </tfoot>
    <?php } ?>
    </table>
    <?php
    return ob_get_clean();
}

function fp_split_rac_items_in_cart($product_name, $image, $quantity, $price) {
    ob_start();
    ?>
    <tr>
        <?php if (get_option('rac_hide_product_name_product_info_shortcode') != 'yes') { ?>
            <td class="<?php echo FPRacCron::enable_border() ?>" style="text-align:left; vertical-align:middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">
                <?php echo $product_name; ?>
            </td>
        <?php } if (get_option('rac_hide_product_image_product_info_shortcode') != 'yes') { ?>
            <td class="<?php echo FPRacCron::enable_border() ?>" style="text-align:left; vertical-align:middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
                <?php echo $image; ?>
            </td>
        <?php } if (get_option('rac_hide_product_quantity_product_info_shortcode') != 'yes') { ?>
            <td class="<?php echo FPRacCron::enable_border() ?>" style="text-align:left; vertical-align:middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
                <?php echo $quantity; ?>
            </td>
        <?php } if (get_option('rac_hide_product_price_product_info_shortcode') != 'yes') { ?>
            <td class="<?php echo FPRacCron::enable_border() ?>" style="text-align:left; vertical-align:middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
                <?php echo $price; ?>
            </td>
        <?php } ?>
    </tr>
    <?php
    return ob_get_clean();
}

function rac_show_cart_products_brief($each_cart) {
    ob_start();
    ?>
    <table class="td" style="width:50%" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1" >
        <thead>
            <?php if (get_option('rac_hide_product_name_product_info_shortcode') != 'yes') { ?>
            <th class="td" scope="col" style="text-align:left;">
                <?php echo fp_get_wpml_text('rac_template_product_name', $each_cart->wpml_lang, get_option('rac_product_info_product_name')); ?>
            </th>
        <?php } if (get_option('rac_hide_product_image_product_info_shortcode') != 'yes') { ?>
            <th class="td" scope="col" style="text-align:left;">
                <?php echo fp_get_wpml_text('rac_template_product_image', $each_cart->wpml_lang, get_option('rac_product_info_product_image')); ?>
            </th>
        <?php } if (get_option('rac_hide_product_quantity_product_info_shortcode') != 'yes') { ?>
            <th class="td" scope="col" style="text-align:left;">
                <!-- For Quantity -->
                <?php echo fp_get_wpml_text('rac_template_product_quantity', $each_cart->wpml_lang, get_option('rac_product_info_quantity')); ?>
            </th>
        <?php } if (get_option('rac_hide_product_price_product_info_shortcode') != 'yes') { ?>
            <th class="td" scope="col" style="text-align:left;">
                <?php echo fp_get_wpml_text('rac_template_product_price', $each_cart->wpml_lang, get_option('rac_product_info_product_price')); ?>
            </th>
        <?php } ?>
    </thead>
    <tbody>
        <?php
        $subtotal = '';
        $tax = '';
        $cart_array = maybe_unserialize($each_cart->cart_details);
        if (is_array($cart_array) && (!empty($cart_array))) {
            if (isset($cart_array[0]['cart'])) {
                foreach ($cart_array[0]['cart'] as $eachproduct) {
                    $product_name = get_the_title($eachproduct['product_id']);
                    if (isset($eachproduct['variation']) && (!empty($eachproduct['variation']))) {
                        $product_name = $product_name . '<br />' . fp_rac_get_formatted_variation($eachproduct['variation']);
                    }
                    $productid = $eachproduct['product_id'];
                    $imageurl = "";
                    if ((get_post_thumbnail_id($eachproduct['variation_id']) != "") || (get_post_thumbnail_id($eachproduct['variation_id']) != 0)) {
                        $image_urls = wp_get_attachment_image_src(get_post_thumbnail_id($eachproduct['variation_id']));
                        $imageurl = $image_urls[0];
                    }
                    if ($imageurl == "") {
                        if ((get_post_thumbnail_id($productid) != "") || (get_post_thumbnail_id($productid) != 0)) {
                            $image_urls = wp_get_attachment_image_src(get_post_thumbnail_id($productid));
                            $imageurl = $image_urls[0];
                        } else {
                            $imageurl = esc_url(wc_placeholder_img_src());
                        }
                    }
                    $image = '<img src="' . $imageurl . '" alt="' . get_the_title($productid) . '" height="90" width="90" />';
                    $quantity = $eachproduct['quantity'];
                    if (get_option('rac_inc_tax_with_product_price_product_info_shortcode') == 'yes') {
                        $price = $eachproduct['line_subtotal'] + $eachproduct['line_subtotal_tax'];
                        $tax = 0;
                        $subtotal += $eachproduct['line_subtotal'] + $eachproduct['line_subtotal_tax'];
                    } else {
                        $price = $eachproduct['line_subtotal'];
                        $tax += $eachproduct['line_subtotal_tax'];
                        $subtotal += $eachproduct['line_subtotal'];
                    }

                    echo fp_split_rac_items_in_cart($product_name, $image, $quantity, FP_List_Table_RAC::format_price($price));
                }
            } elseif (is_array($cart_array) && (!empty($cart_array))) {
                if (isset($cart_array['visitor_mail'])) {
                    unset($cart_array['visitor_mail']);
                }
                if (isset($cart_array['first_name'])) {
                    unset($cart_array['first_name']);
                }
                if (isset($cart_array['last_name'])) {
                    unset($cart_array['last_name']);
                }
                if (isset($cart_array['visitor_phone'])) {
                    unset($cart_array['visitor_phone']);
                }

                foreach ($cart_array as $myproducts) {
                    $product_name = get_the_title($myproducts['product_id']);
                    if (isset($myproducts['variation']) && (!empty($myproducts['variation']))) {
                        $product_name = $product_name . '<br />' . fp_rac_get_formatted_variation($myproducts['variation']);
                    }
                    $productid = $myproducts['product_id'];
                    $imageurl = "";
                    if ((get_post_thumbnail_id($myproducts['variation_id']) != "") || (get_post_thumbnail_id($myproducts['variation_id']) != 0)) {
                        $image_urls = wp_get_attachment_image_src(get_post_thumbnail_id($myproducts['variation_id']));
                        $imageurl = $image_urls[0];
                    }
                    if ($imageurl == "") {
                        if ((get_post_thumbnail_id($productid) != "") || (get_post_thumbnail_id($productid) != 0)) {
                            $image_urls = wp_get_attachment_image_src(get_post_thumbnail_id($productid));
                            $imageurl = $image_urls[0];
                        } else {
                            $imageurl = esc_url(wc_placeholder_img_src());
                        }
                    }
                    $image = '<img src="' . $imageurl . '" alt="' . get_the_title($productid) . '" height="90" width="90" />';
                    $quantity = $myproducts['quantity'];
                    if (get_option('rac_inc_tax_with_product_price_product_info_shortcode') == 'yes') {
                        $price = $myproducts['line_subtotal'] + $myproducts['line_subtotal_tax'];
                        $tax = 0;
                        $subtotal += $myproducts['line_subtotal'] + $myproducts['line_subtotal_tax'];
                    } else {
                        $price = $myproducts['line_subtotal'];
                        $tax += $myproducts['line_subtotal_tax'];
                        $subtotal += $myproducts['line_subtotal'];
                    }
                    echo fp_split_rac_items_in_cart($product_name, $image, $quantity, FP_List_Table_RAC::format_price($price));
                }
            }
        } elseif (is_object($cart_array)) {
            $order = new WC_Order($cart_array->id);

            foreach ($order->get_items() as $products) {
                $product_name = get_the_title($products['product_id']);
                $productid = $products['product_id'];
                $imageurl = "";
                if ((get_post_thumbnail_id($products['variation_id']) != "") || (get_post_thumbnail_id($products['variation_id']) != 0)) {
                    $image_urls = wp_get_attachment_image_src(get_post_thumbnail_id($products['variation_id']));
                    $imageurl = $image_urls[0];
                }
                if ($imageurl == "") {
                    if ((get_post_thumbnail_id($productid) != "") || (get_post_thumbnail_id($productid) != 0)) {
                        $image_urls = wp_get_attachment_image_src(get_post_thumbnail_id($productid));
                        $imageurl = $image_urls[0];
                    } else {
                        $imageurl = esc_url(wc_placeholder_img_src());
                    }
                }
                $image = '<img src="' . $imageurl . '" alt="' . get_the_title($productid) . '" height="90" width="90" />';
                $quantity = $products['qty'];
                if (get_option('rac_inc_tax_with_product_price_product_info_shortcode') == 'yes') {
                    $price = $products['line_subtotal'] + $products['line_subtotal_tax'];
                    $tax = 0;
                    $subtotal += $products['line_subtotal'] + $products['line_subtotal_tax'];
                } else {
                    $price = $products['line_subtotal'];
                    $tax += $products['line_subtotal_tax'];
                    $subtotal += $products['line_subtotal'];
                }
                echo fp_split_rac_items_in_cart($product_name, $image, $quantity, FP_List_Table_RAC::format_price($price));
            }
        }
        ?>
    </tbody>
    <?php if (get_option('rac_hide_tax_total_product_info_shortcode') != 'yes') { ?>
        <tfoot>
            <tr>
                <?php $i = 1; ?>
                <th class="td" scope="row" colspan="3" style="text-align:left; <?php if ($i == 1) echo 'border-top-width: 1px;'; ?>"><?php echo fp_get_wpml_text('rac_template_subtotal', $each_cart->wpml_lang, get_option('rac_product_info_subtotal')); ?></th>
                <td class="td" style="text-align:left; <?php if ($i == 1) echo 'border-top-width: 1px;'; ?>"><?php echo FP_List_Table_RAC::format_price($subtotal); ?></td>
            </tr>
            <?php if ($tax > 0) { ?>
                <tr>
                    <?php
                    $i = 1;
//            foreach ( WC()->cart->get_tax_totals() as $code => $tax1 ){
//                $label = $tax1->label;
//            }
                    ?>
                    <th class="td" scope="row" colspan="3" style="text-align:left; <?php if ($i == 1) echo 'border-top-width: 1px;'; ?>"><?php echo fp_get_wpml_text('rac_template_tax', $each_cart->wpml_lang, get_option('rac_product_info_tax')); ?></th>
                    <td class="td" style="text-align:left; <?php if ($i == 1) echo 'border-top-width: 1px;'; ?>"><?php echo FP_List_Table_RAC::format_price($tax); ?></td>
                </tr>
            <?php } ?>
            <tr>
                <?php $i = 1; ?>
                <th class="td" scope="row" colspan="3" style="text-align:left; <?php if ($i == 1) echo 'border-top-width: 1px;'; ?>"><?php echo fp_get_wpml_text('rac_template_total', $each_cart->wpml_lang, get_option('rac_product_info_total')); ?></th>
                <td class="td" style="text-align:left; <?php if ($i == 1) echo 'border-top-width: 1px;'; ?>"><?php echo FP_List_Table_RAC::format_price($subtotal + $tax); ?></td>
            </tr>

        </tfoot>
    <?php } ?>
    </table>
    <?php
    return ob_get_clean();
}

function fp_rac_get_formatted_variation($variations) {

    foreach ($variations as $key => $variation) {
        $key = str_replace('attribute_', ' ', $key);
        $variations[] = $key . ': ' . $variation . '<br />';
    }
    $variations = implode($variations);
    return $variations;
}

function decode_labels_for_non_english_sites($label, $name, $product = null) {
    return rawurldecode($label);
}

add_filter('woocommerce_attribute_label', 'decode_labels_for_non_english_sites', 10, 2);
