<?php


if( !class_exists( 'Nexthemes_TeamMembers' ) ) {
	
	class Nexthemes_TeamMembers {
		
		protected $post_type = 'team';
		
		public function __construct(){
			add_action('init', array($this,'registerPostType') );
			add_action('init', array($this,'addImageSize') );
		}
		
		public function addImageSize(){
			$size = array(
				'width'		=> '270',
				'height'	=> '270',
				'crop'		=> '1'
			);
			if( class_exists( 'Nexthemes_Plugin_Panel' ) ) {
				$options = Nexthemes_Plugin_Panel::get_option();
				$options = isset( $options['teams_thumb'] )? $options['teams_thumb']: array();
				$size = wp_parse_args( $options, $size );
			}
			
			add_image_size( 'teams_thumb', absint( $size['width'] ), absint( $size['height'] ), absint( $size['crop'] ) );
			
		}
		
		public function registerPostType(){
			$labels = array(
				'name'					=> _x( 'Team Members', 'post type general name', 'nexthemes-plugin' ),
				'singular_name'			=> _x( 'Team Member', 'post type singular name', 'nexthemes-plugin' ),
				'menu_name'				=> _x( 'NTH Members', 'admin menu', 'nexthemes-plugin' ),
				'name_admin_bar'		=> _x( 'Team Members', 'add new on admin bar', 'nexthemes-plugin' ),
				'add_new'				=> _x( 'Add New', 'Team Member', 'nexthemes-plugin' ),
				'add_new_item'			=> __( 'Add New Member', 'nexthemes-plugin' ),
				'new_item'				=> __( 'New Member', 'nexthemes-plugin' ),
				'edit_item'				=> __( 'Edit Member', 'nexthemes-plugin' ),
				'view_item'				=> __( 'View Member', 'nexthemes-plugin' ),
				'all_items'				=> __( 'All Members', 'nexthemes-plugin' ),
				'search_items'			=> __( 'Search Members', 'nexthemes-plugin' ),
				'parent_item_colon'		=> __( 'Parent Member:', 'nexthemes-plugin' ),
				'not_found'				=> __( 'No Members found.', 'nexthemes-plugin' ),
				'not_found_in_trash'	=> __( 'No Members found in Trash.', 'nexthemes-plugin' )
			);
			
			$args = array(
				'labels'             => $labels,
				'public'             => false,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'query_var'          => false,
				'rewrite'            => array( 'slug' => $this->post_type ),
				'capability_type'    => 'post',
				'has_archive'        => false,
				'hierarchical'       => false,
				'menu_position'      => null,
				'can_export'		 => true,
				'exclude_from_search' 	=> false,
				'menu_icon'			 => "dashicons-admin-users",
				'show_in_nav_menus' 	=> false,
				'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' )
			);
			
			register_post_type( $this->post_type, $args);
		}
		
	}
	
}