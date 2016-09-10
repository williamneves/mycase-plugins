<?php

if ( !defined( 'ABSPATH' ) || !defined( 'YITH_YWRAC_VERSION' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Implements features of YITH WooCommerce Recover Abandoned Cart Counters
 *
 * @class   YITH_WC_Recover_Abandoned_Cart_Helper
 * @package YITH WooCommerce Recover Abandoned Cart
 * @since   1.0.0
 * @author  Yithemes
 */
if ( !class_exists( 'YITH_WC_Recover_Abandoned_Cart_Helper' ) ) {

    class YITH_WC_Recover_Abandoned_Cart_Helper {

        /**
         * Single instance of the class
         *
         * @var \YITH_WC_Recover_Abandoned_Cart_Helper
         */
        protected static $instance;



        /**
         * Returns single instance of the class
         *
         * @return \YITH_WC_Recover_Abandoned_Cart_Helper
         * @since 1.0.0
         */
        public static function get_instance() {
            if ( is_null( self::$instance ) ) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        /**
         * Constructor
         *
         * Initialize plugin and registers actions and filters to be used
         *
         * @since  1.0.0
         * @author Emanuela Castorina
         */
        public function __construct() {

            add_action( 'init', array( $this, 'set_cron'), 20 );
            add_filter( 'cron_schedules',  array( $this, 'cron_schedule'), 50 );
            add_action( 'update_option_ywrac_cron_time', array( $this, 'destroy_schedule'));
            add_action( 'update_option_ywrac_cron_time_type', array( $this, 'destroy_schedule'));

        }

        /**
         * Destroy the schedule
         *
         * Called when ywrac_cron_time and ywrac_cron_time_type are update from settings panel
         *
         * @since  1.0.0
         * @author Emanuela Castorina
         */
        public function destroy_schedule() {
            wp_clear_scheduled_hook( 'ywrac_cron' );
            $this->set_cron();
        }

        /**
         * Cron Schedule
         *
         * Add new schedules to wordpress
         *
         *
         * @since  1.0.0
         * @author Emanuela Castorina
         */
        public function cron_schedule( $schedules ){

                $interval = 0;
                $cron_type = get_option( 'ywrac_cron_time_type' );
                $cron_time = get_option( 'ywrac_cron_time' );

                if ( $cron_type == 'hours' ) {
                    $interval = 60 * 60 * $cron_time;
                }
                elseif ( $cron_type == 'days' ) {
                    $interval = 24 * 60 * 60 * $cron_time;
                }
                elseif ( $cron_type == 'minutes' ) {
                    $interval = 60 * $cron_time;
                }

                $schedules['ywrac_gap'] = array(
                    'interval' => $interval,
                    'display' => __( 'YITH WooCommerce Recover Abandoned Cart Cron', 'yith-woocommerce-recover-abandoned-cart' )
                );

                return $schedules;
        }

        /**
         * Set Cron
         *
         * Set ywrac_cron action each ywrac_gap schedule
         *
         * @since  1.0.0
         * @author Emanuela Castorina
         */
        public function set_cron() {
            if ( ! wp_next_scheduled( 'ywrac_cron' ) ) {
                wp_schedule_event( current_time('timestamp', 1), 'ywrac_gap', 'ywrac_cron' );
            }
        }

        /**
         * Update counter to statistic options:
         *
         * email_sent_counter
         * abandoned_carts_counter
         * email_clicks_counter
         * recovered_carts
         *
         * @since  1.0.0
         * @author Emanuela Castorina
         */
        function update_counter( $key ){
            $suffix = 'ywrac_';
            $current_counter = get_option( $suffix. $key );

            update_option( $suffix. $key, $current_counter + 1 );
        }

        /**
         * Update counter meta to statistic params
         *
         * @since  1.0.0
         * @author Emanuela Castorina
         */
        function update_counter_meta( $post_id,  $key ){
            $current_counter = get_post_meta( $post_id, $key, true );
            update_post_meta( $post_id, $key, $current_counter + 1 );
        }

        /**
         * Update total amount when a cart is recovered
         *
         * @since  1.0.0
         * @author Emanuela Castorina
         */
        function update_amount_total( $amount ){
            $current_amount = get_option( 'ywrac_total_amount' );
            update_option( 'ywrac_total_amount', $current_amount + $amount );
        }

        /**
         * Add to yith_ywrac_email_log a new entry
         *
         * @since  1.0.0
         * @author Emanuela Castorina
         */
        function email_log( $user_email, $email_id, $cart_id, $date ){
            global $wpdb;
            $table_name   = $wpdb->prefix . 'yith_ywrac_email_log';
            $insert_query = "INSERT INTO $table_name (email_id, email_template_id, ywrac_cart_id, date_send) VALUES ('" . $user_email . "', $email_id, $cart_id, '" . $date . "' )";
            $wpdb->query( $insert_query );
        }

        /**
         * Clear coupons after use
         *
         * @since  1.0.0
         * @author Emanuela Castorina
         */
        function clear_coupons(){
            $delete_after_use = get_option( 'ywrac_coupon_delete_after_use' );
            $delete_expired   = get_option( 'ywrac_coupon_delete_expired' );
           
            
            if( $delete_after_use != 'yes' && $delete_expired != 'yes'){
                return;
            }

            $args = array(
                'post_type'       => 'shop_coupon',
                'posts_per_pages' => - 1,
                'meta_key'        => 'ywrac_coupon',
                'meta_value'      => 'yes'
            );

            $coupons = get_posts( $args );

            if( ! empty( $coupons ) ){
                foreach( $coupons as $coupon ){
                    if( $delete_after_use == 'yes' ){
                        $usage_count  = get_post_meta( $coupon->ID, 'usage_count', true);
                        if( $usage_count == 1){
                            wp_delete_post( $coupon->ID, true );
                        }
                    }
                }
            }
        }

    }
}

/**
 * Unique access to instance of YITH_WC_Recover_Abandoned_Cart_Helper class
 *
 * @return \YITH_WC_Recover_Abandoned_Cart_Helper
 */
function YITH_WC_Recover_Abandoned_Cart_Helper() {
    return YITH_WC_Recover_Abandoned_Cart_Helper::get_instance();
}




