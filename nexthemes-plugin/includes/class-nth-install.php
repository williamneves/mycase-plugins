<?php 

if ( ! defined( 'ABSPATH' ) ) exit;

class Nexthemes_Installations {
	
	public function __construct(){
		
	}
	
	public static function install(){
		flush_rewrite_rules();
		
	}
	
}