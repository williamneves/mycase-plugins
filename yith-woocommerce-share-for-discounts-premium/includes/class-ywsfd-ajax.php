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
    exit; // Exit if accessed directly
}

if ( !class_exists( 'YWSFD_Ajax' ) ) {

    /**
     * Implements AJAX for YWSFD plugin
     *
     * @class   YWSFD_Ajax
     * @package Yithemes
     * @since   1.0.0
     * @author  Your Inspiration Themes
     *
     */
    class YWSFD_Ajax {

        /**
         * Single instance of the class
         *
         * @var \YWSFD_Ajax
         * @since 1.0.0
         */
        protected static $instance;

        /**
         * Returns single instance of the class
         *
         * @return \YWSFD_Ajax
         * @since 1.0.0
         */
        public static function get_instance() {

            if ( is_null( self::$instance ) ) {

                self::$instance = new self( $_REQUEST );

            }

            return self::$instance;
        }

        /**
         * Constructor
         *
         * @since   1.0.0
         * @return  mixed
         * @author  Alberto Ruggiero
         */
        public function __construct() {

            add_action( 'wp_ajax_ywsfd_get_coupon', array( $this, 'get_coupon' ) );
            add_action( 'wp_ajax_nopriv_ywsfd_get_coupon', array( $this, 'get_coupon' ) );

        }

        /**
         * Get a coupon
         *
         * @since   1.0.0
         * @return  void
         * @author  Alberto Ruggiero
         */
        public function get_coupon() {

            try {
                $response  = array();
                $user_data = YITH_WSFD()->get_user_data();

                $coupon = YITH_WSFD()->create_coupon( $user_data, $_POST['post_id'] );

                WC()->cart->add_discount( $coupon );

                $response['status']   = 'success';
                $response['redirect'] = get_permalink( $_POST['post_id'] );

                if ( is_ajax() ) {
                    echo '<!--WC_START-->' . json_encode( $response ) . '<!--WC_END-->';
                    exit;
                }
                else {
                    wp_redirect( $response['redirect'] );
                    exit;
                }

            } catch ( Exception $e ) {

                if ( !empty( $e ) ) {
                    wc_add_notice( $e->getMessage(), 'error' );
                }

            }

            if ( is_ajax() ) {

                ob_start();
                wc_print_notices();
                $messages = ob_get_clean();

                echo '<!--WC_START-->' . json_encode(
                        array(
                            'result'   => 'failure',
                            'messages' => isset( $messages ) ? $messages : ''
                        )
                    ) . '<!--WC_END-->';

                exit;

            }

        }

    }

    /**
     * Unique access to instance of YWSFD_Ajax class
     *
     * @return \YWSFD_Ajax
     */
    function YWSFD_Ajax() {

        return YWSFD_Ajax::get_instance();

    }

}