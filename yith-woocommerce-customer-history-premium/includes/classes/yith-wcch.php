<?php
/**
 * Main class
 *
 * @author  Your Inspiration Themes
 * @package YITH WooCommerce Ajax Navigation
 * @version 1.3.2
 */

if ( ! defined( 'YITH_WCCH' ) ) {
    exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCCH' ) ) {
    /**
     * YITH WooCommerce Ajax Navigation
     *
     * @since 1.0.0
     */
    class YITH_WCCH {
        /**
         * Plugin version
         *
         * @var string
         * @since 1.0.0
         */
        public $version;

        /**
         * Frontend object
         *
         * @var string
         * @since 1.0.0
         */
        public $frontend = null;


        /**
         * Admin object
         *
         * @var string
         * @since 1.0.0
         */
        public $admin = null;


        /**
         * Main instance
         *
         * @var string
         * @since 1.4.0
         */
        protected static $_instance = null;


        /**
         * Constructor
         *
         * @return mixed|YITH_WCCH_Admin|YITH_WCCH_Frontend
         * @since 1.0.0
         */
        public function __construct() {

            $this->version = YITH_WCCH_VERSION;

            /*
             *  Load Plugin Framework
             */

            add_action( 'after_setup_theme', array( $this, 'plugin_fw_loader' ) , 1 );

            $this->create_tables();
            $this->required();
            $this->init();

            /*
             *  Register plugin to licence/update system
             */

            add_action( 'wp_loaded', array( $this, 'register_plugin_for_activation' ), 99 );
            add_action( 'admin_init', array( $this, 'register_plugin_for_updates' ) );

        }

        /**
		 * Load plugin framework
		 *
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @since  1.0
		 * @return void
		 */
		public function plugin_fw_loader() {
			if ( ! defined( 'YIT_CORE_PLUGIN' ) ) {
                global $plugin_fw_data;
                if( ! empty( $plugin_fw_data ) ) {
                    $plugin_fw_file = array_shift( $plugin_fw_data );
                    require_once( $plugin_fw_file );
                }
            }
		}

        /**
		 * Main plugin Instance
		 *
		 * @return YITH_WCCH Main instance
		 * @author Andrea Frascaspata <andrea.frascaspata@yithemes.com>
		 */
		public static function instance() {

            if( is_null( YITH_WCCH::$_instance ) ){ YITH_WCCH::$_instance = new YITH_WCCH(); }
            return YITH_WCCH::$_instance;

		}

        public static function create_tables() {

            /*
             *  If exists yith_wcch_db_version option return null
             */

            if ( apply_filters( 'yith_wcch_db_version', get_option( 'yith_wcch_db_version' ) ) ) { return; }

            // YITH_WCCH_Group::create_tables();
            // YITH_WCCH_Type::create_tables();

            add_option( 'yith_wcch_db_version', YITH_WCCH_DB_VERSION );

        }

        /**
         * Load required files
         *
         * @since 1.4
         * @return void
         * @author Andrea Frascaspata <andrea.frascaspata@yithemes.com>
         */
        public function required() {

            $required = apply_filters( 'yith_wcch_required_files', array( 'includes/classes/yith-wcch-admin.php', 'includes/classes/yith-wcch-frontend.php' ) );
            foreach( $required as $file ){ file_exists( YITH_WCCH_DIR . $file ) && require_once( YITH_WCCH_DIR . $file ); }

        }

        public function init() {

            YITH_WCCH_Session::create_tables();
            YITH_WCCH_Email::create_tables();
            if ( is_admin() ) { $this->admin = new YITH_WCCH_Admin( $this->version ); }
            else {

                if ( ! current_user_can( 'manage_options' ) || get_option('yith-wcch-default_save_admin_session') ) {

                    if ( isset( $_GET['s'] ) ) {
                        $url = 'ACTION::search::' . $_GET['s'];
                        YITH_WCCH_Session::insert( is_user_logged_in() ? get_current_user_id() : 0, $url );
                    }

                    // Insert session URL
                    function yith_wcch_session_insert() {
                        global $wp;
                        $url = add_query_arg( array(), $wp->request );
                        YITH_WCCH_Session::insert( is_user_logged_in() ? get_current_user_id() : 0, $url );
                    }
                    add_action( 'wp_footer', 'yith_wcch_session_insert' );

                    // Insert add_to_cart action
                    function action_woocommerce_add_to_cart( $array, $product_id, $quantity ) {
                        $url = 'ACTION::add_to_cart::' . $product_id . '::' . $quantity;
                        YITH_WCCH_Session::insert( is_user_logged_in() ? get_current_user_id() : 0, $url );
                    };
                    add_action( 'woocommerce_add_to_cart', 'action_woocommerce_add_to_cart', 10, 3 );

                    // Insert new_order action
                    function action_woocommerce_new_order( $order_id ) {
                        $url = 'ACTION::new_order::' . $order_id;
                        YITH_WCCH_Session::insert( is_user_logged_in() ? get_current_user_id() : 0, $url );
                    }
                    add_action( 'woocommerce_new_order', 'action_woocommerce_new_order', 10, 1 );

                }

                $this->frontend = new YITH_WCCH_Frontend( $this->version );

            }

        }

        /**
         * Register plugins for activation tab
         *
         * @return void
         * @since    2.0.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function register_plugin_for_activation() {
            if( ! class_exists( 'YIT_Plugin_Licence' ) ) {
                require_once YITH_WCCH_DIR . '/plugin-fw/licence/lib/yit-licence.php';
                require_once YITH_WCCH_DIR . '/plugin-fw/licence/lib/yit-plugin-licence.php';
            }
            YIT_Plugin_Licence()->register( YITH_WCCH_INIT, YITH_WCCH_SECRET_KEY, YITH_WCCH_SLUG );
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
                require_once( YITH_WCCH_DIR . '/plugin-fw/lib/yit-upgrade.php' );
            }
            YIT_Upgrade()->register( YITH_WCCH_SLUG, YITH_WCCH_INIT );
        } 

    }
}