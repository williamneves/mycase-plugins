<?php
/**
 * @package nth-portfolios
 */

if( !class_exists( 'Nexthemes_TeamMembers_Front' ) ) {
	
	class Nexthemes_TeamMembers_Front extends Nexthemes_TeamMembers {
		
		function __construct(){
			parent::__construct();
			
		}
		
		public function getByIds( $ids = array() ){
			if( count($ids) == 0 ) return '';
			
			$team = new WP_Query( array( 'post_type' => $this->post_type, 'post__in' => $ids ) );
			
			return $team;
		}
		
	}
	
}
