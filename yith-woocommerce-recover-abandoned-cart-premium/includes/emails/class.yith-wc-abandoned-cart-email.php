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
            $this->title       = __( 'Recover Abandoned Cart Email', 'yith-woocommerce-recover-abandoned-cart' );
            $this->description = __( 'This is the email sent to the customer from the admin with the YITH WooCommerce Recover Abandoned Cart plugin', 'yith-woocommerce-recover-abandoned-cart' );

            $this->heading = get_option('ywrac_email_sender_name');
            $this->subject = get_option('ywrac_email_subject');
            $this->reply_to= get_option('ywrac_email_sender');

            $this->template_html  = 'email/email-template.php';

            // Triggers for this email
            add_action( 'send_rac_mail_notification', array( $this, 'trigger' ), 15 );

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

	        $this->recipient     = $args['user_email'];
	        $this->email_content = $args['email_content'];
	        $this->subject       = $args['email_subject'];
	        $this->email_title   = get_the_title( $args['email_id'] );

            if( ! isset( $args['email_test'] ) ){



                $return = $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content_html(), $this->get_headers(), $this->get_attachments( ) );

                if ( $return ) {

                    $date = date( "Y-m-d H:i:s" , current_time('timestamp', 1) );
                    update_post_meta( $args['cart_id'], '_email_sent', $date );

                    //update cart meta '_emails_sent'
                    $emails_sent = get_post_meta( $args['cart_id'], '_emails_sent', true);


                    $emails_sent[$args['email_id']] = array(
                        'email_id'   => $args['email_id'],
                        'email_name' => $args['email_name'],
                        'data_sent'  => $date,
                        'clicked'    => 0
                    );
                    update_post_meta( $args['cart_id'], '_emails_sent', $emails_sent );

                    //update email template meta '_cart_emails_sent'
                    $cart_emails_sent = get_post_meta( $args['email_id'], '_cart_emails_sent', true);
                    $cart_emails_sent[] = $args['cart_id'];
                    update_post_meta( $args['email_id'], '_cart_emails_sent', $cart_emails_sent );

                    //update email template meta '_email_sent_counter'
                    YITH_WC_Recover_Abandoned_Cart_Helper()->update_counter_meta( $args['email_id'], '_email_sent_counter' );

                    //update general email sent counter
                    YITH_WC_Recover_Abandoned_Cart_Helper()->update_counter('email_sent_counter');

                    //update logs
                    YITH_WC_Recover_Abandoned_Cart_Helper()->email_log( $args['user_email'], $args['email_id'], $args['cart_id'], $date );


                }
            }else{

                $return = $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content_html(), $this->get_headers(), $this->get_attachments( ) );
                if ( $return ) {
                    update_post_meta( $args['email_id'], '_email_test_sent', 1 );
                }
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
                'email_heading' => $this->email_title ,
                'sent_to_admin' => true,
                'plain_text'    => false,
                'email'         => $this
            ) );
            return ob_get_clean();
        }


    }
}


// returns instance of the mail on file include
return new YITH_YWRAC_Send_Email();