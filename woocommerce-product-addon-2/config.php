<?php
/*
 * this file contains pluing meta information and then shared
 * between pluging and admin classes
 * * [1]
 */



if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
	define('NM_DIR_SEPERATOR', '\\');
} else {
	define('NM_DIR_SEPERATOR', '/');
}

function get_plugin_meta_wooproduct(){
	
	
	return array('name'			=> 'Personalized Product',
							'dir_name'		=> '',
							'shortname'		=> 'nm_personalizedproduct',
							'path'			=> untrailingslashit(plugin_dir_path( __FILE__ )),
							'url'			=> untrailingslashit(plugin_dir_url( __FILE__ )),
							'db_version'	=> 3.12,
							'logo'			=> plugin_dir_url( __FILE__ ) . 'images/logo.png',
							'menu_position'	=> 90
	);
}


function nm_personalizedproduct_pa($arr){
	
	echo '<pre>';
	print_r($arr);
	echo '</pre>';
}

/**
 * some WC functions wrapper
 * */
 
if( !function_exists('nm_wc_add_notice')){
function nm_wc_add_notice($string, $type="error"){
 	
 	global $woocommerce;
 	if( version_compare( $woocommerce->version, 2.1, ">=" ) ) {
 		wc_add_notice( $string, $type );
	    // Use new, updated functions
	} else {
	   $woocommerce->add_error ( $string );
	}
 	
 }
 }
 
 function plugin_is_active_ppom($plugin_path) {
    $return_var = in_array( $plugin_path, apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );
    return $return_var;
  }

