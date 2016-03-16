<?php
/*
Plugin Name: MF Custom Login
Plugin URI: 
Description: 
Version: 1.2.1
Author: Martin Fors
Author URI: http://frostkom.se
*/

include_once("include/functions.php");

add_action('init', 'init_custom_login');

if(is_admin())
{
	register_uninstall_hook(__FILE__, 'uninstall_custom_login');

	add_action('admin_init', 'settings_custom_login');
}

else
{
	add_filter('login_message', 'message_custom_login');
}

load_plugin_textdomain('lang_login', false, dirname(plugin_basename(__FILE__))."/lang/");

function uninstall_custom_login()
{
	mf_uninstall_plugin(array(
		'options' => array('settings_custom_login_page'),
	));
}