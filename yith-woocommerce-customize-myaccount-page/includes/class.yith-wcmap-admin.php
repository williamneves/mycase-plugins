<?php
/**
 * Admin class
 *
 * @author Yithemes
 * @package YITH WooCommerce Customize My Account Page
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCMAP' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCMAP_Admin' ) ) {
	/**
	 * Admin class.
	 * The class manage all the admin behaviors.
	 *
	 * @since 1.0.0
	 */
	class YITH_WCMAP_Admin {

		/**
		 * Single instance of the class
		 *
		 * @var \YITH_WCMAP_Admin
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Plugin options
		 *
		 * @var array
		 * @access public
		 * @since 1.0.0
		 */
		public $options = array();

		/**
		 * Add endpoint action
		 *
		 * @var string
		 * @access protected
		 * @since 1.0.0
		 */
		public $_check_endpoint_action = 'yith_wcmap_check_endpoint';

		/**
		 * Add endpoint action
		 *
		 * @var string
		 * @access protected
		 * @since 1.0.0
		 */
		public $_remove_endpoint_action = 'yith_wcmap_remove_endpoint';

		/**
		 * Plugin version
		 *
		 * @var string
		 * @since 1.0.0
		 */
		public $version = YITH_WCMAP_VERSION;

		/**
		 * @var $_panel Panel Object
		 */
		protected $_panel;

		/**
		 * @var string Customize my account panel page
		 */
		protected $_panel_page = 'yith_wcmap_panel';

		/**
		 * Various links
		 *
		 * @var string
		 * @access public
		 * @since 1.0.0
		 */
		public $doc_url = 'https://yithemes.com/docs-plugins/yith-woocommerce-customize-myaccount-page';

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_WCMAP_Admin
		 * @since 1.0.0
		 */
		public static function get_instance(){
			if( is_null( self::$instance ) ){
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

			add_action( 'admin_menu', array( $this, 'register_panel' ), 5) ;

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

			//Add action links
			add_filter( 'plugin_action_links_' . plugin_basename( YITH_WCMAP_DIR . '/' . basename( YITH_WCMAP_FILE ) ), array( $this, 'action_links' ) );
			add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 4 );

			// endpoints
			add_action( 'woocommerce_admin_field_wcmap_endpoints', array( $this, 'wcmap_endpoints' ), 10, 1 );
			add_filter( 'woocommerce_admin_settings_sanitize_option_yith_wcmap_endpoint', array( $this, 'update_wcmap_endpoints' ), 10, 3 );

			// add endpoint ajax
			add_action( 'wp_ajax_' . $this->_check_endpoint_action, array( $this, 'check_endpoint_ajax' ) );
			add_action( 'wp_ajax_nopriv_' . $this->_check_endpoint_action, array( $this, 'check_endpoint_ajax' ) );

			// remove endpoint ajax
			add_action( 'wp_ajax_' . $this->_remove_endpoint_action, array( $this, 'remove_endpoint_ajax' ) );
			add_action( 'wp_ajax_nopriv_' . $this->_remove_endpoint_action, array( $this, 'remove_endpoint_ajax' ) );

			// let's filter the media library
			add_action( 'pre_get_posts', array( $this, 'filter_media_library' ), 10, 1 );

			// Register plugin to licence/update system
			add_action( 'wp_loaded', array( $this, 'register_plugin_for_activation' ), 99 );
			add_action( 'admin_init', array( $this, 'register_plugin_for_updates' ) );

			// reset options
			add_action( 'admin_init', array( $this, 'reset_endpoints_options' ), 1 );

		}

		/**
		 * Enqueue scripts
		 *
		 * @since 1.0.0
		 * @author Francesco Licandro
		 */
		public function enqueue_scripts() {

			$min        = ( ! defined('SCRIPT_DEBUG') || ! SCRIPT_DEBUG ) ? '.min' : '';

			wp_register_style( 'yith_wcmap', YITH_WCMAP_ASSETS_URL . '/css/ywcmap-admin.css' );
			wp_register_script( 'yith_wcmap', YITH_WCMAP_ASSETS_URL . '/js/ywcmap-admin' . $min . '.js', array( 'jquery', 'jquery-ui-sortable' ), false, true );

			// font awesome
			wp_register_style( 'font-awesome', YITH_WCMAP_ASSETS_URL . '/css/font-awesome.min.css' );

			if ( isset( $_GET['page'] ) && $_GET['page'] == 'yith_wcmap_panel' ) {
				wp_enqueue_script( 'jquery-ui' );
				wp_enqueue_script( 'jquery-ui-sortable' );

				wp_enqueue_style( 'font-awesome' );

				wp_enqueue_style( 'yith_wcmap' );
				wp_enqueue_script( 'yith_wcmap' );

				wp_localize_script( 'yith_wcmap', 'ywcmap', array(
					'ajaxurl'  	 => admin_url( 'admin-ajax.php' ),
					'action_check' => $this->_check_endpoint_action,
					'action_remove'	=> $this->_remove_endpoint_action,
					'error_msg'	 => __( 'An error has occurred or this endpoint already exists. Please try again.', 'yith-woocommerce-customize-myaccount-page' ),
					'show_lbl'	=> __( 'Show', 'yith-woocommerce-customize-myaccount-page' ),
					'hide_lbl'	=> __( 'Hide', 'yith-woocommerce-customize-myaccount-page' ),
					'loading'	=> '<img src="' . YITH_WCMAP_ASSETS_URL . '/images/wpspin_light.gif' . '">',
					'checked'	=> '<i class="fa fa-check"></i>',
					'error_icon' => '<i class="fa fa-times"></i>',
					'remove_alert' 	   => __( 'Are you sure you want to delete this endpoint?', 'yith-woocommerce-customize-myaccount-page' )
				));
			}
		}

		/**
		 * Action Links
		 *
		 * add the action links to plugin admin page
		 *
		 * @param $links | links plugin array
		 *
		 * @return   mixed Array
		 * @since    1.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 * @return mixed
		 * @use plugin_action_links_{$plugin_file_name}
		 */
		public function action_links( $links ) {
			$links[] = '<a href="' . admin_url( "admin.php?page={$this->_panel_page}" ) . '">' . __( 'Settings', 'yith-woocommerce-customize-myaccount-page' ) . '</a>';

			return $links;
		}

		/**
		 * Add a panel under YITH Plugins tab
		 *
		 * @return   void
		 * @since    1.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 * @use     /Yit_Plugin_Panel class
		 * @see      plugin-fw/lib/yit-plugin-panel.php
		 */
		public function register_panel() {

			if ( ! empty( $this->_panel ) ) {
				return;
			}

			$admin_tabs = array(
				'general' 	=> __( 'Settings', 'yith-woocommerce-customize-myaccount-page' ),
				'endpoints' => __( 'Endpoints', 'yith-woocommerce-customize-myaccount-page' )
			);

			$args = array(
				'create_menu_page' => true,
				'parent_slug'      => '',
				'page_title'       => __( 'Customize My Account Page', 'yith-woocommerce-customize-myaccount-page' ),
				'menu_title'       => __( 'Customize My Account Page', 'yith-woocommerce-customize-myaccount-page' ),
				'capability'       => 'manage_options',
				'parent'           => '',
				'parent_page'      => 'yit_plugin_panel',
				'page'             => $this->_panel_page,
				'admin-tabs'       => apply_filters( 'yith_wcmap_admin_tabs', $admin_tabs ),
				'options-path'     => YITH_WCMAP_DIR . '/plugin-options'
			);

			/* === Fixed: not updated theme  === */
			if( ! class_exists( 'YIT_Plugin_Panel_WooCommerce' ) ) {
				require_once( YITH_WCMAP_DIR . '/plugin-fw/lib/yit-plugin-panel-wc.php' );
			}

			$this->_panel = new YIT_Plugin_Panel_WooCommerce( $args );

		}

		/**
		 * Register plugins for activation tab
		 *
		 * @return void
		 * @since 2.0.0
		 */
		public function register_plugin_for_activation() {
			if ( ! class_exists( 'YIT_Plugin_Licence' ) ) {
				require_once( YITH_WCMAP_DIR . 'plugin-fw/licence/lib/yit-licence.php' );
				require_once( YITH_WCMAP_DIR . 'plugin-fw/licence/lib/yit-plugin-licence.php' );
			}

			YIT_Plugin_Licence()->register( YITH_WCMAP_INIT, YITH_WCMAP_SECRET_KEY, YITH_WCMAP_SLUG );
		}

		/**
		 * Register plugins for update tab
		 *
		 * @return void
		 * @since 2.0.0
		 */
		public function register_plugin_for_updates() {
			if( ! class_exists( 'YIT_Upgrade' ) ){
				require_once( YITH_WCMAP_DIR . 'plugin-fw/lib/yit-upgrade.php' );
			}

			YIT_Upgrade()->register( YITH_WCMAP_SLUG, YITH_WCMAP_INIT );
		}

		/**
		 * plugin_row_meta
		 *
		 * add the action links to plugin admin page
		 *
		 * @param $plugin_meta
		 * @param $plugin_file
		 * @param $plugin_data
		 * @param $status
		 *
		 * @return   Array
		 * @since    1.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 * @use plugin_row_meta
		 */
		public function plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status ) {

			if ( defined( 'YITH_WCMAP_INIT') && YITH_WCMAP_INIT == $plugin_file ) {
				$plugin_meta[] = '<a href="' . $this->doc_url . '" target="_blank">' . __( 'Plugin Documentation', 'yith-woocommerce-customize-myaccount-page' ) . '</a>';
			}

			return $plugin_meta;
		}

		/**
		 * Create new Woocommerce admin field
		 *
		 * @access public
		 * @param array $value
		 * @return void
		 * @since 1.0.0
		 */
		public function wcmap_endpoints( $value ) {

			// get endpoints
			$args = apply_filters( 'yith_wcmap_admin_endpoints_template', array(
				'value'		=> $value,
				'endpoints' => yith_wcmap_get_endpoints()
			));

			wc_get_template('admin-endpoints.php', $args, '', YITH_WCMAP_TEMPLATE_PATH . '/admin/' );
		}

		/**
		 * Create endpoint key
		 *
		 * @access public
		 * @since 1.0.0
		 * @param string $key
		 * @return string
		 * @author Francesco Licandro
		 */
		public function create_endpoint_key( $key ) {

			// build endpoint key
			$endpoint_key = strtolower( $key );
			$endpoint_key = trim( $endpoint_key );
			// clear from space and add -
			$endpoint_key = preg_replace( '/[^a-z]/', '-', $endpoint_key );

			return $endpoint_key;
		}

		/**
		 * Save the admin field
		 *
		 * @access public
		 * @param mixed $value
		 * @param mixed $option
		 * @param mixed $raw_value
		 * @return mixed
		 * @since 1.0.0
		 */
		public function update_wcmap_endpoints( $value, $option, $raw_value ) {

			$endpoints = explode( ',', $value );

			foreach( $endpoints as &$endpoint ) {

				// check for key
				$endpoint = $this->create_endpoint_key( $endpoint );

				$options = isset( $_POST[ $option['id'] . '_' . $endpoint ] ) ? $_POST[ $option['id'] . '_' . $endpoint ] : $this->get_default_endpoint_options( $endpoint );

				$options['active'] = isset( $options['active'] );
				$options['slug']   = ( isset( $options['slug'] ) && ! empty( $options['slug'] ) ) ? $this->create_endpoint_key( $options['slug'] ) : $endpoint;
				$options['content'] = stripslashes( $options['content'] );

				update_option( $option['id'] . '_' . $endpoint, $options );

				// synchronize wc options
				update_option( 'woocommerce_myaccount_'. str_replace( '-', '_', $endpoint ) .'_endpoint', $options['slug'] );

			}

			// reset options for rewrite rules
			update_option( 'yith-wcmap-flush-rewrite-rules', 1 );

			return implode(',', $endpoints );
		}

		/**
		 * Get default options for new endpoints
		 *
		 * @access public
		 * @since 1.0.0
		 * @param string $endpoint
		 * @return array
		 * @author Francesco Licandro
		 */
		public function get_default_endpoint_options( $endpoint ) {

			$endpoint_name = preg_replace( '/[^a-z]/', ' ', $endpoint );
			$endpoint_name = trim( $endpoint_name );
			$endpoint_name = ucfirst( $endpoint_name );

			// build endpoint options
			$options = array(
				'slug'		=> $endpoint,
				'active'	=> true,
				'label'		=> $endpoint_name,
				'icon'		=> '',
				'content'	=> ''
			);

			return $options;
		}

		/**
		 * Add endpoints using ajax
		 *
		 * @access public
		 * @since 1.0.0
		 * @author Francesco Licandro
		 */
		public function check_endpoint_ajax(){

			if( ! ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == $this->_check_endpoint_action ) || ! isset( $_REQUEST['endpoint_name'] ) ) {
				die();
			}

			// build endpoint key
			$endpoint_key = $this->create_endpoint_key( $_REQUEST['endpoint_name'] );

			// check if endpoints already exists
			$res = yith_wcmap_endpoint_already_exists( $endpoint_key );

			echo wp_json_encode( array(
				'error'	=> $res,
				'endpoint'	=> $endpoint_key
			) );

			die();
		}

		/**
		 * Remove from list custom endpoint
		 *
		 * @access public
		 * @since 1.0.0
		 * @author Francesco Licandro
		 */
		public function remove_endpoint_ajax() {
			if( ! ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == $this->_remove_endpoint_action ) || ! isset( $_REQUEST['endpoint'] ) ) {
				die();
			}

			$to_remove = $_REQUEST['endpoint'];
			$removed = false;

			$endpoints = get_option( 'yith_wcmap_endpoint', '' );
			$endpoints = explode(',', $endpoints );
			$endpoints = array_filter( $endpoints );

			foreach( $endpoints as $key => $endpoint ) {
				if( $endpoint == $to_remove ){
					unset( $endpoints[ $key ] );

					// also delete endpoint option
					delete_option( 'yith_wcmap_endpoint_' . $endpoint );

					// update main option
					update_option( 'yith_wcmap_endpoint', implode( ',', $endpoints ) );
					break;
				}
			}

			echo wp_json_encode( array(
				'success' => true
			));

			die();
		}

		/**
		 * Filter media library query form hide users avatar
		 *
		 * @access public
		 * @since 1.0.0
		 * @param object $q
		 * @author Francesco Licandro
		 */
		public function filter_media_library( $q ){

			$post_ids = get_option( 'yith-wcmap-users-avatar-ids', array() );
			$is_attachment = $q->get( 'post_type' ) == 'attachment';

			if( ! $is_attachment || empty( $post_ids ) || ! is_array( $post_ids )  )
				return;

			$this->_filter_media_library( $q, $post_ids );
		}

		/**
		 * Filter media library query
		 *
		 * @access private
		 * @since 1.0.0
		 * @param object $q
		 * @param array $post_ids Post to filter
		 * @author Francesco Licandro
		 */
		private function _filter_media_library( $q, $post_ids ) {

			$q->set( 'post__not_in', $post_ids );
		}

		/**
		 * Reset endpoints options
		 *
		 * @access public
		 * @since 1.0.0
		 * @author Francesco Licandro
		 */
		public function reset_endpoints_options() {

			if( isset( $_REQUEST['yit-action'] ) && $_REQUEST['yit-action'] == 'wc-options-reset'
				&& isset( $_POST['yith_wc_reset_options_nonce'] ) && wp_verify_nonce( $_POST['yith_wc_reset_options_nonce'], 'yith_wc_reset_options_'. $this->_panel_page )){

				$options = get_option( 'yith_wcmap_endpoint', '' );
				$options = explode( ',', $options );
				$options = array_filter( $options );

				foreach( $options as $option ) {
					delete_option( 'yith_wcmap_endpoint_' . $option );
				}

				// delete also endpoints flush option
				delete_option( 'yith-wcmap-flush-rewrite-rules' );
			}
		}

	}
}
/**
 * Unique access to instance of YITH_WCMAP_Admin class
 *
 * @return \YITH_WCMAP_Admin
 * @since 1.0.0
 */
function YITH_WCMAP_Admin(){
	return YITH_WCMAP_Admin::get_instance();
}