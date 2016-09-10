<?php
/*
Plugin Name: LiveChat
Plugin URI: http://www.livechatinc.com/addons/wordpress/
Description: Live chat software for live help, online sales and customer support. This plugin allows to quickly install LiveChat on any WordPress website.
Author: LiveChat
Author URI: http://www.livechatinc.com
Version: 3.3.1
*/

if (is_admin())
{
	require_once(dirname(__FILE__).'/plugin_files/LiveChatAdmin.class.php');
	LiveChatAdmin::get_instance();
}
else
{
	require_once(dirname(__FILE__).'/plugin_files/LiveChat.class.php');
	LiveChat::get_instance();
}

