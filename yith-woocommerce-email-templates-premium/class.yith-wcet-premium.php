<?php
if ( !defined( 'ABSPATH' ) || !defined( 'YITH_WCET_PREMIUM' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Implements features of FREE version of YITH WooCommerce Email Templates
 *
 * @class   YITH_WCET_Premium
 * @package YITH WooCommerce Email Templates
 * @since   1.0.0
 * @author  Yithemes
 */

if ( !class_exists( 'YITH_WCET_Premium' ) ) {
    /**
     * YITH WooCommerce Email Templates
     *
     * @since 1.0.0
     */
    class YITH_WCET_Premium extends YITH_WCET {

        /**
         * Returns single instance of the class
         *
         * @return \YITH_WCET_Premium
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
         * @since 1.0.0
         */
        public function __construct() {
           parent::__construct();
        }
    }
}

/**
 * Unique access to instance of YITH_WCET_Premium class
 *
 * @return \YITH_WCET_Premium
 * @since 1.0.0
 */
function YITH_WCET_Premium() {
    return YITH_WCET_Premium::get_instance();
}

?>