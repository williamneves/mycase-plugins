<?php 
// ! File Security Check
if ( ! defined( 'ABSPATH' ) ) exit;

if( ! class_exists( 'Nexthemes_Woo_Shortcodes' ) ) :

class Nexthemes_Woo_Shortcodes {

	private static $classes = array('nth-shortcode', 'woocommerce');
	
	public static function get_template( $template_name, $args = array(), $products = null ){
		if ( $args && is_array( $args ) ) {
			extract( $args );
		}
		
		$located = NEXTHEMES_PLUGIN_TMPL_DIR . $template_name;
		
		if ( ! file_exists( $located ) ) {
			_doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', $located ), '1.0' );
			return;
		}
		
		include( $located );
	}
	
	public static function ajax_call(){
		add_action( 'wp_ajax_nth_woo_get_product_by_cat' , array( __CLASS__, 'woo_get_product_by_cat' ) );
		add_action( 'wp_ajax_nopriv_nth_woo_get_product_by_cat', array( __CLASS__, 'woo_get_product_by_cat' ) );
	}
	
	public static function shortcodeArgs(){
		self::ajax_call();
		return array(
			'theshopier_top_rated_products'	=> __CLASS__ . '::top_rated_products',
			'theshopier_best_selling_products'	=> __CLASS__ . '::best_selling_products',
			'theshopier_recent_products'		=> __CLASS__ . '::recent_products',
			'theshopier_sale_products'			=> __CLASS__ . '::sale_products',
			'theshopier_products_category'		=> __CLASS__ . '::products_category',
			'theshopier_products_cats_tabs'	=> __CLASS__ . '::products_categories_tabs',
			'theshopier_featured_products'		=> __CLASS__ . '::featured_products',
			'theshopier_products'				=> __CLASS__ . '::products',
			'theshopier_product_tags'			=> __CLASS__ . '::product_tags',
			'theshopier_product_cats'			=> __CLASS__ . '::product_cats',
			'theshopier_profile_meta'			=> __CLASS__ . '::profile_meta',
			'theshopier_product_subcaterories'	=> __CLASS__ . '::product_subcaterories',
			'theshopier_product_brands'		=> __CLASS__ . '::product_brands',
			'theshopier_featured_prod_cats'	=> __CLASS__ . '::featured_prod_cats',
			'theshopier_woo_single_cat'		=> __CLASS__ . '::woo_single_cat',
			'theshopier_woo_recently_viewed'	=> __CLASS__ . '::woocommerce_recently_viewed',
			'theshopier_woo_attributes'		=> __CLASS__ . '::woo_attributes',
			'theshopier_woo_cats'				=> __CLASS__ . '::woo_categories'
		);
	}
	
	public static function products( $atts ){
		global $woocommerce_loop;
		
		if ( empty( $atts ) ) return '';
		
		$atts = shortcode_atts( array(
			"title" 		=> '',
			"item_style"	=> 'grid',
			"as_widget"		=> '0',
			"box_style"		=> '',
			"head_style"	=> '',
			"is_slider"		=> '1',
			"is_biggest"	=> 0,
			"auto_play"		=> '0',
			"excerpt_limit" => 10,
			'columns' 		=> '4',
			'orderby' 		=> 'title',
			'order'   		=> 'asc',
			'ids'     		=> '',
			'skus'    		=> ''
		), $atts );

		$classes = self::pareShortcodeClass( 'columns-' . absint( $atts['columns'] ) );

		$cache_key = 'theshopier_'. __FUNCTION__;
		$cache = self::get_cached_shortcode($atts, $cache_key);

		if( !is_array($cache) && !empty($cache) ){
			return '<div class="'.esc_attr( implode( ' ', $classes ) ).'">' . $cache . '</div>';
		}
		
		$meta_query = WC()->query->get_meta_query();
		
		$args = array(
			'post_type'           => 'product',
			'post_status'         => 'publish',
			'ignore_sticky_posts' => 1,
			'orderby'             => $atts['orderby'],
			'order'               => $atts['order'],
			'posts_per_page'      => -1,
			'meta_query'          => $meta_query
		);
		
		if ( ! empty( $atts['skus'] ) ) {
			$skus = explode( ',', $atts['skus'] );
			$skus = array_map( 'trim', $skus );
			$args['meta_query'][] = array(
				'key' 		=> '_sku',
				'value' 	=> $skus,
				'compare' 	=> 'IN'
			);
		}
		
		if ( ! empty( $atts['ids'] ) ) {
			$ids = explode( ',', $atts['ids'] );
			$ids = array_map( 'trim', $ids );
			$args['post__in'] = $ids;
		}
		
		ob_start();
		
		$products = new WP_Query( apply_filters( 'woocommerce_shortcode_products_query', $args, $atts ) );
		
		$old_columns = $woocommerce_loop['columns'];
		
		if ( $products->have_posts() ) :

			if( absint($atts['as_widget']) )
				self::get_template( 'shortcode-woo-widget.tpl.php', $atts, $products );
			else {
				if( absint($atts['is_biggest']) ) self::get_template( 'shortcode-woo-big.tpl.php', $atts, $products );
				else self::get_template( 'shortcode-woo-nomal.tpl.php', $atts, $products );
			}
			
		endif;
		
		wp_reset_postdata();
		
		$woocommerce_loop['columns'] = $old_columns;
		$output = ob_get_clean();
		$output = self::cache_shortcode($atts, $cache_key, $output, $cache);
		
		return '<div class="'.esc_attr( implode( ' ', $classes ) ).'">' . $output . '</div>';
	}
	
