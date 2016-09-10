<?php 
$styleMaps = array(
	'facebook'	=> array(
		array(
			'featureType'	=> 'water',
			'elementType'	=> 'all',
			'stylers'		=> array(
				array('color' => "#3b5998")
			)
		),
		array(
			'featureType'	=> 'administrative.province',
			'elementType'	=> 'all',
			'stylers'		=> array(
				array('visibility' => "off")
			)
		),
		array(
			'featureType'	=> 'all',
			'elementType'	=> 'all',
			'stylers'		=> array(
				array('hue' => '#3b5998'),
				array('saturation' => -22)
			)
		),
		array(
			'featureType'	=> 'landscape',
			'elementType'	=> 'all',
			'stylers'		=> array(
				array('visibility' => 'on'),
				array('color' => '#f7f7f7'),
				array('saturation' => 10),
				array('lightness' => 76)
			)
		),
		array(
			'featureType'	=> 'landscape.natural',
			'elementType'	=> 'all',
			'stylers'		=> array(
				array('color' => '#f7f7f7')
			)
		),
		array(
			'featureType'	=> 'road.highway',
			'elementType'	=> 'all',
			'stylers'		=> array(
				array('color' => '#8b9dc3'),
				array('visibility' => 'simplified')
			)
		),
		array(
			'featureType'	=> 'road.highway',
			'elementType'	=> 'labels.icon',
			'stylers'		=> array(
				array('visibility' => 'off')
			)
		),
		array(
			'featureType'	=> 'road.local',
			'elementType'	=> 'geometry.fill',
			'stylers'		=> array(
				array('color' => '#8b9dc3')
			)
		),
		array(
			'featureType'	=> 'administrative.country',
			'elementType'	=> 'geometry.stroke',
			'stylers'		=> array(
				array('visibility' => 'simplified'),
				array('color' => '#3b5998')
			)
		),
		array(
			'featureType'	=> 'administrative',
			'elementType'	=> 'labels.icon',
			'stylers'		=> array(
				array('visibility' => 'on'),
				array('color' => '#3b5998')
			)
		),
		array(
			'featureType'	=> 'transit.line',
			'elementType'	=> 'geometry.stroke',
			'stylers'		=> array(
				array('invert_lightness' => 0),
				array('color' => '#ffffff'),
				array('weight' => 0.43)
			)
		)
	),
	'gray'		=> array(
		array(
			'featureType'	=> 'all',
			'elementType'	=> 'all',
			'stylers'		=> array(
				array('saturation' => -100)
			)
		),
		array(
			'featureType'	=> 'water',
			'elementType'	=> 'all',
			'stylers'		=> array(
				array('color' => '#6f6f6f')
			)
		)
	),
	'lightgray'	=> array(
		array(
			'featureType'	=> 'landscape',
			'elementType'	=> 'geometry.fill',
			'stylers'		=> array(
				array('color' => '#edecea')
			)
		),
		array(
			'featureType'	=> 'landscape',
			'elementType'	=> 'geometry.stroke',
			'stylers'		=> array(
				array('color' => '#edecea')
			)
		),
		array(
			'featureType'	=> 'poi',
			'elementType'	=> 'geometry.stroke',
			'stylers'		=> array(
				array('visibility' => 'off')
			)
		),
		array(
			'featureType'	=> 'poi',
			'elementType'	=> 'geometry.fill',
			'stylers'		=> array(
				array('visibility' => 'on'),
				array('color' => '#f7f7f7')
			)
		),
		array(
			'featureType'	=> 'road',
			'elementType'	=> 'geometry.stroke',
			'stylers'		=> array(
				array('color' => '#e6e6e6')
			)
		),
		array(
			'featureType'	=> 'water',
			'elementType'	=> 'geometry.fill',
			'stylers'		=> array(
				array('color' => '#bfbfbf')
			)
		),
		array(
			'featureType'	=> 'road.highway',
			'elementType'	=> 'geometry.fill',
			'stylers'		=> array(
				array('color' => '#d9d9d9')
			)
		),
		array(
			'featureType'	=> 'road',
			'elementType'	=> 'labels.text.fill',
			'stylers'		=> array(
				array('color' => '#424242')
			)
		),
		array(
			'featureType'	=> 'road.local',
			'elementType'	=> 'geometry.fill',
			'stylers'		=> array(
				array('saturation' => -100)
			)
		),
		array(
			'featureType'	=> 'water',
			'elementType'	=> 'labels.text.fill',
			'stylers'		=> array(
				array('color' => '#ffffff')
			)
		),
		array(
			'featureType'	=> 'water',
			'elementType'	=> 'labels.text.stroke',
			'stylers'		=> array(
				array('color' => '#a6a6a6')
			)
		),
		array(
			'featureType'	=> 'poi.business',
			'elementType'	=> 'labels',
			'stylers'		=> array(
				array('saturation' => -100)
			)
		),
		array(
			'featureType'	=> 'poi',
			'elementType'	=> 'all',
			'stylers'		=> array(
				array('saturation' => -100)
			)
		),
		array(
			'featureType'	=> 'transit',
			'elementType'	=> 'all',
			'stylers'		=> array(
				array('saturation' => -100),
				array('lightness', 1)
			)
		)
	)
);
$classes = array('maps-save-widget');
$classes[] = vc_shortcode_custom_css_class( $css );
?>

