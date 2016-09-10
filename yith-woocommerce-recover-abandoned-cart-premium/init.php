<?php
/*
Plugin Name: YITH WooCommerce Recover Abandoned Cart Premium
Description: YITH WooCommerce Recover Abandoned Cart helps you manage easily and efficiently all the abandoned carts of your customers.
Version: 1.0.7
Author: YITHEMES
Author URI: http://yithemes.com/
Text Domain: yith-woocommerce-recover-abandoned-cart
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

/*
 * @package YITH WooCommerce Recover Abandoned Cart Premium
 * @since   1.0.0
 * @author  YITHEMES
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

if ( ! function_exists( 'is_plugin_active' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}



// Free version deactivation if installed __________________

if( ! function_exists( 'yit_deactive_free_version' ) ) {
    require_once 'plugin-fw/yit-deactive-plugin.php';
}
yit_deactive_free_version( 'YITH_YWRAC_FREE_INIT', plugin_basename( __FILE__ ) );


if ( ! defined( 'YITH_YWRAC_DIR' ) ) {
    define( 'YITH_YWRAC_DIR', plugin_dir_path( __FILE__ ) );
}

/* Plugin Framework Version Check */
if( ! function_exists( 'yit_maybe_plugin_fw_loader' ) && file_exists( YITH_YWRAC_DIR . 'plugin-fw/init.php' ) ) {
    require_once( YITH_YWRAC_DIR . 'plugin-fw/init.php' );
}
yit_maybe_plugin_fw_loader( YITH_YWRAC_DIR  );



// Define constants ________________________________________
if ( defined( 'YITH_YWRAC_VERSION' ) ) {
    return;
}else{
    define( 'YITH_YWRAC_VERSION', '1.0.7' );
}

if ( ! defined( 'YITH_YWRAC_PREMIUM' ) ) {
    define( 'YITH_YWRAC_PREMIUM', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'YITH_YWRAC_INIT' ) ) {
    define( 'YITH_YWRAC_INIT', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'YITH_YWRAC_FILE' ) ) {
    define( 'YITH_YWRAC_FILE', __FILE__ );
}


if ( ! defined( 'YITH_YWRAC_URL' ) ) {
    define( 'YITH_YWRAC_URL', plugins_url( '/', __FILE__ ) );
}

if ( ! defined( 'YITH_YWRAC_ASSETS_URL' ) ) {
    define( 'YITH_YWRAC_ASSETS_URL', YITH_YWRAC_URL . 'assets' );
}

if ( ! defined( 'YITH_YWRAC_TEMPLATE_PATH' ) ) {
    define( 'YITH_YWRAC_TEMPLATE_PATH', YITH_YWRAC_DIR . 'templates' );
}

if ( ! defined( 'YITH_YWRAC_INC' ) ) {
    define( 'YITH_YWRAC_INC', YITH_YWRAC_DIR . '/includes/' );
}

if ( ! defined( 'YITH_YWRAC_SUFFIX' ) ) {
    $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
    define( 'YITH_YWRAC_SUFFIX', $suffix );
}

if ( ! defined( 'YITH_YWRAC_SLUG' ) ) {
    define( 'YITH_YWRAC_SLUG', 'yith-woocommerce-recover-abandoned-cart' );
}

if ( ! defined( 'YITH_YWRAC_SECRET_KEY' ) ) {
    define( 'YITH_YWRAC_SECRET_KEY', 'EMqDs75CCAYPHZVZcFna' );
}

function yith_ywrac_install_woocommerce_admin_notice() {
    ?>
    <div class="error">
        <p><?php _e( 'YITH WooCommerce Recover Abandoned Cart is enabled but not effective. It requires WooCommerce in order to work.', 'yith-woocommerce-recover-abandoned-cart' ); ?></p>
    </div>
    <?php
}

if ( ! function_exists( 'yith_ywrac_install' ) ) {
    function yith_ywrac_install() {

        if ( !function_exists( 'WC' ) ) {
            add_action( 'admin_notices', 'yith_ywrac_install_woocommerce_admin_notice' );
        } else {
            do_action( 'yith_ywrac_init' );
        }

        // check for update table
        if( function_exists( 'yith_ywrac_update_db_check' ) ) {
            yith_ywrac_update_db_check();
        }
    }

    add_action( 'plugins_loaded', 'yith_ywrac_install', 11 );
}


function yith_ywrac_premium_constructor() {

    require_once( YITH_YWRAC_INC . 'functions.yith-wc-abandoned-cart.php' );
    
    // Woocommerce installation check _________________________
    if ( !function_exists( 'WC' ) ) {
        add_action( 'admin_notices', 'yith_ywrac_install_woocommerce_admin_notice' );
        return;
    }

    // Load YWSL text domain ___________________________________
    load_plugin_textdomain( 'yith-woocommerce-recover-abandoned-cart', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );


    if( ! class_exists( 'WP_List_Table' ) ) {
        require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
    }

    //require_once( YITH_YWRAC_INC . 'emails/class.yith-wc-abandoned-cart-email.php' );

    require_once( YITH_YWRAC_INC . 'class-yith-wc-abandoned-cart.php' );
    require_once( YITH_YWRAC_INC . 'class-yith-wc-abandoned-cart-email.php' );
    require_once( YITH_YWRAC_INC . 'class-yith-wc-abandoned-cart-helper.php' );
    require_once( YITH_YWRAC_INC . 'class-yith-wc-abandoned-cart-admin.php' );
    require_once( YITH_YWRAC_INC . 'admin/class-yith-wc-abandoned-cart-metaboxes.php' );
    require_once( YITH_YWRAC_INC . 'admin/class-wp-carts-list-table.php' );
    require_once( YITH_YWRAC_INC . 'admin/class-wp-emails-list-table.php' );
    require_once( YITH_YWRAC_INC . 'admin/class-wp-email-log-list-table.php' );
    require_once( YITH_YWRAC_INC . 'admin/class-wp-recovered-list-table.php' );

    YITH_WC_Recover_Abandoned_Cart();
    YITH_WC_Recover_Abandoned_Cart_Email();
    YITH_WC_RAC_Metaboxes();

    if ( is_admin() ) {
        YITH_WC_Recover_Abandoned_Cart_Admin();
    }

    YITH_WC_Recover_Abandoned_Cart_Helper();

    add_action( 'ywrac_cron', array( YITH_WC_Recover_Abandoned_Cart_Helper(), 'clear_coupons'));
    add_action( 'ywrac_cron', array( YITH_WC_Recover_Abandoned_Cart_Email(), 'email_cron' ) );

}
add_action( 'yith_ywrac_init', 'yith_ywrac_premium_constructor' );