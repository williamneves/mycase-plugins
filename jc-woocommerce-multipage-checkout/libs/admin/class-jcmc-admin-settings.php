<?php
/**
 * Plugin Settings 
 *
 * Add WooCommerce settings page for custom options
 *
 * @author James Collings <james@jclabs.co.uk>
 * @version 0.2
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class JCMC_Admin_Settings extends WC_Settings_Page {

	public function __construct(){
		$this->id    = 'multistep-checkout';
		$this->label = __( 'Multistep Checkout', 'jcmc' );

		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'woocommerce_sections_' . $this->id, array( $this, 'output_sections' ) );
		add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );

		add_action( 'woocommerce_sections_'.$this->id, array( $this, 'before_settings_output' ), 5 );
		add_action( 'woocommerce_settings_'.$this->id, array( $this, 'after_settings_output' ), 15 );

		add_action( 'woocommerce_admin_field_checkout_sections', array( $this, 'field_checkout_sections'));
	}

	public function before_settings_output(){
		?>
		<style type="text/css">

		.jcmc_wrapper{
			position: relative;
		}

		.jcmc_wrapper .jcmc_right, .jcmc_wrapper .jcmc_left{
			width: 100%;
			position: static;
		}

		.jcmc_wrapper .jcmc_right{
			background: #FFF;
			border: 1px solid #CCC;
			margin-top: 10px;
			margin-bottom: 10px;
		}

		.jcmc_inside{
			padding: 0 12px 12px;
			line-height: 1.4em;
			font-size: 13px;
		}

		.jcmc_inside p{
			margin:6px 0 0;
		}

		.jcmc_right h3{
			font-size: 14px;
			line-height: 1.4;
			padding: 8px 12px;
			margin: 0;
		}

		.jcmc_right ul{
			margin-bottom: 0;
		}

		.jcmc_version{
			margin:0;
			padding:10px;
			background: #5a9889;
			color: #FFF;
		}

		.jcmc_right hr{
			margin-top: 0;
		}

		@media screen and ( min-width: 782px ) {
			
			.jcmc_wrapper .jcmc_right{
				width: 250px;
				position: absolute;
				right: 0;	
				margin-top: 40px;		
			}

			

			.jcmc_wrapper .jcmc_left{
				padding-right: 270px;
				width: auto;
			}
		}

		</style>
		<div class="jcmc_wrapper">
		<div class="jcmc_right">
			<h3><span><?php _e('JC WooCommerce Multistep Checkout', 'jcmc'); ?></span></h3>
			<hr />
			<div class="jcmc_inside">
				<p><?php _e('Thank you for using this plugin, for more information on how to use it checkout the following links.', 'jcmc'); ?></p>
				<ul>
					<li><a href="http://jamescollings.co.uk/docs/v1/jc-woocommerce-multistep-checkout/?ref=<?php echo site_url('/'); ?>" target="_blank"><?php _e('Documentation', 'jcmc'); ?></a></li>
					<li><a href="http://jamescollings.co.uk/wordpress-plugins/woocommerce-multistep-checkout/?ref=<?php echo site_url('/'); ?>" target="_blank"><?php _e('About', 'jcmc'); ?></a></li>
				</ul>
			</div>
			<p class="jcmc_version">
				Version: <strong><?php echo JCMC()->get_version(); ?></strong>
			</p>
		</div>
		<div class="jcmc_left">
		<?php
	}

	public function after_settings_output(){
		?>
		</div><!-- end of jcmc_left -->
		</div><!-- end of jcmc_wrapper -->
		<div class="clear"></div>
		<?php
	}

	/**
	 * Get sections
	 *
	 * @return array
	 */
	public function get_sections() {

		$sections = array(
			''  => __( 'General Settings', 'jcmc' ),
			'sections'  => __( 'Checkout Steps', 'jcmc' )
		);

		if(JCMC()->get_settings('enable_css') === 'yes'){
			$sections['style'] = __('Checkout Styles', 'jcmc');
		}

		$sections['labels'] = __('Labels', 'jcmc');

		return apply_filters( 'woocommerce_get_sections_' . $this->id, $sections );
	}

	/**
	 * Get settings array
	 *
	 * @return array
	 */
	public function get_settings( $current_section = '' ) {
		$settings = array();

		if( 'style' == $current_section ){

			$settings[] = array(	
				'title' => __( 'Display Settings', 'jcmc' ), 
				'desc' => __( 'Change how your checkout page is displayed.', 'jcmc'),
				'type' => 'title', 
				'id' => 'jcmc_display_options'
			);

			$settings[] = array(
				'title' => __( 'Choose Tab Style', 'jcmc'),
				'desc' => __( 'Choose the style of the steps.', 'jcmc'),
				'type' => 'select',
				'options' => array('block' => 'Blocks', 'arrows' => 'Arrows', 'progress' => 'Progress'),
				'default' => JCMC()->get_settings('tab_style', true),
				'id' => 'jcmc_tab_style'
			);

			$settings[] = array(
				'title' => __( 'Choose Tab Alignment', 'jcmc'),
				'desc' => __( 'Position / Alignment of checkout steps.', 'jcmc'),
				'type' => 'select',
				'options' => array('left' => 'Left', 'top' => 'Top', 'right' => 'Right'),
				'default' => JCMC()->get_settings('tab_alignment', true),
				'id' => 'jcmc_tab_alignment'
			);

			$settings[] = array(
				'title' => __( 'Choose Tab Size', 'jcmc'),
				'type' => 'select',
				'options' => array('sm' => 'Small', 'md' => 'Medium', 'lg' => 'Large'),
				'default' => JCMC()->get_settings('tab_size', true),
				'id' => 'jcmc_tab_size'
			);

			$settings[] = array(
				'title' => __( 'Span Full Width', 'jcmc'),
				'type' => 'checkbox',
				'desc' => __( 'If Block Style is chosen and displayed at the top, then the elements will try and span full width if this option is checked.'),
				'default' => JCMC()->get_settings('tab_full_width', true),
				'id' => 'jcmc_tab_full_width'
			);

			$settings[] = array(
				'title' => __( 'Choose Button Size', 'jcmc'),
				'type' => 'select',
				'options' => array('sm' => 'Small', 'md' => 'Medium', 'lg' => 'Large'),
				'default' => JCMC()->get_settings('button_size', true),
				'id' => 'jcmc_button_size'
			);

			$settings[] = array( 'type' => 'sectionend', 'id' => 'jcmc_display_options' );

			$settings[] = array(	
				'title' => __( 'Display Styles', 'jcmc' ), 
				'desc' => __( 'Change checkout element styles.', 'jcmc'),
				'type' => 'title', 
				'id' => 'jcmc_style_options'
			);

			$settings[] = array(
				'title'    => __( 'Active Tab Background Color', 'jcmc' ),
				'id'       => 'jcmc_tab_color_active',
				'default'  => JCMC()->get_settings('tab_color_active'),
				'type'     => 'color',
				'autoload' => false
			);
			$settings[] = array(
				'title'    => __( 'Active Tab Text Color', 'jcmc' ),
				'id'       => 'jcmc_tab_text_active',
				'default'  => JCMC()->get_settings('tab_text_active'),
				'type'     => 'color',
				'autoload' => false
			);

			$settings[] = array(
				'title'    => __( 'Enabled Tab Background Color', 'jcmc' ),
				'id'       => 'jcmc_tab_color_enabled',
				'default'  => JCMC()->get_settings('tab_color_enabled'),
				'type'     => 'color',
				'autoload' => false
			);
			$settings[] = array(
				'title'    => __( 'Enabled Tab Text Color', 'jcmc' ),
				'id'       => 'jcmc_tab_text_enabled',
				'default'  => JCMC()->get_settings('tab_text_enabled'),
				'type'     => 'color',
				'autoload' => false
			);

			$settings[] = array(
				'title'    => __( 'Disabled Tab Background Color', 'jcmc' ),
				'id'       => 'jcmc_tab_color_disabled',
				'default'  => JCMC()->get_settings('tab_color_disabled'),
				'type'     => 'color',
				'autoload' => false
			);
			$settings[] = array(
				'title'    => __( 'Disabled Tab Text Color', 'jcmc' ),
				'id'       => 'jcmc_tab_text_disabled',
				'default'  => JCMC()->get_settings('tab_text_disabled'),
				'type'     => 'color',
				'autoload' => false
			);

			// Button Colours
			$settings[] = array(
				'title'    => __( 'Next / Previous Button Background Color', 'jcmc' ),
				'id'       => 'jcmc_button_color_default',
				'default'  => JCMC()->get_settings('button_color_default'),
				'type'     => 'color',
				'autoload' => false
			);
			$settings[] = array(
				'title'    => __( 'Next / Previous Button Text Color', 'jcmc' ),
				'id'       => 'jcmc_button_text_default',
				'default'  => JCMC()->get_settings('button_text_default'),
				'type'     => 'color',
				'autoload' => false
			);

			// Order Button
			$settings[] = array(
				'title'    => __( 'Order Button Background Color', 'jcmc' ),
				'id'       => 'jcmc_button_color_primary',
				'default'  => JCMC()->get_settings('button_color_primary'),
				'type'     => 'color',
				'autoload' => false
			);
			$settings[] = array(
				'title'    => __( 'Order Button Text Color', 'jcmc' ),
				'id'       => 'jcmc_button_text_primary',
				'default'  => JCMC()->get_settings('button_text_primary'),
				'type'     => 'color',
				'autoload' => false
			);

			$settings[] = array(
				'title' => __('Hide Step Numbers', 'jcmc'),
				'id' => 'jcmc_hide_step_numbers',
				'default'  => JCMC()->get_settings('hide_step_numbers'),
				'type' => 'checkbox'
			);

			$settings[] = array(
				'title' => __('Reset Styles', 'jcmc'),
				'id' => 'jcmc_reset_styles',
				'default'  => 'no',
				'type' => 'checkbox'
			);

			$settings[] = array( 'type' => 'sectionend', 'id' => 'jcmc_style_options' );
		}elseif( 'sections' == $current_section ){

			$settings[] = array(	
				'title' => __( 'Checkout Steps', 'jcmc' ), 
				'desc' => __( 'Change what sections are displayed.', 'jcmc'),
				'type' => 'title', 
				'id' => 'jcmc_checkout_sections'
			);

			$settings[] = array(
				'title'    => __( 'Steps', 'jcmc' ),
				'id'       => 'jcmc_checkout_steps',
				'default'  => JCMC()->get_settings('checkout_steps'),
				'type'     => 'checkout_sections',
				'autoload' => false
			);

			$settings[] = array( 'type' => 'sectionend', 'id' => 'jcmc_checkout_sections' );
		}elseif( 'labels' == $current_section ){

			$defaults = JCMC()->get_settings('text');

			$settings[] = array(	
				'title' => __( 'Checkout Buttons', 'jcmc' ), 
				'desc' => __( 'Change the text that appears on the checkout buttons', 'jcmc'),
				'type' => 'title', 
				'id' => 'jcmc_general_labels'
			);
			$settings[] = array(
				'title' => __('Next Button Label', 'jcmc'),
				// 'desc' => __( 'Localise plugin labels', 'jcmc'),
				'type' => 'text', 
				'default' => $defaults['btn_next'],
				'id' => 'jcmc_text[btn_next]'
			);
			$settings[] = array(
				'title' => __('Prev Button Label', 'jcmc'),
				// 'desc' => __( 'Localise plugin labels', 'jcmc'),
				'type' => 'text', 
				'default' => $defaults['btn_prev'],
				'id' => 'jcmc_text[btn_prev]'
			);
			$settings[] = array(
				'title' => __('Place Order Button Label', 'jcmc'),
				// 'desc' => __( 'Localise plugin labels', 'jcmc'),
				'type' => 'text', 
				'default' => $defaults['lbtn_order'],
				'id' => 'jcmc_text[lbtn_order]'
			);
			$settings[] = array( 'type' => 'sectionend', 'id' => 'jcmc_general_labels' );

			$settings[] = array(	
				'title' => __( 'Checkout Validation Messages', 'jcmc' ), 
				'desc' => __( 'Change the text on validation messages', 'jcmc'),
				'type' => 'title', 
				'id' => 'jcmc_general_errors'
			);
			$settings[] = array(
				'title' => __('Validation Email Message', 'jcmc'),
				// 'desc' => __( 'Localise plugin labels', 'jcmc'),
				'type' => 'text', 
				'default' => $defaults['validation_email'],
				'id' => 'jcmc_text[validation_email]'
			);
			$settings[] = array(
				'title' => __('Validation Required Message', 'jcmc'),
				// 'desc' => __( 'Localise plugin labels', 'jcmc'),
				'type' => 'text', 
				'default' => $defaults['validation_required'],
				'id' => 'jcmc_text[validation_required]'
			);
			$settings[] = array( 'type' => 'sectionend', 'id' => 'jcmc_general_errors' );
		}else{
			$settings[] = array(	
				'title' => __( 'General Settings', 'jcmc' ), 
				'desc' => __( 'Change settings to do with JC WooCommerce Multistep Checkout Plugin', 'jcmc'),
				'type' => 'title', 
				'id' => 'jcmc_general_options'
			);
			$settings[] = array(
				'title'    => __( 'Enable Plugin Styles', 'jcmc' ),
				'desc'     => __( 'Enable css styles', 'jcmc' ),
				'id'       => 'jcmc_enable_css',
				'default'  => 'yes',
				'type'     => 'checkbox',
				'autoload' => false
			);
			$settings[] = array(
				'title'    => __( 'Enable Multistep Checkout', 'jcmc' ),
				'desc'     => __( 'Enable the use of this multistep plugin', 'jcmc' ),
				'id'       => 'jcmc_enable_plugin',
				'default'  => 'yes',
				'type'     => 'checkbox',
				'autoload' => false
			);
			$settings[] = array( 'type' => 'sectionend', 'id' => 'jcmc_general_options' );
		}

		return $settings;
	}

	/**
	 * Save settings
	 */
	public function save() {
		global $current_section;

		$settings = $this->get_settings( $current_section );

		if( 'sections' == $current_section){

			$output = array();

			foreach($_POST['jcmc_checkout_steps']['name'] as $i => $name){

				// Skip Empty Name Field
				if(empty($name)){
					continue;
				}

				$output[] = array(
					'name' => esc_attr($name),
					'selector' => esc_attr($_POST['jcmc_checkout_steps']['selector'][$i]),
					'in_form' => isset($_POST['jcmc_checkout_steps']['in_form'][$i]) && $_POST['jcmc_checkout_steps']['in_form'][$i] == 'no'  ? 'no' : 'yes'
				);
			}

			$_POST['jcmc_checkout_steps'] = serialize( $output );	
		}elseif( 'style' == $current_section){

			// reset styles toggle
			if(isset($_POST['jcmc_reset_styles'])){
				if($_POST['jcmc_reset_styles'] == 1){

					foreach($_POST as $key => $val){

						if( strpos($key, 'jcmc_') === 0 ){

							$short_key = substr($key, 5);
							$value = JCMC()->get_settings($short_key, true);

							if($value == 'yes'){
								$value = 1;
							}elseif($value == 'no'){
								$value = 0;
							}
							$_POST[ $key ] = $value;
						}						
					}
				}

				unset($_POST['jcmc_reset_styles']);
			}
		}		

		WC_Admin_Settings::save_fields( $settings );

		// force reload of settings
		JCMC()->load_settings();
	}

	/**
	 * Output the settings
	 */
	public function output() {
		global $current_section;

		$settings = $this->get_settings($current_section);
		WC_Admin_Settings::output_fields( $settings );
	}

	public function field_checkout_sections($value){

		$description = WC_Admin_Settings::get_field_description($value);
		$values = JCMC()->get_settings('checkout_steps');
		extract($description);

		$values = is_serialized( $values ) ? unserialize($values) : $values;

		$sections = JCMC()->load_custom_sections(true);

		?>
		<p>You can use css selectors to target an element you wish to display on a selected step, to use multiple seperate them with a &quot;,&quot;.<br /><br />Or use the following predefined variables:
			<span class="jcmc-selector">{BILLING_FIELDS}</span>
			<span class="jcmc-selector">{SHIPPING_FIELDS}</span>
			<span class="jcmc-selector">{ORDER_COMMENTS}</span>
			<span class="jcmc-selector">{ORDER_DETAILS}</span>
			<span class="jcmc-selector">{PAYMENT_DETAILS}</span>
			<?php
			if(!empty($sections)){
				foreach($sections as $section_id => $section){
					echo '<span class="jcmc-selector">{' . str_replace(' ', '_', strtoupper($section_id) ) . '}</span>';
				}
			}
			?>
		</p>

		<table class="jcmc-checkout-sections">
			<thead>
				<tr>
					<th width="2%"></th>
					<th width="20%">Step Name</th>
					<th width="2%"></th>
					<th>Selector</th>
					<th width="2%"></th>
					<th width="4%">In Form</th>
					<th width="2%"></th>
					<th width="10%"></th>
				</tr>
			</thead>
			<tbody class="jcmc-sections">

				<?php
				if(!empty($values)):
				foreach($values as $i => $val):

					if(!isset($val['in_form'])){
						$val['in_form'] = 'yes';
					}

					?>

				<tr class="jcmc-section-row">
					<td class="jcmc-handle"></td>
					<td><input type="text" name="<?php echo esc_attr( $value['id'] ); ?>[name][<?php echo $i; ?>]" id="<?php echo esc_attr( $value['id'] ); ?>" value="<?php echo $val['name']; ?>" /></td>
					<td></td>
					<td><input type="text" name="<?php echo esc_attr( $value['id'] ); ?>[selector][<?php echo $i; ?>]" id="<?php echo esc_attr( $value['id'] ); ?>" value="<?php echo $val['selector']; ?>" /></td>
					<td></td>
					<td>
						<input type="hidden" name="<?php echo esc_attr( $value['id'] ); ?>[in_form][<?php echo $i; ?>]" value="no">
						<input type="checkbox" name="<?php echo esc_attr( $value['id'] ); ?>[in_form][<?php echo $i; ?>]" <?php checked( $val['in_form'], 'yes', true ); ?> value="yes">
					</td>
					<td></td>
					<td>
						<a href="#" class="jcmc-add-row">[+]</a>
						<a href="#" class="jcmc-add-del">[-]</a>
					</td>
				</tr>							
				<?php
				endforeach;
				endif;
				?>

			</tbody>
			
			<tfoot>
				<tr id="template" style="display:none;" class="jcmc-section-row">
					<td class="jcmc-handle"></td>
					<td><input type="text" name="<?php echo esc_attr( $value['id'] ); ?>[name][]" id="<?php echo esc_attr( $value['id'] ); ?>" value="" /></td>
					<td></td>
					<td><input type="text" name="<?php echo esc_attr( $value['id'] ); ?>[selector][]" id="<?php echo esc_attr( $value['id'] ); ?>" value="" /></td>
					<td></td>
					<td>
						<input type="hidden" name="<?php echo esc_attr( $value['id'] ); ?>[in_form][]" value="no">
						<input type="checkbox" name="<?php echo esc_attr( $value['id'] ); ?>[in_form][]" checked="checked" value="yes">
					</td>
					<td></td>
					<td>
						<a href="#" class="jcmc-add-row">[+]</a>
						<a href="#" class="jcmc-add-del">[-]</a>
					</td>
				</tr>
			</tfoot>

		</table>
		
		<style type="text/css">
			.jcmc-checkout-sections input[type="text"]{
				width: 100%;
			}
		</style>

		<script>
		jQuery(function($){

			var section_table = $('.jcmc-checkout-sections');
			var row_template = section_table.find('#template').clone();
			section_table.find('#template').remove();

			// reindex rows
			var reindex_rows = function(){
				$('.jcmc-sections .jcmc-section-row').each(function(rowIndex){
					/// find each input with a name attribute inside each row
					$(this).find('input[name]').each(function(){
						var name;
						name = $(this).attr('name');
						name = name.replace(/\[[0-9]*\]/g, '['+rowIndex+']');
						$(this).attr('name',name);
					});
				});
			};


			// add new row
			section_table.on('click', '.jcmc-add-row', function(e){

				var row = $(this).parents('.jcmc-section-row');
				var new_row = row_template.clone().show();
				new_row.attr('id', '');
				row.after(new_row);

				reindex_rows();				

				e.preventDefault();
			});

			// del new row
			section_table.on('click', '.jcmc-add-del', function(e){

				// escape if less than one field remains
				if(section_table.find('.jcmc-sections > .jcmc-section-row').length <= 1){
					return false;
				}

				$(this).closest('tr').remove();

				reindex_rows();

				e.preventDefault();
			});

			// fix issue of empty array
			var _count = section_table.find('tbody > tr').length;
			if(_count == 0){

				var new_row = row_template.clone().show();
				new_row.attr('id', '');
				console.log(new_row);
				section_table.find('tbody').append(new_row);
				reindex_rows();
			}
		});
		</script>
		<?php
	}
}

return new JCMC_Admin_Settings();