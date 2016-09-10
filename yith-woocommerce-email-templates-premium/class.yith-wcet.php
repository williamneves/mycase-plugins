<?php
/**
 * Main class
 *
 * @author  Yithemes
 * @package YITH WooCommerce Email Templates
 * @version 1.0.0
 */


if ( !defined( 'YITH_WCET' ) ) {
    exit;
} // Exit if accessed directly

if ( !class_exists( 'YITH_WCET' ) ) {
    /**
     * YITH WooCommerce Email Templates
     *
     * @since 1.0.0
     */
    class YITH_WCET {

        /**
         * Single instance of the class
         *
         * @var \YITH_WCET
         * @since 1.0.0
         */
        protected static $instance;

        /**
         * Plugin version
         *
         * @var string
         * @since 1.0.0
         */
        public $version = YITH_WCET_VERSION;

        /**
         * Plugin object
         *
         * @var string
         * @since 1.0.0
         */
        public $obj = null;

        /**
         * Returns single instance of the class
         *
         * @return \YITH_WCET
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
         * @return mixed| YITH_WCET_Admin
         * @since 1.0.0
         */
        public function __construct() {

            // Load Plugin Framework
            add_action( 'plugins_loaded', array( $this, 'plugin_fw_loader' ), 15 );

            YITH_WCET_WC_Compatibility();
            YITH_WCET_Email_Template_Helper();

            if ( is_admin() ) {
                YITH_WCET_Admin();
            }

        }


        /**
         * Load Plugin Framework
         *
         * @since  1.0
         * @access public
         * @return void
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
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
    }
}

/**
 * Unique access to instance of YITH_WCET class
 *
 * @return \YITH_WCET
 * @since 1.0.0
 */
function YITH_WCET() {
    return YITH_WCET::get_instance();
}

?>