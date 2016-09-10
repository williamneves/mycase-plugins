<?php
/**
 * Frontend class
 *
 * @author Yithemes
 * @package YITH WooCommerce Customize My Account Page
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCMAP' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCMAP_Frontend' ) ) {
	/**
	 * Frontend class.
	 * The class manage all the frontend behaviors.
	 *
	 * @since 1.0.0
	 */
	class YITH_WCMAP_Frontend {

		/**
		 * Single instance of the class
		 *
		 * @var \YITH_WCMAP_Frontend
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Plugin version
		 *
		 * @var string
		 * @since 1.0.0
		 */
		public $version = YITH_WCMAP_VERSION;

		/**
		 * Page templates
		 *
		 * @var string
		 * @since 1.0.0
		 */
		protected $_is_myaccount = false;

		/**
		 * Menu Shortcode
		 *
		 * @access protected
		 * @var string
		 */
		protected $_shortcode_name = 'yith-wcmap-menubar';

		/**
		 * Page templates
		 *
		 * @var string
		 * @since 1.0.0
		 */
		protected $_add_avatar_action = 'yith_wcmap_add_avatar';

		/**
		 * My account endpoint
		 *
		 * @var string
		 * @since 1.0.0
		 */
		protected $_menu_endpoints = array();

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_WCMAP_Frontend
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

			// plugin init
			add_action( 'init', array( $this, 'init' ) );

			// enqueue scripts and styles
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 15 );

			// check if is shortcode my-account
			add_action( 'template_redirect', array( $this, 'check_myaccount' ), 1 );
			// redirect to the default endpoint
			add_action( 'template_redirect', array( $this, 'redirect_to_default' ), 2 );
			// add custom endpoints content
			add_action( 'template_redirect', array( $this, 'add_custom_endpoint_content' ), 99 );
			// add account menu
			add_action( 'template_redirect', array( $this, 'add_account_menu' ), 50 );

			// shortcode for print my account sidebar
			add_shortcode( $this->_shortcode_name, array( $this, 'my_account_menu' ) );

			// add avatar
			add_action( 'wp_ajax_' . $this->_add_avatar_action, array( $this, 'add_avatar' ) );
			add_action( 'wp_ajax_nopriv_' . $this->_add_avatar_action, array( $this, 'add_avatar' ) );

			add_action( 'init', array( $this, 'add_avatar' ) );

			// shortcodes for my-downloads and view order content
			add_shortcode( 'my_downloads_content', array( $this, 'my_downloads_content' ) );
			add_shortcode( 'view_order_content', array( $this, 'view_order_content' ) );

			// mem if is my account page
			add_action( 'shutdown', array( $this, 'save_is_my_account' ) );
		}

		/**
		 * Init plugins variable
		 *
		 * @access public
		 * @since 1.0.0
		 * @author Francesco Licandro
		 */
		public function init() {

			$this->_menu_endpoints = yith_wcmap_get_endpoints();

			// first register string for translations then remove disable
			foreach( $this->_menu_endpoints as $endpoint => &$options ) {
				// register string for translation with WPML
				do_action( 'wpml_register_single_string', 'Plugins', 'plugin_yit_wcmap_' . $endpoint, $options['label'] );

				if( ! $options['active'] ){
					unset( $this->_menu_endpoints[$endpoint] );
				}
			}

			// remove theme sidebar
			if( defined('YIT') && YIT ) {
				remove_action( 'yit_content_loop', 'yit_my_account_template', 5 );
				// also remove the my-account template
				$my_account_id = wc_get_page_id( 'myaccount' );
				if ( 'my-account.php' == get_post_meta( $my_account_id, '_wp_page_template', true ) ) {
					update_post_meta( $my_account_id, '_wp_page_template', 'default' );
				}
			}
		}

		/**
		 * Enqueue scripts and styles
		 *
		 * @access public
		 * @since 1.0.0
		 * @author Francesco Licandro
		 */
		public function enqueue_scripts(){

			if( ! $this->_is_myaccount ){
				return;
			};

			$paths      = apply_filters( 'yith_wcmap_stylesheet_paths', array( WC()->template_path() . 'yith-customize-myaccount.css', 'yith-customize-myaccount.css' ) );
			$located    = locate_template( $paths, false, false );
			$search     = array( get_stylesheet_directory(), get_template_directory() );
			$replace    = array( get_stylesheet_directory_uri(), get_template_directory_uri() );
			$stylesheet = ! empty( $located ) ? str_replace( $search, $replace, $located ) : YITH_WCMAP_ASSETS_URL . '/css/ywcmap-frontend.css';
			$min        = ( ! defined('SCRIPT_DEBUG') || ! SCRIPT_DEBUG ) ? '.min' : '';

			wp_register_style( 'ywcmap-frontend', $stylesheet );

			wp_register_script( 'ywcmap-frontend', YITH_WCMAP_ASSETS_URL . '/js/ywcmap-frontend'. $min . '.js', array( 'jquery' ), false, true );
			// font awesome
			wp_register_style( 'font-awesome', YITH_WCMAP_ASSETS_URL . '/css/font-awesome.min.css' );

			$suffix               = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			$assets_path          = str_replace( array( 'http:', 'https:' ), '', WC()->plugin_url() ) . '/assets/';

			if( get_option( 'yith-wcmap-custom-avatar' ) == 'yes' ) {
				wp_enqueue_script('ywcmap_prettyPhoto', $assets_path . 'js/prettyPhoto/jquery.prettyPhoto' . $suffix . '.js', array('jquery'), '3.1.6', true);
				wp_enqueue_style('ywcmap_prettyPhoto_css', $assets_path . 'css/prettyPhoto.css');

				wp_enqueue_script( 'ywcmap-frontend' );
			}


			wp_enqueue_style( 'ywcmap-frontend' );

			wp_enqueue_style( 'font-awesome' );

			wp_localize_script( 'ywcmap-frontend', 'ywcmap', array(
				'ajaxurl'  	 => admin_url( 'admin-ajax.php' ),
				'action_add_avatar' => $this->_add_avatar_action,
			));

			$inline_css = '
				#my-account-menu .logout a,
				#my-account-menu-tab .logout a {
					color:' . get_option('yith-wcmap-logout-color') . ';
					background-color:' . get_option('yith-wcmap-logout-background') . ';
				}
				#my-account-menu .logout:hover a,
				#my-account-menu-tab .logout:hover a {
					color:' . get_option('yith-wcmap-logout-color-hover') . ';
					background-color:' . get_option('yith-wcmap-logout-background-hover') . ';
				}
				.myaccount-menu li a {
					color:' . get_option( 'yith-wcmap-menu-item-color' ). ';
				}
				.myaccount-menu li:hover a,
				.myaccount-menu li.active a {
					color:' . get_option( 'yith-wcmap-menu-item-color-hover' ). ';
				}
			';

			wp_add_inline_style( 'ywcmap-frontend', $inline_css );
		}

		/**
		 * Check if is page my-account and set class variable
		 *
		 * @access public
		 * @since 1.0.0
		 * @author Francesco Licandro
		 */
		public function check_myaccount() {

			global $post;

			if( ! is_null( $post ) && strpos( $post->post_content, '[woocommerce_my_account' ) !== false && is_user_logged_in() ) {
				$this->_is_myaccount = true;
			}
		}

		/**
		 * Redirect to default endpoint
		 *
		 * @access public
		 * @since 1.0.4
		 * @author Francesco Licandro
		 */
		public function redirect_to_default(){

			// exit if not my account
			if( ! $this->_is_myaccount || ! is_array( $this->_menu_endpoints ) ) {
				return;
			}

			$current_endpoint = WC()->query->get_current_endpoint();
			! $current_endpoint && $current_endpoint = 'dashboard'; // set dashboard if empty
			$default_endpoint = get_option( 'yith-wcmap-default-endpoint', 'dashboard' );
			$url = wc_get_page_permalink( 'myaccount' );
			$current_endpoint_active = false;

			// exit if current is default, if current is logout
			if( $current_endpoint == $default_endpoint || $current_endpoint == 'customer-logout' ){
				return;
			}

			foreach( $this->_menu_endpoints as $endpoint => $options ) {
				if( $options['slug'] == $current_endpoint || $endpoint == $current_endpoint ){
					$current_endpoint_active = true;
					break;
				}
			}

			// if request an active endpoints no redirect to default
			if( $current_endpoint_active && $current_endpoint != 'dashboard' ) {
				return;
			}

			if( ! get_option( 'yith_wcmap_is_my_account', true ) ) {
				$default_endpoint != 'dashboard' && $url = wc_get_endpoint_url( $default_endpoint, '', $url );

				wp_safe_redirect( $url );
				exit;
			}
		}

		/**
		 * Add custom endpoints content
		 *
		 * @access public
		 * @since 1.0.0
		 * @author Francesco Licandro
		 */
		public function add_custom_endpoint_content() {

			if( ! $this->_is_myaccount ){
				return;
			}

			global $wp, $post;

			$active = 'dashboard';

			if( empty( $this->_menu_endpoints ) ) {
				return;
			}

			// search for active endpoints
			if( is_array( $this->_menu_endpoints ) ) {
				foreach ( $this->_menu_endpoints as $endpoint => $endpoint_opts ) {

					if ( ! isset( $wp->query_vars[ $endpoint_opts['slug'] ] ) ) {
						continue;
					}

					$active = $endpoint;
				}
			}

			$active = apply_filters( 'yith_wcmap_current_endpoint', $active );

			// set endpoint title
			if( $active == 'view-quote' && ! empty( $wp->query_vars[$active] ) ) {
				$order_id           = $wp->query_vars[$active];
				$post->post_title   = sprintf( __( 'Quote #%s', 'yith-woocommerce-request-a-quote' ), $order_id );
			}
			elseif( ! empty( $this->_menu_endpoints[$active]['label'] ) && $active != 'dashboard' ) {
				$localized_label_text = apply_filters( 'wpml_translate_single_string', $this->_menu_endpoints[$active]['label'], 'Plugins', 'plugin_yit_wcmap_' . $active );
				$post->post_title = stripslashes( $localized_label_text );				
			}

			// first check in custom content
			if( ! empty( $this->_menu_endpoints[$active]['content'] ) && yith_wcmap_endpoint_already_exists( $active ) ) {
				$this->vc_compatibility( 'set', stripslashes( $this->_menu_endpoints[$active]['content'] ) );

				if( $active == 'my-wishlist' ) {
					add_filter( 'yith_wcwl_current_wishlist_view_params', array( $this, 'change_wishlist_view_params' ), 10, 1 );
				}


			}
		}

		/**
		 * Change view params for wishlist shortcode
		 *
		 * @since 1.0.6
		 * @param $params
		 * @author Francesco Licandro
		 * @return mixed
		 */
		public function change_wishlist_view_params( $params ) {

			$params = get_query_var( $this->_menu_endpoints['my-wishlist']['slug'], false );

			return $params;
		}

		/**
		 * If is my account add menu to content
		 *
		 * @access public
		 * @since 1.0.0
		 * @author Francesco Licandro
		 */
		public function add_account_menu() {

			if( $this->_is_myaccount ) {

				$post_content = $this->vc_compatibility('get');

				$position = get_option( 'yith-wcmap-menu-position', 'left' );
				$tab = get_option( 'yith-wcmap-menu-style', 'sidebar' ) == 'tab' ? '-tab' : '';
				$menu = '<div id="my-account-menu' . $tab . '" class="yith-wcmap position-' . $position .'">[' . $this->_shortcode_name . ']</div>';
				$post_content = '<div id="my-account-content" class="woocommerce">' . $post_content . '</div>';

				$content = ( $position == 'right' && $tab == '' ) ? $post_content . $menu : $menu . $post_content;
				// set new post content
				$this->vc_compatibility( 'set', $content );
			}
		}

		/**
		 * Output my-account shortcode
		 *
		 * @since 1.0.0
		 * @author Frnacesco Licandro
		 */
		public function my_account_menu() {

			$args = apply_filters( 'yith-wcmap-myaccount-menu-template-args', array(
				'endpoints' => $this->_menu_endpoints,
				'my_account_url' => get_permalink( wc_get_page_id( 'myaccount' ) ),
				'avatar'	=> get_option( 'yith-wcmap-custom-avatar' ) == 'yes'
			));

			ob_start();

			wc_get_template( 'ywcmap-myaccount-menu.php', $args, '', YITH_WCMAP_DIR . 'templates/' );

			return ob_get_clean();

		}

		/**
		 * Add user avatar
		 *
		 * @access public
		 * @since 1.0.0
		 * @author Francesco Licandro
		 */
		public function add_avatar(){

			if( ! isset( $_FILES['ywcmap_user_avatar'] ) || ! wp_verify_nonce( $_POST['_nonce'], 'wp_handle_upload' ) )
				return;

			// required file
			if ( ! function_exists( 'media_handle_upload' )  ) {
				require_once( ABSPATH . 'wp-admin/includes/media.php' );
			}
			if( ! function_exists( 'wp_handle_upload' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/file.php' );
			}
			if( ! function_exists('wp_generate_attachment_metadata' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/image.php' );
			}

			$media_id = media_handle_upload( 'ywcmap_user_avatar', 0 );

			if( is_wp_error( $media_id ) ) {
				return;
			}

			// save media id for filter query in media library
			$medias = get_option('yith-wcmap-users-avatar-ids', array() );
			$medias[] = $media_id;
			// then save
			update_option( 'yith-wcmap-users-avatar-ids', $medias );


			// save user meta
			$user = get_current_user_id();
			update_user_meta( $user, 'yith-wcmap-avatar', $media_id );

		}

		/**
		 * Print my-downloads endpoint content
		 *
		 * @access public
		 * @since 1.0.0
		 * @author Francesco Licandro
		 */
		public function my_downloads_content( $atts ) {

			$content = '';

			ob_start();
					wc_get_template( 'myaccount/my-downloads.php' );
			$content = ob_get_clean();

			// print message if no downloads
			if( ! $content ){
				$content = '<p>' . __( 'There are no available downloads yet.', 'yith-woocommerce-customize-myaccount-page' ) . '</p>';
			}

			return $content;
		}

		/**
		 * Print view-order endpoint content, if view-order is not empty print order details
		 *
		 * @access public
		 * @since 1.0.0
		 * @author Francesco Licandro
		 */
		public function view_order_content( $atts ) {

			global $wp;

			$content = '';

			if ( ! empty( $wp->query_vars['view-order'] ) ) {

				$order_id = absint( $wp->query_vars['view-order'] );
				$order    = wc_get_order( $order_id );

				if ( ! current_user_can( 'view_order', $order_id ) ) {
					$content = '<div class="woocommerce-error">' . __( 'Invalid order.', 'woocommerce' ) . ' <a href="' . wc_get_page_permalink( 'myaccount' ) . '" class="wc-forward">' . __( 'My Account', 'woocommerce' ) . '</a>' . '</div>';

				}
				else {
					// Backwards compatibility
					$status       = new stdClass();
					$status->name = wc_get_order_status_name( $order->get_status() );

					ob_start();
					wc_get_template( 'myaccount/view-order.php', array(
							'status'   => $status, // @deprecated 2.2
							'order'    => wc_get_order( $order_id ),
							'order_id' => $order_id
					) );
					$content = ob_get_clean();
				}
			}
			else {

				extract( shortcode_atts( array(
						'order_count' => 15
				), $atts ) );

				$order_count = $order_count == 'all' ? -1 : $order_count;

				ob_start();
				wc_get_template( 'myaccount/my-orders.php', array( 'order_count' => $order_count ) );
				$content = ob_get_clean();

				// print message if no orders
				if( ! $content ){
					$content = '<p>' . __( 'There are no orders yet.', 'yith-woocommerce-customize-myaccount-page' ) . '</p>';
				}
			}


			return $content;
		}

		/**
		 * Save an option to check if the page is myaccount
		 *
		 * @access public
		 * @since 1.0.4
		 * @author Francesco Licandro
		 */
		public function save_is_my_account(){
			update_option( 'yith_wcmap_is_my_account', $this->_is_myaccount );
		}

		/**
		 * Compatibility with visual composer plugin.
		 *
		 * @since 1.0.7
		 * @author Francesco Licandro
		 * @param string $action
		 * @param string $content
		 * @return string
		 */
		public function vc_compatibility( $action = 'get', $content = '' ){

			global $post;

			// extract from post content the my-account shortcode
			preg_match( '/\[woocommerce_my_account[^\]]*\]/', $post->post_content, $shortcode );
			// get content
			$shortcode = isset( $shortcode[0] ) ? $shortcode[0] : $post->post_content;

			if( $action == 'get' ) {
				return $shortcode;
			}
			elseif( $action = 'set' && $content ) {
				$post->post_content = str_replace( $shortcode, $content, $post->post_content );
				return true;
			}
		}

	}
}
/**
 * Unique access to instance of YITH_WCMAP_Frontend class
 *
 * @return \YITH_WCMAP_Frontend
 * @since 1.0.0
 */
function YITH_WCMAP_Frontend(){
	return YITH_WCMAP_Frontend::get_instance();
}