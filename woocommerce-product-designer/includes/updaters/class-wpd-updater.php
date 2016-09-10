<?php

class WPD_Updater {
	protected $version_url = 'http://static.orionorigin.com/wpc-notifier.xml';
	public $title = 'Woocommerce Products Designer';

	protected $auto_updater = false;
	protected $upgrade_manager = false;
	protected $iframe = false;

	public function init() {
		add_filter('upgrader_pre_download', array($this, 'upgradeFilterFromEnvato'), 10, 4);
		add_action('upgrader_process_complete', array($this, 'removeTemporaryDir'));
	}

	/**
	 * Setter for manager updater.
	 *
	 * @param WPD_Updating_Manager $updater
	 */
	public function setUpdateManager(WPD_Updating_Manager $updater) {
		$this->auto_updater = $updater;
	}
	/**
	 * Getter for manager updater.
	 *
	 * @return WPD_Updating_Manager
	 */
	public function updateManager() {
		return $this->auto_updater;
	}

	/**
	 * Get url for version validation
	 * @return string
	 */
	public function versionUrl() {
		return $this->version_url;
	}
	/**
	 * Downloads new VC from Envato marketplace and unzips into temporary directory.
	 *
	 * @param $reply
	 * @param $package
	 * @param $updater
	 * @return mixed|string|WP_Error
	 */
	public function upgradeFilterFromEnvato($reply, $package, $updater) {
		global $wp_filesystem;
		if((isset($updater->skin->plugin) && $updater->skin->plugin === WPD_MAIN_FILE) ||
		  (isset($updater->skin->plugin_info) && $updater->skin->plugin_info['Name'] === $this->title)
		) {
			$updater->strings['download_envato'] = __( 'Downloading package from envato market...', 'wpd' );
			$updater->skin->feedback( 'download_envato' );
			$package_filename = 'woocommerce-product-designer.zip';
			$res = $updater->fs_connect( array( WP_CONTENT_DIR ) );
			if ( ! $res ) {
				return new WP_Error( 'no_credentials', __( "Error! Can't connect to filesystem", 'wpd' ) );
			}
                        global $wpc_options_settings;
                        $username = $wpc_options_settings["wpc-licence"]["envato-username"];
                        $api_key = $wpc_options_settings["wpc-licence"]["envato-api-key"];
                        $purchase_code = $wpc_options_settings["wpc-licence"]["purchase-code"];
			if ( /*!vc_license()->isActivated() || */empty( $username ) || empty( $api_key ) || empty( $purchase_code ) ) {
				return new WP_Error( 'no_credentials', __( 'To receive automatic updates license activation is required. Please visit <a href="' . admin_url( 'admin.php?page=wpc-manage-settings&tab=updater' ) . '' . '" target="_blank">Settings</a> to activate your Woocommerce Product Customizer.', 'wpd' ) );
			}
			$json = wp_remote_get( $this->envatoDownloadPurchaseUrl( $username, $api_key, $purchase_code ) );
			$result = json_decode( $json['body'], true );
			if ( ! isset( $result['download-purchase']['download_url'] ) ) {
				return new WP_Error( 'no_credentials', __( 'Error! Envato API error' . ( isset( $result['error'] ) ? ': ' . $result['error'] : '.' ), 'wpd' ) );
			}
			$result['download-purchase']['download_url'];
			$download_file = download_url( $result['download-purchase']['download_url'] );
			if ( is_wp_error( $download_file ) ) {
				return $download_file;
			}
//			$upgrade_folder = $wp_filesystem->wp_content_dir() . 'uploads/wpc_envato_package';
                        $uploads_dir_obj=wp_upload_dir();
			$upgrade_folder = $uploads_dir_obj["basedir"] . '/wpc_envato_package';
			if ( is_dir( $upgrade_folder ) ) {
				$wp_filesystem->delete( $upgrade_folder );
			}
                        //We rename the tmp file to a zip file
                        $new_zipname=  str_replace(".tmp", ".zip", $download_file);
                        rename($download_file, $new_zipname);
                        $result = unzip_file( $new_zipname, $upgrade_folder );
                        $dir_content = scandir($upgrade_folder);
                        //The upgrade is in the unique directory inside the upgrade folder
                        $new_version="$upgrade_folder/$dir_content[2]/$package_filename";
                        if ( $result && is_file( $new_version ) ) {
                                return $new_version;
                        }
                        return new WP_Error( 'no_credentials', __( 'Error on unzipping package', 'wpd' ) );
		}
		return $reply;
	}
	public function removeTemporaryDir() {
		global $wp_filesystem;
		if(is_dir($wp_filesystem->wp_content_dir() . 'uploads/wpc_envato_package')) {
			$wp_filesystem->delete($wp_filesystem->wp_content_dir() . 'uploads/wpc_envato_package', true);
		}
	}
	protected function envatoDownloadPurchaseUrl( $username, $api_key, $purchase_code ) {
		return 'http://marketplace.envato.com/api/edge/' . rawurlencode( $username ) . '/' . rawurlencode( $api_key ) . '/download-purchase:' . rawurlencode( $purchase_code ) . '.json';
	}
}