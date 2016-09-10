<?php
/**
 * @package nth-portfolios
 */

if( !class_exists( 'Nexthemes_Portfolio_Admin' ) ) {
	
	class Nexthemes_Portfolio_Admin extends Nexthemes_Portfolio {
		
		function __construct(){
			parent::__construct();
			self::init();
		}
		
		public function init(){
			add_filter('manage_'.$this->post_type.'_posts_columns', array( $this, 'tableHeading' ) );
			add_action( 'manage_'.$this->post_type.'_posts_custom_column', array( $this,'tableContent' ), 10, 2 );
			add_action( 'admin_enqueue_scripts', array( $this, 'registerScript' ) );
		}
		
		public function registerScript(){
			/*do something*/
		}
		
		public function tableHeading( $columns = array() ){
			if( count($columns) == 0  ) return;
			$columns = array_merge(array( 'cb' => "", 'nth_thumb' => "<span class=\"dashicons dashicons-format-image\"> </span>" ), $columns);
			return $columns;
		}
		
		public function tableContent( $column_name, $post_id ){
			if( strcmp( trim($column_name), 'nth_thumb' ) == 0 ): 
				if( has_post_thumbnail() ): ?>
					<a href="<?php echo get_edit_post_link();?>">
					<?php the_post_thumbnail('thumbnail'); ?>
					</a>
				<?php else: 
					echo "—";
				endif;
			endif;
		}
		
		
	}

	new Nexthemes_Portfolio_Admin();
}
