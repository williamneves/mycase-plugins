<?php
/**
 * Admin View: Notice - Theme Support
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
} ?>

<div id="message" class="updated woocommerce-message wc-connect wsoe-connect" style="padding: 10px">
	<div class="wsoe-que">
		<div><strong><?php _e('Wish to schedule the order export?', 'woocommerce-simply-order-export') ?></strong></div>
		<div><strong><?php _e('Wish to log the reports, you already exported and able to download them?', 'woocommerce-simply-order-export') ?></strong></div>
	</div>
	<p class="submit">
		<a href="http://sharethingz.com/downloads/wsoe-scheduler-logger/?utm_source=notice&utm_medium=plugin&utm_campaign=wsoe" class="button-primary" target="_blank"><?php _e( 'Try Order Export Scheduler and Logger', 'woocommerce-simply-order-export' ); ?></a>
		<a href="http://sharethingz.com" class="button-primary" target="_blank"><?php _e( 'Donate to this plugin', 'woocommerce-simply-order-export' ); ?></a>
		<a class="skip button" href="<?php echo esc_url( add_query_arg( 'wsoe-hide-notice', 'wsoe_scheduler_notice' ) ); ?>"><?php _e( 'Hide this notice', 'woocommerce-simply-order-export' ); ?></a>
	</p>
</div>