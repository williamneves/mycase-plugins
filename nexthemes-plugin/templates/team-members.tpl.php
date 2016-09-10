<?php 

if( strlen( $title ) > 0 ) {
	$heading_class = array('heading-title');
	if( strlen(trim($h_style)) > 0 ) $heading_class[] = esc_attr($h_style);
	echo '<h3 class="'.esc_attr(implode(' ', $heading_class)).'">'.esc_html( $title ).'</h3>';
}

$classes = array('nth-team-members-wrapper row');
if( isset( $style ) && strlen( $style ) > 0 ) $classes[] = $style;

echo '<div class="'.esc_attr( implode( ' ', $classes ) ).'">';

$classes = array();

if( absint($columns) > 1 ) {
	$classes[] = 'col-lg-' . round( 24 / absint($columns) );
	$classes[] = 'col-md-' . round( 24 / round( absint($columns) * 992 / 1200) );
	$classes[] = 'col-sm-' . round( 24 / round( absint($columns) * 768 / 1200) );
	$classes[] = 'col-xs-' . round( 24 / round( absint($columns) * 480 / 1200) );
	$classes[] = 'col-mb-24';
} else {
	$classes[] = 'col-lg-24';
	$classes[] = 'col-md-24';
	$classes[] = 'col-sm-24';
	$classes[] = 'col-xs-24';
	$classes[] = 'col-mb-24';
}

while($teams->have_posts()) {
	$teams->the_post(); global $post;
	$meta = unserialize( get_post_meta($post->ID, 'nth_team_options',true) );
	$social_args = array();
	if( isset( $meta['fb_link'] ) && strlen( $meta['fb_link'] ) > 0 ) 
		$social_args['facebook'] = array( 'fa fa-facebook-square', $meta['fb_link'] );
	if( isset( $meta['tw_link'] ) && strlen( $meta['tw_link'] ) > 0 ) 
		$social_args['twitter'] = array( 'fa fa-twitter-square', $meta['tw_link'] );
	if( isset( $meta['goo_link'] ) && strlen( $meta['goo_link'] ) > 0 ) 
		$social_args['google'] = array( 'fa fa-google-plus-square', $meta['goo_link'] );
	if( isset( $meta['pin_link'] ) && strlen( $meta['pin_link'] ) > 0 ) 
		$social_args['pinterest'] = array( 'fa fa-pinterest-square', $meta['pin_link'] );
	if( isset( $meta['inst_link'] ) && strlen( $meta['inst_link'] ) > 0 ) 
		$social_args['instagram'] = array( 'fa fa-instagram', $meta['inst_link'] );
	if( isset( $meta['in_link'] ) && strlen( $meta['in_link'] ) > 0 ) 
		$social_args['linkedin'] = array( 'fa fa-linkedin-square', $meta['in_link'] );
	if( isset( $meta['drib_link'] ) && strlen( $meta['drib_link'] ) > 0 ) 
		$social_args['dribbble'] = array( 'fa fa-dribbble', $meta['drib_link'] );
	
	$meta['pr_link'] = isset($meta['pr_link']) && strlen($meta['pr_link']) > 0? $meta['pr_link']: '#';
	?>
	<div class="<?php echo esc_attr( implode( ' ', $classes ) );?>">
		<div class="team-member post-<?php echo esc_attr($post->ID);?>">
			<?php if(has_post_thumbnail()): ?>
				<a title="<?php the_title();?>" href="<?php echo esc_url( $meta['pr_link'] );?>">
					<?php the_post_thumbnail('teams_thumb'); ?>
				</a>
			<?php endif;?>
			
			<div class="info">
				<h3><a title="<?php the_title();?>" href="<?php echo esc_url( $meta['pr_link'] );?>"><?php the_title();?></a></h3>
				
				<?php if( isset( $meta['role'] ) && strlen( $meta['role'] ) > 0 ):?>
				<em><?php echo $meta['role']; ?></em>
				<?php endif;?>
				
				<?php the_excerpt();?>
				<ul class="nth-social-network">
					<?php foreach( $social_args as $key => $item ): ?>
					<li class="<?php echo esc_attr($key)?>">
						<a href="<?php echo esc_url( $item[1] )?>" title="<?php echo esc_attr($key);?>">
							<i class="<?php echo esc_attr( $item[0] );?>"></i>
						</a>
					</li>
					<?php endforeach;?>
				</ul>
			</div>
		</div><!-- .team-member -->
	</div>
	<?php
	
}

echo '</div>';

?>