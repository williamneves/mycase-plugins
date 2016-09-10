<?php
/**
 * Admin View: Notice - Theme Support
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
} ?>

<?php if( is_dir( wsoe_upload_dir() ) && stristr( $_SERVER['SERVER_SOFTWARE'], 'apache' ) && !wsoe_htaccess_exists() ) { ?>

	<div id="message" class="updated woocommerce-message wc-connect wsoe-connect" style="padding: 10px">
		<div><strong><?php _e('htaccess file is missing in '.  trailingslashit(wsoe_upload_dir()) .' folder, please create .htaccess file in folder and add following rules.', 'woocommerce-simply-order-export') ?></strong></div>
		<div>
			<?php $rules = wsoe_get_htaccess_rules(); ?>
			<p><textarea cols="45" rows="7" readonly="readonly"><?php echo $rules; ?></textarea></p>
			<a class="button-primary" href="<?php echo esc_url( add_query_arg( 'wsoe-hide-notice', 'wsoe_htaccess_missing' ) ); ?>"><?php _e( 'Hide this notice', 'woocommerce-simply-order-export' ); ?></a>
		</div>
	</div><?php

}

if( is_dir( wsoe_upload_dir() ) && stristr( $_SERVER['SERVER_SOFTWARE'], 'nginx' ) ) { ?>

<div id="message" class="updated woocommerce-message wc-connect wsoe-connect" style="padding: 10px">
	<div><strong><?php _e('Please add following rule to the server block of nginx configuration file for your site., to protect csv files from accessing directly.', 'woocommerce-simply-order-export') ?></strong></div>
	<div>
		<?php $rules = wsoe_get_htaccess_rules(); ?>
		<p>
		<pre>
location ~ ^/wp-content/uploads/wsoe/(.*?)\.csv$ {
		rewrite / permanent;
}
		</pre>
	</p>
	<p><?php _e('If you have changed directory path to save csv, please correct path in above rule', 'woocommerce-simply-order-export' ) ?></p>
	<a class="button-primary" href="<?php echo esc_url( add_query_arg( 'wsoe-hide-notice', 'wsoe_htaccess_missing' ) ); ?>"><?php _e( 'Hide this notice', 'woocommerce-simply-order-export' ); ?></a>
</div>
</div><?php
}