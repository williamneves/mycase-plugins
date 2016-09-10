<?php
if ( !defined( 'ABSPATH' ) || ! defined( 'YITH_YWRAC_VERSION' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Implements helper functions for YITH WooCommerce Recover Abandoned Cart
 *
 * @package YITH WooCommerce Recover Abandoned Cart
 * @since   1.0.0
 * @author  Yithemes
 */

global $yith_ywrac_db_version;

$yith_ywrac_db_version = '1.0.0';

if ( !function_exists( 'yith_ywrac_db_install' ) ) {
    /**
     * Install the table yith_ywrac_email_log
     *
     * @return void
     * @since 1.0.0
     */
    function yith_ywrac_db_install() {
        global $wpdb;
        global $yith_ywrac_db_version;

        $installed_ver = get_option( "yith_ywrac_db_version" );

        if ( $installed_ver != $yith_ywrac_db_version ) {

            $table_name = $wpdb->prefix . 'yith_ywrac_email_log';

            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table_name (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`email_id` varchar(255) NOT NULL,
		`email_template_id` int(11) NOT NULL,
		`ywrac_cart_id` int(11) NOT NULL,
		`date_send` datetime NOT NULL,
		PRIMARY KEY (id)
		) $charset_collate;";

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );

            add_option( 'yith_ywrac_db_version', $yith_ywrac_db_version );
        }
    }
}



if ( !function_exists( 'yith_ywrac_update_db_check' ) ) {
    /**
     * check if the function yith_ywrac_db_install must be installed or updated
     *
     * @return void
     * @since 1.0.0
     */
    function yith_ywrac_update_db_check() {
        global $yith_ywrac_db_version;

        if ( get_site_option( 'yith_ywrac_db_version' ) != $yith_ywrac_db_version ) {
            yith_ywrac_db_install();
        }
    }
}


if ( !function_exists( 'yith_ywrac_locate_template' ) ) {
    /**
     * Locate the templates and return the path of the file found
     *
     * @param string $path
     * @param array  $var
     *
     * @return void
     * @since 1.0.0
     */
    function yith_ywrac_locate_template( $path, $var = NULL ) {
        global $woocommerce;

        if ( function_exists( 'WC' ) ) {
            $woocommerce_base = WC()->template_path();
        }
        elseif ( defined( 'WC_TEMPLATE_PATH' ) ) {
            $woocommerce_base = WC_TEMPLATE_PATH;
        }
        else {
            $woocommerce_base = $woocommerce->plugin_path() . '/templates/';
        }

        $template_woocommerce_path = $woocommerce_base . $path;
        $template_path             = '/' . $path;
        $plugin_path               = YITH_YWRAC_DIR . 'templates/' . $path;

        $located = locate_template( array(
            $template_woocommerce_path, // Search in <theme>/woocommerce/
            $template_path,             // Search in <theme>/
            $plugin_path                // Search in <plugin>/templates/
        ) );

        if ( !$located && file_exists( $plugin_path ) ) {
            return apply_filters( 'yith_ywrac_locate_template', $plugin_path, $path );
        }

        return apply_filters( 'yith_ywrac_locate_template', $located, $path );
    }
}


if ( !function_exists( 'yith_ywrac_get_excerpt' ) ) {
    /**
     * Return the excerpt of template email
     *
     * @return void
     * @since 1.0.0
     */
    function yith_ywrac_get_excerpt( $post_id ) {
        $post         = get_post( $post_id );
        $excerpt      = ( $post->post_excerpt != '' ) ? $post->post_excerpt : $post->post_content;
        $num_of_words = apply_filters( 'yith_ywrac_get_excerpt_num_words', 10 );
        return wp_trim_words( $excerpt, $num_of_words );
    }
}


if ( !function_exists( 'yith_ywrac_get_roles' ) ) {
    /**
     * Return the roles of users
     *
     * @return void
     * @since 1.0.0
     */
    function yith_ywrac_get_roles(){
        global $wp_roles;
        return array_merge( array( 'all' => __( 'All', 'yith-woocommerce-recover-abandoned-cart' ) ), $wp_roles->get_names() );
    }
}


if ( !function_exists( 'ywrac_get_cutoff' ) ) {
    /**
     * calculate the cutoff time
     *
     * @return int
     * @since 1.0.0
     */

    function ywrac_get_cutoff( $qty, $type ){
        $cutoff = 0;
       if( $type == 'hours' ){
           $cutoff = 60*60*$qty;
       }elseif( $type == 'days' ){
           $cutoff = 24*60*60*$qty;
       }elseif( $type == 'minutes' ){
           $cutoff = 60*$qty;
       }

        return $cutoff;
    }
}

if( !function_exists( 'ywrac_is_customer_unsubscribed' ) ) {
    /**
     * Check if a customer is currently unsubscribed from email
     *
     * @param int | string
     * @since 1.0.4
     * @return bool
     * @author Francesco Licandro
     */
    function ywrac_is_customer_unsubscribed( $user = null ) {

        $blacklist = get_option( 'ywrac_mail_blacklist', '' );
        $blacklist = maybe_unserialize( $blacklist );
        ! $blacklist && $blacklist = array();

        if( is_null( $user ) ){
            $customer_id = get_current_user_id();
        }
        elseif( is_email( $user ) ) {
            $customer = get_user_by( 'email', $user );
            if( $customer ) {
                $customer_id = $customer->ID;
            }
            else {
                return in_array( $user, $blacklist );
            }
        }
        else {
            $customer_id = intval( $user );
        }

        return get_user_meta( $customer_id, '_ywrac_is_unsubscribed', true ) == '1';
    }
}


