<?php
if( strlen( trim( $heading ) ) > 0 ) {
	$heading_class = array('heading-title');
	if( strlen(trim($h_style)) > 0 ) $heading_class[] = esc_attr($h_style);
	echo '<h3 class="'.esc_attr(implode(' ', $heading_class)).'">'. esc_html($heading) .'</h3>';
}

$options = array(
	"items"				=> $columns,
);
$options = NexThemes_Plg::get_owlResponsive($options);


if(!empty($stores)):

?>

<div class="row">

	<div class="nth-shortcode-content nth-owlCarousel loading" data-options="<?php echo esc_attr(json_encode($options));?>" data-base="1">

		<?php foreach($stores as $store) :
			$_zoom = empty($store['zoom'])? '15': $store['zoom'];
			$_gmap_static_link = add_query_arg(array(
				'center'	=> urlencode($store['address']),
				'zoom'		=> absint($_zoom),
				'size'		=> urlencode($map_size),
				'markers'	=> urlencode($store['address']),
				/*'key'		=> urlencode('AIzaSyAcFghU3y3-DAibCdLTiQfEodk7jVQ1bHw')*/
			), 'https://maps.googleapis.com/maps/api/staticmap');

			?>

		<div class="col-sm-24 location-item">
			<div class="location-item-inner">
				<div class="gmap_static_wrapper">
					<?php
					NexThemes_Plg::getImage(array(
						'src'	=> $_gmap_static_link,
						'alt'	=> esc_attr__('Store location map', 'nexthemes-plugin'),
						'width'	=> '270',
						'height'	=> '170'
					))?>
				</div>
				<div class="store-meta">
					<?php
					if(!empty($store['name'])) {
						printf('<h3>%s</h3>', esc_html($store['name']));
					}
					if(!empty($store['infos'])) {
						foreach($store['infos'] as $info) {
							if(!empty($info) && !empty($info['line'])) printf('<p>%s</p>', esc_html($info['line']));
						}
					}
					if( !empty($store['link'])) {
						$_li_txt = !empty($store['link']['title'])? esc_html($store['link']['title']): esc_html__('More infomation','nexthemes-plugin');
						$_li_link = !empty($store['link']['url'])? esc_url($store['link']['url']) : '#';
						printf('<a href="%1$s" title="%2$s">%2$s <i class="fa fa-caret-right" aria-hidden="true"></i></a>', esc_url($_li_link), esc_attr($_li_txt));
					}
					?>

				</div>
			</div>
		</div>

		<?php endforeach;?>

	</div>

</div>

<?php endif; ?>