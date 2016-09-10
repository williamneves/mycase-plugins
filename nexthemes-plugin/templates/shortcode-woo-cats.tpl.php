<?php 

global $woocommerce_loop;

$woocommerce_loop['columns'] = $columns;

$fills = array_map('trim', explode(',', $cat_fills));

$heading_start = '<div class="nth-shortcode-header"><h3 class="heading-title ud-line">';
$heading_end = '</h3></div>';

$item_class = array('feature-cat');

$item_class[] = esc_attr($style);
if( isset( $is_slider ) && absint( $is_slider ) ) {
	$item_class[] = 'col-sm-24';
	if( absint($columns) > 1 )
		$options = array(
			"items"			=> $columns,
			"responsive"	=> array(
				0	=> array(
					'items'	=> 2,
					'loop'	=> false
				)
			),
			"autoPlay"		=> isset($auto_play) && absint($auto_play)? true: false,
		);
	else {
		$options = array(
			"items"			=> 3,
			"autoPlay"		=> isset($auto_play) && absint($auto_play)? true: false,
		);
	}
	$options = NexThemes_Plg::get_owlResponsive($options);
	printf('<div class="nth-woo-shortcode nth-owlCarousel loading" data-options="%1$s" data-slider="%2$s" data-base="1">', esc_attr(json_encode($options)), '.feature-prod-cat-wrapper');
} else {
	echo '<div class="nth-woo-shortcode">';

	$item_class[] = 'col-lg-' . round( 24 /$columns );
	$item_class[] = 'col-md-' . round( 24 / round( $columns * 992 / 1200) );
	$item_class[] = 'col-sm-' . round( 24 / round( $columns * 768 / 1200) );
	$item_class[] = 'col-xs-' . round( 24 / round( $columns * 480 / 1200) );
	$item_class[] = 'col-mb-24';
}
?>

<div class="content-inner">
	<div class="row">
		<div class="feature-prod-cat-wrapper <?php echo esc_attr($style)?>">

			<?php foreach ( $product_categories as $category ){
				$small_thumbnail_size  	= 'shop_catalog';
				//$dimensions    			= wc_get_image_size( $small_thumbnail_size );
				$thumbnail_id  			= get_woocommerce_term_meta( $category->term_id, 'thumbnail_id', true  );

				if ( $thumbnail_id ) {
					$image = wp_get_attachment_image( $thumbnail_id, $small_thumbnail_size  );
				} else {
					$image = '<img alt="Placeholder" src="'.wc_placeholder_img_src().'"/>';
				}
				$before = ''; $after = '';
				$head_before = ''; $head_after = '';
				if( strcmp($style, 'inside-meta') == 0 ){
					$before .= sprintf('<a href="%s" title="%s">', esc_url( get_term_link( $category->slug, 'product_cat' ) ), esc_attr( $category->name ));
					$after .= '</a>';
				}elseif(in_array('link', $fills)) {
					$head_before .= sprintf('<a href="%s" title="%s">', esc_url( get_term_link( $category->slug, 'product_cat' ) ), esc_attr( $category->name ));
					$head_after .= '</a>';
				}
				?>
				<div class="<?php echo esc_attr(implode(' ', $item_class))?>">
					<div class="feature-cat-inner">
						<?php echo $before;?>

						<?php echo $head_before;?>
							<?php if($image):?>
								<div class="f-thumbnail">
									<?php echo $image;?>
								</div>
							<?php endif;?>
						<?php echo $head_after;?>

						<div class="f-meta text-center">
							<?php echo $head_before;?>
							<h3 class="text-uppercase"><?php echo esc_html($category->name)?></h3>
							<?php echo $head_after;?>
							<?php if( strcmp($style, 'inside-meta') !== 0 ):?>

								<?php if(!in_array('count', $fills)) : ?>
									<p><?php printf( _n('%s product', '%s products', absint($category->count), 'nexthemes-plugin'), absint($category->count))?></p>
								<?php endif;?>

								<?php if(!in_array('link', $fills)) : ?>
									<a href="<?php echo esc_url( get_term_link( $category->slug, 'product_cat' ) );?>" title="<?php echo esc_attr( $category->name ); ?>"><?php _e('Shop Now', 'nexthemes-plugin');?></a>
								<?php endif;?>

							<?php endif;?>
						</div>

						<?php echo $after;?>
					</div>
				</div>

			<?php } ?>

		</div>
	</div>
</div>

<?php

echo "</div><!--.nth-owlCarousel-->";

?>
