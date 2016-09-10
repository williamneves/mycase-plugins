<?php

if ( !defined( 'ABSPATH' ) || !defined( 'YITH_YWRAC_VERSION' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Implements admin features of YITH_WC_Recover_Abandoned_Cart_Admin
 *
 * @class   YITH_WC_Recover_Abandoned_Cart_Admin
 * @package YITH WooCommerce Recover Abandoned Cart
 * @since   1.0.0
 * @author  Yithemes
 */
if ( !class_exists( 'YITH_WC_Recover_Abandoned_Cart_Admin' ) ) {

    class YITH_WC_Recover_Abandoned_Cart_Admin {

        /**
         * Single instance of the class
         *
         * @var \YITH_WC_Dynamic_Pricing_Admin
         */
        protected static $instance;

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
        protected $_premium_landing = 'http://yithemes.com/themes/plugins/yith-woocommerce-recover-abandoned-cart/';

        /**
         * @var string Panel page
         */
        protected $_panel_page = 'yith_woocommerce_recover_abandoned_cart';

        /**
         * @var string Doc Url
         */
        public $doc_url = 'https://yithemes.com/docs-plugins/yith-woocommerce-recover-abandoned-cart/';

        /**
         * @var Wp List Table
         */
		public $cpt_obj;

        /**
         * Returns single instance of the class
         *
         * @return \YITH_WC_Dynamic_Pricing_Admin
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
         * Initialize plugin and registers actions and filters to be used
         *
         * @since  1.0.0
         * @author Emanuela Castorina
         */
        public function __construct() {

            $this->create_menu_items();

            add_action( 'after_setup_theme', array( $this, 'plugin_fw_loader' ), 1 );
            //Add action links
            add_filter( 'plugin_action_links_' . plugin_basename( YITH_YWRAC_DIR . '/' . basename( YITH_YWRAC_FILE ) ), array( $this, 'action_links' ) );
            add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 4 );

            // panel type category search
            add_action( 'wp_ajax_ywrac_email_send', array( $this, 'ajax_email_send' ) );
            add_action( 'wp_ajax_nopriv_ywrac_email_send', array( $this, 'ajax_email_send' ) );

            /* email actions and filter */
            add_filter( 'woocommerce_email_classes', array( $this, 'add_woocommerce_emails' ) );
            add_action( 'woocommerce_init', array( $this, 'load_wc_mailer' ) );

            /* general actions */
            add_filter( 'woocommerce_locate_core_template', array( $this, 'filter_woocommerce_template' ), 10, 3 );
            add_filter( 'woocommerce_locate_template', array( $this, 'filter_woocommerce_template' ), 10, 3 );

            //custom styles and javascripts
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles_scripts' ), 11);
            $this->init();

        }

        /**
         * Load YIT Plugin Framework
         *
         * @since  1.0.0
         * @return void
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function plugin_fw_loader() {
            if ( !defined( 'YIT' ) || !defined( 'YIT_CORE_PLUGIN' ) ) {
                require_once( YITH_YWRAC_DIR.'plugin-fw/yit-plugin.php' );
            }
        }

        /**
         * Init function check if the plugin is enabled
         *
         * @since  1.0.0
         * @return void
         * @author Emanuela Castorina
         */
        public function init(){
            if ( get_option('ywrac_enabled') != 'yes' ){
                return;
            }
        }

        /**
         * Enqueue styles and scripts
         *
         * @access public
         * @return void
         * @since 1.0.0
         */
        public function enqueue_styles_scripts() {
            wp_enqueue_style( 'yith_ywrac_backend', YITH_YWRAC_ASSETS_URL . '/css/backend.css', YITH_YWRAC_VERSION );
            wp_enqueue_script( 'jquery-blockui', YITH_YWRAC_ASSETS_URL . '/js/jquery.blockUI.min.js', array( 'jquery' ), false, true );
            wp_enqueue_script( 'yith_ywrac_admin', YITH_YWRAC_ASSETS_URL . '/js/ywrac-admin' . YITH_YWRAC_SUFFIX . '.js', array( 'jquery' ), YITH_YWRAC_VERSION, true );

            wp_localize_script( 'yith_ywrac_admin', 'yith_ywrac_admin', array(
                'ajaxurl'          => admin_url( 'admin-ajax.php' ),
                'send_email_nonce' => wp_create_nonce( 'send-email' ),
                'sent_label'       => __('Email Sent', 'ywrac'),
                'block_loader'     => YITH_YWRAC_ASSETS_URL .'/images/ajax-loader.gif'
            ));

        }

        /**
         * Create Menu Items
         *
         * Print admin menu items
         *
         * @since  1.0
         * @author Emanuela Castorina
         */
        private function create_menu_items() {

            // Add a panel under YITH Plugins tab
            add_action( 'admin_menu', array( $this, 'register_panel' ), 5 );
            add_action( 'yith_ywrac_premium_tab', array( $this, 'premium_tab' ) );
			add_action( 'yith_ywrac_carts', array( $this, 'carts_tab' ) );
        }

        /**
         * Add a panel under YITH Plugins tab
         *
         * @return   void
         * @since    1.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         * @use      /Yit_Plugin_Panel class
         * @see      plugin-fw/lib/yit-plugin-panel.php
         */
        public function register_panel() {

            if ( !empty( $this->_panel ) ) {
                return;
            }

            $admin_tabs = array(
                'general' => __( 'Settings', 'ywrac' ),
				'carts'   => __( 'Carts', 'ywrac' ),
                'email'   => __( 'Email Template', 'ywrac' )
            );

            if ( defined( 'YITH_YWRAC_FREE_INIT' ) ) {
                $admin_tabs['premium'] = __( 'Premium Version', 'ywrac' );
            }

            $args = array(
                'create_menu_page' => true,
                'parent_slug'      => '',
                'page_title'       => __( 'Abandoned Cart', 'ywrac' ),
                'menu_title'       => __( 'Abandoned Cart', 'ywrac' ),
                'capability'       => 'manage_options',
                'parent'           => 'ywrac',
                'parent_page'      => 'yit_plugin_panel',
                'page'             => $this->_panel_page,
                'admin-tabs'       => $admin_tabs,
                'options-path'     => YITH_YWRAC_DIR . '/plugin-options'
            );

            /* === Fixed: not updated theme  === */
            if ( !class_exists( 'YIT_Plugin_Panel_WooCommerce' ) ) {
                require_once( YITH_YWRAC_DIR.'/plugin-fw/lib/yit-plugin-panel-wc.php' );
            }

            $this->_panel = new YIT_Plugin_Panel_WooCommerce( $args );

            add_action( 'woocommerce_admin_field_ywrac_additional_textarea', array( $this, 'additional_textarea' ), 10, 1 );
            add_filter('woocommerce_admin_settings_sanitize_option_ywrac_email_content', array( $this, 'sanitize'), 10, 3);

            //Custom tinymce button
            add_action('admin_head', array( $this, 'tc_button' ) );


        }

        public function sanitize(  $value, $option, $raw_value  ) {
            return nl2br($raw_value);
        }
        /**
         * Add a new button to tinymce
         *
         * @return   void
         * @since    1.0
         * @author   Emanuela Castorina
         */
        public function tc_button() {
            global $typenow;

            if ( !current_user_can( 'edit_posts' ) && !current_user_can( 'edit_pages' ) ) {
                return;
            }

            if ( !isset( $_GET['page'] ) || $_GET['page'] != $this->_panel_page ) {
                return;
            }

            if ( get_user_option( 'rich_editing' ) == 'true' ) {
                add_filter( "mce_external_plugins", array( $this, 'add_tinymce_plugin' ) );
                add_filter( "mce_buttons", array( $this, 'register_tc_button' ) );
                add_filter( 'mce_external_languages', array( $this, 'add_tc_button_lang' ) );
            }
        }

        /**
         * Add plugin button to tinymce from filter mce_external_plugins
         *
         * @return   void
         * @since    1.0
         * @author   Emanuela Castorina
         */
        function add_tinymce_plugin( $plugin_array ) {
            $plugin_array['tc_button'] = YITH_YWRAC_ASSETS_URL . '/js/tinymce/text-editor.js';
            return $plugin_array;
        }

        /**
         * Register the custom button to tinymce from filter mce_buttons
         *
         * @return   void
         * @since    1.0
         * @author   Emanuela Castorina
         */
        function register_tc_button( $buttons ) {
            array_push( $buttons, "tc_button" );
            return $buttons;
        }

        /**
         * Add multilingual to mce button from filter mce_external_languages
         *
         * @return   void
         * @since    1.0
         * @author   Emanuela Castorina
         */
        function add_tc_button_lang( $locales ) {
            $locales ['tc_button'] = YITH_YWRAC_INC . 'admin/tinymce/tinymce-plugin-langs.php';
            return $locales;
        }

        /**
         * Premium Tab Template
         *
         * Load the premium tab template on admin page
         *
         * @return   void
         * @since    1.0
         * @author   Emanuela Castorina
         */
        public function premium_tab() {
            $premium_tab_template = YITH_YWRAC_TEMPLATE_PATH . '/admin/' . $this->_premium;
            if ( file_exists( $premium_tab_template ) ) {
                include_once( $premium_tab_template );
            }
        }

        /**
         * Carts Template
         *
         * Load the abandoned cart template on admin page
         *
         * @return   void
         * @since    1.0
         * @author   Emanuela Castorina
         */
        public function carts_tab() {
			$this->cpt_obj = new WP_Carts_List_Table();
            YITH_WC_Recover_Abandoned_Cart()->update_carts();
			$carts_tab = YITH_YWRAC_TEMPLATE_PATH . '/admin/carts-tab.php';
			if ( file_exists( $carts_tab ) ) {
				include_once( $carts_tab );
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
         * @use      plugin_action_links_{$plugin_file_name}
         */
        public function action_links( $links ) {

            $links[] = '<a href="' . admin_url( "admin.php?page={$this->_panel_page}" ) . '">' . __( 'Settings', 'ywrac' ) . '</a>';
            if ( defined( 'YITH_YWRAC_FREE_INIT' ) ) {
              $links[] = '<a href="' . $this->get_premium_landing_uri() . '" target="_blank">' . __( 'Premium Version', 'ywrac' ) . '</a>';
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
         * @use      plugin_row_meta
         */
        public function plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status ) {
            if ( defined( 'YITH_YWRAC_INIT' ) && YITH_YWRAC_INIT == $plugin_file ) {
                $plugin_meta[] = '<a href="' . $this->doc_url . '" target="_blank">' . __( 'Plugin Documentation', 'ywrac' ) . '</a>';
            }
            return $plugin_meta;
        }

        /**
         * Add a textarea with editor as type of plugin panel
         *
         *
         * @return   Array
         * @since    1.0
         * @author   Emanuela Castorina
         */
        public function additional_textarea( $opt ){
            $opt['default'] = ( get_option( $opt['id'] ) ) ? get_option( $opt['id'] ) : $opt['default'];
            ?>
            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="<?php echo $opt['id'] ?>"><?php echo $opt['name']  ?></label>
                </th>
                <td class="forminp forminp-text">
                  <?php wc_get_template( 'admin/panel/textarea-editor.php', array('args' => $opt )); ?>
                </td>
            </tr>
        <?php
        }

        /**
         * Update the value of textarea in the plugin panel
         *
         *
         * @return   Array
         * @since    1.0
         * @author   Emanuela Castorina
         */
        public function update_additional_textarea( $opt ){

            if( isset( $_POST[ $opt['id'] ] ) ){
                update_option( $opt['id'], $_POST[ $opt['id'] ] );
            }

        }

        /**
         * Get the premium landing uri
         *
         * @since   1.0.0
         * @author  Andrea Grillo <andrea.grillo@yithemes.com>
         * @return  string The premium landing link
         */
        public function get_premium_landing_uri(){
            return defined( 'YITH_REFER_ID' ) ? $this->_premium_landing . '?refer_id=' . YITH_REFER_ID : $this->_premium_landing.'?refer_id=1030585';
        }

        /**
         * Filters woocommerce available mails
         *
         * @param $emails array
         *
         * @return array
         * @since 1.0
         */
        public function add_woocommerce_emails( $emails ) {
           include( YITH_YWRAC_INC . 'emails/class.yith-wc-abandoned-cart-email.php' );
            return $emails;
        }

        /**
         * Loads WC Mailer when needed
         *
         * @return void
         * @since 1.0
         */
        public function load_wc_mailer() {
            add_action( 'send_rac_mail', array( 'WC_Emails', 'send_transactional_email' ), 10 );
        }

        /**
         * Send email for recovery a single cart
         *
         * @return void
         * @since 1.0
         */
        public function email_send( $cart_id ){

            $user_id           = get_post_meta( $cart_id, '_user_id', true );

            $cart_content = get_post_meta( $cart_id, '_cart_content', true );
            $cart_content_meta = ( $cart_content != '') ? maybe_unserialize( $cart_content ) : '';

            $user  = get_userdata( $user_id );

            $email_sender_name = get_option( 'ywrac_email_sender_name' );
            $template_content  = get_option( 'ywrac_email_content' );

            $template_content = str_replace('{{ywrac.firstname}}', $user->first_name, $template_content);
            $template_content = str_replace('{{ywrac.lastname}}', $user->last_name, $template_content);
            $template_content = str_replace('{{ywrac.username}}', $user->user_login, $template_content);

            $cart_content = $this->get_cart_content( $cart_id, $cart_content_meta );
            $template_content = str_replace('{{ywrac.cart}}', $cart_content, $template_content);

            $args = array(
                'cart_id'       => $cart_id,
                'user_email'    => $user->user_email,
                'email_content' => $template_content,
                'email_heading' => $email_sender_name
            );
            do_action( 'send_rac_mail', $args );


            $email_sent =  get_post_meta( $cart_id, '_email_sent');
            return $email_sent;

        }

        /**
         * Send email for recovery a single cart in ajax
         *
         * @return void
         * @since 1.0
         */
        public function ajax_email_send(){
            check_ajax_referer( 'send-email', 'security' );

            if ( !isset( $_POST['cart_id'] ) ) {
                return;
            }

            $result = $this->email_send( $_POST['cart_id']  );

            wp_send_json( array(
                'email_sent' => $result
            ) );


        }

        /**
         * Send email for recovery a single cart in ajax
         *
         * @return void
         * @since 1.0
         */
        public function get_cart_content( $cart_id, $cart_content ){
            ob_start();
            $subtotal = get_post_meta( $cart_id, '_cart_subtotal', true );
            wc_get_template( 'cart_content.php', array( 'cart_content' => $cart_content, 'subtotal' => $subtotal ) );
            return ob_get_clean();
        }

        /**
         * Locate default templates of woocommerce in plugin, if exists
         *
         * @param $core_file     string
         * @param $template      string
         * @param $template_base string
         *
         * @return string
         * @since  1.0.0
         */
        public function filter_woocommerce_template( $core_file, $template, $template_base ) {
            $located = yith_ywrac_locate_template( $template );

            if ( $located ) {
                return $located;
            }
            else {
                return $core_file;
            }
        }


    }
}

/**
 * Unique access to instance of YITH_WC_Recover_Abandoned_Cart_Admin class
 *
 * @return \YITH_WC_Recover_Abandoned_Cart_Admin
 */
function YITH_WC_Recover_Abandoned_Cart_Admin() {
    return YITH_WC_Recover_Abandoned_Cart_Admin::get_instance();
}
