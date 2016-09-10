<?php
/**
 * Load all admin classes
 *
 * @author James Collings <james@jclabs.co.uk>
 * @version 0.1
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

include_once 'class-jcmc-admin-update.php';

/**
 * Plugin Settings
 * 
 * @param  array  $pages 
 * @return array
 */
function jcmc_register_settings($pages = array()){
	$pages[] = include_once 'class-jcmc-admin-settings.php';
	return $pages;
}
add_filter( 'woocommerce_get_settings_pages', 'jcmc_register_settings' );