<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! defined( 'YWQE_RESET_DROPBOX' ) ) {
	define( 'YWQE_RESET_DROPBOX', 'reset-dropbox' );
}

if ( ! class_exists( 'YWQE_Custom_Types' ) ) {

	/**
	 * custom types fields
	 *
	 * @class YWQE_Custom_Types
	 * @package Yithemes
	 * @since   1.0.0
	 * @author  Your Inspiration Themes
	 */
	class YWQE_Custom_Types {

		/**
		 * Single instance of the class
		 *
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Returns single instance of the class
		 *
		 * @since 1.0.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * @var store the DropBox singleton Instancew
		 */
		protected $dropbox;

		public function __construct() {

			$this->dropbox = YITH_DropBox::get_instance();

			/**
			 * Manage showing and saving of dropbox save button
			 */
			add_action( 'woocommerce_admin_field_ywqe_dropbox', array( $this, 'yit_enable_dropbox' ), 10, 1 );
			add_action( 'woocommerce_update_option_ywqe_dropbox', array( $this, 'yit_save_dropbox' ), 10, 1 );
		}

		public function yit_enable_dropbox( $args = array() ) {
			if ( ! empty( $args ) ) {
				$args['value'] = ( get_option( $args['id'] ) ) ? get_option( $args['id'] ) : '';
				extract( $args );
			}

			$show_dropbox_login = false;

			?>
			<tr valign="top">
				<th scope="row">
					<label for="ywqe_enable_dropbox"><?php echo $name; ?></label>
				</th>
				<td class="forminp forminp-color plugin-option">
					<fieldset>
						<legend class="screen-reader-text"><span><?php echo $name; ?></span>
						</legend>
						<label for="ywqe_enable_dropbox">
							<?php if ( $this->dropbox->dropbox_accesstoken ) {
								$account_info = $this->dropbox->get_dropbox_account_info();
								if ( $account_info ) {
									$quota = $account_info['quota_info'];
									echo sprintf( __( 'Dropbox backup is currently active for <b>%s</b>. <b>%s</b> used over <b>%s</b> total.', 'yith-woocommerce-quick-export' ), $account_info['email'], yith_get_filesize_text( $quota['normal'] ), yith_get_filesize_text( $quota['quota'] ) );
									echo '<br><a href="' . esc_url( add_query_arg( YWQE_RESET_DROPBOX, 1 ) ) . '" id="ywqe_disable_dropbox_button" class="button button-secondary">' . __( "Disable Dropbox", 'yith-woocommerce-quick-export' ) . '</a>';
								} else {
									$show_dropbox_login = true;
									echo '<p><span style="color:red">' . __( "The authentication token provided is no longer valid, please repeat the Dropbox authentication steps.", 'yith-woocommerce-quick-export' ) . '</span></p>';
								}
							}

							if ( ! $this->dropbox->dropbox_accesstoken || $show_dropbox_login ) {
								$example_url = '<a class="thickbox" href="' . YITH_YWQE_ASSETS_IMAGES_URL . 'dropbox-howto.jpg">';
								?>
								<a target="_blank" href="<?php echo $this->dropbox->get_dropbox_authentication_url(); ?>"
								   id="ywqe_enable_dropbox_button"
								   class="button button-primary"/><?php _e( 'Login to Dropbox', 'yith-woocommerce-quick-export' ); ?></a>
								<?php echo __( sprintf( 'Authorize this plugin to access to your Dropbox space.<br> All <b>new documents</b> will be sent to your Dropbox space as soon as they are created. Copy and paste authorization code here, as in %s this example %s.', $example_url, '</a>' ), 'yith-woocommerce-quick-export' ); ?>
								<input name="ywqe_dropbox_key" id="ywqe_dropbox_key" type="text" style="width: 75%;">
								<br>
							<?php } ?>
						</label>
					</fieldset>
				</td>
			</tr>
		<?php
		}

		/**
		 * Save dropbox access token
		 */
		public function yit_save_dropbox( $option_value ) {

			if ( isset( $_POST['ywqe_dropbox_key'] ) && ( ! empty( $_POST['ywqe_dropbox_key'] ) ) ) {
				//  Extract access token  if autorization token is valid
				$access_token = $this->dropbox->get_dropbox_access_token( $_POST['ywqe_dropbox_key'] );
				if ( $access_token ) {
					update_option( 'ywqe_dropbox_access_token', $access_token );
				}
			}
		}
	}
}
