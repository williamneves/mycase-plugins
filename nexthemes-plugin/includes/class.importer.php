<?php

if( !class_exists( 'Nexthemes_Importer' ) ) {
	class Nexthemes_Importer{

		private $data_key = array(
			'dummy'		=> 'nth_dummy_installed',
			'home_dmm'	=> 'nth_home_dummy',
			'rev'		=> 'nth_home_install_rev',
			'menu'		=> 'nth_home_install_menu',
			'reading'	=> 'nth_home_install_reading',
			'woo'		=> 'nth_cofig_woo_page',
			'widget'	=> 'nth_import_widgets',
			'home'		=> 'nth_home_importing',
			'imported'	=> 'nth_theme_imported',
			'current'	=> 'nth_theme_current'
		);

		private $_slider_download = "", $_download_dummy = '';
		private $_local_dummy = "";
		private $_local_dummy_uri = "";

		private $_bg_color = array();

		public function __construct(){
			$home_install = get_option($this->data_key['home']);

			$this->_slider_download = 'http://demo.nexthemes.com/wordpress/downloads/theshopier/sliders/';
			$this->_download_dummy = 'http://demo.nexthemes.com/wordpress/downloads/theshopier';
			$this->_local_dummy = get_template_directory() .'/framework/backend/incs/dummy';
			$this->_local_dummy_uri = get_template_directory_uri() . "/framework/backend/incs/dummy";

			$this->_bg_color = array(
				1 => array('color'	=> 'f6f6f6'),
				2 => array('color'	=> 'f7f7f7'),
				3 => array('color'	=> 'f2ecdf'),
				4 => array('color'	=> 'f6f6f6'),
				5 => array('color'	=> 'f6f6f6'),
				6 => array('color'	=> 'ffffff'),
				7 => array('color'	=> 'ffffff'),
				8 => array('color'	=> 'eeeeee'),
				9 => array('color'	=> 'ffffff'),
				10 => array('color'	=> 'ffffff'),
				11 => array('color'	=> 'ffffff'),
				12 => array(
					'color'	=> '93d8e3',
					'bg'	=> 'http://demo.nexthemes.com/wordpress/theshopier/home12/wp-content/uploads/2016/02/main-background.jpg'
				),
			);


			add_action('wp_ajax_nexthemes_import_dummy', array($this, 'import_main_dummy'));
			add_action('wp_ajax_nexthemes_import_home', array($this, 'import_home'));
			$this->resg_system();
		}

		private function resg_system(){
			global $wp_filesystem;
			if (empty($wp_filesystem)) {
				require_once (ABSPATH . '/wp-admin/includes/file.php');
				WP_Filesystem();
			}
		}

		public static function checkImportfiles($slug){
			$return = false;
			$dumm_fol = get_template_directory() ."/framework/backend/incs/dummy";
			$dumm_home = $dumm_fol . "/dumm_home/home{$slug}.xml";
			$redux_color = $dumm_fol . "/redux/color_options_{$slug}.xml";
			$redux_options = $dumm_fol . "/redux/redux_options_{$slug}.json";
			$widgets = $dumm_fol . "/widgets/widget_home{$slug}.wie";
			$image = get_template_directory() ."/framework/backend/images/imports/homepage-iwrap{$slug}.jpg";
			if( file_exists($dumm_home) && file_exists($redux_color) && file_exists($redux_options) && file_exists($widgets) && file_exists($image) ) $return = true;
			return $return;
		}

		public static function getThemeHomepages(){
			global $wp_filesystem;
			$import_arr = array();
			$homes_json_file = get_template_directory() .'/framework/backend/incs/dummy/home_args.json';
			if(file_exists($homes_json_file)) {
				$home_obj_str = file_get_contents($homes_json_file);
				if ( ! empty ( $home_obj_str ) ) $import_arr = json_decode($home_obj_str, true);
				if( !empty($import_arr['req_plugins']) ) {
					foreach($import_arr['req_plugins'] as $k => $plugin){
						if( $info = NexThemes_Plg::checkPlugin($plugin['path'], 'info') ) {
							$import_arr['req_plugins'][$k]['name'] = $info['Name'];
							$import_arr['req_plugins'][$k]['status'] = true;
						} else {
							$import_arr['req_plugins'][$k]['status'] = false;
						}
					}
				} else $import_arr = array();
			}

			return $import_arr;
		}

		public static function checkContention(){
			$fp = @fsockopen("108.61.207.13", 80, $errno, $errstr, 30);
			if (!$fp) {
				return false;
			} else {
				@fclose($fp);
				return true;
			}
		}

		public function update_options($key, $val){
			$data = get_option($key);
			if(isset($data)) update_option($key, $val);
			else add_option($key, $val);
		}

		public function wpImport(){
			$res = true;
			if ( !class_exists( 'WP_Importer' ) ) {
				$class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
				if ( file_exists( $class_wp_importer ) ) {
					include $class_wp_importer;
				} else {
					$res = false;
				}
			}
			if( !class_exists('WP_Import') ) {
				$class_wp_import = dirname( __FILE__ ) . '/wordpress-importer.php';
				if( file_exists($class_wp_import) ) include $class_wp_import;
				else $res = false;
			}

			return $res;
		}

		public function import_dummy( $dummy_xml ){
			if ( !current_user_can( 'manage_options' ) ) return 'permission denied';

			if ( !defined('WP_LOAD_IMPORTERS') ) define('WP_LOAD_IMPORTERS', true);

			$lib = $this->wpImport();

			if( !$lib ) {
				return "The Auto importing script could't be loaded. Please use the wordpress importer and import the XML file.";
			} elseif( file_exists($dummy_xml) ) {
				$importer = new WP_Import();
				$importer->fetch_attachments = true;
				ob_start();
				$importer->import($dummy_xml);
				return ob_end_clean();
			} else {
				return $dummy_xml . ' not exist!';
			}

		}

		private function available_widgets(){
			global $wp_registered_widget_controls;
			$widget_controls = $wp_registered_widget_controls;
			$available_widgets = array();
			foreach ( $widget_controls as $widget ) {
				if ( !empty($widget['id_base']) && !isset( $available_widgets[$widget['id_base']] )) {
					$available_widgets[$widget['id_base']]['id_base'] = $widget['id_base'];
					$available_widgets[$widget['id_base']]['name'] = $widget['name'];
				}
			}

			return $available_widgets;
		}

		public function import_main_dummy(){
			check_ajax_referer('__THEME_IMPORT_5362', 'security');
			set_time_limit(0);

			if( !$this->checkContention() ) wp_die('connect_error');

			if( NexThemes_Plg::checkPlugin('wordpress-importer/wordpress-importer.php') ) {
				wp_die('wp_import_exist');
			}

			$dummy_installed = get_option($this->data_key['dummy']);
			if( absint($dummy_installed) ) wp_die('installed');
			$dummy_folder = get_template_directory() ."/framework/backend/incs/dummy";
			$dummy_xml = $dummy_folder . "/dummy_data.xml";

			if( NexThemes_Plg::checkPlugin('woocommerce/woocommerce.php')) {
				$woopage = get_page_by_title( 'shop' );
				if( !isset( $woopage->ID ) || absint($woopage->ID) <= 0 ) {
					$dummy_xml = $dummy_folder . "/dummy_data_woo.xml";
				}
			}
			$this->update_options($this->data_key['dummy'], 1);
			echo $this->import_dummy($dummy_xml);

			wp_die();
		}

		public function import_home_dummy($home = 1){
			$page_title = 'Home page '. $home;
			$homepage = get_page_by_title( $page_title );
			if( !isset( $homepage ) || !$homepage->ID ){
				$xml_file = $this->_local_dummy ."/dumm_home/home{$home}.xml";
				if(strlen($xml_file) > 0) {
					return $this->import_dummy($xml_file);
				}
			}
		}

		public function clearWidgets(){
			$sidebars_widgets = get_option( 'sidebars_widgets' );
			foreach($sidebars_widgets as $k => $v){
				$sidebars_widgets[$k] = array();
			}

			update_option( 'sidebars_widgets', $sidebars_widgets );
		}

		public function import_widget( $home = 1 ){
			global $wp_registered_sidebars;
			switch( absint($home) ) {
				default:
					$file_num = $home;
			}
			$file = get_template_directory() ."/framework/backend/incs/dummy/widgets/widget_home{$file_num}.wie";
			$data = file_get_contents($file);
			$data = json_decode( $data );
			if ( empty( $data ) || ! is_object( $data ) ) {
				return "Import widgets data could not be read. Please contact to support team.";
			}

			$this->clearWidgets();

			$available_widgets = $this->available_widgets();

			$widget_instances = array();
			foreach ( $available_widgets as $widget_data ) {
				$widget_instances[$widget_data['id_base']] = get_option( 'widget_' . $widget_data['id_base'] );
			}

			$results = array();
			foreach ( $data as $sidebar_id => $widgets ) {
				// Skip inactive widgets
				// (should not be in export file)
				if ( 'wp_inactive_widgets' == $sidebar_id ) {
					continue;
				}

				// Check if sidebar is available on this site
				// Otherwise add widgets to inactive, and say so
				if ( isset( $wp_registered_sidebars[$sidebar_id] ) ) {
					$sidebar_available = true;
					$use_sidebar_id = $sidebar_id;
					$sidebar_message_type = 'success';
					$sidebar_message = '';
				} else {
					$sidebar_available = false;
					$use_sidebar_id = 'wp_inactive_widgets'; // add to inactive if sidebar does not exist in theme
					$sidebar_message_type = 'error';
					$sidebar_message = __( 'Sidebar does not exist in theme (using Inactive)', 'nexthemes-plugin' );
				}

				// Result for sidebar
				$results[$sidebar_id]['name'] = ! empty( $wp_registered_sidebars[$sidebar_id]['name'] ) ? $wp_registered_sidebars[$sidebar_id]['name'] : $sidebar_id; // sidebar name if theme supports it; otherwise ID
				$results[$sidebar_id]['message_type'] = $sidebar_message_type;
				$results[$sidebar_id]['message'] = $sidebar_message;
				$results[$sidebar_id]['widgets'] = array();

				// Loop widgets
				foreach ( $widgets as $widget_instance_id => $widget ) {
					$fail = false;
					// Get id_base (remove -# from end) and instance ID number
					$id_base = preg_replace( '/-[0-9]+$/', '', $widget_instance_id );
					$instance_id_number = str_replace( $id_base . '-', '', $widget_instance_id );

					// Does site support this widget?
					if ( ! $fail && ! isset( $available_widgets[$id_base] ) ) {
						$fail = true;
						$widget_message_type = 'error';
						$widget_message = __( 'Site does not support widget', 'nexthemes-plugin' ); // explain why widget not imported
					}

					$widget = apply_filters( 'wie_widget_settings', $widget ); // object

					$widget = json_decode( json_encode( $widget ), true );
					$widget = apply_filters( 'wie_widget_settings_array', $widget );

					if ( ! $fail && isset( $widget_instances[$id_base] ) ) {
						$sidebars_widgets = get_option( 'sidebars_widgets' );
						$sidebar_widgets = isset( $sidebars_widgets[$use_sidebar_id] ) ? $sidebars_widgets[$use_sidebar_id] : array(); // check Inactive if that's where will go

						$single_widget_instances = ! empty( $widget_instances[$id_base] ) ? $widget_instances[$id_base] : array();
						foreach ( $single_widget_instances as $check_id => $check_widget ) {
							if ( in_array( "$id_base-$check_id", $sidebar_widgets ) && (array) $widget == $check_widget ) {
								$fail = true;
								$widget_message_type = 'warning';
								$widget_message = __( 'Widget already exists', 'nexthemes-plugin' ); // explain why widget not imported

								break;
							}
						}

					}

					if ( ! $fail ) {
						// Add widget instance
						$single_widget_instances = get_option( 'widget_' . $id_base ); // all instances for that widget ID base, get fresh every time
						$single_widget_instances = ! empty( $single_widget_instances ) ? $single_widget_instances : array( '_multiwidget' => 1 ); // start fresh if have to
						$single_widget_instances[] = $widget; // add it

						// Get the key it was given
						end( $single_widget_instances );
						$new_instance_id_number = key( $single_widget_instances );

						// If key is 0, make it 1
						// When 0, an issue can occur where adding a widget causes data from other widget to load, and the widget doesn't stick (reload wipes it)
						if ( '0' === strval( $new_instance_id_number ) ) {
							$new_instance_id_number = 1;
							$single_widget_instances[$new_instance_id_number] = $single_widget_instances[0];
							unset( $single_widget_instances[0] );
						}

						// Move _multiwidget to end of array for uniformity
						if ( isset( $single_widget_instances['_multiwidget'] ) ) {
							$multiwidget = $single_widget_instances['_multiwidget'];
							unset( $single_widget_instances['_multiwidget'] );
							$single_widget_instances['_multiwidget'] = $multiwidget;
						}

						// Update option with new widget
						update_option( 'widget_' . $id_base, $single_widget_instances );

						// Assign widget instance to sidebar
						$sidebars_widgets = get_option( 'sidebars_widgets' ); // which sidebars have which widgets, get fresh every time
						$new_instance_id = $id_base . '-' . $new_instance_id_number; // use ID number from new widget instance
						$sidebars_widgets[$use_sidebar_id][] = $new_instance_id; // add new instance to sidebar
						update_option( 'sidebars_widgets', $sidebars_widgets ); // save the amended data

						// After widget import action
						$after_widget_import = array(
							'sidebar'           => $use_sidebar_id,
							'sidebar_old'       => $sidebar_id,
							'widget'            => $widget,
							'widget_type'       => $id_base,
							'widget_id'         => $new_instance_id,
							'widget_id_old'     => $widget_instance_id,
							'widget_id_num'     => $new_instance_id_number,
							'widget_id_num_old' => $instance_id_number
						);
						do_action( 'wie_after_widget_import', $after_widget_import );

						// Success message
						if ( $sidebar_available ) {
							$widget_message_type = 'success';
							$widget_message = __( 'Imported', 'nexthemes-plugin' );
						} else {
							$widget_message_type = 'warning';
							$widget_message = __( 'Imported to Inactive', 'nexthemes-plugin' );
						}

					}

					// Result for widget instance
					$results[$sidebar_id]['widgets'][$widget_instance_id]['name'] = isset( $available_widgets[$id_base]['name'] ) ? $available_widgets[$id_base]['name'] : $id_base; // widget name or ID if name not available (not supported by site)
					$results[$sidebar_id]['widgets'][$widget_instance_id]['title'] = ! empty( $widget['title'] ) ? $widget['title'] : __( 'No Title', 'nexthemes-plugin' ); // show "No Title" if widget instance is untitled
					$results[$sidebar_id]['widgets'][$widget_instance_id]['message_type'] = $widget_message_type;
					$results[$sidebar_id]['widgets'][$widget_instance_id]['message'] = $widget_message;

				}
			}

			return "Widgets imported to Inactive!";

		}

		public function import_redux( $home = 1 ){
			global $theshopier_datas;
			$file_num = $home;
			$res = array();

			$color_file = $this->_local_dummy ."/redux/color_options_{$file_num}.xml";
			if(!file_exists($color_file)) $res['msg'] = "color_options_{$file_num}.xml not exist!";
			else {
				$new_file = get_template_directory() ."/framework/incs/redux-framework/xml/color_options.xml";
				if( file_exists($new_file) ) @unlink($new_file);
				@copy($color_file, $new_file);
				$res['msg'] = "color_options_{$file_num}.xml imported!";

			}

			switch( absint($home) ) {
				case 1: case 2: case 3: case 4: case 5: case 6: case 7:
				$font_file = $this->_local_dummy . "/redux/font_options.xml";
				break;
				default:
					$font_file = $this->_local_dummy . "/redux/font_options_{$home}.xml";
			}

			if(!file_exists($font_file)) $res['msg'] = "{$font_file} not exist!";
			else {
				$new_font_file = get_template_directory() ."/framework/incs/redux-framework/xml/font_options.xml";
				if( file_exists($new_font_file) ) @unlink($new_font_file);
				@copy($font_file, $new_font_file);
				$res['msg'] = "{$font_file} imported!";

			}

			switch( absint($home) ) {
				default:
					$import_url = $this->_local_dummy_uri . "/redux/redux_options_{$file_num}.json";
			}

			if( strlen($import_url) > 0 ) {
				$import = wp_remote_retrieve_body( wp_remote_get( $import_url ) );
				if ( ! empty ( $import ) ) $im_import = json_decode($import, true);
				else $im_import = $theshopier_datas;
				update_option('theshopier_datas', $im_import);
				$res['msg'] .= 'Redux options imported!';
			}

			return $res;
		}

		public function _download_slider( $slider_name ){
			global $wp_filesystem;
			$tmp_path = $this->_local_dummy . "/sliders/";
			if( !is_dir($tmp_path) ) {
				try{
					@mkdir($tmp_path, 0755, true);
				} catch(Exception $e) {
					return false;
				}
			}
			$_request = wp_remote_post( $this->_slider_download . "{$slider_name}.zip", array('timeout' => 45));
			if( isset($_request['response']) && strcmp(trim($_request['response']['code']), '200') == 0) {
				$_downslider_data = wp_remote_retrieve_body( $_request );
				if( !empty($_downslider_data) ) {
					if( file_put_contents( $tmp_path . "{$slider_name}.zip", $_downslider_data) ) {
						return $tmp_path . "{$slider_name}.zip";
					} else {
						return false;
					}
				} else {
					return false;
				}
			} else {
				return false;
			}
		}

		public function import_revolution($home){
			ob_start();
			$res = array(
				'status' => true,
				'msg'	 => ''
			);
			if( class_exists('UniteFunctionsRev') && class_exists('ZipArchive') ) {
				$updateAnim = true;
				$updateStatic= true;
				$slider_keys = array(
					"home-slider-{$home}", "product_catalog"
				);

				$slider = new RevSlider();

				foreach($slider_keys as $s_key){
					if($slider->isAliasExists($s_key)) continue;

					$exactfilepath = $this->_local_dummy ."/sliders/{$s_key}.zip";
					if( !file_exists($exactfilepath) ) $exactfilepath = $this->_download_slider( $s_key );
					if( $exactfilepath == false ) {
						if( strcmp($s_key, "home-slider-{$home}") === 0 ) $res['status'] = false;
						continue;
					}
					$response = $slider->importSliderFromPost($updateAnim, $updateStatic, $exactfilepath);

					if($response["success"] == false){
						if( strcmp($s_key, "home-slider-{$home}") === 0 ) $res['status'] = false;
						$message = $response["error"];
						$res['msg'] .= "<b>Error: ".$message."</b>";
					}else {    //handle success, js redirect.
						$res['msg'] .= __("Main Slider Import Success!", 'nexthemes-plugin');
					}
				}

			} else {
				$res['msg'] .= __("Not found Revolution plugin", 'nexthemes-plugin');
			}
			$rev_msg = ob_get_clean();
			//$this->update_options($this->data_key['rev'], 1);
			return $res;
		}

		public function config_menu(){
			$locations = get_theme_mod( 'nav_menu_locations' );
			$menus = wp_get_nav_menus();
			if( $menus ) {
				foreach($menus as $menu) {
					switch($menu->name) {
						case 'Main menu':
							$locations['primary-menu'] = $menu->term_id;
							break;
						case 'Vertical menu':
							$locations['vertical-menu'] = $menu->term_id;
							break;
						case 'Mobile menu':
							$locations['mobile-menu'] = $menu->term_id;
							break;
					}
				}
				$res = '<p>Menu setting complete!</p>';
			} else {
				$res = "<p>Menu hadn't been created!!</p>";
			}
			set_theme_mod( 'nav_menu_locations', $locations );
			return $res;
		}

		public function update_setting_reading( $h = 1){
			$page_title = 'Home page '. $h;
			$home = get_page_by_title( $page_title );
			if( isset( $home ) && $home->ID ){
				update_option('show_on_front', 'page');
				update_option('page_on_front', $home->ID);
				update_option('posts_per_page', 5);
				update_option('posts_per_rss', 5);

				$page_conf = @unserialize(get_post_meta($home->ID, 'theshopier_page_options'));

				switch( absint($h) ) {
					case 2:
					case 3:
					case 5:
					case 6:
					case 8:
					case 11:
					case 12:
						$page_conf['nth_slider_type'] = '';
						break;
					default:
						$page_conf['nth_slider_type'] = 'revolution';
						$page_conf['nth_rev_slider'] = 'home-slider-'.$h;
				}
				update_post_meta( $home->ID, 'theshopier_page_options', serialize( $page_conf ) );
			}

			$bg_color = isset($this->_bg_color[$h])? esc_attr($this->_bg_color[$h]['color']): '#f6f6f6';
			set_theme_mod('background_color', $bg_color);
			if(isset($this->_bg_color[$h]['bg']) && strlen($this->_bg_color[$h]['bg']) > 0) {
				set_theme_mod('background_image', esc_url($this->_bg_color[$h]['bg']));
			} else {
				remove_theme_mod('background_image');
			}

			$this->update_options('yith_woocompare_compare_button_in_products_list', 'yes');

			$this->update_options($this->data_key['reading'], 1);
			return "<p>Reading update complete!</p>";
		}

		public function config_woo_page(){
			$_actived = NexThemes_Plg::checkPlugin("woocommerce/woocommerce.php");
			if( $_actived ) {
				update_option( 'shop_catalog_image_size', array(
					'width'		=> '280',
					'height'	=> '315',
					'crop'		=> 1
				) );

				update_option( 'shop_single_image_size', array(
					'width'		=> '480',
					'height'	=> '540',
					'crop'		=> 1
				) );

				update_option( 'shop_thumbnail_image_size', array(
					'width'		=> '70',
					'height'	=> '78',
					'crop'		=> 1
				) );

				$woopages = array(
					'woocommerce_shop_page_id' 			=> 'Shop'
				,'woocommerce_cart_page_id' 		=> 'Cart'
				,'woocommerce_checkout_page_id' 	=> 'Checkout'
				,'woocommerce_myaccount_page_id' 	=> 'My Account'
				);
				foreach( $woopages as $page_id => $page_title ) {
					$woopage = get_page_by_title( $page_title );
					if( isset( $woopage->ID ) && $woopage->ID ) {
						update_option($page_id, $woopage->ID);
					}
				}

				$this->update_options('woocommerce_enable_myaccount_registration', 'yes');

				delete_transient( '_wc_activation_redirect' );

				flush_rewrite_rules();
			}
		}

		public function checkOption( $key ){
			$check = get_option($key, 0);
			return absint($check);
		}

		public function check_requestPlugins($home){
			$import_arrs = Nexthemes_Importer::getThemeHomepages();
			$homepages = !empty($import_arrs['homepages'])? $import_arrs['homepages']: array();
			$req_plugins = !empty($import_arrs['req_plugins'])? $import_arrs['req_plugins']: array();
			foreach( $homepages[$home]['pl_request'] as  $pl_path) {
				if( $req_plugins[$pl_path]['status'] !== true ) return false;
			}
			return true;
		}

		public function import_home(){
			check_ajax_referer('__THEME_IMPORT_5362', 'security');
			$home = $_REQUEST['home'];

			$res = array('msg' => '', 'status'	=> 0);

			if( !$this->checkContention() )  {
				$res['status'] = 'connect_error';
				wp_send_json($res);
			}

			if( NexThemes_Plg::checkPlugin('wordpress-importer/wordpress-importer.php') ) {
				$res['status'] = 'wp_import_exist';
				wp_send_json($res);
			}

			if( !$this->check_requestPlugins($home) ) {
				$res['status'] = 'res_plugin_error';
				wp_send_json($res);
			}

			$imported = @unserialize(get_option($this->data_key['imported'], array()));

			$revolution = $this->import_revolution($home);
			$res['msg']  .=  $revolution['msg'];
			if( $revolution['status'] == false ) {
				$res['status'] = 'rev_error';
				wp_send_json($res);
			}

			$res['msg'] .= $this->import_home_dummy( $home );

			$res['msg'] .= $this->import_widget($home);

			$res['msg'] .=  $this->config_woo_page();

			$res['msg'] .=  $this->config_menu();

			$redux_imp = $this->import_redux( $home );

			$res['msg'] .= $redux_imp['msg'];

			$res['msg'] .=  $this->update_setting_reading($home);

			if(!is_array($imported)) $imported = array();

			$imported[] = $home;
			$this->update_options($this->data_key['imported'], maybe_serialize($imported));

			$this->update_options($this->data_key['current'], $home);

			echo wp_json_encode($res);

			wp_die();
		}

	}

	new Nexthemes_Importer();
}