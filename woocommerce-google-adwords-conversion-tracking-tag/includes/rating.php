<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<div class="rate">
	<div class="rating-stars">
		<a href="//wordpress.org/support/view/plugin-reviews/woocommerce-google-adwords-conversion-tracking-tag?rate=1#postform"
		   data-rating="1" title="" target="_blank">
			<span class="dashicons dashicons-star-filled" style="color:#ffb900 !important;"></span></a>
		<a href="//wordpress.org/support/view/plugin-reviews/woocommerce-google-adwords-conversion-tracking-tag?rate=2#postform"
		   data-rating="2" title="" target="_blank">
			<span class="dashicons dashicons-star-filled" style="color:#ffb900 !important;"></span></a>
		<a href="//wordpress.org/support/view/plugin-reviews/woocommerce-google-adwords-conversion-tracking-tag?rate=3#postform"
		   data-rating="3" title="" target="_blank">
			<span class="dashicons dashicons-star-filled" style="color:#ffb900 !important;"></span></a>
		<a href="//wordpress.org/support/view/plugin-reviews/woocommerce-google-adwords-conversion-tracking-tag?rate=4#postform"
		   data-rating="4" title="" target="_blank">
			<span class="dashicons dashicons-star-filled" style="color:#ffb900 !important;"></span></a>
		<a href="//wordpress.org/support/view/plugin-reviews/woocommerce-google-adwords-conversion-tracking-tag?rate=5#postform"
		   data-rating="5" title="" target="_blank">
			<span class="dashicons dashicons-star-filled" style="color:#ffb900 !important;"></span></a>
	</div>
	<input type="hidden" name="rating" id="rating" value="5">
	<input type="hidden" name="review_topic_slug" value="woocommerce-google-adwords-conversion-tracking-tag">
	<script>
		jQuery(document).ready(function ($) {
			$('.rating-stars').find('a').hover(
				function () {
					$(this).nextAll('a').children('span').removeClass('dashicons-star-filled').addClass('dashicons-star-empty');
					$(this).prevAll('a').children('span').removeClass('dashicons-star-empty').addClass('dashicons-star-filled');
					$(this).children('span').removeClass('dashicons-star-empty').addClass('dashicons-star-filled');
				}, function () {
					var rating = $('input#rating').val();
					if (rating) {
						var list = $('.rating-stars a');
						list.children('span').removeClass('dashicons-star-filled').addClass('dashicons-star-empty');
						list.slice(0, rating).children('span').removeClass('dashicons-star-empty').addClass('dashicons-star-filled');
					}
				}
			);
		});
	</script>
</div>
