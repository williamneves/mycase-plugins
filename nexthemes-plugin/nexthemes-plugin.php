<?php 

/**
 * Plugin Name: Nexthemes Plugins
 * Plugin URI: http://themeforest.net/user/themeyeti
 * Description: A product from ThemeYeti
 * Version: 1.1.2
 * Author: Nexthemes
 * Author URI: http://nexthemes.com/
 *
 * License: GPLv2 or later
 * Text Domain: nexthemes-plugin
 * Domain Path: /languages/
 *
 * @package Nexthemes Plugins
 * @author Nexthemes
 */


if ( ! defined( 'ABSPATH' ) ) exit;

if( ! class_exists( 'NexThemes_Plg' ) ):

final class NexThemes_Plg {
	
	public $version = '1.1.1';

	public static $nexthemes_settings = array();
	
	public function __construct(){
		add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( $this, 'action_links' ) );
		$this->define_constants();
		$this->includes();
		$this->init_hooks();
		self::$nexthemes_settings = $this->get_settings();
	}

	public function get_settings(){
		$res = array();
		$res['nexthemes_pl_settings'] = get_option('nexthemes_pl_settings', false);
		$res['nexthemes_ajaxsearch_settings'] = get_option('nexthemes_ajaxsearch_settings', false);
		return $res;
	}
	
	private function define_constants(){
		$this->define( 'NEXTHEMES_PLUGIN_FILE', __FILE__ );
		$this->define( 'NEXTHEMES_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		$this->define( 'NEXTHEMES_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		$this->define( 'NEXTHEMES_PLUGIN_TMPL_DIR', NEXTHEMES_PLUGIN_DIR . 'templates/' );
		$this->define( 'NEXTHEMES_VERSION', $this->version );
	}
	
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}
	
	public function load_plugin_textdomain(){
		$locale = get_locale();
		
		load_textdomain( 'nexthemes-plugin', WP_LANG_DIR . '/nexthemes-plugin/nexthemes-plugin-' . $locale . '.mo' );
		load_plugin_textdomain( 'nexthemes-plugin', false, plugin_basename( dirname( __FILE__ ) ) . "/languages" );
	}

	public function action_links($links){
		$links[] = '<a href="' . admin_url( "admin.php?page=nexthemes" ) . '">' . __( 'Settings', 'nexthemes-plugin' ) . '</a>';
		return $links;
	}
	
	private function is_request( $type ) {
		switch ( $type ) {
			case 'admin' :
				return is_admin();
			case 'ajax' :
				return defined( 'DOING_AJAX' );
			case 'cron' :
				return defined( 'DOING_CRON' );
			case 'frontend' :
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
		}
	}
	
	public function includes(){
		
		include_once( 'includes/class-nth-install.php' );
		include_once( 'includes/nth-functions.php' );
		
		include_once( 'includes/class.settings-api.php' );
		include_once( 'includes/class-nth-plugin-panel.php' );
		include_once( 'includes/class-nth-admin-notices.php' );
		
		include_once( 'includes/class-nth-frontend-scripts.php' );

		include_once( 'includes/instagram.php' );
		new Instagram();

		include_once( 'includes/class.importer.php' );
		
		$features = get_option('nexthemes_pl_settings');
		
		$features = isset($features['dis_features'])? $features['dis_features'] : array();
		
		if( !in_array( 'staticblock', $features ) )
			$this->include_staticblocks();
		
		if( !in_array( 'portfolio', $features ) )
			$this->include_portfolios();
		
		if( !in_array( 'teams', $features ) )
			$this->include_members();

		if( !in_array( 'woovideos', $features ) )
			$this->include_woovideos();

		if( !in_array( 'ajaxsearch', $features ) ) $this->include_ajaxsearch();
		
		$this->include_shortcodes();

		$this->include_gallery();
		
		if( self::checkPlugin( 'woocommerce/woocommerce.php' ) ) {
			if( !in_array( 'gridlisttoggle', $features ) )
				include_once( 'includes/woo-gridlisttoggle/class-gridlisttoggle.php' );

			include_once( 'includes/woo-brands/class-woo-brands.php' );
		}
	}
	
	public function include_portfolios(){
		include_once( 'includes/portfolios/class-portfolios.php' );
		if( $this->is_request( 'admin' ) ) {
			include_once( 'includes/portfolios/class-portfolios-admin.php' );
		} else {
			include_once( 'includes/portfolios/class-portfolios-front.php' );
		}
	}
	
	public function include_staticblocks(){
		include_once( 'includes/staticblocks/class-staticblocks.php' );
	}
	
	public function include_members(){
		include_once( 'includes/teams/class-team-members.php' );
		if( $this->is_request( 'admin' ) ) {
			include_once( 'includes/teams/class-team-members-admin.php' );
		} else {
			include_once( 'includes/teams/class-team-members-front.php' );
		}
	}

	public function include_woovideos(){
		include_once( 'includes/woo-product-videos/class-wooproductvideos.php' );
	}

	public function include_gallery(){
		include_once( 'includes/gallery/class.gallery.php' );
	}

	public function include_ajaxsearch(){
		include_once( 'includes/ajax-search/class-ajax-search.php' );
	}
	
	public function include_shortcodes(){
		if( $this->is_request( 'frontend' ) ) {
			include_once( 'includes/shortcodes/class-woo-shortcodes.php' );
			include_once( 'includes/shortcodes/class-shortcodes.php' );
		}
	}
	
	public function init_hooks(){
		register_activation_hook( __FILE__, array( 'Nexthemes_Installations', 'install' ) );
		$__plugin_panel = new Nexthemes_Plugin_Panel();
		add_action( 'init', array( $this, 'init' ), 0 );
		
		if( $this->is_request( 'frontend' ) ) {
			$shortcode = new Nexthemes_Shortcodes();
			add_action( 'init', array( $shortcode, 'init' ) );
		}
		
	}
	
	public function init(){
		// Set up localisation
		$this->load_plugin_textdomain();
	}
	
	public static function checkPlugin( $path = '', $res = 'bool' ){
		if( strlen( $path ) == 0 ) return false;
		$_actived = apply_filters( 'active_plugins', get_option( 'active_plugins' )  );
		if ( in_array( trim( $path ), $_actived ) ) {
			switch($res) {
				case 'info':
					return get_plugin_data( WP_PLUGIN_DIR . '/' .$path);
				default:
					return true;
			}
		}
		else return false;
	}

	public static function get_owlResponsive( $options = array() ){
		$column = $options['items'];

		$resp = array(
			0	=> array(
				'items'	=> round( $column * (479 / 1200) ),
				'loop'	=> false
			),
			480	=> array(
				'items'	=> round( $column * (768 / 1200) ),
				'loop'	=> false
			),
			769	=> array(
				'items'	=> round( $column * (980 / 1200) ),
				'loop'	=> false
			),
			981	=> array(
				'items'	=> round( $column * (1199 / 1200) ),
				'loop'	=> false
			),
			/*1200	=> array(
				'items'	=> round( $column * (1439 / 1200) ),
				'loop'	=> true
			),*/
			1440	=> array(
				'items'	=> round( $column * (1600 / 1200) ),
				'loop'	=> false
			),
		);
		if( isset($options['responsive']) && is_array($options['responsive']) ) {
			foreach($options['responsive'] as $k => $arg){
				$resp[$k] = $arg;
			}
		}
		$options['responsive'] = $resp;

		return $options;
	}

	public static function get_template( $template_name, $args = array(), $products = null, $theme = false ){
		if ( $args && is_array( $args ) ) {
			extract( $args );
		}

		$located = NEXTHEMES_PLUGIN_TMPL_DIR . $template_name;

		if($theme) {
			$tmp_f_arr = array_slice(explode('/', $template_name), 1);
			$tmp_f = implode('/', $tmp_f_arr);
			$theme_located = get_template_directory() . '/framework/backend/pl_tmps/' . $tmp_f;
			if(file_exists($theme_located)) $located = $theme_located;
		}

		if ( ! file_exists( $located ) ) {
			_doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', $located ), '1.0' );
			return;
		}

		include( $located );
	}

	public static function getImage( $atts ){
		$atts = wp_parse_args($atts, array(
			'alt'   	=> 'image alt',
			'width' 	=> '',
			'height' 	=> '',
			'src'  		=> '',
			'before'	=> '',
			'after'		=> ''
		));
		$src = esc_url($atts['src']);
		if(strlen(trim($src)) > 0) {
			if(strlen(trim($atts['width'])) == 0) {
				list($atts['width'], $atts['height']) = @getimagesize($src);
			}
			echo $atts['before'] . "<img width='{$atts['width']}' height='{$atts['height']}' src='{$src}' alt='{$atts['alt']}' />" .$atts['after'];
		}
	}

	public static function getThemeInfo(){
		$nexthemes = wp_get_theme();
		if($nexthemes->parent_theme) {
			$template_dir =  basename(get_template_directory());
			$nexthemes = wp_get_theme($template_dir);
		}
		return $nexthemes;
	}

}

$nexthemes_plg = new NexThemes_Plg();

endif;