	public static function sale_products( $atts ) {
		global $woocommerce_loop, $woocommerce;
		
		$atts = shortcode_atts(array(
			'title' 		=> '',
			'item_style'	=> 'grid',
			'is_slider'		=> '1',
			"is_biggest"	=> 0,
			'auto_play'		=> '0',
			'per_page'		=> '12',
			'columns'		=> '4',
			'orderby'		=> 'title',
			'is_deal'		=> 0,
			'order'			=> 'asc',
			'as_widget'		=> 0,
			"box_style"		=> '',
			"head_style"	=> '',
			'excerpt_limit' => 10,
			'hide_free'		=> 0,
			'show_hidden'	=> 0
		),$atts);
		
		$product_ids_on_sale = wc_get_product_ids_on_sale();
		
		$args = array(
			'posts_per_page'	=> $atts['per_page'],
			'orderby' 			=> $atts['orderby'],
			'order' 			=> $atts['order'],
			'no_found_rows' 	=> 1,
			'post_status' 		=> 'publish',
			'post_type' 		=> 'product',
			'meta_query' 		=> array(),
			'post__in'			=> array_merge( array( 0 ), $product_ids_on_sale )
		);
		
		if( absint($atts['as_widget']) ) {
			if ( !absint($atts['show_hidden']) ) {
				$args['meta_query'][] = WC()->query->visibility_meta_query();
				$args['post_parent']  = 0;
			}
			
			if ( absint( $atts['hide_free'] ) ) {
				$args['meta_query'][] = array(
					'key'     => '_price',
					'value'   => 0,
					'compare' => '>',
					'type'    => 'DECIMAL',
				);
			}
		} else {
			//$args['meta_query'] = WC()->query->get_meta_query();
			if( absint($atts['is_deal']) ) {
				$fil_date = strtotime ("now");
				$args['meta_query'] = array(
					array(
						'key' => '_sale_price_dates_from',
						'value' => $fil_date,
						'compare' => '<=',
						'type' => 'numeric'
					),
					array(
						'key' => '_sale_price_dates_to',
						'value' => $fil_date,
						'compare' => '>=',
						'type' => 'numeric'
					)
				);
			} else {
				$args['meta_query'] = WC()->query->get_meta_query();
			}

		}
		
		ob_start();
		
		$products = new WP_Query( apply_filters( 'woocommerce_shortcode_products_query', $args, $atts ) );
		
		$old_columns = $woocommerce_loop['columns'];
		
		if ( $products->have_posts() ) :

			if( absint($atts['as_widget']) )
				self::get_template( 'shortcode-woo-widget.tpl.php', $atts, $products );
			else {
				if( absint($atts['is_biggest']) ) self::get_template( 'shortcode-woo-big.tpl.php', $atts, $products );
				else self::get_template( 'shortcode-woo-nomal.tpl.php', $atts, $products );
			}
		
		endif;
		
		wp_reset_postdata();
		
		$woocommerce_loop['columns'] = $old_columns;
		
		$classes = self::pareShortcodeClass( 'columns-' . absint( $atts['columns'] ) );
		
		return '<div class="'.esc_attr(implode(' ', $classes)).'">' . ob_get_clean() . '</div>';
	}
	
	public static function products_category( $atts ){
		global $woocommerce_loop;
		
		$atts = shortcode_atts( array(
			"title" 		=> '',
			"item_style"	=> 'grid',
			"as_widget"		=> '0',
			"box_style"		=> '',
			"head_style"	=> '',
			"per_page"		=> '12',
			"is_slider"		=> '1',
			"is_biggest"	=> 0,
			"auto_play"		=> '0',
			"excerpt_limit" => 10,
			"columns"		=> '4',
			"orderby"		=> 'date',
			"order"			=> 'desc',
			"category" 		=> '',
			"hide_free"		=> 0,
			"show_hidden"	=> 0
		), $atts );
		
		$ordering_args = WC()->query->get_catalog_ordering_args( $atts['orderby'], $atts['order'] );
		$meta_query    = WC()->query->get_meta_query();
		
		$args = array(
			'post_type'				=> 'product',
			'post_status' 			=> 'publish',
			'ignore_sticky_posts'	=> 1,
			'orderby' 				=> $ordering_args['orderby'],
			'order' 				=> $ordering_args['order'],
			'posts_per_page' 		=> $atts['per_page'],
			'meta_query' 			=> $meta_query,
			'tax_query' 			=> array(
				array(
					'taxonomy' 		=> 'product_cat',
					'terms' 		=> array_map( 'sanitize_title', explode( ',', $atts['category'] ) ),
					'field' 		=> 'slug',
					'operator' 		=> 'IN'
				)
			)
		);
		
		if ( isset( $ordering_args['meta_key'] ) ) {
			$args['meta_key'] = $ordering_args['meta_key'];
		}
		
		ob_start();
		
		$products = new WP_Query( apply_filters( 'woocommerce_shortcode_products_query', $args, $atts ) );
		
		if ( $products->have_posts() ) :

			if( absint($atts['as_widget']) )
				self::get_template( 'shortcode-woo-widget.tpl.php', $atts, $products );
			else {
				if( absint($atts['is_biggest']) ) self::get_template( 'shortcode-woo-big.tpl.php', $atts, $products );
				else self::get_template( 'shortcode-woo-nomal.tpl.php', $atts, $products );
			}
			
		endif;
		
		wp_reset_postdata();
		
		$classes = self::pareShortcodeClass( 'columns-' . absint( $atts['columns'] ) );

		return '<div class="'.esc_attr(implode(' ', $classes)).'">' . ob_get_clean() . '</div>';
	}
	
