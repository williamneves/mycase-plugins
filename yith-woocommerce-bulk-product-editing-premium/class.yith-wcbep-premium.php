<?php
/**
 * Main class
 *
 * @author  Yithemes
 * @package YITH WooCommerce Bulk Edit Products
 * @version 1.0.0
 */


if ( !defined( 'YITH_WCBEP' ) ) {
    exit;
} // Exit if accessed directly

if ( !class_exists( 'YITH_WCBEP_Premium' ) ) {
    /**
     * YITH WooCommerce Bulk Edit Products PREMIUM
     *
     * @since 1.0.0
     */
    class YITH_WCBEP_Premium extends YITH_WCBEP {

        /**
         * Returns single instance of the class
         *
         * @return \YITH_WCBEP
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
         * @return mixed| YITH_WCBEP_Admin
         * @since 1.0.0
         */
        public function __construct() {

            // Load Plugin Framework
            add_action( 'plugins_loaded', array( $this, 'plugin_fw_loader' ), 15 );

            // Class admin
            if ( is_admin() ) {
                YITH_WCBEP_Compatibility();

                YITH_WCBEP_Admin_Premium();
            }
        }


        /**
         * Load Plugin Framework
         *
         * @since  1.0
         * @access public
         * @return void
         */
        public function plugin_fw_loader() {

            if ( !defined( 'YIT' ) || !defined( 'YIT_CORE_PLUGIN' ) ) {
                require_once( 'plugin-fw/yit-plugin.php' );
            }

        }
    }
}

/**
 * Unique access to instance of YITH_WCBEP_Premium class
 *
 * @return \YITH_WCBEP_Premium
 * @since 1.0.0
 */
function YITH_WCBEP_Premium() {
    return YITH_WCBEP_Premium::get_instance();
}

?>