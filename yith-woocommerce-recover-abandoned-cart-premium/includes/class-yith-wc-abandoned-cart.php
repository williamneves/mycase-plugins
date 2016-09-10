<?php

if ( !defined( 'ABSPATH' ) || !defined( 'YITH_YWRAC_VERSION' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Implements features of YITH WooCommerce Recover Abandoned Cart
 *
 * @class   YITH_WC_Recover_Abandoned_Cart
 * @package YITH WooCommerce Recover Abandoned Cart
 * @since   1.0.0
 * @author  Yithemes
 */
if ( !class_exists( 'YITH_WC_Recover_Abandoned_Cart' ) ) {

    class YITH_WC_Recover_Abandoned_Cart {

        /**
         * Single instance of the class
         *
         * @var \YITH_WC_Recover_Abandoned_Cart
         */
        protected static $instance;

        /**
         * Post type name
         *
         * @var \YITH_WC_Recover_Abandoned_Cart
         */
        public $post_type_name = 'ywrac_cart';

        /**
         * Cut Off time
         *
         * @var \YITH_WC_Recover_Abandoned_Cart
         */
        public $cutoff = 60;
        public $delete_abandoned_time = 0;

        /**
         * Returns single instance of the class
         *
         * @return \YITH_WC_Dynamic_Pricing
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

            $this->cutoff = get_option('ywrac_cut_off_time')*60;
            $this->delete_abandoned_time = get_option('ywrac_delete_cart')*60*60;

            $this->checkout();

            add_action( 'plugins_loaded', array( $this, 'plugin_fw_loader' ), 15 );

            add_action('init', array($this, 'add_post_type'), 10);
            add_action('wp_loaded', array($this, 'recovery_cart'), 10);

            //custom styles and javascripts
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles_scripts' ), 11);

            /* email actions and filter */
            add_filter( 'woocommerce_email_classes', array( $this, 'add_woocommerce_emails' ) );
            add_action( 'woocommerce_init', array( $this, 'load_wc_mailer' ) );


            add_action( 'woocommerce_checkout_order_processed', array( $this, 'order_processed'), 10, 2 );

            // register plugin to licence/update system
            add_action( 'wp_loaded', array( $this, 'register_plugin_for_activation' ), 99 );
            add_action( 'admin_init', array( $this, 'register_plugin_for_updates' ) );

            add_action( 'ywrac_cron', array( $this, 'update_carts' ) );

            /* general actions */
            add_filter( 'woocommerce_locate_core_template', array( $this, 'filter_woocommerce_template' ), 10, 3 );
            add_filter( 'woocommerce_locate_template', array( $this, 'filter_woocommerce_template' ), 10, 3 );

            /* unsubscribe from email */
            add_action( 'template_redirect', array( $this, 'unsubscribe_from_mail' ) );
            if( class_exists('WOOCS') ){
                add_action( 'template_redirect', array( $this, 'checkout' ));
                add_action( 'woocommerce_checkout_update_order_review', array( $this, 'checkout' ));
                add_action( 'woocommerce_checkout_order_processed', array( $this, 'checkout_order_processed'), 10, 1 );
            }


            if( ! $this->check_user() ){
                return;
            }

            add_action('woocommerce_cart_updated', array($this, 'cart_updated'));
            add_action( 'wp_ajax_ywrac_grab_guest', array( $this, 'ajax_grab_guest' ) );
            add_action( 'wp_ajax_nopriv_ywrac_grab_guest', array( $this, 'ajax_grab_guest' ) );

            add_action( 'wp_ajax_ywrac_grab_guest_phone', array( $this, 'ajax_grab_guest_phone' ) );
            add_action( 'wp_ajax_nopriv_ywrac_grab_guest_phone', array( $this, 'ajax_grab_guest_phone' ) );



        }

        /**
         * Load YIT Plugin Framework
         *
         * @since  1.0.0
         * @return void
         * @author Emanuela Castorina
         */
        public function plugin_fw_loader() {
            if ( ! defined( 'YIT_CORE_PLUGIN' ) ) {
                global $plugin_fw_data;
                if( ! empty( $plugin_fw_data ) ){
                    $plugin_fw_file = array_shift( $plugin_fw_data );
                    require_once( $plugin_fw_file );
                }
            }
        }

        /**
         * Enqueue styles and scripts
         *
         * @access public
         * @return void
         * @since 1.0.0
         */
        public function enqueue_styles_scripts() {
            if( is_checkout() ){
                wp_enqueue_script( 'yith_ywrac_frontend', YITH_YWRAC_ASSETS_URL . '/js/ywrac-frontend' . YITH_YWRAC_SUFFIX . '.js', array( 'jquery' ), YITH_YWRAC_VERSION, true );
            }

            wp_localize_script( 'yith_ywrac_frontend', 'yith_ywrac_frontend', array(
                'ajaxurl'          => admin_url( 'admin-ajax.php' ),
                'grab_guest_nonce'       => wp_create_nonce( 'grab-guest' ),
                'grab_guest_phone_nonce' => wp_create_nonce( 'grab-guest-phone' ),
                'is_guest'         => !is_user_logged_in(),
            ));

        }

        /**
         * Register the custom post type ywrac_cart
         *
         * @since  1.0.0
         * @author Emanuela Castorina
         */
        public function add_post_type() {
            
                $labels = array(
                    'name' => _x('Abandoned Cart', 'Post Type General Name', 'yith-woocommerce-recover-abandoned-cart'),
                    'singular_name' => _x('Abandoned Cart', 'Post Type Singular Name', 'yith-woocommerce-recover-abandoned-cart'),
                    'menu_name' => __('Abandoned Cart', 'yith-woocommerce-recover-abandoned-cart'),
                    'parent_item_colon' => __('Parent Item:', 'yith-woocommerce-recover-abandoned-cart'),
                    'all_items' => __('All Abandoned Carts', 'yith-woocommerce-recover-abandoned-cart'),
                    'view_item' => __('View Abandoned Carts', 'yith-woocommerce-recover-abandoned-cart'),
                    'add_new_item' => __('Add New Abandoned Cart', 'yith-woocommerce-recover-abandoned-cart'),
                    'add_new' => __('Add New Abandoned Cart', 'yith-woocommerce-recover-abandoned-cart'),
                    'edit_item' => __('Edit Abandoned Cart', 'yith-woocommerce-recover-abandoned-cart'),
                    'update_item' => __('Update Abandoned Cart', 'yith-woocommerce-recover-abandoned-cart'),
                    'search_items' => __('Search Abandoned Cart', 'yith-woocommerce-recover-abandoned-cart'),
                    'not_found' => __('Not found', 'yith-woocommerce-recover-abandoned-cart'),
                    'not_found_in_trash' => __('Not found in Trash', 'yith-woocommerce-recover-abandoned-cart'),
                );
                $args = array(
                    'label' => __('Carts', 'yith-woocommerce-recover-abandoned-cart'),
                    'description' => '',
                    'labels' => $labels,
                    'supports' => array('title'),
                    'hierarchical' => false,
                    'public' => false,
                    'show_ui' => true,
                    'show_in_menu' => false,
                    'exclude_from_search' => true,
                    'capability_type' => 'post',
                    'capabilities'       => array( 'create_posts' => false ),
                    'map_meta_cap'       => true
                    );

                register_post_type($this->post_type_name, $args);

            }

        /**
         * Register a guest cart when he add your email address in checkout page
         *
         * @since  1.0.0
         * @author Emanuela Castorina
         */
        public function ajax_grab_guest(){

            check_ajax_referer( 'grab-guest', 'security' );

            if( empty( $_POST['email'] ) || ! is_email( $_POST['email'] ) || ywrac_is_customer_unsubscribed( $_POST['email'] ) ){
                return;
            }

            $email = $_POST['email'];
            $post_id =  0;
            $add_new = true;
            $cart = $this->guest_email_exists( $email );
            if ( ! empty( $cart )  ){
                $post_id = $cart->ID;
                $this->update_abandoned_cart( $cart->ID, $cart->post_date, array(
                    'cart_status' => 'open',
                    'user_phone'  => intval( $_POST['phone'] )
                ) );
                setcookie( 'ywrac_guest_cart', $post_id, current_time('timestamp', 1) + $this->delete_abandoned_time * 60, '/' );
                $add_new = false;

            } elseif( isset( $_COOKIE['ywrac_guest_cart']) &&  $_COOKIE['ywrac_guest_cart'] ){
                $cart = get_post( $_COOKIE['ywrac_guest_cart'] );
                if( !empty( $cart ) ){
                    $this->update_abandoned_cart( $cart->ID, $cart->post_date, array(
                        'cart_status' => 'open',
                        'user_email'  => $_POST['email'],
                        'user_phone'  => intval( $_POST['phone'] ),
                        'user_currency'   => $this->get_user_currency()
                    ) );
                    setcookie( 'ywrac_guest_cart', $cart->ID,  current_time('timestamp', 1) + $this->delete_abandoned_time * 60, '/' );
                    $add_new = false;
                }
            }
            if( $add_new ){
                $meta_cart = array(
                    'user_id'         => '0',
                    'user_email'      => $_POST['email'],
                    'user_first_name' => $_POST['first_name'],
                    'user_last_name'  => $_POST['last_name'],
                    'user_phone'      => $_POST['phone'],
                    'email_sent'      => 'no',
                    'cart_status'     => 'open',
                    'user_currency'   => $this->get_user_currency()
                );


                if ( $meta_cart['user_first_name'] != '' || $meta_cart['user_last_name'] != '' ) {
                    $title = $meta_cart['user_first_name'] . ' ' . $meta_cart['user_last_name'];
                }
                else {
                    $title = $meta_cart['user_email'];
                }

                $post_id = $this->add_abandoned_cart( $title, $meta_cart);

                if( $post_id ){
                    //add a cookie to the user
                    setcookie( 'ywrac_guest_cart', $post_id,  current_time('timestamp', 1) + $this->delete_abandoned_time * 60, '/' );
                }

            }

            wp_send_json( array(
                'cart_id' => $post_id
            ) );

        }

        /**
         * Add guest phone number for abandoned cart
         *
         * @since  1.0.0
         * @author Emanuela Castorina
         */
        public function ajax_grab_guest_phone() {

            check_ajax_referer( 'grab-guest-phone', 'security' );

            if( empty( $_POST['phone'] ) || ( empty( $_POST['email'] ) && ! is_email( $_POST['email'] ) ) ){
                die();
            }

            // get post
            $cart = $this->guest_email_exists( $_POST['email'] );

            if( ! $cart ) {
                die();
            }

            $result = false;
            foreach( $cart as $cart_obj ) {
                $result = update_post_meta( $cart_obj->ID, '_user_phone', intval( $_POST['phone'] ) );
            }

            wp_send_json( array(
                'result' => $result
            ) );
        }

        /**
         * Add a new abandoned cart
         *
         * @since  1.0.0
         * @author Emanuela Castorina
         */
        public function add_abandoned_cart( $title, $metas){
            $post = array(
                'post_content' => '',
                'post_status'  => 'publish',
                'post_title'   => $title,
                'post_type'    => $this->post_type_name
            );
            $cart_id = wp_insert_post( $post );
            if ( $cart_id && !empty( $metas ) ) {
                foreach ( $metas as $meta_key => $meta_value ) {
                    update_post_meta( $cart_id, '_' . $meta_key, $meta_value );
                }

                update_post_meta( $cart_id, '_language', $this->get_user_language() );
                update_post_meta( $cart_id, '_cart_content', $this->get_item_cart() );
                update_post_meta( $cart_id, '_cart_subtotal', $this->get_subtotal_cart() );
            }
            return $cart_id;
        }

        /**
         * Update abandoned cart
         *
         * @since  1.0.0
         * @author Emanuela Castorina
         */
        public function update_abandoned_cart( $cart_id, $post_date, $metas ){

            $post_updated = array(
                'ID'        => $cart_id,
                'post_date' => $post_date,
                'post_type' => $this->post_type_name
            );

            $updated = wp_update_post( $post_updated );

            if( $updated ){
                foreach ( $metas as $meta_key => $meta_value ) {
                    update_post_meta( $cart_id, '_' . $meta_key, $meta_value );
                }

                update_post_meta( $cart_id, '_cart_content', $this->get_item_cart() );
                update_post_meta( $cart_id, '_cart_subtotal', $this->get_subtotal_cart() );
            }

            return $updated;
        }

        /**
         * Update the entry on db
         *
         * when the user update the cart update the entry on db of the current cart
         *
         * @since  1.0.0
         * @author Emanuela Castorina
         */
        public function cart_updated(){

            if( isset( $_GET['rec_cart']) ){
                return;
            }

            if ( is_user_logged_in() ) {
                $user_id           = get_current_user_id();

                if( ywrac_is_customer_unsubscribed( $user_id ) ){
                    return;
                }

                $user_details      = get_userdata( $user_id );

                $has_previous_cart = $this->has_previous_cart( $user_id );
                $title             = $user_details->display_name;

                $first_name = get_user_meta( $user_id, 'billing_first_name', true );
                $last_name  = get_user_meta( $user_id, 'billing_last_name', true );
                $phone      = get_user_meta( $user_id, 'billing_phone_name', true );

                $metas = apply_filters('ywrac_cart_updated_meta', array(
                    'user_id'         => $user_id,
                    'user_email'      => $user_details->user_email,
                    'user_first_name' => $first_name ? $first_name : $user_details->first_name,
                    'user_last_name'  => $last_name ? $last_name : $user_details->last_name,
                    'user_phone'      => $phone ? $phone : $user_details->phone,
                    'user_currency'   => $this->get_user_currency(),
                    'email_sent'      => 'no',
                    'cart_status'     => 'open'
                ));
                
                $get_cart = WC()->cart->get_cart();
                if ( ! $has_previous_cart && ! empty( $get_cart ) ) {
                    $post_id = $this->add_abandoned_cart( $title, $metas );
                } else {
                    if ( ! empty( $get_cart ) && $this->get_subtotal_cart() > 0 ) {
                        $post_id   = $has_previous_cart->ID;
                        $post_date = $has_previous_cart->post_date;
                        $this->update_abandoned_cart( $post_id, $post_date, array( 'cart_status' => 'open' ) );
                    } else {
                        $this->remove_abandoned_cart_for_current_user();
                    }
                }
            }
            elseif ( isset( $_COOKIE['ywrac_guest_cart'] ) ) {
                $post_id   = $_COOKIE['ywrac_guest_cart'];
                $post      = get_post( $post_id );
                if( !empty($post) ){
                    $post_date = $post->post_date;
                    $this->update_abandoned_cart( $post_id, $post_date, array( 'cart_status' => 'open' ) );
                }

            }
        }

        /**
         * Check if a user has a previous cart in database
         *
         * @since  1.0.0
         * @author Emanuela Castorina
         */
        public function has_previous_cart( $user_id ){
            $args = array(
                'post_type'   => $this->post_type_name,
                'post_status' => 'publish',
                'meta_query' => array(
                    'relation' => 'AND',
                    array(
                        'key'     => '_user_id',
                        'value'   => $user_id,
                        'compare' => '='
                    ),
                    array(
                        'key' => '_cart_status',
                        'value'   => 'recovered',
                        'compare' => 'NOT LIKE',
                    ),
                ),
            );

            $r = get_posts( $args );
            if( empty($r) ){
                return false;
            }else{
                return $r[0];
            }
        }

        /**
         * Return a json with cart content
         *
         * @since  1.0.0
         * @author Emanuela Castorina
         */
        public function get_item_cart() {

            if ( is_user_logged_in() ) {
                $user_id = get_current_user_id();
                $cart    = maybe_serialize( get_user_meta( $user_id, '_woocommerce_persistent_cart', true ) );
            }
            else {
                $cart = maybe_serialize( array( 'cart' => WC()->session->get( 'cart' ) ) );
            }

            return $cart;
        }

        /**
         * Return the subtotal of the cart
         *
         * @since  1.0.0
         * @author Emanuela Castorina
         */
        public function get_subtotal_cart() {

            $subtotal = ( WC()->cart->tax_display_cart == 'excl' ) ? WC()->cart->subtotal_ex_tax : WC()->cart->subtotal;
            return $subtotal;

        }

        /**
         * Called when a cart is updated
         *
         * @since  1.0.0
         * @author Emanuela Castorina
         */
        public function update_carts(){
            $start_to_date = (int) (  current_time('timestamp', 1) - $this->cutoff );
            $args = array(
                'post_type'   => $this->post_type_name,
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'meta_value'  => 'open',
                'meta_key'    => '_cart_status',
                'date_query' => array(
                    array(
                        'column' => 'post_modified_gmt',
                        'before'  => date("Y-m-d H:i:s", $start_to_date),
                    ),
                ),
            );
            $p = get_posts($args);

            if( ! empty ($p) ){
                foreach( $p as $post ){
                    $this->update_status( $post );
                }
            }
        }

        /**
         * Update the status of a cart
         *
         * @since  1.0.0
         * @author Emanuela Castorina
         */
        public function update_status( $cart ){


            $current_status = get_post_meta( $cart->ID, '_cart_status', true );
            $post_modified = strtotime( $cart->post_modified_gmt );
            $current_time =  current_time('timestamp', 1);
            $delta = $current_time - $post_modified;

            //change the status from open to abandoned if cuttoff time is over
            if(  $delta  > $this->cutoff ){
                if( $current_status == 'open' ){
                    update_post_meta( $cart->ID, '_cart_status', 'abandoned');
                    YITH_WC_Recover_Abandoned_Cart_Helper()->update_counter('abandoned_carts_counter');
                }
            }

            //delete the entry of cart is the deadline is over
            if( $this->delete_abandoned_time != 0 ){
                if(  $delta > $this->delete_abandoned_time ){
                    if( $current_status == 'abandoned' ){
                        wp_delete_post( $cart->ID, true);
                    }
                }
            }
        }

        public function remove_abandoned_cart_for_current_user( $user = null ){

            $args = array(
                'post_type'   => $this->post_type_name,
                'post_status' => 'publish'
            );

            if( is_email( $user ) ){

                $args['meta_query'] = array(
                    array(
                        'key'     => '_user_email',
                        'value'   => $user,
                    ),
                );
            }
            else {

                if( is_null( $user ) ) {
                    $user = get_current_user_id();
                }

                $user = get_user_by( 'id', $user );

                if( ! $user ) {
                    return;
                }

                $args['meta_query'] = array(
                    'relation'  => 'OR',
                    array(
                        'key'     => '_user_email',
                        'value'   => $user->data->user_email,
                        'compare' => 'LIKE'
                    ),
                    array(
                        'key'     => '_user_id',
                        'value'   => $user->data->ID,
                        'compare' => 'LIKE'
                    ),
                );
            }

            $p = get_posts($args);
            if ( !empty( $p ) ) {
                foreach ( $p as $post ) {
                    wp_delete_post( $post->ID, true );
                }
            }

        }
        /**
         * Send email for recovery a single cart in ajax
         *
         * @return void
         * @since 1.0
         */
        public function get_cart_content( $cart_id, $cart_content, $lang){

            ob_start();
            $subtotal = get_post_meta( $cart_id, '_cart_subtotal', true );
            wc_get_template( 'cart_content.php', array( 'cart_content' => $cart_content, 'subtotal' => $subtotal, 'lang' => $lang ) );
            return ob_get_clean();
        }

        /**
         * Return the link of the cart
         *
         * @return void
         * @since 1.0
         */
        public function get_cart_link( $cart_id, $email_id ) {
            $cart_page_id  = wc_get_page_id( 'cart' );
            $woo_cart_link = apply_filters( 'woocommerce_get_cart_url', $cart_page_id ? get_permalink( $cart_page_id ) : '' );
            $query_args    = http_build_query( array( 'cart_id' => $cart_id, 'emailtemp' => $email_id ));
            $encript       = base64_encode( $query_args );
            $link          = add_query_arg( array( 'rec_cart' => $encript ), $woo_cart_link );

            return $link;
        }


	    /**
         * @param $params
         * return array
         */
        public function get_info_cart_by_link( $params ) {
            $decode = base64_decode( $params );
            parse_str( $decode, $cart_info );
            return $cart_info;
        }

        /**
         * Return the language of the current user
         *
         * @return void
         * @since 1.0
         */
        function get_user_language(){
            if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
                return ICL_LANGUAGE_CODE;
            }
            else {
                return substr( get_bloginfo( 'language' ), 0, 2 );
            }
        }

        /**
         * Return the language of the current user
         *
         * @return void
         * @since 1.0
         */
        function get_user_currency(){
            $currency = get_woocommerce_currency();
            if ( class_exists('WOOCS') ) {
                global $WOOCS;
                $currency = $WOOCS->current_currency;
            }

            return $currency;
        }
        
        /**
         * Check if the user is enabled to save the cart
         *
         * @return boolean
         * @since 1.0
         */
        function check_user(){
            if( is_user_logged_in() ){
                $rules = get_option('ywrac_user_roles');
                if( empty($rules) || !is_array($rules) ){
                    return false;
                }
                if( in_array( 'all', $rules) ){
                    return true;
                }
                $current_user = wp_get_current_user();
                $intersect = array_intersect( $current_user->roles, $rules );
                if( !empty( $intersect ) ){
                    return true;
                }
            }else{
                if( get_option('ywrac_user_guest_enabled') == 'yes'){
                    return true;
                }
            }

            return false;
        }

        /**
         * Check if the email of the current user exists
         *
         * @return void
         * @since 1.0
         */
        function guest_email_exists( $email ){
            $args = array(
                'post_type'   => $this->post_type_name,
                'post_status' => 'publish',
                'meta_query' => array(
                    'relation' => 'AND',
                    array(
                        'key'     => '_user_email',
                        'value'   => $email
                    ),
                    array(
                        'key'     => '_user_id',
                        'value'   => 0,
                    ),
                )
            );


            $p = get_posts($args);

            if( empty( $p ) ){
                return false;
            }

            return $p[0];
        }

        /**
         * Filters woocommerce available emails
         *
         * @param $emails array
         *
         * @return array
         * @since 1.0
         */
        public function add_woocommerce_emails( $emails ) {
            $emails['YITH_YWRAC_Send_Email'] = include( YITH_YWRAC_INC . 'emails/class.yith-wc-abandoned-cart-email.php' );
            $emails['YITH_YWRAC_Send_Email_Recovered_Cart'] =include( YITH_YWRAC_INC . 'emails/class.yith-wc-abandoned-cart-email-recovered-cart.php' );
            
            return $emails;
        }

        /**
         * Loads WC Mailer when needed
         *
         * @return void
         * @since 1.0
         */
        public function load_wc_mailer() {
            add_action( 'send_rac_mail', array( 'WC_Emails', 'send_transactional_email' ), 10 );
            add_action( 'send_recovered_cart_mail', array( 'WC_Emails', 'send_transactional_email' ), 10 );
        }

        /**
         * Locate default templates of woocommerce in plugin, if exists
         *
         * @param $core_file     string
         * @param $template      string
         * @param $template_base string
         *
         * @return string
         * @since  1.0.0
         */
        public function filter_woocommerce_template( $core_file, $template, $template_base ) {
            $located = yith_ywrac_locate_template( $template );

            if ( $located ) {
                return $located;
            }
            else {
                return $core_file;
            }
        }

        /**
         * Recovery cart from cart email link
         *
         * @return string
         * @since  1.0.0
         */
        function recovery_cart(){
            if ( isset( $_GET['rec_cart'] ) ) {
                $cart_info = $this->get_info_cart_by_link( $_GET['rec_cart'] );
                if ( ! $cart_info || ! isset( $cart_info['cart_id'] ) || ! isset( $cart_info['emailtemp'] ) ) {
                    return;
                }
                $cart_id  = $cart_info['cart_id'];
                $email_id = $cart_info['emailtemp'];
            } elseif ( isset( $_GET['cart_id'] ) && isset( $_GET['emailtemp'] ) ) {
                $cart_id  = $_GET['cart_id'];
                $email_id = $_GET['emailtemp'];
            } else {
                return;
            }
            

            if( class_exists('WOOCS')){
                global $WOOCS;
                $WOOCS->storage->set_val('woocs_current_currency', get_post_meta( $cart_id, '_user_currency', true ));
                $WOOCS->current_currency = get_post_meta( $cart_id, '_user_currency', true );
            }

            $cart = get_post( $cart_id );

            $emails_sent = get_post_meta( $cart_id, '_emails_sent', true);
            $clicked = false;

            if( isset( $emails_sent[$email_id]) ){
                $clicked = ( isset( $emails_sent[$email_id]['clicked'] ) &&  $emails_sent[$email_id]['clicked'] == 1  ) ? true : false;
                $emails_sent[$email_id]['clicked'] = 1;
                update_post_meta( $cart_id, '_emails_sent', $emails_sent);
            }

            if( !$clicked ){
                //update general click counter
                YITH_WC_Recover_Abandoned_Cart_Helper()->update_counter('email_clicks_counter');
                //update email template click counter
                YITH_WC_Recover_Abandoned_Cart_Helper()->update_counter_meta( $email_id, '_email_clicks_counter' );
            }



            //add abandoned cart into the session
            if ( !empty( $cart ) ) {

                $cart_content = maybe_unserialize( get_post_meta( $cart_id, '_cart_content', true ) );

                if( $cart_content != ''){
                    WC()->session->cart = $cart_content['cart'];
                    $cookie_content = 'cart_id='.$cart_id.'&email_id='.$email_id;
                    setcookie('ywrac_recovered_cart', $cookie_content,  current_time('timestamp', 1) + 24 * 3600, '/');
                }
            }

        }

        function checkout() {
            if ( isset( $_COOKIE['ywrac_recovered_cart'] ) ) {
                parse_str( $_COOKIE['ywrac_recovered_cart'], $cookie );
                $cart_id = $cookie['cart_id'];
                if ( class_exists( 'WOOCS' ) ) {
                    global $WOOCS;
                    $WOOCS->storage->set_val( 'woocs_current_currency', get_post_meta( $cart_id, '_user_currency', true ) );
                    $WOOCS->current_currency = get_post_meta( $cart_id, '_user_currency', true );
                }
            }

        }

        function checkout_order_processed() {
            if ( isset( $_COOKIE['ywrac_recovered_cart'] ) ) {
                parse_str( $_COOKIE['ywrac_recovered_cart'], $cookie );
                $cart_id = $cookie['cart_id'];
                if ( class_exists( 'WOOCS' ) ) {
                    global $WOOCS;
                    $WOOCS->storage->set_val( 'woocs_current_currency', get_post_meta( $cart_id, '_user_currency', true ) );
                    $WOOCS->current_currency                   = get_post_meta( $cart_id, '_user_currency', true );
                    $_REQUEST['woocommerce-currency-switcher'] = $WOOCS->escape( $WOOCS->storage->get_val( 'woocs_current_currency' ) );
                    $WOOCS->current_currency                   = $WOOCS->escape( $WOOCS->storage->get_val( 'woocs_current_currency' ) );
                    $_REQUEST['woocs_in_order_currency']       = $WOOCS->current_currency;
                }
            }
        }

        /**
         * Update counters when an order is completed
         *
         * @return string
         * @since  1.0.0
         */
        public function order_processed( $order_id, $posted ){

//            if ( !isset( $_COOKIE['ywrac_recovered_cart'] ) ) {
//                if( is_user_logged_in()){
//                    $this->remove_abandoned_cart_for_current_user();
//                }
//            }

            if( isset($_COOKIE['ywrac_recovered_cart'] ) ){
                parse_str( $_COOKIE['ywrac_recovered_cart'], $cookie );

                $cart_id   = $cookie['cart_id'];
                $email_id = $cookie['email_id'];

                //add meta to order
                YITH_WC_Recover_Abandoned_Cart_Helper()->update_counter_meta( $order_id, '_ywrac_recovered' );
                //update email template meta counter of recovered cart
                YITH_WC_Recover_Abandoned_Cart_Helper()->update_counter_meta( $email_id, '_cart_recovered' );
                //update general counter of recovered cart
                YITH_WC_Recover_Abandoned_Cart_Helper()->update_counter( 'recovered_carts' );
                //update total of recovered carts
                $order = wc_get_order( $order_id );
                YITH_WC_Recover_Abandoned_Cart_Helper()->update_amount_total( $order->order_total );

                update_post_meta( $cart_id, '_cart_status', 'recovered');

                $args = array(
                    'order_id' => $order_id
                );

                if ( get_option( 'ywrac_enable_email_admin' ) == 'yes' ) {
                    do_action( 'send_recovered_cart_mail', $args );
                }

                setcookie ( "ywrac_recovered_cart",  $_COOKIE['ywrac_recovered_cart'], time()-1);

            }

            $user = get_post_meta( $order_id, '_customer_user', true);

            if( ! $user ) {
                $user = get_post_meta( $order_id, '_billing_email', true );
            }
            $this->remove_abandoned_cart_for_current_user( $user );

        }

        /**
         * Register plugins for activation tab
         *
         * @return void
         * @since    2.0.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function register_plugin_for_activation() {
            if ( ! class_exists( 'YIT_Plugin_Licence' ) ) {
                require_once( YITH_YWRAC_DIR . 'plugin-fw/licence/lib/yit-licence.php' );
                require_once( YITH_YWRAC_DIR . 'plugin-fw/licence/lib/yit-plugin-licence.php' );
            }
            YIT_Plugin_Licence()->register( YITH_YWRAC_INIT, YITH_YWRAC_SECRET_KEY, YITH_YWRAC_SLUG );
        }

        /**
         * Register plugins for update tab
         *
         * @return void
         * @since    2.0.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         */

        public function register_plugin_for_updates() {
            if( ! class_exists( 'YIT_Upgrade' ) ) {
                require_once YITH_YWRAC_DIR.'plugin-fw/lib/yit-upgrade.php';
            }
            YIT_Upgrade()->register( YITH_YWRAC_SLUG, YITH_YWRAC_INIT );
        }

        /**
         * Unsubscribe from email list
         *
         * @access public
         * @since 1.0.4
         * @author Francesco Licandro
         */
        public function unsubscribe_from_mail(){

            if( ! isset( $_GET['action'] ) || ! isset( $_GET['customer'] ) || ! is_email( $_GET['customer'] ) || $_GET['action'] != '_ywrac_unsubscribe_from_mail' ) {
                return;
            }

            // check if customer exist.
            $customer_email = $_GET['customer'];
            $customer = get_user_by( 'email', $customer_email );

            if( $customer ){ // user exist
                // set meta
                update_user_meta( $customer->ID, '_ywrac_is_unsubscribed', 1 );
            }
            else {
                // add user to blacklist
                $blacklist = get_option( 'ywrac_mail_blacklist', '' );
                $blacklist = maybe_unserialize( $blacklist );
                ( ! $blacklist || ! in_array( $customer_email, $blacklist ) ) && $blacklist[] = $customer_email;

                // then save option
                update_option( 'ywrac_mail_blacklist', $blacklist );
            }

            // delete all abandoned cart for this user
            $this->remove_abandoned_cart_for_current_user( $customer_email );

            wc_add_notice( __( 'You have successfully unsubscribed from this mailing list', 'yith-woocommerce-recover-abandoned-cart'), 'success' );
            $redirect_url = apply_filters( 'yith_ywrac_redirect_after_unsubscribe_action', get_permalink( wc_get_page_id( 'shop' ) ) );
            wp_safe_redirect( $redirect_url );
            exit;
        }
    }


}

/**
 * Unique access to instance of YITH_WC_Recover_Abandoned_Cart class
 *
 * @return \YITH_WC_Recover_Abandoned_Cart
 */
function YITH_WC_Recover_Abandoned_Cart() {
    return YITH_WC_Recover_Abandoned_Cart::get_instance();
}

