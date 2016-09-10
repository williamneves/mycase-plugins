<?php
/**
 * YITH_DropBox base class
 *
 * @author Your Inspiration Themes
 * @package YITH WooCommerce Quick Export
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly


if ( ! class_exists( 'YITH_DropBox' ) ) {
	/**
	 * Admin class.
	 * The class manage all the Frontend behaviors.
	 *
	 * @since 1.0.0
	 */
	class YITH_DropBox {
		/**
		 * @var array DropBox application keys, please initialize it with the initialize method
		 */
		private $dropbox_app_info = array( 'key' => '', 'secret' => '' );

		/**
		 * @var mixed|string|void DropBox user access token
		 */
		public $dropbox_accesstoken = '';

		/**
		 * @var string set the base dir from which the files should be sent to DropBox
		 */
		public $base_dir_backup = '';
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
		 * Constructor
		 *
		 * @access public
		 * @since 1.0.0
		 */
		public function __construct() {

		}

		public function initialize( $app_key, $app_secret, $base_dir_backup ) {

			$this->dropbox_app_info = array(
				'key'    => $app_key,
				'secret' => $app_secret
			);

			$this->base_dir_backup = $base_dir_backup;
		}

		/**
		 * Get the url to the dropbox authentication url
		 *
		 * @return string
		 */
		public function get_dropbox_authentication_url() {
			# Include the Dropbox SDK libraries
			require_once YITH_YWQE_LIB_DIR . 'Dropbox/autoload.php';

			$appInfo = Dropbox\AppInfo::loadFromJson( $this->dropbox_app_info );
			$webAuth = new Dropbox\WebAuthNoRedirect( $appInfo, "PHP-Example/1.0" );

			$authorizeUrl = $webAuth->start();

			return $authorizeUrl;
		}

		private function get_relative_folder_from_filepath( $filepath ) {

			return dirname( $this->get_relative_path_from_filepath( $filepath ) );
		}

		private function get_relative_path_from_filepath( $filepath ) {

			return str_replace( $this->base_dir_backup, '', $filepath );
		}

		/**
		 * Upload document to dropbox, if access token is valid
		 *
		 * @param $filepath  the document to upload
		 */
		public function send_document_to_dropbox( $filepath ) {

			if ( ! $this->dropbox_accesstoken ) {
				return;
			}

			# Include the Dropbox SDK libraries
			require_once YITH_YWQE_LIB_DIR . 'Dropbox/autoload.php';
			try {
				$dbxClient = new Dropbox\Client( $this->dropbox_accesstoken, "PHP-Example/1.0" );

				if ( file_exists( $filepath ) ) {
					$f = fopen( $filepath, "rb" );

					$dbxClient->createFolder( '/' . $this->get_relative_folder_from_filepath( $filepath ) );

					$result = $dbxClient->uploadFile( '/' . $this->get_relative_path_from_filepath( $filepath ), Dropbox\WriteMode::force(), $f );

					fclose( $f );
				}
			} catch ( Exception $e ) {
				error_log( __( 'Dropbox backup: unable to send file  -> ', 'yith-woocommerce-quick-export' ) . $e->getMessage() );
			}
		}

		/**
		 * Get the url to the dropbox authentication url
		 *
		 * @return string
		 */
		public function disable_dropbox_backup() {
			if ( $this->dropbox_accesstoken ) {
				try {
					# Include the Dropbox SDK libraries
					require_once YITH_YWQE_LIB_DIR . 'Dropbox/autoload.php';

					$dbxClient = new Dropbox\Client( $this->dropbox_accesstoken, "PHP-Example/1.0" );

					//  try to retrieve information to verify if access token is valid
					return $dbxClient->disableAccessToken();
				} catch ( \Dropbox\Exception $e ) {
					error_log( __( 'Dropbox backup: unable to disable authorization  -> ', 'yith-woocommerce-quick-export' ) . $e->getMessage() );
				}
			}
		}

		/**
		 * Check if current access token is valid and retrieve account information
		 *
		 * @return array|bool
		 */
		public function get_dropbox_account_info() {
			if ( $this->dropbox_accesstoken ) {
				try {
					# Include the Dropbox SDK libraries
					require_once YITH_YWQE_LIB_DIR . 'Dropbox/autoload.php';

					$dbxClient = new Dropbox\Client( $this->dropbox_accesstoken, "PHP-Example/1.0" );

					//  try to retrieve information to verify if access token is valid
					return $dbxClient->getAccountInfo();
				} catch ( \Dropbox\Exception $e ) {
					error_log( __( 'Dropbox backup: unable to retrieve account information  -> ', 'yith-woocommerce-quick-export' ) . $e->getMessage() );

				}
			}

			return false;
		}

		/**
		 * Retrieve access token starting from an authorization code
		 */
		public function get_dropbox_access_token( $auth_code ) {
			try {
				# Include the Dropbox SDK libraries
				require_once YITH_YWQE_LIB_DIR . 'Dropbox/autoload.php';

				$appInfo = Dropbox\AppInfo::loadFromJson( $this->dropbox_app_info );
				$webAuth = new Dropbox\WebAuthNoRedirect( $appInfo, "PHP-Example/1.0" );

				list( $accessToken, $dropboxUserId ) = $webAuth->finish( $auth_code );

				return $accessToken;
			} catch ( Exception $e ) {
				error_log( __( 'Dropbox backup: unable to get access token  -> ', 'yith-woocommerce-quick-export' ) . $e->getMessage() );
			}

			return false;
		}
	}
}