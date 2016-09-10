<?php

if ( !defined( 'ABSPATH' ) || !defined( 'YITH_YWRAC_VERSION' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Implements features of YITH WooCommerce Recover Abandoned Cart Email
 *
 * @class   YITH_WC_Recover_Abandoned_Cart
 * @package YITH WooCommerce Recover Abandoned Cart
 * @since   1.0.0
 * @author  Yithemes
 */
if ( !class_exists( 'YITH_WC_Recover_Abandoned_Cart_Email' ) ) {

    class YITH_WC_Recover_Abandoned_Cart_Email {

        /**
         * Single instance of the class
         *
         * @var \YITH_WC_Recover_Abandoned_Cart_Email
         */
        protected static $instance;

        /**
         * Post type name
         */
        public $post_type_name = 'ywrac_email';

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
            add_action( 'init', array( $this, 'add_post_type' ), 10 );
            add_action( 'admin_init', array($this, 'add_metabox'), 1);
            add_action( 'admin_init', array($this, 'action'), 1);
            add_filter( 'get_delete_post_link', array( $this, 'get_delete_post_link'), 10, 2 );

            // panel type category search
            add_action( 'wp_ajax_ywrac_email_send', array( $this, 'ajax_email_send' ) );
            add_action( 'wp_ajax_nopriv_ywrac_email_send', array( $this, 'ajax_email_send' ) );

            // panel type category search
            add_action( 'wp_ajax_ywrac_email_test_send', array( $this, 'ajax_email_test_send' ) );
            add_action( 'wp_ajax_nopriv_ywrac_email_test_send', array( $this, 'ajax_email_test_send' ) );

            if( function_exists('icl_register_string') ){
                icl_register_string( 'yith-woocommerce-recover-abandoned-cart', 'ywrac_cart_template_thumbnail' , 'Thumbnail' );
                icl_register_string( 'yith-woocommerce-recover-abandoned-cart', 'ywrac_cart_template_product' , 'Product' );
                icl_register_string( 'yith-woocommerce-recover-abandoned-cart', 'ywrac_cart_template_product_price' , 'Product Price' );
                icl_register_string( 'yith-woocommerce-recover-abandoned-cart', 'ywrac_cart_template_quantity' , 'Quantity' );
                icl_register_string( 'yith-woocommerce-recover-abandoned-cart', 'ywrac_cart_template_total' , 'Total' );
                icl_register_string( 'yith-woocommerce-recover-abandoned-cart', 'ywrac_cart_template_cart_subtotal' , 'Cart Subtotal' );
            }


        }

        
        /**
         * Register the custom post type ywrac_email
         *
         * @since  1.0.0
         * @author Emanuela Castorina
         */
        public function add_post_type() {

                $labels = array(
                    'name'               => _x( 'Email Templates', 'Post Type General Name', 'yith-woocommerce-recover-abandoned-cart' ),
                    'singular_name'      => _x( 'Email Template', 'Post Type Singular Name', 'yith-woocommerce-recover-abandoned-cart' ),
                    'menu_name'          => __( 'Email Template', 'yith-woocommerce-recover-abandoned-cart' ),
                    'parent_item_colon'  => __( 'Parent Item:', 'yith-woocommerce-recover-abandoned-cart' ),
                    'all_items'          => __( 'All Email Templates', 'yith-woocommerce-recover-abandoned-cart' ),
                    'view_item'          => __( 'View Email Templates', 'yith-woocommerce-recover-abandoned-cart' ),
                    'add_new_item'       => __( 'Add New Email Template', 'yith-woocommerce-recover-abandoned-cart' ),
                    'add_new'            => __( 'Add New Email Template', 'yith-woocommerce-recover-abandoned-cart' ),
                    'edit_item'          => __( 'Edit Email Template', 'yith-woocommerce-recover-abandoned-cart' ),
                    'update_item'        => __( 'Update Email Template', 'yith-woocommerce-recover-abandoned-cart' ),
                    'search_items'       => __( 'Search Email Template', 'yith-woocommerce-recover-abandoned-cart' ),
                    'not_found'          => __( 'Not found', 'yith-woocommerce-recover-abandoned-cart' ),
                    'not_found_in_trash' => __( 'Not found in Trash', 'yith-woocommerce-recover-abandoned-cart' ),
                );
                $args = array(
                    'label'               => __( 'Email Templates', 'yith-woocommerce-recover-abandoned-cart' ),
                    'description'         => '',
                    'labels'              => $labels,
                    'supports'            => array( 'title', 'editor' ),
                    'hierarchical'        => false,
                    'public'              => false,
                    'show_ui'             => true,
                    'show_in_menu'        => false,
                    'exclude_from_search' => true,
                    'capability_type'     => 'post',
                    'map_meta_cap'        => true
                    );

                register_post_type($this->post_type_name, $args);
            }

        /**
         * Add metabox into ywrac_email editor page
         *
         * @since  1.0.0
         * @author Emanuela Castorina
         */
        public function add_metabox() {
            $args = require_once( YITH_YWRAC_DIR . 'plugin-options/metabox/ywrac_email_metabox.php' );
            if ( !function_exists( 'YIT_Metabox' ) ) {
                require_once( 'plugin-fw/yit-plugin.php' );
            }
            $metabox = YIT_Metabox( 'ywrac-email' );
            $metabox->init( $args );

            $args           = require_once( YITH_YWRAC_DIR . 'plugin-options/metabox/ywrac_email_metabox_coupon.php' );
            $metabox_coupon = YIT_Metabox( 'ywrac-email-coupon' );
            $metabox_coupon->init( $args );

            $args         = require_once( YITH_YWRAC_DIR . 'plugin-options/metabox/ywrac_email_metabox_stat.php' );
            $metabox_stat = YIT_Metabox( 'ywrac-email-stat' );
            $metabox_stat->init( $args );

            $args         = require_once( YITH_YWRAC_DIR . 'plugin-options/metabox/ywrac_email_metabox_test.php' );
            $metabox_test = YIT_Metabox( 'ywrac-email-test' );
            $metabox_test->init( $args );
        }

        /**
         * Activate
         *
         * change the status _ywrac_email_active to the current email template
         *
         * @since  1.0.0
         * @author Emanuela Castorina
         */
        public function activate( $email_id, $activate = true ){
            if( $activate ){
                update_post_meta( $email_id, '_ywrac_email_active', 'yes');
            }else{
                update_post_meta( $email_id, '_ywrac_email_active', 'no');
            }
        }

        /**
         * Remove the "Move to trash" button in email template Editor
         *
         * @since  1.0.0
         * @author Emanuela Castorina
         */
        public function get_delete_post_link( $url, $post_id ){

            $post_type = get_post_type( $post_id );
            if( $post_type != $this->post_type_name ){
                return $url;
            }

            $action = 'delete';
            $delete_link = add_query_arg( 'action', $action, admin_url( 'admin.php' ) );
            $delete_link = add_query_arg( 'page', YITH_WC_Recover_Abandoned_Cart_Admin()->get_panel_page(), $delete_link );
            $delete_link = add_query_arg( 'tab', 'email', $delete_link );
            $delete_link = add_query_arg( 'post', $post_id, $delete_link );
            $delete_link = wp_nonce_url( $delete_link, "$action-post_{$post_id}" );
            return $delete_link;

        }


        /**
         * Delete a post
         *
         * @since  1.0.0
         * @author Emanuela Castorina
         */
        function action(){

            if( ! isset( $_GET['action'] ) || ! isset( $_GET['_wpnonce'] )  ||  ! isset( $_GET['post'] ) || ! isset( $_GET['page'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], $_GET['action'].'-post_'. $_GET['post'] )  ){
                return;
            }

            $action = $_GET['action'];
            $post = get_post( $_GET['post'] );

            if( !empty( $post ) ){
                $post_type_object = get_post_type_object( $post->post_type );
                if( $action == 'delete' && $post->post_type == $this->post_type_name && current_user_can( $post_type_object->cap->delete_post, $post->ID ) ){
                    wp_delete_post( $post->ID, true );
                }
            }

        }

        /**
         * Get the email template objects
         *
         * @since  1.0.0
         * @author Emanuela Castorina
         */
        public function get_email_templates( $only_active = false ){
            $args = array(
                'post_type' => $this->post_type_name,
                'posts_per_page' => -1,
                'suppress_filters' => false
            );

            if( $only_active ){
                $args['meta_key'] = '_ywrac_email_active';
                $args['meta_value'] = 'yes';
            }

            $emails = get_posts( $args );

            return $emails;
        }

        /**
         * Email cron send emails for each active email template
         *
         * @since  1.0.0
         * @author Emanuela Castorina
         */
        public function email_cron() {

            $emails = $this->get_email_templates( true );

            if ( !( empty( $emails ) ) ) {
                foreach ( $emails as $email ) {

                    $time_type  = get_post_meta( $email->ID, '_ywrac_type_time', true );
                    $time_qty   = get_post_meta( $email->ID, '_ywrac_time', true );
                    $email_sent = get_post_meta( $email->ID, '_cart_emails_sent', true ); //list of carts
                    $email_auto = get_post_meta( $email->ID, '_ywrac_email_auto', true );



                    if ( $email_auto != 'yes'  || $time_qty == '' ) {
                        continue;
                    }

                    $cutoff = ywrac_get_cutoff( $time_qty, $time_type );


                    $start_to_date = (int) (  current_time('timestamp', 1) - $cutoff );
                    $args          = array(
                        'post_type'      => YITH_WC_Recover_Abandoned_Cart()->post_type_name,
                        'post_status'    => 'publish',
                        'posts_per_page' => - 1,
                        'date_query'     => array(
                            array(
                                'column' => 'post_modified_gmt',
                                'before' => date( "Y-m-d H:i:s", $start_to_date ),
                            ),
                        ),
                        'meta_query'     => array(
                            array(
                                'key'     => '_cart_status',
                                'value'   => 'abandoned',
                                'compare' => 'LIKE',
                            ),
                        ),
                        'post__not_in'   => $email_sent
                    );

                    $carts = get_posts( $args );

                    if ( !empty ( $carts ) ) {
                        foreach ( $carts as $cart ) {
                            $lang = get_post_meta( $cart->ID, '_language', true );

                            //todo:verificare questo
                            // Check for WPML
//                            if( defined('ICL_LANGUAGE_CODE') ){
//                                $email_id       = $email->ID;
//                                $tr_email_id    = ( function_exists('wpml_object_id_filter') ) ? wpml_object_id_filter(  $email_id, 'ywrac_email', true, $lang ) : apply_filters( $email_id, 'ywrac_email', true, $lang );
//                                $email          = get_post( $tr_email_id );
//                                $email->ID      = $email_id;
//                            }
//
//                            $this->email_send( $cart->ID, $email, $lang );
//
                            $this->email_send( $cart->ID, $email, $lang );
                        }
                    }
                }

            }

        }

        /**
         * Send email for recovery a single cart in ajax
         *
         * @return void
         * @since 1.0
         */
        public function ajax_email_send(){

            check_ajax_referer( 'send-email', 'security' );

            if ( !isset( $_POST['cart_id'] ) || !isset( $_POST['email_template'] ) ) {
                return;
            }


            $email_id = $_POST['email_template'];
            $lang     = get_post_meta( $_POST['cart_id'], '_language', true );

            if( defined('ICL_LANGUAGE_CODE') ){
                $email_id = ( function_exists('wpml_object_id_filter') ) ? wpml_object_id_filter(  $email_id, 'ywrac_email', true, $lang ) : icl_object_id(  $email_id, 'ywrac_email', true, $lang );
            }

            $email_template = get_post( $email_id );

            if( ! empty( $email_template ) ){
                $result = $this->email_send( $_POST['cart_id'], $email_template, $lang );
            }else{
                $result = false;
            }


            wp_send_json( $result );

        }

        /**
         * Send an email from a template email
         *
         * @return void
         * @since 1.0
         */
        public function ajax_email_test_send(){

            check_ajax_referer( 'send-email', 'security' );

            if ( !isset( $_POST['email_to_sent'] ) || !isset( $_POST['email_template'] ) ) {
                return;
            }


            $email_id = $_POST['email_template'];

            //todo:get the language of the post
            $lang     = 'en';

            $email_template = get_post( $email_id );
            $email_to_send = $_POST['email_to_sent'];
            if( ! empty( $email_template ) ){
                $result = $this->email_send_test( $email_to_send, $email_template, $lang );
            }else{
                $result = false;
            }


            wp_send_json( $result );

        }

        /**
         * Send email test
         *
         * @return void
         * @since 1.0
         */
        public function email_send_test( $email_to_send, $email, $lang ){

            $email_sender_name = get_option( 'ywrac_sender_name' );
            $email_sender      = get_option( 'ywrac_email_sender' );
            $email_reply_to    = get_option( 'ywrac_email_reply' );
            $email_subject     = get_post_meta( $email->ID, '_ywrac_email_subject', true);

            $template_content  = $email->post_content;


            $args = array(
                'email_test'     => true,
                'email_id'       => $email->ID,
                'email_name'     => $email->post_title,
                'user_email'     => $email_to_send,
                'email_content'  => $template_content,
                'email_heading'  => $email_sender_name,
                'email_sender'   => $email_sender,
                'email_reply_to' => $email_reply_to,
                'email_subject'  => $email_subject,
            );

            do_action( 'send_rac_mail', $args );

            $email_sent =  get_post_meta( $email->ID, '_email_test_sent', true);

            $result = array(
                'email_sent' => $email_sent,
            );

            update_post_meta( $email->ID, '_email_test_sent', 0 );

            return $result;

        }

        /**
         * Send email for recovery a single cart
         *
         * @return mixed
         * @since 1.0
         */
        public function email_send( $cart_id, $email, $lang ){

            $user_first_name = get_post_meta( $cart_id, '_user_first_name', true );
            $user_last_name  = get_post_meta( $cart_id, '_user_last_name', true );
            $user_email      = get_post_meta( $cart_id, '_user_email', true );

            if( apply_filters( 'ywrac_skip_user_from_mail', false, $user_email ) ) {
                return false;
            }

            $cart_content = get_post_meta( $cart_id, '_cart_content', true );
            $cart_content_meta = ( $cart_content != '') ? maybe_unserialize( $cart_content ) : '';

            if( class_exists('WOOCS')){
                global $WOOCS;
                $WOOCS->current_currency = get_post_meta( $cart_id, '_user_currency', true );
            }

            $email_sender_name = get_option( 'ywrac_sender_name' );
            $email_sender      = get_option( 'ywrac_email_sender' );
            $email_reply_to    = get_option( 'ywrac_email_reply' );
            $email_subject     = get_post_meta( $email->ID, '_ywrac_email_subject', true );
            $template_content  = nl2br($email->post_content);
            $cart_content      = YITH_WC_Recover_Abandoned_Cart()->get_cart_content( $cart_id, $cart_content_meta, $lang );
            $cart_link         = YITH_WC_Recover_Abandoned_Cart()->get_cart_link( $cart_id, $email->ID );
            $unsubscribe_link  = $this->get_unsubscribe_link( $user_email );
            $template_content  = str_replace( '{{ywrac.firstname}}', $user_first_name, $template_content );
            $template_content  = str_replace( '{{ywrac.lastname}}', $user_last_name, $template_content );
            $template_content  = str_replace( '{{ywrac.fullname}}', $user_first_name . ' ' . $user_last_name, $template_content );
            $template_content  = str_replace( '{{ywrac.useremail}}', $user_email, $template_content );
            $template_content  = str_replace( '{{ywrac.cart}}', $cart_content, $template_content );
            $template_content  = str_replace( '{{ywrac.cartlink}}', $cart_link, $template_content );
            $template_content  = str_replace( '{{ywrac.unsubscribelink}}', $unsubscribe_link, $template_content );


            //check if a coupon must be send with the email
            $pos = strpos( $template_content, '{{ywrac.coupon}}' );
            if ( $pos !== false ) {
                $coupon_code      = $this->create_coupon( $cart_id, $email->ID );
                if( $coupon_code ){
                    $template_content = str_replace( '{{ywrac.coupon}}', $coupon_code, $template_content );
                    update_post_meta( $cart_id, '_coupon_code', $coupon_code );
                }else{
                    $template_content = str_replace( '{{ywrac.coupon}}', '', $template_content );
                }
            }

            $args = array(
                'cart_id'        => $cart_id,
                'email_id'       => $email->ID,
                'email_name'     => $email->post_title,
                'user_email'     => $user_email,
                'email_content'  => $template_content,
                'email_heading'  => $email_sender_name,
                'email_sender'   => $email_sender,
                'email_reply_to' => $email_reply_to,
                'email_subject'  => $email_subject,
            );

            do_action( 'send_rac_mail', $args );

            $email_sent =  get_post_meta( $cart_id, '_email_sent', true);

            $result = array(
                'email_sent' => $email_sent,
                'email_name' => $email->post_title
            );


            return $result;

        }

        /**
         * Get unsubscribe link
         *
         * @access public
         * @since 1.0.4
         * @param string $user_email
         * @return string
         * @author Francesco Licandro
         */
        public function get_unsubscribe_link( $user_email ){
            return $url = esc_url_raw( add_query_arg( array(
                'action' => '_ywrac_unsubscribe_from_mail',
                'customer' => $user_email ), home_url() ) );
        }

        /**
         * Create a new coupon to send with email
         *
         * @return void
         * @since 1.0
         */
        public function create_coupon( $cart_id, $email_id ){

            $amount        = get_post_meta( $email_id, '_ywrac_coupon_value', true );

            if( empty( $amount ) || $amount == 0 ){
                return false;
            }

            $prefix        = get_option( 'ywrac_coupon_prefix' );
            $coupon_code   = uniqid( strtolower( $prefix ).'_' ); // Code
            $discount_type = get_post_meta( $email_id, '_ywrac_coupon_type', true );
            $expiry_time   = current_time('timestamp', 1) + get_post_meta( $email_id, '_ywrac_coupon_validity', true ) * 24 * 3600;

            $coupon = array(
                'post_title' => $coupon_code,
                'post_content' => '',
                'post_status' => 'publish',
                'post_author' => 1,
                'post_type'		=> 'shop_coupon'
            );

            $new_coupon_id = wp_insert_post( $coupon );

            update_post_meta( $new_coupon_id, 'discount_type', $discount_type );
            update_post_meta( $new_coupon_id, 'coupon_amount', $amount );
            update_post_meta( $new_coupon_id, 'individual_use', 'no' );
            update_post_meta( $new_coupon_id, 'product_ids', '' );
            update_post_meta( $new_coupon_id, 'exclude_product_ids', '' );
            update_post_meta( $new_coupon_id, 'usage_limit', '1' );
            update_post_meta( $new_coupon_id, 'expiry_date', date( 'Y-m-d', $expiry_time ) );
            update_post_meta( $new_coupon_id, 'apply_before_tax', 'yes' );
            update_post_meta( $new_coupon_id, 'free_shipping', 'no' );
            update_post_meta( $new_coupon_id, 'ywrac_coupon', 'yes' );

            return $coupon_code;

        }


    }
}

/**
 * Unique access to instance of YITH_WC_Recover_Abandoned_Cart_Email class
 *
 * @return \YITH_WC_Recover_Abandoned_Cart_Email
 */
function YITH_WC_Recover_Abandoned_Cart_Email() {
    return YITH_WC_Recover_Abandoned_Cart_Email::get_instance();
}



