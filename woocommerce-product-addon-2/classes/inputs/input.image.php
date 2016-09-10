<?php
/*
 * Followig class handling pre-uploaded image control and their
* dependencies. Do not make changes in code
* Create on: 9 November, 2013
*/

class NM_Image_wooproduct extends NM_Inputs_wooproduct{
	
	/*
	 * input control settings
	 */
	var $title, $desc, $settings, $ispro;
	
	/*
	 * this var is pouplated with current plugin meta
	*/
	var $plugin_meta;
	
	function __construct(){
		
		$this -> plugin_meta = get_plugin_meta_wooproduct();
		
		$this -> title 		= __ ( 'Images', 'nm-personalizedproduct' );
		$this -> desc		= __ ( 'Images selection', 'nm-personalizedproduct' );
		$this -> settings	= self::get_settings();
		$this -> ispro 		= true;
	}
	
	
	
	
	private function get_settings(){
		
		return array (
		'title' => array (
				'type' => 'text',
				'title' => __ ( 'Title', 'nm-personalizedproduct' ),
				'desc' => __ ( 'It will be shown as field label', 'nm-personalizedproduct' ) 
		),
		'data_name' => array (
				'type' => 'text',
				'title' => __ ( 'Data name', 'nm-personalizedproduct' ),
				'desc' => __ ( 'REQUIRED: The identification name of this field, that you can insert into body email configuration. Note:Use only lowercase characters and underscores.', 'nm-personalizedproduct' ) 
		),
		'description' => array (
				'type' => 'text',
				'title' => __ ( 'Description', 'nm-personalizedproduct' ),
				'desc' => __ ( 'Small description, it will be diplay near name title.', 'nm-personalizedproduct' ) 
		),
		'error_message' => array (
				'type' => 'text',
				'title' => __ ( 'Error message', 'nm-personalizedproduct' ),
				'desc' => __ ( 'Insert the error message for validation.', 'nm-personalizedproduct' ) 
		),
				
		'class' => array (
				'type' => 'text',
				'title' => __ ( 'Class', 'nm-personalizedproduct' ),
				'desc' => __ ( 'Insert an additional class(es) (separateb by comma) for more personalization.', 'nm-personalizedproduct' )
		),
		
		'width' => array (
				'type' => 'text',
				'title' => __ ( 'Width', 'nm-personalizedproduct' ),
				'desc' => __ ( 'Type field width in % e.g: 50%', 'nm-personalizedproduct' )
		),
		
		'required' => array (
				'type' => 'checkbox',
				'title' => __ ( 'Required', 'nm-personalizedproduct' ),
				'desc' => __ ( 'Select this if it must be required.', 'nm-personalizedproduct' ) 
		),
				
		'images' => array (
				'type' => 'pre-images',
				'title' => __ ( 'Select images', 'nm-personalizedproduct' ),
				'desc' => __ ( 'Select images from media library', 'nm-personalizedproduct' )
		),
				
		'multiple_allowed' => array (
				'type' => 'checkbox',
				'title' => __ ( 'Multiple selection?', 'nm-personalizedproduct' ),
				'desc' => __ ( 'Allow users to select more then one images?.', 'nm-personalizedproduct' )
		),
				
		'selected' => array (
				'type' => 'text',
				'title' => __ ( 'Selected image', 'nm-personalizedproduct' ),
				'desc' => __ ( 'Type option title (given above) if you want it already selected.', 'nm-personalizedproduct' )
		),
				
		'popup_width' => array (
				'type' => 'text',
				'title' => __ ( 'Popup width', 'nm-personalizedproduct' ),
				'desc' => __ ( 'Popup window width in px e.g: 750', 'nm-personalizedproduct' )
		),
		
		'popup_height' => array (
				'type' => 'text',
				'title' => __ ( 'Popup height', 'nm-personalizedproduct' ),
				'desc' => __ ( 'Popup window height in px e.g: 550', 'nm-personalizedproduct' )
		),
		
		'logic' => array (
				'type' => 'checkbox',
				'title' => __ ( 'Enable conditional logic', 'nm-personalizedproduct' ),
				'desc' => __ ( 'Tick it to turn conditional logic to work below', 'nm-personalizedproduct' )
		),
		'conditions' => array (
				'type' => 'html-conditions',
				'title' => __ ( 'Conditions', 'nm-personalizedproduct' ),
				'desc' => __ ( 'Tick it to turn conditional logic to work below', 'nm-personalizedproduct' )
		),
		);
	}
	
	
	/*
	 * @params: $options
	*/
	function render_input($args, $images = "", $default_selected = ""){
		
		// nm_personalizedproduct_pa($images);
		
		$_html = '<div class="pre_upload_image_box">';
			
		$img_index = 0;
		$popup_width	= $args['popup-width'] == '' ? 600 : $args['popup-width'];
		$popup_height	= $args['popup-height'] == '' ? 450 : $args['popup-height'];
		
		if ($images) {
			
			foreach ($images as $image){
					
				
				$_html .= '<div class="pre_upload_image">';
				if($image['id'] != ''){
					$_html .= '<img src="'.wp_get_attachment_thumb_url( $image['id'] ).'" />';
				}else{
					$_html .= '<img width="150" height="150" src="'.$image['link'].'" />';
				}
				
					
				// for bigger view
				$_html	.= '<div style="display:none" id="pre_uploaded_image_' . $args['id'].'-'.$img_index.'"><img style="margin: 0 auto;display: block;" src="' . $image['link'] . '" /></div>';
					
				$_html	.= '<div class="input_image">';
				if ($args['multiple-allowed'] == 'on') {
					$_html	.= '<input type="checkbox" data-price="'.$image['price'].'" data-title="'.stripslashes( $image['title'] ).'" name="'.$args['name'].'[]" value="'.esc_attr(json_encode($image)).'" />';
				}else{
					
					//default selected
					$checked = ($image['title'] == $default_selected ? 'checked = "checked"' : '' );
					$_html	.= '<input type="radio" data-price="'.$image['price'].'" data-title="'.stripslashes( $image['title'] ).'" data-type="'.stripslashes( $args['data-type'] ).'" name="'.$args['name'].'" value="'.esc_attr(json_encode($image)).'" '.$checked.' />';
				}
					
				
				$price = '';
				if(function_exists('woocommerce_price') && $image['price'] > 0)
					$price = woocommerce_price( $image['price'] );
				
				// image big view	 
				$_html	.= '<a href="#TB_inline?width='.$popup_width.'&height='.$popup_height.'&inlineId=pre_uploaded_image_' . $args['id'].'-'.$img_index.'" class="thickbox" title="' . $image['title'] . '"><img width="15" src="' . $this -> plugin_meta['url'] . '/images/zoom.png" /></a>';
				$_html	.= '<div class="p_u_i_name">'.stripslashes( $image['title'] ) . ' ' . $price . '</div>';
				$_html	.= '</div>';	//input_image
					
					
				$_html .= '</div>';
					
				$img_index++;
			}
		}
		
		$_html .= '<div style="clear:both"></div>';		//container_buttons
			
		$_html .= '</div>';		//container_buttons
		
		echo $_html;
		
		$this -> get_input_js($args);
	}
	
	
	/*
	 * following function is rendering JS needed for input
	*/
	function get_input_js($args){
		?>
			
					<script type="text/javascript">	
					<!--
					jQuery(function($){
	
						// pre upload image click selection
						/*$(".pre_upload_image").click(function(){

							if($(this).find('input:checkbox').attr("checked") === 'checked'){
								$(this).find('input:checkbox').attr("checked", false);
							}else{
								$(this).find('input:radio, input:checkbox').attr("checked", "checked");
							}

						});*/
						
					});
					
					//--></script>
					<?php
			}
}