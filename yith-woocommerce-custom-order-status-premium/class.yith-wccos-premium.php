<?php
if ( !defined( 'ABSPATH' ) || !defined( 'YITH_WCCOS_PREMIUM' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Implements features of FREE version of YITH WooCommerce Custom Order Status
 *
 * @class   YITH_WCCOS_Premium
 * @package YITH WooCommerce Custom Order Status
 * @since   1.0.0
 * @author  Yithemes
 */

if ( !class_exists( 'YITH_WCCOS_Premium' ) ) {
    /**
     * YITH WooCommerce Custom Order Status
     *
     * @since 1.0.0
     */
    class YITH_WCCOS_Premium extends YITH_WCCOS {

        /**
         * Returns single instance of the class
         *
         * @return \YITH_WCCOS
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
         * @return mixed| YITH_WCCOS_Admin | YITH_WCCOS_Frontend
         * @since 1.0.0
         */
        public function __construct() {

            //parent::__construct();
            // Load Plugin Framework

            add_action( 'plugins_loaded', array( $this, 'plugin_fw_loader' ), 15 );

            YITH_WCCOS_Admin_Premium();
            YITH_WCCOS_Frontend_Premium();

            add_filter( 'woocommerce_email_classes', array( $this, 'add_email_classes' ) );
        }

        /**
         * add email classes to woocommerce
         *
         * @param array $emails
         *
         * @return array
         *
         * @access public
         * @since  1.0.0
         * @author Leanza Francesco <leanzafrancesco@gmail.com>
         */
        public function add_email_classes( $emails ) {
            $emails[ 'YITH_WCCOS_Email' ] = include( YITH_WCCOS_DIR . '/includes/class.yith-wccos-email.php' );

            return $emails;
        }
    }
}

/**
 * Unique access to instance of YITH_WCCOS_Premium class
 *
 * @return \YITH_WCCOS_Premium
 * @since 1.0.0
 */
function YITH_WCCOS_Premium() {
    return YITH_WCCOS_Premium::get_instance();
}

?>