	public static function recent_products( $atts ){
		global $woocommerce_loop;
		
		$atts = shortcode_atts(array(
			"title" 		=> '',
			"item_style"	=> 'grid',
			"as_widget"		=> '0',
			"box_style"		=> '',
			"head_style"	=> '',
			"is_slider"		=> '1',
			"is_biggest"	=> 0,
			"auto_play"		=> '0',
			"excerpt_limit" => 10,
			"per_page"		=> '12',
			"columns"		=> '4',
			"orderby"		=> 'date',
			"order"			=> 'desc',
			"hide_free"		=> 0,
			"show_hidden"	=> 0
		),$atts);
		
		$args = array(
			'post_type'				=> 'product',
			'post_status'			=> 'publish',
			'no_found_rows'			=> 1,
			'posts_per_page' 		=> $atts['per_page'],
			'orderby' 				=> $atts['orderby'],
			'order' 				=> $atts['order'],
			'meta_query' 			=> array()
		);
		
		if ( !absint($atts['show_hidden']) ) {
			$args['meta_query'][] = WC()->query->visibility_meta_query();
			$args['post_parent']  = 0;
		}
		
		if ( absint( $atts['hide_free'] ) ) {
			$args['meta_query'][] = array(
				'key'     => '_price',
				'value'   => 0,
				'compare' => '>',
				'type'    => 'DECIMAL',
			);
		}
		
		ob_start();
		
		$products = new WP_Query( apply_filters( 'woocommerce_shortcode_products_query', $args, $atts ) );
		
		$old_columns = $woocommerce_loop['columns'];
		
		if ( $products->have_posts() ) :

			if( absint($atts['as_widget']) )
				self::get_template( 'shortcode-woo-widget.tpl.php', $atts, $products );
			else {
				if( absint($atts['is_biggest']) ) self::get_template( 'shortcode-woo-big.tpl.php', $atts, $products );
				else self::get_template( 'shortcode-woo-nomal.tpl.php', $atts, $products );
			}
			
		endif;
		
		wp_reset_postdata();
		
		$woocommerce_loop['columns'] = $old_columns;
		
		$classes = self::pareShortcodeClass( 'columns-' . absint( $atts['columns'] ) );
		
		return '<div class="'.esc_attr( implode( ' ', $classes ) ).'">' . ob_get_clean() . '</div>';
	}
	
	public static function top_rated_products( $atts ){
		global $woocommerce_loop;
		
		$atts = shortcode_atts(array(
			"title" 		=> '',
			"item_style"	=> 'grid',
			"as_widget"		=> '0',
			"box_style"		=> '',
			"head_style"	=> '',
			"is_slider"		=> '1',
			"is_biggest"	=> 0,
			"auto_play"		=> '0',
			"excerpt_limit" => 10,
			"per_page"		=> '12',
			"columns"		=> '4',
			"orderby"		=> 'date',
			"order"			=> 'desc',
		),$atts);
		
		$meta_query = WC()->query->get_meta_query();
		
		$args = array(
			'post_type'           => 'product',
			'post_status'         => 'publish',
			'ignore_sticky_posts' => 1,
			'orderby'             => $atts['orderby'],
			'order'               => $atts['order'],
			'posts_per_page'      => $atts['per_page'],
			'meta_query'          => $meta_query
		);
		
		ob_start();
		
		add_filter( 'posts_clauses', array( __CLASS__, 'order_by_rating_post_clauses' ) );
		
		$products = new WP_Query( apply_filters( 'woocommerce_shortcode_products_query', $args, $atts ) );
		
		remove_filter( 'posts_clauses', array( __CLASS__, 'order_by_rating_post_clauses' ) );
		
		$old_columns = $woocommerce_loop['columns'];
		
		if ( $products->have_posts() ) :

			if( absint($atts['as_widget']) )
				self::get_template( 'shortcode-woo-widget.tpl.php', $atts, $products );
			else {
				if( absint($atts['is_biggest']) ) self::get_template( 'shortcode-woo-big.tpl.php', $atts, $products );
				else self::get_template( 'shortcode-woo-nomal.tpl.php', $atts, $products );
			}
			
		endif;
		
		wp_reset_postdata();
		
		$woocommerce_loop['columns'] = $old_columns;
		
		$classes = self::pareShortcodeClass( 'columns-' . absint( $atts['columns'] ) );
		
		return '<div class="'.esc_attr( implode( ' ', $classes ) ).'">' . ob_get_clean() . '</div>';
	}
	
	public static function best_selling_products( $atts ){
		global $woocommerce_loop;
		$atts = shortcode_atts(array(
			"title" 		=> '',
			"item_style"	=> 'grid',
			"as_widget"		=> '0',
			"box_style"		=> '',
			"head_style"	=> '',
			"is_slider"		=> '1',
			"is_biggest"	=> 0,
			"auto_play"		=> '0',
			"excerpt_limit" => 10,
			"per_page"		=> '12',
			"columns"		=> '4',
		),$atts);
		
		$meta_query = WC()->query->get_meta_query();
		
		$args = array(
			'post_type'           => 'product',
			'post_status'         => 'publish',
			'ignore_sticky_posts' => 1,
			'posts_per_page'      => $atts['per_page'],
			'meta_key'            => 'total_sales',
			'orderby'             => 'meta_value_num',
			'meta_query'          => $meta_query
		);
		
		ob_start();
		
		$products = new WP_Query( apply_filters( 'woocommerce_shortcode_products_query', $args, $atts ) );
		
		$old_columns = $woocommerce_loop['columns'];
		
		if ( $products->have_posts() ) :

			if( absint($atts['as_widget']) )
				self::get_template( 'shortcode-woo-widget.tpl.php', $atts, $products );
			else {
				if( absint($atts['is_biggest']) ) self::get_template( 'shortcode-woo-big.tpl.php', $atts, $products );
				else self::get_template( 'shortcode-woo-nomal.tpl.php', $atts, $products );
			}
			
		endif;
		
		wp_reset_postdata();
		
		$woocommerce_loop['columns'] = $old_columns;
		
		$classes = self::pareShortcodeClass( 'columns-' . absint( $atts['columns'] ) );
		
		return '<div class="'.esc_attr( implode( ' ', $classes ) ).'">' . ob_get_clean() . '</div>';
	}
	
	public static function product_tags( $atts ){
		$atts = shortcode_atts( array(
			'title'		=> '',
			'tags' 		=> '',
			't_color'	=> '#333333'
		), $atts );
		
		if( strlen( trim( $atts['tags'] ) ) == 0 ) return;
		
		$tags = explode( ',', $atts['tags'] );
		
		ob_start();
		
		?>
		<div class="nth-shortcode nth-product-taxs-wrapper nth-product-categories-wrapper">
			<?php if( strlen( $atts['title'] ) > 0 ): ?>
			<span style="color: <?php echo esc_attr( $atts['t_color'] );?>"><?php echo esc_attr( $atts['title'] );?></span>:
			<?php endif;?>
			
			<?php foreach( $tags as $tag ): ?>
				
				<?php if($term_tag = get_term_by( 'slug', $tag, "product_tag" )): ?>
				
					<a href="<?php echo esc_url( get_term_link( $term_tag->term_id, "product_tag" ) );?>"><?php echo esc_attr( $term_tag->name );?></a>
				
				<?php endif;?>
			
			<?php endforeach;?>
			
		</div><!-- .nth-product-categories-wrapper -->
		
		<?php 
		
		wp_reset_postdata();
		
		return ob_get_clean();
	}
	
