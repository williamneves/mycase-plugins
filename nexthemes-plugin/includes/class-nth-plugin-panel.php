<?php

if ( ! defined( 'ABSPATH' ) ) exit;

if( ! class_exists( 'Nexthemes_Plugin_Panel' ) ) :

class Nexthemes_Plugin_Panel {

	public $settings = array();

	private $settings_api;

	public function __construct(){
		$this->settings_api = new WeDevs_Settings_API();

		add_action( 'admin_init', array( $this, 'settings_init' ) );
		add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
		add_action( 'admin_menu', array( $this, 'edit_menu_page' ) );
		$this->notices();
	}

	public function notices(){
		$options = self::get_option();
		if( isset( $options['dis_features'] ) && is_array( $options['dis_features'] ) ) {
			if( in_array( 'staticblock', $options['dis_features'] ) ) {
				Nexthemes_Admin_Notices::create( __('<strong>WARNING!</strong> If you disabled "Static block", some featured on theme will not working (Ex: Mega menu, Static block widget, Static block shortcode, ... )', 'nexthemes-plugin'), 'warning');
			}
		}
	}

	public function settings_init(){
		$this->settings_api->set_sections( $this->get_settings_sections() );
        $this->settings_api->set_fields( $this->get_settings_fields() );

		$this->settings_api->admin_init();
	}

	public function get_settings_sections() {
        $sections = array(
			array(
				'id' 	=> 'nexthemes_pl_settings',
				'title' => __( 'General', 'nexthemes-plugin' )
			)
        );
		$options = self::get_option();
		if( !isset($options['dis_features']) || !in_array( 'ajaxsearch', $options['dis_features'] ) ) {
			$sections[] = array(
				'id' 	=> 'nexthemes_ajaxsearch_settings',
				'title' => __( 'Ajax search', 'nexthemes-plugin' )
			);
		}

		$sections[] = array(
			'id' 	=> 'nexthemes_social_settings',
			'title' => __( 'Instagram', 'nexthemes-plugin' )
		);
        return $sections;
    }

	public function get_settings_fields() {
		$settings_fields = array(
			'nexthemes_pl_settings' => array(
				array(
					'name'    => 'dis_features',
					'label'   => __( 'Disable Features', 'nexthemes-plugin' ),
					'desc'    => __( 'Ticking on the features that you want to disable', 'nexthemes-plugin' ),
					'type'    => 'multicheck',
					'options' => array(
						'staticblock'   	=> 'Static Block',
						'gridlisttoggle' 	=> 'Woo Grid List Toggle',
						'portfolio' 		=> 'Portfolio',
						'teams' 			=> 'Team Members',
						'woovideos' 		=> 'Woo Custom Tabs',
						'ajaxsearch'		=> 'Ajax Product Search',
					)
				),
			)
        );
		$options = self::get_option();
		if( !isset($options['dis_features']) || !in_array( 'portfolio', $options['dis_features'] ) ) {
			$settings_fields['nexthemes_pl_settings'][] = array(
				'name'              => 'portfolio_thumb',
				'label'             => __( 'Portfolio thumb size', 'nexthemes-plugin' ),
				'type'              => 'image_size',
				'default'			=> array(
					'width'		=> '460',
					'height'	=> '300',
					'crop'		=> '1',
				)
			);
		}

		if( !isset($options['dis_features']) || !in_array( 'teams', $options['dis_features'] ) ) {
			$settings_fields['nexthemes_pl_settings'][] = array(
				'name'              => 'teams_thumb',
				'label'             => __( 'Team member thumb size', 'nexthemes-plugin' ),
				'type'              => 'image_size',
			);
		}

		if( !isset($options['dis_features']) || !in_array( 'gallery', $options['dis_features'] ) ) {
			$settings_fields['nexthemes_pl_settings'][] = array(
				'name'              => 'gallery_thumb_auto',
				'label'             => __( 'Gallery thumb size (auto height)', 'nexthemes-plugin' ),
				'type'              => 'image_size',
				'auto'				=> 1
			);
		}

		$settings_fields['nexthemes_pl_settings'][] = array(
			'name'              => 'gg_analytics_id',
			'label'             => __( 'GG Analytics Id', 'nexthemes-plugin' ),
			'type'              => 'text',
		);

		if( !isset($options['dis_features']) || !in_array( 'ajaxsearch', $options['dis_features'] ) ) {
			$settings_fields['nexthemes_ajaxsearch_settings'] = array(
				array(
					'name'              => 'search_thumb',
					'label'             => __( 'Seach product thumb size', 'nexthemes-plugin' ),
					'type'              => 'image_size',
					'default'			=> array(
						'width'		=> '30',
						'height'	=> '30',
						'crop'		=> '1',
					)
				),
				array(
					'name'              => 'min_char',
					'label'             => __( 'Min Char', 'nexthemes-plugin' ),
					'type'              => 'number',
					'default'			=> '3',
					'sanitize_callback'	=> 'intval',
					'min'				=> '2'
				),
				array(
					'name'              => 'result_limit',
					'label'             => __( 'Result Limit', 'nexthemes-plugin' ),
					'type'              => 'number',
					'default'			=> '3',
					'sanitize_callback'	=> 'intval',
					'unit'				=> 'Items',
					'min'				=> '2'
				),
			);
		}

		$settings_fields['nexthemes_social_settings'][] = array(
			'name'              => 'instagram_token',
			'label'             => __( 'Access token', 'nexthemes-plugin' ),
			'type'              => 'validate_api',
			'desc'				=> sprintf('<a href="http://instagram.pixelunion.net/" target="_blank" title="%1$s">%1$s</a>', esc_attr__('Get Your Instagram Access Token here.', 'nexthemes-plugin')),
			'default'			=> '',
			'action'			=> 'instagram'
		);

        return $settings_fields;
    }

