<?php
/*
Plugin Name: YITH WooCommerce Recover Abandoned Cart
Description: YITH WooCommerce Recover Abandoned Cart
Version: 1.0.3
Author: YITHEMES
Author URI: http://yithemes.com/
Text Domain: ywrac
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

/*
 * @package YITH WooCommerce Recover Abandoned Cart
 * @since   1.0.0
 * @author  YITHEMES
 */



if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

if ( ! function_exists( 'is_plugin_active' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

// This version can't be activate if premium version is active  ________________________________________
if ( defined( 'YITH_YWRAC_PREMIUM' ) ) {
    function yith_ywrac_install_free_admin_notice() {
        ?>
        <div class="error">
            <p><?php _e( 'You can\'t activate the free version of YITH WooCommerce Recover Abandoned Cart while you are using the premium one.', 'ywrac' ); ?></p>
        </div>
    <?php
    }

    add_action( 'admin_notices', 'yith_ywrac_install_free_admin_notice' );

    deactivate_plugins( plugin_basename( __FILE__ ) );
    return;
}


// Registration hook  ________________________________________
if ( !function_exists( 'yith_plugin_registration_hook' ) ) {
    require_once 'plugin-fw/yit-plugin-registration-hook.php';
}
register_activation_hook( __FILE__, 'yith_plugin_registration_hook' );

if ( !function_exists( 'yith_ywrac_install_woocommerce_admin_notice' ) ) {
    function yith_ywrac_install_woocommerce_admin_notice() {
        ?>
        <div class="error">
            <p><?php _e( 'YITH WooCommerce Recover Abandoned Cart is enabled but not effective. It requires WooCommerce in order to work.', 'ywrac' ); ?></p>
        </div>
    <?php
    }
}

// Define constants ________________________________________
if ( defined( 'YITH_YWRAC_VERSION' ) ) {
    return;
}else{
    define( 'YITH_YWRAC_VERSION', '1.0.3' );
}

if ( ! defined( 'YITH_YWRAC_FREE_INIT' ) ) {
    define( 'YITH_YWRAC_FREE_INIT', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'YITH_YWRAC_INIT' ) ) {
    define( 'YITH_YWRAC_INIT', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'YITH_YWRAC_FILE' ) ) {
    define( 'YITH_YWRAC_FILE', __FILE__ );
}

if ( ! defined( 'YITH_YWRAC_DIR' ) ) {
    define( 'YITH_YWRAC_DIR', plugin_dir_path( __FILE__ ) );
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


if ( ! function_exists( 'yith_ywrac_install' ) ) {
    function yith_ywrac_install() {

        if ( !function_exists( 'WC' ) ) {
            add_action( 'admin_notices', 'yith_ywrac_install_woocommerce_admin_notice' );
        } else {
            do_action( 'yith_ywrac_init' );
        }
    }

    add_action( 'plugins_loaded', 'yith_ywrac_install', 11 );
}


function yith_ywrac_constructor() {

    // Woocommerce installation check _________________________
    if ( !function_exists( 'WC' ) ) {
        function yith_ywrac_install_woocommerce_admin_notice() {
            ?>
            <div class="error">
                <p><?php _e( 'YITH WooCommerce Recover Abandoned Cart is enabled but not effective. It requires WooCommerce in order to work.', 'ywrac' ); ?></p>
            </div>
        <?php
        }

        add_action( 'admin_notices', 'yith_ywrac_install_woocommerce_admin_notice' );
        return;
    }

    // Load YWSL text domain ___________________________________
    load_plugin_textdomain( 'ywrac', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );


    if( ! class_exists( 'WP_List_Table' ) ) {
        require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
    }

    require_once( YITH_YWRAC_INC . 'functions.yith-wc-abandoned-cart.php' );
    require_once( YITH_YWRAC_INC . 'admin/class-wp-carts-list-table.php' );

    require_once( YITH_YWRAC_INC . 'class-yith-wc-abandoned-cart.php' );
    require_once( YITH_YWRAC_INC . 'class-yith-wc-abandoned-cart-admin.php' );
    require_once( YITH_YWRAC_INC . 'admin/class-yith-wc-abandoned-cart-metaboxes.php' );

    YITH_WC_Recover_Abandoned_Cart();
    YITH_WC_RAC_Metaboxes();

    if ( is_admin() ) {
        YITH_WC_Recover_Abandoned_Cart_Admin();
    }
    
}
add_action( 'yith_ywrac_init', 'yith_ywrac_constructor' );