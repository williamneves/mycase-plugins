<?php
/**
 * Customer Reset Password email
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates/Emails
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<?php
	$mail_type = "customer_reset_password";
	do_action( 'yith_wcet_email_header', $email_heading, $mail_type);
?>

<p><?php _e( 'Someone requested a password reset for the following account:', 'woocommerce' ); ?></p>
<p><?php printf( __( 'Username: %s', 'woocommerce' ), $user_login ); ?></p>
<p><?php _e( 'If this was a mistake, just ignore this email and nothing will happen.', 'woocommerce' ); ?></p>
<p><?php _e( 'To reset your password, visit the following address:', 'woocommerce' ); ?></p>
<p>
    <a href="<?php echo esc_url( add_query_arg( array( 'key' => $reset_key, 'login' => rawurlencode( $user_login ) ), wc_get_endpoint_url( 'lost-password', '', wc_get_page_permalink( 'myaccount' ) ) ) ); ?>">
			<?php _e( 'Click here to reset your password', 'woocommerce' ); ?></a>
</p>
<p></p>

<?php do_action( 'yith_wcet_email_footer', $mail_type); ?>

