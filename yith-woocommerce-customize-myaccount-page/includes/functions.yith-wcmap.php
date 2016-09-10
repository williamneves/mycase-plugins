<?php
/**
 * Plugins Functions and Hooks
 *
 * @author Yithemes
 * @package YITH WooCommerce Customize My Account Page
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCMAP' ) ) {
	exit;
} // Exit if accessed directly

if( ! function_exists( 'yith_wcmap_print_endpoint_fields' ) ) {
	/**
	 * Print endpoint field options
	 *
	 * @since 1.0.0
	 * @param string $endpoint The endpoint name
	 * @param array $options All endpoints options
	 * @param array $id The main option value id
	 * @author Francesco Licandro
	 */
	function yith_wcmap_print_endpoint_fields( $endpoint, $options, $id ) {

		if( ! class_exists( 'YIT_Plugin_Common' ) ) {
			require_once( YITH_WCMAP_DIR . '/plugin-fw/lib/yit-plugin-common.php' );
		}

		$args = apply_filters( 'yith_wcmap_admin_endpoint_fields_template', array(
			'endpoint' 	=> $endpoint,
			'options' 	=> $options,
			'id' 		=> $id,
			'icon_list'	=> YIT_Plugin_Common::get_icon_list(),
		));

		wc_get_template( 'endpoint-fields.php', $args, '', YITH_WCMAP_TEMPLATE_PATH . '/admin/' );
	}
}
add_action( 'yith_wcmap_endpoint_fields', 'yith_wcmap_print_endpoint_fields', 10, 3 );


if( ! function_exists( 'yith_wcmap_default_endpoints_keys' ) ) {
	/**
	 * Get endpoints key
	 *
	 * @since 1.0.0
	 * @author Francesco Licandro
	 */
	function yith_wcmap_default_endpoints_keys() {
		return apply_filters( 'yith_wcmap_get_default_endpoints_keys_array',
			array(
				'dashboard',
				'my-downloads',
				'view-order',
				'edit-account',
				'edit-address'
			) );
	}
}

if( ! function_exists( 'yith_wcmap_get_default_endpoints' ) ) {
	/**
	 * Get default endpoints and options
	 *
	 * @since 1.0.0
	 * @return array
	 * @author Francesco Licandro
	 */
	function yith_wcmap_get_default_endpoints(){

		$endpoints_keys = yith_wcmap_default_endpoints_keys();
		$endpoints_keys = array_unique( $endpoints_keys );

		if( empty( $endpoints_keys ) || ! is_array( $endpoints_keys ) ) {
			return array();
		}

		$endpoints = array();

		// populate endpoints array with options
		foreach ( $endpoints_keys as $endpoint ) {

			$label = $icon = $content = $slug = '';

			switch( $endpoint ) {
				case 'view-order':
					$label 		= __( 'My Orders', 'yith-woocommerce-customize-myaccount-page' );
					$icon 		= 'file-text-o';
					$content 	= '[view_order_content]';
					$slug 		=  get_option( 'woocommerce_myaccount_view_order_endpoint', 'view-order' );
					break;
				case 'edit-account':
					$label 		= __( 'Edit Account', 'yith-woocommerce-customize-myaccount-page' );
					$icon 		= 'pencil-square-o';
					$slug 		=  get_option( 'woocommerce_myaccount_edit_account_endpoint', 'edit-account' );
					break;
				case 'edit-address':
					$label 		= __( 'Edit Address', 'yith-woocommerce-customize-myaccount-page' );
					$icon 		= 'pencil-square-o';
					$slug 		=  get_option( 'woocommerce_myaccount_edit_address_endpoint', 'edit-address' );
					break;
				case 'my-downloads':
					$label 		= __( 'My Downloads', 'yith-woocommerce-customize-myaccount-page' );
					$icon 		= 'download';
					$content 	= '[my_downloads_content]';
					$slug 		=  'my-downloads';
					break;
				case 'dashboard':
					$label 		= __( 'Dashboard', 'yith-woocommerce-customize-myaccount-page' );
					$icon 		= 'tachometer';
					$slug 		= 'dashboard';
					break;
				case 'payment-methods' :
					$label 		= __( 'Payment Methods', 'yith-woocommerce-customize-myaccount-page' );
					$icon 		= 'money';
					$content 	= '';
					$slug 		= get_option( 'woocommerce_myaccount_payment_methods_endpoint', 'payment-methods' );
					break;
			}

			$endpoints[ $endpoint ]['slug'] = $slug;
			$endpoints[ $endpoint ]['active'] = true;
			$endpoints[ $endpoint ]['label'] = $label;
			$endpoints[ $endpoint ]['icon'] = $icon;
			$endpoints[ $endpoint ]['content'] = $content;
		}

		// lets filter endpoints array
		return apply_filters( 'yith_wcmap_get_default_endpoints_array', $endpoints );
	}
}

