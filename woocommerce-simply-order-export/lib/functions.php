<?php

/**
 * Filters elements of array
 * @param bool $value
 * @return boolean
 */
function wsoe_array_filter( $value ) {

	if( $value == true ) {
		return true;
	}

	return false;
}

/**
 * WSOE upload path
 */
function wsoe_upload_dir() {

	$upload_dir = wp_upload_dir();
	wp_mkdir_p( $upload_dir['basedir']. '/wsoe' );
	$path = $upload_dir['basedir'] . '/wsoe';

	return apply_filters( 'wsoe_get_upload_dir', $path );
}

/**
 * Hook for wp_schedule event in wsoe_admin_notices class
 */
function wsoe_call_notices_func() {
	wsoe_admin_notices::update_notices();
}
add_action( 'wsoe_call_notices', 'wsoe_call_notices_func' );

/**
 * Create .htaccess file for protecting files.
 * 
 * IMPORTANT NOTE: .htaccess would not work in nginx, it needs to add rule manually to the server block.
 * 
 * @param bool $force
 * @param bool $method
 */
function wsoe_create_protection_files( $force = false ) {

	if ( false === get_transient( 'wsoe_check_protection_files' ) || $force ) {

		$upload_path = wsoe_upload_dir();

		// Top level .htaccess file
		$rules = wsoe_get_htaccess_rules();

		if ( wsoe_htaccess_exists() ) {
			$contents = @file_get_contents( $upload_path . '/.htaccess' );
			if ( $contents !== $rules || ! $contents ) {
				// Update the .htaccess rules if they don't match
				@file_put_contents( $upload_path . '/.htaccess', $rules );
			}
		} elseif( wp_is_writable( $upload_path ) ) {
			// Create the file if it doesn't exist
			@file_put_contents( $upload_path . '/.htaccess', $rules );
		}

		// Top level blank index.php
		if ( ! file_exists( $upload_path . '/index.php' ) && wp_is_writable( $upload_path ) ) {
			@file_put_contents( $upload_path . '/index.php', '<?php' . PHP_EOL . '// Silence is golden.' );
		}

		// Now place index.php files in all sub folders
		$folders = wsoe_scan_folders( $upload_path );
		foreach ( $folders as $folder ) {
			// Create index.php, if it doesn't exist
			if ( ! file_exists( $folder . 'index.php' ) && wp_is_writable( $folder ) ) {
				@file_put_contents( $folder . 'index.php', '<?php' . PHP_EOL . '// Silence is golden.' );
			}
		}

		// Check for the files once per day
		set_transient( 'wsoe_check_protection_files', true, 3600 * 24 );
	}
}
add_action( 'admin_init', 'wsoe_create_protection_files' );

/**
 * Rules for .htaccess files
 */
function wsoe_get_htaccess_rules() {

	// Prevent directory browsing and direct access to all files, except images
	$rules = "Options -Indexes\n";
	$rules .= "deny from all\n";
	$rules .= "<FilesMatch '\.(jpg|png|gif|mp3|ogg)$'>\n";
		$rules .= "Order Allow,Deny\n";
		$rules .= "Allow from all\n";
	$rules .= "</FilesMatch>\n";

	return apply_filters( 'wsoe_protected_directory_htaccess_rules', $rules );
}

/**
 * Checks if the .htaccess file exists in wp-content/uploads/wsoe
 *
 * @since 1.3
 * @return bool
 */
function wsoe_htaccess_exists() {

	$upload_path = wsoe_upload_dir();

	return file_exists( $upload_path . '/.htaccess' );
}

/**
 * Scans all folders inside of /uploads/wsoe
 *
 * @since 1.3
 * @return array $return List of files inside directory
 */
function wsoe_scan_folders( $path = '', $return = array() ) {

	$path = $path == ''? dirname( __FILE__ ) : $path;
	$lists = @scandir( $path );

	if ( ! empty( $lists ) ) {
		foreach ( $lists as $f ) {
			if ( is_dir( $path . DIRECTORY_SEPARATOR . $f ) && $f != "." && $f != ".." ) {
				if ( ! in_array( $path . DIRECTORY_SEPARATOR . $f, $return ) )
					$return[] = trailingslashit( $path . DIRECTORY_SEPARATOR . $f );

				wsoe_scan_folders( $path . DIRECTORY_SEPARATOR . $f, $return);
			}
		}
	}

	return $return;
}

/**
 * Determine if user is shop manager
 * 
 * @return bool
 */
function wsoe_is_shop_manager() {

	if( current_user_can( 'manage_woocommerce' ) ) {
		return true;
	}

	return false;
}

/**
 * Returns formatted price
 */
function wsoe_formatted_price( $amount, $order_details ) {

	if( is_a( $order_details, 'WC_Order' ) && apply_filters( 'wsoe_formatted_price', true ) ){

		// Support for php versions older than PHP 5.4.0
		if( !defined( 'ENT_HTML5' ) ){
			return strip_tags( html_entity_decode( wc_price( $amount , apply_filters( 'wsoe_formatted_price_args', array() ) ) ) ) ;
		}else {
			$charset = get_option('blog_charset');
			return strip_tags( html_entity_decode( wc_price( $amount , apply_filters( 'wsoe_formatted_price_args', array() ) ), ENT_HTML5, $charset ) ) ;
		}
	}

	return $amount;
}

/**
 * Fix weird characters in CSV
 */
function wsoe_fix_weird_chars() {
	
	$settings = wpg_order_export::advanced_option_settings();
	?>

	<tr>

		<th>
			<?php _e( 'Fix weird charactes in CSV', 'woocommerce-simply-order-export') ?>
			<img class="help_tip" data-tip="<?php _e('Check this option only if you are getting some weird characters in exported CSV file', 'woocommerce-simply-order-export') ?>" src="<?php echo OE_IMG; ?>help.png" height="16" width="16">
		</th>

		<td>
			<input type="checkbox" name="wpg_fix_chars" value="1" <?php checked( $settings['wsoe_fix_chars'], 1, true ); ?> />
		</td>

	</tr><?php
	
}
add_action( 'advanced_options_end', 'wsoe_fix_weird_chars', 9 );