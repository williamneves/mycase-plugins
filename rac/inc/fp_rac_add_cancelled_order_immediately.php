<?php

class FPRacCancelledOrder {

    public function __construct() {
        if(get_option('rac_insert_abandon_cart_when_os_cancelled') == 'yes'){
            add_action('woocommerce_order_status_cancelled', array($this, 'add_cancelled_order_immediately_to_cart_list_as_abandoned'));
            add_action('woocommerce_cancelled_order',array($this,'prevent_add_new_cart_while_order_cancelled_in_cart_page'),1,1);
        }
    }

    public static function add_cancelled_order_immediately_to_cart_list_as_abandoned( $order_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'rac_abandoncart';
        $order = new WC_Order($order_id);
        $cart_details = maybe_serialize($order);
        $user_id = 'old_order';
        if ($order->user_id != '') {
            $user_email = $order->billing_email;
        } else {
            $user_email = $order->billing_email;
        }
        $order_modified_time = strtotime($order->modified_date);
        $wpdb->insert($table_name, array('cart_details' => $cart_details, 'user_id' => $user_id, 'email_id' => $user_email, 'cart_abandon_time' => $order_modified_time, 'cart_status' => 'ABANDON'), array('%s'));
    }
    
    public static function prevent_add_new_cart_while_order_cancelled_in_cart_page($order_id){
        if(get_option('rac_prevent_entry_in_cartlist_while_order_cancelled_in_cart_page') != 'no'){
            remove_action('woocommerce_cart_updated',array('RecoverAbandonCart','fp_rac_insert_entry'));
        }
    }

}

new FPRacCancelledOrder();
