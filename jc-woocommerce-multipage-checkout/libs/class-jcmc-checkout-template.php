<?php 
/**
 * @author James Collings <james@jclabs.co.uk>
 * @version 0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class JCMC_Checkout_Template{

	public function __construct(){

		if(JCMC()->get_settings('enable_plugin') != 'yes'){
			return;
		}

		add_action('wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action('wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );

		add_action( 'woocommerce_before_checkout_form', array( $this, 'output_custom_sections'));
	}

	public function parseStepSelectors($steps = false){

		if(!is_array($steps) || empty($steps)){
			return array();
		}

		$sections = JCMC()->load_custom_sections(true);

		$patterns = array(
			'/{BILLING_FIELDS}/',
			'/{SHIPPING_FIELDS}/',
			'/{ORDER_COMMENTS}/',
			'/{ORDER_DETAILS}/',
			'/{PAYMENT_DETAILS}/',
		);
		$replacements = array(
			'#customer_details .woocommerce-billing-fields',
			'#customer_details .woocommerce-shipping-fields',
			'#order_comments_field',
			'#order_review_heading, .woocommerce-checkout-review-order-table',
			'#order_review',
		);

		if(!empty($sections)){
			foreach($sections as $section_id => $section){

				$patterns[] = '/{' . str_replace(' ', '_', strtoupper($section_id) ) . '}/';

				$visible_logged_in = isset($section['logged_in']) && $section['logged_in'] == false ? false : true;

				if( is_user_logged_in() && !$visible_logged_in ){
					$replacements[] = '';
				}else{
					$replacements[] = '#' . $section_id;
				}
			}
		}

		// anything that has not been replaced, and is still in the format {XYZ} ignore
		$patterns[] = '/,[ ]*{([a-zA-Z_-]+)}/';
		$replacements[] = '';

		$patterns[] = '/{([a-zA-Z_-]+)}/';
		$replacements[] = '';

		foreach($steps as $i => $step){

			$selector = preg_replace($patterns, $replacements, $step['selector']);

			// hide empty selectors
			if(!empty($selector)){
				$steps[$i]['selector'] = $selector;
			}else{
				unset($steps[$i]);
			}
		}

		return array_values($steps);
	}

	public function output_custom_sections(){

		$sections = JCMC()->load_custom_sections();

		foreach($sections as $section_id => $section){

			echo '<div id="'.$section_id.'" style="display:none;" class="jcmc-custom-section">';
			echo isset( $section['content'] ) ? $section['content'] : '';
			echo '</div>';
		}
	}

	/**
	 * Load plugin frontend javascript files
	 * @return void
	 */
	public function enqueue_scripts(){

		if(!defined('WOOCOMMERCE_CHECKOUT') && !is_checkout()){
			return false;
		}

		$sections = JCMC()->load_custom_sections();
	
		$jcmc_settings = JCMC()->get_display_settings();
		$steps = $this->parseStepSelectors($jcmc_settings['checkout_steps']);
		foreach($steps as &$step){
			$temp = explode(',', $step['selector']);
			$output = array();
			foreach($temp as $part){
				if(!empty($part)){
					$output[] = $part;
				}
			}
			$step['selector_parts'] = $output;
		}
		$jcmc_settings['checkout_steps'] = $steps;
		$jcmc_settings['custom_sections'] = array_keys($sections);
		$jcmc_settings['text'] = JCMC()->get_settings('text');

		wp_register_script( 'jcmc-checkout', JCMC()->get_plugin_url() . 'assets/js/checkout.js', array('jquery'), JCMC()->get_version(), true );
		wp_localize_script( 'jcmc-checkout', 'jcmc', $jcmc_settings );
		wp_enqueue_script( 'jcmc-checkout' );
	}

	/**
	 * Load plugin frontend css files
	 * @return void
	 */
	public function enqueue_styles(){

		if(!defined('WOOCOMMERCE_CHECKOUT') && !is_checkout()){
			return false;
		}

		// core styles
		wp_enqueue_style( 'jcmc-core', JCMC()->get_plugin_url() . 'assets/css/core.css', false, JCMC()->get_version() );

		// optional styles
		if(JCMC()->get_settings('enable_css') === 'yes'){
			wp_enqueue_style( 'jcmc-basic', JCMC()->get_plugin_url() . 'assets/css/basic.css', array('jcmc-core'), JCMC()->get_version() );
			wp_add_inline_style( 'jcmc-basic', $this->generate_checkout_css() );
		}
	}

	public function darken($colour, $percent){
		$percent = ($percent * -2);
		return $this->adjustBrightness($colour, $percent);
	}

	public function lighten($colour, $percent){
		$percent = ($percent * 2);
		return $this->adjustBrightness($colour, $percent);
	}

	private function adjustBrightness($hex, $steps) {
	    // Steps should be between -255 and 255. Negative = darker, positive = lighter
	    $steps = max(-255, min(255, $steps));

	    // Normalize into a six character long hex string
	    $hex = str_replace('#', '', $hex);
	    if (strlen($hex) == 3) {
	        $hex = str_repeat(substr($hex,0,1), 2).str_repeat(substr($hex,1,1), 2).str_repeat(substr($hex,2,1), 2);
	    }

	    // Split into three parts: R, G and B
	    $color_parts = str_split($hex, 2);
	    $return = '#';

	    foreach ($color_parts as $color) {
	        $color   = hexdec($color); // Convert to decimal
	        $color   = max(0,min(255,$color + $steps)); // Adjust color
	        $return .= str_pad(dechex($color), 2, '0', STR_PAD_LEFT); // Make two char hex code
	    }

	    return $return;
	}


	public function generate_checkout_css(){

		ob_start();

		$tab_active_background = JCMC()->get_settings('tab_color_active');
		$tab_active_colour = JCMC()->get_settings('tab_text_active');

		$tab_enabled_background = JCMC()->get_settings('tab_color_enabled');
		$tab_enabled_colour = JCMC()->get_settings('tab_text_enabled');

		$tab_disabled_background = JCMC()->get_settings('tab_color_disabled');
		$tab_disabled_colour = JCMC()->get_settings('tab_text_disabled');

		$tab_active_number_background = $this->darken($tab_active_background, 5);
		$tab_active_number_border = $this->lighten($tab_active_background, 5);
		$tab_active_number_colour = $tab_active_colour;

		$tab_enabled_number_background = $this->darken($tab_enabled_background, 5);
		$tab_enabled_number_border = $this->lighten($tab_enabled_background, 5);
		$tab_enabled_number_colour = $tab_enabled_colour;

		$tab_disabled_number_background = $this->darken($tab_disabled_background, 5);
		$tab_disabled_number_border = $this->lighten($tab_disabled_background, 5);
		$tab_disabled_number_colour = $tab_disabled_colour;

		// Panel Colours
		$panel_bg_background = '#F7F7F7';

		// Buttons
		$button_color_default = JCMC()->get_settings('button_color_default');
		$button_text_default = JCMC()->get_settings('button_text_default');
		$button_color_primary = JCMC()->get_settings('button_color_primary');
		$button_text_primary = JCMC()->get_settings('button_text_primary');

		// display settings
		$tab_full_width = JCMC()->get_settings('tab_full_width');
		$tab_style = JCMC()->get_settings('tab_style');
		$tab_alignment = JCMC()->get_settings('tab_alignment');
		$tabs = JCMC()->get_settings('checkout_steps');	

		// calculate tab full width
		$tabs_total = count($this->parseStepSelectors($tabs));
		$margin_width = 1;
		$margin_total = ($tabs_total - 1) * $margin_width;
		$tab_width = ((100 - $margin_total) / $tabs_total );
		?>
@media screen and (min-width: 768px){

	/**
	 * Full Width Blocks
	 */
	.jcmc-tabs-top .jcmc-wide li{
		width: <?php echo $tab_width; ?>%;
		margin-right: <?php echo $margin_width; ?>%;
	}
	.jcmc-tabs-top .jcmc-wide li:last-child{
		margin-right: 0;
	}
}

/**
 * Tabs Colours
 */

.jcmc-blocks li a, .jcmc-blocks a:visited {
	background: <?php echo $tab_disabled_background; ?> !important;
	color: <?php echo $tab_disabled_colour; ?> !important;
}
.jcmc-blocks li .jcmc-number{
	border: 1px solid <?php echo $tab_disabled_number_border; ?>;
	background: <?php echo $tab_disabled_number_background; ?>;
	color: <?php echo $tab_disabled_number_colour; ?>;
}

/**
 * enabled tab colour
 */
.jcmc-blocks li.jcmc-enabled a {
	background: <?php echo $tab_enabled_background; ?> !important;
	color: <?php echo $tab_enabled_colour; ?> !important;
}
.jcmc-blocks li.jcmc-enabled .jcmc-number{
	border: 1px solid <?php echo $tab_enabled_number_border; ?>;
	background: <?php echo $tab_enabled_number_background; ?>;
	color: <?php echo $tab_enabled_number_colour; ?>;
}

/**
 * active tab colour
 */
.jcmc-blocks li.jcmc-active-link a {
	background: <?php echo $tab_active_background; ?> !important;
	color: <?php echo $tab_active_colour; ?> !important;
}
.jcmc-blocks li.jcmc-active-link .jcmc-number{
	border: 1px solid <?php echo $tab_active_number_border; ?>;
	background: <?php echo $tab_active_number_background; ?>;
	color: <?php echo $tab_active_number_colour; ?>;
}

/**
 * Arrow Colors
 */
.jcmc-arrows li.jcmc-enabled a:before {
	border-left-color: transparent;
	border-top-color: <?php echo $tab_enabled_background; ?>;
	border-bottom-color: <?php echo $tab_enabled_background; ?>;
}
.jcmc-arrows li a:before {
	border-left-color: transparent;
	border-top-color: <?php echo $tab_disabled_background; ?>;
	border-bottom-color: <?php echo $tab_disabled_background; ?>;
}
.jcmc-arrows li.jcmc-active-link a:before {
	border-left-color: transparent;
	border-top-color: <?php echo $tab_active_background; ?>;
	border-bottom-color: <?php echo $tab_active_background; ?>;
}

.jcmc-arrows li.jcmc-enabled a:after {
	border-left-color: <?php echo $tab_enabled_background; ?>;
}
.jcmc-arrows li a:after {
	border-left-color: <?php echo $tab_disabled_background; ?>;
}
.jcmc-arrows li.jcmc-active-link a:after {
	border-left-color: <?php echo $tab_active_background; ?>;
}

/**
 * Progress Colors
 */
#jcmc-tabs.jcmc-progress li.jcmc-enabled, #jcmc-tabs.jcmc-progress li.jcmc-enabled a, #jcmc-tabs.jcmc-progress li.jcmc-enabled .jcmc-number{
	border-color: <?php echo $tab_enabled_background; ?>;
}
#jcmc-tabs.jcmc-progress li.jcmc-enabled .jcmc-number{
	background: <?php echo $tab_enabled_number_background; ?>;
	color: <?php echo $tab_enabled_number_colour; ?>;
}

