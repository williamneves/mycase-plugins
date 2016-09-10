<?php
/**
 * Admin class
 *
 * @author Yithemes
 * @package YITH WooCommerce Email Templates
 * @version 1.0.0
 */

if ( !defined( 'YITH_WCET' ) ) { exit; } // Exit if accessed directly

require_once('functions.yith-wcet.php');

if( !class_exists( 'YITH_WCET_Admin' ) ) {
    /**
     * Admin class.
	 * The class manage all the admin behaviors.
     *
     * @since 1.0.0
     * @author   Leanza Francesco <leanzafrancesco@gmail.com>
     */
    class YITH_WCET_Admin {
		
        /**
         * Single instance of the class
         *
         * @var \YITH_WCQV_Admin
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
         * Plugin version
         *
         * @var string
         * @since 1.0.0
         */
        public $version = YITH_WCET_VERSION;

        /**
         * @var $_panel Panel Object
         */
        protected $_panel;

        /**
         * @var string Premium version landing link
         */
        protected $_premium_landing = 'https://yithemes.com/themes/plugins/yith-woocommerce-email-templates/';

        /**
         * @var string Quick View panel page
         */
        protected $_panel_page = 'yith_wcet_panel';
        
        /**
         * Mail Type
         * Type of the mail sended (es. new-order, cancelled-order, invoice, etc...)
         * @var string
         * @access public
         * @since 1.0.0
         */
        public $mail_type  = '';

        /**
         * Various links
         *
         * @var string
         * @access public
         * @since 1.0.0
         */
        public $doc_url = 'http://yithemes.com/docs-plugins/yith-woocommerce-email-templates/';

        public $templates = array();

        /**
         * Returns single instance of the class
         *
         * @return \YITH_WCET
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

            //Add action links
            add_filter( 'plugin_action_links_' . plugin_basename( YITH_WCET_DIR . '/' . basename( YITH_WCET_FILE ) ), array( $this, 'action_links') );
            add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 4 );

            add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

            add_filter('wc_get_template', array( $this, 'custom_template') , 999, 5 );

            add_action('init', array( $this, 'post_type_register'));

            add_action('save_post', array( $this, 'metabox_save'));

            add_action( 'admin_init', array( $this, 'preview_emails' ) );

            add_action( 'yith_wcet_email_header', array( $this, 'email_header' ), 10, 2);
            add_action( 'yith_wcet_email_footer', array( $this, 'email_footer' ), 10, 1);

            add_filter( 'woocommerce_email_styles', array( $this, 'email_styles' ) );
            add_filter( 'woocommerce_mail_content', array( $this, 'mail_content_styling' ) );

            add_filter('woocommerce_email_settings', array( $this, 'email_extra_settings') );
            add_filter( 'yith_wcet_panel_settings_options', array($this, 'add_email_extra_settings_in_tab_settings'));

            // Premium Tabs
            add_action( 'yith_wcet_premium_tab', array( $this, 'show_premium_tab' ) );
		 }

        /**
         * This function copy the mail extra settings in the plugin settings tab
         *
         * @access public
         * @since 1.0.0
         */
        public function add_email_extra_settings_in_tab_settings($settings){
            $settings['settings'] = $this->email_extra_settings( $settings['settings'] );
            $settings['settings'][] = array(
                                        'type'      => 'sectionend',
                                        'id'        => 'yith-wcet-email-extra-settings'
                                    );
            return $settings;
        }

        /**
        * Email Header
        * It's used to pass $mail_type to template email-header
        * @access public
        * @since 1.0.0
        * @author   Leanza Francesco <leanzafrancesco@gmail.com>
        */
        public function email_header( $email_heading, $mail_type){
            wc_get_template( 'emails/email-header.php', array( 'email_heading' => $email_heading, 'mail_type' => $mail_type ) );
            $this->mail_type = $mail_type;
        }

        /**
        * Email Footer
        * It's used to pass $mail_type to template email-footer
        * @access public
        * @since 1.0.0
        * @author   Leanza Francesco <leanzafrancesco@gmail.com>
        */
        public function email_footer( $mail_type){
            wc_get_template( 'emails/email-footer.php', array( 'mail_type' => $mail_type ) );
        }

        /**
        * Woocommerce Email Styles
        *
        * @access public
        * @since 1.0.0
        * @author   Leanza Francesco <leanzafrancesco@gmail.com>
        */
        public function email_styles( $style ){
            if ( $this->mail_type == '' ){
                return $style;
            }
            return '';
        }

        /**
        * Add Email extra settings in woocommerce email settings
        *
        * @access public
        * @since 1.0.0
        * @author   Leanza Francesco <leanzafrancesco@gmail.com>
        */
        public function email_extra_settings( $settings ){
            $templates_array = array( 
                'default'       => __( 'Default', 'yith-woocommerce-email-templates' )
                );

            $args = ( array('posts_per_page' => -1, 'post_type' => 'yith-wcet-etemplate', 'orderby' => 'title', 'order' => 'ASC', 'post_status'=> 'publish') );
            $templates = get_posts( $args );
            foreach ($templates as $template) {
                $templates_array[$template->ID] = get_the_title($template->ID);
            }

            $settings[] = array( 
                'title' => __( 'YITH WooCommerce Email Settings', 'yith-wcbm' ),
                'type' => 'title',
                'desc' => __( 'Select templates for email', 'yith-wcbm' ),
                'id' => 'yith_wcet_email_extra_settings'
                );

            $settings[] = array(
                    'id'                => 'yith-wcet-email-template',
                    'name'              => __( 'Email Template', 'yith-woocommerce-email-templates' ),
                    'type'              => 'select',
                    'desc_tip'              => __( 'Select the email template that you want to use for your emails!', 'yith-woocommerce-email-templates' ),
                    'class'             => 'email_type wc-enhanced-select',
                    'options'           => $templates_array,
                    'default'           => 'default'
                );

            $settings[] = array(
                'type' => 'sectionend',
                'id' => 'yith_wcet_email_extra_settings'
                );

            return $settings;
        }

        /**
        * Mail Content Styling
        * 
        * This func transforms css style of the mail in inline style; and return the content with the inline style
        * @return string
        * @access public
        * @since 1.0.0
        * @author   Leanza Francesco <leanzafrancesco@gmail.com>
        */
        public function mail_content_styling( $content ){
            // get CSS styles
            ob_start();
            $mail_type = $this->mail_type;
            wc_get_template( 'emails/email-styles.php');
            $css = ob_get_clean();

            try {
                // apply CSS styles inline for picky email clients
                $emogrifier = new Emogrifier( $content, $css );
                $content = $emogrifier->emogrify();

            } catch ( Exception $e ) {

                $logger = new WC_Logger();

                $logger->add( 'emogrifier', $e->getMessage() );
            }

            return $content;
        }

        /**
        * Custom Template
        * 
        * Filters wc_get_template for custom templates
        * @return string
        * @access public
        * @since 1.0.0
        * @author   Leanza Francesco <leanzafrancesco@gmail.com>
        */
        public function custom_template($located, $template_name, $args, $template_path, $default_path){
            
            $this->_templates = array(
                'emails/admin-cancelled-order.php',
                'emails/admin-new-order.php',
                'emails/customer-completed-order.php',
                'emails/customer-invoice.php',
                'emails/customer-new-account.php',
                'emails/customer-note.php',
                'emails/customer-processing-order.php',
                'emails/customer-reset-password.php',
                'emails/email-addresses.php',
                'emails/email-footer.php',
                'emails/email-header.php',
                'emails/email-order-items.php',
                'emails/email-styles.php'
            );

            if( in_array( $template_name, $this->_templates ) )
            return YITH_WCET_TEMPLATE_PATH . '/' . $template_name;

            return $located;
        }

        /**
         * Preview email template
         *
         * @return string
         * @author   Leanza Francesco <leanzafrancesco@gmail.com>
         */
        public function preview_emails() {
            if ( isset( $_GET['yith_wcet_preview_mail'] ) ) {

                if ( isset ($_GET['template_id'] ) ) {
                    // get CSS styles
                    ob_start();
                    global $template_id; 
                    $template_id= $_GET['template_id'];
                    wc_get_template( 'emails/email-styles.php');
                    $css = apply_filters( 'woocommerce_email_styles', ob_get_clean() );
                }
                // load the mailer class
                $mailer        = WC()->mailer();

                // get the preview email subject
                $email_heading = __( 'HTML Email Template', 'woocommerce' );

                // get the preview email content
                ob_start();
                include( YITH_WCET_TEMPLATE_PATH . '/views/html-email-template-preview.php' );
                $message       = ob_get_clean();

                // create a new email
                $email         = new WC_Email();

                // wrap the content with the email template and then add styles
                $message       = $email->style_inline( $mailer->wrap_message( $email_heading, $message ) );

                // print the preview email
                echo $message;
                exit;
            }
        }

        /**
         * Register Email Template custom post type with options metabox
         *
         * @return   void
         * @since    1.0
         * @author   Leanza Francesco <leanzafrancesco@gmail.com>
         */
        public function post_type_register() {
            $labels = array(
                'name'                  => __('Email Templates', 'yith-woocommerce-email-templates'),
                'singular_name'         => __('Email Template', 'yith-woocommerce-email-templates'),
                'add_new'               => __('Add Email Template', 'yith-woocommerce-email-templates'),
                'add_new_item'          => __('Add new Email Template', 'yith-woocommerce-email-templates'),
                'edit_item'             => __('Edit Email Template', 'yith-woocommerce-email-templates'),
                'view_item'             => __('View Email Template', 'yith-woocommerce-email-templates'),
                'not_found'             => __('Email Template not found', 'yith-woocommerce-email-templates'),
                'not_found_in_trash'    => __('Email Template not found in trash', 'yith-woocommerce-email-templates')
            );

            $args = array(
                'labels'                    => $labels,
                'public'                    => true,
                'show_ui'                   => true,
                'menu_position'             => 10,
                'exclude_from_search'       => true,
                'capability_type'           => 'post',
                'map_meta_cap'              => true,
                'rewrite'                   => true,
                'has_archive'               => true,
                'hierarchical'              => false,
                'show_in_nav_menus'         => false,
                'menu_icon'                 => 'dashicons-email-alt',
                'supports'                  => array('title'),
                'register_meta_box_cb'      => array($this, 'register_metabox')
            );

            register_post_type('yith-wcet-etemplate', $args);
        }

        public function register_metabox(){
            add_meta_box('yith-wcet-metabox', __('Template Options', 'yith-woocommerce-email-templates'), array( $this, 'metabox_render'), 'yith-wcet-etemplate', 'normal', 'high');
        }

        public function metabox_render( $post ){
            
            $meta = get_post_meta( $post->ID, '_template_meta', true);

            $default = array(
                'txt_color_default'             => '#000000', 
                'txt_color'                     => '#000000', 
                'bg_color_default'              => '#F5F5F5', 
                'bg_color'                      => '#F5F5F5',
                'base_color_default'            => '#2470FF', 
                'base_color'                    => '#2470FF',
                'body_color_default'            => '#FFFFFF', 
                'body_color'                    => '#FFFFFF',
                'logo_url'                      => '',
                'custom_logo_url'               => get_option( 'yith-wcet-custom-default-header-logo' )
            );

            $args = wp_parse_args( $meta , $default );

            $args = apply_filters('yith_wcet_metabox_options_content_args' , $args);
            
            yith_wcet_metabox_options_content($args);
            
        }

        public function metabox_save( $post_id ) {
            if ( !empty( $_POST[ '_template_meta' ] ) ){
                $meta['txt_color'] = ( !empty( $_POST[ '_template_meta' ]['txt_color'] ) ) ? $_POST[ '_template_meta' ]['txt_color'] : '';
                $meta['bg_color'] = ( !empty( $_POST[ '_template_meta' ]['bg_color'] ) ) ? $_POST[ '_template_meta' ]['bg_color'] : '';
                $meta['base_color'] = ( !empty( $_POST[ '_template_meta' ]['base_color'] ) ) ? $_POST[ '_template_meta' ]['base_color'] : '';
                $meta['body_color'] = ( !empty( $_POST[ '_template_meta' ]['body_color'] ) ) ? $_POST[ '_template_meta' ]['body_color'] : '';
                $meta['logo_url'] = ( !empty( $_POST[ '_template_meta' ]['logo_url'] ) ) ? $_POST[ '_template_meta' ]['logo_url'] : '';
                update_post_meta( $post_id, '_template_meta', $meta );
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
         * @author   Leanza Francesco <leanzafrancesco@gmail.com>
         * @return mixed
         * @use plugin_action_links_{$plugin_file_name}
         */
        public function action_links( $links ) {

            $links[] = '<a href="' . admin_url( "admin.php?page={$this->_panel_page}" ) . '">' . __( 'Settings', 'yith-woocommerce-email-templates' ) . '</a>';
            if ( defined( 'YITH_WCET_FREE_INIT' ) ) {
                $links[] = '<a href="' . $this->_premium_landing . '" target="_blank">' . __( 'Premium Version', 'ywcm' ) . '</a>';
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
         * @use plugin_row_meta
         */
        public function plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status ) {

            if ( defined( 'YITH_WCET_FREE_INIT' ) && YITH_WCET_FREE_INIT == $plugin_file ) {
                $plugin_meta[] = '<a href="' . $this->doc_url . '" target="_blank">' . __( 'Plugin Documentation', 'yith-woocommerce-email-templates' ) . '</a>';
            }
            return $plugin_meta;
        }

        /**
         * Add a panel under YITH Plugins tab
         *
         * @return   void
         * @since    1.0
         * @author   Leanza Francesco <leanzafrancesco@gmail.com>
         * @use     /Yit_Plugin_Panel class
         * @see      plugin-fw/lib/yit-plugin-panel.php
         */
        public function register_panel() {

            if ( ! empty( $this->_panel ) ) {
                return;
            }

            $admin_tabs_free = array(
                'settings'      => __( 'Settings', 'yith-woocommerce-email-templates' ),
                'premium'       => __( 'Premium Version', 'yith-woocommerce-email-templates' )
                );

            $admin_tabs = apply_filters('yith_wcet_settings_admin_tabs', $admin_tabs_free);

            $args = array(
                'create_menu_page' => true,
                'parent_slug'      => '',
                'page_title'       => __( 'Email Templates', 'yith-woocommerce-email-templates' ),
                'menu_title'       => __( 'Email Templates', 'yith-woocommerce-email-templates' ),
                'capability'       => 'manage_options',
                'parent'           => '',
                'parent_page'      => 'yit_plugin_panel',
                'page'             => $this->_panel_page,
                'admin-tabs'       => $admin_tabs,
                'options-path'     => YITH_WCET_DIR . '/plugin-options'
            );

            /* === Fixed: not updated theme  === */
            if( ! class_exists( 'YIT_Plugin_Panel_WooCommerce' ) ) {
                require_once( 'plugin-fw/lib/yit-plugin-panel-wc.php' );
            }

            $this->_panel = new YIT_Plugin_Panel_WooCommerce( $args );
            
            add_action( 'woocommerce_admin_field_yith_wcet_upload', array( $this->_panel, 'yit_upload' ), 10, 1 );
            //add_action( 'woocommerce_update_option_yith_wcet_upload', array( $this->_panel, 'yit_upload_update' ), 10, 1 );
        }

        /**
         * Show premium landing tab
         *
         * @return   void
         * @since    1.0
         * @author   Leanza Francesco <leanzafrancesco@gmail.com>
         */
        public function show_premium_tab(){
            $landing = YITH_WCET_TEMPLATE_PATH . '/premium.php';
            file_exists( $landing ) && require( $landing );
        }

        /**
         * Get the premium landing uri
         *
         * @since   1.0.0
         * @author  Andrea Grillo <andrea.grillo@yithemes.com>
         * @return  string The premium landing link
         */
        public function get_premium_landing_uri() {
            return defined( 'YITH_REFER_ID' ) ? $this->_premium_landing . '?refer_id=' . YITH_REFER_ID : $this->_premium_landing . '?refer_id=1030585';
        }

        public function admin_enqueue_scripts() {
            wp_enqueue_style( 'yith-wcet-admin-styles', YITH_WCET_ASSETS_URL . '/css/admin.css');
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_script('wp-color-picker');
            wp_enqueue_script('jquery-ui-tabs');
            wp_enqueue_style( 'jquery-ui-style-css', YITH_WCET_ASSETS_URL . '/css/jquery-ui.css' );
            wp_enqueue_style( 'googleFontsOpenSans', '//fonts.googleapis.com/css?family=Open+Sans:400,600,700,800,300' );
            
            $screen     = get_current_screen();
            $metabox_js = defined( 'YITH_WCET_PREMIUM' ) ? 'metabox_options_premium.js' : 'metabox_options.js';

            if( 'yith-wcet-etemplate' == $screen->id  ) {
                wp_enqueue_script( 'yith_wcet_metabox_options', YITH_WCET_ASSETS_URL .'/js/' . $metabox_js, array('jquery', 'wp-color-picker'), '1.0.0', true );
                wp_localize_script( 'yith_wcet_metabox_options', 'ajax_object', array( 'assets_url' => YITH_WCET_ASSETS_URL , 'wp_ajax_url' => admin_url( 'admin-ajax.php' )) );
            }
        }
    }
}

/**
 * Unique access to instance of YITH_WCET_Admin class
 *
 * @return \YITH_WCET_Admin
 * @since 1.0.0
 */
function YITH_WCET_Admin(){
    return YITH_WCET_Admin::get_instance();
}
?>
