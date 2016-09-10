<?php
/**
 * Plugin Name:  WooCommerce AdWords Conversion Tracking
 * Plugin URI:   https://wordpress.org/plugins/woocommerce-google-adwords-conversion-tracking-tag/
 * Description:  Google AdWords dynamic conversion value tracking for WooCommerce.
 * Author:       Wolf+Bär GmbH
 * Author URI:   https://wolfundbaer.ch
 * Version:      1.3.3
 * License:      GPLv2 or later
 * Text Domain:  woocommerce-google-adwords-conversion-tracking-tag
 **/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


class WGACT {

	public function __construct() {

		// startup all functions
		$this->init();

	}

	// startup all functions
	public function init() {

		// add the admin options page
		add_action( 'admin_menu', array( $this, 'wgact_plugin_admin_add_page' ), 99 );

		// install a settings page in the admin console
		add_action( 'admin_init', array( $this, 'wgact_plugin_admin_init' ) );

		// add a settings link on the plugins page
		add_filter( 'plugin_action_links', array( $this, 'wgact_settings_link' ), 10, 2 );

		// Load textdomain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// insert the conversion code only for visitors of the site
		add_action( 'plugins_loaded', array( $this, 'run_conversion_pixel_for_visitor' ) );

	}

	// only run the conversion code for visitors, not for the admin or shop managers
	public function run_conversion_pixel_for_visitor() {

		// don't load the pixel if a shop manager oder the admin is logged in
		if ( ! current_user_can( 'edit_others_pages' ) ) {

			// add the Google AdWords tag to the thankyou part of the page within the body tags
			add_action( 'woocommerce_thankyou', array( $this, 'GoogleAdWordsTag' ) );
		}
	}

	// Load text domain function
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'woocommerce-google-adwords-conversion-tracking-tag', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	// adds a link on the plugins page for the wgact settings
	function wgact_settings_link( $links, $file ) {
		if ( $file == plugin_basename( __FILE__ ) ) {
			$links[] = '<a href="' . admin_url( "admin.php?page=wgact" ) . '">' . __( 'Settings' ) . '</a>';
		}

		return $links;
	}

	// add the admin options page
	function wgact_plugin_admin_add_page() {
		//add_options_page('WGACT Plugin Page', 'WGACT Plugin Menu', 'manage_options', 'wgact', array($this, 'wgact_plugin_options_page'));
		add_submenu_page( 'woocommerce', esc_html__( 'AdWords Conversion Tracking', 'woocommerce-google-adwords-conversion-tracking-tag' ), esc_html__( 'AdWords Conversion Tracking', 'woocommerce-google-adwords-conversion-tracking-tag' ), 'manage_options', 'wgact', array(
			$this,
			'wgact_plugin_options_page'
		) );
	}

	// display the admin options page
	function wgact_plugin_options_page() {

		?>

		<br>
		<div style="float: right; padding-right: 20px">
			<div style="float:left; margin-right: 10px">Tell us how much you like the plugin!</div>
			<div style="float:left"><?php require( 'includes/rating.php' ); ?></div>
		</div>
		<div style="width:980px; float: left; margin: 5px">
			<div style="float:left; margin: 5px; margin-right:20px; width:750px">
				<div style="background: #0073aa; padding: 10px; font-weight: bold; color: white; border-radius: 2px">
					<?php esc_html_e( 'Google AdWords Conversion Tracking Settings', 'woocommerce-google-adwords-conversion-tracking-tag' ) ?>
				</div>
				<form action="options.php" method="post">
					<?php settings_fields( 'wgact_plugin_options' ); ?>
					<?php do_settings_sections( 'wgact' ); ?>
					<br>
					<table class="form-table" style="margin: 10px">
						<tr>
							<th scope="row" style="white-space: nowrap">
								<input name="Submit" type="submit" value="<?php esc_attr_e( 'Save Changes' ); ?>"
								       class="button button-primary"/>
							</th>
						</tr>
					</table>
				</form>

				<br>
				<div
					style="background: #0073aa; padding: 10px; font-weight: bold; color: white; margin-bottom: 20px; border-radius: 2px">
					<span>
						<?php esc_html_e( 'Profit Driven Marketing by Wolf+Bär', 'woocommerce-google-adwords-conversion-tracking-tag' ) ?>
					</span>
					<span style="float: right;">
						<a href="https://wolfundbaer.ch/?utm_source=WGACT&utm_medium=plugin&utm_campaign=WGACT-Plugin"
						   target="_blank" style="color: white">
							<?php esc_html_e( 'Visit us here: https://wolfundbaer.ch', 'woocommerce-google-dynamic-retargeting-tag' ) ?>
						</a>
					</span>
				</div>
			</div>
			<div style="float: left; margin: 5px">
				<a href="https://wordpress.org/plugins/woocommerce-google-dynamic-retargeting-tag/" target="_blank">
					<img src="<?php echo( plugins_url( 'images/wgdr-icon-256x256.png', __FILE__ ) ) ?>" width="150px"
					     height="150px">
				</a>
			</div>
			<div style="float: left; margin: 5px">
				<a href="https://wordpress.org/plugins/woocommerce-google-adwords-conversion-tracking-tag/"
				   target="_blank">
					<img src="<?php echo( plugins_url( 'images/wgact-icon-256x256.png', __FILE__ ) ) ?>" width="150px"
					     height="150px">
				</a>
			</div>
		</div>
		<?php
	}


