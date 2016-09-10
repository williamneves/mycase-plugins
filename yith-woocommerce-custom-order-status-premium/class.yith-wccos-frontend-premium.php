<?php
if ( !defined( 'ABSPATH' ) || !defined( 'YITH_WCCOS_PREMIUM' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Implements features of FREE version of YITH WooCommerce Custom Order Status
 *
 * @class   YITH_WCCOS_Frontend_Premium
 * @package YITH WooCommerce Custom Order Status
 * @since   1.0.0
 * @author  Yithemes
 */


if ( !class_exists( 'YITH_WCCOS_Frontend_Premium' ) ) {
    /**
     * Frontend class.
     * The class manage all the Frontend behaviors.
     *
     * @since 1.0.0
     */
    class YITH_WCCOS_Frontend_Premium extends YITH_WCCOS_Frontend {


        /**
         * Constructor
         *
         * @access public
         * @since  1.0.0
         */
        public function __construct() {
            if ( is_admin() )
                return;

            parent::__construct();

            add_filter( 'woocommerce_valid_order_statuses_for_cancel', array( $this, 'add_statuses_for_cancel' ) );

            add_filter( 'woocommerce_valid_order_statuses_for_payment', array( $this, 'add_statuses_for_pay' ) );

            add_filter( 'woocommerce_order_is_download_permitted', array( $this, 'woocommerce_order_is_download_permitted' ), 10, 2 );
        }

        /**
         * Order is download permitted
         *
         * Check if the order status has downloads permitted checked
         *
         * @access public
         * @since  1.0.0
         * @author Leanza Francesco <leanzafrancesco@gmail.com>
         */
        public function woocommerce_order_is_download_permitted( $val, $order ) {

            if ( get_option( 'woocommerce_downloads_grant_access_after_payment' ) == 'yes' && $order->has_status( 'processing' ) ) {
                return true;
            }

            $status_posts = get_posts( array(
                                           'posts_per_page' => -1,
                                           'post_type'      => 'yith-wccos-ostatus',
                                           'post_status'    => 'publish',
                                       ) );

            $new_statuses   = array();
            $completed_flag = 1;

            foreach ( $status_posts as $sp ) {
                $downloads_permitted = get_post_meta( $sp->ID, 'downloads-permitted', true );
                $slug                = get_post_meta( $sp->ID, 'slug', true );
                if ( $downloads_permitted ) {
                    if ( $slug != 'completed' ) {
                        $new_statuses[] = $slug;
                    }
                } else {
                    if ( $slug == 'completed' ) {
                        $completed_flag = 0;
                    }
                }
            }

            if ( $completed_flag ) {
                $new_statuses[] = 'completed';
            }


            if ( $order->has_status( $new_statuses ) ) {
                return true;
            }

            return false;
        }

        /**
         * Add Statuses for cancel
         *
         * Add the statuses in which the order can be cancelled by user
         *
         * @access public
         * @since  1.0.0
         * @author Leanza Francesco <leanzafrancesco@gmail.com>
         */
        public function add_statuses_for_cancel( $statuses ) {
            $status_posts = get_posts( array(
                                           'posts_per_page' => -1,
                                           'post_type'      => 'yith-wccos-ostatus',
                                           'post_status'    => 'publish',
                                       ) );

            $new_statuses            = array();
            $cancel_default_statuses = array();

            foreach ( (array) $statuses as $status ) {
                $cancel_default_statuses[ $status ] = 1;
            }

            foreach ( $status_posts as $sp ) {
                $can_cancel = get_post_meta( $sp->ID, 'can-cancel', true );
                $slug       = get_post_meta( $sp->ID, 'slug', true );
                if ( $can_cancel ) {
                    if ( !in_array( $slug, (array) $statuses ) ) {
                        $new_statuses[] = $slug;
                    }
                } else {
                    if ( in_array( $slug, (array) $statuses ) ) {
                        $cancel_default_statuses[ $slug ] = 0;
                    }
                }
            }
            foreach ( $cancel_default_statuses as $key => $value ) {
                if ( $value ) {
                    $new_statuses[] = $key;
                }
            }

            return $new_statuses;
        }


        /**
         * Add Statuses for pay
         *
         * Add the statuses in which the order can be payed by user
         *
         * @access public
         * @since  1.0.0
         * @author Leanza Francesco <leanzafrancesco@gmail.com>
         */
        public function add_statuses_for_pay( $statuses ) {
            $status_posts = get_posts( array(
                                           'posts_per_page' => -1,
                                           'post_type'      => 'yith-wccos-ostatus',
                                           'post_status'    => 'publish',
                                           'fields'         => 'ids',
                                       ) );

            $all_statuses        = $statuses;
            $statuses_to_disable = array();

            foreach ( $status_posts as $status_id ) {
                $can_pay = get_post_meta( $status_id, 'can-pay', true );
                $slug    = get_post_meta( $status_id, 'slug', true );

                $all_statuses[] = $slug;

                if ( !$can_pay ) {
                    $statuses_to_disable[] = $slug;
                }
            }

            $all_statuses = array_unique( $all_statuses );

            $new_statuses = array_diff( $all_statuses, $statuses_to_disable );

            return array_unique( $new_statuses );
        }

        /**
         * Returns single instance of the class
         *
         * @return \YITH_WCCOS_Frontend_Premium
         * @since 1.0.0
         */
        public static function get_instance() {
            if ( is_null( self::$instance ) ) {
                self::$instance = new self();
            }

            return self::$instance;
        }
    }
}
/**
 * Unique access to instance of YITH_WCCOS_Frontend_Premium class
 *
 * @return \YITH_WCCOS_Frontend_Premium
 * @since 1.0.0
 */
function YITH_WCCOS_Frontend_Premium() {
    return YITH_WCCOS_Frontend_Premium::get_instance();
}

?>