	public static function product_cats( $atts ){
		$atts = shortcode_atts( array(
			'title'			=> '',
			'cats' 			=> '',
			'hover_color'	=> '#333333'
		), $atts );
		
		if( strlen( trim( $atts['cats'] ) ) == 0 ) return;
		
		$cats = explode( ',', $atts['cats'] );
		
		$args = array(
			'slug' => $cats,
			'orderby'	=> 'name',
			'order'		=> 'ASC',
			'hide_empty'	=> false,
			'cache_domain'	=> 'theshopier_woo'
		);
		
		ob_start();
		
		$terms = get_terms('product_cat', $args);
		
		if( !empty($terms) ):
			$customcss_class = 'theshopier_shortcodeclass_' . rand();

			?>
			<style type="text/css" scoped>
				.<?php echo $customcss_class?> li a:hover {
					color: <?php echo $atts['hover_color'];?>;
					font-weight: bold;
				}
			</style>
			<?php

			echo '<ul class="product-cats '.$customcss_class.'">';
			foreach( $terms as $term ) {
				
				$link = get_term_link($term);
				printf( '<li class="cat-%1$d"><a href="%2$s" title="%3$s">%3$s</a></li>', absint($term->term_id), esc_url($link), esc_attr($term->name) );
				
			}
			echo "</ul>";
			
		endif;
		
		wp_reset_postdata();
		
		$classes = self::pareShortcodeClass( __FUNCTION__ );
		
		return '<div class="'.esc_attr( implode( ' ', $classes ) ).'">' . ob_get_clean() . '</div>';
	}

	public static function profile_meta( $atts ){
		global $theshopier_datas;
		ob_start();
		if(is_user_logged_in()) {
			global $current_user;
			$current_user = wp_get_current_user();
			$wl_id = get_option( 'yith_wcwl_wishlist_page_id', false );
			$shop_myaccount_id = get_option( 'woocommerce_myaccount_page_id', false );
			?>
			<div class="nth-row-grid">
				<div class="col-sm-24">
					<div class="gavatar-box">
						<?php echo get_avatar($current_user->user_email, 70, '', null, array('class'=> 'img-circle'));?>
						<div class="meta">
							<h3><?php echo $current_user->display_name;?></h3>
							<a href="<?php echo wp_logout_url();?>"><?php _e("Logout", 'nexthemes-plugin');?></a>
						</div>
					</div>
					<ul>
						<?php if($shop_myaccount_id):?>
							<li><a href="<?php echo esc_url(get_permalink($shop_myaccount_id));?>"><?php _e("My Order", 'nexthemes-plugin')?></a></li>
						<?php endif;?>
						<?php if($wl_id):?>
							<li><a href="<?php echo esc_url(get_permalink($wl_id));?>"><?php _e("My Wishlist", 'nexthemes-plugin')?></a></li>
						<?php endif;?>
						<?php if( !empty($theshopier_datas['nth-my-account-track-order']) ):?>
							<li><a href="<?php echo get_permalink($theshopier_datas['nth-my-account-track-order']);?>"><?php _e("Track my order", 'nexthemes-plugin') ?></a></li>
						<?php endif;?>
						<li><a href="<?php echo wc_customer_edit_account_url();?>"><?php _e("Custom Account page", 'nexthemes-plugin');?></a></li>
						<li><a href="<?php echo wp_logout_url();?>"><?php _e("Log Out", 'nexthemes-plugin');?></a></li>
					</ul>
				</div>
			</div>

			<?php
		}

		return ob_get_clean();

	}

	public static function featured_products( $atts ){
		global $woocommerce_loop;
		$atts = shortcode_atts( array(
			"title" 		=> '',
			"item_style"	=> 'grid',
			"as_widget"		=> '0',
			"box_style"		=> '',
			"head_style"	=> '',
			"is_slider"		=> '1',
			"is_biggest"	=> '0',
			"auto_play"		=> '0',
			"excerpt_limit" => 10,
			"per_page"		=> '12',
			"columns"		=> '4',
			"orderby"		=> 'date',
			"order"			=> 'desc',
			"hide_free"		=> 0,
			"show_hidden"	=> 0
		), $atts );

		$classes = self::pareShortcodeClass( 'columns-' . absint( $atts['columns'] ) );

		$cache = self::get_cached_shortcode($atts, 'theshopier_'. __FUNCTION__ );

		if( !is_array($cache) && strlen(trim($cache)) > 0 ) {
			return '<div class="cached_shortcode '.esc_attr( implode( ' ', $classes ) ).'">' . $cache . '</div>';
		}
		
		$meta_query   = array();
		
		$args = array(
			'post_type'           => 'product',
			'post_status'         => 'publish',
			'ignore_sticky_posts' => 1,
			'posts_per_page'      => $atts['per_page'],
			'orderby'             => $atts['orderby'],
			'order'               => $atts['order'],
			'meta_query'          => $meta_query
		);

		$args['meta_query'] = WC()->query->get_meta_query();

		$args['meta_query'][] = array(
			'key'   => '_featured',
			'value' => 'yes'
		);

		if( absint($atts['as_widget']) ) {
			if ( !absint($atts['show_hidden']) ) {
				$args['meta_query'][] = WC()->query->visibility_meta_query();
				$args['post_parent']  = 0;
			}
			
			if ( absint( $atts['hide_free'] ) ) {
				$args['meta_query'][] = array(
					'key'     => '_price',
					'value'   => 0,
					'compare' => '>',
					'type'    => 'DECIMAL',
				);
			}
		}

		ob_start();
		
		$products = new WP_Query( apply_filters( 'woocommerce_shortcode_products_query', $args, $atts ) );
		
		$old_columns = $woocommerce_loop['columns'];
		
		if ( $products->have_posts() ) :

			if( absint($atts['as_widget']) )
				self::get_template( 'shortcode-woo-widget.tpl.php', $atts, $products );
			else {

				if( absint($atts['is_biggest']) ) self::get_template( 'shortcode-woo-big.tpl.php', $atts, $products );
				else self::get_template( 'shortcode-woo-nomal.tpl.php', $atts, $products );
			}
			
		endif;
		
		wp_reset_postdata();
		
		$woocommerce_loop['columns'] = $old_columns;

		$output = ob_get_clean();

		$output = self::cache_shortcode($atts, 'theshopier_'. __FUNCTION__, $output, $cache);
		
		return '<div class="'.esc_attr( implode( ' ', $classes ) ).'">' . $output . '</div>';
	}
	
