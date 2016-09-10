<?php
/**
 * Plugin Name: YITH WooCommerce Quick Export Premium
 * Plugin URI: http://yithemes.com/themes/plugins/yith-woocommerce-quick-export/
 * Description: YITH WooCommerce Quick Export allows you to export orders, customer details and coupons on the fly, or scheduling automatic backup processes and recurrences.
 * Version: 1.0.2
 * Author: Yithemes
 * Author URI: http://yithemes.com/
 * Text Domain: yith-woocommerce-quick-export
 * Domain Path: /languages/
 *
 * @author Your Inspiration Themes
 * @package YITH WooCommerce Quick Export
 * @version 1.0.2
 */

/*  Copyright 2013-2015  Your Inspiration Themes  (email : plugins@yithemes.com)

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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

//region    ****    Check if prerequisites are satisfied before enabling and using current plugin
if ( ! function_exists( 'is_plugin_active' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

function yith_ywqe_premium_install_woocommerce_admin_notice() {
	?>
	<div class="error">
		<p><?php _e( 'YITH WooCommerce Quick Export is enabled but not effective. It requires WooCommerce in order to work.', 'yith-woocommerce-quick-export' ); ?></p>
	</div>
<?php
}

/**
 * Check if a free version is currently active and try disabling before activating this one
 */
if ( ! function_exists( 'yit_deactive_free_version' ) ) {
	require_once 'plugin-fw/yit-deactive-plugin.php';
}
yit_deactive_free_version( 'YITH_YWQE_FREE_INIT', plugin_basename( __FILE__ ) );


if ( ! function_exists( 'yith_plugin_registration_hook' ) ) {
	require_once 'plugin-fw/yit-plugin-registration-hook.php';
}
register_activation_hook( __FILE__, 'yith_plugin_registration_hook' );

//endregion

//region    ****    Define constants
if ( ! defined( 'YITH_YWQE_INIT' ) ) {
	define( 'YITH_YWQE_INIT', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'YITH_YWQE_PREMIUM' ) ) {
	define( 'YITH_YWQE_PREMIUM', '1' );
}

if ( ! defined( 'YITH_YWQE_SLUG' ) ) {
	define( 'YITH_YWQE_SLUG', 'yith-woocommerce-quick-export' );
}

if ( ! defined( 'YITH_YWQE_SECRET_KEY' ) ) {
	define( 'YITH_YWQE_SECRET_KEY', 'SHHcyrUevgjYsa5JpY3h' );
}

if ( ! defined( 'YITH_YWQE_VERSION' ) ) {
	define( 'YITH_YWQE_VERSION', '1.0.2' );
}

if ( ! defined( 'YITH_YWQE_FILE' ) ) {
	define( 'YITH_YWQE_FILE', __FILE__ );
}

if ( ! defined( 'YITH_YWQE_DIR' ) ) {
	define( 'YITH_YWQE_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'YITH_YWQE_URL' ) ) {
	define( 'YITH_YWQE_URL', plugins_url( '/', __FILE__ ) );
}

if ( ! defined( 'YITH_YWQE_ASSETS_URL' ) ) {
	define( 'YITH_YWQE_ASSETS_URL', YITH_YWQE_URL . 'assets' );
}

if ( ! defined( 'YITH_YWQE_TEMPLATES_DIR' ) ) {
	define( 'YITH_YWQE_TEMPLATES_DIR', YITH_YWQE_DIR . 'templates/' );
}

if ( ! defined( 'YITH_YWQE_ASSETS_IMAGES_URL' ) ) {
	define( 'YITH_YWQE_ASSETS_IMAGES_URL', YITH_YWQE_ASSETS_URL . '/images/' );
}

if ( ! defined( 'YITH_YWQE_LIB_DIR' ) ) {
	define( 'YITH_YWQE_LIB_DIR', YITH_YWQE_DIR . 'lib/' );
}

$wp_upload_dir = wp_upload_dir();

if ( ! defined( 'YITH_YWQE_DOCUMENT_SAVE_DIR' ) ) {
	define( 'YITH_YWQE_DOCUMENT_SAVE_DIR', $wp_upload_dir['basedir'] . '/yith-quick-export/' );
}

//endregion

function yith_ywqe_premium_init() {

	/**
	 * Load text domain and start plugin
	 */
	load_plugin_textdomain( 'yith-woocommerce-quick-export', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	// Load required classes and functions
	require_once( YITH_YWQE_DIR . 'functions.php' );

	require_once( YITH_YWQE_LIB_DIR . 'class.ywqe-plugin-fw-loader.php' );
	require_once( YITH_YWQE_LIB_DIR . 'class.ywqe-custom-types.php' );
	require_once( YITH_YWQE_LIB_DIR . 'class.yith-woocommerce-quick-export.php' );
	require_once( YITH_YWQE_LIB_DIR . 'class.yith-dropbox.php' );
	require_once( YITH_YWQE_LIB_DIR . 'class.ywqe-export-job.php' );
	require_once( YITH_YWQE_LIB_DIR . 'pclzip/pclzip.lib.php' );

	YWQE_Plugin_FW_Loader::get_instance();

	// Let's start the game!
	YITH_WooCommerce_Quick_Export::get_instance();
}

add_action( 'yith_ywqe_premium_init', 'yith_ywqe_premium_init' );


function yith_ywqe_premium_install() {

	if ( ! function_exists( 'WC' ) ) {
		add_action( 'admin_notices', 'yith_ywqe_premium_install_woocommerce_admin_notice' );
	} else {
		do_action( 'yith_ywqe_premium_init' );
	}
}

add_action( 'plugins_loaded', 'yith_ywqe_premium_install', 11 );