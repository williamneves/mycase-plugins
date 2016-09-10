<?php
/*
Plugin Name: Google Analytics by MonsterInsights eCommerce tracking addon
Plugin URI: https://www.monsterinsights.com/pricing/
Description: Relying on the Google Analytics by MonsterInsights plugin, this plugin allows you to track your eCommerce transactions.
Version: 5.5.2
Author: The MonsterInsights Team
Author URI: https://www.monsterinsights.com/
Text Domain: yoast-ga-ecommerce
Depends: Google Analytics by MonsterInsights
*/

define( 'GAWP_ECOMMERCE_PATH', dirname( __FILE__ ) );


/**
 * Class Yoast_GA_eCommerce_Tracking
 *
 * Tracks transactions as soon as they're set to paid on the server.
 *
 * @since 3.0
 */
class Yoast_GA_eCommerce_Tracking {

	/**
	 * Holds the plugins version number, used for update checks.
	 */
	const VERSION = '5.5.2';

	/**
	 * Holds the main plugin file for use with dir and url functions.
	 */
	const PLUGIN_FILE = __FILE__;

	/**
	 * @var null|Yoast_GA_eCommerce_Tracking
	 */
	protected static $instance = null;

	/**
	 * Creating instance on the fly if its necessary - giving its instance back, allowing access to public functionality
	 *
	 * @return null|Yoast_GA_eCommerce_Tracking
	 */
	public static function instance() {

		if ( self::$instance == null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Class Constructor, adds action.
	 *
	 * @since 3.0
	 */
	public function __construct() {

		if ( self::$instance == null ) {
			self::$instance = $this;
		}

		// register class autoloader
		spl_autoload_register( array( $this, 'autoload' ) );

		// maybe run upgrade routine
		$this->upgrade();

		if ( is_admin() ) {
			new Yoast_GA_eCommerce_Admin();
		}

		add_action( 'plugins_loaded', array( $this, 'load' ), 1 );
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
	}

	/**
	 * Determine which eCommerce plugin(s) we're hooking into.
	 *
	 * @since 3.0
	 */
	public function load() {

		$tracking_code     = false;
		$universal_enabled = false;
		if ( class_exists( 'Yoast_GA_Options' ) ) {
			$tracking_code     = Yoast_GA_Options::instance()->get_tracking_code();
			$universal_enabled = Yoast_GA_Options::instance()->options['enable_universal'];
		}

		// Only do eCommerce Tracking when tracking_code is set
		if ( ! empty( $tracking_code ) && ! empty( $universal_enabled ) ) {
			if ( class_exists( 'Easy_Digital_Downloads' ) ) {
				new Yoast_GA_EDD_eCommerce_Tracking();
			}

			if ( class_exists( 'WooCommerce' ) ) {
				new Yoast_GA_Woo_eCommerce_Tracking();
			}
		}
	}

	/**
	 * Loading the textdomain for this plugin
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'yoast-ga-ecommerce', false, dirname( plugin_basename( self::PLUGIN_FILE ) ) . '/lib/languages/' );
	}

	/**
	 * Autoloader method
	 *
	 * @since 3.0
	 *
	 * @param string $class Class to load
	 */
	public function autoload( $class ) {
		static $classes = null;

		if ( $classes === null ) {

			$include_path = dirname( __FILE__ );

			$classes = array(
				'mi_product_ga_ecommerce'           => $include_path . '/includes/class-product-ga-e-commerce.php',
				'yoast_ga_ecommerce_admin'             => $include_path . '/lib/class-ga-ecommerce-admin.php',
				'yoast_ga_ecommerce_tracking_abstract' => $include_path . '/lib/abstract-class-ecommerce-tracking.php',
				'yoast_ga_edd_ecommerce_tracking'      => $include_path . '/lib/class-edd-ecommerce-tracking.php',
				'yoast_ga_woo_ecommerce_tracking'      => $include_path . '/lib/class-woo-ecommerce-tracking.php',
			);
		}

		$class_name = strtolower( $class );

		if ( isset( $classes[ $class_name ] ) ) {
			require_once( $classes[ $class_name ] );
		}
	}

	/**
	 * Runs an upgrade routine, if necessary
	 *
	 * @since 3.0
	 *
	 * @return boolean True if the upgrade routine ran, false otherwise
	 */
	private function upgrade() {

		$code_version = get_option( 'yst_ga_ecommerce_version', 0 );

		if ( version_compare( self::VERSION, $code_version ) !== 1 ) {
			return false;
		}

		// update code version option
		update_option( 'yst_ga_ecommerce_version', self::VERSION );

		return true;
	}

}

new Yoast_GA_eCommerce_Tracking();
