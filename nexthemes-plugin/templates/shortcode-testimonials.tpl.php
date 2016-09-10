<?php if( strlen( trim( $title ) ) > 0 ):

	$heading_class = array('heading-title');
	if( strlen(trim($h_style)) > 0 ) $heading_class[] = esc_attr($h_style);
	echo '<div class="nth-shortcode-header">';
	echo '<h3 class="'.esc_attr(implode(' ', $heading_class)).'">'. esc_html($title) .'</h3>';
	echo '</div>';

endif;

$_wrap_class = array('testimonials-wrapper');
$_wrap_class[] = 'testimonials-style-' . $style;
$options = array();
if(strcmp($use_slider, 'true') == 0) {
	$_wrap_class[] = 'row nth-owlCarousel loading';
	$options = array(
		'items'		=> 1,
		'nav'		=> false,
		'dots'		=> true
	);
	$options = NexThemes_Plg::get_owlResponsive($options);
}

$filters = array(
	'image'		=> 0,
	'desc'		=> 1,
	'author'	=> 1,
);
switch($style){
	case 'def':
	case '2':
		$filters['image'] = 1;
		$_wrap_class[] = 'text-center';

}
?>

<div class="<?php echo esc_attr(implode(' ', $_wrap_class))?>" data-options="<?php echo esc_attr(json_encode($options));?>" data-base="1">

<?php foreach( $nexthemes_testimonials as $testimonial ): ?>
				
	<?php $post = $testimonial;?>
	
	<?php setup_postdata( $post );?>
	
	<div class="testimonials-item">

		<?php if(absint($filters['image'])) : ?>
		<div class="image"><?php echo $testimonial->image;?></div>
		<?php endif;?>

		<?php if(absint($filters['desc'])) : ?>
		<div class="description"><?php echo get_the_content();?></div>
		<?php endif;?>

		<?php if(absint($filters['author'])) : ?>
		<h3 class="author"><?php echo $post->post_title;?></h3>
		<?php endif;?>
	
	</div>

<?php endforeach;?>

</div>