if( ! function_exists( 'yith_wcmap_get_endpoints' ) ) {
	/**
	 * Get ordered endpoints based on plugin option
	 *
	 * @since 1.0.0
	 * @return array
	 * @author Francesco Licandro
	 */
	function yith_wcmap_get_endpoints() {

		// get saved endpoints order
		$order = get_option( 'yith_wcmap_endpoint', '' );
		$order = explode( ',', $order );
		$order = array_filter( $order );

		// get default endpoints
		$default = yith_wcmap_get_default_endpoints();
		$default_plugin = yith_wcmap_get_plugins_endpoints();
		if( ! empty( $default_plugin ) ) {
			// merge default and plugin default
			$default = array_merge( $default, $default_plugin );
			// get keys for plugin endpoints
			$default_keys = array_keys( $default );
			// merge with the order
			$order = array_merge( $order, $default_keys );
		}

		$endpoints = array();

		if( ! empty( $order ) ) {
			foreach ( $order as $value ) {

				$options = get_option( 'yith_wcmap_endpoint_' . $value, array() );

				$endpoints[$value] = empty($options) ? $default[$value] : $options;
			}
		}

		return apply_filters( 'yith_wcmap_get_endpoints_array', $endpoints );
	}
}


if( ! function_exists( 'yith_wcmap_endpoint_already_exists' ) ) {
	/**
	 * Check if endpoints already exists
	 *
	 * @since 1.0.0
	 * @param string $endpoint
	 * @return boolean
	 * @author Francesco Licandro
	 */
	function yith_wcmap_endpoint_already_exists( $endpoint ) {

		// get endpoint key
		$endpoint_slug = yith_wcmap_get_endpoints_slug();

		return in_array( $endpoint, $endpoint_slug );
	}
}


if( ! function_exists( 'yith_wcmap_add_new_endpoint_form' ) ) {
	/**
	 * Add new endpoint form
	 *
	 * @since 1.0.0
	 * @author Francesco Licandro
	 */
	function yith_wcmap_add_new_endpoint_form() {

		wc_get_template( 'new-endpoint.php', array(), '', YITH_WCMAP_TEMPLATE_PATH . '/admin/' );
	}
}
add_action( 'yith_wcmap_add_new_endpoint', 'yith_wcmap_add_new_endpoint_form', 10 );


if( ! function_exists( 'yith_wcmap_get_endpoint_active' ) ) {
	/**
	 * Get active endpoints on frontend
	 *
	 * @access public
	 * @since 1.0.0
	 * @param $endpoints
	 * @return boolean | string
	 * @deprecated
	 */
	function yith_wcmap_get_endpoint_active( $endpoints ) {

		global $wp;

		$return = false;

		foreach( $endpoints as $endpoint => $options ) {
			if( isset( $wp->query_vars[ $options['slug'] ] ) ) {
				$return = $endpoint;
				break;
			}
		}

		return $return;
	}
}

if( ! function_exists( 'yith_wcmap_get_endpoints_slug' ) ) {
	/**
	 * Get endpoints slugs for register endpoints
	 */
	function yith_wcmap_get_endpoints_slug(){

		$slugs = array();
		$endpoints = yith_wcmap_get_endpoints();

		foreach( $endpoints as $endpoint ) {
			$slugs[] = $endpoint['slug'];
		}

		return $slugs;
	}
}

if( ! function_exists( 'yith_wcmap_endpoints_option_default' ) ) {
	/**
	 * Get endpoints slugs for register endpoints
	 */
	function yith_wcmap_endpoints_option_default(){

		$return = array();
		$endpoints = yith_wcmap_get_endpoints();

		foreach( $endpoints as $endpoint ) {
			$return[ $endpoint['slug'] ] = $endpoint['label'];
		}

		return $return;
	}
}


/*#####################################
 AVATAR FUNCTION
#####################################*/

if( ! function_exists( 'yith_wcmap_generate_avatar_path' ) ){
	/**
	 * Generate avatar path
	 *
	 * @param $attachment_id
	 * @param $size
	 * @return string
	 */
	function  yith_wcmap_generate_avatar_path( $attachment_id, $size ) {
		// Retrieves attached file path based on attachment ID.
		$filename = get_attached_file( $attachment_id );

		$pathinfo  = pathinfo( $filename );
		$dirname   = $pathinfo['dirname'];
		$extension = $pathinfo['extension'];

		// i18n friendly version of basename().
		$basename = wp_basename( $filename, '.' . $extension );

		$suffix    = $size . 'x' . $size;
		$dest_path = $dirname . '/' . $basename . '-' . $suffix . '.' . $extension;

		return $dest_path;
	}
}

