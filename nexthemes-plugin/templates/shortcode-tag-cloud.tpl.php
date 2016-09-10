<?php
if( strlen( trim( $heading ) ) > 0 ) {
	$heading_class = array('heading-title');
	if( strlen(trim($h_style)) > 0 ) $heading_class[] = esc_attr($h_style);
	echo '<h3 class="'.esc_attr(implode(' ', $heading_class)).'">'. esc_html($heading) .'</h3>';
}
$type = 'WP_Widget_Tag_Cloud';
$args = array();
global $wp_widget_factory;

$taxonomy = ( !empty($taxonomy) && taxonomy_exists($taxonomy) )? $taxonomy: 'post_tag';

$tag_cloud = wp_tag_cloud( apply_filters( 'widget_tag_cloud_args', array(
	'taxonomy' => $taxonomy,
	'echo' => false
) ) );

?>
<div class="nth-shortcode-content">
	<?php
	if ( !empty($tag_cloud) ) {
		echo $tag_cloud;
	}
	?>
</div>
