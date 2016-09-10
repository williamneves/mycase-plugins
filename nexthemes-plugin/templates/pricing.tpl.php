<?php 
$ul_class = array('nth-pricing-ul list-unstyled');
if( strlen($label) > 0 ) $ul_class[] = esc_attr($label);

?>

<div class="nth-pricing-wrapper list-unstyled">
	<ul class="<?php echo esc_attr(implode(' ', $ul_class))?>">
		<?php if( strlen($label) > 0 ):?>
		<li class="pricing-label"><?php echo esc_attr($label_text);?></li>
		<?php endif;?>
		<li class="widget-heading">
			<h3 class="heading-title"><?php echo esc_attr( $title );?></h3>
		</li>
		<li class="prices">
			<span class="price_table">
				<?php 
				$prices = array_map('trim', explode('|', $price));
				if(count($prices) == 2) array_unshift( $prices, "" );
				?>
				<sup class="currency_symbol"><?php echo esc_attr( $prices[0] );?></sup>
				<span class="pricing"><?php echo esc_attr( $prices[1] );?></span>
				<sub class="mark"><?php printf( __('/%s', 'nexthemes-plugin'), esc_attr( $prices[2] ) );?></sub>
			</span>
		</li>
		<?php if( strlen($desc) > 0 ):?>
		<li class="desc"><?php echo esc_attr($desc);?></li>
		<?php endif;?>
		
		<?php 
		if( strlen($features) > 0 ): 
			$features = (array) vc_param_group_parse_atts( $features );
			//$features = array_map( 'trim', explode(',', wp_strip_all_tags($features)) );
		?>
			<?php 
			foreach( $features as $feature ): 
				if( isset($feature['title']) && strlen($feature['title']) > 0 ):
			?>
			<li class="feature"><?php 
				echo do_shortcode($feature['title']);
				if( isset($feature['tooltip']) && strlen($feature['tooltip']) > 0 ) {
					echo '<span class="nth-more-info" data-toggle="tooltip" data-placement="top" title="'.esc_attr($feature['tooltip']).'">?</span>';
				}
			?></li>
			<?php 
				endif;
			endforeach;
			?>
		<?php endif;?>
		
		<li class="price-buttons">
			<?php printf('<a class="btn btn-primary" href="%1$s" title="%2$s" %3$s>%4$s</a>', esc_url($bt_link['url']), esc_attr($bt_link['title']), $bt_link['target'], esc_html($bt_text));?>
		</li>
	</ul>
</div>