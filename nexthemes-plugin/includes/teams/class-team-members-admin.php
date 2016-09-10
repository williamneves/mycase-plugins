<?php
/**
 * @package nth-portfolios
 */

if( !class_exists( 'Nexthemes_TeamMembers_Admin' ) ) {
	
	class Nexthemes_TeamMembers_Admin extends Nexthemes_TeamMembers {
		
		function __construct(){
			parent::__construct();
			self::init();
		}
		
		public function init(){
			add_filter('manage_'.$this->post_type.'_posts_columns', array( $this, 'tableHeading' ) );
			add_action( 'manage_'.$this->post_type.'_posts_custom_column', array( $this,'tableContent' ), 10, 2 );
		}
		
		public function tableHeading( $columns = array() ){
			if( count($columns) == 0  ) return;
			
			$args = array( 
				'cb' => "", 
				'nth_thumb' => "<span class=\"dashicons dashicons-format-image\"> </span>",
				'title'	=> '',
				'role'	=> __('Role', 'nexthemes-plugin'),
			);
			$columns = array_merge( $args, $columns );
			$columns['title'] = __('Name', 'nexthemes-plugin');
			return $columns;
		}
		
		public function tableContent( $column_name, $post_id ){
			$team_options = unserialize( get_post_meta( $post_id, 'nth_team_options', true ) );
			switch( trim($column_name) ){
				case 'nth_thumb':
					if( has_post_thumbnail() ){
						echo '<a href="'.get_edit_post_link().'">';
						the_post_thumbnail('thumbnail');
						echo '</a>';
					} else echo "—";
					break;
				case 'role':
					echo $team_options['role'];
					break;
			}
		}
		
		
	}

	new Nexthemes_TeamMembers_Admin();
}
