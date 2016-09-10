<?php
if ( !defined( 'ABSPATH' ) || !defined( 'YITH_WCCOS_PREMIUM' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Implements features of FREE version of YITH WooCommerce Custom Order Status
 *
 * @class   YITH_WCCOS_Admin_Premium
 * @package YITH WooCommerce Custom Order Status
 * @since   1.0.0
 * @author  Yithemes
 */

if ( !class_exists( 'YITH_WCCOS_Admin_Premium' ) ) {
    /**
     * Admin class.
     * The class manage all the admin behaviors.
     *
     * @since 1.0.0
     */
    class YITH_WCCOS_Admin_Premium extends YITH_WCCOS_Admin {
        /**
         * Returns single instance of the class
         *
         * @return \YITH_WCCOS
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
         * @since  1.0.0
         */
        public function __construct() {
            parent::__construct();
            add_action( 'woocommerce_order_status_changed', array( $this, 'woocommerce_order_status_changed' ), 10, 3 );
            add_filter( 'woocommerce_reports_order_statuses', array( $this, 'woocommerce_reports_order_statuses' ) );

            if ( is_admin() ) {
                include_once( 'includes/class-yit-icon.php' );
                YITH_WCCOS_ICON();

                add_filter( 'yith_wccos_tabs_metabox', array( $this, 'metabox_premium' ) );
                add_action( 'load-edit.php', array( $this, 'bulk_actions_handler' ) );

                // register plugin to licence/update system
                add_action( 'wp_loaded', array( $this, 'register_plugin_for_activation' ), 99 );
                add_action( 'admin_init', array( $this, 'register_plugin_for_updates' ) );

                // Remove submenu Custom Order Status in yit plugin menu
                add_action( 'admin_menu', array( $this, 'remove_submenu_pages' ), 999 );

                add_filter( 'yit_fw_metaboxes_type_args', array( $this, 'custom_type_icons' ) );
            }
        }

        public function custom_type_icons( $args ) {
            if ( isset( $args[ 'type' ] ) && $args[ 'type' ] == 'yith-wccos-icons' ) {
                $new_args = array(
                    'basename' => YITH_WCCOS_DIR,
                    'path'     => 'metaboxes/',
                    'type'     => 'yith-wccos-icons',
                    'args'     => $args[ 'args' ],
                );

                return $new_args;
            }

            return $args;
        }


        /**
         * Remove submenu Custom Order Status in yit plugin menu
         *
         * @access public
         * @since  1.0.0
         * @author Leanza Francesco <leanzafrancesco@gmail.com>
         */
        public function remove_submenu_pages() {
            remove_submenu_page( 'yit_plugin_panel', $this->_panel_page );
        }

        /**
         * Add orders with custom statuses in Reports
         *
         * @return array
         * @access public
         * @since  1.0.0
         * @author Leanza Francesco <leanzafrancesco@gmail.com>
         */
        public function woocommerce_reports_order_statuses( $statuses ) {
            // fix for woocommerce refund in reports
            if ( !is_array( $statuses ) || $statuses == array( 'refunded' ) )
                return $statuses;

            $status_posts = get_posts( array(
                                           'posts_per_page' => -1,
                                           'post_type'      => 'yith-wccos-ostatus',
                                           'post_status'    => 'publish',
                                       ) );

            $new_statuses = array();

            $display_default_statuses = array();

            foreach ( (array) $statuses as $status ) {
                $display_default_statuses[ $status ] = 1;
            }

            foreach ( $status_posts as $sp ) {
                $display = get_post_meta( $sp->ID, 'display-in-reports', true );
                $slug    = get_post_meta( $sp->ID, 'slug', true );
                if ( $display ) {
                    if ( !in_array( $slug, (array) $statuses ) ) {
                        $new_statuses[] = $slug;
                    }
                } else {
                    if ( in_array( $slug, (array) $statuses ) ) {
                        $display_default_statuses[ $slug ] = 0;
                    }
                }
            }

            foreach ( $display_default_statuses as $key => $value ) {
                if ( $value ) {
                    $new_statuses[] = $key;
                }
            }

            return $new_statuses;
        }

        /**
         * Handler for status changed; send emails for custom order statuses
         *
         * @return void
         * @access public
         * @since  1.0.0
         * @author Leanza Francesco <leanzafrancesco@gmail.com>
         */
        public function woocommerce_order_status_changed( $order_id, $old_status, $new_status ) {
            $order = new WC_Order( $order_id );

            $custom_status = get_posts( array(
                                            'posts_per_page' => -1,
                                            'post_type'      => 'yith-wccos-ostatus',
                                            'post_status'    => 'publish',
                                            'meta_key'       => 'slug',
                                            'meta_value'     => $new_status,
                                        ) );


            if ( !!$custom_status && $custom_status[ 0 ] ) {
                $current_cos = $custom_status[ 0 ];

                $status_id           = $current_cos->ID;
                $send_mail           = get_post_meta( $status_id, 'sendmail', true );
                $send_mail           = !!$send_mail ? $send_mail : 0;
                $downloads_permitted = get_post_meta( $status_id, 'downloads-permitted', true );
                $custom_recipient    = get_post_meta( $status_id, 'custom_recipient', true );
                if ( $downloads_permitted ) {
                    wc_downloadable_product_permissions( $order_id );
                }

                $mailer = WC()->mailer();
                $dests  = array();

                switch ( $send_mail ) {
                    case '1':
                        $dests = array( get_option( 'admin_email' ) => true );
                        break;
                    case '2':
                        $dests = array( $order->billing_email => false );
                        break;
                    case '3':
                        $dests                                = array( $order->billing_email => false );
                        $dests[ get_option( 'admin_email' ) ] = true;
                        break;
                    case '4':
                        $dests = array( $custom_recipient => false );
                        break;
                    default:
                }

                if ( !!$dests ) {

                    $notification_args = array(
                        'heading'              => get_post_meta( $status_id, 'mail_heading', true ),
                        'subject'              => get_post_meta( $status_id, 'mail_subject', true ),
                        'from_name'            => get_post_meta( $status_id, 'mail_name_from', true ),
                        'from_email'           => get_post_meta( $status_id, 'mail_from', true ),
                        'display_order_info'   => get_post_meta( $status_id, 'mail_order_info', true ),
                        'custom_email_address' => $custom_recipient,
                        'order'                => $order,
                        'custom_message'       => get_post_meta( $status_id, 'mail_custom_message', true ),
                    );

                    foreach ( $dests as $dest => $sent_to_admin ) {
                        $notification_args[ 'recipient' ]     = $dest;
                        $notification_args[ 'sent_to_admin' ] = $sent_to_admin;
                        do_action( 'yith_wccos_custom_order_status_notification', $notification_args );
                    }
                }
            }
        }


        public function metabox_premium( $tabs ) {

            $statuses = wc_get_order_statuses();

            //var_dump($statuses);

            $premium_fields = array(
                'status_type' => array(
                    'label'   => __( 'Status Type', 'yith-woocommerce-custom-order-status' ),
                    'desc'    => __( 'Select a type for your status.', 'yith-woocommerce-custom-order-status' ),
                    'type'    => 'select',
                    'options' => array(
                        'custom'     => _x( 'Custom Status', 'Select status type', 'yith-woocommerce-custom-order-status' ),
                        'pending'    => _x( 'Pending Payment', 'Select status type', 'yith-woocommerce-custom-order-status' ),
                        'processing' => _x( 'Processing', 'Select status type', 'yith-woocommerce-custom-order-status' ),
                        'on-hold'    => _x( 'On Hold', 'Select status type', 'yith-woocommerce-custom-order-status' ),
                        'completed'  => _x( 'Completed', 'Select status type', 'yith-woocommerce-custom-order-status' ),
                        'cancelled'  => _x( 'Cancelled', 'Select status type', 'yith-woocommerce-custom-order-status' ),
                        'refunded'   => _x( 'Refunded', 'Select status type', 'yith-woocommerce-custom-order-status' ),
                        'failed'     => _x( 'Failed', 'Select status type', 'yith-woocommerce-custom-order-status' ),
                    ),
                    'private' => false,
                    'std'     => 'custom',
                ),
            );

            $tabs[ 'settings' ][ 'fields' ] = array_merge( $premium_fields, $tabs[ 'settings' ][ 'fields' ] );

            $tabs[ 'settings' ][ 'fields' ][ 'icon' ] = array(
                'label'   => __( 'Icon', 'yith-woocommerce-custom-order-status' ),
                'desc'    => __( 'Icon of your status', 'yith-woocommerce-custom-order-status' ),
                'type'    => 'yith-wccos-icons',
                'private' => false,
                'options' => array(
                    'select' => array(
                        'none' => __( 'Default', 'yith-woocommerce-custom-order-status' ),
                        'icon' => __( 'Choose Icon', 'yith-woocommerce-custom-order-status' ),
                        //'custom' => __( 'Select File', 'yith-woocommerce-custom-order-status' ),
                    ),
                    'icon'   => array(
                        'uno' => 'one',
                        'due' => 'two',
                        'tre' => 'three',
                    ),
                ),
                'std'     => array(
                    'select' => 'none',
                    'icon'   => 'FontAwesome:genderless',
                ),
            );

            $tabs[ 'settings' ][ 'fields' ][ 'graphicstyle' ] = array(
                'label'   => __( 'Graphic Style', 'yith-woocommerce-custom-order-status' ),
                'desc'    => __( 'Style of your status button and indicator', 'yith-woocommerce-custom-order-status' ),
                'type'    => 'select',
                'options' => array(
                    'icon' => __( 'Icon', 'yith-woocommerce-custom-order-status' ),
                    'text' => __( 'Text', 'yith-woocommerce-custom-order-status' ),
                ),
                'private' => false,

            );

            $tabs[ 'settings' ][ 'fields' ][ 'nextactions' ] = array(
                'label'    => __( 'Next Actions', 'yith-woocommerce-custom-order-status' ),
                'desc'     => __( 'Select statuses that will be enabled by this status', 'yith-woocommerce-custom-order-status' ),
                'type'     => 'chosen',
                'options'  => $statuses,
                'std'      => array(
                    'wc-completed',
                ),
                'multiple' => true,
                'private'  => false,

            );

            $tabs[ 'settings' ][ 'fields' ][ 'can-cancel' ] = array(
                'label'   => __( 'User can cancel', 'yith-woocommerce-custom-order-status' ),
                'desc'    => __( 'Choose whether the customer can cancel orders when this status is applied or not', 'yith-woocommerce-custom-order-status' ),
                'type'    => 'checkbox',
                'private' => false,
            );

            $tabs[ 'settings' ][ 'fields' ][ 'can-pay' ] = array(
                'label'   => __( 'User can pay', 'yith-woocommerce-custom-order-status' ),
                'desc'    => __( 'Choose whether the customer can pay orders when this status is applied or not', 'yith-woocommerce-custom-order-status' ),
                'type'    => 'checkbox',
                'private' => false,
            );

            $tabs[ 'settings' ][ 'fields' ][ 'downloads-permitted' ] = array(
                'label'   => __( 'Allow Downloads', 'yith-woocommerce-custom-order-status' ),
                'desc'    => __( 'Choose whether you want to allow downloads when this status is applied or not', 'yith-woocommerce-custom-order-status' ),
                'type'    => 'checkbox',
                'private' => false,
            );

            $tabs[ 'settings' ][ 'fields' ][ 'display-in-reports' ] = array(
                'label'   => __( 'Display in Reports', 'yith-woocommerce-custom-order-status' ),
                'desc'    => __( 'Choose whether you want to include orders marked with this status in Reports', 'yith-woocommerce-custom-order-status' ),
                'type'    => 'checkbox',
                'private' => false,
            );

            $tabs[ 'settings' ][ 'fields' ][ 'mail-settings-info' ] = array(
                'label'   => __( 'Email Settings', 'yith-woocommerce-custom-order-status' ),
                'desc'    => __( 'To set emails for WooCommerce default status, use WooCommerce Panel in ', 'yith-woocommerce-custom-order-status' ) . '<a href="admin.php?page=wc-settings&tab=email">' . __( 'WooCommerce -> Settings -> Emails', 'yith-woocommerce-custom-order-status' ) . '</a>',
                'type'    => 'simple-text',
                'private' => false,
            );


            $tabs[ 'mail_settings' ] = array( //tab
                                              'label'  => __( 'Email Settings', 'yith-woocommerce-custom-order-status' ),
                                              'fields' => array(
                                                  'sendmail'            => array(
                                                      'label'   => __( 'Send email to', 'yith-woocommerce-custom-order-status' ),
                                                      'desc'    => __( 'Choose recipients of email notifications for this status; you can configure email settings from tab "Email Settings" on top', 'yith-woocommerce-custom-order-status' ),
                                                      'type'    => 'select',
                                                      'options' => array(
                                                          '0' => __( 'None', 'yith-woocommerce-custom-order-status' ),
                                                          '1' => __( 'Administrator', 'yith-woocommerce-custom-order-status' ),
                                                          '2' => __( 'Customer', 'yith-woocommerce-custom-order-status' ),
                                                          '3' => __( 'Administrator and Customer', 'yith-woocommerce-custom-order-status' ),
                                                          '4' => __( 'Custom Email Address', 'yith-woocommerce-custom-order-status' ),
                                                      ),
                                                      'private' => false,
                                                  ),
                                                  'custom_recipient'    => array(
                                                      'label'   => __( 'Recipient Email Address', 'yith-woocommerce-custom-order-status' ),
                                                      'desc'    => __( 'Type here the email address to notify when the selected status is selected', 'yith-woocommerce-custom-order-status' ),
                                                      'type'    => 'text',
                                                      'private' => false,
                                                      'std'     => '',
                                                  ),
                                                  'mail_name_from'      => array(
                                                      'label'   => __( '"From" Name', 'yith-woocommerce-custom-order-status' ),
                                                      'desc'    => __( 'Enter the email sender name which will appear to recipients', 'yith-woocommerce-custom-order-status' ),
                                                      'type'    => 'text',
                                                      'private' => false,
                                                      'std'     => get_bloginfo( 'name' ),
                                                  ),
                                                  'mail_from'           => array(
                                                      'label'   => __( '"From" Email Address', 'yith-woocommerce-custom-order-status' ),
                                                      'desc'    => __( 'Enter the email address which will appear to recipients', 'yith-woocommerce-custom-order-status' ),
                                                      'type'    => 'text',
                                                      'private' => false,
                                                      'std'     => get_option( 'admin_email' ),
                                                  ),
                                                  'mail_subject'        => array(
                                                      'label'   => __( 'Email Subject', 'yith-woocommerce-custom-order-status' ),
                                                      'desc'    => __( 'Enter the email subject which will appear to recipients of the email', 'yith-woocommerce-custom-order-status' ),
                                                      'type'    => 'text',
                                                      'private' => false,
                                                      'std'     => '',
                                                  ),
                                                  'mail_heading'        => array(
                                                      'label'   => __( 'Email Heading', 'yith-woocommerce-custom-order-status' ),
                                                      'desc'    => __( 'Enter the heading you want to appear in the email sent', 'yith-woocommerce-custom-order-status' ),
                                                      'type'    => 'text',
                                                      'private' => false,
                                                      'std'     => '',
                                                  ),
                                                  'mail_custom_message' => array(
                                                      'label'   => __( 'Custom Message', 'yith-woocommerce-custom-order-status' ),
                                                      'desc'    => __( 'Available Shortcodes: {customer_first_name} , {customer_last_name} , {order_date} , {order_number} , {order_value} , {billing_address} , {shipping_address}', 'yith-woocommerce-custom-order-status' ),
                                                      'type'    => 'textarea',
                                                      'private' => false,
                                                      'std'     => '',
                                                  ),
                                                  'mail_order_info'     => array(
                                                      'label'   => __( 'Include Order Information', 'yith-woocommerce-custom-order-status' ),
                                                      'desc'    => __( 'Select whether you want to include order information (billing and shipping address, order items, total, etc)', 'yith-woocommerce-custom-order-status' ),
                                                      'type'    => 'checkbox',
                                                      'private' => false,
                                                      'std'     => '',
                                                  ),

                                              ),
            );

            return $tabs;
        }

        /**
         * Add Button Actions in Order list
         *
         * @return   array
         * @since    1.0
         * @author   Leanza Francesco <leanzafrancesco@gmail.com>
         */
        function add_submit_to_order_admin_actions( $actions, $the_order ) {
            global $post;

            $status_posts = get_posts( array(
                                           'posts_per_page' => -1,
                                           'post_type'      => 'yith-wccos-ostatus',
                                           'post_status'    => 'publish',
                                       ) );
            $status_names = array();

            foreach ( $status_posts as $sp ) {
                $status_names[] = get_post_meta( $sp->ID, 'slug', true );
            }


            // Add all status to on-hold status if 'on-hold' is not customized

            if ( !in_array( 'on-hold', $status_names ) ) {
                if ( $the_order->has_status( 'on-hold' ) ) {
                    foreach ( $status_posts as $sp ) {
                        $meta   = array(
                            'label' => $sp->post_title,
                            'slug'  => get_post_meta( $sp->ID, 'slug', true ),
                        );
                        $action = $meta[ 'slug' ];
                        if ( $action == 'completed' ) {
                            $actions[ 'complete' ] = array(
                                'url'    => wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce_mark_order_status&status=completed&order_id=' . $post->ID ), 'woocommerce-mark-order-status' ),
                                'name'   => __( 'Complete', 'woocommerce' ),
                                'action' => "complete",
                            );
                        } else {
                            $actions[ $action ] = array(
                                'url'    => wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce_mark_order_status&status=' . $action . '&order_id=' . $post->ID ), 'woocommerce-mark-order-status' ),
                                'name'   => $meta[ 'label' ],
                                'action' => $action,
                            );
                        }
                    }
                }
            }

            // Add next action for all statuses
            foreach ( $status_posts as $sp ) {
                $meta = array(
                    'label'       => $sp->post_title,
                    'color'       => get_post_meta( $sp->ID, 'color', true ),
                    'nextactions' => ( ( get_post_meta( $sp->ID, 'nextactions', true ) ) != null ) ? get_post_meta( $sp->ID, 'nextactions', true ) : array(),
                    'slug'        => get_post_meta( $sp->ID, 'slug', true ),
                );

                if ( $the_order->has_status( array( $meta[ 'slug' ] ) ) ) {
                    unset( $actions[ 'complete' ] );
                    unset( $actions[ 'processing' ] );
                    foreach ( $meta[ 'nextactions' ] as $action ) {
                        if ( !wc_is_order_status( $action ) )
                            continue;
                        $action = str_replace( "wc-", "", $action );
                        if ( $action == 'completed' ) {
                            $actions[ 'complete' ] = array(
                                'url'    => wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce_mark_order_status&status=completed&order_id=' . $post->ID ), 'woocommerce-mark-order-status' ),
                                'name'   => __( 'Complete', 'woocommerce' ),
                                'action' => "complete",
                            );
                        } else {
                            $actions[ $action ] = array(
                                'url'    => wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce_mark_order_status&status=' . $action . '&order_id=' . $post->ID ), 'woocommerce-mark-order-status' ),
                                'name'   => $action,
                                'action' => $action,
                            );
                        }
                    }
                }
            }

            return $actions;
        }

        public function admin_enqueue_scripts() {
            parent::admin_enqueue_scripts();

            $screen = get_current_screen();
            if ( 'edit-shop_order' == $screen->id ) {
                wp_enqueue_script( 'yith_wccos_order_bulk_actions', YITH_WCCOS_ASSETS_URL . '/js/order_bulk_actions.js', array( 'jquery' ), '1.0.0', true );
                $status_posts = get_posts( array(
                                               'posts_per_page' => -1,
                                               'post_type'      => 'yith-wccos-ostatus',
                                               'post_status'    => 'publish',
                                           ) );

                $wc_status = array( 'pending', 'processing', 'on-hold', 'completed', 'cancelled', 'refunded', 'failed' );

                $my_custom_status = array();

                foreach ( $status_posts as $sp ) {
                    $slug  = get_post_meta( $sp->ID, 'slug', true );
                    $label = $sp->post_title;
                    //if ( !in_array( $slug, $wc_status ) ){
                    $my_custom_status[ $slug ] = $label;
                    //}
                }
                $mark_text = __( "Mark", "woocommerce_status_actions" );

                wp_localize_script( 'yith_wccos_order_bulk_actions', 'localized_obj', array( 'my_custom_status' => $my_custom_status, 'mark_text' => $mark_text ) );
            }
        }

        public function get_status_inline_css() {
            $css          = '';
            $status_posts = get_posts( array(
                                           'posts_per_page' => -1,
                                           'post_type'      => 'yith-wccos-ostatus',
                                           'post_status'    => 'publish',
                                       ) );

            foreach ( $status_posts as $sp ) {
                $name = get_post_meta( $sp->ID, 'slug', true );
                $meta = array(
                    'label'        => $sp->post_title,
                    'color'        => get_post_meta( $sp->ID, 'color', true ),
                    'icon'         => get_post_meta( $sp->ID, 'icon', true ),
                    'graphicstyle' => get_post_meta( $sp->ID, 'graphicstyle', true ),
                );

                $my_icon                    = isset( $meta[ 'icon' ][ 'icon' ] ) ? $meta[ 'icon' ][ 'icon' ] : 'FontAwesome:genderless';
                $meta[ 'icon' ][ 'select' ] = isset( $meta[ 'icon' ][ 'select' ] ) ? $meta[ 'icon' ][ 'select' ] : 'none';

                $icon_data = YITH_WCCOS_ICON()->get_icon_data_array( $my_icon );
                $no_icon   = ( $meta[ 'icon' ][ 'select' ] == 'none' ) ? true : false;

                if ( $meta[ 'graphicstyle' ] == 'text' ) {
                    $icon_data[ 'icon' ] = $meta[ 'label' ];
                    $icon_data[ 'font' ] = 'Open Sans';

                    $css .= '.widefat .column-order_status mark.' . $name . '::after, .yith_status_icon mark.' . $name . '::after, mark.' . $name . '::after{
                                content:"' . $icon_data[ 'icon' ] . '" !important;
                                color: #FFFFFF !important;
                                background:' . $meta[ 'color' ] . ' !important;
                                font-family: ' . $icon_data[ 'font' ] . ' !important;
                                font-variant: normal !important;
                                text-transform: none !important;
                                line-height: 1 !important;
                                margin: 0px !important;
                                text-indent: 0px !important;
                                position: absolute !important;
                                top: 0px !important;
                                left: calc(50% - 35px) !important;
                                width: 70px !important;
                                text-align: center !important;
                                font-size:9px !important;
                                padding: 5px 3px !important;
                                box-sizing: border-box !important;
                                border-radius: 3px !important;
                            }';

                    if ( $name == 'completed' ) {
                        $name = 'complete';
                    }

                    $css .= '.order_actions .' . $name . '{
                                display: block;
                                padding: 0px 7px !important;
                                color:' . $meta[ 'color' ] . ' !important;
                            }';

                    $css .= '.order_actions .' . $name . '::after{
                                color:' . $meta[ 'color' ] . ' !important;
                            }';
                } else {
                    $wc_status = array(
                        'pending',
                        'processing',
                        'on-hold',
                        'completed',
                        'cancelled',
                        'refunded',
                        'failed',
                    );

                    if ( $no_icon && in_array( $name, $wc_status ) ) {
                        $css .= '.widefat .column-order_status mark.' . $name . '::after, .yith_status_icon mark.' . $name . '::after, mark.' . $name . '::after{
		                                color:' . $meta[ 'color' ] . ' !important;
		                            }';
                        if ( $name == 'completed' ) {
                            $name = 'complete';
                        }

                        $css .= '.order_actions .' . $name . '::after {
		                                color: ' . $meta[ 'color' ] . ';
		                            }';
                    } else {
                        $css .= '.widefat .column-order_status mark.' . $name . '::after, .yith_status_icon mark.' . $name . '::after, mark.' . $name . '::after{
		                               content:"' . $icon_data[ 'icon' ] . '" !important;
		                               color:' . $meta[ 'color' ] . ' !important;
		                               font-family: ' . $icon_data[ 'font' ] . ' !important;
		                               font-weight: 400;
		                               font-variant: normal;
		                               text-transform: none;
		                               line-height: 1;
		                               margin: 0px;
		                               text-indent: 0px;
		                               position: absolute;
		                               top: 0px;
		                               left: 0px;
		                               width: 100%;
		                               height: 100%;
		                               text-align: center;
		                           }';


                        if ( $name == 'completed' ) {
                            $name = 'complete';
                        }

                        $css .= '.order_actions .' . $name . '{
		                               display: block;
		                               text-indent: -9999px;
		                               position: relative;
		                               padding: 0px !important;
		                               height: 2em !important;
		                               width: 2em;
		                           }';

                        $css .= '.order_actions .' . $name . '::after {
		                              	content:"' . $icon_data[ 'icon' ] . '" !important;
		                               color: ' . $meta[ 'color' ] . ';
		                               font-family: ' . $icon_data[ 'font' ] . ' !important;
		                               text-indent: 0px;
		                               position: absolute;
		                               width: 100%;
		                               height: 100%;
		                               font-weight: 400;
		                               text-align: center;
		                               margin: 0px;
		                               font-variant: normal;
		                               text-transform: none;
		                               top: 0px;
		                               left: 0px;
		                               line-height: 1.85;
		                           }';
                    }
                }
            }

            return $css;
        }

        public function bulk_actions_handler() {
            if ( !isset( $_REQUEST[ 'post' ] ) ) {
                return;
            }
            $wp_list_table = _get_list_table( 'WP_Posts_List_Table' );
            $action        = $wp_list_table->current_action();

            $changed  = 0;
            $post_ids = array_map( 'absint', (array) $_REQUEST[ 'post' ] );
            if ( strstr( $action, 'mark_custom_status' ) ) {
                $new_status    = substr( $action, strlen( 'mark_custom_status_' ) );
                $report_action = "order_status_changed";
                foreach ( $post_ids as $post_id ) {
                    $order = new WC_Order( $post_id );
                    $order->update_status( $new_status );
                    $changed++;
                }
            } else {
                return;
            }
            $sendback = add_query_arg( array( 'post_type' => 'shop_order', $report_action => $changed, 'ids' => join( ',', $post_ids ) ), '' );
            wp_redirect( $sendback );
            exit();
        }

        /**
         * Register plugins for activation tab
         *
         * @return void
         * @since 1.0.0
         */
        public function register_plugin_for_activation() {
            if ( !class_exists( 'YIT_Plugin_Licence' ) ) {
                require_once( YITH_WCCOS_DIR . 'plugin-fw/lib/yit-plugin-licence.php' );
            }

            YIT_Plugin_Licence()->register( YITH_WCCOS_INIT, YITH_WCCOS_SECRET_KEY, YITH_WCCOS_SLUG );
        }

        /**
         * Register plugins for update tab
         *
         * @return void
         * @since 1.0.0
         */
        public function register_plugin_for_updates() {
            if ( !class_exists( 'YIT_Upgrade' ) ) {
                require_once( YITH_WCCOS_DIR . 'plugin-fw/lib/yit-upgrade.php' );
            }

            YIT_Upgrade()->register( YITH_WCCOS_SLUG, YITH_WCCOS_INIT );
        }
    }
}

/**
 * Unique access to instance of YITH_WCCOS_Admin_Premium class
 *
 * @return \YITH_WCCOS_Admin_Premium
 * @since 1.0.0
 */
function YITH_WCCOS_Admin_Premium() {
    return YITH_WCCOS_Admin_Premium::get_instance();
}

?>
