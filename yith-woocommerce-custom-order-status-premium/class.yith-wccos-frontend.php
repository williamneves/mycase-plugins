<?php
/**
 * Frontend class
 *
 * @author Yithemes
 * @package YITH WooCommerce Custom Order Status
 * @version 1.1.1
 */

if ( ! defined( 'YITH_WCCOS' ) ) { exit; } // Exit if accessed directly

if( ! class_exists( 'YITH_WCCOS_Frontend' ) ) {
    /**
     * Frontend class.
     * The class manage all the Frontend behaviors.
     *
     * @since 1.0.0
     * @author   Leanza Francesco <leanzafrancesco@gmail.com>
     */
    class YITH_WCCOS_Frontend {

        /**
         * Single instance of the class
         *
         * @var \YITH_WCQV_Frontend
         * @since 1.0.0
         */
        protected static $instance;

        /**
         * Plugin version
         *
         * @var string
         * @since 1.0.0
         */
        public $version = YITH_WCCOS_VERSION;


        public $this_is_product = NULL;

        public $templates = array();


        /**
         * Constructor
         *
         * @access public
         * @since 1.0.0
         */
        public function __construct() {
        }

        /**
         * Returns single instance of the class
         *
         * @return \YITH_WCQV_Frontend
         * @since 1.0.0
         */
        public static function get_instance(){
            if( is_null( self::$instance ) ){
                self::$instance = new self();
            }

            return self::$instance;
        }
    }
}
/**
 * Unique access to instance of YITH_WCCOS_Frontend class
 *
 * @return \YITH_WCCOS_Frontend
 * @since 1.0.0
 */
function YITH_WCCOS_Frontend(){
    return YITH_WCCOS_Frontend::get_instance();
}
?>
