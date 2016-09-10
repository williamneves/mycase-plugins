<?php
/**
 * @package nth-portfolios
 */

if( !class_exists( 'Nexthemes_Portfolio' ) ) {
	
	class Nexthemes_Portfolio {
		
		protected $post_type = 'portfolio';
		
		protected $tax_cat = 'portfolio_cat';
		
		protected $tax_tag = 'portfolio_tag';
		
		function __construct(){
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
				$options = isset( $options['portfolio_thumb'] )? $options['portfolio_thumb']: array();
				if( empty($options['crop']) ) $options['crop'] = 0;
				$size = wp_parse_args( $options, $size );
			}
			
			add_image_size( 'portfolio_thumb', absint( $size['width'] ), absint( $size['height'] ), absint( $size['crop'] ) );
			
		}
		
		public function registerPostType(){
			$labels = array(
				'name'					=> _x( 'NTH Portfolios', 'post type general name', 'nexthemes-plugin' ),
				'singular_name'			=> _x( 'Portfolio', 'post type singular name', 'nexthemes-plugin' ),
				'menu_name'				=> _x( 'NTH Portfolios', 'admin menu', 'nexthemes-plugin' ),
				'name_admin_bar'		=> _x( 'Portfolio', 'add new on admin bar', 'nexthemes-plugin' ),
				'add_new'				=> _x( 'Add New', 'Portfolio', 'nexthemes-plugin' ),
				'add_new_item'			=> __( 'Add New Portfolio', 'nexthemes-plugin' ),
				'new_item'				=> __( 'New Portfolio', 'nexthemes-plugin' ),
				'edit_item'				=> __( 'Edit Portfolio', 'nexthemes-plugin' ),
				'view_item'				=> __( 'View Portfolio', 'nexthemes-plugin' ),
				'all_items'				=> __( 'All Portfolios', 'nexthemes-plugin' ),
				'search_items'			=> __( 'Search Portfolios', 'nexthemes-plugin' ),
				'parent_item_colon'		=> __( 'Parent Portfolios:', 'nexthemes-plugin' ),
				'not_found'				=> __( 'No Portfolios found.', 'nexthemes-plugin' ),
				'not_found_in_trash'	=> __( 'No Portfolios found in Trash.', 'nexthemes-plugin' )
			);
			
			$args = array(
				'labels'             => $labels,
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'query_var'          => true,
				'rewrite'            => array( 'slug' => $this->post_type ),
				'capability_type'    => 'post',
				'has_archive'        => true,
				'hierarchical'       => false,
				'menu_position'      => null,
				'can_export'		 => true,
				'exclude_from_search' => false,
				'taxonomies'		 => array( $this->tax_cat, $this->tax_tag ),
				'menu_icon'			 => "dashicons-portfolio",
				'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' )
			);
			
			register_post_type( $this->post_type, $args);

			flush_rewrite_rules();
			
			$this->registerCategoryTax();
		}
		
		public function registerCategoryTax(){
			$labels = array(
				'name'                       => _x( 'Categories', 'taxonomy general name', 'nexthemes-plugin' ),
				'singular_name'              => _x( 'Category', 'taxonomy singular name', 'nexthemes-plugin' ),
				'search_items'               => __( 'Search Categories', 'nexthemes-plugin' ),
				'popular_items'              => __( 'Popular Categories', 'nexthemes-plugin' ),
				'all_items'                  => __( 'All Categories', 'nexthemes-plugin' ),
				'parent_item'                => null,
				'parent_item_colon'          => null,
				'edit_item'                  => __( 'Edit Category', 'nexthemes-plugin' ),
				'update_item'                => __( 'Update Category', 'nexthemes-plugin' ),
				'add_new_item'               => __( 'Add New Category', 'nexthemes-plugin' ),
				'new_item_name'              => __( 'New Category Name', 'nexthemes-plugin' ),
				'separate_items_with_commas' => __( 'Separate Categories with commas', 'nexthemes-plugin' ),
				'add_or_remove_items'        => __( 'Add or remove Categories', 'nexthemes-plugin' ),
				'choose_from_most_used'      => __( 'Choose from the most used Categories', 'nexthemes-plugin' ),
				'not_found'                  => __( 'No Categories found.', 'nexthemes-plugin' ),
				'menu_name'                  => __( 'Categories', 'nexthemes-plugin' ),
			);
			
			$args = array(
				'hierarchical'          => true,
				'labels'                => $labels,
				'show_ui'               => true,
				'show_admin_column'     => true,
				//'update_count_callback' => '_update_post_term_count',
				'query_var'             => true,
				'rewrite'               => array( 'slug' => $this->tax_cat ),
			);
			
			register_taxonomy( $this->tax_cat, $this->post_type, $args );
			
			$labels = array(
				'name'                       => _x( 'Tags', 'taxonomy general name', 'nexthemes-plugin' ),
				'singular_name'              => _x( 'Tag', 'taxonomy singular name', 'nexthemes-plugin' ),
				'search_items'               => __( 'Search Tags', 'nexthemes-plugin' ),
				'popular_items'              => __( 'Popular Tags', 'nexthemes-plugin' ),
				'all_items'                  => __( 'All Tags', 'nexthemes-plugin' ),
				'parent_item'                => null,
				'parent_item_colon'          => null,
				'edit_item'                  => __( 'Edit Tag', 'nexthemes-plugin' ),
				'update_item'                => __( 'Update Tag', 'nexthemes-plugin' ),
				'add_new_item'               => __( 'Add New Tag', 'nexthemes-plugin' ),
				'new_item_name'              => __( 'New Tag Name', 'nexthemes-plugin' ),
				'separate_items_with_commas' => __( 'Separate tags with commas', 'nexthemes-plugin' ),
				'add_or_remove_items'        => __( 'Add or remove tags', 'nexthemes-plugin' ),
				'choose_from_most_used'      => __( 'Choose from the most used tags', 'nexthemes-plugin' ),
				'not_found'                  => __( 'No tags found.', 'nexthemes-plugin' ),
				'menu_name'                  => __( 'Tags', 'nexthemes-plugin' ),
			);
			
			$args = array(
				'hierarchical'          => false,
				'labels'                => $labels,
				'show_ui'               => true,
				'show_admin_column'     => true,
				//'update_count_callback' => '_update_post_term_count',
				'query_var'             => true,
				'rewrite'               => array( 'slug' => $this->tax_tag ),
			);
			
			register_taxonomy( $this->tax_tag, $this->post_type, $args );
		}
		
	}
}