	public static function products_categories_tabs( $atts ){
		$atts = shortcode_atts( array(
			"title" 		=> '',
			"h_style"		=> '',
			"tabs_style"	=> '',
			"item_style"	=> 'grid',
			"as_widget"		=> '0',
			"box_style"		=> '',
			"head_style"	=> '',
			"excerpt_limit"	=> 10,
			"per_page"		=> '12',
			"columns"		=> '4',
			"orderby"		=> 'date',
			"order"			=> 'desc',
			"category" 		=> '',
			"use_ajax"		=> '1'
		), $atts );
		
		if ( ! $atts['category'] ) return '';
		
		ob_start();
		
		$category = explode( ',', $atts['category'] );
		
		if( count($category) > 0 ):
		
			$tab_rand = 'tab_item_' . mt_rand();
			$tab_rand2 = 'tab_' . mt_rand(); ?>
			
			<div class="nth_products_categories_shortcode <?php echo esc_attr($atts['tabs_style'])?>" data-atts="<?php echo esc_attr( json_encode($atts) );?>" id="<?php echo esc_attr($tab_rand2);?>">
				
				<?php
				$_tabs_content = '';
				$i = 0;
				$first_term = '';
				foreach( $category as $slug ) {
					$term = get_term_by( "slug", $slug, "product_cat" );
					if( $term ) {
						$_tabs_content .= '<div class="tab-content-item'.($i !== 0? ' hidden': ' show').'" id="'.esc_attr($tab_rand . '_' . $term->term_id).'">';
						$_tabs_content .= self::get_product_by_cat_content( $atts, $term->slug );
						$_tabs_content .= '</div>';
						if( $i == 0 ) $first_term = $term;
						$i++;
					}
				}

				self::get_product_by_cat_tabs($atts, $tab_rand);
				?>
				
				<div class="nth-shortcode-content">
					<?php if(absint($atts['use_ajax']) == 0): ?>
					<?php echo $_tabs_content;?>
					<?php else: ?>
					<div class="tab-content-item show ajax-content" id="<?php echo esc_attr($tab_rand) . '_ajax_content'?>">
						<?php echo self::get_product_by_cat_content( $atts, $first_term->slug );?>
					</div>
					<?php endif;?>
				</div>
			
			</div>
			
		<?php 
		$classes = self::pareShortcodeClass( 'nth-woo-none-cat columns-' . absint( $atts['columns'] ) );
		
		return '<div class="'.esc_attr( implode( ' ', $classes ) ).'">' . ob_get_clean() . '</div>';?>
		
		<?php endif;
	}

	public static function product_subcaterories( $atts ){
		$atts = shortcode_atts( array(
			'title'		=> '',
			'box_style' => '',
			'h_style'	=> '',
			'cat_group'	=> '',
			'orderby'	=> 'date',
			'order'		=> 'desc',
			'columns'		=> 3,
			'is_slider'		=> 1,
			'per_page'		=> 3
		), $atts );

		if(strlen($atts['cat_group']) === 0) return '';
		$cats_ops = vc_param_group_parse_atts($atts['cat_group']);

		$options = array(
			'items'	=> $atts['columns']
		);
		$options = NexThemes_Plg::get_owlResponsive($options);

		ob_start();

		if(strlen($atts['title']) > 0) {
			$head_class = array('heading-title');
			if(strlen($atts['h_style']) > 0) $head_class[] = esc_attr($atts['h_style']);
			echo '<div class="nth-shortcode-header"><h3 class="'.esc_attr(implode(' ', $head_class)).'">'.esc_attr($atts['title']).'</h3></div>';
		}

		echo '<div class="row">';

		if( absint($atts['is_slider']) == 1 )
			echo '<div class="nth-owlCarousel loading" data-options="'.esc_attr(json_encode($options)).'" data-base="1">';

		foreach( $cats_ops as $ops ){
			$atts['ops'] = $ops;
			self::get_template('shortcode-product-subcaterories.tpl.php', $atts);
		}

		if( absint($atts['is_slider']) == 1 ) echo '</div>';

		echo '</div>';

		$classes = self::pareShortcodeClass( __FUNCTION__ );
		if(strlen($atts['box_style']) > 0) $classes[''] = esc_attr($atts['box_style']);
		return '<div class="'.esc_attr( implode( ' ', $classes ) ).'">' . ob_get_clean() . '</div>';
	}

