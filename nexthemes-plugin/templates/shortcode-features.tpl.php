<?php global $post;?>

<?php if( strlen( trim( $title ) ) > 0 ):?>

	<h3 class="heading-title ud-line"><?php echo esc_html( $title );?></h3>

<?php endif;?>

<?php $short_id = 'features-' . rand();?>

<div class="features <?php echo esc_attr($short_id);?> columns-<?php echo absint($per_row);?><?php if(absint($use_boxed)) echo ' row';?>">

	<?php
	$custom_css = '';
	if( strlen(trim($color)) > 0 ) {
		$custom_css .= ".{$short_id} .feature-sumary, .{$short_id} .feature-sumary a {color: {$color}}";
	}
	if( strlen(trim($t_color)) > 0 ) {
		$custom_css .= ".{$short_id} .feature-sumary h3.feature-title a {color: {$t_color}}";
	}
	if( strlen(trim($l_color)) > 0 ) {
		$custom_css .= ".{$short_id} .feature-sumary a.learn-more-link {color: {$l_color}}";
	}
	?>

	<?php if( strlen(trim($custom_css)) > 0 ): ?>
		<style scoped><?php echo $custom_css;?></style>
	<?php endif;?>

	<?php $i = 0; foreach( $theshopier_features as $post ): $i++;?>

		<?php setup_postdata( $post );?>

		<?php
		$class = 'feature';
		if( ( 0 == $i % $per_row ) ) {
			$class .= ' last';
		} elseif ( 0 == ( $i - 1 ) % ( $per_row ) ) {
			$class .= ' first';
		}
		if( strlen(trim($style)) > 0 ) $class .= ' '.esc_attr($style);
		if( absint($use_boxed) ) $class .= ' nth-row-grid';

		$image_size = apply_filters( 'woothemes_features_image_size', array($size, $size), $post );

		$img_atts = array();
		if( strcmp( $style, 'icon-left' ) == 0 ) $img_atts['class'] = 'pull-left';
		$image = get_the_post_thumbnail( $post->ID, $image_size, $img_atts );
		$title = get_the_title();

		if ( apply_filters( 'woothemes_features_disable_external_links', true ) ) {
			$external = '';
		} else {
			$external = 'target="_blank"';
		}

		if ( '' != $post->url ) {
			$image = '<a href="' . esc_url( $post->url ) . '" title="' . esc_attr( $title ) . '" ' . $external . '>' . $image . '</a>';
			$title = '<a href="' . esc_url( $post->url ) . '" title="' . esc_attr( $title ) . '" ' . $external . '>' . $title . '</a>';
		}

		if ( '' != $post->post_excerpt ) {
			//$content = get_the_excerpt();
		} else {
			//$content = get_the_content();
		}

		$content = get_the_excerpt();

		if( (int)$w_limit > 0 ) {
			$words = explode(' ', $content);
			$words = array_slice($words, 0, absint($w_limit));
			$content = implode(' ', $words);
		}

		$content = apply_filters( 'woothemes_features_content', $content, $post );

		$class_inner = 'feature-inner';
		//if(absint($use_boxed)) $class_inner .= ' nth-row-grid';
		?>

		<div class="<?php echo esc_attr($class);?>">

			<div class="<?php echo esc_attr($class_inner);?>">

				<?php if ( has_post_thumbnail() ) echo $image;?>

				<div class="feature-sumary">

					<h3 class="feature-title"><?php echo $title;?></h3>

					<?php if( strcmp($s_excerpt, 'true') == 0 ): ?>
						<div class="feature-content"><?php echo $content;?></div>
					<?php endif;?>

					<?php if( strcmp($learn_more, 'true') == 0 ):?>
						<p class="learn-more-wrap"><a class="learn-more-link" href="<?php echo esc_url($post->url);?>" title="<?php echo esc_attr(get_the_title());?>"><?php echo esc_html($l_text);?> <i class="<?php echo esc_attr($icon)?>"></i></a></p>
					<?php endif;?>

				</div>

			</div>

		</div>

	<?php endforeach;?>

	<div class="fix"></div>

</div><!-- .features -->