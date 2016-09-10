<?php
/**
 * @package nth-portfolios
 */

if( !class_exists( 'Nexthemes_Portfolio_Front' ) ) {
	
	class Nexthemes_Portfolio_Front extends Nexthemes_Portfolio {

		function __construct(){
			parent::__construct();
			$this->init_shortcode();
		}
		
		public function getCats(){
			$args = array(
				'hide_empty'	=> true,
				'orderby'		=> 'title',
				'order'			=> 'ASC',
				'post_type'		=> $this->post_type,
			);
			$terms = get_terms( $this->tax_cat, $args );
			return $terms;
		}
		
		private function init_shortcode(){
			add_shortcode( 'theshopier_portfolio', array($this, 'nth_portfolio') );
		}
		
		private function paging_nav() {
			global $wp_query;
			if(function_exists ('wp_pagenavi')){
				wp_pagenavi() ; return;
			}
			echo '<div class="wp-pagenavi">';
			$links = paginate_links( array(
				'total'    => $wp_query->max_num_pages,
				'current'  => max( 1, get_query_var('paged') ),
				'mid_size' => 3,
				'type'      => 'list',
			) );
			echo $links;
			echo '</div>';
		}
		
		public function nth_portfolio( $atts = array() ){
			$atts = shortcode_atts(array(
				'columns' 				=>  4
				,'filter_s' 			=>  1
				,'title_s' 				=>  1
				,'desc_s' 				=>  1
				,'limit' 				=>  '-1'
			),$atts);
			
			ob_start();
			?>
			
			<div class="nth-portfolios-wrapper">
				<div class="nth-portfolio-container">
					<?php if( absint( $atts["filter_s"] ) ):?>
					<div class="nth-portfolio-filters-wrap">
						<?php $this->get_filters();?>
					</div><!-- .nth-portfolio-filters -->
					<?php endif;?>
					<div class="nth-portfolio-content row">
						<?php $this->get_content( $atts );?>
					</div><!-- .nth-portfolio-content -->
				</div><!-- .nth-portfolio-container -->

				<?php
				if( function_exists('theshopier_paging_nav') ) {
					theshopier_paging_nav();
				} else {
					$this->paging_nav();
				}
				?>

			</div>
			<?php 
			$content = ob_get_clean();
			wp_reset_query();
			return $content;
			
		}
		
		public function get_filters(){
			$cats = $this->getCats();
			?>
            <div class="nth-tabs">
                <ul class="nth-portfolio-filters tabs   ">
                    <li id="all" class="active"><a href="javascript:void(0)" id="all_a" data-filter=".nth-portfolio-item" class="filter active"><?php _e('ALL', 'nexthemes-plugin');?></a></li>
                    <?php foreach( $cats as $cat ){ ?>
                    <li id="<?php echo esc_html($cat->slug) ; ?>"><a href="javascript:void(0)" data-filter=".<?php echo esc_html($cat->slug) ; ?>" id="<?php echo esc_html($cat->slug) ; ?>_a" class="filter-portfoio"><?php echo esc_html($cat->name); ?></a></li>
                    <?php }?>
                </ul>
            </div>
			<?php 
		}
		
		public function get_content( $atts = array() ){
			$def = array(
				'columns' 				=>  4
				,'filter_s' 			=>  1
				,'title_s' 				=>  1
				,'desc_s' 				=>  1
				,'limit' 				=>  '-1'
			);

			$atts = wp_parse_args($atts, $def);

			query_posts( "post_type={$this->post_type}&posts_per_page=".$atts["limit"]."&paged=" . get_query_var('paged') );
			if(have_posts()) : 
				while(have_posts()): the_post();
					global $post;global $wp_query;
					$class = array();
					
					$class[] = 'col-lg-' . round( 24 / absint($atts["columns"]) );
					$class[] = 'col-md-' . round( 24 / (1+round( absint($atts["columns"]) * 992 / 1200)) );
					$class[] = 'col-sm-' . round( 24 / (round( absint($atts["columns"]) * 768 / 1200)) );
					$class[] = 'col-xs-' . round( 24 / (round( absint($atts["columns"]) * 480 / 1200)) );
					$class[] = 'col-mb-24';
					
					$cats = wp_get_post_terms($post->ID, $this->tax_cat, array("fields" => "slugs"));
					
					//thumbnail
					$thumb = get_post_thumbnail_id($post->ID);
					$thumb_url = wp_get_attachment_image_src($thumb,'full');
					
					?>
					<div class="nth-portfolio-item <?php echo esc_attr(implode(' ', $class));?> <?php echo esc_attr( implode(' ', $cats) );?>" data-filter="<?php echo esc_attr( implode(',', $cats) );?>">
						<div class="nth-portfolio-thumb">
							<div class="thumnail">
								<a href="<?php echo get_permalink();?>" title="<?php echo get_the_title();?>"><?php if(has_post_thumbnail()) the_post_thumbnail('portfolio_thumb');?></a>
								<div class="icons thumb-holder">
									<a href="<?php echo esc_url($thumb_url[0]);?>" class="nth-pretty-photo" data-rel="prettyPhoto[image]"><i class="fa icon-nth-search"></i></a>
								</div>
							</div>
							<div class="summary">
								<?php if( absint( $atts["title_s"] ) ):?>
								<h3><a href="<?php echo get_permalink();?>" title="<?php echo get_the_title();?>"><?php the_title();?></a></h3>
								<?php endif;?>
								<div class="nth-meta">
									<?php //echo get_the_term_list( $post->ID, $this->tax_cat, "<div class=\"meta-cats\"><strong>Cats: </strong>", ', ', "</div><!--.meta-cats-->" );?>
									<?php if( absint( $atts["desc_s"] ) ):?>
									<?php the_excerpt();?>
									<?php endif;?>
								</div>
							</div>
						</div>
						
						
						
					</div>
					<?php 
				endwhile;
			endif;
			
		}

		
	}

	new Nexthemes_Portfolio_Front();
}
