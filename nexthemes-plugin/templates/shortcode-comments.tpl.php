<ul class="nth-recent-comments">
		
	<?php foreach ( (array) $comments as $comment): ?>
	
	<li class="recentcomment">
	
		<h3><a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) );?>"><?php echo get_the_title( $comment->comment_post_ID );?></a></h3>
		
		<p class="short-content"><?php echo Nexthemes_Shortcodes::popString(wp_strip_all_tags($comment->comment_content), $excerpt_words);?></p>
		
		<div class="post-meta">
		
			<span class="author"><?php _e('By', 'nexthemes-plugin');?> <?php comment_author_link( $comment ); ?></span>
			
			<span class="date"><?php echo get_the_date();?></span>
		
		</div>
	
	</li>
	
	<?php endforeach;?>

</ul>