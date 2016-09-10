<?php 
global $woocommerce_loop;

$heading_start = '<div class="nth-shortcode-header"><h3 class="heading-title ud-line">';
$heading_end = '</h3></div>';
$class1 = esc_attr($box_style);

if( strcmp('widget_boxed', trim($box_style)) == 0 ) {
	$heading_start = '<div class="widget-heading"><h3 class="widget-title heading-title">';
}

echo '<div class="nth-woo-shortcode '.esc_attr($class1).'">';

?>

	<?php if( strlen( $title ) > 0 ):?>

	<?php echo $heading_start . esc_attr($title) . $heading_end;?>

	<?php endif;?>

	<div class="content-inner">

		<div class="row">

			<?php if(isset($excerpt_limit)) {
				add_filter( 'theshopier_woocommerce_short_description_count', function($limit) use( $excerpt_limit ) { return $excerpt_limit;}, 10, 1 );
			}?>

			<div class="big-product-wrapper">

			<?php
			$i = 0; $is_first = true;
			$post_count = isset($products->post_count)? $products->post_count: $per_page;

			while ( $products->have_posts() ) : $products->the_post();
				if( $i == 0 ) {
					$woocommerce_loop['columns'] = 1;
					echo '<div class="col-md-12 col-lg-10"><div class="row">';
						wc_get_template( 'loop/loop-start.php', array("item_style" => 'grid') );
						wc_get_template_part( 'content', 'product' );
						woocommerce_product_loop_end();
					echo '</div></div>';

				} else {
					if( $i == 1 ) {
						echo '<div class="col-md-12 col-lg-14 big-list-products"><div class="row">';
						wc_get_template( 'loop/loop-start.php', array("item_style" => $item_style) );
					}
					$woocommerce_loop['columns'] = $columns;
					add_filter('theshopier_shop_loop_res_classes', 'nexthemes_shop_loop_res_classes_big_item', 10, 2);
					wc_get_template_part( 'content', 'product' );
					remove_filter('theshopier_shop_loop_res_classes', 'nexthemes_shop_loop_res_classes_big_item', 10, 2);
					if( $i >= $post_count - 1 ) {
						woocommerce_product_loop_end();
						echo '</div></div>';
					}
				}

				$i++;
			endwhile;

			?>

			</div>

			<?php if(isset($excerpt_limit)): ?>
				<?php remove_all_filters( 'theshopier_woocommerce_short_description_count', 10 );?>
			<?php endif;?>

		</div><!-- END .nth-woo-shortcode -->

	</div>

<?php echo "</div>";?>

<?php

function nexthemes_shop_loop_res_classes_big_item( $resp, $__columns){
	$resp = array();
	$resp[] = 'col-lg-' . round( 24 / round( $__columns / 1.74 ) );
	$resp[] = 'col-md-' . round( 24 / round( ($__columns / 2) * 992 / 1200) );
	$resp[] = 'col-sm-' . round( 24 / round( $__columns * 768 / 1200) );
	return $resp;
}

?>
