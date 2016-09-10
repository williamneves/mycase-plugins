<?php
/*
 * rendering product meta on product page
 */
global $nmpersonalizedproduct, $product;

$single_form = $nmpersonalizedproduct -> get_product_meta ( $nmpersonalizedproduct -> productmeta_id );
//nm_personalizedproduct_pa( $single_form );

$existing_meta = json_decode ( $single_form -> the_meta, true );

if ($existing_meta) {
//pasting the custom css if used in form settings	
if ( $single_form -> productmeta_style != '') {
	echo '<style>';
		echo '.related.products .amount-options { display:none; }';

        //added on September 2, 2014
        echo '.upsells .amount-options { display:none; }';
		echo stripslashes(strip_tags( $single_form -> productmeta_style ));
	echo '</style>';
}


	echo '<div id="nm-productmeta-box-' . $nmpersonalizedproduct -> productmeta_id . '" class="nm-productmeta-box">';
	echo '<input type="hidden" name="woo_option_price">';	// it will be populated while dynamic prices set in script.js
	echo '<input type="hidden" id="_product_price" value="'.$product->get_price().'">';	// it is setting price to be used for dymanic prices in script.js
	echo '<input type="hidden" id="_productmeta_id" value="'.$nmpersonalizedproduct -> productmeta_id.'">';
	echo '<input type="hidden" id="_product_id" value="'.$product->id.'">';
	
	echo '<input type="hidden" name="woo_onetime_fee">';	// it will be populated while dynamic prices set in script.js
	echo '<input type="hidden" name="woo_file_cost">';	// to hold the file cost
	
	$row_size = 0;
	
	$started_section = '';
	
	foreach ( $existing_meta as $key => $meta ) {
		
		$type = $meta ['type'];
		
		$name = strtolower ( preg_replace ( "![^a-z0-9]+!i", "_", $meta ['data_name'] ) );
		
		// conditioned elements
		$visibility = '';
		$conditions_data = '';
		if (isset( $meta['logic'] ) && $meta['logic'] == 'on') {
		
			if($meta['conditions']['visibility'] == 'Show')
				$visibility = 'display: none';
		
			$conditions_data	= 'data-rules="'.esc_attr( json_encode($meta['conditions'] )).'"';
		}
		
		if (($row_size + intval ( $meta ['width'] )) > 100 || $type == 'section') {
			
			echo '<div style="clear:both; margin: 0;"></div>';
			
			if ($type == 'section') {
				$row_size = 100;
			} else {
				
				$row_size = intval ( $meta ['width'] );
			}
		} else {
			
			$row_size += intval ( $meta ['width'] );
		}
		
		$show_asterisk = (isset( $meta ['required'] ) && $meta ['required']) ? '<span class="show_required"> *</span>' : '';
		$show_description = ($meta ['description']) ? '<span class="show_description"> ' . stripslashes ( $meta ['description'] ) . '</span>' : '';
		
		$the_width = intval ( $meta ['width'] ) - 1 . '%';
		$the_margin = '1%';
		
		$field_label = stripslashes( $meta ['title'] ) . $show_asterisk . $show_description;
		
		$required = ( isset( $meta['required'] ) ? $meta['required'] : '' );
		$error_message = ( isset( $meta['error_message'] ) ? $meta['error_message'] : '' );
		
		$args = '';
			
		switch ($type) {

			case 'text':
					
					$args = array(	'name'			=> $name,
									'id'			=> $name,
									'data-type'		=> $type,
									'data-req'		=> $required,
									'data-message'	=> $error_message,
									'maxlength'	=> $meta['max_length'],
									);
					echo '<div id="box-'.$name.'" style="width: '. $the_width.'; margin-right: '. $the_margin.';'.$visibility.'" '.$conditions_data.'>';
					printf( __('<label for="%1$s">%2$s</label><br />', 'nm-personalizedproduct'), $name, $field_label );
					
					$data_default = ( isset( $meta['default_value'] ) ? $meta['default_value'] : '');
					$nmpersonalizedproduct -> inputs[$type]	-> render_input($args, $data_default);					
					
					//for validtion message
					echo '<span class="errors"></span>';
					echo '</div>';
					break;
					
				
					
				
				case 'textarea':
				
					$args = array(	'name'			=> $name,
							'id'			=> $name,
							'data-type'		=> $type,
							'data-req'		=> $required,
							'data-message'	=> $error_message,
							'maxlength'	=> $meta['max_length'],
							'minlength'	=> $meta['min_length']);
					
					echo '<div id="box-'.$name.'" style="width: '. $the_width.'; margin-right: '. $the_margin.';'.$visibility.'" '.$conditions_data.'>';
					printf( __('<label for="%1$s">%2$s</label><br />', 'nm-personalizedproduct'), $name, $field_label );
					
					$data_default = ( isset( $meta['default_value'] ) ? $meta['default_value'] : '');
					$nmpersonalizedproduct -> inputs[$type]	-> render_input($args, $data_default);				
					//for validtion message
					echo '<span class="errors"></span>';
					echo '</div>';
					break;
					
					
				case 'select':
				
					$default_selected = (isset( $meta['selected'] ) ? $meta['selected'] : '' );
					$data_onetime = (isset( $meta['onetime'] ) ? $meta['onetime'] : '' );
					$data_onetime_taxable = (isset( $meta['onetime_taxable'] ) ? $meta['onetime_taxable'] : '' );
					
					$args = array(	'name'			=> $name,
									'id'			=> $name,
									'data-type'		=> $type,
									'data-req'		=> $required,
									'data-message'		=> $error_message,
									'data-onetime'		=> $data_onetime,
									'data-onetime-taxable'	=> $data_onetime_taxable);
				
					echo '<div id="box-'.$name.'" style="width: '. $the_width.'; margin-right: '. $the_margin.';'.$visibility.'" '.$conditions_data.'>';
					printf( __('<label for="%1$s">%2$s</label><br />', 'nm-personalizedproduct'), $name, $field_label );
					
					$nmpersonalizedproduct -> inputs[$type]	-> render_input($args, $meta['options'], $default_selected);
				
					//for validtion message
					echo '<span class="errors"></span>';
					echo '</div>';
					break;
						
				case 'radio':
					$default_selected = $meta['selected'];
					$data_onetime = (isset( $meta['onetime'] ) ? $meta['onetime'] : '' );
					$data_onetime_taxable = (isset( $meta['onetime_taxable'] ) ? $meta['onetime_taxable'] : '' );
					
					$args = array(	'name'			=> $name,
									'data-type'		=> $type,
									'data-req'		=> $required,
									'data-message'		=> $error_message,
									'data-onetime'		=> $data_onetime,
									'data-onetime-taxable'	=> $data_onetime_taxable);
				
					echo '<div id="box-'.$name.'" style="width: '. $the_width.'; margin-right: '. $the_margin.';'.$visibility.'" '.$conditions_data.'>';
					printf( __('<label for="%1$s">%2$s</label><br />', 'nm-personalizedproduct'), $name, $field_label );
					
					$nmpersonalizedproduct -> inputs[$type]	-> render_input($args, $meta['options'], $default_selected);
					//for validtion message
					echo '<span class="errors"></span>';
					echo '</div>';
					break;
		
				
		}
	}
	
	echo '<div style="clear: both"></div>';
	
	echo '</div>'; // ends nm-productmeta-box
}