	public static function get_option( $k = 'nexthemes_pl_settings' ){
		$options = get_option($k);
		return $options? $options : array();
	}

	public function add_menu_page() {
		add_menu_page( __( "Welcome to TheShopier", 'nexthemes-plugin' ), __( 'Nexthemes', 'nexthemes-plugin' ), 'manage_options', 'nexthemes', array( $this, 'dashboard_panel' ), 'dashicons-nexthemes-logo', 3 ); /*NEXTHEMES_PLUGIN_URL . 'assets/images/nexthemes-pl-icon.png'*/

		add_submenu_page( 'nexthemes', __( "Supporting", 'nexthemes-plugin' ), __( "Support", 'nexthemes-plugin' ), 'manage_options', 'nexthemes-support', array( $this, 'support_panel' ) );
		add_submenu_page( 'nexthemes', __( "Theme Improter", 'nexthemes-plugin' ), __( "Importer", 'nexthemes-plugin' ), 'manage_options', 'nexthemes-importer', array( $this, 'importer_panel' ) );
		add_submenu_page( 'nexthemes', __( "Nexthemes Settings", 'nexthemes-plugin' ), __( "Settings", 'nexthemes-plugin' ), 'manage_options', 'nexthemes-settings', array( $this, 'settings_panel' ) );
		add_submenu_page( 'nexthemes', __( "System status", 'nexthemes-plugin' ), __( "System status", 'nexthemes-plugin' ), 'manage_options', 'nexthemes-sys', array( $this, 'system_status_panel' ) );
	}
	
	public function edit_menu_page(){
		global $submenu;

		if ( current_user_can( 'edit_theme_options' ) ) {
			$submenu['nexthemes'][0][0] = __( "About", 'nexthemes-plugin' );
		}
	}

	public function randerTabHeader(){
		$nexthemes = NexThemes_Plg::getThemeInfo();
		?>
		<h1><?php printf(__( "Welcome to %s!", "nexthemes-plugin" ), $nexthemes->get('Name')); ?></h1>

		<div class="about-text">
			<?php echo $nexthemes->get('Description')?>
		</div>
		<div class="wp-badge nth-page-logo">
			<span class="theshopier-version"><?php echo __( "Version", "nexthemes-plugin"); ?> <?php echo $nexthemes->get('Version'); ?></span>
		</div>


		<h2 class="nav-tab-wrapper">
			<?php
			$header_tab_args = array(
				'nexthemes'				=> array(
					'title'		=> __( "About TheShopier", "nexthemes-plugin" ),
					'icon'		=> 'fa fa-desktop'
				),
				'nexthemes-support'		=>  array(
					'title'		=> __( "Support", "nexthemes-plugin" ),
					'icon'		=> 'fa fa-envelope'
				),
				'nexthemes-importer'	=>  array(
					'title'		=> __( "Importer", "nexthemes-plugin" ),
					'icon'		=> 'fa fa-cloud-download'
				),
				'nexthemes-settings'	=> array(
					'title'		=> __( "Settings", "nexthemes-plugin" ),
					'icon'		=> 'fa fa-cogs'
				),
				'nexthemes-sys'			=> array(
					'title'		=> __( "System Status", "nexthemes-plugin" ),
					'icon'		=> 'fa fa-desktop'
				),
			);
			//nav-tab-active
			foreach($header_tab_args as $slug => $tt){
				$tab_class = array('nav-tab');
				if( strcmp($slug, trim($_GET['page'])) === 0 ) {
					$tab_class[] = 'nav-tab-active';
				}
				if( !empty($tt['title']) ) {
					$title = $tt['title'];
					printf( '<a href="%s" class="%s">%s</a>', admin_url( 'admin.php?page='.$slug ), esc_attr(implode(' ', $tab_class)) , $title );
				}
			}
			?>
		</h2>
		<?php
	}

	public function dashboard_panel(){
		$nexthemes = NexThemes_Plg::getThemeInfo();
		add_action('nth_plugin_panel_header', array($this, 'randerTabHeader'));
		NexThemes_Plg::get_template('admin/dashboard.tpl.php', array('nexthemes' => $nexthemes), null, true);
	}

	public function support_panel(){
		add_action('nth_plugin_panel_header', array($this, 'randerTabHeader'));
		NexThemes_Plg::get_template('admin/support.tpl.php', array(), null, true);
	}

	public function importer_panel(){
		add_action('nth_plugin_panel_header', array($this, 'randerTabHeader'));
		NexThemes_Plg::get_template('admin/importer.tpl.php');
	}
	public function settings_panel(){
		add_action('nth_plugin_panel_header', array($this, 'randerTabHeader'));
		NexThemes_Plg::get_template('admin/settings.tpl.php', array('settings_api' => $this->settings_api));
	}

	public function system_status_panel(){
		add_action('nth_plugin_panel_header', array($this, 'randerTabHeader'));
		NexThemes_Plg::get_template('admin/system.tpl.php');
	}

}

endif;