if( ! function_exists( 'yith_wcmap_generate_avatar_url' ) ) {
	/**
	 * Generate avatar url
	 *
	 * @param $attachment_id
	 * @param $size
	 * @return mixed
	 */
	function yith_wcmap_generate_avatar_url( $attachment_id, $size ) {
		// Retrieves path information on the currently configured uploads directory.
		$upload_dir = wp_upload_dir();

		// Generates a file path of an avatar image based on attachment ID and size.
		$path = yith_wcmap_generate_avatar_path( $attachment_id, $size );

		return str_replace( $upload_dir['basedir'], $upload_dir['baseurl'], $path );
	}
}

if( ! function_exists( 'yith_wcmap_resize_avatar_url' ) ) {
	/**
	 * Resize avatar
	 *
	 * @param $attachment_id
	 * @param $size
	 * @return boolean
	 */
	function yith_wcmap_resize_avatar_url( $attachment_id, $size ){

		$dest_path = yith_wcmap_generate_avatar_path( $attachment_id, $size );

		if ( file_exists( $dest_path ) ) {
			$resize = true;
		} else {
			// Retrieves attached file path based on attachment ID.
			$path = get_attached_file( $attachment_id );

			// Retrieves a WP_Image_Editor instance and loads a file into it.
			$image = wp_get_image_editor( $path );

			if ( ! is_wp_error( $image ) ) {

				// Resizes current image.
				$image->resize( $size, $size, true );

				// Saves current image to file.
				$image->save( $dest_path );

				$resize = true;

			}
			else {
				$resize = false;
			}
		}

		return $resize;
	}
}

if( ! function_exists( 'yith_wcmap_check_endpoint_active' ) ) {
	/**
	 * Check if and endpoint is active on frontend. Used for add class 'active' on account menu in frontend
	 * 
	 * @since 1.1.0
	 * @author Francesco Licandro
	 * @param string $endpoint
	 * @return boolean
	 */
	function yith_wcmap_check_endpoint_active( $endpoint ){

		global $wp;

		if ( 'dashboard' === $endpoint && ( isset( $wp->query_vars['page'] ) || empty( $wp->query_vars ) ) ) {
			$active = true; // Dashboard is not an endpoint, so needs a custom check.
		}
		else {
			$active = isset( $wp->query_vars[ $endpoint ] ); // Check on query vars
		}
		
		return $active;
	}
}


/*#########################################
 CUSTOM PLUGINS ENDPOINTS
###########################################*/

if( ! function_exists( 'yith_wcmap_get_plugins_endpoints' ) ) {
	/**
	 * Get plugins endpoints
	 *
	 * @since 1.0.0
	 * @param string $key
	 * @return array
	 * @author Francesco Licandro
	 */
	function yith_wcmap_get_plugins_endpoints( $key = '' ) {

		$endpoints = array();

		if( defined( 'YITH_WCWL' ) && YITH_WCWL ) {
			$endpoints['my-wishlist'] = array(
				'slug'		=> 'my-wishlist',
				'active' 	=> true,
				'label'  	=> __( 'My Wishlist', 'yith-woocommerce-customize-myaccount-page' ),
				'icon'	 	=> 'heart',
				'content' 	=> '[yith_wcwl_wishlist]'
			);
		}
		if( defined( 'YITH_WOCC_PREMIUM' ) && YITH_WOCC_PREMIUM ) {
			$endpoints['one-click'] = array(
				'slug'		=> 'one-click',
				'active' 	=> true,
				'label'  	=> __( 'One click checkout', 'yith-woocommerce-customize-myaccount-page' ),
				'icon'	 	=> 'hand-o-up',
				'content' 	=> '[yith_wocc_myaccount]'
			);
		}
		if( defined( 'YITH_WCSTRIPE_PREMIUM' ) && YITH_WCSTRIPE_PREMIUM ) {
			$endpoints['stripe'] = array(
				'slug'		=> 'saved-cards',
				'active' 	=> true,
				'label'  	=> __( 'Saved Cards', 'yith-woocommerce-customize-myaccount-page' ),
				'icon'	 	=> 'cc-stripe',
				'content' 	=> ''
			);
		}
		if( defined( 'YITH_YWRAQ_PREMIUM' ) && YITH_YWRAQ_PREMIUM ) {
			$endpoints['view-quote'] = array(
				'slug'		=> 'view-quote',
				'active' 	=> true,
				'label'  	=> __( 'My Quotes', 'yith-woocommerce-customize-myaccount-page' ),
				'icon'	 	=> 'pencil',
				'content' 	=> '[yith_ywraq_myaccount_quote]'
			);
		}
		if( defined( 'YITH_WCWTL_PREMIUM' ) && YITH_WCWTL_PREMIUM ) {
			$endpoints['waiting-list'] = array(
				'slug'		=> 'my-waiting-list',
				'active' 	=> true,
				'label'  	=> __( 'My Waiting List', 'yith-woocommerce-customize-myaccount-page' ),
				'icon'	 	=> 'clock-o',
				'content' 	=> '[ywcwtl_waitlist_table]'
			);
		}
		if( class_exists( 'WC_Subscriptions' ) ) {
			$endpoints['view-subscription'] = array(
				'slug'		=> 'view-subscription',
				'active' 	=> true,
				'label'  	=> __( 'My Subscription', 'yith-woocommerce-customize-myaccount-page' ),
				'icon'	 	=> 'pencil',
				'content' 	=> '[ywcwtl_woocommerce_subscription]'
			);
		}

		return ( $key && isset( $endpoints[$key] ) ) ? $endpoints[$key] : $endpoints;
	}
}

