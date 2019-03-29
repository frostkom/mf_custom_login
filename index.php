<?php
/*
Plugin Name: MF Custom Login
Plugin URI: https://github.com/frostkom/mf_custom_login
Description: 
Version: 2.7.1
Licence: GPLv2 or later
Author: Martin Fors
Author URI: https://frostkom.se
Text Domain: lang_login
Domain Path: /lang

Depends: MF Base
GitHub Plugin URI: frostkom/mf_custom_login
*/

include_once("include/classes.php");

$obj_custom_login = new mf_custom_login();

add_action('cron_base', 'activate_custom_login', mt_rand(1, 10));
add_action('cron_base', array($obj_custom_login, 'cron_base'), mt_rand(1, 10));

if(is_admin())
{
	register_activation_hook(__FILE__, 'activate_custom_login');
	register_uninstall_hook(__FILE__, 'uninstall_custom_login');

	add_action('admin_init', array($obj_custom_login, 'settings_custom_login'));
	add_action('admin_init', array($obj_custom_login, 'admin_init'));

	add_filter('user_row_actions', array($obj_custom_login, 'user_row_actions'), 10, 2);

	add_action('show_user_profile', array($obj_custom_login, 'edit_user_profile'));
	add_action('edit_user_profile', array($obj_custom_login, 'edit_user_profile'));
}

else
{
	add_action('login_init', array($obj_custom_login, 'login_init'), 0);
	add_filter('login_message', array($obj_custom_login, 'login_message'));

	/* Direct Link Login */
	add_action('wp_login_errors', array($obj_custom_login, 'wp_login_errors'));
	add_action('wp_login', array($obj_custom_login, 'wp_login'));
	add_action('wp_logout', array($obj_custom_login, 'wp_logout'));

	add_filter('wp_new_user_notification_email', array($obj_custom_login, 'wp_new_user_notification_email'), 10, 2);
	add_filter('retrieve_password_message', array($obj_custom_login, 'retrieve_password_message'), 10, 4);

	add_action('login_form', array($obj_custom_login, 'login_form'));

	add_action('wp_head', array($obj_custom_login, 'wp_head'), 0);
}

add_filter('login_url', array($obj_custom_login, 'login_url'), 10, 2);
add_filter('register_url', array($obj_custom_login, 'register_url'), 10, 2);
add_filter('lostpassword_url', array($obj_custom_login, 'lostpassword_url'), 10, 2);
add_filter('logout_url', array($obj_custom_login, 'logout_url'), 10, 2);

add_action('wp_ajax_get_direct_login_url', array($obj_custom_login, 'get_direct_login_url'));
//add_action('wp_ajax_nopriv_get_direct_login_url', array($obj_custom_login, 'get_direct_login_url'));

add_action('wp_ajax_send_direct_link_email', array($obj_custom_login, 'send_direct_link_email'));
add_action('wp_ajax_nopriv_send_direct_link_email', array($obj_custom_login, 'send_direct_link_email'));

add_action('widgets_init', array($obj_custom_login, 'widgets_init'));

load_plugin_textdomain('lang_login', false, dirname(plugin_basename(__FILE__))."/lang/");

function activate_custom_login()
{
	mf_uninstall_plugin(array(
		'options' => array('setting_custom_login_info'),
	));

	replace_option(array('old' => 'settings_custom_login_page', 'new' => 'setting_custom_login_page'));
	replace_option(array('old' => 'setting_approve_user_registration_email_text', 'new' => 'setting_custom_login_email_registration'));
}

function uninstall_custom_login()
{
	mf_uninstall_plugin(array(
		'options' => array('setting_custom_login_display_theme_logo', 'setting_custom_login_custom_logo', 'setting_custom_login_page', 'setting_custom_login_register', 'setting_custom_login_lostpassword', 'setting_custom_login_recoverpassword', 'setting_custom_login_allow_direct_link', 'setting_custom_login_allow_api', 'setting_custom_login_direct_link_expire', 'setting_custom_login_info', 'setting_custom_login_email_registration', 'setting_custom_login_email_lost_password'),
		'meta' => array('meta_login_auth'),
	));
}