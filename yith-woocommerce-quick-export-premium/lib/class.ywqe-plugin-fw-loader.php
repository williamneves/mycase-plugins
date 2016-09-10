<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'YWQE_Plugin_FW_Loader' ) ) {

	/**
	 * Load YITH plugin framework related methods
	 *
	 * @class YWQE_Plugin_FW_Loader
	 * @package Yithemes
	 * @since   1.0.0
	 * @author  Lorenzo Giuffrida
	 */
	class YWQE_Plugin_FW_Loader {

		/**
		 * @var $_panel Panel Object
		 */
		protected $_panel;

		/**
		 * @var $_premium string Premium tab template file name
		 */
		protected $_premium = 'premium.php';

		/**
		 * @var string Premium version landing link
		 */
		protected $_premium_landing = 'http://yithemes.com/themes/plugins/yith-woocommerce-quick-export/';

		/**
		 * @var string Plugin official documentation
		 */
		protected $_official_documentation = 'http://yithemes.com/docs-plugins/yith-woocommerce-quick-export/';

		/**
		 * @var string Plugin panel page
		 */
		protected $_panel_page = 'yith_woocommerce_quick_export';

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

		public function __construct() {
			/**
			 * Register actions and filters to be used for creating an entry on YIT Plugin menu
			 */
			add_action( 'admin_init', array( $this, 'register_pointer' ) );

			add_action( 'plugins_loaded', array( $this, 'plugin_fw_loader' ), 15 );

			//Add action links
			add_filter( 'plugin_action_links_' . plugin_basename( YITH_YWQE_DIR . '/' . basename( YITH_YWQE_FILE ) ), array(
				$this,
				'action_links'
			) );

			add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 4 );

			//  Add stylesheets and scripts files
			add_action( 'admin_menu', array( $this, 'register_panel' ), 5 );

			if ( ! defined( 'YITH_YWQE_PREMIUM' ) ) {
				//  Show plugin premium tab
				add_action( 'yith_woocommerce_quick_export_premium', array( $this, 'premium_tab' ) );
			} else {

				/**
				 * register plugin to licence/update system
				 */
				$this->licence_activation();
			}
		}


		/**
		 * Load YIT core plugin
		 *
		 * @since  1.0
		 * @access public
		 * @return void
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 */
		public function plugin_fw_loader() {
			if ( ! defined( 'YIT' ) || ! defined( 'YIT_CORE_PLUGIN' ) ) {
				require_once( YITH_YWQE_DIR . 'plugin-fw/yit-plugin.php' );
			}
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

			$admin_tabs['general'] = __( 'General', 'yith-woocommerce-quick-export' );
			$admin_tabs['exportation-jobs'] = __( 'Data Exportation', 'yith-woocommerce-quick-export' );
			$admin_tabs['exportation-history'] = __( 'Schedule history', 'yith-woocommerce-quick-export' );

			if ( ! defined( 'YITH_YWQE_PREMIUM' ) ) {
				$admin_tabs['premium-landing'] = __( 'Premium Version', 'yith-woocommerce-quick-export' );
			}

			$args = array(
				'create_menu_page' => true,
				'parent_slug'      => '',
				'page_title'       => 'Quick Export',
				'menu_title'       => 'Quick Export',
				'capability'       => 'manage_options',
				'parent'           => '',
				'parent_page'      => 'yit_plugin_panel',
				'page'             => $this->_panel_page,
				'admin-tabs'       => $admin_tabs,
				'options-path'     => YITH_YWQE_DIR . '/plugin-options'
			);

			/* === Fixed: not updated theme  === */
			if ( ! class_exists( 'YIT_Plugin_Panel_WooCommerce' ) ) {

				require_once( 'plugin-fw/lib/yit-plugin-panel-wc.php' );
			}

			$this->_panel = new YIT_Plugin_Panel_WooCommerce( $args );

			/** Add custom types actions and filters */
			YWQE_Custom_Types::get_instance();
		}

		/**
		 * Premium Tab Template
		 *
		 * Load the premium tab template on admin page
		 *
		 * @return   void
		 * @since    1.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 * @return void
		 */
		public function premium_tab() {
			$premium_tab_template = YITH_YWQE_TEMPLATE_DIR . '/admin/' . $this->_premium;
			if ( file_exists( $premium_tab_template ) ) {
				include_once( $premium_tab_template );
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
			$links[] = '<a href="' . admin_url( "admin.php?page={$this->_panel_page}" ) . '">' . __( 'Settings', 'yith-woocommerce-quick-export' ) . '</a>';

			if ( defined( 'YITH_YWQE_FREE_INIT' ) ) {
				$links[] = '<a href="' . $this->_premium_landing . '" target="_blank">' . __( 'Premium Version', 'yith-woocommerce-quick-export' ) . '</a>';
			}

			return $links;
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
			if ( ( defined( 'YITH_YWQE_INIT' ) && ( YITH_YWQE_INIT == $plugin_file ) ) ||
			     ( defined( 'YITH_YWQE_FREE_INIT' ) && ( YITH_YWQE_FREE_INIT == $plugin_file ) )
			) {
				$plugin_meta[] = '<a href="' . $this->_official_documentation . '" target="_blank">' . __( 'Plugin Documentation', 'yith-woocommerce-quick-export' ) . '</a>';
			}

			return $plugin_meta;
		}

		public function register_pointer() {
			if ( ! class_exists( 'YIT_Pointers' ) ) {
				include_once( 'plugin-fw/lib/yit-pointers.php' );
			}

			$premium_message = defined( 'YITH_YWQE_PREMIUM' )
				? ''
				: __( 'YITH WooCommerce Quick Export is available in an outstanding PREMIUM version with many new options, discover it now.', 'yith-woocommerce-quick-export' ) .
				  ' <a href="' . $this->_premium_landing . '">' . __( 'Premium version', 'yith-woocommerce-quick-export' ) . '</a>';

			$args[] = array(
				'screen_id'  => 'plugins',
				'pointer_id' => 'yith_woocommerce_quick_export',
				'target'     => '#toplevel_page_yit_plugin_panel',
				'content'    => sprintf( '<h3> %s </h3> <p> %s </p>',
					__( 'YITH WooCommerce Quick Export', 'yith-woocommerce-quick-export' ),
					__( 'In the YIT Plugins tab, you can find the YITH WooCommerce Quick Export options.<br> From this menu you can have access to all settings of the activated YITH plugins.', 'yith-woocommerce-quick-export' ) . '<br>' . $premium_message
				),
				'position'   => array( 'edge' => 'left', 'align' => 'center' ),
				'init'       => defined( 'YITH_YWQE_PREMIUM' ) ? YITH_YWQE_INIT : YITH_YWQE_FREE_INIT
			);

			YIT_Pointers()->register( $args );
		}

		/**
		 * Get the premium landing uri
		 *
		 * @since   1.0.0
		 * @author  Andrea Grillo <andrea.grillo@yithemes.com>
		 * @return  string The premium landing link
		 */
		public function get_premium_landing_uri() {
			return defined( 'YITH_REFER_ID' ) ? $this->_premium_landing . '?refer_id=' . YITH_REFER_ID : $this->_premium_landing;
		}

		//region    ****    licence related methods ****

		/**
		 * Add actions to manage licence activation and updates
		 */
		public function licence_activation() {
			if ( ! defined( 'YITH_YWQE_PREMIUM' ) ) {
				return;
			}

			add_action( 'wp_loaded', array( $this, 'register_plugin_for_activation' ), 99 );
			add_action( 'admin_init', array( $this, 'register_plugin_for_updates' ) );
		}

		/**
		 * Register plugins for activation tab
		 *
		 * @return void
		 * @since    2.0.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 */
		public function register_plugin_for_activation() {
			if ( ! class_exists( 'YIT_Plugin_Licence' ) ) {
				require_once 'plugin-fw/lib/yit-plugin-licence.php';
			}
			YIT_Plugin_Licence()->register( YITH_YWQE_INIT, YITH_YWQE_SECRET_KEY, YITH_YWQE_SLUG );
		}

		/**
		 * Register plugins for update tab
		 *
		 * @return void
		 * @since    2.0.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 */
		public function register_plugin_for_updates() {
			if ( ! class_exists( 'YIT_Upgrade' ) ) {
				require_once 'plugin-fw/lib/yit-upgrade.php';
			}
			YIT_Upgrade()->register( YITH_YWQE_SLUG, YITH_YWQE_INIT );
		}
		//endregion
	}
}