if( ! function_exists( 'yith_wcmap_is_plugin_endpoint' ) ) {
	/**
	 * Check if an endpoint is a plugin
	 *
	 * @since 1.0.4
	 * @author Francesco Licandro
	 */
	function yith_wcmap_is_plugin_endpoint( $endpoint ) {
		$plugin_endpoints = yith_wcmap_get_plugins_endpoints();
		return array_key_exists( $endpoint, $plugin_endpoints );
	}
}

/*####################################
* YITH WOOCOMMERCE ONE CLICK CHECKOUT
######################################*/

if( defined( 'YITH_WOCC_PREMIUM' ) && YITH_WOCC_PREMIUM ) {
	/**
	 * Add One Click Checkout compatibility
	 *
	 * @author Francesco Licandro
	 */
	function yith_wcmap_one_click_compatibility(){

		if( class_exists( 'YITH_WOCC_User_Account' ) ) {
			// remove content in my account
			remove_action( 'woocommerce_after_my_account', array( YITH_WOCC_User_Account(), 'my_account_options' ) );
		}

		add_filter( 'yith_wcmap_endpoint_menu_class', 'yith_wcmap_set_active_one_click', 10, 3 );
	}

	/**
	 * Assign active class to endpoint one-click
	 *
	 * @since 1.1.0
	 * @author Francesco Licandro
	 * @param array $classes
	 * @param string $endpoint
	 * @param array $options
	 * @return array
	 */
	function yith_wcmap_set_active_one_click( $classes, $endpoint, $options ) {

		global $wp;

		if( $endpoint == 'one-click' && ! in_array( 'active', $classes ) && isset( $wp->query_vars['custom-address'] ) ) {
			$classes[] = 'active';
		}

		return $classes;
	}

	add_action( 'template_redirect', 'yith_wcmap_one_click_compatibility', 5 );
}

/*####################################
* YITH WOOCOMMERCE STRIPE
######################################*/

if( defined( 'YITH_WCSTRIPE_PREMIUM' ) && YITH_WCSTRIPE_PREMIUM ) {
	/**
	 * Add Stripe compatibility
	 *
	 * @author Francesco Licandro
	 */
	function yith_wcmap_stripe_compatibility(){

		global $wp;

		if( ! class_exists( 'YITH_WCStripe_Premium' ) ) {
			return;
		}

		$endpoints = yith_wcmap_get_plugins_endpoints( 'stripe' );
		$options = get_option( 'yith_wcmap_endpoint_stripe', array() );
		$slug = isset( $options['slug'] ) ? $options['slug'] : $endpoints['slug'];

		// remove content in my account
		remove_action( 'woocommerce_after_my_account', array( YITH_WCStripe_Premium::get_instance(), 'saved_cards_box' ) );
		if( isset( $wp->query_vars[ $slug ] ) ) {
			add_filter( 'yith_savedcards_page', '__return_true' );
		}
	}

	add_action( 'template_redirect', 'yith_wcmap_stripe_compatibility', 5 );
}

/*####################################
* YITH WOOCOMMERCE REQUEST A QUOTE
######################################*/

