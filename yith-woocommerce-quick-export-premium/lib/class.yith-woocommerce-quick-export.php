<?php
/**
 * YITH_WooCommerce_Quick_Export base class
 *
 * @author Your Inspiration Themes
 * @package YITH WooCommerce Quick Export
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WooCommerce_Quick_Export' ) ) {
	/**
	 * Admin class.
	 * The class manage all the Frontend behaviors.
	 *
	 * @since 1.0.0
	 */
	class YITH_WooCommerce_Quick_Export {

		public $show_customer_data = true;
		public $show_customer_billing_data = true;
		public $show_customer_shipping_data = true;

		public $fields_separator = ";";
		public $line_separator = "\r\n";

		/**
		 * Single instance of the class
		 *
		 * @since 1.0.0
		 */
		protected static $instance;


		/**
		 * Returns single instance of the class
		 *
		 * @since 1.0.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @access public
		 * @since 1.0.0
		 */
		public function __construct() {

			$dropbox = YITH_DropBox::get_instance();
			$dropbox->initialize(
				'gcg01lxqfi1sfvo',
				'e8s7yy6q3miwek5',
				YITH_YWQE_DOCUMENT_SAVE_DIR );

			$dropbox->dropbox_accesstoken = get_option( 'ywqe_dropbox_access_token' );

			$this->show_customer_data          = true;
			$this->show_customer_billing_data  = true;
			$this->show_customer_shipping_data = true;

			//Actions

			add_action( 'admin_enqueue_scripts', array( $this, 'back_end_scripts' ) );

			/**
			 * Show custom tab for exportation jobs
			 */
			add_action( 'ywqe_exportation_jobs_tab', array( $this, 'show_exportation_jobs' ) );

			/**
			 * Show custom tab for exportation jobs
			 */
			add_action( 'ywqe_exportation_history_tab', array( $this, 'show_exportation_history' ) );

			add_action( 'init', array( $this, 'check_actions_on_init' ) );

			add_filter( 'cron_schedules', array( $this, 'add_custom_schedule_recurrence' ) );
			add_action( 'ywqe_scheduled_export', array( $this, 'start_scheduled_job' ) );

			/**
			 * Let the user to download a file from the scheduled job history
			 */
			add_action( "admin_action_download_item", array( $this, 'download_item' ) );

			/**
			 * Let the user to delete entries from the scheduled job history
			 */
			add_action( "admin_action_delete_history_item", array( $this, 'delete_history_item' ) );

			/**
			 * Let the user to delete a previous scheduled exportation
			 */
			add_action( "admin_action_delete_job", array( $this, 'delete_job' ) );
		}

		/**
		 * Delete an previous scheduled exportation
		 */
		public function delete_job() {

			if ( isset( $_GET['id'] ) && isset( $_GET['sig'] ) && isset( $_GET['next_run'] ) ) {
				$id       = $_GET['id'];
				$sig      = $_GET['sig'];
				$next_run = $_GET['next_run'];

				$this->delete_cron( $id, $sig, $next_run );
				wp_redirect( admin_url( "admin.php?page=yith_woocommerce_quick_export&tab=exportation-jobs" ) );
			}
		}

		/**
		 * Deletes a cron event.
		 *
		 * @param string $name The hookname of the event to delete.
		 */
		function delete_cron( $to_delete, $sig, $next_run ) {
			$crons = _get_cron_array();
			if ( isset( $crons[ $next_run ][ $to_delete ][ $sig ] ) ) {
				$args = $crons[ $next_run ][ $to_delete ][ $sig ]['args'];
				wp_unschedule_event( $next_run, $to_delete, $args );

				return true;
			}

			return false;
		}

		/**
		 * Send the requested archive file to the browser
		 */
		public function download_item() {
			if ( ! isset( $_GET["item_id"] ) ) {
				return;
			}

			$job_history = get_option( "ywqe_job_history", array() );
			foreach ( $job_history as $key => $job ) {
				if ( $job["file_id"] == $_GET["item_id"] ) {
					yith_download_file( $job["file_path"] );
				}
			}
		}

		/**
		 * Remove items from the history
		 *
		 * @param $items array of items to be deleted
		 */
		public function delete_history_items( $items ) {

			$job_history = get_option( "ywqe_job_history", array() );
			foreach ( $job_history as $key => $job ) {
				if ( in_array( $job["file_id"], $items ) ) {

					unset( $job_history[ $key ] );

					//  remove file and folder where the archive file is stored
					$zip_folder = trailingslashit( str_replace( '.zip', '', $job["file_path"] ) );
					if ( file_exists( $job["file_path"] ) ) {
						wp_delete_file( $job["file_path"] );
					}

					if ( file_exists( $zip_folder ) ) {
						yith_delete_folder( $zip_folder );
					}
				}
			}
			update_option( "ywqe_job_history", $job_history );
		}

		/**
		 * Send the requested archive file to the browser
		 */
		public function delete_history_item() {
			if ( ! isset( $_GET["item_id"] ) ) {
				return;
			}

			$this->delete_history_items( array( $_GET["item_id"] ) );

			wp_redirect( admin_url( "admin.php?page=yith_woocommerce_quick_export&tab=exportation-history" ) );
		}

		/**
		 * Add custom recurrence pattern to the default ones.
		 *
		 * @param $schedules current schedule recurrence
		 *
		 * @return mixed
		 */
		public function add_custom_schedule_recurrence( $schedules ) {
			$schedules['weekly'] = array(
				'interval' => 7 * 24 * 60 * 60, //7 days * 24 hours * 60 minutes * 60 seconds
				'display'  => __( 'Weekly', 'yith-woocommerce-quick-export' )
			);

			$schedules['monthly'] = array(
				'interval' => 30 * 24 * 60 * 60, //30 days * 24 hours * 60 minutes * 60 seconds
				'display'  => __( 'Monthly', 'yith-woocommerce-quick-export' )
			);

			return $schedules;
		}

		public function start_scheduled_job( $args ) {

			$job = unserialize( $args );
			//  Start an exportation now, in silent mode

			$exported_file = $job->start( 1 );
			//  Store path
			$job_history = get_option( "ywqe_job_history", array() );

			$file_id = round( microtime( true ) * 1000 );

			array_unshift( $job_history, array(
				"job_id"          => $job->id,
				"name"            => $job->name,
				"generation_date" => date( "Y-m-d H:i:s", time() ),
				"file_id"         => $file_id,
				"file_path"       => $exported_file
			) );

			update_option( "ywqe_job_history", $job_history );
		}

		/**
		 * Show the template showing exportation jobs created by the user
		 */
		public function show_exportation_jobs() {

			include( YITH_YWQE_TEMPLATES_DIR . 'admin/exportation-jobs.php' );
		}

		/**
		 * Show the template showing the results of previous exportation jobs
		 */
		public function show_exportation_history() {
			include( YITH_YWQE_TEMPLATES_DIR . 'admin/exportation-history.php' );
		}

		/**
		 * Check for POST of a new exportation job
		 */
		public function check_actions_on_init() {

			/**
			 * Check if there is a request for  a reset dropbox action
			 */
			if ( isset( $_POST["job_action"] ) && ( "trash" == $_POST["job_action"] ) ) {
				if ( isset( $_POST["job_list"] ) && is_array( $_POST["job_list"] ) ) {
					$this->delete_history_items( $_POST["job_list"] );

					return;
				}
			}

			/**
			 * Check if there is a request for  a reset dropbox action
			 */
			if ( isset( $_GET[ YWQE_RESET_DROPBOX ] ) ) {
				YITH_DropBox::get_instance()->disable_dropbox_backup();
				delete_option( 'ywqe_dropbox_access_token' );
				wp_redirect( esc_url_raw( remove_query_arg( YWQE_RESET_DROPBOX ) ) );
			}

			//  *****  start form validation
			if ( isset( $_POST["ywqe_folder_format"] ) ) {
				if ( ( strpbrk( $_POST["ywqe_folder_format"], "\?%*:|\"<>" ) != false ) ||
				     ( strpbrk( $_POST["ywqe_filename_format"], "\?%*:|\"<>" ) != false )
				) {
					wp_die( __( "Please enter a valid folder/path name", 'yith-woocommerce-quick-export' ) );
				}
			}

			if ( ! isset( $_POST["ywqe_schedule_exportation"] ) ) {
				if ( ! empty( $_POST["ywqe_export_start_date"] ) && ! empty( $_POST["ywqe_export_end_date"] ) ) {
					if ( strtotime( $_POST["ywqe_export_start_date"] ) > strtotime( $_POST["ywqe_export_end_date"] ) ) {
						wp_die( __( "Starting date must be earlier than ending date", 'yith-woocommerce-quick-export' ) );
					}
				}
			}
			//  ****    end form validation

			if ( isset( $_POST["ywqe_new_job"] ) ) {
				//  Prepare the parameters array
				$args = array(
					'export_items' => array()
				);

				if ( isset( $_POST["ywqe_export_orders"] ) ) {
					$args['export_items'][] = 'orders';
				}

				if ( isset( $_POST["ywqe_export_customers"] ) ) {
					$args['export_items'][] = 'customers';
				}

				if ( isset( $_POST["ywqe_export_coupons"] ) ) {
					$args['export_items'][] = 'coupons';
				}

				if ( isset( $_POST["ywqe_schedule_exportation"] ) ) {

					// Schedule a new job
					if ( ! empty( $_POST["ywqe_export_on_date"] ) ) {
						$args["export_on_date"] = $_POST["ywqe_export_on_date"];
					}

					if ( ! empty( $_POST["ywqe_export_on_time"] ) ) {
						$args["export_on_time"] = $_POST["ywqe_export_on_time"];
					}

					if ( ! empty( $_POST["ywqe_recurrence_type"] ) ) {
						$args["recurrency"] = $_POST["ywqe_recurrence_type"];
					}

					if ( ! empty( $_POST["ywqe_export_title"] ) ) {
						$args["name"] = $_POST["ywqe_export_title"];
					}

				} else {
					//  Start now with optional start and end date filters

					$args['autostart'] = 1;

					if ( ! empty( $_POST["ywqe_export_start_date"] ) ) {
						$args["start_filter"] = $_POST["ywqe_export_start_date"];
					}

					if ( ! empty( $_POST["ywqe_export_end_date"] ) ) {
						$args["end_filter"] = $_POST["ywqe_export_end_date"];
					}
				}

				$job = new YWQE_Export_Job( $args );
				$job->start();
				wp_redirect( remove_query_arg( "create-job" ) );
			}
		}

		/**
		 * Enqueue admin styles and scripts
		 *
		 * @access public
		 * @return void
		 * @since 1.0.0
		 */
		public function back_end_scripts() {

			wp_enqueue_style( 'ywqe_style', YITH_YWQE_ASSETS_URL . '/css/ywqe_style.css' );

			//  register and enqueue ajax calls related script file
			wp_register_script( 'ywqe_script', YITH_YWQE_ASSETS_URL . '/js/ywqe_script.js', array( 'jquery' ) );

			wp_localize_script( 'ywqe_script', 'messages', array(
				'schedulation_time' => __( "Please enter a valid schedule time.", 'yith-woocommerce-quick-export' ),
				'valid_interval'    => __( "Starting date must be earlier than/the same of ending date.", 'yith-woocommerce-quick-export' ),
			) );
			wp_enqueue_script( 'ywqe_script' );
		}
	}
}