<?php
/**
 * Add content to help tab.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if( !class_exists('wsoe_admin_help') ) :

	class wsoe_admin_help {

		/**
		 * Hook in tabs.
		 */
		public function __construct() {
			add_action( "current_screen", array( $this, 'add_tabs' ), 100 );
		}

		function add_tabs() {

			$screen = get_current_screen();
			$wc_screen_id = sanitize_title( __( 'WooCommerce', 'woocommerce-simply-order-export' ) );
			$wsoe_tab = ( !empty( $_GET['tab'] ) && $_GET['tab'] == 'order_export' ) ? true : false;
			
			if( $screen->id === $wc_screen_id . '_page_wc-settings' && $wsoe_tab ) {

				$screen->add_help_tab( array(
					'id'        => 'wsoe_help_tab',
					'title'     => __( 'Order Export', 'woocommerce-simply-order-export' ),
					'content'   =>

						'<p>' . __( 'Thank you for using <strong>WooCommerce Simply Order Export</strong> plugin :)', 'woocommerce-simply-order-export' ) . '</p>' .

						'<p>'. __('Please use following steps to use the plugin.', 'woocommerce-simply-order-export') .'</p>'.
						'<ul>'.
							'<li>'.__( 'Choose the fields you want to export.', 'woocommerce-simply-order-export' ).'</li>'.
							'<li>'.__( 'Click Save Settings button at the bottom of the page.', 'woocommerce-simply-order-export' ).'</li>'.
							'<li>'.__( 'Click Advanced Options to explore more controls.', 'woocommerce-simply-order-export' ).'</li>'.
							'<li>'.__( 'Select the duration for which you want to export orders.', 'woocommerce-simply-order-export' ).'</li>'.
							'<li>'.__( 'Click Export Order button.', 'woocommerce-simply-order-export' ).'</li>'.
						'</ul>'

				) );

				$screen->add_help_tab( array(
					'id'        => 'wsoe_addon_tab',
					'title'     => __( 'Order Export Add-on', 'woocommerce-simply-order-export' ),
					'content'   =>

						'<p>' . __( 'With <strong>WooCommerce Simply Order Export Add-on</strong>, you can do following things.', 'woocommerce-simply-order-export' ) . '</p>' .

						'<ol>'.
							'<li>'.__( 'Export all the possible fields related to order.', 'woocommerce-simply-order-export' ).'</li>'.
							'<li>'.__( 'Reorder the fields, so that exported CSV would contain columns in that order.', 'woocommerce-simply-order-export' ).'</li>'.
						'</ol>'.

						'<p><a target="_blank" href="' . 'http://sharethingz.com/woocommerce-simply-order-export-add-on/' . '" class="button button-primary">' . __( 'Purchase Add-on', 'woocommerce-simply-order-export' ) . '</a> <a target="_blank" href="' . 'https://github.com/ankitrox/WooCommerce-Simply-Order-Export-Add-on-Doc/blob/master/README.md' . '" class="button">' . __( 'Add-on Documentation', 'woocommerce-simply-order-export' ) . '</a></p>'
				) );			

				$screen->add_help_tab( array(
					'id'        => 'wsoe_scheduler_tab',
					'title'     => __( 'Order Export Scheduler and Logger', 'woocommerce-simply-order-export' ),
					'content'   =>

						'<p>' . __( 'With <strong>Order Export Scheduler and Logger</strong>plugin, you can do following things.', 'woocommerce-simply-order-export' ) . '</p>' .

						'<ol>'.
							'<li>'.__( 'Schedule order export process for future.', 'woocommerce-simply-order-export' ).'</li>'.
							'<li>'.__( 'Maintain log of already exported reports. You can also download those reports either individually or in bulk (zip).', 'woocommerce-simply-order-export' ).'</li>'.
						'</ol>'.

						'<p><a target="_blank" href="' . 'http://sharethingz.com/downloads/wsoe-scheduler-logger/' . '" class="button button-primary">' . __( 'Purchase Add-on', 'woocommerce-simply-order-export' ) . '</a> <a target="_blank" href="' . 'https://github.com/ankitrox/Order-Export-Scheduler-and-Logger/blob/master/README.md' . '" class="button">' . __( 'Add-on Documentation', 'woocommerce-simply-order-export' ) . '</a></p>'
				) );	

			}
		}
	}

	return new wsoe_admin_help();

endif;