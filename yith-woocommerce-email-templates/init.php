<?php
/**
* Plugin Name: YITH WooCommerce Email Templates
* Plugin URI: https://yithemes.com/themes/plugins/yith-woocommerce-email-templates/
* Description: YITH WooCommerce Email Templates allows you to create and manage custom email templates for Woocommerce.
* Version: 1.1.4
* Author: YITHEMES
* Author URI: http://yithemes.com/
* Text Domain: yith-woocommerce-email-templates
* Domain Path: /languages/
*
* @author yithemes
* @package YITH WooCommerce Email Templates
* @version 1.1.4
*/
/*  Copyright 2015  Your Inspiration Themes  (email : plugins@yithemes.com)

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

/* == COMMENT == */ 

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

if ( ! function_exists( 'is_plugin_active' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

function yith_wcet_install_woocommerce_admin_notice() {
    ?>
    <div class="error">
        <p><?php _e( 'YITH WooCommerce Email Templates is enabled but not effective. It requires WooCommerce in order to work.', 'yith-woocommerce-email-templates' ); ?></p>
    </div>
    <?php
}


function yith_wcet_install_free_admin_notice() {
    ?>
    <div class="error">
        <p><?php _e( 'You can\'t activate the free version of YITH WooCommerce Email Templates while you are using the premium one.', 'yith-woocommerce-email-templates' ); ?></p>
    </div>
    <?php
}

if ( ! function_exists( 'yith_plugin_registration_hook' ) ) {
    require_once 'plugin-fw/yit-plugin-registration-hook.php';
}
register_activation_hook( __FILE__, 'yith_plugin_registration_hook' );


if ( ! defined( 'YITH_WCET_VERSION' ) ){
    define( 'YITH_WCET_VERSION', '1.1.4' );
}

if ( ! defined( 'YITH_WCET_FREE_INIT' ) ) {
    define( 'YITH_WCET_FREE_INIT', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'YITH_WCET' ) ) {
    define( 'YITH_WCET', true );
}

if ( ! defined( 'YITH_WCET_FILE' ) ) {
    define( 'YITH_WCET_FILE', __FILE__ );
}

if ( ! defined( 'YITH_WCET_URL' ) ) {
    define( 'YITH_WCET_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'YITH_WCET_DIR' ) ) {
    define( 'YITH_WCET_DIR', plugin_dir_path( __FILE__ )  );
}

if ( ! defined( 'YITH_WCET_TEMPLATE_PATH' ) ) {
    define( 'YITH_WCET_TEMPLATE_PATH', YITH_WCET_DIR . 'templates' );
}

if ( ! defined( 'YITH_WCET_ASSETS_URL' ) ) {
    define( 'YITH_WCET_ASSETS_URL', YITH_WCET_URL . 'assets' );
}


function yith_wcet_init() {

    load_plugin_textdomain( 'yith-woocommerce-email-templates', false, dirname( plugin_basename( __FILE__ ) ). '/languages/' );

    // Load required classes and functions
    require_once('functions.yith-wcet.php');
    require_once('class.yith-wcet-admin.php');
    require_once('class.yith-wcet.php');

    // Let's start the game!
    YITH_WCET();
}
add_action( 'yith_wcet_init', 'yith_wcet_init' );


function yith_wcet_install() {

    if ( ! function_exists( 'WC' ) ) {
        add_action( 'admin_notices', 'yith_wcet_install_woocommerce_admin_notice' );
    }
    elseif ( defined( 'YITH_WCET_PREMIUM' ) ) {
        add_action( 'admin_notices', 'yith_wcet_install_free_admin_notice' );
        deactivate_plugins( plugin_basename( __FILE__ ) );
    }
    else {
        do_action( 'yith_wcet_init' );
    }
}
add_action( 'plugins_loaded', 'yith_wcet_install', 11 );

/* Plugin Framework Version Check */
if ( !function_exists( 'yit_maybe_plugin_fw_loader' ) && file_exists( plugin_dir_path( __FILE__ ) . 'plugin-fw/init.php' ) ) {
    require_once( plugin_dir_path( __FILE__ ) . 'plugin-fw/init.php' );
}
yit_maybe_plugin_fw_loader( plugin_dir_path( __FILE__ ) );