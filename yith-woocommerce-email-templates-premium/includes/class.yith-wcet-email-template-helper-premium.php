<?php
/**
 * Email Template Helper PREMIUM
 *
 * @author  Yithemes
 * @package YITH WooCommerce Email Templates
 * @version 1.2.0
 */

defined( 'YITH_WCET' ) || exit; // Exit if accessed directly

if ( !class_exists( 'YITH_WCET_Email_Template_Helper_Premium' ) ) {
    /**
     * YITH_WCET_Email_Template_Helper_Premium class.
     * The class manage all the admin behaviors.
     *
     * @since    1.2.0
     * @author   Leanza Francesco <leanzafrancesco@gmail.com>
     */
    class YITH_WCET_Email_Template_Helper_Premium extends YITH_WCET_Email_Template_Helper {

        /**
         * Constructor
         *
         * @access public
         * @since  1.2.0
         */
        public function __construct() {
            parent::__construct();

            add_filter( 'yith_wcet_premium_email_extra_settings', array( $this, 'premium_email_extra_settings' ), 10, 2 );

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

                if ( $template_name == 'emails/email-styles.php' ) {
                    global $current_email;
                    $template = yith_wcet_get_email_template( $current_email );
                    $meta     = get_post_meta( $template, '_template_meta', true );
                    if ( $meta ) {
                        $premium_style = isset( $meta[ 'premium_mail_style' ] ) && $meta[ 'premium_mail_style' ] > 0 ? $meta[ 'premium_mail_style' ] : '';
                        $template_name = "emails/email-styles{$premium_style}.php";
                    }
                }

                /**
                 * to override templates of Email Templates put them into THEME_FOLDER/yith-woocommerce-email-templates/emails/
                 */

                $template         = locate_template( 'yith-woocommerce-email-templates/' . $template_name );
                $default_template = YITH_WCET_TEMPLATE_EMAIL_PATH . '/' . $template_name;

                if ( !$template ) {
                    $template = $default_template;
                }

                return apply_filters( 'yith_wcet_get_template', $template, $template_name );
                //return YITH_WCET_TEMPLATE_EMAIL_PATH . '/' . $template_name;
            }

            return $located;
        }

        public function premium_email_extra_settings( $settings, $templates_array ) {
            $settings[] = array(
                'title' => __( 'YITH WooCommerce Email Settings', 'yith-woocommerce-email-templates' ),
                'type'  => 'title',
                'desc'  => __( 'Select templates for email', 'yith-woocommerce-email-templates' ),
                'id'    => 'yith-wcet-email-extra-settings'
            );

            $mailer = WC()->mailer();
            $emails = $mailer->get_emails();

            foreach ( $emails as $email ) {
                if ( apply_filters( 'yith_wcet_hide_email_in_settings', false, $email ) )
                    continue;

                $settings[] = array(
                    'id'       => 'yith-wcet-email-template-' . $email->id,
                    'name'     => $email->title . ' ' . __( 'Template', 'yith-woocommerce-email-templates' ),
                    'type'     => 'select',
                    'desc_tip' => sprintf( __( 'Select the email template that you want to use for the %s email', 'yith-woocommerce-email-templates' ), $email->title ),
                    'class'    => 'email_type wc-enhanced-select',
                    'options'  => $templates_array,
                    'default'  => 'default'
                );
            }

            $settings[] = array(
                'type' => 'sectionend',
                'id'   => 'yith_wcet_email_extra_settings'
            );

            return $settings;
        }


    }
}