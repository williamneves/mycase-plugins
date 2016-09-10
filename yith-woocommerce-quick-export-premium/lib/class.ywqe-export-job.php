<?php
/**
 * YWQE_Export_Job
 *
 * @author Your Inspiration Themes
 * @package YITH WooCommerce Quick Export
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YWQE_Export_Job' ) ) {
	/**
	 * Single exportation job
	 *
	 * @since 1.0.0
	 */
	class YWQE_Export_Job {

		/**
		 * Set if the job start now or is scheduled
		 * @var int
		 */
		public $autostart = 0;

		/**
		 * @var Type of the exportation that this job will execute
		 */
		public $export_items;

		/**
		 * @var specify if this is a repeated job
		 */
		public $recurrency;

		/**
		 * @var string Separator from columns in CSV files
		 */
		public $fields_separator = ";";

		/**
		 * @var string Separator for new lines in CSV files
		 */
		public $newline_separator = "\r\n";

		/**
		 * @var array list all customer columns to shown on CSV file
		 */
		protected $customers_visible_columns = array();

		/**
		 * @var array list all orders columns to shown on CSV file
		 */
		protected $orders_visible_columns = array();

		/**
		 * Constructor
		 *
		 * @access public
		 * @since 1.0.0
		 */
		public function __construct( $args ) {

			$this->init_settings( $args );

		}

		/**
		 * Initialize job parameters
		 *
		 * @param $args parameters specifying the type of exportation
		 */
		public function init_settings( $args ) {

			$customer_columns = array(
				'ID',
				'user_login',
				'user_pass',
				'user_nicename',
				'user_email',
				'user_url',
				'user_registered',
				'user_activation_key',
				'user_status',
				'display_name',
				'spam',
				'deleted'
			);

			$this->customers_visible_columns = apply_filters( 'yith_quick_export_customer_columns_order', $customer_columns );

			if ( YITH_WooCommerce_Quick_Export::get_instance()->show_customer_billing_data ) {

				$billing_columns = array(
					'billing_first_name',
					'billing_last_name',
					'billing_address_1',
					'billing_address_2',
					'billing_city',
					'billing_postcode',
					'billing_country',
					'billing_state',
					'billing_company',
					'billing_email',
					'billing_phone'
				);

				$billing_columns = apply_filters( 'yith_quick_export_customer_billing_columns_order', $billing_columns );

				$this->customers_visible_columns = array_merge( $this->customers_visible_columns, $billing_columns );
			}

			if ( YITH_WooCommerce_Quick_Export::get_instance()->show_customer_shipping_data ) {
				$shipping_columns = array(
					'shipping_first_name',
					'shipping_last_name',
					'shipping_company',
					'shipping_address_1',
					'shipping_address_2',
					'shipping_city',
					'shipping_postcode',
					'shipping_country',
					'shipping_state'
				);

				$shipping_columns                = apply_filters( 'yith_quick_export_customer_shipping_columns_order', $shipping_columns );
				$this->customers_visible_columns = array_merge( $this->customers_visible_columns, $shipping_columns );
			}

			$orders_columns = array(
				'id',
				'status',
				'order_date',
				'order_key',
				'order_currency',
				'prices_include_tax',
				'customer_ip_address',
				'customer_user_agent',
				'customer_user',
				'created_via',
				'order_version',
				'order_shipping',
				'billing_country',
				'billing_first_name',
				'billing_last_name',
				'billing_company',
				'billing_address_1',
				'billing_address_2',
				'billing_city',
				'billing_state',
				'billing_postcode',
				'billing_email',
				'billing_phone',
				'shipping_country',
				'shipping_first_name',
				'shipping_last_name',
				'shipping_company',
				'shipping_address_1',
				'shipping_address_2',
				'shipping_city',
				'shipping_state',
				'shipping_postcode',
				'cart_discount',
				'cart_discount_tax',
				'order_tax',
				'order_shipping_tax',
				'order_total',
				'recorded_sales',
				'recorded_coupon_usage_counts',
				'paid_date',
				'completed_date'

			);

			$this->orders_visible_columns = apply_filters( 'yith_quick_export_orders_columns_order', $orders_columns );

			//  Set the job settings
			$defaults = array(
				'name'           => __( "Export data", 'yith-woocommerce-quick-export' ),
				'autostart'      => 0,
				'export_items'   => array(),
				'export_on_date' => null,
				'export_on_time' => null,
				'recurrency'     => 'none',
				'start_filter'   => '2000-01-01',
				'end_filter'     => date( "Y-m-d H:i:s" )
			);

			$args = wp_parse_args( $args, $defaults );

			//  Map job settings to class fields
			foreach ( $args as $key => $value ) {
				$this->{$key} = $value;
			}
		}

		/**
		 * Export requested data to an archive file
		 */
		private function export_data() {
			$zip_filepath = $this->create_filename();

			$zip_folder = trailingslashit( str_replace( '.zip', '', $zip_filepath ) );

			if ( ! file_exists( $zip_folder ) ) {
				wp_mkdir_p( $zip_folder );
			}

			$files = array();

			if ( in_array( 'customers', $this->export_items ) ) {
				$customers_filepath = $zip_folder . 'customers.csv';

				file_put_contents( $customers_filepath, $this->render_customers() );
				$files[] = $customers_filepath;
			}

			if ( in_array( 'orders', $this->export_items ) ) {
				$orders_filepath = $zip_folder . 'orders.csv';

				file_put_contents( $orders_filepath, $this->render_orders() );
				$files[] = $orders_filepath;
			}

			if ( in_array( 'coupons', $this->export_items ) ) {
				$coupons_filepath = $zip_folder . 'coupons.csv';
				file_put_contents( $coupons_filepath, $this->render_coupons() );
				$files[] = $coupons_filepath;
			}

			if ( count( $files ) > 0 ) {
				$zip_file = yith_create_zip( $files,
					$zip_filepath,
					$zip_folder,
					true );

				YITH_DropBox::get_instance()->send_document_to_dropbox( $zip_filepath );

				return $zip_filepath;
			}

			return null;
		}

		/**
		 * Start execution of the job
		 */
		public function start( $silent_mode = false ) {

			//  The current job can be an auto start exportation job or a scheduled one.
			if ( $silent_mode ) {
				$result = $this->export_data();

				return $result;
			}

			if ( $this->autostart ) {
				//  Start the job now and send it to the browser so it can
				//  be downloaded.

				$result = $this->export_data();
				if ( ! is_null( $result ) ) {
					yith_download_file( $result );
				}
			} else {
				//  Set a univocal id for every job to be scheduled
				$this->id = round( microtime( true ) * 1000 );

				$start_job_time = strtotime( $this->export_on_date . " " . $this->export_on_time ) - ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );

				$job_args = serialize( $this );
				$temp     = unserialize( $job_args );

				$scheduled_hook = 'ywqe_scheduled_export';

				if ( "none" === $this->recurrency ) {
					wp_schedule_single_event( $start_job_time, $scheduled_hook, array( $job_args ) );
				} else {
					//Schedule the event
					wp_schedule_event( $start_job_time, $this->recurrency, $scheduled_hook, array( $job_args ) );
				}
			}
		}

		/**
		 * Build a CSV row for columns title
		 *
		 * @param $columns columns to be shown
		 *
		 * @return string CSV formatted row
		 */
		protected function get_csv_columns_title_row( $columns ) {

			$csv_title = '';
			foreach ( $columns as $column ) {
				$csv_title .= ucwords( strtolower( $column ) ) . YITH_WooCommerce_Quick_Export::get_instance()->fields_separator;
			}

			$csv_title .= $this->newline_separator;
			return $csv_title;
		}

		/**
		 * Build a CSV row for a specific customer
		 *
		 * @param $customer
		 */
		protected function get_customer_csv( $customer ) {

			$csv_row = '';

			foreach ( $this->customers_visible_columns as $column ) {
				$csv_row .= '"' . trim( $customer->{$column} ) . '"' . $this->fields_separator;
			}

			return $csv_row;
		}

		/**
		 * Build a CSV row for a specific order
		 *
		 * @param $order
		 */
		protected function get_order_csv( $order ) {

			$csv_row = '';
			foreach ( $this->orders_visible_columns as $column ) {
				$csv_row .= '"' . trim( $order->{$column} ) . '"' . $this->fields_separator;
			}

			return $csv_row;
		}

		/**
		 * Extract customers for the specific time interval and retrieve a CSV formatted text
		 *
		 * @return string CSV formatted text
		 */
		protected function render_customers() {
			$args = array(
				'role'    => 'customer',
				'fields'  => 'all_with_meta',
				'orderby' => 'user_registered',
				'order'   => 'DEC',
			);

			$customers = get_users( apply_filters( 'yith_quick_export_render_customers_args', $args ) );

			$customer_csv = $this->get_csv_columns_title_row( $this->customers_visible_columns );
			foreach ( $customers as $k => $customer ) {
				if ( $this->in_interval( $customer->user_registered ) ) {

					//  Add customer informations
					$customer_csv .= $this->get_customer_csv( $customer );
					//  close row
					$customer_csv .= $this->newline_separator;
				}
			}

			return $customer_csv;
		}

		/**
		 * Extract orders for the specific time interval and retrieve a CSV formatted text
		 *
		 * @return string CSV formatted text
		 */
		protected function render_orders() {

			$args = array(
				'posts_per_page' => - 1,
				'post_type'      => 'shop_order',
				'post_status'    => array_keys( wc_get_order_statuses() ),
				'orderby'        => 'post_date',
				'order'          => 'ASC',
			);

			$orders = get_posts( apply_filters( 'yith_quick_export_render_orders_args', $args ) );

			$orders_csv = $this->get_csv_columns_title_row( $this->orders_visible_columns );

			foreach ( $orders as $order ) {
				$order = new WC_Order( $order );

				if ( $this->in_interval( $order->order_date ) ) {
					//  Add orders informations
					$orders_csv .= $this->get_order_csv( $order );

					//  close row
					$orders_csv .= $this->newline_separator;
				}
			}

			return $orders_csv;
		}

		private function in_interval( $date, $start_interval = null, $end_interval = null ) {
			if ( is_null( $start_interval ) ) {
				$start_interval = $this->start_filter;
			}

			if ( is_null( $end_interval ) ) {
				$end_interval = $this->end_filter;
			}

			if ( strtotime( $date ) < strtotime( $start_interval ) ) {
				return false;
			}

			if ( strtotime( $date ) > ( strtotime( $end_interval . "+1 day" ) ) ) {
				return false;
			}

			return true;
		}

		/**
		 * Extract coupons for the specific time interval and retrieve a CSV formatted text
		 *
		 * @return string CSV formatted text
		 */
		protected function render_coupons() {
			$args = array(
				'posts_per_page' => - 1,
				'post_type'      => 'shop_order',
				'post_status'    => array_keys( wc_get_order_statuses() ),
				'orderby'        => 'post_date',
				'order'          => 'ASC',
			);

			$orders      = get_posts( apply_filters( 'yith_quick_export_render_orders_args', $args ) );
			$coupons_csv = '';

			$coupons_columns = array(
				'order_id',
				'order_date',
				'coupon_id',
				'discount_type',
				'coupon_amount',
				'coupon_name',
				'order_discount'
			);

			$coupons_csv = $this->get_csv_columns_title_row( $coupons_columns );

			foreach ( $orders as $order ) {
				$order = new WC_Order( $order );

				if ( $this->in_interval( $order->order_date ) ) {
					//  if there aren't coupon used in this order, skip it
					$coupons = $order->get_used_coupons();
					if ( count( $coupons ) == 0 ) {
						continue;
					}

					foreach ( $coupons as $coupon ) {

						$wc_coupon = new WC_Coupon( $coupon );

						foreach ( $coupons_columns as $column ) {
							switch ( $column ) {
								case 'order_id' :
									$coupons_csv .= sprintf( '"%d"%s', $order->id, $this->fields_separator );
									break;

								case 'order_date' :
									$coupons_csv .= sprintf( '"%s"%s', $order->order_date, $this->fields_separator );
									break;

								case 'coupon_id' :
									$coupons_csv .= sprintf( '"%s"%s', $wc_coupon->id, $this->fields_separator );
									break;

								case 'discount_type' :
									$coupons_csv .= sprintf( '"%s"%s', $wc_coupon->discount_type, $this->fields_separator );
									break;

								case 'coupon_amount' :
									$coupons_csv .= sprintf( '"%s"%s', $wc_coupon->coupon_amount, $this->fields_separator );
									break;

								case 'coupon_name' :
									$coupons_csv .= sprintf( '"%s"%s', $wc_coupon->code, $this->fields_separator );
									break;

								case 'order_discount' :
									$coupons_csv .= sprintf( '"%s"%s', $this->get_coupon_amount_for_order( $order->id, $wc_coupon->code ), $this->fields_separator );
									break;

								default :
									$value = apply_filters( 'yith_quick_export_coupon_column', '', $order, $coupon, $column );
									$coupons_csv .= sprintf( '"%s"%s', $value, $this->fields_separator );
							}
						}
						$coupons_csv .= $this->newline_separator;
					}
				}
			}

			return $coupons_csv;
		}

		/**
		 * Retrieve the amount of discount for a coupon in a specific order
		 *
		 * @param $order_id the id of the order in which the coupon was used
		 * @param $coupon_name the name of the coupon used
		 *
		 * @return float|int
		 */
		private function get_coupon_amount_for_order( $order_id, $coupon_name ) {

			global $wpdb;

			$prepare_query = "
				SELECT meta_value as amount
				FROM {$wpdb->prefix}woocommerce_order_items itm
				LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta meta
				ON itm.order_item_id = meta.order_item_id
				WHERE order_item_type = 'coupon'
				AND order_id = %s
				AND order_item_name='%s'
				AND meta_key='discount_amount'";

			$results = $wpdb->get_results( $wpdb->prepare( $prepare_query, $order_id, $coupon_name ) );

			if ( isset( $results[0] ) ) {
				return round( $results[0]->amount, 2 );
			} else {
				return 0;
			}
		}

		/**
		 * Create a folder with a specific pattern, used to store files created with an exportation job
		 *
		 * @return string folder name
		 */
		public function create_storing_folder( $date = null ) {

			$folder_pattern = get_option( 'ywqe_folder_format' );
			$date           = isset( $date ) ? $date : getdate();

			$folder_pattern = str_replace(
				array(
					'{{year}}',
					'{{month}}',
					'{{day}}',
					'{{hours}}',
					'{{minutes}}',
					'{{seconds}}',
				),
				array(
					$date['year'],
					sprintf( "%02d", $date['mon'] ),
					sprintf( "%02d", $date['mday'] ),
					sprintf( "%02d", $date['hours'] ),
					sprintf( "%02d", $date['minutes'] ),
					sprintf( "%02d", $date['seconds'] ),
				),
				$folder_pattern );

			if ( ! file_exists( YITH_YWQE_DOCUMENT_SAVE_DIR . $folder_pattern ) ) {
				wp_mkdir_p( YITH_YWQE_DOCUMENT_SAVE_DIR . $folder_pattern );
			}

			return YITH_YWQE_DOCUMENT_SAVE_DIR . $folder_pattern;
		}

		/**
		 * Return the filename associated to the document, based on plugin settings.
		 *
		 * @return mixed|string|void
		 */
		public function create_filename( $date = null ) {

			$date   = isset( $date ) ? $date : getdate();
			$folder = $this->create_storing_folder( $date );

			$pattern = get_option( 'ywqe_filename_format' );
			$pattern = str_replace(
				array(
					'{{year}}',
					'{{month}}',
					'{{day}}',
					'{{hours}}',
					'{{minutes}}',
					'{{seconds}}',
				),
				array(
					$date['year'],
					sprintf( "%02d", $date['mon'] ),
					sprintf( "%02d", $date['mday'] ),
					sprintf( "%02d", $date['hours'] ),
					sprintf( "%02d", $date['minutes'] ),
					sprintf( "%02d", $date['seconds'] ),
				),
				$pattern );

			$pattern_loop = $pattern;

			$i = 0;
			//  Ensure the filename is univoque
			do {

				if ( $i ) {
					$pattern_loop = sprintf( "%s(%s)", $pattern, $i );
				}

				$filepath = sprintf( '%s/%s.zip', $folder, $pattern_loop );
				$i ++;
			} while ( file_exists( $filepath ) );

			return $filepath;
		}
	}
}