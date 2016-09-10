<?php
/**
 * Plugin Name: YITH WooCommerce Customer History Premium
 * Plugin URI: http://yithemes.com/
 * Description: YITH WooCommerce Customer History Premium.
 * Version: 1.0.2
 * Author: YITHEMES
 * Author URI: http://yithemes.com/
 * Text Domain: yith-woocommerce-customer-history
 * Domain Path: /languages/
 *
 * @author  YITHEMES
 * @package YITH WooCommerce Customer History Premium
 * @version 1.0.2
 */

/*
    Copyright 2016  Your Inspiration Themes  (email : plugins@yithemes.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

if ( ! function_exists( 'yith_wcch_install_premium_woocommerce_admin_notice' ) ) {
    /**
     * Print an admin notice if woocommerce is deactivated
     *
     * @author Andrea Grillo <andrea.grillo@yithemes.com>
     * @since  1.0
     * @return void
     * @use admin_notices hooks
     */
    function yith_wcch_install_premium_woocommerce_admin_notice() { ?>
        <div class="error">
            <p><?php _e( 'YITH WooCommerce Advanced Product Options is enabled but not effective. In order to work it requires WooCommerce.', 'yith-woocommerce-customer-history' ); ?></p>
        </div>
    <?php
    }
}

if ( ! function_exists( 'yith_plugin_registration_hook' ) ) { require_once 'plugin-fw/yit-plugin-registration-hook.php'; }
register_activation_hook( __FILE__, 'yith_plugin_registration_hook' );

/*
 *  Free version deactivation if installed
 */

if ( ! function_exists( 'yit_deactive_free_version' ) ) { require_once 'plugin-fw/yit-deactive-plugin.php'; }
yit_deactive_free_version( 'YITH_WCCL_FREE_INIT', plugin_basename( __FILE__ ) );

/*
 *  Advanced Option Constant
 */

! defined( 'YITH_WCCH' )                        && define( 'YITH_WCCH', true );
! defined( 'YITH_WCCH_URL' )                    && define( 'YITH_WCCH_URL', plugin_dir_url( __FILE__ ) );
! defined( 'YITH_WCCH_DIR' )                    && define( 'YITH_WCCH_DIR', plugin_dir_path( __FILE__ ) );
! defined( 'YITH_WCCH_TEMPLATE_PATH' )          && define( 'YITH_WCCH_TEMPLATE_PATH', YITH_WCCH_DIR . 'templates' );
! defined( 'YITH_WCCH_TEMPLATE_FRONTEND_PATH' ) && define( 'YITH_WCCH_TEMPLATE_FRONTEND_PATH', YITH_WCCH_TEMPLATE_PATH . '/frontend/' );
! defined( 'YITH_WCCH_ASSETS_URL' )             && define( 'YITH_WCCH_ASSETS_URL', YITH_WCCH_URL . 'assets' );
! defined( 'YITH_WCCH_VERSION' )                && define( 'YITH_WCCH_VERSION', '1.0.0' );
! defined( 'YITH_WCCH_DB_VERSION' )             && define( 'YITH_WCCH_DB_VERSION', '1.0.0' );
! defined( 'YITH_WCCH_PREMIUM' )                && define( 'YITH_WCCH_PREMIUM', true );
! defined( 'YITH_WCCH_FILE' )                   && define( 'YITH_WCCH_FILE', __FILE__ );
! defined( 'YITH_WCCH_SLUG' )                   && define( 'YITH_WCCH_SLUG', 'yith-woocommerce-customer-history' );
! defined( 'YITH_WCCH_SECRET_KEY' )             && define( 'YITH_WCCH_SECRET_KEY', 'WUf9PnJiJ2noTyDf6Gfo' );
! defined( 'YITH_WCCH_INIT' )                   && define( 'YITH_WCCH_INIT', plugin_basename( __FILE__ ) );
! defined( 'YITH_WCCH_WPML_CONTEXT' )           && define( 'YITH_WCCH_WPML_CONTEXT', 'YITH WooCommerce Custome History' );

/*
 *  Plugin Framework Version Check
 */

if ( ! function_exists( 'yit_maybe_plugin_fw_loader' ) && file_exists( YITH_WCCH_DIR . 'plugin-fw/init.php' ) ) { require_once( YITH_WCCH_DIR . 'plugin-fw/init.php' ); }
yit_maybe_plugin_fw_loader( YITH_WCCH_DIR );

if ( ! function_exists( 'YITH_WCCH' ) ) {
    /**
     * Unique access to instance the class
     *
     * @return YITH_WCCH
     * @since 1.0.0
     */
    function YITH_WCCH() {
        // Load required classes and functions
        require_once( YITH_WCCH_DIR . 'includes/classes/yith-wcch.php' );
        return YITH_WCCH::instance();
    }
}

/**
 * Require core files
 *
 * @author Andrea Frascaspata <andrea.frascaspata@yithemes.com>
 * @since  1.0
 * @return void
 * @use Load plugin core
 */
function yith_wcch_premium_init() {

    load_plugin_textdomain( YITH_WCCH_SLUG, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    YITH_WCCH();

}
add_action( 'yith_wcch_premium_init', 'yith_wcch_premium_init' );

function yith_wcch_premium_install() {

    require_once( 'includes/classes/yith-wcch-email.php' );
    require_once( 'includes/classes/yith-wcch-session.php' );
    require_once( 'includes/functions/yith-ch-get-customer-total-spent.php' );

    if ( ! function_exists( 'WC' ) ) { add_action( 'admin_notices', 'yith_wcch_install_premium_woocommerce_admin_notice' ); }
    else { do_action( 'yith_wcch_premium_init' ); }

}
add_action( 'plugins_loaded', 'yith_wcch_premium_install', 12 );
