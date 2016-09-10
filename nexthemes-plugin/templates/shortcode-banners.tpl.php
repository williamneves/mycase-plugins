<?php
$classes = array($class);
$classes[] = vc_shortcode_custom_css_class( $css );
$bg_image = strlen($bg_img) > 0? $bg_img: $bg_image;

$link_args = wp_parse_args(vc_build_link($link), array(
	'url'		=> '',
	'title'		=> 'Banner link',
	'target'	=> ''
));

$link_before = ''; $link_after = '';
if( strlen(trim($link_args['url'])) > 0 ) {
	$link_before = sprintf('<a href="%1$s" title="%2$s" target="%3$s">', esc_url($link_args['url']), esc_attr($link_args['title']), esc_attr($link_args['target']));
	$link_after = '</a>';
}
?>


<div class="<?php echo esc_attr( implode(' ', $classes) );?>">

	<?php if( strlen( trim( $css ) ) > 0 ):?>
		<style type="text/css" data-type="vc_shortcodes-custom-css" scoped><?php echo $css;?></style>
	<?php endif;?>

	<?php echo $link_before;?>
	<figure>
		<?php if( strlen( $bg_image ) > 0 ){
			$bg_img_url = is_numeric($bg_image)? wp_get_attachment_url( $bg_image ) : $bg_image;
			NexThemes_Plg::getImage(array(
				'alt' => 'Banner link',
				'src'	=> $bg_img_url
			));
		}?>
		<?php if( strlen( trim($content) ) > 0 ):?>
		<figcaption>
			<p><?php echo do_shortcode( $content );?></p>
		</figcaption>
		<?php endif;?>
	</figure>
	<?php echo $link_after;?>
</div>
