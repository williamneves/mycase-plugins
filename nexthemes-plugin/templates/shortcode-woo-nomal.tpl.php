<?php 
global $woocommerce_loop;

$woocommerce_loop['columns'] = $columns;


if(strlen(trim($head_style)) > 0) $head_style = ' '.$head_style;
$heading_start = '<div class="nth-shortcode-header"><h3 class="heading-title'.esc_attr($head_style).'">';
$heading_end = '</h3></div>';
$class1 = isset($box_style)? esc_attr($box_style): '';

if( isset($box_style) && strcmp('widget_boxed', trim($box_style)) == 0 ) {
	$heading_start = '<div class="widget-heading"><h3 class="widget-title">';
}

if( isset( $is_slider ) && absint( $is_slider ) ) {
	if( absint($columns) > 1 ) {
		$mb_column = isset($item_style) && strcmp($item_style, 'list') == 0? 1: 2;
		$options = array(
			"items"			=> $columns,
			"responsive"	=> array(
				0	=> array(
					'items'	=> $mb_column,
					'loop'	=> true
				)
			),
			"autoPlay"			=> isset($auto_play) && absint($auto_play)? true: false,
		);
	} else {
		$options = array(
			"items"			=> 3,
			"autoPlay"		=> isset($auto_play) && absint($auto_play)? true: false,
		);
	}
	$options = NexThemes_Plg::get_owlResponsive($options);
	printf('<div class="nth-woo-shortcode nth-owlCarousel loading %3$s" data-options="%1$s" data-slider="%2$s" data-base="1">', esc_attr(json_encode($options)), '.products', $class1);
} else {
	echo '<div class="nth-woo-shortcode '.esc_attr($class1).'">';
}
?>

	<?php if( strlen( $title ) > 0 ):?>

	<?php echo $heading_start . esc_attr($title) . $heading_end;?>

	<?php endif;?>

	<div class="content-inner">

		<div class="row">

			<?php if(isset($excerpt_limit)) {
				add_filter( 'theshopier_woocommerce_short_description_count', function($limit) use( $excerpt_limit ) { return $excerpt_limit;}, 10, 1 );
			}?>

			<?php wc_get_template( 'loop/loop-start.php', array("item_style" => $item_style) ); ?>

			<?php while ( $products->have_posts() ) : $products->the_post(); ?>

				<?php wc_get_template_part( 'content', 'product' ); ?>

			<?php endwhile; // end of the loop. ?>

			<?php woocommerce_product_loop_end(); ?>

			<?php if(isset($excerpt_limit)): ?>
				<?php remove_all_filters( 'theshopier_woocommerce_short_description_count', 10 );?>
			<?php endif;?>

		</div><!-- END .nth-woo-shortcode -->

	</div>

<?php echo "</div>";?>