if( defined( 'YITH_YWRAQ_PREMIUM' ) && YITH_YWRAQ_PREMIUM ) {
	/**
	 * Add Request Quote compatibility
	 *
	 * @author Francesco Licandro
	 */
	function yith_wcmap_request_quote_compatibility(){

		if( class_exists( 'YITH_YWRAQ_Order_Request' ) ) {
			// remove content in my account
			remove_action( 'woocommerce_before_my_account', array( YITH_YWRAQ_Order_Request(), 'my_account_my_quotes' ) );
			remove_action( 'template_redirect', array( YITH_YWRAQ_Order_Request(), 'load_view_quote_page' ) );
		}
	}

	add_action( 'template_redirect', 'yith_wcmap_request_quote_compatibility', 5 );
}

/*####################################
* YITH WOOCOMMERCE WAITING LIST
######################################*/

if( defined( 'YITH_WCWTL_PREMIUM' ) && YITH_WCWTL_PREMIUM ) {
	/**
	 * Add Request Quote compatibility
	 *
	 * @author Francesco Licandro
	 */
	function yith_wcmap_waiting_list_compatibility(){

		if( class_exists( 'YITH_WCWTL_Frontend' ) ) {
			// remove content in my account
			remove_action( 'woocommerce_before_my_account', array( YITH_WCWTL_Frontend(), 'add_waitlist_my_account' ) );
		}
	}

	add_action( 'template_redirect', 'yith_wcmap_waiting_list_compatibility', 5 );
}

if( ! function_exists( 'yith_wcmap_woocommerce_subscription_compatibility' ) ) {
	/**
	 * Add Request Quote compatibility
	 *
	 * @author Francesco Licandro
	 */
	function yith_wcmap_woocommerce_subscription_compatibility(){

		if( ! class_exists( 'WC_Subscriptions' ) ) {
			return;
		}

		// remove content in my account
		remove_action( 'woocommerce_before_my_account', array( 'WC_Subscriptions', 'get_my_subscriptions_template' ) );
		add_shortcode( 'ywcwtl_woocommerce_subscription', 'ywcwtl_woocommerce_subscription' );
	}

	function ywcwtl_woocommerce_subscription( $args ){
		
		global $wp;

		ob_start();
		if( ! empty( $wp->query_vars['view-subscription'] ) ) {
			wc_get_template( 'myaccount/view-subscription.php', array(), '', plugin_dir_path( WC_Subscriptions::$plugin_file ) . 'templates/' );
		}
		else {
			WC_Subscriptions::get_my_subscriptions_template();
		}

		return ob_get_clean();
	}
}
add_action( 'template_redirect', 'yith_wcmap_woocommerce_subscription_compatibility', 5 );

/*###########################
* COMPATIBILITY WITH WC 2.6
#############################*/

/**
 * Check if WC version is 2.6
 *
 * @author Francesco Licandro
 * @return mixed
 */
function yith_wcmap_wc26(){
	return version_compare( WC()->version, '2.6', '>=' );
}

if( yith_wcmap_wc26() ) {

	// remove standard woocommerce sidebar;
	if( $priority = has_action( 'woocommerce_account_navigation', 'woocommerce_account_navigation' ) ) {
		remove_action( 'woocommerce_account_navigation', 'woocommerce_account_navigation', $priority );
	}

	// Add endpoint WC 2.6
	add_filter( 'yith_wcmap_get_default_endpoints_keys_array', 'yith_wcmap_default_endpoints_keys_wc26', 10, 1 );
	// Assign active class to endpoint payment-methods for other payment methods endpoints
	add_filter( 'yith_wcmap_endpoint_menu_class', 'yith_wcmap_set_active_payment_methods', 10, 3 );

	/**
	 * Add endpoint WC 2.6
	 *
	 * @param array $endpoints
	 * @return array
	 * @author Francesco Licandro
	 */
	function yith_wcmap_default_endpoints_keys_wc26( $endpoints ) {
		// add wc 2.6 endpoints
		$endpoints[] = 'payment-methods';
		return $endpoints;
	}

	/**
	 * Assign active class to endpoint payment-methods for other payment methods endpoints
	 *
	 * @since 1.1.0
	 * @author Francesco Licandro
	 * @param array $classes
	 * @param string $endpoint
	 * @param array $options
	 * @return array
	 */
	function yith_wcmap_set_active_payment_methods( $classes, $endpoint, $options ) {

		if( $endpoint == 'payment-methods' ) {

			$current = WC()->query->get_current_endpoint();
			if( ! in_array( 'active', $classes ) &&
			    in_array( $current, array( 'add-payment-method', 'delete-payment-method', 'set-default-payment-method' ) ) ) {
				$classes[] = 'active';
			}
		}

		return $classes;
	}
}