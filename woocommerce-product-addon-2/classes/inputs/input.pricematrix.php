<?php
/*
 * Followig class handling price matrix based on quantity provied in range
 * like 1-25
* dependencies. Do not make changes in code
* Create on: 9 November, 2013
*/

class NM_PriceMatrix_wooproduct extends NM_Inputs_wooproduct{
	
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
		
		$this -> title 		= __ ( 'Price Matrix', 'nm-personalizedproduct' );
		$this -> desc		= __ ( 'Price/Quantity', 'nm-personalizedproduct' );
		$this -> settings	= self::get_settings();
		$this -> ispro 		= true;
	}
	
	
	
	
	private function get_settings(){
		
		return array (
				
						'title' => array (
								'type' => 'text',
								'title' => __ ( 'Title', 'nm-personalizedproduct' ),
								'desc' => __ ( 'It will as section heading wrapped in h2', 'nm-personalizedproduct' )
						),
						'description' => array (
								'type' => 'textarea',
								'title' => __ ( 'Description', 'nm-personalizedproduct' ),
								'desc' => __ ( 'Type description, it will be diplay under section heading.', 'nm-personalizedproduct' )
						),
						'options' => array (
								'type' => 'paired',
								'title' => __ ( 'Price matrix', 'nm-personalizedproduct' ),
								'desc' => __ ( 'Type quantity range with price', 'nm-personalizedproduct' )
						),
						
						
				);
	}
	
	
	/*
	 * @params: args
	*/
	function render_input($args, $ranges){

		$_html = '<input name="_pricematrix" id="_pricematrix" type="hidden" value="'.esc_attr( json_encode($ranges)).'" />';

		$_html .= '<p id="box-'.$name.'">'. stripslashes( $args['description']).'</p>';
		
		foreach($ranges as $opt)
		{
			$_html .= '<div style="clear:both;border-bottom:1px #ccc dashed;">';
			$_html .= '<span>'.stripslashes(trim($opt['option'])).'</span>';
			$_html .= '<span style="float:right">'.woocommerce_price(trim($opt['price'])).'</span>';
			$_html .= '</div>';
		}

		echo $_html;
	}
}