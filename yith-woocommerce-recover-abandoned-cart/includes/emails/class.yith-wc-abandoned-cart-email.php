<?php
if ( !defined( 'ABSPATH' ) || !defined( 'YITH_YWRAC_VERSION' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Implements features of YITH WooCommerce Recover Abandoned Cart
 *
 * @class   YITH_YWRAC_Send_Email
 * @package YITH WooCommerce Recover Abandoned Cart
 * @since   1.0.0
 * @author  Yithemes
 */
if ( !class_exists( 'YITH_YWRAC_Send_Email' ) ) {

    /**
     * YITH_YWRAC_Send_Email
     *
     * @since 1.0.0
     */
    class YITH_YWRAC_Send_Email extends WC_Email {

        /**
         * Constructor method, used to return object of the class to WC
         *
         * @since 1.0.0
         */
        public function __construct() {
            $this->id          = 'ywrac_email';
            $this->title       = __( 'Recover Abandoned Cart Email', 'ywrac' );
            $this->description = __( 'This is the email sent to the customer from the admin in YITH WooCommerce Recover Abandoned Cart Plugin', 'ywrac' );

            $this->heading = get_option('ywrac_email_sender_name');
            $this->subject = get_option('ywrac_email_subject');
            $this->reply_to= get_option('ywrac_email_sender');

            $this->template_html  = 'email/email-template.php';

            // Triggers for this email
            add_action( 'send_rac_mail_notification', array( $this, 'trigger' ), 15, 1 );

            // Call parent constructor
            parent::__construct();
        }

        /**
         * Method triggered to send email
         *
         * @param int $args
         *
         * @return void
         * @since  1.0
         * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
         */
        public function trigger( $args ) {


            $this->recipient = $args['user_email'];
            $this->email_content = $args['email_content'];

            $return = $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content_html(), $this->get_headers(), $this->get_attachments(  ) );

            if ( $return ) {
                update_post_meta( $args['cart_id'], '_email_sent', date("Y-m-d H:i:s"));
            }
        }

        /**
         * get_headers function.
         *
         * @access public
         * @return string
         */
          public function get_headers() {
            $headers = "Reply-to: " . $this->reply_to . "\r\n";
            $headers .= "Content-Type: " . $this->get_content_type() . "\r\n";

            return apply_filters( 'woocommerce_email_headers', $headers, $this->id, $this->object );
        }

        /**
         * Get HTML content for the mail
         *
         * @return string HTML content of the mail
         * @since  1.0
         * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
         */
        public function get_content_html() {
            ob_start();
            wc_get_template( $this->template_html, array(
                'email_content' => $this->email_content,
                'email_heading' => $this->heading ,
                'sent_to_admin' => true,
                'plain_text'    => false
            ) );
            return ob_get_clean();
        }


    }
}


// returns instance of the mail on file include
return new YITH_YWRAC_Send_Email();