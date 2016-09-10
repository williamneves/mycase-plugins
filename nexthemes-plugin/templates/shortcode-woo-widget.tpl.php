<?php 
global $woocommerce_loop;

if( strcmp( $item_style, 'grid' ) == 0 ) {
	$woocommerce_loop['columns'] = 2;
} else {
	$woocommerce_loop['columns'] = 1;
}

$head_class = array('heading-title');
if(strlen(trim($head_style)) > 0) $head_class[] = esc_attr($head_style);
$heading_start = '<div class="nth-shortcode-header"><h3 class="'.esc_attr(implode(' ', $head_class)).'">';
$heading_end = '</h3></div>';
$class1 = esc_attr($box_style);

if( strcmp('widget_boxed', trim($box_style)) == 0 ) {
	$heading_start = '<div class="widget-heading"><h3 class="widget-title">';
}
?>
<div class="nth-woo-shortcode <?php echo esc_attr($class1);?>">

	<?php if( strlen( $title ) > 0 ):?>

		<?php echo $heading_start;?><?php echo esc_html($title);?><?php echo $heading_end;?>

	<?php endif?>

	<div class="content-inner">

		<?php echo apply_filters( 'woocommerce_before_widget_product_list', '<ul class="product_list_widget '.esc_attr($item_style).'">' );?>

		<?php while ( $products->have_posts() ) : $products->the_post(); ?>

			<?php wc_get_template( 'content-widget-product.php', array( 'show_rating' => true ) );?>

		<?php endwhile;?>

		<?php echo apply_filters( 'woocommerce_after_widget_product_list', '</ul>' );?>

	</div>

</div>