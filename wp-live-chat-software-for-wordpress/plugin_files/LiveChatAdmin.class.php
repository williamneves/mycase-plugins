<?php

require_once('LiveChat.class.php');

final class LiveChatAdmin extends LiveChat
{
	/**
	 * Plugin's version
	 */
	protected $plugin_version = null;

	/**
	 * Returns true if "Advanced settings" form has just been submitted,
	 * false otherwise
	 *
	 * @return bool
	 */
	protected $changes_saved = false;

	/**
	 * Starts the plugin
	 */
	protected function __construct()
	{
		parent::__construct();

		add_action('init', array($this, 'load_scripts'));
		add_action('admin_menu', array($this, 'admin_menu'));

		// tricky error reporting
		if (defined('WP_DEBUG') && WP_DEBUG == true)
		{
			add_action('init', array($this, 'error_reporting'));
		}

		if (isset($_GET['reset']) && $_GET['reset'] == '1')
		{
			$this->reset_options();
		}
		elseif ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			$this->update_options($_POST);
		}
	}

	public static function get_instance()
	{
		if (!isset(self::$instance))
		{
			$c = __CLASS__;
			self::$instance = new $c;
		}

		return self::$instance;
	}

	/**
	 * Set error reporting for debugging purposes
	 */
	public function error_reporting()
	{
		error_reporting(E_ALL & ~E_USER_NOTICE);
	}

	/**
	 * Returns this plugin's version
	 *
	 * @return string
	 */
	public function get_plugin_version()
	{
		if (is_null($this->plugin_version))
		{
			if (!function_exists('get_plugins'))
			{
				require_once(ABSPATH.'wp-admin/includes/plugin.php');
			}

			$plugin_folder = get_plugins('/'.plugin_basename(dirname(__FILE__).'/..'));
			$this->plugin_version = $plugin_folder['livechat.php']['Version'];
		}

		return $this->plugin_version;
	}

	public function load_scripts()
	{
		wp_enqueue_script('livechat', $this->get_plugin_url().'/js/livechat.js', 'jquery', $this->get_plugin_version(), true);
		wp_enqueue_style('livechat', $this->get_plugin_url().'/css/livechat.css', false, $this->get_plugin_version());
	}

	public function admin_menu()
	{
		add_menu_page(
			'LiveChat',
			'LiveChat',
			'administrator',
			'livechat',
			array($this, 'livechat_settings_page'),
			$this->get_plugin_url().'/images/favicon.png'
		);

		add_submenu_page(
			'livechat',
			'Settings',
			'Settings',
			'administrator',
			'livechat_settings',
			array($this, 'livechat_settings_page')
		);

		// remove the submenu that is automatically added
		if (function_exists('remove_submenu_page'))
		{
			remove_submenu_page('livechat', 'livechat');
		}

		// Settings link
		add_filter('plugin_action_links', array($this, 'livechat_settings_link'), 10, 2);
	}

	/**
	 * Displays settings page
	 */
	public function livechat_settings_page()
	{
		$this->get_helper('Settings');
	}

	public function changes_saved()
	{
		return $this->changes_saved;
	}

	public function livechat_settings_link($links, $file)
	{
		if (basename($file) !== 'livechat.php')
		{
			return $links;
		}

		$settings_link = sprintf('<a href="admin.php?page=livechat_settings">%s</a>', __('Settings'));
		array_unshift ($links, $settings_link); 
		return $links;
	}

	protected function reset_options()
	{
		delete_option('livechat_license_number');
	}

	protected function update_options($data)
	{
		// check if we are handling LiveChat settings form
		if (isset($data['settings_form']) == false && isset($data['new_license_form']) == false)
		{
			return false;
		}

		$license_number = isset($data['license_number']) ? (int)$data['license_number'] : 0;

		update_option('livechat_license_number', $license_number);

		if (isset($data['changes_saved']) && $data['changes_saved'] == '1')
		{
			$this->changes_saved = true;
		}
	}
}