#jcmc-tabs.jcmc-progress li.jcmc-active-link, #jcmc-tabs.jcmc-progress li.jcmc-active-link a, #jcmc-tabs.jcmc-progress li.jcmc-active-link .jcmc-number{
	border-color: <?php echo $tab_active_background; ?>;
}
#jcmc-tabs.jcmc-progress li.jcmc-active-link .jcmc-number{
	background: <?php echo $tab_active_number_background; ?>;
	color: <?php echo $tab_active_number_colour; ?>;
}

#jcmc-tabs.jcmc-progress li, #jcmc-tabs.jcmc-progress li a, #jcmc-tabs.jcmc-progress li .jcmc-number{
	border-color: <?php echo $tab_disabled_background; ?>;
}
#jcmc-tabs.jcmc-progress li .jcmc-number{
	background: <?php echo $tab_disabled_number_background; ?>;
	color: <?php echo $tab_disabled_number_colour; ?>;
}

/**
 * Panel Colours
 */
.jcmc-tab{
	background-color: <?php echo $panel_bg_background; ?>;
}

/**
 * Button Colours
 */
#jcmc-wrap .jcmc-nextprev, #jcmc-wrap .jcmc-nextprev:visited{
	background: <?php echo $button_color_default; ?>;
	color: <?php echo $button_text_default; ?>;
}

#jcmc-wrap .jcmc-nextprev.jcmc-order, #jcmc-wrap .jcmc-nextprev.jcmc-order:visited{
	background: <?php echo $button_color_primary; ?>;
	color: <?php echo $button_text_primary; ?>;
}
		<?php
		$contents = ob_get_clean();
		return $contents;
	}
}

new JCMC_Checkout_Template();