	public static function product_brands( $atts ){
		$atts = shortcode_atts( array(
			'title'			=> '',
			'brand_group'	=> '',
			'orderby'		=> 'date',
			'order'			=> 'desc',
			'columns'		=> 4,
			'is_slider'		=> 1,
			'per_page'		=> 4
		), $atts );

		if(strlen($atts['brand_group']) === 0) return '';
		$brand_ops = vc_param_group_parse_atts($atts['brand_group']);

		$options = array(
			'items'	=> $atts['columns'],
			"responsive"	=> array(
				0	=> array(
					'items'	=> 1,
					'loop'	=> true
				),
				480	=> array(
					'items'	=> 2,
					'loop'	=> true
				)
			),
		);
		$options = NexThemes_Plg::get_owlResponsive($options);

		ob_start();

		if(strlen($atts['title']) > 0)
			echo '<div class="nth-shortcode-header"><h3 class="heading-title ud-line">'.esc_attr($atts['title']).'</h3></div>';

		echo '<div class="row">';
		if( absint($atts['is_slider']) == 1 )
			echo '<div class="nth-owlCarousel loading" data-options="'.esc_attr(json_encode($options)).'" data-base="1">';

		foreach( $brand_ops as $ops ){
			$atts['ops'] = $ops;
			self::get_template('shortcode-product-brand.tpl.php', $atts);
		}
		if( absint($atts['is_slider']) == 1 ) echo '</div>';

		echo '</div>';

		$classes = self::pareShortcodeClass( __FUNCTION__ );
		$before = '<div class="'.esc_attr(implode(' ', $classes)).'">';
		$after = '</div>';


		return $before . ob_get_clean() . $after;
	}

	public static function featured_prod_cats($atts){
		global $woocommerce_loop;
		$atts = shortcode_atts( array(
			'title'			=> '',
			'h_style'		=> '',
			'box_style'		=> '',
			'slugs'			=> '',
			'columns'		=> 4,
			'style'			=> '',
			'is_slider'		=> 1,
			'hide_empty'	=> 1,
			'cat_fills'		=> ''
		), $atts );

		if ( isset( $atts['slugs'] ) ) {
			$slugs = explode( ',', $atts['slugs'] );
			$slugs = array_map( 'trim', $slugs );
		} else {
			$slugs = array();
		}

		$hide_empty = ( $atts['hide_empty'] == true || $atts['hide_empty'] == 1 ) ? 1 : 0;

		// get terms and workaround WP bug with parents/pad counts
		$args = array(
			'hide_empty' => $hide_empty,
			'slug'    	=> $slugs,
			'pad_counts' => true,
			'orderby'	=> 'none'
		);

		$_term_arg = get_terms( 'product_cat', $args );

		$old_columns = $woocommerce_loop['columns'];

		$product_categories = array();
		foreach($slugs as $slug){
			foreach($_term_arg as $term){
				if($term->slug == $slug) {
					$product_categories[] = $term;
					continue;
				}
			}
		}

		ob_start();

		if ($product_categories) :

			if(strlen($atts['title']) > 0) {
				$heading_class = array('heading-title');
				if(strlen($atts['h_style']) > 0) $heading_class[] = esc_attr($atts['h_style']);
				echo '<div class="nth-shortcode-header"><h3 class="'.esc_attr(implode(' ', $heading_class)).'">'.esc_attr($atts['title']).'</h3></div>';
			}

			$atts['product_categories'] = $product_categories;

			self::get_template( 'shortcode-woo-cats.tpl.php', $atts );

		endif;

		wp_reset_postdata();

		$woocommerce_loop['columns'] = $old_columns;

		$classes = self::pareShortcodeClass( 'columns-' . absint( $atts['columns'] ) );
		if( strlen($atts['box_style']) > 0 ) $classes[] = esc_attr($atts['box_style']);
		if( strlen($atts['style']) > 0 ) $classes[] = esc_attr($atts['style']);

		return '<div class="'.esc_attr(implode(' ', $classes)).'">' . ob_get_clean() . '</div>';
	}

	public static function woo_single_cat($atts){
		$atts = shortcode_atts(array(
			'title'			=> '',
			'style'			=> '1',
			'slug'			=> '',
			'hide_empty'	=> 0
		), $atts);

		// get terms and workaround WP bug with parents/pad counts
		if( strlen($atts['slug']) == 0 ) return;

		$product_cat = get_term_by( 'slug', $atts['slug'], 'product_cat' );

		if(empty($product_cat)) {
			return;
		}

		ob_start();

		echo '<div class="woo-single-cat-inner">';

		echo '<h3 class="text-uppercase">'.esc_html($product_cat->name).'</h3>';

		echo '<p class="cat-count">'. sprintf( _n( '%s Item', '%s Items', absint($product_cat->count), 'nexthemes-plugin' ), absint($product_cat->count) ) .'</p>';

		echo '<a href="'.esc_url(get_term_link($product_cat)).'" title="'.__('shop now') .'" class="button medium outline">'. __('shop now') .'</a>';

		echo '</div>';

		$classes = self::pareShortcodeClass();

		$classes[] = "woo-single-cat style-{$atts['style']}";

		return '<div class="'.esc_attr(implode(' ', $classes)).'">' . ob_get_clean() . '</div>';
	}