<style type="text/css" data-type="nth_vc_shortcodes-custom-css" scoped>
	<?php echo $css;?>
	#map-<?php echo esc_attr($map_id);?> {
		height: <?php echo esc_attr($height);?>
	}
</style>

<div id="map-<?php echo esc_attr($map_id);?>" class="map-wrapper"></div>
<div id="save-widget-<?php echo esc_attr($map_id);?>" class="<?php echo esc_attr(implode(' ', $classes))?>">
	<p><?php echo $content;?></p>
</div>

<script src="https://maps.googleapis.com/maps/api/js?&callback=initMap&signed_in=true" async defer></script>

<script type="text/javascript">
	
	function initMap() {
		var map = new google.maps.Map(document.getElementById('map-<?php echo esc_attr($map_id);?>'), {
			zoom: <?php echo esc_js($zoom);?>,
			center: {lat: -34.397, lng: 150.644},
			scrollwheel: false,
			panControl: false,
			scaleControl: false,
			mapTypeControl: false,
			streetViewControl: false,
			zoomControl: true,
		});
		
		var mapStyle = [];
		<?php 
		$style = trim($style);
		if( !empty($style) ): ?>
		mapStyle = <?php if( strcmp( $style, 'custom_color' ) == 0 ) echo urldecode(base64_decode($m_color)); elseif(isset($styleMaps[$style])) echo json_encode( $styleMaps[$style] ); ?>
		<?php endif;?>
		
		customMap(map, mapStyle);
		
		var address = "<?php echo esc_attr($address);?>";
		var geocoder = new google.maps.Geocoder();
		
		geocodeAddress(geocoder, map, address);
		
	}
	
	function geocodeAddress(geocoder, resultsMap, address) {
		
		geocoder.geocode({'address': address}, function(results, status) {
			if (status === google.maps.GeocoderStatus.OK) {
				resultsMap.setCenter(results[0].geometry.location);
				var mk_options = {
					map: resultsMap,
					animation: google.maps.Animation.DROP,
					position: results[0].geometry.location
				}
				<?php if( strlen(trim($mk_icon)) > 0 ):
					if( is_numeric($mk_icon) ) $mk_icon = wp_get_attachment_url(absint($mk_icon));
				?>
				mk_options.icon = '<?php echo esc_url($mk_icon);?>';
				<?php endif;?>
				var marker = new google.maps.Marker(mk_options);
				
				var widgetDiv = document.getElementById('save-widget-<?php echo esc_attr($map_id);?>');
				resultsMap.controls[google.maps.ControlPosition.TOP_LEFT].push(widgetDiv);
				
				var saveWidget = new google.maps.SaveWidget(widgetDiv, {
					place: {
						query: '<?php echo esc_attr($address);?>',
						location: results[0].geometry.location
					},
					attribution: {
						source: '<?php echo get_option('blogname');?>',
						webUrl: '<?php echo esc_url(home_url());?>'
					}
				});
				/*setTimeout(function () {
					widgetDiv.className  = "maps-save-widget animated fadeInLeft";
				}, 3000 );*/

			} else {
				alert('Geocode was not successful for the following reason: ' + status);
			}
		});
	}
	
	function customMap(map, mapStyle){
		if(mapStyle.length == 0) return;
		var customMapType = new google.maps.StyledMapType( mapStyle, {
			name: 'Custom Style'
		});
		
		var customMapTypeId = 'custom_style';
		
		map.mapTypes.set(customMapTypeId, customMapType);
		map.setMapTypeId(customMapTypeId);
	}
</script>