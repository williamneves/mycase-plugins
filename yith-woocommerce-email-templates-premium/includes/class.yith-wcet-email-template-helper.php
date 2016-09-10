<?php
/**
 * Email Template Helper
 *
 * @author  Yithemes
 * @package YITH WooCommerce Email Templates
 * @version 1.2.0
 */

defined( 'YITH_WCET' ) || exit; // Exit if accessed directly

if ( !class_exists( 'YITH_WCET_Email_Template_Helper' ) ) {
    /**
     * YITH_WCET_Email_Template_Helper class.
     * The class manage all the admin behaviors.
     *
     * @since    1.2.0
     * @author   Leanza Francesco <leanzafrancesco@gmail.com>
     */
    class YITH_WCET_Email_Template_Helper {

        /**
         * Single instance of the class
         *
         * @var YITH_WCET_Email_Template_Helper
         * @since 1.2.0
         */
        protected static $instance;

        public $templates;

        public $current_email;

        /**
         * Returns single instance of the class
         *
         * @return YITH_WCET_Email_Template_Helper || YITH_WCET_Email_Template_Helper_Premium
         * @since                                   1.2.0
         */
        public static function get_instance() {
            $self = __CLASS__ . ( class_exists( __CLASS__ . '_Premium' ) ? '_Premium' : '' );

            if ( is_null( $self::$instance ) ) {
                $self::$instance = new $self;
            }

            return $self::$instance;
        }

        /**
         * Constructor
         *
         * @access public
         */
        public function __construct() {
            $this->_init_templates();

            add_filter( 'wc_get_template', array( $this, 'custom_template' ), 999, 5 );

            add_action( 'woocommerce_email', array( $this, 'woocommerce_email' ) );

            add_filter( 'woocommerce_email_styles', array( $this, 'email_styles' ), 999 );
            add_filter( 'woocommerce_mail_content', array( $this, 'mail_content_styling' ) );

            add_action( 'admin_init', array( $this, 'preview_emails' ) );

        }

        /**
         * change woocommerce_email_header with Email Templates Header
         *
         * @param WC_Emails $mailer
         */
        public function woocommerce_email( $mailer ) {
            remove_action( 'woocommerce_email_header', array( $mailer, 'email_header' ) );
            add_action( 'woocommerce_email_header', array( $this, 'email_header' ), 10, 2 );

            remove_action( 'woocommerce_email_footer', array( $mailer, 'email_footer' ) );
            add_action( 'woocommerce_email_footer', array( $this, 'email_footer' ), 10, 2 );
        }

        private function _init_templates() {
            $templates = array(
                'emails/email-footer.php',
                'emails/email-header.php',
                'emails/email-order-details.php',
                'emails/email-order-items.php',
                'emails/email-styles.php'
            );

            $this->templates = apply_filters( 'yith_wcet_templates', $templates );
        }

        /**
         * Custom Template
         *
         * Filters wc_get_template for custom templates
         * @return string
         * @access   public
         * @since    1.0.0
         * @author   Leanza Francesco <leanzafrancesco@gmail.com>
         */
        public function custom_template( $located, $template_name, $args, $template_path, $default_path ) {
            if ( in_array( $template_name, $this->templates ) ) {

                return YITH_WCET_TEMPLATE_EMAIL_PATH . '/' . $template_name;
            }

            return $located;
        }

        /**
         * Woocommerce Email Styles
         *
         * @access   public
         * @since    1.0.0
         * @author   Leanza Francesco <leanzafrancesco@gmail.com>
         */
        public function email_styles( $style ) {
            return '';
        }

        public function email_header( $email_heading, $email = '' ) {
            global $current_email;

            if ( empty( $current_email ) )
                $current_email = $email;

            wc_get_template( 'emails/email-header.php', array( 'email_heading' => $email_heading, 'email' => $current_email ) );
        }

        public function email_footer( $email = '', $args = array() ) {
            wc_get_template( 'emails/email-footer.php', array( 'args' => $args, 'email' => $email ) );
        }

        /**
         * Mail Content Styling
         *
         * This func transforms css style of the mail in inline style; and return the content with the inline style
         * @return string
         * @access   public
         * @since    1.0.0
         * @author   Leanza Francesco <leanzafrancesco@gmail.com>
         */
        public function mail_content_styling( $content ) {
            // get CSS styles
            ob_start();
            wc_get_template( 'emails/email-styles.php' );
            $css = ob_get_clean();

            try {
                // apply CSS styles inline for picky email clients
                $emogrifier = new Emogrifier( $content, $css );
                $content    = $emogrifier->emogrify();

            } catch ( Exception $e ) {

                $logger = new WC_Logger();

                $logger->add( 'emogrifier', $e->getMessage() );
            }

            return $content;
        }

        /**
         * Preview email template
         *
         * @return string
         * @author   Leanza Francesco <leanzafrancesco@gmail.com>
         */
        public function preview_emails() {
            if ( isset( $_GET[ 'yith_wcet_preview_mail' ] ) ) {

                if ( isset( $_GET[ 'template_id' ] ) ) {
                    global $current_email;
                    $current_email = 'preview';
                    $template_id   = $_GET[ 'template_id' ];

                    // load the mailer class
                    $mailer = WC()->mailer();

                    // get the preview email subject
                    $email_heading = __( 'HTML Email Template', 'woocommerce' );

                    // get the preview email content
                    ob_start();
                    wc_get_template( '/views/html-email-template-preview.php', array( 'template_id' => $template_id ), YITH_WCET_TEMPLATE_PATH, YITH_WCET_TEMPLATE_PATH );
                    $message = ob_get_clean();

                    // wrap the content with the email template and then add styles
                    $message = $this->mail_content_styling( $mailer->wrap_message( $email_heading, $message ) );

                    // print the preview email
                    echo $message;
                    exit;
                }
            }
        }

    }
}

/**
 * Unique access to instance of YITH_WCET_Email_Template_Helper class
 *
 * @return YITH_WCET_Email_Template_Helper || YITH_WCET_Email_Template_Helper_Premium
 *
 * @since                                   1.2.0
 */
function YITH_WCET_Email_Template_Helper() {
    return YITH_WCET_Email_Template_Helper::get_instance();
}
