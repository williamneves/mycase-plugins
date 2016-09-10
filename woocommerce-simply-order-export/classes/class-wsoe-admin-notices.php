<?php
/**
 * Class to handle admin notices
 */
if( !class_exists('wsoe_admin_notices') ) {

	class wsoe_admin_notices {

		function __construct() {

			add_action( 'admin_print_styles', array( $this, 'check_wsoe_messages' ) );
			add_action( 'wp_loaded', array( $this, 'wsoe_hide_notices' ) );
		}

		static function update_notices() {

			if( !wsoe_is_shop_manager() ){
				return;
			}

			$wsoe_messages = array();
			if( !in_array( 'woocommerce-simply-order-export-add-on/main.php', apply_filters( 'active_plugins', get_option( 'active_plugins' )) ) ) {
				$wsoe_messages['wsoe_addon_installed'] = false;
				$wsoe_messages['wsoe_addon_notice_display'] = true;
			}else {
				$wsoe_messages['wsoe_addon_installed'] = true;
				$wsoe_messages['wsoe_addon_notice_display'] = false;
			}

			if( !in_array( 'wsoe-scheduler-logger/wsoe-schedular.php', apply_filters( 'active_plugins', get_option( 'active_plugins' )) ) ) {
				$wsoe_messages['wsoe_scheduler_installed'] = false;
				$wsoe_messages['wsoe_scheduler_notice_display'] = true;
			}else {
				$wsoe_messages['wsoe_scheduler_installed'] = true;
				$wsoe_messages['wsoe_scheduler_notice_display'] = false;
			}

			/**
			 * This option will be utilized in admin_notices
			 */
			update_user_meta( get_current_user_id(), 'wsoe_messages', $wsoe_messages );
			
			do_action('wsoe_update_notices');
		}

		function check_wsoe_messages() {

			/**
			 * Do not display notice if current user is not shop manager.
			 */
			if( !wsoe_is_shop_manager() ){
				return;
			}

			/**
			 * For add-on notice
			 */
			$wsoe_messages = get_user_meta( get_current_user_id(), 'wsoe_messages', true );
			$wsoe_messages = wp_parse_args( $wsoe_messages, array('wsoe_addon_installed'=>false, 'wsoe_addon_notice_display'=>true ) );

			if ( (empty($wsoe_messages['wsoe_addon_installed']) &&  !empty($wsoe_messages['wsoe_addon_notice_display'])) && !get_user_meta( get_current_user_id(), 'wsoe_addon_notice_dismissed', true ) ) {
				add_action( 'admin_notices', array( $this, 'install_addon' ) );
			}

			if ( (empty($wsoe_messages['wsoe_scheduler_installed']) &&  !empty($wsoe_messages['wsoe_scheduler_notice_display'])) && !get_user_meta( get_current_user_id(), 'wsoe_scheduler_notice_dismissed', true ) ) {
				add_action( 'admin_notices', array( $this, 'install_scheduler_logger' ) );
			}
			
			/**
			 * For file protection
			*/
			if( !get_user_meta( get_current_user_id(), 'wsoe_htaccess_dismissed', true ) ) {
				add_action( 'admin_notices', array( $this, 'htaccess_missing' ) );
			}

			do_action('wsoe_check_wsoe_messages');

		}

		/**
		 * Display notice if add-on is not installed.
		 */
		function install_addon() {

			include WSOE_BASE. 'views/html-notice-addon-support.php';
		}

		/**
		 * Display notice if Scheduler plugin is not installed
		 */
		function install_scheduler_logger() {

			include WSOE_BASE. 'views/html-notice-scheduler.php';
		}

		/**
		 * Show notice if htacess is missing.
		 */
		function htaccess_missing() {

			include WSOE_BASE. 'views/html-notice-htaccess-missing.php';
		}

		function wsoe_hide_notices() {
			if ( isset( $_GET['wsoe-hide-notice'] ) ) {
				$hide_notice = sanitize_text_field( $_GET['wsoe-hide-notice'] );
				self::remove_notice( $hide_notice );
				do_action( 'wsoe_hide_' . $hide_notice . '_notice' );
			}
		}

		static function remove_notice($notice) {

			switch($notice) {

				case 'wsoe_addon_notice':
					self::hide_wsoe_addon_notice();
				break;
			
				case 'wsoe_scheduler_notice':
					self::hide_wsoe_scheduler_notice();
				break;

				case 'wsoe_htaccess_missing':
					update_user_meta( get_current_user_id(), 'wsoe_htaccess_dismissed', true );
				break;

				default :
					do_action('wsoe_hide_notice_'.$notice);
					break;
			}
		}

		static function hide_wsoe_addon_notice() {

			$wsoe_messages = get_user_meta( get_current_user_id(), 'wsoe_messages', true );
			$wsoe_messages['wsoe_addon_notice_display'] = false;
			update_user_meta( get_current_user_id(), 'wsoe_messages', $wsoe_messages );
			update_user_meta( get_current_user_id(), 'wsoe_addon_notice_dismissed', 1 );
		}
		
		static function hide_wsoe_scheduler_notice() {
			$wsoe_messages = get_user_meta( get_current_user_id(), 'wsoe_messages', true );
			$wsoe_messages['wsoe_scheduler_notice_display'] = false;
			update_user_meta( get_current_user_id(), 'wsoe_messages', $wsoe_messages );
			update_user_meta( get_current_user_id(), 'wsoe_scheduler_notice_dismissed', 1 );
		}

	}

	new wsoe_admin_notices();

}