if( $single_form -> productmeta_validation == 'yes'){	//enable ajax based validation
?>
<script type="text/javascript">
	<!--
	jQuery(function($){
		
		//updating nm_personalizedproduct_vars.settings
		nm_personalizedproduct_vars.settings = {dynamic_price_hide: '<?php echo $single_form->dynamic_price_hide;?>'};
		$(".nm-productmeta-box").closest('form').find('button').click(function(event)
		  {
		    event.preventDefault(); // cancel default behavior
		
		    if( validate_cart_data() ){
		    	$(this).closest('form').submit();
		    }
		  });
	});
	
	function validate_cart_data(){
	
	var form_data = jQuery.parseJSON( '<?php echo stripslashes($single_form -> the_meta);?>' );
	var has_error = true;
	var error_in = '';
	
	jQuery.each( form_data, function( key, meta ) {
		
		var type = meta['type'];
		var error_message	= stripslashes( meta['error_message'] );
		//console.log('err message '+error_message+' id '+meta['data_name']);
		
		error_message = (error_message === '') ? nm_personalizedproduct_vars.default_error_message : error_message;
		
		if(type === 'text' || type === 'textarea' || type === 'select' || type === 'email' || type === 'date'){
			
			var input_control = jQuery('#'+meta['data_name']);
			
			if(meta['required'] === "on" && jQuery(input_control).val() === '' && jQuery(input_control).closest('div').css('display') != 'none'){
				jQuery(input_control).closest('div').find('span.errors').html(error_message).css('color', 'red');
				has_error = false;
				error_in = meta['data_name']
			}else{
				jQuery(input_control).closest('div').find('span.errors').html('').css({'border' : '','padding' : '0'});
			}
		}else if(type === 'checkbox'){
			
			if(meta['required'] === "on" && jQuery('input:checkbox[name="'+meta['data_name']+'[]"]:checked').length === 0 && jQuery('input:checkbox[name="'+meta['data_name']+'[]"]').closest('div').css('display') != 'none'){
				
				jQuery('input:checkbox[name="'+meta['data_name']+'[]"]').closest('div').find('span.errors').html(error_message).css('color', 'red');
				has_error = false;
			}else if(meta['min_checked'] != '' && jQuery('input:checkbox[name="'+meta['data_name']+'[]"]:checked').length < meta['min_checked']){
				jQuery('input:checkbox[name="'+meta['data_name']+'[]"]').closest('div').find('span.errors').html(error_message).css('color', 'red');
				has_error = false;
			}else if(meta['max_checked'] != '' && jQuery('input:checkbox[name="'+meta['data_name']+'[]"]:checked').length > meta['max_checked']){
				jQuery('input:checkbox[name="'+meta['data_name']+'[]"]').closest('div').find('span.errors').html(error_message).css('color', 'red');
				has_error = false;
			}else{
				
				jQuery('input:checkbox[name="'+meta['data_name']+'[]"]').closest('div').find('span.errors').html('').css({'border' : '','padding' : '0'});
				
				}
		}else if(type === 'radio'){
				
				if(meta['required'] === "on" && jQuery('input:radio[name="'+meta['data_name']+'"]:checked').length === 0 && jQuery('input:radio[name="'+meta['data_name']+'[]"]').closest('div').css('display') != 'none'){
					jQuery('input:radio[name="'+meta['data_name']+'"]').closest('div').find('span.errors').html(error_message).css('color', 'red');
					has_error = false;
					error_in = meta['data_name']
				}else{
					jQuery('input:radio[name="'+meta['data_name']+'"]').closest('div').find('span.errors').html('').css({'border' : '','padding' : '0'});
				}
		}else if(type === 'file'){
			
				var $upload_box = jQuery('#nm-uploader-area-'+meta['data_name']);
				var $uploaded_files = $upload_box.find('input:checkbox:checked');
				if(meta['required'] === "on" && $uploaded_files.length === 0 && $upload_box.css('display') != 'none'){
					$upload_box.find('span.errors').html(error_message).css('color', 'red');
					has_error = false;
					error_in = meta['data_name']
				}else{
					$upload_box.find('span.errors').html('').css({'border' : '','padding' : '0'});
				}
		}else if(type === 'image'){
			
			var $image_box = jQuery('#pre-uploaded-images-'+meta['data_name']);
			if(meta['required'] === "on" && jQuery('input:radio[name="'+meta['data_name']+'"]:checked').length === 0 && jQuery('input:checkbox[name="'+meta['data_name']+'[]"]:checked').length === 0 && $image_box.css('display') != 'none'){
				$image_box.find('span.errors').html(error_message).css('color', 'red');
				has_error = false;
				error_in = meta['data_name']
			}else{
				$image_box.find('span.errors').html('').css({'border' : '','padding' : '0'});
			}
		}else if(type === 'masked'){
			
			var input_control = jQuery('#'+meta['data_name']);
			
			if(meta['required'] === "on" && (jQuery(input_control).val() === '' || jQuery(input_control).attr('data-ismask') === 'no') && jQuery(input_control).closest('div').css('display') != 'none'){
				jQuery(input_control).closest('div').find('span.errors').html(error_message).css('color', 'red');
				has_error = false;
				error_in = meta['data_name'];
			}else{
				jQuery(input_control).closest('div').find('span.errors').html('').css({'border' : '','padding' : '0'});
			}
		}
		
	});
	
	//console.log( error_in ); return false;
	return has_error;
}
	//-->
</script>

<?php
}	//ending if() ajax based validation
