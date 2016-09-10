<?php 

if( !class_exists( 'Nexthemes_WooProductOptions' ) ) {
	class Nexthemes_WooProductOptions{

		private $woo_data;
		public $data = array(
			'prod_tab_not_empty'	=> false
		);
		
		public function __construct(){
			$this->backend_call();
			if(( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' ))
				$this->frontend_call();
		}

		public function backend_call(){
			//add video tab
			add_filter('woocommerce_product_data_tabs', array($this, 'woocommerce_product_custom_tabs'), 10, 1);
			//add video tab panel
			add_action('woocommerce_product_data_panels', array($this, 'woocommerce_product_video_panels'), 10);
			//add custom tab panel
			add_action('woocommerce_product_data_panels', array($this, 'woocommerce_product_custom_tab_panels'), 11);
			//save video
			add_action( 'woocommerce_process_product_meta', array($this, 'save_video_data'), 10, 2 );
		}

		public function frontend_call(){
			add_action('theshopier_after_woocommerce_product_thumnail', array($this, 'add_action_video_gallery_thumb'), 10, 1);
			add_action('theshopier_after_woocommerce_product_image', array($this, 'add_action_video_gallery_image'), 10);

			add_filter( 'woocommerce_product_tabs', array($this, 'woocommer_custom_tabs') );
		}

		public function add_action_video_gallery_image(){
			global $post;
			$woo_data = unserialize(get_post_meta( $post->ID, '_nth_wootabs', true ));
			$woovideo_data = $woo_data['woo_video'];
			if( empty($woovideo_data) ) return '';
			if( isset( $woovideo_data ) ) {
				foreach( $woovideo_data as $data ) {
					if(isset($data['size'])) {
						$image_size_w = isset($data['size']['width'])? absint($data['size']['width']) : 480;
						$image_size_h = isset($data['size']['height'])? absint($data['size']['height']) : 540;
					}
					if( isset($data['emb']) ) {
						$fr_url = $data['emb'];
						printf( '<div class="item-video" ><a class="owl-video" href="%s"></a></div>', $fr_url);
					} elseif( isset($data['source']) ){
						$atts = '';
						if(isset($data['source']['autoplay']) && strcmp('true', $data['source']['autoplay']) == 0) $atts .= ' autoplay';
						if(isset($data['source']['loop']) && strcmp('true', $data['source']['loop']) == 0) $atts .= ' loop';
						$strings = "<div>";
						$strings .= "<video controls width=\"".$image_size_w."\" height=\"".$image_size_h."\"".$atts.">";
						if( isset($data['source']['mp4']) && strlen($data['source']['mp4']) > 0 )
							$strings .= "<source src=\"".esc_attr($data['source']['mp4'])."\" type=\"video/mp4\">";
						if( isset($data['source']['webm']) && strlen($data['source']['webm']) > 0 )
							$strings .= "<source src=\"".esc_attr($data['source']['webm'])."\" type=\"video/webm\">";
						if( isset($data['source']['ogv']) && strlen($data['source']['ogv']) > 0 )
							$strings .= "<source src=\"".esc_attr($data['source']['ogv'])."\" type=\"video/ogg\">";
						$strings .= "Your browser does not support the <code>video</code> element.";
						$strings .= "</video>";
						$strings .= "</div>";
						echo $strings;
					}
				}
			}
		}

		public function add_action_video_gallery_thumb( $loop ){
			global $post, $product;
			$woo_data = unserialize(get_post_meta( $post->ID, '_nth_wootabs', true ));
			$woovideo_data = $woo_data['woo_video'];

			if( empty($woovideo_data) ) return '';
			if( isset( $woovideo_data ) ) {
				foreach( $woovideo_data as $data ) {
					if( empty($data['thumb'])) return;
					$attachment_id = $data['thumb'];
					$image_title = esc_attr( get_the_title( $attachment_id ) );
					$image = wp_get_attachment_image( $attachment_id, apply_filters( 'single_product_small_thumbnail_size', 'shop_thumbnail' ) );
					printf( '<a href="#" data-pos="%s" class="video_thumbnail" title="%s">%s</a>', $loop++, $image_title, $image );
				}
			}
		}

		public function woocommer_custom_tabs($tabs = array()){
			global $post;
			$woo_data =  unserialize(get_post_meta( $post->ID, '_nth_wootabs', true ));
			$customtab_data = $woo_data['custom_tab'];

			if( !empty($customtab_data) ) {
				foreach($customtab_data as $k => $data){
					$id = 'nth-customtab-' . $k;
					$tabs[$id] = array(
						'title'		=> sprintf( '%s', esc_html($data['custom_tab_title']) ),
						'priority' 	=> 70 + $k,
						'callback'	=> __CLASS__ . '::woocommer_custom_callback',
						'hidden'	=> isset($data['custom_tab_hidden'])? esc_attr($data['custom_tab_hidden']): ''
					);
				}
				$this->data['prod_tab_not_empty'] = true;
				add_filter('theshopier_woo_custom_tabs_init', function($res){return true;}, 10);
			}

			return $tabs;
		}

		public static function woocommer_custom_callback( $tabID ){
			if(!class_exists('Nexthemes_StaticBlock')) return '';
			global $post;
			$woo_data =  unserialize(get_post_meta( $post->ID, '_nth_wootabs', true ));
			$customtab_data = $woo_data['custom_tab'];
			$k_ = explode('-', $tabID);
			$i = $k_[count($k_)-1];
			$stb_id = $customtab_data[$i]['custom_tab_content'];
			Nexthemes_StaticBlock::getSticBlockContent($stb_id);
		}

		public function woocommerce_product_custom_tabs( $tabs ){
			$tabs['nth_videos'] = array(
				'label'		=> __('Videos', 'nexthemes-plugin'),
				'target'	=> 'videos_product_data',
				'class'		=> array(),
			);
			$tabs['nth_custom_tab'] = array(
				'label'		=> __('Single Tabs', 'nexthemes-plugin'),
				'target'	=> 'custom_tab_product_data',
				'class'		=> array(),
			);

			return $tabs;
		}

		public function woocommerce_product_video_panels(){
			global $post;
			$woo_data =  unserialize(get_post_meta( $post->ID, '_nth_wootabs', true ));
			$woovodes_data = $woo_data['woo_video'];
			$i = 0;
			$no_found = __("No videos found!", 'nexthemes-plugin');
			$tbody = "<tr id=\"no_item\"><td colspan='3'><small>{$no_found}</small></td></tr>";
			if( !empty( $woovodes_data ) && is_array($woovodes_data) ) {
				$tbody = "<tr id=\"no_item\" class='hidden'><td colspan='3'><small>{$no_found}</small></td></tr>";
				foreach( $woovodes_data as $k => $v ){
					$image_src = THEME_BACKEND_URI . "images/placeholder.png";
					if(!empty($v['thumb']) && absint($v['thumb']) > 0) {
						$image_src = wp_get_attachment_image_src( $v['thumb'], 'thumbnail' );
						$image_src = $image_src[0];
					}
					$image = "<img src=\"".$image_src."\" width=\"50\" />";

					$hidden_input = "<input type=\"hidden\" name=\"nth_woovideos_thumb[{$i}]\" value=\"".esc_attr($v['thumb'])."\" />";

					if(!empty($v['size']) && is_array($v['size'])) {
						if( isset($v['size']['width']) )
							$hidden_input .= "<input type=\"hidden\" name=\"nth_woovideos_size[{$i}][width]\" value=\"".esc_attr($v['size']['width'])."\" />";
						if( isset($v['size']['height']) )
							$hidden_input .= "<input type=\"hidden\" name=\"nth_woovideos_size[{$i}][height]\" value=\"".esc_attr($v['size']['height'])."\" />";
					}

					if( isset($v['emb']) ) {
						$viewcode = esc_url($v['emb']);
						$iframe_data = $viewcode;
						$hidden_input .= "<input type=\"hidden\" name=\"nth_woovideos[{$i}]\" value=\"".esc_attr($iframe_data)."\" />";
					} elseif($v['source']) {
						$viewcode = "<ul>";
						if( ! empty($v['source']['mp4']) ) {
							$viewcode .= "<li>".esc_attr($v['source']['mp4'])."</li>";
							$hidden_input .= "<input type=\"hidden\" name=\"nth_woovideos[{$i}][mp4]\" value=\"".esc_url($v['source']['mp4'])."\" />";
						}
						if( ! empty($v['source']['webm']) ) {
							$viewcode .= "<li>".esc_attr($v['source']['webm'])."</li>";
							$hidden_input .= "<input type=\"hidden\" name=\"nth_woovideos[{$i}][webm]\" value=\"".esc_url($v['source']['webm'])."\" />";
						}
						if( ! empty($v['source']['ogv']) ) {
							$viewcode .= "<li>".esc_attr($v['source']['ogv'])."</li>";
							$hidden_input .= "<input type=\"hidden\" name=\"nth_woovideos[{$i}][ogv]\" value=\"".esc_url($v['source']['ogv'])."\" />";
						}

						if( ! empty($v['source']['poster']) )
							$hidden_input .= "<input type=\"hidden\" name=\"nth_woovideos[{$i}][poster]\" value=\"".esc_url($v['source']['poster'])."\" />";
						if( ! empty($v['source']['autoplay']) )
							$hidden_input .= "<input type=\"hidden\" name=\"nth_woovideos[{$i}][autoplay]\" value=\"".esc_attr($v['source']['autoplay'])."\" />";
						if( ! empty($v['source']['loop']) )
							$hidden_input .= "<input type=\"hidden\" name=\"nth_woovideos[{$i}][loop]\" value=\"".esc_attr($v['source']['loop'])."\" />";


						$viewcode .= "</ul>";

					}

					$tbody .= "<tr data-index='{$i}' id='nth_woovideo_item_{$i}'>";
					$tbody .= "<td>".$image."</td>";
					$tbody .= "<td class='view_code'>";
					$tbody .= $viewcode;
					$tbody .= $hidden_input;
					$tbody .= "</td>";
					$tbody .= "<td><a href='#' class='dashicons-before dashicons-trash remove'></a></td>";
					$tbody .= '</tr>';
					$i++;
				}

			}

			if( strlen($tbody) > 0 ) $tbody = "<tbody>" . $tbody . "</tbody>";
			?>

			<div id="videos_product_data" class="panel woocommerce_options_panel">
				<div class="options_group">
					<p><?php _e("Attached videos");?></p>
					<div style="margin: 10px 12px;">
						<table id="table_videos_html" class="wp-list-table nth-ad-table widefat wo_di_table_videos">
							<thead>
							<tr>
								<th class="nth-table-thumb"><?php _e('Thumb', 'nexthemes-plugin')?></th>
								<th class="nth-table-preview"><?php _e('Preview', 'nexthemes-plugin')?></th>
								<th class="nth-table-action"></th>
							</tr>
							</thead>
							<?php echo $tbody;?>
						</table>
					</div>

				</div>


				<div class="options_group add_woovideos woovideos_embedded">

					<div class="form-field" style="padding: 5px 20px 5px 162px!important; margin: 9px 0;">
						<?php
						$image = THEME_BACKEND_URI . "images/placeholder.png";
						$dis_none_remove = "display: none;";
						?>
						<label for=""><?php _e('Thumbnail');?></label>
						<div class="nth_upload_image" data-f_title="<?php _e( 'Choose an image', 'nexthemes-plugin' ); ?>" data-f_btext="<?php _e( 'Use image', 'nexthemes-plugin' ); ?>">
							<div class="nth_thumbnail" style="float:left;margin-right:10px;"><img src="<?php echo $image; ?>" width="28px" height="28px" /></div>
							<div style="line-height:28px;">
								<input type="hidden" class="nth_image_id" name="thumbnail_id" value="" />
								<button type="submit" class="nth_upload_image_button button"><?php _e( 'Add image', 'nexthemes-plugin' ); ?></button>
								<button type="submit" style="<?php echo esc_attr($dis_none_remove);?>" class="nth_remove_image_button button"><?php _e( 'Remove', 'nexthemes-plugin' ); ?></button>
							</div>
						</div>
					</div>

				</div>

				<div class="options_group">

					<p class="form-field">
						<input type="radio" id="nth_vsource_type_embedded" name="nth_vsource_type" checked value="embedded" />
						<label for="nth_vsource_type_embedded"><?php _e("Video URL", "nexthemes-plugin")?></label>
						<span class="description"><?php _e("You can use video link on youtube or vimeo");?></span>
					</p>

					<?php
					// External URL
					woocommerce_wp_text_input( array(
						'id' => 'nth_embcode',
						'label' => '',
						'placeholder' => 'http://',
						'desc_tip' => true,
						'description' => __( 'Enter the external URL to the youtube or vimeo.', 'nexthemes-plugin' )
					) );
					?>

				</div>

				<div class="options_group add_woovideos woovideos_upload">

					<p class="form-field">
						<input type="radio" id="nth_vsource_type_upload" name="nth_vsource_type" value="upload" />
						<label for="nth_vsource_type_upload"><?php _e("Media upload", "nexthemes-plugin")?></label>
						<span class="description"><?php _e("You can upload media file as mp4, ogg, webm.");?></span>
					</p>

					<div class="form-field" style="padding: 5px 20px 5px 162px!important; margin: 9px 0;">
						<?php
						$options = array(
							'title'	=> __("Choose an video"),
							'button'	=> array(
								'text'	=> __('Use This Video'),
								'multiple'	=> 0,
							),
							'frame'		=> 'video',
							'state'		=> 'video-details',
						);
						?>
						<div class="nth-media-video-upload" data-options="<?php echo esc_attr( json_encode($options) )?>">
							<input type="hidden" name="nth_media_video_mp4" class="nth_media_video_mp4" />
							<input type="hidden" name="nth_media_video_webm" class="nth_media_video_webm" />
							<input type="hidden" name="nth_media_video_ogv" class="nth_media_video_ogv" />
							<input type="hidden" name="nth_media_video_poster" class="nth_media_video_poster" />
							<input type="hidden" name="nth_media_video_autoplay" class="nth_media_video_autoplay" />
							<input type="hidden" name="nth_media_video_loop" class="nth_media_video_loop" />
							<button type="submit" class="nth_media_upload_button button"><?php _e( 'Upload Video', 'nexthemes-plugin' ); ?></button>
							<button type="submit" class="nth_media_remove_button button hidden"><?php _e( 'Remove Video', 'nexthemes-plugin' ); ?></button>
							<ul class="description"></ul>
						</div>
					</div>

					<p class="form-field dimensions_field">
						<span class="wrap">
							<input type="number" placeholder="width" name="nth_video_size_w" class="nth-minsize input-text wc_input_decimal" min="100" step="1" value="480" max="600"/>
							<span style="float: left; margin-right: 3.8%">&times</span>
							<input type="number" placeholder="height" name="nth_video_size_h" class="nth-minsize input-text wc_input_decimal" min="100" step="1" value="535" max="600"/>
						</span>
					</p>

				</div>

				<div class="options_group">
					<p class="form-field"><button id="button_add_video" class="button button-primary"><i class="dashicons-before dashicons-plus"></i><?php _e("Add Video", 'nexthemes-plugin');?></button></p>
				</div>

			</div>

			<?php
		}

		public function woocommerce_product_custom_tab_panels(){
			global $post;
			$woo_data =  unserialize(get_post_meta( $post->ID, '_nth_wootabs', true ));
			$customtab_data = $woo_data['custom_tab'];
			$static_block = Nexthemes_StaticBlock::getStaticBlocks();
			$tbody = "<tr id=\"no_item\"><td colspan='3'><small>".__("No single tab found!", 'nexthemes-plugin')."</small></td></tr>";

			$i = 0;
			if( !empty( $customtab_data ) && is_array($customtab_data) ) {
				$tbody = "<tr id=\"no_item\" class='hidden'><td colspan='3'><small>".__("No single tab found!", 'nexthemes-plugin')."</small></td></tr>";
				foreach( $customtab_data as $k => $v ){
					$tbody .= "<tr data-index='{$i}'>";
					$tbody .= "<td>{$v['custom_tab_title']}</td>";
					$tbody .= "<td><span class='nth-label'>" . esc_html(get_the_title($v['custom_tab_content'])) . '</span>';
					$tbody .= "<input type='hidden' name='nth_prod_tab_title[{$i}]' value='{$v['custom_tab_title']}' />";
					$tbody .= "<input type='hidden' name='nth_prod_tab_content[{$i}]' value='{$v['custom_tab_content']}' />";
					$tbody .= "<input type='hidden' name='nth_prod_tab_hidden[{$i}]' value='{$v['custom_tab_hidden']}' />";
					$tbody .= "</td><td>";
					foreach( explode(' ', trim($v['custom_tab_hidden'])) as $v ) {
						$str = '';
						switch( $v ) {
							case 'hidden-xs':
								$str = "<span class='nth-label menu'>".__('Mobile', 'nexthemes-plugin')."</span>";
								break;
							case 'hidden-sm':
								$str = "<span class='nth-label widget'>".__('Tablet', 'nexthemes-plugin')."</span>";
								break;
							case 'hidden-lg':
								$str = "<span class='nth-label'>".__('Desktop', 'nexthemes-plugin')."</span>";
								break;

						}
						$tbody .= $str;
					}
					$tbody .= "</td><td><a href=\"#\" title='Remove item' class=\"dashicons-before dashicons-trash remove\"></a></td>";
					$tbody .= "</tr>";
					$i++;
				}
			}

			?>

			<div id="custom_tab_product_data" class="panel woocommerce_options_panel">

				<div class="options_group">
					<h3><?php _e("Single tabs");?></h3>
					<div>
						<table id="table_customtabs_html" class="nth-ad-table wp-list-table widefat wo_di_table_tabs">
							<thead>
							<tr>
								<th class="nth-customtab-title"><?php _e('Title', 'nexthemes-plugin')?></th>
								<th class="nth-customtab-staticblock"><?php _e('Static block', 'nexthemes-plugin')?></th>
								<th class="nth-customtab-staticblock"><?php _e('Hidden on', 'nexthemes-plugin')?></th>
								<th class="nth-table-action"></th>
							</tr>
							</thead>
							<tbody><?php echo $tbody;?></tbody>

						</table>
					</div>
				</div>

				<div class="options_group">
					<p class="form-field">
						<label><?php _e("Tab title", "nexthemes-plugin");?></label>
						<input type="text" id="nth_prod_tab_title_adding"/>
					</p>
					<?php
					$stb_args = array();
					foreach($static_block as $stb){
						$stb_args[$stb['id']] = esc_html($stb['title']);
					}
					woocommerce_wp_select(array(
						'id' => 'nth_prod_tab_content_adding',
						'label' => __( "Custom content", "nexthemes-plugin" ),
						'options' => $stb_args,
						'desc_tip' => true,
						'description' => __( 'Please create a NTH Staticblock and select it in here.', 'nexthemes-plugin' ) ));

					?>

					<p class="form-field">
						<label><?php _e("Hidden on devices", "nexthemes-plugin");?></label>
						<input type="checkbox" class="checkbox" name="nth_prod_tab_hidden_xs" id="nth_prod_tab_hidden_xs" value="hidden-xs">
						<span class="description"><?php _e('Hidden on mobile.', 'nexthemes-plugin')?></span><br/>
						<input type="checkbox" class="checkbox" name="nth_prod_tab_hidden_sm" id="nth_prod_tab_hidden_sm" value="hidden-sm">
						<span class="description"><?php _e('Hidden on table.', 'nexthemes-plugin')?></span><br/>
						<input type="checkbox" class="checkbox" name="nth_prod_tab_hidden_lg" id="nth_prod_tab_hidden_lg" value="hidden-md hidden-lg">
						<span class="description"><?php _e('Hidden on PC.', 'nexthemes-plugin')?></span>
					</p>
				</div>

				<div class="options_group">
					<p class="form-field"><button id="button_add_customtab" class="button button-primary"><?php _e("Add Single Tab", 'nexthemes-plugin');?></button></p>
				</div>

			</div>

			<?php
		}

		public function save_video_data( $post_id, $post ){
			$up_data = array(
				'woo_video'	=> array(),
				'custom_tab'	=> array()
			);
			if( isset($_POST['nth_woovideos_thumb']) && isset( $_POST['nth_woovideos'] )) {
				$thumb_ids = $_POST['nth_woovideos_thumb'];
				$v_size = $_POST['nth_woovideos_size'];
				$data = array();
				foreach($_POST['nth_woovideos'] as $k => $v) {
					$data[$k]['thumb'] = !empty($thumb_ids[$k])? $thumb_ids[$k]: '0';
					$data[$k]['size'] = !empty($v_size[$k])? $v_size[$k]: array();
					if(is_array($v)) {
						$data[$k]['source'] = $v;
					} else {
						$data[$k]['emb'] = $v;
					}
				}
				$up_data['woo_video'] = $data;
			}

			if( isset($_POST['nth_prod_tab_title']) && isset($_POST['nth_prod_tab_content']) ) {
				$data = array();
				$tab_contents = $_POST['nth_prod_tab_content'];
				$tab_hidden = $_POST['nth_prod_tab_hidden'];
				$i = 0;
				foreach( $_POST['nth_prod_tab_title'] as $k => $v ){
					$data[$i]['custom_tab_title'] = strip_tags($v);
					$data[$i]['custom_tab_content'] = esc_attr($tab_contents[$k]);
					$data[$i]['custom_tab_hidden'] = esc_attr(trim($tab_hidden[$k]));
					$i++;
				}
				$up_data['custom_tab'] = $data;
			}

			$data_up = serialize($up_data);
			update_post_meta( $post_id, '_nth_wootabs', $data_up );
		}

	}
	
	new Nexthemes_WooProductOptions();
}