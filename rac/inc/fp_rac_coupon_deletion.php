<?php

class FPRacCouponDelete {

    public static function delete_expired_rac_coupon() {

        if ('yes' === get_option('rac_delete_coupon_expired')) {
            $args = array('post_type' => 'shop_coupon', 'posts_per_page' => -1);
            $cus_query = get_posts($args);

           foreach($cus_query as $value)
               
           {
               $coupon = new WC_Coupon($value->post_name);
                       
                    if (get_post_meta($value->ID, 'coupon_by_rac', true) == 'yes') {
                
                        $expiry_date = strtotime(get_post_meta($value->ID,'expiry_date',true));
                           if (current_time('timestamp') > $expiry_date) {
                            wp_delete_post($value->ID,true);
                        }
                   
                wp_reset_postdata();
            }
        }
    }
    }
    

    public static function delete_rac_coupon($order_id) {
        if ('yes' === get_option('rac_delete_coupon_after_use')) {
            $order = new WC_Order($order_id);
            $used_coupons = $order->get_used_coupons();
            foreach ($used_coupons as $coupon) {
                $coupon_ob = new WC_Coupon($coupon);
                if (get_post_meta($coupon_ob->id, 'coupon_by_rac', true) == 'yes') {
                    wp_delete_post($coupon_ob->id,true);
                }
            }
        }
    }

}

$rac_coupon_delete = new FPRacCouponDelete();

add_action('woocommerce_checkout_order_processed', array('FPRacCouponDelete', 'delete_rac_coupon'), 10, 1);
add_action('rac_cron_job', array($rac_coupon_delete, 'delete_expired_rac_coupon'),999);
