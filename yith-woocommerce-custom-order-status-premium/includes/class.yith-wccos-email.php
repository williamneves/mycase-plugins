<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

if ( !class_exists( 'YITH_WCCOS_Email' ) ) {
    class YITH_WCCOS_Email extends WC_Email {
        /**
         * @type string
         */
        public $heading = '';
        /**
         * @type string
         */
        public $from_name = '';
        /**
         * @type string
         */
        public $from_email = '';
        /**
         * @type string
         */
        public $custom_message = '';
        /**
         * @type bool
         */
        public $sent_to_admin = false;
        /**
         * @type bool
         */
        public $display_order_info = false;
        /**
         * @type WC_Order
         */
        public $order;

        public function __construct() {
            $this->id          = "custom_order_status_email";
            $this->title       = __( 'Custom Order Status Mail', 'yith-woocommerce-custom-order-status' );
            $this->description = __( "YITH WooCommerce Custom Order Status Mail", 'yith-woocommerce-custom-order-status' );

            $this->template_html  = 'emails/custom_status_email_template.php';
            $this->template_plain = 'emails/plain/custom_status_email_template.php';
            $this->template_base  = YITH_WCCOS_TEMPLATE_PATH . '/';

            // Triggers
            add_action( 'yith_wccos_custom_order_status_notification', array( $this, 'trigger' ) );

            parent::__construct();
        }

        public function is_pretty_mail_active() {
            return class_exists( 'WooCommerce_Pretty_Emails' ) && defined( 'MBWPE_TPL_PATH' );
        }

        /**
         * Trigger.
         *
         * @param array $args
         */
        public function trigger( $args ) {
            if ( !$this->is_enabled() ) {
                return;
            }

            $requested_fields = array(
                'heading',
                'subject',
                'from_name',
                'from_email',
                'display_order_info',
                'custom_email_address',
                'order',
                'recipient',
                'sent_to_admin'
            );
            if ( $args ) {
                foreach ( $requested_fields as $field ) {
                    if ( !isset( $args[ $field ] ) )
                        return;
                }
                $this->order                = $args[ 'order' ];
                $this->heading              = $this->apply_shortcode( $args[ 'heading' ], $this->order->id );
                $this->subject              = $args[ 'subject' ];
                $this->subject              = $this->apply_shortcode( $args[ 'subject' ], $this->order->id );
                $this->from_name            = stripslashes( $args[ 'from_name' ] );
                $this->from_email           = $args[ 'from_email' ];
                $this->display_order_info   = $args[ 'display_order_info' ];
                $this->custom_email_address = $args[ 'custom_email_address' ];
                $this->custom_message       = $this->apply_shortcode( $args[ 'custom_message' ], $this->order->id );
                $this->recipient            = $args[ 'recipient' ];
                $this->sent_to_admin        = $args[ 'sent_to_admin' ];

                if ( $this->get_recipient() ) {
                    $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
                }
            }
        }

        public function apply_shortcode( $content, $order_id ) {
            $order = new WC_Order( $order_id );

            $shortcode = array(
                '{customer_first_name}' => $order->billing_first_name,
                '{customer_last_name}'  => $order->billing_last_name,
                '{order_date}'          => date_i18n( wc_date_format(), strtotime( $order->order_date ) ),
                '{order_number}'        => $order->get_order_number(),
                '{order_value}'         => $order->order_total,
                '{billing_address}'     => $order->get_formatted_billing_address(),
                '{shipping_address}'    => $order->get_formatted_shipping_address()
            );

            $cont = strtr( $content, $shortcode );

            return stripslashes( nl2br( $cont ) );
        }

        public function get_content_html() {
            $base = $this->is_pretty_mail_active() ? $this->template_base . 'pretty-emails/' : $this->template_base;
            ob_start();
            wc_get_template( $this->template_html, array(
                'order'              => $this->order,
                'email_heading'      => $this->heading,
                'custom_message'     => $this->custom_message,
                'display_order_info' => $this->display_order_info,
                'sent_to_admin'      => $this->sent_to_admin,
                'email'              => $this,
                'plain_text'         => false,
            ), '', $base );

            return ob_get_clean();
        }

        /**
         * Get content plain.
         *
         * @return string
         */
        public function get_content_plain() {
            ob_start();
            wc_get_template( $this->template_plain, array(
                'email_heading'      => $this->get_heading(),
                'custom_message'     => $this->format_string( $this->custom_message ),
                'display_order_info' => $this->display_order_info,
                'sent_to_admin'      => $this->sent_to_admin,
                'email'              => $this,
                'plain_text'         => true,
            ), '', $this->template_base );

            return ob_get_clean();
        }

        /**
         * Initialise Settings Form Fields - these are generic email options most will use.
         */
        public function init_form_fields() {
            $this->form_fields = array(
                'enabled'    => array(
                    'title'   => __( 'Enable/Disable', 'woocommerce' ),
                    'type'    => 'checkbox',
                    'label'   => __( 'Enable this email notification', 'woocommerce' ),
                    'default' => 'yes'
                ),
                'email_type' => array(
                    'title'       => __( 'Email type', 'woocommerce' ),
                    'type'        => 'select',
                    'description' => __( 'Choose which format of email to send.', 'woocommerce' ),
                    'default'     => 'html',
                    'class'       => 'email_type wc-enhanced-select',
                    'options'     => $this->get_email_type_options()
                )
            );
        }
    }
}

return new YITH_WCCOS_Email();