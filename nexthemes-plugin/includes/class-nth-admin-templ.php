<?php 

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( ! class_exists( 'Nexthemes_Admin_Templ' ) ) {
	
	class Nexthemes_Admin_Templ{
		
		public static function register_settings(){
			
		}
		
		public static function create_fields( $ops = array(), $options = array() ){
			foreach( $ops as $k => $v ) {
				if( !isset($v['type']) ) continue;
				switch( $v['type'] ){
					case 'select':
						break;
					default:
						$output = '<input type="text" name="'.esc_attr($k).'" id="'.esc_attr($k).'" />';
				}
				?>
				<tr valign="top">
					<th scope="row"><label for="<?php echo esc_attr($k);?>"><?php echo esc_html($v['label']);?></label></th>
					<td class="forminp forminp-text">
						<?php echo $output;?>
					</td>
				</tr>
				<?php 
			}
		}
		
	}
	
}
