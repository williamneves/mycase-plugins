<?php 

$imgs = explode( ',', $imgs );

if( strlen( $title ) > 0 ) {
	echo '<div class="nth-shortcode-header">';
	$heading_class = array('heading-title');
	if( strlen(trim($h_style)) > 0 ) $heading_class[] = esc_attr($h_style);
	echo '<h3 class="'.esc_attr(implode(' ', $heading_class)).'">'. esc_html($title) .'</h3>';
	echo '</div>';
}

$options = array( 
	"items"				=> $column,
);
$options = NexThemes_Plg::get_owlResponsive($options);

$_item_class = 'item-inner';
if(strlen($style) > 0) {
	$_item_class .= '-' . $style;
}

$class = array('col-sm-24');
$ul_class = vc_shortcode_custom_css_class( $css );
?>
<div class="row nth-owlCarousel loading <?php echo esc_attr($ul_class);?>" data-options="<?php echo esc_attr(json_encode($options));?>" data-base="1">
	<?php foreach( $imgs as $img ): ?>
	<div class="<?php echo esc_attr( implode( ' ', $class ) );?>">
		<div class="<?php echo esc_attr($_item_class);?>"><?php echo wp_get_attachment_image( $img, 'full' );?></div>
	</div>
	<?php endforeach;?>
</div>