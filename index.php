<?php
/*
Plugin Name: MF Custom Login
Plugin URI: https://github.com/frostkom/mf_custom_login
Description: 
Version: 2.1.4
Author: Martin Fors
Author URI: http://frostkom.se
Text Domain: lang_login
Domain Path: /lang

GitHub Plugin URI: frostkom/mf_custom_login
*/

include_once("include/functions.php");

add_action('cron_base', 'activate_custom_login', mt_rand(1, 10));

add_action('init', 'init_custom_login');

if(is_admin())
{
	register_activation_hook(__FILE__, 'activate_custom_login');
	register_uninstall_hook(__FILE__, 'uninstall_custom_login');

	add_action('admin_init', 'settings_custom_login');
}

else
{
	add_filter('login_message', 'message_custom_login');
}

load_plugin_textdomain('lang_login', false, dirname(plugin_basename(__FILE__))."/lang/");

function activate_custom_login()
{
	replace_option(array('old' => 'settings_custom_login_page', 'new' => 'setting_custom_login_page'));
}

function uninstall_custom_login()
{
	mf_uninstall_plugin(array(
		'options' => array('setting_custom_login_page', 'setting_custom_login_register', 'setting_custom_login_lostpassword', 'setting_custom_login_recoverpassword'),
	));
}