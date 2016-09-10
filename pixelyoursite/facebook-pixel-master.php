<?php
/*
	Plugin Name: PixelYourSite
	Description: Add the Facebook Pixel code into your Wordpress site and set up standard events with just a few clicks. Fully compatible with Woocommerce, purchase event included.
	Plugin URI: http://www.pixelyoursite.com/facebook-pixel-plugin-help
	Author: PixelYourSite
	Author URI: http://www.pixelyoursite.com
	Version: 3.1.0
	License: GPLv3
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) { die; }

if( defined( 'WP_DEBUG' ) && WP_DEBUG == true ) {
	error_reporting( E_ALL );
}

define( 'PYS_FREE_VERSION_REAL', '3.1.0');
define( 'PYS_FREE_VERSION', '3.0.2');           // for plugin notices capability

require_once( 'inc/common.php' );
require_once( 'inc/admin_notices.php' );
require_once( 'inc/core.php' );
require_once( 'inc/ajax-standard.php' );

add_action( 'plugins_loaded', 'pys_free_init' );
function pys_free_init() {

	$options = get_option( 'pixel_your_site' );
	if ( ! $options || ! isset( $options['general']['pixel_id'] ) || empty( $options['general']['pixel_id'] ) ) {
		pys_initialize_settings();
	}

	if( is_admin() || pys_get_option( 'general', 'enabled' ) == false || pys_is_disabled_for_role() || ! pys_get_option( 'general', 'pixel_id' ) ) {
		return;
	}

	add_action( 'wp_head', 'pys_pixel_code', 1 ); // display Facebook Pixel Code
	add_action( 'wp_enqueue_scripts', 'pys_public_scripts' );

	// add addtocart ajax support only if woocommerce installed and events enabled
	if ( pys_is_woocommerce_active() && pys_get_option( 'woo', 'enabled' ) && pys_get_option( 'woo', 'on_add_to_cart_btn' ) ) {
		add_filter( 'woocommerce_loop_add_to_cart_link', 'pys_add_code_to_woo_cart_link', 10, 2 );
	}
	
}

/* Register Admin Page for plugin */
if( !function_exists( 'pys_admin_menu' ) ) {

	add_action( 'admin_menu', 'pys_admin_menu' );
	function pys_admin_menu() {

		add_menu_page( 'PixelYourSite', 'PixelYourSite', 'manage_options', 'pixel-your-site', 'pys_admin_page_callback', plugins_url( 'pixelyoursite/img/favicon.png' ) );

	}

}

/* Admin page display callback */
if( !function_exists( 'pys_admin_page_callback' ) ) {

	function pys_admin_page_callback() {

		// update general and woo settings
		if( isset($_POST['pys']) ) {
			update_option( 'pixel_your_site', $_POST['pys'] );
		}

		include( 'inc/html-admin.php' );

	}

}

/* Register scripts for plugin admin screen */
if( !function_exists( 'pys_admin_scripts' ) ) {

	add_action( 'admin_enqueue_scripts', 'pys_admin_scripts' );
	function pys_admin_scripts() {

		// include only if plugin admin page
		if ( isset( $_GET['page'] ) && $_GET['page'] == 'pixel-your-site' ) {

			add_thickbox();

			wp_enqueue_style( 'pys', plugins_url( 'css/admin.css', __FILE__ ), array(), PYS_FREE_VERSION );
			wp_enqueue_script( 'pys', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery' ), PYS_FREE_VERSION );

			wp_localize_script( 'pys', 'pys', array(
				'ajax' => admin_url( 'admin-ajax.php' ),
			) );
		}

	}

}

/* Register front-end scripts. */
if( !function_exists( 'pys_public_scripts' ) ) {

	function pys_public_scripts() {

		wp_enqueue_script( 'pys', plugins_url( 'js/public.js', __FILE__ ), array( 'jquery' ), PYS_FREE_VERSION );

	}

}

/* Plugin activation. */
if( !function_exists( 'pys_free_plugin_activated' ) ) {

	register_activation_hook( __FILE__, 'pys_free_plugin_activated' );
	function pys_free_plugin_activated() {

		if ( ! function_exists( 'is_plugin_active' ) ) {

			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		}

		if ( is_plugin_active( 'pixelyoursite-pro/pixelyoursite-pro.php' ) ) {

			wp_die( 'Please deactivate PixelYourSite Pro version First.', 'Plugin Activation' );

		}

		$options = get_option( 'pixel_your_site' );
		if ( ! $options || ! isset( $options['general']['pixel_id'] ) || empty( $options['general']['pixel_id'] ) ) {

			pys_initialize_settings();

		}

		//@todo: refactor options names in future releases
		// set plugin activation data and remember version (used in admin notices)
		$activation_date = get_option('pysf_activation_date', '');
		$version = get_option('pysf_plugin_version', '');

		if( empty($activation_date) || version_compare($version, PYS_FREE_VERSION, '<') ) {
			update_option( 'pysf_activation_date', time() );
			update_option( 'pysf_plugin_version', PYS_FREE_VERSION );
			update_option( 'pysf_notice_dismiss', '' );
			update_option( 'woo_pysf_notice_dismiss', '' );
		}

	}

}

if( !function_exists( 'pys_initialize_settings' ) ) {

	function pys_initialize_settings() {

		// set default options values
		$defaults = pys_get_default_options();
		update_option( 'pixel_your_site', $defaults );

		// migrate settings from old versions
		if( get_option( 'woofp_admin_settings' ) ) {

			require_once( 'inc/migrate.php' );
			pys_migrate_from_22x();

		}

	}

}