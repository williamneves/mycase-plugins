<?php
/*
	Plugin Name: JC WooCommerce Multipage Checkout
	Plugin URI: http://www.jamescollings.co.uk/wordpress-plugins/woocommerce-multipage-checkout
	Description: Improve your websites checkout experience and help out your users by converting WooCommerce’s default checkout experience into an easy to follow multistep checkout.
	Version: 0.2.5
	Author: James Collings
	Author URI: http://www.jamescollings.co.uk
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class JCMC_Multipage_Checkout {

	protected $version = '0.2.5';
	public $plugin_dir = false;
	public $plugin_url = false;
	protected $plugin_slug = false;
	protected $settings = false;
	private $default_settings = false;

	/**
	 * Single instance of class
	 */
	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct(){

		$this->plugin_dir =  plugin_dir_path( __FILE__ );
		$this->plugin_url = plugins_url( '/', __FILE__ );
		$this->plugin_slug = basename(dirname(__FILE__));

		add_action( 'woocommerce_init', array( $this, 'init' ) );
	}

	public function includes(){

		if(is_admin()){
			include_once 'libs/admin/jcmc-admin-init.php';
		}

		include_once 'libs/jcmc-functions.php';

		include_once 'libs/class-jcmc-checkout-template.php';

	}

	/**
	 * Initite plugin
	 * @return void
	 */
	public function init(){

		$this->load_settings();
		$this->includes();
	}

	/**
	 * Plugin Default Settings
	 * @return void
	 */
	public function default_settings(){

		// set default settings
		$this->default_settings = array(
			'enable_css' => 'yes',
			'enable_plugin' => 'yes',
			// tabs
			'tab_style' => 'arrows',
			'tab_size' => 'md',
			'tab_alignment' => 'top',
			'tab_full_width' => 'no',
			'tab_color_active' => '#5fc562',
			'tab_color_enabled' => '#3491C4',
			'tab_color_disabled' => '#B6D7EA',
			'tab_text_active' => '#FFFFFF',
			'tab_text_enabled' => '#FFFFFF',
			'tab_text_disabled' => '#333333',
			// buttons
			'button_size' => 'md',
			'button_color_default' => '#333333',
			'button_text_default' => '#eeeeee',
			'button_color_primary' => '#5fc562',
			'button_text_primary' => '#FFFFFF',

			'hide_step_numbers' => 'no',
			'checkout_steps' =>  array(
				array(
					'name' => 'Billing',
					'selector' => '{BILLING_FIELDS}'
				),
				array(
					'name' => 'Shipping',
					'selector' => '{SHIPPING_FIELDS}, {ORDER_COMMENTS}'
				),
				array(
					'name' => 'Order Details',
					'selector' => '{ORDER_DETAILS}'
				),
				array(
					'name' => 'Payment Details',
					'selector' => '{PAYMENT_DETAILS}'
				)
			),
			'text' => array(
				'btn_next' => __( 'Next' , 'jcmc'),
				'btn_prev' => __( 'Prev' , 'jcmc'),
				'lbtn_order' => __( 'Place Order' , 'jcmc'),
				'validation_email' => __( 'Please enter a valid email' , 'jcmc'),
				'validation_required' => __( 'This field is required' , 'jcmc')
			)
		);
	}

	/**
	 * Load plugin settings
	 * @return void
	 */
	public function load_settings(){

		$this->default_settings();
		$this->settings = array();

		// load settings from db
		foreach($this->default_settings as $key => $default){
			$data = get_option('jcmc_'.$key, $default);
			$this->settings[$key] = is_serialized( $data ) ? unserialize( $data ) : $data;
		}
	}

	/**
	 * Get all settings relating to the checkout display
	 * @return array
	 */
	public function get_display_settings(){
		$settings = array(
			'enable_css' => false,
			'enable_plugin' => false,
			'tab_style' => false,
			'tab_size' => false,
			'tab_full_width' => false,
			'checkout_steps' => false,
			'hide_step_numbers' => false,
			'tab_alignment' => false,
			'button_size' => false
		);

		foreach($settings as $key => $val){
			$settings[$key] = $this->get_settings($key);
		}
		return $settings;
	}

	public function get_settings($key, $default = false){

		if($default){
			return isset($this->default_settings[$key]) ? $this->default_settings[$key] : false;
		}

		return isset($this->settings[$key]) ? $this->settings[$key] : false;
	}

	public function load_custom_sections($show_all = false){

		$sections = array();
		$sections = apply_filters( 'jcmc_custom_section', $sections );

		foreach($sections as $section_id => $section){

			// visible = true , visible to everyone
			$visible_logged_in = isset($section['logged_in']) && $section['logged_in'] == false ? false : true;

			if( ( is_user_logged_in() && !$visible_logged_in && !$show_all ) ){
				unset($sections[$section_id]);
			}
		}

		return $sections;
	}

	public function get_version(){
		return $this->version;
	}
	public function get_plugin_slug(){
		return $this->plugin_slug;
	}
	public function get_plugin_url(){
		return $this->plugin_url;
	}
	public function get_plugin_dir(){
		return $this->plugin_dir;
	}
}

function JCMC() {
	return JCMC_Multipage_Checkout::instance();
}

$GLOBALS['jcmc'] = JCMC();