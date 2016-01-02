<?php
/*
Plugin Name: MF Custom Login
Plugin URI: 
Description: 
Version: 1.0.4
Author: Martin Fors
Author URI: http://frostkom.se
*/

include_once("include/functions.php");

add_action('init', 'init_custom_login');

if(is_admin())
{
	add_action('admin_init', 'settings_custom_login');
}

else
{
	add_filter('login_message', 'message_custom_login');
}

load_plugin_textdomain('lang_login', false, dirname(plugin_basename(__FILE__))."/lang/");

function init_custom_login()
{
	wp_enqueue_style('style_custom_login', plugins_url()."/".dirname(plugin_basename(__FILE__))."/include/style.css");
	mf_enqueue_script('script_custom_login', plugins_url()."/".dirname(plugin_basename(__FILE__))."/include/script.js");
}