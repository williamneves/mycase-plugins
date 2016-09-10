<?php

if( !defined('ABSPATH') ) {
	exit;
}

/**
 * This class handles all the settings related WSOE plugin
 */
if( !class_exists( 'wpg_order_export' ) ){

	class wpg_order_export {

		/**
		 * Bootstraps the class and hooks required actions & filters.
		 *
		 */
		public function __construct() {

			add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_tab' ), 50 );
			add_action( 'woocommerce_settings_tabs_order_export', array($this, 'settings_tab') );
			add_action( 'woocommerce_update_options_order_export', array($this, 'update_settings') );
			add_action( 'woocommerce_admin_field_short_desc', array($this, 'short_desc_field') );
			add_action( 'woocommerce_admin_field_advanced_options', array($this, 'advanced_options') );
			add_action( 'admin_enqueue_scripts', array($this, 'scripts') );
			add_action( 'woocommerce_settings_wc_settings_tab_orderexport_section_end_after', array($this, 'section_end'), 999 );

			add_action('wp_ajax_wpg_order_export', array($this, 'wsoe_order_export'));
			add_action( 'admin_init' , array( $this, 'wsoe_download' ) );
			add_filter( 'plugin_action_links_'.WSOE_BASENAME, array($this, 'wsoe_action_links') );
			add_action( 'woocommerce_settings_saved', array( $this, 'settings_saved' ) );
		}

		/**
		 * Runs when plugin is activated.
		 */
		function install() {

			global $wpg_order_columns;

			foreach( $wpg_order_columns as $key=>$val ){

				$option = get_option( $key, null );
				if( empty( $option ) ) {
					update_option($key, 'yes');
				}
			}
		}

		public function scripts( $pagehook ) {

			if(  (!empty( $_GET['tab'] )&& $_GET['tab'] === 'order_export') ) {
				wp_enqueue_script('jquery-ui-datepicker');
				wp_enqueue_style('jquery-ui-datepicker');
				wp_enqueue_script('jquery-ui-sortable');
				wp_enqueue_script( 'order-export', OE_JS. 'orderexport.js', array('jquery','jquery-ui-datepicker'), false, true );
			}
			
			wp_enqueue_style('wpg-style', OE_CSS.'style.css');
		}

		/**
		 * Add Settings link to plugins page, this allows users to navigate to settings page directly.
		 * @param array $links array of links
		 * @return array action links
		 */
		public function wsoe_action_links($links) {

			$setting_link = array('<a href="' . admin_url( 'admin.php?page=wc-settings&tab=order_export' ) . '">'.__('Settings', 'woocommerce-simply-order-export').'</a>',);
			return array_merge($links, $setting_link);
		}

		/**
		 * Add a new settings tab to the WooCommerce settings tabs array.
		 *
		 * @param array $settings_tabs Array of WooCommerce setting tabs & their labels, excluding the Subscription tab.
		 * @return array $settings_tabs Array of WooCommerce setting tabs & their labels, including the Subscription tab.
		 */
		public function add_settings_tab( $settings_tabs ) {
			$settings_tabs['order_export'] = __( 'Order Export', 'woocommerce-simply-order-export' );
			return $settings_tabs;
		}


		/**
		 * Uses the WooCommerce admin fields API to output settings via the @see woocommerce_admin_fields() function.
		 *
		 * @uses woocommerce_admin_fields()
		 * @uses self::get_settings()
		 */
		public function settings_tab() {
			woocommerce_admin_fields( $this->get_settings() );
		}


		/**
		 * Uses the WooCommerce options API to save settings via the @see woocommerce_update_options() function.
		 *
		 * @uses woocommerce_update_options()
		 * @uses self::get_settings()
		 */
		public function update_settings() {
			woocommerce_update_options( $this->get_settings() );
		}

		/**
		 * Returns settings fields.
		 */
		static function get_settings_fields() {

			$settings = array(

				'section_title' => array(
					'name'     => __( 'WooCommerce Order Export', 'woocommerce-simply-order-export' ),
					'type'     => 'title',
					'desc'     => '',
					'id'       => 'wc_settings_tab_orderexport_section_title'
				),

				'short_desc' => array(
					'type'     => 'short_desc',
					'desc'     => __( 'Please choose settings for order export.', 'woocommerce-simply-order-export' ),
				),

				'order_id' => array(
					'name' => __( 'Order ID', 'woocommerce-simply-order-export' ),
					'type' => 'checkbox',
					'desc' => __( 'Order ID', 'woocommerce-simply-order-export' ),
					'id'   => 'wc_settings_tab_order_id'
				),

				'customer_name' => array(
					'name' => __( 'Customer Name', 'woocommerce-simply-order-export' ),
					'type' => 'checkbox',
					'desc' => __( 'Customer Name', 'woocommerce-simply-order-export' ),
					'id'   => 'wc_settings_tab_customer_name'
				),

				'product_name' => array(
					'name' => __( 'Product Name', 'woocommerce-simply-order-export' ),
					'type' => 'checkbox',
					'desc' => __( 'Name of items purchased', 'woocommerce-simply-order-export' ),
					'id'   => 'wc_settings_tab_product_name'
				),

				'product_quantity' => array(
					'name' => __( 'Product Quantity', 'woocommerce-simply-order-export' ),
					'type' => 'checkbox',
					'desc' => __( 'Quantity of items purchased', 'woocommerce-simply-order-export' ),
					'id'   => 'wc_settings_tab_product_quantity'
				),

				'product_variation' => array(
					'name' => __( 'Product Variation', 'woocommerce-simply-order-export' ),
					'type' => 'checkbox',
					'desc' => __( 'Product variation', 'woocommerce-simply-order-export' ),
					'id'   => 'wc_settings_tab_product_variation'
				),

				'amount' => array(
					'name' => __( 'Amount', 'woocommerce-simply-order-export' ),
					'type' => 'checkbox',
					'desc' => __( 'Amount paid by customer', 'woocommerce-simply-order-export' ),
					'id'   => 'wc_settings_tab_amount'
				),

				'email' => array(
					'name' => __( 'Email', 'woocommerce-simply-order-export' ),
					'type' => 'checkbox',
					'desc' => __( 'Email of customer', 'woocommerce-simply-order-export' ),
					'id'   => 'wc_settings_tab_customer_email'
				),

				'phone' => array(
					'name' => __( 'Phone', 'woocommerce-simply-order-export' ),
					'type' => 'checkbox',
					'desc' => __( 'Phone number of customer', 'woocommerce-simply-order-export' ),
					'id'   => 'wc_settings_tab_customer_phone'
				),			

				'status' => array(
					'name' => __( 'Status', 'woocommerce-simply-order-export' ),
					'type' => 'checkbox',
					'desc' => __( 'Order Status', 'woocommerce-simply-order-export' ),
					'id'   => 'wc_settings_tab_order_status'
				)
			);

			/**
			 * Add more fields to plugin.
			 * Also you can use this filter to change settings fields order.
			 */
			return apply_filters( 'wc_settings_tab_order_export', $settings );

		}

		/**
		 * Get all the settings for this plugin for @see woocommerce_admin_fields() function.
		 *
		 * @return array Array of settings for @see woocommerce_admin_fields() function.
		 */
		public function get_settings() {

			$settings = self::get_settings_fields();

			$settings = apply_filters( 'wpg_before_advanced_options', $settings );

			$settings['advanced_options'] = array(
											'name' => __( 'Advanced Options', 'woocommerce-simply-order-export' ),
											'type' => 'advanced_options',
											'desc' => __( 'Order Status', 'woocommerce-simply-order-export' )
											);

			$settings['orderexport_section_end'] = array(
													'type' => 'sectionend',
													'id' => 'wc_settings_tab_orderexport_section_end'
												);

			return $settings;
		}

		/**
		 * Add custom types
		 */
		function short_desc_field( $value ) {

			$value['desc'] = empty($value['desc']) ? '' : $value['desc'];
			echo '<p class="wpg-short-desc">'. $value['desc'] .'</p>';
		}

		function section_end() { ?>

			<h3 class="orderexport-action"><?php _e( 'Select Duration and Export', 'woocommerce-simply-order-export' ) ?></h3>

			<p class="wpg-response-msg"></p>
			<div class="clearfix wpg-inputs">
				<div class="wpg-dateholder">
					<label for="wpg-start-date"><?php _e('Start Date', 'woocommerce-simply-order-export') ?></label>
					<input id="wpg-start-date" type="text" name="start_date" class="wpg-datepicker" value="" />
				</div>
				<div class="wpg-dateholder">
					<label for="wpg-end-date"><?php _e('End Date', 'woocommerce-simply-order-export') ?></label>
					<input id="wpg-end-date" type="text" name="end_date" class="wpg-datepicker" value="" />
				</div>

				<div class="orderexport-button">
					<input type="button" class="button wpg-order-export" value="<?php _e('Export Orders', 'woocommerce-simply-order-export') ?>" />
					<span class="spinner"></span>
				</div>
			</div>
			<input type="hidden" id="wpg_order_export_nonce" name="nonce" value="<?php echo wp_create_nonce('wpg_order_export') ?>" />
			<input type="hidden" name="action" value="wpg_order_export" /><?php
		}

		/**
		 * Advanced options.
		 */
		function advanced_options() {

			$settings = self::advanced_option_settings(); ?>

			<tr valign="top" class="single_select_page">
				<td style="padding-left: 0;" colspan="2">
					<div class="woo-soe">
						<a id="woo-soe-advanced" title="<?php _e('Click to see advanced options', 'woocommerce-simply-order-export') ?>" href="#"><?php _e('Advanced options', 'woocommerce-simply-order-export') ?></a>
						<p><span style="font-style: italic;"><?php _e( 'These are one time use options and will not be saved.', 'woocommerce-simply-order-export' ) ?></span></p>
						<div class="woo-soe-advanced" style="display: none;">
							<table>
								
								<?php do_action( 'advanced_options_begin' ) ?>

								<tr>
									<th>
										<?php _e( 'Order Export Filename', 'woocommerce-simply-order-export' ) ?>
										<img class="help_tip" data-tip="<?php _e('This will be the downloaded csv filename', 'woocommerce-simply-order-export') ?>" src="<?php echo OE_IMG; ?>help.png" height="16" width="16">
									</th>
									<td><input type="text" name="woo_soe_csv_name" value="<?php echo $settings['wsoe_export_filename'] ?>" /><?php _e('.csv', 'woocommerce-simply-order-export') ?></td>
								</tr>

								<tr>
									<th>
										<?php _e('Order Statuses', 'woocommerce-simply-order-export') ?>
										<img class="help_tip" data-tip="<?php _e('Orders with only selected status will be exported, if none selected then all order status will be exported', 'woocommerce-simply-order-export') ?>" src="<?php echo OE_IMG; ?>help.png" height="16" width="16">
									</th>
									<td><?php

										$statuses = wc_get_order_statuses();

										foreach( $statuses as $key=>$status ) { ?>

											<div class="order-statuses">
												<label>
													<input type="checkbox" <?php echo ( in_array( $key , $settings['wsoe_order_statuses'] ) ) ? 'checked="checked"' : '' ?> value="<?php echo $key; ?>" name="order_status[]" />
													<?php echo sprintf( '%1s', $status ) ?>
												</label>
											</div><?php
										} ?>

									</td>
								</tr>

								<tr>

									<th>
										<?php _e( 'Delimiter', 'woocommerce-simply-order-export') ?>
										<img class="help_tip" data-tip="<?php _e('Delimiter for exported file.', 'woocommerce-simply-order-export') ?>" src="<?php echo OE_IMG; ?>help.png" height="16" width="16">
									</th>

									<td>
										<input type="text" maxlength="1" name="wpg_delimiter" value="<?php echo $settings['wsoe_delimiter']; ?>" />
									</td>

								</tr>

								<?php do_action( 'advanced_options_end' ) ?>

							</table>
						</div>
					</div>
				</td>
			</tr><?php
		}

		/**
		 * Validates input
		 */
		static function validate() {

			if( empty( $_POST['start_date'] ) || ( empty( $_POST['end_date'] ) ) ){
				return new WP_Error( 'dates_empty', __( 'Enter both dates', 'woocommerce-simply-order-export' ) );
			}

			if( !self::checkdate( $_POST['start_date'] ) ) {
				return new WP_Error( 'invalid_start_date', __( 'Invalid start date.', 'woocommerce-simply-order-export' ) );
			}
			
			if( !self::checkdate( $_POST['end_date'] ) ) {
				return new WP_Error( 'invalid_end_date', __( 'Invalid end date.', 'woocommerce-simply-order-export' ) );
			}
			
			if( empty( $_POST['nonce'] ) ){
				return new WP_Error( 'empty_nonce', __( 'Invalid request', 'woocommerce-simply-order-export' ) );
			}elseif( !wp_verify_nonce( $_POST['nonce'], 'wpg_order_export') ){
				return new WP_Error( 'invalid_nonce', __( 'Invalid nonce.', 'woocommerce-simply-order-export' ) );
			}

			if( !empty( $_POST['woo_soe_csv_name'] ) && ( preg_match( '/^[a-zA-Z][a-zA-Z0-9\-\_]*\Z/', $_POST['woo_soe_csv_name'] ) === 0 ) ) {
				return new WP_Error( 'invalid_csv_filename', __( 'Invalid CSV filename. Only letters, numbers, dashes and underscore are allowed.' ) );
			}
		}

		/**
		 * Checks if a date is valid or not.
		 * Returns true if valid , false otherwise.
		 */
		static function checkdate( $date ){

			$date = explode( '-', $date );

			if( count( $date ) !== 3 )
				return false;

			if( !is_numeric( $date[0] ) || !is_numeric( $date[1] ) || !is_numeric( $date[2] ) )
				return false;

			return checkdate( $date[1], $date[2], $date[0] );
		}

		/**
		 * Validates input, creates csv file and sends the response to ajax.
		 */
		static function wsoe_order_export() {

			$response = array( 'error'=>false, 'msg'=>'', 'url'=>'' );

			if( is_wp_error( $validate = self::validate() ) ){

				$response = array( 'error'=>true, 'msg'=>$validate->get_error_message(), 'downloadname'=>'', 'url'=>'' );
				echo json_encode($response);
				die();
			}

			$result = order_export_process::get_orders();

			if( is_wp_error( $result ) ){
				$response['error'] = true;
				$response['msg'] = $result->get_error_message();
			}else{

				$response['url'] = trailingslashit( wsoe_upload_dir() ).'order_export.csv';
				$response['msg'] = $GLOBALS['wsoe_filename'];
				$response['downloadname'] = empty( $_POST['woo_soe_csv_name'] ) ? $GLOBALS['wsoe_filename'] : $_POST['woo_soe_csv_name'];
			}

			//wp_mail('alnobody70@gmail.com', 'Data test Response', var_export($_POST['return_data'], true));
			if( isset( $_POST['return_data'] ) ){
				return $response;
			}else{
				echo json_encode( $response );
				die;
			}

		}

		/**
		 * 
		 */
		function wsoe_download(){

			if( !empty($_GET['filename']) && !empty($_GET['downloadname']) && file_exists( trailingslashit( wsoe_upload_dir() ).$_GET['filename'].'.csv' ) && wsoe_is_shop_manager() ) {

				$download_filename = $_GET['downloadname'];
				$filename   = trailingslashit( wsoe_upload_dir() ).$_GET['filename'].'.csv';
				$charset = get_option('blog_charset');
				$settings = self::advanced_option_settings();

				$file = fopen( $filename, 'r' );
				$contents = fread($file, filesize($filename));
				fclose($file);

				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header('Content-Description: File Transfer');
				header('Content-Encoding: '. $charset);
				header('Content-type: text/csv; charset='. $charset);
				header("Content-Disposition: attachment; filename=$download_filename.csv");
				header("Expires: 0");
				header("Pragma: public");

				$fh = @fopen( 'php://output', 'w' );

				if( !empty($settings['wsoe_fix_chars']) ){

					/**
					 * This is a fix for Microsoft Excel. It may happen that some weird characters
					 * may appear while viewing the csv on excel with MAC OS.
					 * 
					 * Reference: http://bit.ly/229hcTL
					 */

					$contents = mb_convert_encoding( $contents, 'UTF-16LE', $charset );
					$contents = chr(255) . chr(254).$contents; // Add byte order mark
				}

				fwrite( $fh, $contents );
				fclose($fh);
				exit();
			
			}
        }

		/**
		 * This function will be used to save the advanced settings options
		 * 
		 * @since 1.3.0
		 */
		function settings_saved() {

			if( !empty($_REQUEST['page']) && !empty($_REQUEST['tab']) && $_REQUEST['tab'] === 'order_export' ) {

				$advanced_settings = array( 'wsoe_export_filename'=>'', 'wsoe_order_statuses'=> array(), 'wsoe_delimiter'=>'', 'wsoe_fix_chars'=>0 );
				
				/**
				 * Validate and save filename
				 */
				if( isset( $_POST['woo_soe_csv_name'] ) &&  (preg_match( '/^[a-zA-Z][a-zA-Z0-9\-\_]*\Z/', $_POST['woo_soe_csv_name'] ) !== 0 ) ) {
					$advanced_settings['wsoe_export_filename'] = $_POST['woo_soe_csv_name'];
				}else{
					$advanced_settings['wsoe_export_filename'] = '';
				}

				/**
				 * Save order statuses
				 */
				$advanced_settings['wsoe_order_statuses'] = ( empty( $_POST['order_status'] ) || !is_array( $_POST['order_status'] ) ) ? array() : $_POST['order_status'];

				/**
				 * Save delimiter
				 */
				$advanced_settings['wsoe_delimiter'] = (isset( $_POST['wpg_delimiter'] ) && ( strlen($_POST['wpg_delimiter']) == 1 ) ) ? $_POST['wpg_delimiter'] : '';

				/**
				 * Fix weird characters
				 */
				$advanced_settings['wsoe_fix_chars'] = ( !empty( $_POST['wpg_fix_chars'] ) ) ? 1 : 0;

				update_option( 'wsoe_advanced_settings_core', $advanced_settings, false );
			}
		}

		/**
		 * Retrieves advanced option settings for plugin.
		 */
		static function advanced_option_settings() {

			$default_settings = $advanced_settings = array( 'wsoe_export_filename'=>'', 'wsoe_order_statuses'=> array(), 'wsoe_delimiter'=>',', 'wsoe_fix_chars'=>0  );
			$settings = get_option( 'wsoe_advanced_settings_core', array() );

			$settings = wp_parse_args( $settings, $default_settings );

			return $settings;
		}

	}
}