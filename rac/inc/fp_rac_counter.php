<?php

// RAC Table row can be deleted,
//to provide exact report we count it and have it options table
class FPRacCounter {

    public static function rac_do_recovered_count() {
        if (get_option('rac_recovered_count')) { // count started already
            $recovered_count = get_option('rac_recovered_count');
            $recovered_count++;
            update_option('rac_recovered_count', $recovered_count);
        } else {// first time counting
            update_option('rac_recovered_count', 1);
        }
    }

    public static function record_order_id_and_cart_id($order_id) {
        $save_recovered_order_id = (array) get_option('fp_rac_recovered_order_ids');
        $order_object = new WC_Order($order_id);
        $total = $order_object->order_total;
        $order_date = $order_object->order_date;

        $current_order_id = (array) array(
                    $order_id => array(
                        'order_id' => $order_id,
                        'order_total' => $total,
                        'date' => $order_date
                    )
        );
        $merge_data = array_merge($save_recovered_order_id, $current_order_id);

        $update_this_data = update_option('fp_rac_recovered_order_ids', $merge_data);
    }

    public static function add_list_table() {
        // echo "Donation Table";
        $newwp_list_table = new FP_List_Table_RAC();
        $newwp_list_table->prepare_items();
        $newwp_list_table->display();
    }

    public static function rac_do_abandoned_count() {
        if (get_option('rac_abandoned_count')) { // count started already
            $abandoned_count = get_option('rac_abandoned_count');
            $abandoned_count++;
            update_option('rac_abandoned_count', $abandoned_count);
        } else {// first time counting
            update_option('rac_abandoned_count', 1);
        }
    }

    public static function rac_do_mail_count() {
        if (get_option('rac_mail_count')) { // count started already
            $mail_count = get_option('rac_mail_count');
            $mail_count++;
            update_option('rac_mail_count', $mail_count);
        } else {// first time counting
            update_option('rac_mail_count', 1);
        }
    }
    
    public static function email_count_by_template($templateid) {
        $template_id = str_replace('- Manual', '', $templateid);
        if (get_option('email_count_of_'.$template_id)) { // count started already
            $mail_count = get_option('email_count_of_'.$template_id);
            $mail_count++;
            update_option('email_count_of_'.$template_id, $mail_count);
        } else {// first time counting
            update_option('email_count_of_'.$template_id, 1);
        }
    }
    
    public static function rac_recovered_count_by_mail_template($templateid) {
        if (get_option('rac_recovered_count_of_'.$templateid)) { // count started already
            $recovered_count = get_option('rac_recovered_count_of_'.$templateid);
            $recovered_count++;
            update_option('rac_recovered_count_of_'.$templateid, $recovered_count);
        } else {// first time counting
            update_option('rac_recovered_count_of_'.$templateid, 1);
        }
    }

    public static function rac_do_linkc_count($abandon_cart_id , $email_template_id) {
        if (get_option('rac_link_count')) { // count started already
            $link_count = get_option('rac_link_count');
            if(get_option('already_clicked_this_link_'.$abandon_cart_id.'_'.$email_template_id) != 'yes'){
                update_option('rac_link_count', $link_count + 1);
                update_option('already_clicked_this_link_'.$abandon_cart_id.'_'.$email_template_id,'yes');
            }
        } else {// first time counting
            update_option('rac_link_count', 1);
            update_option('already_clicked_this_link_'.$abandon_cart_id.'_'.$email_template_id,'yes');
        }
    }

}

?>