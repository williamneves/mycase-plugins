<?php

defined( 'ABSPATH' ) or exit;

/*
 *  YITH WooCommerce Customer History Admin
 */

if ( ! class_exists( 'YITH_WCCH_Admin' ) ) {

    class YITH_WCCH_Admin {

        /*
         *  Constructor
         */

        function __construct() {

            /*
             *  Hooks
             */

            add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
            add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue_scripts' ), 1 );

        }

        /*
         *  Admin Enqueue Scripts
         */

        function admin_enqueue_scripts() {

            /*
             *  Js
             */

            wp_enqueue_script( 'jquery' );
            wp_enqueue_script( 'yith-wcch-scripts-admin', YITH_WCCH_URL . 'assets/js/yith-wcch-admin.js' );
            wp_enqueue_script( 'select2', WC()->plugin_url() . '/assets/js/select2/select2.min.js', array( 'jquery' ) );

            /*
             *  Css
             */

            // wp_enqueue_style( 'jquery-ui', YITH_WCCH_URL . 'assets/css/jquery-ui.min.css', false, '1.11.4' );
            wp_enqueue_style( 'font-awesome', YITH_WCCH_URL . 'assets/css/font-awesome.min.css', false, '4.5.0' );
            wp_enqueue_style( 'yith-wcch-style-admin', YITH_WCCH_URL . 'assets/css/yith-wcch-admin.css', false, '1.0.0' );

        }

        /*
         *  Admin Menu and Separator
         */

        function admin_menu() {

            global $menu;

            $capability = 'manage_options';

            add_menu_page( 'Customer History', 'Customer History', $capability, 'yith-wcch-customers.php', array( $this, 'admin_customers' ), 'dashicons-pressthis', '55.5' );

            add_submenu_page( 'yith-wcch-customers.php', __( 'Customers', 'yith-woocommerce-customer-history' ), __( 'Customers', 'yith-woocommerce-customer-history' ), $capability, 'yith-wcch-customers.php', array( $this, 'admin_customers' ) );
            add_submenu_page( '', __( 'Customer', 'yith-woocommerce-customer-history' ), __( 'Customer', 'yith-woocommerce-customer-history' ), $capability, 'yith-wcch-customer.php', array( $this, 'admin_customer' ) );
            add_submenu_page( 'yith-wcch-customers.php', __( 'Other Users', 'yith-woocommerce-customer-history' ), __( 'Other Users', 'yith-woocommerce-customer-history' ), $capability, 'yith-wcch-users.php', array( $this, 'admin_users' ) );
            add_submenu_page( 'yith-wcch-customers.php', __( 'Sessions', 'yith-woocommerce-customer-history' ), __( 'Sessions', 'yith-woocommerce-customer-history' ), $capability, 'yith-wcch-sessions.php', array( $this, 'admin_sessions' ) );
            add_submenu_page( '', __( 'Session', 'yith-woocommerce-customer-history' ), __( 'Session', 'yith-woocommerce-customer-history' ), $capability, 'yith-wcch-session.php', array( $this, 'admin_session' ) );
            add_submenu_page( 'yith-wcch-customers.php', __( 'Searches', 'yith-woocommerce-customer-history' ), __( 'Searches', 'yith-woocommerce-customer-history' ), $capability, 'yith-wcch-searches.php', array( $this, 'admin_searches' ) );
            add_submenu_page( 'yith-wcch-customers.php', __( 'Emails', 'yith-woocommerce-customer-history' ), __( 'Emails', 'yith-woocommerce-customer-history' ), $capability, 'yith-wcch-emails.php', array( $this, 'admin_emails' ) );
            add_submenu_page( '', __( 'Email', 'yith-woocommerce-customer-history' ), __( 'Email', 'yith-woocommerce-customer-history' ), $capability, 'yith-wcch-email.php', array( $this, 'admin_email' ) );
            add_submenu_page( 'yith-wcch-customers.php', __( 'Settings', 'yith-woocommerce-customer-history' ), __( 'Settings', 'yith-woocommerce-customer-history' ), $capability, 'yith-wcch-settings.php', array( $this, 'admin_settings' ) );

            global $admin_page_hooks;
            if( ! isset( $admin_page_hooks['yit_plugin_panel'] ) ){
                add_menu_page( 'yit_plugin_panel', __( 'YITH Plugins', 'yith-plugin-fw' ), 'manage_options', 'yit_plugin_panel', NULL, YIT_CORE_PLUGIN_URL . '/assets/images/yithemes-icon.png', '62.32' );
            }
            add_submenu_page( 'yit_plugin_panel', __( 'Customer History', 'yith-woocommerce-customer-history' ), __( 'Customer History', 'yith-woocommerce-customer-history' ), $capability, 'yith-wcch-customers.php', array( $this, 'admin_customers' ) );
            remove_submenu_page( 'yit_plugin_panel', 'yit_plugin_panel' );
        }

        /*
         *  Callback functions
         */

        function admin_customers() { require YITH_WCCH_TEMPLATE_PATH . '/backend/customers.php'; }
        function admin_customer() { require YITH_WCCH_TEMPLATE_PATH . '/backend/customer.php'; }
        function admin_users() { require YITH_WCCH_TEMPLATE_PATH . '/backend/users.php'; }
        function admin_sessions() { require YITH_WCCH_TEMPLATE_PATH . '/backend/sessions.php'; }
        function admin_session() { require YITH_WCCH_TEMPLATE_PATH . '/backend/session.php'; }
        function admin_searches() { require YITH_WCCH_TEMPLATE_PATH . '/backend/searches.php'; }
        function admin_emails() { require YITH_WCCH_TEMPLATE_PATH . '/backend/emails.php'; }
        function admin_email() { require YITH_WCCH_TEMPLATE_PATH . '/backend/email.php'; }
        function admin_settings() { require YITH_WCCH_TEMPLATE_PATH . '/backend/settings.php'; }

    }

}