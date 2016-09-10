<?php
/**
 * Plugin Update API
 *
 * Check jclabs for plugin update, and overwrite default plugin update behaviour
 *
 * @author James Collings <james@jclabs.co.uk>
 * @version 0.1
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class JCMC_Update{

	private $api_url = null;
	private $plugin_slug = null;

	public function __construct(){

		$this->api_url = 'http://jclabs.co.uk/api/';
		$this->plugin_slug = JCMC()->get_plugin_slug();
		
		// Take over the update check
		add_filter('pre_set_site_transient_update_plugins', array( $this, 'check_for_plugin_update' ) );

		// Take over the Plugin info screen
		add_filter('plugins_api', array( $this, 'plugin_api_call' ), 10, 3);
	}

	public function check_for_plugin_update($checked_data) {

		global $wp_version;

		$plugin_slug = JCMC()->get_plugin_slug();
		
		//Comment out these two lines during testing.
		if (empty($checked_data->checked))
			return $checked_data;
		
		$args = array(
			'slug' => $plugin_slug,
			'version' => $checked_data->checked[$plugin_slug .'/'. $plugin_slug .'.php'],
		);
		$request_string = array(
				'body' => array(
					'action' => 'basic_check', 
					'request' => serialize($args),
					'api-key' => md5(get_bloginfo('url'))
				),
				'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url')
			);
		
		// Start checking for an update
		$raw_response = wp_remote_post($this->api_url, $request_string);
		
		if (!is_wp_error($raw_response) && ($raw_response['response']['code'] == 200))
			$response = unserialize($raw_response['body']);
		
		if (is_object($response) && !empty($response)) // Feed the update data into WP updater
			$checked_data->response[$plugin_slug .'/'. $plugin_slug .'.php'] = $response;
		
		return $checked_data;
	}

	public function plugin_api_call($def, $action, $args) {

		global $wp_version;

		$plugin_slug = JCMC()->get_plugin_slug();
		
		if ( !isset($args->slug) || $args->slug != $plugin_slug)
			return false;
		
		// Get the current version
		$plugin_info = get_site_transient('update_plugins');
		$current_version = $plugin_info->checked[$plugin_slug .'/'. $plugin_slug .'.php'];
		$args->version = $current_version;
		
		$request_string = array(
				'body' => array(
					'action' => $action, 
					'request' => serialize($args),
					'api-key' => md5(get_bloginfo('url'))
				),
				'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url')
			);
		
		$request = wp_remote_post($this->api_url, $request_string);
		
		if (is_wp_error($request)) {
			$res = new WP_Error('plugins_api_failed', __('An Unexpected HTTP Error occurred during the API request.</p> <p><a href="?" onclick="document.location.reload(); return false;">Try again</a>'), $request->get_error_message());
		} else {
			$res = unserialize($request['body']);
			
			if ($res === false)
				$res = new WP_Error('plugins_api_failed', __('An unknown error occurred'), $request['body']);
		}
		
		return $res;
	}

}

new JCMC_Update();