	// add the admin settings and such
	function wgact_plugin_admin_init() {
		//register_setting( 'plugin_options', 'plugin_options', 'wgact_plugin_options_validate' );
		register_setting( 'wgact_plugin_options', 'wgact_plugin_options_1' );
		register_setting( 'wgact_plugin_options', 'wgact_plugin_options_2' );
		add_settings_section( 'wgact_plugin_main', esc_html__( 'Settings', 'woocommerce-google-adwords-conversion-tracking-tag' ), array(
			$this,
			'wgact_plugin_section_text'
		), 'wgact' );
		add_settings_field( 'wgact_plugin_text_string_1', esc_html__( 'Conversion ID', 'woocommerce-google-adwords-conversion-tracking-tag' ), array(
			$this,
			'wgact_plugin_setting_string_1'
		), 'wgact', 'wgact_plugin_main' );
		add_settings_field( 'wgact_plugin_text_string_2', esc_html__( 'Conversion Label', 'woocommerce-google-adwords-conversion-tracking-tag' ), array(
			$this,
			'wgact_plugin_setting_string_2'
		), 'wgact', 'wgact_plugin_main' );
	}

	function wgact_plugin_section_text() {
		//echo '<p>Woocommerce Google AdWords conversion tracking tag</p>';
	}

	/*
	function wgact_plugin_setting_string_1() {
		$options = get_option('wgact_plugin_options_1');
		echo "<input id='wgact_plugin_text_string_1' name='wgact_plugin_options_1[text_string]' size='40' type='text' value='{$options['text_string']}' />";	
	}
	*/

	function wgact_plugin_setting_string_1() {
		$options = get_option( 'wgact_plugin_options_1' );
		echo "<input id='wgact_plugin_text_string_1' name='wgact_plugin_options_1[text_string]' size='40' type='text' value='{$options['text_string']}' />";
	}

	function wgact_plugin_setting_string_2() {
		$options = get_option( 'wgact_plugin_options_2' );
		echo "<input id='wgact_plugin_text_string_2' name='wgact_plugin_options_2[text_string]' size='40' type='text' value='{$options['text_string']}' />";
	}

	/*
	function wgact_plugin_setting_string_3() {
		$options = get_option('wgact_plugin_options_3');
		echo "<input id='wgact_plugin_text_string_3' name='wgact_plugin_options_3[text_string]' size='40' type='text' value='{$options['text_string']}' />";
	}
	*/

	// validate our options
	function wgact_plugin_options_validate( $input ) {
		$newinput['text_string'] = trim( $input['text_string'] );
		if ( ! preg_match( '/^[a-z0-9]{32}$/i', $newinput['text_string'] ) ) {
			$newinput['text_string'] = '';
		}

		return $newinput;
	}

	private function get_conversion_id() {
		$opt           = get_option( 'wgact_plugin_options_1' );
		$conversion_id = $opt['text_string'];

		return $conversion_id;
	}

	private function get_conversion_label() {
		$opt              = get_option( 'wgact_plugin_options_2' );
		$conversion_label = $opt['text_string'];

		return $conversion_label;
	}

	// insert the Google AdWords tag into the page
	public function GoogleAdWordsTag( $order_id ) {

		$conversion_id    = $this->get_conversion_id();
		$conversion_label = $this->get_conversion_label();

		// get order from URL and evaluate order total
		$order       = new WC_Order( $order_id );
		$order_total = $order->get_total();

		$order_total = apply_filters( 'wgact_conversion_value_filter', $order_total, $order );

		// Only run conversion script if the payment has not failed. (has_status('completed') is too restrictive)
		// And use the order meta to check if the conversion code has already run for this order ID. If yes, don't run it again.
		if ( ! $order->has_status( 'failed' ) && ( ( get_post_meta( $order_id, '_WGACT_conversion_pixel_fired', true ) != "true" ) ) ) {
			?>

			<!-- START Google Code for Sales (AdWords) Conversion Page -->

			<div style="display:inline;">
				<img height="1" width="1" style="border-style:none;" alt=""
				     src="//www.googleadservices.com/pagead/conversion/<?php echo $conversion_id; ?>/?value=<?php echo $order_total; ?>&amp;currency_code=<?php echo $order->get_order_currency(); ?>&amp;label=<?php echo $conversion_label; ?>&amp;guid=ON&amp;oid=<?php echo $order_id; ?>&amp;script=0"/>
			</div>

			<!-- END Google Code for Sales (AdWords) Conversion Page -->

			<?php
			// Set the order ID meta after the conversion code has run once.
			update_post_meta( $order_id, '_WGACT_conversion_pixel_fired', 'true' );
		} // end if order status
	}
}

$wgact = new WGACT();