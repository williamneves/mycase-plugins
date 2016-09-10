<?php
/**
 * This file belongs to the YIT Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Main class
 *
 * @class   YITH_WC_Share_For_Discounts
 * @package Yithemes
 * @since   1.0.0
 * @author  Your Inspiration Themes
 */

if ( !class_exists( 'YITH_WC_Share_For_Discounts' ) ) {

    class YITH_WC_Share_For_Discounts {

        /**
         * Single instance of the class
         *
         * @var \YITH_WC_Share_For_Discounts
         * @since 1.0.0
         */
        protected static $instance;

        /**
         * Panel object
         *
         * @var     /Yit_Plugin_Panel object
         * @since   1.0.0
         * @see     plugin-fw/lib/yit-plugin-panel.php
         */
        protected $_panel = null;

        /**
         * @var $_premium string Premium tab template file name
         */
        protected $_premium = 'premium.php';

        /**
         * @var string Premium version landing link
         */
        protected $_premium_landing = 'http://yithemes.com/themes/plugins/yith-woocommerce-share-for-discounts/';

        /**
         * @var string Plugin official documentation
         */
        protected $_official_documentation = 'http://yithemes.com/docs-plugins/yith-woocommerce-share-for-discounts/';

        /**
         * @var string Yith WooCommerce Share For Discounts panel page
         */
        protected $_panel_page = 'yith-wc-share-for-discounts';

        /**
         * Returns single instance of the class
         *
         * @return \YITH_WC_Share_For_Discounts
         * @since 1.0.0
         */
        public static function get_instance() {

            if ( is_null( self::$instance ) ) {

                self::$instance = new self;

            }

            return self::$instance;

        }

        /**
         * @var array
         */
        var $_coupon_options = array();

        /**
         * Constructor
         *
         * @since   1.0.0
         * @return  mixed
         * @author  Alberto Ruggiero
         */
        public function __construct() {

            if ( !function_exists( 'WC' ) ) {
                return;
            }

            //Load plugin framework
            add_action( 'plugins_loaded', array( $this, 'plugin_fw_loader' ), 12 );
            add_filter( 'plugin_action_links_' . plugin_basename( YWSFD_DIR . '/' . basename( YWSFD_FILE ) ), array( $this, 'action_links' ) );
            add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 4 );
            add_action( 'admin_menu', array( $this, 'add_menu_page' ), 5 );
            add_action( 'yith_share_for_discounts_premium', array( $this, 'premium_tab' ) );

            $this->includes();

            if ( get_option( 'ywsfd_enable_plugin' ) == 'yes' ) {

                add_action( 'init', array( $this, 'get_coupon_options' ) );

                $this->session = new YWSFD_Session();

                YWSFD_Ajax();

                if ( is_admin() ) {

                    add_action( 'admin_notices', array( $this, 'check_active_options' ), 10 );

                }
                else {

                    add_action( 'woocommerce_before_main_content', array( $this, 'show_ywsfd_product_page' ), 5 );
                    add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );
                    add_action( 'woocommerce_add_to_cart', array( $this, 'check_coupon' ), 10, 2 );
                    add_action( 'woocommerce_before_checkout_process', array( $this, 'check_coupon_checkout' ) );
                    add_action( 'woocommerce_check_cart_items', array( $this, 'coupon_validation' ) );

                }

                add_action( 'wp_login', array( $this, 'switch_to_logged_user' ) );

            }

        }

        /**
         * Files inclusion
         *
         * @since   1.0.0
         * @return  void
         * @author  Alberto Ruggiero
         */
        private function includes() {

            include_once( 'includes/class-ywsfd-ajax.php' );
            include_once( 'includes/class-ywsfd-session.php' );

        }

        /**
         * ADMIN FUNCTIONS
         */

        /**
         * Add a panel under YITH Plugins tab
         *
         * @since   1.0.0
         * @return  void
         * @author  Alberto Ruggiero
         * @use     /Yit_Plugin_Panel class
         * @see     plugin-fw/lib/yit-plugin-panel.php
         */
        public function add_menu_page() {

            if ( !empty( $this->_panel ) ) {
                return;
            }

            $admin_tabs = array(
                'general' => __( 'General Settings', 'yith-woocommerce-share-for-discounts' )
            );

            if ( defined( 'YWSFD_PREMIUM' ) ) {
                $admin_tabs['coupon'] = __( 'Coupon Settings', 'yith-woocommerce-share-for-discounts' );
                $admin_tabs['share']  = __( 'Sharing Settings', 'yith-woocommerce-share-for-discounts' );
            }
            else {
                $admin_tabs['premium-landing'] = __( 'Premium Version', 'yith-woocommerce-share-for-discounts' );
            }

            $args = array(
                'create_menu_page' => true,
                'parent_slug'      => '',
                'page_title'       => __( 'Share For Discounts', 'yith-woocommerce-share-for-discounts' ),
                'menu_title'       => __( 'Share For Discounts', 'yith-woocommerce-share-for-discounts' ),
                'capability'       => 'manage_options',
                'parent'           => '',
                'parent_page'      => 'yit_plugin_panel',
                'page'             => $this->_panel_page,
                'admin-tabs'       => $admin_tabs,
                'options-path'     => YWSFD_DIR . 'plugin-options'
            );

            $this->_panel = new YIT_Plugin_Panel_WooCommerce( $args );

        }

        /**
         * Check if active options have at least a social network selected
         *
         * @since   1.0.0
         * @return  void
         * @author  Alberto Ruggiero
         */
        public function check_active_options() {

            if ( isset( $_POST['ywsfd_enable_facebook'] ) && '1' == $_POST['ywsfd_enable_facebook'] ) {

                if ( $_POST['ywsfd_appid_facebook'] == '' ) :

                    ?>
                    <div class="error">
                        <p>
                            <?php _e( 'You need to add a Facebook App ID', 'yith-woocommerce-share-for-discounts' ); ?>
                        </p>
                    </div>
                    <?php

                endif;

            }

        }

        /**
         * FRONTEND FUNCTIONS
         */

        /**
         * Initializes CSS and javascript
         *
         * @since   1.0.0
         * @return  void
         * @author  Alberto Ruggiero
         */
        public function frontend_scripts() {

            global $post;

            $params = array(
                'ajax_social_url'    => add_query_arg( 'action', 'ywsfd_get_coupon', str_replace( array( 'https:', 'http:' ), '', admin_url( 'admin-ajax.php' ) ) ),
                'apply_coupon_nonce' => wp_create_nonce( 'apply-coupon' ),
                'post_id'            => isset( $post->ID ) ? $post->ID : '',
                'sharing'            => array(
                    'url'              => apply_filters( 'ywsfd_post_url', isset( $post->ID ) ? get_permalink( $post->ID ) : '' ),
                    'message'          => apply_filters( 'ywsfd_post_message', isset( $post->ID ) ? get_the_title( $post->ID ) : '' ),
                    'twitter_username' => ( get_option( 'ywsfd_user_twitter' ) != '' ? get_option( 'ywsfd_user_twitter' ) : '' ),
                ),
                'locale'             => get_locale(),
                'facebook'           => 'no',
                'twitter'            => 'no',
                'google'             => 'no'
            );

            if ( get_option( 'ywsfd_enable_facebook' ) == 'yes' && get_option( 'ywsfd_appid_facebook' ) != '' ) {

                $params['facebook']  = 'yes';
                $params['fb_app_id'] = get_option( 'ywsfd_appid_facebook' );

            }

            if ( get_option( 'ywsfd_enable_twitter' ) == 'yes' ) {

                $params['twitter'] = 'yes';

            }

            if ( get_option( 'ywsfd_enable_google' ) == 'yes' ) {

                $params['google'] = 'yes';

            }

            $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

            wp_enqueue_script( 'ywsfd-frontend', YWSFD_ASSETS_URL . '/js/ywsfd-frontend' . $suffix . '.js', array( 'jquery' ) );

            wp_enqueue_style( 'ywsfd-frontend', YWSFD_ASSETS_URL . '/css/ywsfd-frontend' . $suffix . '.css' );

            wp_localize_script( 'ywsfd-frontend', 'ywsfd', apply_filters( 'ywsfd_scripts_filter', $params ) );

        }

        /**
         * Get the position and show YWSFD in product page
         *
         * @since   1.0.0
         * @return  void
         * @author  Alberto Ruggiero
         */
        public function show_ywsfd_product_page() {

            if ( get_option( 'ywsfd_share_product_enable' ) != 'no' ) {

                $args = apply_filters( 'ywsfd_share_position', array(
                    'hook'     => 'single_product',
                    'priority' => 25 ) );

                add_action( 'woocommerce_' . $args['hook'] . '_summary', array( $this, 'add_ywsfd_template' ), $args['priority'] );

            }

        }

        /**
         * Add YWSFD to product page
         *
         * @since   1.0.0
         * @return  void
         * @author  Alberto Ruggiero
         */
        public function add_ywsfd_template() {

            if ( $this->check_social_active() ) {

                if ( !$this->coupon_already_assigned() ):?>
                    <div id="YWSFD_wrapper" class="woocommerce ywsfd-wrapper">
                        <h2>
                            <?php echo apply_filters( 'ywsfd_share_title', __( 'Share the product and get 10% discount!', 'yith-woocommerce-share-for-discounts' ) ); ?>
                        </h2>

                        <div class="ywsfd-social">
                            <?php

                            global $post;

                            $social_params = apply_filters( 'ywsfd_social_params', array(
                                'sharing'       => array(
                                    'url'              => apply_filters( 'ywsfd_post_url', isset( $post->ID ) ? get_permalink( $post->ID ) : '' ),
                                    'message'          => apply_filters( 'ywsfd_post_message', isset( $post->ID ) ? get_the_title( $post->ID ) : '' ),
                                    'twitter_username' => ( get_option( 'ywsfd_user_twitter' ) != '' ? get_option( 'ywsfd_user_twitter' ) : '' ),
                                ),
                                'facebook'      => get_option( 'ywsfd_enable_facebook' ),
                                'facebook_type' => get_option( 'ywsfd_button_type_facebook' ),
                                'twitter'       => get_option( 'ywsfd_enable_twitter' ),
                                'google'        => get_option( 'ywsfd_enable_google' ),
                                'google_type'   => get_option( 'ywsfd_button_type_google' ),
                            ) );

                            include( YWSFD_TEMPLATE_PATH . '/frontend/social-buttons.php' );

                            apply_filters( 'ywsfd_social_buttons', '', $social_params );

                            ?>

                        </div>
                    </div>

                <?php else: ?>

                    <div id="YWSFD_wrapper" class="ywsfd-wrapper">
                        <h2>
                            <?php echo apply_filters( 'ywsfd_share_title_after', __( 'Thank you for sharing!', 'yith-woocommerce-share-for-discounts' ) ); ?>
                        </h2>

                        <div class="ywsfd-social">
                            <div class="ywsfd-after-share">
                                <?php echo apply_filters( 'ywsfd_share_message', __( 'Your discount has been activated and applied to your shopping cart.', 'yith-woocommerce-share-for-discounts' ) ); ?>
                            </div>
                        </div>
                    </div>

                <?php endif;

            }

        }

        /**
         * Check if at least a social network is active
         *
         * @since   1.0.0
         * @return  array
         * @author  Alberto Ruggiero
         */
        public function check_social_active() {

            $socials = apply_filters( 'ywsfd_available_socials', array(
                'facebook',
                'twitter',
                'google'
            ) );

            $active = false;

            foreach ( $socials as $social ) {

                if ( get_option( 'ywsfd_enable_' . $social ) == 'yes' ) {

                    $active = true;

                }

            }

            return $active;

        }

        /**
         * Check if current product was shared from user and get coupon code
         *
         * @since   1.0.0
         *
         * @param   $product_id
         *
         * @return  boolean|string
         * @author  Alberto Ruggiero
         */
        public function coupon_already_assigned( $product_id = false ) {

            if ( !$product_id ) {

                global $post;
                $product_id = $post->ID;

            }

            $result    = false;
            $user_data = $this->get_user_data();

            switch ( $this->_coupon_options['discount_type'] ) {
                case 'fixed_cart':
                case 'percent':
                    $args = array(
                        'post_type'   => 'shop_coupon',
                        'post_status' => 'publish',
                        'meta_query'  => array(
                            array(
                                'key'     => 'customer_email',
                                'value'   => $user_data['email'],
                                'compare' => '=',
                            ),
                        ),
                        'date_query'  => array(
                            array(
                                'year'  => date( 'Y' ),
                                'month' => date( 'm' ),
                                'day'   => date( 'd' ),
                            ),
                        ),
                    );
                    break;
                default:

                    if ( get_post_type( $product_id ) == 'product' ) {

                        $args = array(
                            'post_type'   => 'shop_coupon',
                            'post_status' => 'publish',
                            'meta_query'  => array(
                                'relation' => 'AND',
                                array(
                                    'key'     => 'product_ids',
                                    'value'   => $product_id,
                                    'compare' => '=',
                                ),
                                array(
                                    'key'     => 'customer_email',
                                    'value'   => $user_data['email'],
                                    'compare' => '=',
                                ),
                            ),
                            'date_query'  => array(
                                array(
                                    'year'  => date( 'Y' ),
                                    'month' => date( 'm' ),
                                    'day'   => date( 'd' ),
                                ),
                            ),
                        );


                    }
                    else {

                        $args = array(
                            'post_type'   => 'shop_coupon',
                            'post_status' => 'publish',
                            'meta_query'  => array(
                                'relation' => 'AND',
                                array(
                                    'key'     => 'other_page_id',
                                    'value'   => $product_id,
                                    'compare' => '=',
                                ),
                                array(
                                    'key'     => 'customer_email',
                                    'value'   => $user_data['email'],
                                    'compare' => '=',
                                ),
                            ),
                            'date_query'  => array(
                                array(
                                    'year'  => date( 'Y' ),
                                    'month' => date( 'm' ),
                                    'day'   => date( 'd' ),
                                ),
                            ),
                        );

                    }

            }


            $query = new WP_Query( $args );

            if ( $query->have_posts() ) {

                while ( $query->have_posts() ) {

                    $query->the_post();
                    $result = $query->post->post_title;

                }

            }

            wp_reset_query();
            wp_reset_postdata();

            return $result;

        }

        /**
         * Get current user data
         *
         * @since   1.0.0
         * @return  array
         * @author  Alberto Ruggiero
         */
        public function get_user_data() {

            $user_data = array(
                'nickname' => '',
                'email'    => '',
            );

            if ( is_user_logged_in() ) {

                $current_user = wp_get_current_user();

                $user_data['nickname'] = get_user_meta( $current_user->ID, 'nickname', true );
                $user_data['email']    = get_user_meta( $current_user->ID, 'billing_email', true );

            }
            else {

                $guest_id = $this->session->get( 'guest_id' );

                if ( empty( $guest_id ) ) {

                    $guest_id = uniqid( rand(), false );
                    $this->session->set( 'guest_id', $guest_id );

                }

                $user_data['nickname'] = __( 'Guest', 'yith-woocommerce-share-for-discounts' );
                $user_data['email']    = $guest_id;

            }

            return $user_data;

        }

        /**
         * Re-assign coupon when user is logged
         *
         * @since   1.0.0
         * @return  void
         * @author  Alberto Ruggiero
         */
        public function switch_to_logged_user() {

            $guest_id = $this->session->get( 'guest_id' );

            if ( !empty( $guest_id ) ) {

                if ( isset( $_POST['username'] ) ) {

                    $user_id = $_POST['username'];

                }
                else {

                    $user_id = $_POST['log'];

                }

                $user = get_user_by( 'login', $user_id );

                $this->assign_guest_coupon( $guest_id, $user->user_email );

                $this->session->destroy_session();
            }

        }

        /**
         * Re-assign coupon on checkout
         *
         * @since   1.0.7
         * @return  void
         * @author  Alberto Ruggiero
         */
        public function check_coupon_checkout() {

            $guest_id = $this->session->get( 'guest_id' );

            if ( !empty( $guest_id ) ) {

                $this->assign_guest_coupon( $guest_id, $_POST['billing_email'] );

            }

        }

        /**
         * Re-assign coupon when to provided email address
         *
         * @since   1.0.7
         *
         * @param   $guest_id
         * @param   $user_email
         *
         * @return  void
         * @author  Alberto Ruggiero
         */
        public function assign_guest_coupon( $guest_id, $user_email ) {

            $found_ids = array();
            $args      = array(
                'post_type'   => 'shop_coupon',
                'post_status' => 'publish',
                'meta_query'  => array(
                    array(
                        'key'     => 'customer_email',
                        'value'   => $guest_id,
                        'compare' => '=',
                    ),
                ),
                'date_query'  => array(
                    array(
                        'year'  => date( 'Y' ),
                        'month' => date( 'm' ),
                        'day'   => date( 'd' ),
                    ),
                ),
            );

            $query = new WP_Query( $args );

            if ( $query->have_posts() ) {

                while ( $query->have_posts() ) {

                    $query->the_post();
                    $found_ids[] = $query->post->ID;

                }

            }

            wp_reset_query();
            wp_reset_postdata();


            if ( !empty( $found_ids ) ) {

                foreach ( $found_ids as $coupon_id ) {

                    update_post_meta( $coupon_id, 'customer_email', $user_email );

                }

            }

        }

        /**
         * Get coupon settings
         *
         * @since   1.0.0
         * @return  string
         * @author  Alberto Ruggiero
         */
        public function get_coupon_options() {

            $this->_coupon_options = apply_filters( 'ywsfd_coupon_options', array(
                'description'   => __( '10% off for the shared product', 'yith-woocommerce-share-for-discounts' ),
                'discount_type' => 'percent_product',
                'coupon_amount' => 10,
                'expiry_days'   => 1,
            ) );

        }

        /**
         * Creates a coupon with specific settings
         *
         * @since   1.0.0
         *
         * @param   $user_data
         * @param   $product_id
         *
         * @return  string
         * @author  Alberto Ruggiero
         */
        public function create_coupon( $user_data, $product_id ) {

            $coupon_code = $user_data['nickname'] . '-' . current_time( 'YmdHis' );

            $coupon_option = $this->_coupon_options;

            $coupon_data = array(
                'post_title'   => $coupon_code,
                'post_excerpt' => $coupon_option['description'],
                'post_content' => '',
                'post_status'  => 'publish',
                'post_author'  => 1,
                'post_type'    => 'shop_coupon'
            );

            $coupon_id   = wp_insert_post( $coupon_data );
            $expiry_date = ( $coupon_option['expiry_days'] != '' ) ? date( 'Y-m-d', strtotime( '+' . $coupon_option['expiry_days'] . ' days' ) ) : '';

            switch ( $coupon_option['discount_type'] ) {
                case 'fixed_cart':
                case 'percent':
                    $product_ids = '';
                    break;
                default:

                    if ( !empty( $product_id ) ) {

                        if ( get_post_type( $product_id ) == 'product' ) {

                            $product_ids = $product_id;

                        }
                        else {

                            $product_ids = '';
                            update_post_meta( $coupon_id, 'other_page_id', $product_id );

                        }

                    }
                    else {
                        $product_ids = '';
                    }


            }

            update_post_meta( $coupon_id, 'discount_type', $coupon_option['discount_type'] );
            update_post_meta( $coupon_id, 'coupon_amount', $coupon_option['coupon_amount'] );
            update_post_meta( $coupon_id, 'free_shipping', ( isset( $coupon_option['free_shipping'] ) && $coupon_option['free_shipping'] != '' ? 'yes' : 'no' ) );
            update_post_meta( $coupon_id, 'expiry_date', $expiry_date );
            update_post_meta( $coupon_id, 'product_ids', $product_ids );
            update_post_meta( $coupon_id, 'customer_email', $user_data['email'] );
            update_post_meta( $coupon_id, 'usage_limit', '1' );
            update_post_meta( $coupon_id, 'usage_limit_per_user', '1' );
            update_post_meta( $coupon_id, 'generated_by', 'ywsfd' );


            return $coupon_code;

        }

        /**
         * Check if the coupon for current product needs to be added after adding product to cart
         *
         * @since   1.0.0
         *
         * @param $cart_item_key
         * @param $product_id
         *
         * @return  void
         * @author  Alberto Ruggiero
         */
        public function check_coupon( $cart_item_key, $product_id ) {

            $coupon_id = $this->coupon_already_assigned( $product_id );

            if ( $coupon_id && !in_array( strtolower( $coupon_id ), WC()->cart->applied_coupons ) ) {

                WC()->cart->add_discount( $coupon_id );

            }

        }

        /**
         * Prevent multiple discount on the same article.
         *
         * @since   1.1.3
         * @return  void
         * @author  Alberto Ruggiero
         */
        public function coupon_validation() {

            $today = getdate();

            foreach ( WC()->cart->get_coupons() as $coupon ) {

                if ( get_post_meta( $coupon->id, 'generated_by', true ) == 'ywsfd' && get_post_status( $coupon->id ) == 'publish' ) {

                    $args = array(
                        'post_type'      => 'shop_coupon',
                        'post_status'    => 'publish',
                        'posts_per_page' => - 1,
                        'post__not_in'   => array( $coupon->id ),
                        'date_query'     => array(
                            array(
                                'year'  => $today['year'],
                                'month' => $today['mon'],
                                'day'   => $today['mday'],
                            ),
                        ),
                        'meta_query'     => array(
                            'relation' => 'AND',
                            array(
                                'key'   => 'generated_by',
                                'value' => 'ywsfd',
                            ),
                            array(
                                'relation' => 'AND',
                                array(
                                    'key'     => 'product_ids',
                                    'value'   => implode( ',', $coupon->product_ids ),
                                    'compare' => '=',
                                ),
                                array(
                                    'key'     => 'customer_email',
                                    'value'   => implode( ',', $coupon->customer_email ),
                                    'compare' => '='
                                )
                            )
                        )
                    );

                    $query = new WP_Query( $args );

                    if ( $query->have_posts() ) {

                        while ( $query->have_posts() ) {

                            $query->the_post();

                            wp_trash_post( $query->post->ID );

                            WC()->cart->remove_coupon( $query->post->post_title );

                        }

                    }

                    wp_reset_query();
                    wp_reset_postdata();

                }

            }

        }

        /**
         * YITH FRAMEWORK
         */

        /**
         * Load plugin framework
         *
         * @since   1.0.0
         * @return  void
         * @author  Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function plugin_fw_loader() {
            if ( !defined( 'YIT_CORE_PLUGIN' ) ) {
                global $plugin_fw_data;
                if ( !empty( $plugin_fw_data ) ) {
                    $plugin_fw_file = array_shift( $plugin_fw_data );
                    require_once( $plugin_fw_file );
                }
            }
        }

        /**
         * Premium Tab Template
         *
         * Load the premium tab template on admin page
         *
         * @since   1.0.0
         * @return  void
         * @author  Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function premium_tab() {
            $premium_tab_template = YWSFD_TEMPLATE_PATH . '/admin/' . $this->_premium;
            if ( file_exists( $premium_tab_template ) ) {
                include_once( $premium_tab_template );
            }
        }

        /**
         * Get the premium landing uri
         *
         * @since   1.0.0
         * @return  string The premium landing link
         * @author  Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function get_premium_landing_uri() {
            return defined( 'YITH_REFER_ID' ) ? $this->_premium_landing . '?refer_id=' . YITH_REFER_ID : $this->_premium_landing;
        }

        /**
         * Action Links
         *
         * add the action links to plugin admin page
         * @since   1.0.0
         *
         * @param   $links | links plugin array
         *
         * @return  mixed
         * @author  Andrea Grillo <andrea.grillo@yithemes.com>
         * @use     plugin_action_links_{$plugin_file_name}
         */
        public function action_links( $links ) {

            $links[] = '<a href="' . admin_url( "admin.php?page={$this->_panel_page}" ) . '">' . __( 'Settings', 'yith-woocommerce-share-for-discounts' ) . '</a>';

            if ( defined( 'YWSFD_FREE_INIT' ) ) {
                $links[] = '<a href="' . $this->get_premium_landing_uri() . '" target="_blank">' . __( 'Premium Version', 'yith-woocommerce-share-for-discounts' ) . '</a>';
            }

            return $links;
        }

        /**
         * Plugin row meta
         *
         * add the action links to plugin admin page
         *
         * @since   1.0.0
         *
         * @param   $plugin_meta
         * @param   $plugin_file
         * @param   $plugin_data
         * @param   $status
         *
         * @return  Array
         * @author  Andrea Grillo <andrea.grillo@yithemes.com>
         * @use     plugin_row_meta
         */
        public function plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status ) {
            if ( ( defined( 'YWSFD_INIT' ) && ( YWSFD_INIT == $plugin_file ) ) ||
                ( defined( 'YWSFD_FREE_INIT' ) && ( YWSFD_FREE_INIT == $plugin_file ) )
            ) {

                $plugin_meta[] = '<a href="' . $this->_official_documentation . '" target="_blank">' . __( 'Plugin Documentation', 'yith-woocommerce-share-for-discounts' ) . '</a>';
            }

            return $plugin_meta;
        }

    }

}

if ( !function_exists( 'check_coupon_ajax' ) ) {

    /**
     * Check if the coupon for current product needs to be added after adding product to cart (AJAX)
     *
     * @since   1.0.0
     *
     * @param   $product_id
     *
     * @return  void
     * @author  Alberto Ruggiero
     */
    function check_coupon_ajax( $product_id ) {

        if ( get_option( 'ywsfd_enable_plugin' ) == 'yes' ) {

            YITH_WSFD()->check_coupon( '', $product_id );

        }

    }

    add_action( 'woocommerce_ajax_added_to_cart', 'check_coupon_ajax' );

}

if ( !function_exists( 'ywsfd_coupon_message' ) ) {

    /**
     * Manage coupon errors
     *
     * @since   1.0.4
     *
     * @param   $err
     * @param   $err_code
     * @param   $obj
     *
     * @return  string
     * @author  Alberto Ruggiero
     */
    function ywsfd_coupon_message( $err, $err_code, $obj ) {

        if ( $err_code == 109 ) {
            $err = __( 'To use the coupon, you need to add the product to the cart', 'yith-woocommerce-share-for-discounts' );
        }
        return $err;

    }

    add_filter( 'woocommerce_coupon_error', 'ywsfd_coupon_message', 10, 3 );

}