	public static function get_product_by_cat_tabs( $atts, $tab_rand ){
		$_tabs_li = '';
		$i = 0;

		$category = explode( ',', $atts['category'] );
		foreach( $category as $slug ) {
			$term = get_term_by( "slug", $slug, "product_cat" );
			if( $term ) {
				$_class = array();
				$_ul_class = array('shortcode-woo-tabs', 'clearfix', 'hidden-xs');
				$_class[] = absint( $atts['use_ajax'] ) == 0? 'tab-item': 'tab-item-ajax';
				if($i == 0) $_class[] = 'active';
				if( strcmp('style-2', trim($atts['tabs_style'])) == 0 ) {
					$__columns = count($category);
					/*$_class[] = 'col-lg-' . round( 24 /$__columns );
					$_class[] = 'col-md-' . round( 24 / round( $__columns * 992 / 1200) );
					$_class[] = 'col-sm-' . round( 24 / round( $__columns * 768 / 1200) );
					$_class[] = 'col-xs-' . round( 24 / round( $__columns * 480 / 1200) );
					$_class[] = 'col-mb-12';*/
                    $_class[] = 'col-lg-' . round( 24 /$__columns );
                    $_class[] = 'col-md-' . round( 24 /$__columns );
                    $_class[] = 'col-sm-' . round( 24 /$__columns );
                    $_class[] = 'col-xs-' . round( 24 /$__columns );
                    $_class[] = 'col-mb-12';

					$_ul_class[] = '';
				}

				$_tabs_li .= '<li class="'.esc_attr(implode(' ', $_class)).'">';
				$_tabs_li .= '<a title="'.$term->name.'" href="javascript:void(0);" data-id="'.esc_attr( $tab_rand . '_' . $term->term_id ).'" data-slug="'.esc_attr($term->slug).'">';

				switch( $atts['tabs_style'] ) {
					case 'style-2':
						$small_thumbnail_size  	= 'theshopier_shop_subcat';
						$thumbnail_id  			= get_woocommerce_term_meta( $term->term_id, 'thumbnail_id', true  );

						if ( $thumbnail_id ) {
							$image = wp_get_attachment_image_src( $thumbnail_id, $small_thumbnail_size  );
						} else {
							$image = array(wc_placeholder_img_src(), 100, 100);
						}

						if ( $image ) {
							$image_src = str_replace( ' ', '%20', $image[0] );
							$_tabs_li .= '<img src="' . esc_url( $image_src ) . '" alt="' . esc_attr( $term->name ) . '" width="' . esc_attr( $image[1] ) . '" height="' . esc_attr( $image[2] ) . '" />';
							$_tabs_li .= esc_attr($term->name);
						}
						break;
					default:
						$_tabs_li .= esc_attr($term->name);
				}

				$_tabs_li .= '</a>';
				$_tabs_li .= "</li>";
				$i++;
			}
		}
		?>
		<div class="nth-shortcode-header clearfix">
			<?php if( strlen( $atts['title'] ) > 0 ):
				$heading_class = array('heading-title');
				if( strlen($atts['h_style']) > 0 ) $heading_class[] = esc_attr($atts['h_style']);
				?>
				<h3 class="<?php echo esc_attr(implode(' ', $heading_class))?>"><?php echo esc_attr($atts['title']);?></h3>
			<?php endif;?>

			<ul class="<?php echo esc_attr(implode(' ', $_ul_class));?>">
				<?php echo $_tabs_li;?>
			</ul>
		</div>
		<?php
	}
	
	public static function get_product_by_cat_content( $atts, $slug, $slide_id = '' ){
		global $woocommerce_loop;
		
		$ordering_args = WC()->query->get_catalog_ordering_args( $atts['orderby'], $atts['order'] );
		$meta_query    = WC()->query->get_meta_query();
		
		$args = array(
			'post_type'				=> 'product',
			'post_status' 			=> 'publish',
			'ignore_sticky_posts'	=> 1,
			'orderby' 				=> $ordering_args['orderby'],
			'order' 				=> $ordering_args['order'],
			'posts_per_page' 		=> $atts['per_page'],
			'meta_query' 			=> $meta_query,
			'tax_query' 			=> array(
				array(
					'taxonomy' 		=> 'product_cat',
					'terms' 		=> array( $slug ),
					'field' 		=> 'slug',
					'operator' 		=> 'IN'
				)
			)
		);
		
		if ( isset( $ordering_args['meta_key'] ) ) {
			$args['meta_key'] = $ordering_args['meta_key'];
		}
		
		$products = new WP_Query( apply_filters( 'woocommerce_shortcode_products_query', $args, $atts ) );
		
		$old_columns = $woocommerce_loop['columns'];
		
		ob_start();
		
		$atts['title'] = '';
		$atts['is_slider']	= 1;
		
		if ( $products->have_posts() ) :

			if( absint($atts['as_widget']) )
				self::get_template( 'shortcode-woo-widget.tpl.php', $atts, $products );
			else {
				self::get_template( 'shortcode-woo-nomal.tpl.php', $atts, $products );
			}
		
		endif;
		
		wp_reset_postdata();
		
		$woocommerce_loop['columns'] = $old_columns;
		
		$classes = self::pareShortcodeClass( 'columns-' . absint( $atts['columns'] ) );
		
		return '<div class="'.esc_attr( implode( ' ', $classes ) ).'">' . ob_get_clean() . '</div>';
	}

	public static function order_by_rating_post_clauses( $args ){
		global $wpdb;
		
		$args['where'] .= " AND $wpdb->commentmeta.meta_key = 'rating' ";
		
		$args['join'] .= "
			LEFT JOIN $wpdb->comments ON($wpdb->posts.ID = $wpdb->comments.comment_post_ID)
			LEFT JOIN $wpdb->commentmeta ON($wpdb->comments.comment_ID = $wpdb->commentmeta.comment_id)
		";
		
		$args['orderby'] = "$wpdb->commentmeta.meta_value DESC";

		$args['groupby'] = "$wpdb->posts.ID";

		return $args;
	}
	
	private static function pareShortcodeClass( $class = '' ){
		$classes = self::$classes;
		if( strlen($class) > 0 )
			$classes[] = $class;
		return $classes;
	}
	
