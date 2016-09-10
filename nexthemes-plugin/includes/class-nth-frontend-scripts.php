<?php 

if ( ! defined( 'ABSPATH' ) ) exit;

if( ! class_exists( 'Nexthemes_Frontend_Scripts' ) ) {
	
	class Nexthemes_Frontend_Scripts {
	
		public function __construct(){
			
		}
		
		public static function init(){
			add_action( 'wp_print_scripts', array( __CLASS__, 'localize_printed_scripts' ), 5 );
			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'load_scripts' ) );
			
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_load_scripts' ) );
		}
		
		public static function load_scripts(){
			
			wp_register_script( 'isotope.min', NEXTHEMES_PLUGIN_URL . 'assets/js/isotope.min.js', false, '2.2.0', true );
			wp_enqueue_script( 'isotope.min' );

			wp_register_script( 'imagesloaded.pkgd.min', NEXTHEMES_PLUGIN_URL . 'assets/js/imagesloaded.pkgd.min.js', false, '3.2.0', true );
			wp_enqueue_script( 'imagesloaded.pkgd.min' );
			
			if( NexThemes_Plg::checkPlugin('woocommerce/woocommerce.php') && !wp_script_is('jquery.prettyPhoto', 'enqueued') && !wp_script_is('jquery.prettyPhoto.min', 'enqueued') ) {
				wp_register_style( 'prettyPhoto', NEXTHEMES_PLUGIN_URL . 'assets/css/prettyPhoto.css', false, '3.1.6' );
				wp_enqueue_style( 'prettyPhoto' );
				
				wp_register_script( 'jquery.prettyPhoto', NEXTHEMES_PLUGIN_URL . 'assets/js/jquery.prettyPhoto.js', false, '3.1.6', true );
				wp_enqueue_script( 'jquery.prettyPhoto' );
			}

            wp_enqueue_style( 'nexthemes-style', NEXTHEMES_PLUGIN_URL . 'assets/css/style.css' );

			if( is_singular('nth_gallery') ) {
				wp_enqueue_script( 'jquery.royalslider.min.js', NEXTHEMES_PLUGIN_URL . 'assets/royalslider/jquery.royalslider.min.js', array('jquery'), NEXTHEMES_VERSION, true );
				wp_enqueue_style( 'royalslider', NEXTHEMES_PLUGIN_URL . 'assets/royalslider/royalslider.css', false, NEXTHEMES_VERSION );
				wp_enqueue_style( 'rs-default', NEXTHEMES_PLUGIN_URL . 'assets/royalslider/skins/default/rs-default.css', false, NEXTHEMES_VERSION );
			}

			wp_enqueue_script( 'nextheme-plugin-js', NEXTHEMES_PLUGIN_URL . 'assets/js/nexthemes.js', array('jquery'), NEXTHEMES_VERSION, true );

			$features = get_option('nexthemes_pl_settings');
			if(!empty( $features['gg_analytics_id'] )) {
				wp_add_inline_script('nextheme-plugin-js', self::ga_script($features['gg_analytics_id'] ));
			}
		}

		public static function ga_script($ga_id){
			return "(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){"
  					. "(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),"
  					. "m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)"
  					. "})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');"

  					. "ga('create', '".esc_js($ga_id)."', 'auto');"
  					. "ga('send', 'pageview');";
		}


		public static function pareCss(){
			global $nth_sc_css_output;
			$cssOP = '';
			foreach( $nth_sc_css_output as $selector => $css ){
				if( is_array($css) ) {
					$cssOP .= $selector . '{';
					foreach ( $css as $k => $v ) {
						switch($k){
							case 'bgcolor':
								$cssOP .= 'background-color: '.$v;
								break;
						}
					}
					$cssOP .= '}';
				}
			}

			return $cssOP;
		}
		
		public static function localize_printed_scripts(){
			$localizes = array(
				'ajax_url'	=> admin_url( 'admin-ajax.php' ),
				'nonce'		=> wp_create_nonce('nth_plugin_none_339419'),
				'data'		=> array(
					'search'	=> array(),
				),
			);
			$pl_settings = NexThemes_Plg::$nexthemes_settings['nexthemes_ajaxsearch_settings'];

			if( isset($pl_settings['min_char']) )
				$localizes['data']['search']['min_char'] = $pl_settings['min_char'];

			if( !empty($pl_settings['result_limit']) )
				$localizes['data']['search']['result_limit'] = $pl_settings['result_limit'];
			
			wp_localize_script('nextheme-plugin-js', 'NexThemes', $localizes);
		}
		
		public static function admin_load_scripts(){
			wp_enqueue_style( 'nexthemes-adminstyle', NEXTHEMES_PLUGIN_URL . 'assets/css/admin-style.css' );

			wp_register_script('jquery-tiptip', NEXTHEMES_PLUGIN_URL . 'assets/js/jquery.tipTip.min.js', array( 'jquery'));
			wp_enqueue_script('jquery-tiptip');

			wp_register_script('nexthemes-admin-script', NEXTHEMES_PLUGIN_URL . 'assets/js/nexthemes.admin.js', array( 'jquery','media-upload', 'thickbox'));
			wp_enqueue_script('nexthemes-admin-script');

			wp_enqueue_script("jquery-ui-core");
			wp_enqueue_script("jquery-ui-dialog");
		}
		
	}
	Nexthemes_Frontend_Scripts::init();
}
