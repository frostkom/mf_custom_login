<?php
/*
Plugin Name: MF Custom Login
Plugin URI: https://github.com/frostkom/mf_custom_login
Description: 
Version: 2.3.2
Licence: GPLv2 or later
Author: Martin Fors
Author URI: http://frostkom.se
Text Domain: lang_login
Domain Path: /lang

Depends: MF Base
GitHub Plugin URI: frostkom/mf_custom_login
*/

include_once("include/classes.php");
include_once("include/functions.php");

$obj_custom_login = new mf_custom_login();

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

	/* Direct Link Login */
	add_action('login_init', array($obj_custom_login, 'login_init'));
	add_action('wp_login_errors', array($obj_custom_login, 'wp_login_errors'));
	add_action('wp_login', array($obj_custom_login, 'wp_login'));
	add_action('wp_logout', array($obj_custom_login, 'wp_logout'));
	add_filter('retrieve_password_message', array($obj_custom_login, 'retrieve_password_message'), 10, 4);

	add_action('login_form', array($obj_custom_login, 'login_form'));
	add_action('login_footer', array($obj_custom_login, 'login_footer'));
}

add_action('wp_ajax_send_direct_link_email', array($obj_custom_login, 'send_direct_link_email'));
add_action('wp_ajax_nopriv_send_direct_link_email', array($obj_custom_login, 'send_direct_link_email'));

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