	public static function woo_get_product_by_cat(){
		
		$atts = array(); $sub_cat_slug = ''; $element = '';
			
		if( isset($_POST['atts']) ){
			$atts = $_POST['atts'];
		}
		
		if( isset($_POST['cat_slug']) ){
			$cat_slug = $_POST['cat_slug'];
		}
		
		if( isset($_POST['element']) ){
			$element = $_POST['element'];
		}
		
		$atts = shortcode_atts(array(
			"title" 		=> '',
			"item_style"	=> 'grid',
			"as_widget"		=> '0',
			"box_style"		=> '',
			"head_style"	=> '',
			"per_page"		=> '12',
			"excerpt_limit"	=> 10,
			"columns"		=> '4',
			"orderby"		=> 'date',
			"order"			=> 'desc',
			"category" 		=> '',
			"use_ajax"		=> '1'
		),$atts);
		
		global $woocommerce_loop;
	
		$ordering_args = WC()->query->get_catalog_ordering_args( $atts['orderby'], $atts['order'] );
		$meta_query    = WC()->query->get_meta_query();
		
		$args = array(
			'post_type'				=> 'product',
			'post_status' 			=> 'publish',
			'ignore_sticky_posts'	=> 1,
			'orderby' 				=> $ordering_args['orderby'],
			'order' 				=> $ordering_args['order'],
			'posts_per_page' 		=> $atts['per_page'],
			'meta_query' 			=> $meta_query,
			'tax_query' 			=> array(
				array(
					'taxonomy' 		=> 'product_cat',
					'terms' 		=> array( $cat_slug ),
					'field' 		=> 'slug',
					'operator' 		=> 'IN'
				)
			)
		);
		
		if ( isset( $ordering_args['meta_key'] ) ) {
			$args['meta_key'] = $ordering_args['meta_key'];
		}
		
		$products = new WP_Query( $args );
		
		$old_columns = $woocommerce_loop['columns'];
		
		ob_start();
		
		$atts['title'] = '';
		$atts['is_slider']	= 1;
		
		if ( $products->have_posts() ) :

			if( absint($atts['as_widget']) )
				self::get_template( 'shortcode-woo-widget.tpl.php', $atts, $products );
			else {
				self::get_template( 'shortcode-woo-nomal.tpl.php', $atts, $products );
			}
		
		endif;
		
		wp_reset_postdata();
		
		$woocommerce_loop['columns'] = $old_columns;
		
		$classes = self::pareShortcodeClass( 'columns-' . absint( $atts['columns'] ) );
		
		echo '<div class="'.esc_attr( implode( ' ', $classes ) ).'">' . ob_get_clean() . '</div>';
		
		die();
	}

	public static function woocommerce_recently_viewed( $atts ){
		global $woocommerce_loop;
		$atts = shortcode_atts(array(
			'title'			=> '',
			'limit'			=> 1,
			'item_style'	=> 'grid',
			'columns'		=> 4
		), $atts);
		$viewed_products = ! empty( $_COOKIE['woocommerce_recently_viewed'] ) ? (array) explode( '|', $_COOKIE['woocommerce_recently_viewed'] ) : array();
		$viewed_products = array_filter( array_map( 'absint', $viewed_products ) );
		if ( empty( $viewed_products ) ) {
			return;
		}

		$query_args = array(
			'posts_per_page' => $atts['limit'],
			'no_found_rows' => 1,
			'post_status' => 'publish',
			'post_type' => 'product',
			'post__in' => $viewed_products,
			'orderby' => 'rand'
		);

		$query_args['meta_query']   = array();
		$query_args['meta_query'][] = WC()->query->stock_status_meta_query();
		$query_args['meta_query']   = array_filter( $query_args['meta_query'] );

		ob_start();

		$products = new WP_Query( $query_args );

		$old_columns = $woocommerce_loop['columns'];

		if ( $products->have_posts() ) :

			self::get_template( 'shortcode-woo-nomal.tpl.php', $atts, $products );

		endif;

		wp_reset_postdata();

		$woocommerce_loop['columns'] = $old_columns;

		$classes = self::pareShortcodeClass( 'columns-' . absint( $atts['columns'] ) );

		return '<div class="'.esc_attr( implode( ' ', $classes ) ).'">' . ob_get_clean() . '</div>';
	}

	public static function woo_attributes( $atts ){
		$atts = shortcode_atts(array(
			'title'			=> '',
			'limit'			=> 15,
			'attribute'		=> '',
			'columns'		=> 6
		), $atts);


		if(empty($atts['attribute'])) return;
		$taxonomy = wc_attribute_taxonomy_name($atts['attribute']);
		if ( ! taxonomy_exists( $taxonomy ) ) return;
		$get_terms_args = array(
			'hide_empty' => '0',
			'number'		=> $atts['limit'],
			'orderby'		=> 'name',
			'menu_order'	=> false
		);
		$terms = get_terms( $taxonomy, $get_terms_args );
		$output = '';
		if(count( $terms ) > 0) {
			ob_start();
			$li_class = array('woo-attr-item text-center');
			$li_class[] = 'col-lg-' . round( 24 /$atts['columns'] );
			$li_class[] = 'col-md-' . round( 24 / round( $atts['columns'] * 992 / 1200) );
			$li_class[] = 'col-sm-' . round( 24 / round( $atts['columns'] * 768 / 1200) );
			$li_class[] = 'col-xs-' . round( 24 / round( $atts['columns'] * 480 / 1200) );
			echo '<ul class="list-inline">';
			foreach ( $terms as $term ) {
				$link = get_post_type_archive_link( 'product' );
				$link = add_query_arg( 'filter_' . esc_attr($atts['attribute']), $term->term_id, $link );

				echo '<li class="'.esc_attr(implode(' ', $li_class)).'">';
				printf('<a href="%1$s" title="%2$s">%2$s</a>', $link, $term->name);
				echo '</li>';
			}
			echo '</ul>';
			$output = ob_get_clean();
		}

		return $output;

	}

	public static function woo_categories( $atts ){
		$atts = shortcode_atts(array(
			'title'			=> '',
			'box_style'		=> '',
			'h_style'		=> '',
			'cats_group'	=> '',
			'button_txt'	=> 'Show now',
			'shop_all_text'	=> ''
		), $atts);

		ob_start();

		self::get_template("shortcode-woo-cats2.tpl.php", $atts);

		$classes = self::pareShortcodeClass( __FUNCTION__ );
		if( strlen($atts['box_style']) > 0 ) $classes[] = esc_attr($atts['box_style']);

		return '<div class="'.esc_attr(implode(' ', $classes)).'">' . ob_get_clean() . '</div>';
	}

	public static function get_cached_shortcode( $atts, $key ){
		$cache_id = md5(serialize($atts));
		$cache = wp_cache_get( $key, 'shortcode');
		if ( ! is_array( $cache ) ) $cache = array();
		if ( isset( $cache[$cache_id] ) )
			return $cache[ $cache_id ];
		else return $cache;
	}

	public static function cache_shortcode( $atts, $key, $content, $cache ) {
		$cache_id = md5(serialize($atts));
		$cache[$cache_id] = $content;
		wp_cache_set( $key, $cache, 'shortcode' );
		return $content;
	}
}

endif;