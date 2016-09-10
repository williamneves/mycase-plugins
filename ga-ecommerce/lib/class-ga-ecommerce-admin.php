<?php

/**
 * Class Yoast_GA_eCommerce_Admin
 *
 * Admin class for the Yoast GA eCommerce plugin
 *
 * @since 3.0
 */
class Yoast_GA_eCommerce_Admin {

	/**
	 * Class Constructor, adds action.
	 *
	 * @since 3.0
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'admin_init' ) );
	}

	/**
	 * Initialize the admin side of the plugin, hook on plugins_loaded to allow checking whether GA for WP is active.
	 *
	 * @since 3.0
	 */
	public function admin_init() {

		// show notice if Google Analytics for WordPress is not defined
		if ( ! defined( 'GAWP_VERSION' ) ) {
			$this->add_notice( 'show_ga_missing_error' );
			return;
		}

		// Check version of Google Analytics for WordPress
		if ( class_exists( 'Yoast_GA_Options' ) ) {
			$ga_options      = Yoast_GA_Options::instance()->options;
			$minimal_version = '5.4.9';
			if ( version_compare( $minimal_version, GAWP_VERSION, '>' ) ) {
				$this->add_notice( 'show_version_notice' );
				return;
			}

			if ( $ga_options['enable_universal'] == 0 ) {
				$this->add_notice( 'show_enable_universal_notice' );
				return;
			}

			// Setting up the license manager
			if ( defined( 'GAWP_FILE' ) ) {
				new MI_Product_GA_eCommerce();
			}

		}
	}

	/**
	 * Throw an error if Google Analytics for WordPress is not installed.
	 *
	 * @since 3.0
	 */
	public function show_ga_missing_error() {
		echo '<div class="error"><p>' . sprintf( __( 'This plugin depends on the Google Analytics by MonsterInsights plugin. To allow the eCommerce tracking to work, please %sinstall &amp; activate Google Analytics by MonsterInsights%s.', 'yoast-ga-ecommerce' ), '<a href="' . admin_url( 'plugin-install.php?tab=search&type=term&s=google+analytics+by+monsterinsights&plugin-search-input=Search+Plugins' ) . '">', '</a>' ) . '</p></div>';
	}

	/**
	 * If the version is not correct, show a notice
	 *
	 */
	public function show_version_notice() {
		$notice  = sprintf( __( 'Google Analytics eCommerce Tracking requires at least version %s of Google Analytics by MonsterInsights.', 'yoast-ga-ecommerce' ), '5.4.9' );
		$notice .= '<br />';
		$notice .= __( 'Please make sure you have installed the latest version.', 'yoast-ga-ecommerce' );

		echo '<div class="error"><p>' . $notice . '</p></div>';
	}

	/**
	 * if universal is disable, show a notice
	 *
	 */
	public function show_enable_universal_notice() {
		echo '<div class="error"><p>' .sprintf( __( 'eCommerce tracking can only be used if Universal tracking is enabled. Please %sactivate Universal tracking%s.', 'yoast-ga-ecommerce' ), "<a href='admin.php?page=yst_ga_settings#top#universal'>", '</a>' ) . '</p></div>';
	}

	private function add_notice( $method ) {
		add_action( 'all_admin_notices', array( $this, $method ) );
	}

}
