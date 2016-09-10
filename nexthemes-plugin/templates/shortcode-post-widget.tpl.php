
<?php if( strlen( $title ) > 0 ):?>

	<?php $head_style = strlen($head_style) >0? ' '.$head_style: '';?>
	<h3 class="heading-title<?php echo esc_attr($head_style)?>"><?php echo esc_html($title);?></h3>
	
<?php endif;?>

<?php
if( absint($as_widget) ) {
	echo '<ul class="list-post-widget">';
} else {
	if( isset( $is_slider ) && absint($is_slider) ) {
		$options = array(
			"items"			=> 2,
			"responsive"	=> array(
				1199	=> 2,
				980		=> 2,
				768		=> 1,
				479		=> 1
			)
		);
		$class = " nth-owlCarousel loading";
		$data_options = esc_attr( json_encode($options) );

	} else {
		$class = '';
		$data_options = '';
	}
	echo "<ul class=\"list-posts row{$class}\" data-options=\"{$data_options}\">";
}

?>
	
	<?php while( $_post->have_posts() ): $_post->the_post(); global $post; ?>
	
		<?php
		$__post_data = array(
			'hidden_date'	=> $hidden_date,
			'w_style'		=> $w_style,
			'style'			=> $style,
		);
		set_query_var( '__post_data', $__post_data );
		set_query_var( 'excerpt_words', $excerpt_words );

		?>
	
		<?php set_query_var( 'shortcode_style', $style );?>
	
		<?php if(absint($as_widget)):?>
	
			<?php get_template_part( 'content', 'widget' ); ?>
	
		<?php else: ?>
		
			<?php get_template_part( 'content', get_post_format() ); ?>
	
		<?php endif;?>

	<?php endwhile; ?>
	
</ul>
