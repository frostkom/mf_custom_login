<?php
/*
Plugin Name: MF Custom Login
Plugin URI: https://github.com/frostkom/mf_custom_login
Description:
Version: 3.6.24
Licence: GPLv2 or later
Author: Martin Fors
Author URI: https://martinfors.se
Text Domain: lang_login
Domain Path: /lang
*/

if(!function_exists('is_plugin_active') || function_exists('is_plugin_active') && is_plugin_active("mf_base/index.php"))
{
	include_once("include/classes.php");

	$obj_custom_login = new mf_custom_login();

	add_action('cron_base', 'activate_custom_login', mt_rand(1, 10));
	add_action('cron_base', array($obj_custom_login, 'cron_base'), mt_rand(1, 10));

	add_action('init', array($obj_custom_login, 'init'), 1);

	if(is_admin())
	{
		register_activation_hook(__FILE__, 'activate_custom_login');
		register_uninstall_hook(__FILE__, 'uninstall_custom_login');

		add_action('admin_init', array($obj_custom_login, 'settings_custom_login'));
		add_action('admin_init', array($obj_custom_login, 'admin_init'), 0);

		add_filter('filter_sites_table_settings', array($obj_custom_login, 'filter_sites_table_settings'));

		add_filter('display_post_states', array($obj_custom_login, 'display_post_states'), 10, 2);

		add_filter('user_row_actions', array($obj_custom_login, 'user_row_actions'), 10, 2);
		add_action('ms_user_row_actions', array($obj_custom_login, 'user_row_actions'), 10, 2);

		add_action('show_user_profile', array($obj_custom_login, 'edit_user_profile'));
		add_action('edit_user_profile', array($obj_custom_login, 'edit_user_profile'));
	}

	else
	{
		add_action('signup_header', array($obj_custom_login, 'signup_header'));

		add_filter('login_headertext', array($obj_custom_login, 'login_headertext'));

		/* Validate fields on login, registration and lost password forms */
		add_action('wp_authenticate_user', array($obj_custom_login, 'wp_authenticate_user'), 10);
		add_filter('registration_errors', array($obj_custom_login, 'registration_errors'), 10, 3);
		//add_action('lostpassword_post', array($obj_custom_login, 'lostpassword_post'), 10, 3); // This does not validate and return errors

		add_action('login_init', array($obj_custom_login, 'login_init'), 0);
		add_filter('login_redirect', array($obj_custom_login, 'login_redirect'), 10, 3);
		add_filter('login_message', array($obj_custom_login, 'login_message'));

		/* Direct Link Login */
		add_action('wp_login_errors', array($obj_custom_login, 'wp_login_errors'));
		add_action('wp_login', array($obj_custom_login, 'wp_login'));
		add_action('wp_logout', array($obj_custom_login, 'wp_logout'));

		/* Add fields to login, registration and lost password forms */
		add_action('login_form', array($obj_custom_login, 'login_form'));
		add_action('register_form', array($obj_custom_login, 'register_form'));
		add_action('lostpassword_form', array($obj_custom_login, 'lostpassword_form'));

		add_action('wp_head', array($obj_custom_login, 'wp_head'), 0);
		add_filter('body_class', array($obj_custom_login, 'body_class'));
	}

	add_filter('is_public_page', array($obj_custom_login, 'is_public_page'), 10, 2);
	add_filter('login_url', array($obj_custom_login, 'login_url'), 10, 2);
		add_filter('login_display_language_dropdown', '__return_false');
	add_filter('register_url', array($obj_custom_login, 'register_url'), 10, 2);
		add_filter('wp_new_user_notification_email_admin', array($obj_custom_login, 'wp_new_user_notification_email_admin'), 10, 2);
		add_filter('wp_new_user_notification_email', array($obj_custom_login, 'wp_new_user_notification_email'), 10, 2);
	add_filter('lostpassword_url', array($obj_custom_login, 'lostpassword_url'), 10, 2);
		add_filter('retrieve_password_title', array($obj_custom_login, 'retrieve_password_title'), 10, 3);
		add_filter('retrieve_password_message', array($obj_custom_login, 'retrieve_password_message'), 10, 4);
	add_filter('logout_url', array($obj_custom_login, 'logout_url'), 10, 2);

	add_filter('determine_current_user', array($obj_custom_login, 'determine_current_user'), 21);

	add_action('wp_ajax_api_custom_login_direct_create', array($obj_custom_login, 'api_custom_login_direct_create'));
	add_action('wp_ajax_api_custom_login_direct_revoke', array($obj_custom_login, 'api_custom_login_direct_revoke'));

	add_action('wp_ajax_api_custom_login_direct_link_email', array($obj_custom_login, 'api_custom_login_direct_link_email'));
	add_action('wp_ajax_nopriv_api_custom_login_direct_link_email', array($obj_custom_login, 'api_custom_login_direct_link_email'));

	add_action('wp_ajax_api_custom_login_nonce', array($obj_custom_login, 'api_custom_login_nonce'));
	add_action('wp_ajax_nopriv_api_custom_login_nonce', array($obj_custom_login, 'api_custom_login_nonce'));

	add_action('widgets_init', array($obj_custom_login, 'widgets_init'));

	load_plugin_textdomain('lang_login', false, dirname(plugin_basename(__FILE__))."/lang/");

	function activate_custom_login()
	{
		global $wpdb;

		//replace_option(array('old' => 'setting_no_public_pages', 'new' => 'setting_login_no_public_pages'));
		//replace_option(array('old' => 'setting_theme_core_login', 'new' => 'setting_login_require_for_public_pages'));

		/*$default_charset = (DB_CHARSET != '' ? DB_CHARSET : 'utf8');

		$arr_add_column = $arr_update_column = $arr_add_index = array();

		$wpdb->query("CREATE TABLE IF NOT EXISTS ".$wpdb->base_prefix."custom_login (
			loginID INT UNSIGNED NOT NULL AUTO_INCREMENT,
			loginIP VARCHAR(15) DEFAULT NULL,
			loginStatus ENUM('failure', 'success') NOT NULL DEFAULT 'failure',
			loginUsername VARCHAR(40) DEFAULT NULL,
			loginCreated DATETIME DEFAULT NULL,
			PRIMARY KEY (loginID),
			KEY logIP (loginIP),
			KEY loginStatus (loginStatus)
		) DEFAULT CHARSET=".$default_charset);

		$arr_add_column[$wpdb->base_prefix."custom_login"] = array(
			//'loginUsername' => "ALTER TABLE [table] ADD [column] VARCHAR(40) DEFAULT NULL AFTER loginStatus",
		);

		update_columns($arr_update_column);
		add_columns($arr_add_column);
		add_index($arr_add_index);*/
	}

	function uninstall_custom_login()
	{
		mf_uninstall_plugin(array(
			'options' => array('setting_custom_login_display_theme_logo', 'setting_custom_login_custom_logo', 'setting_custom_login_page', 'setting_custom_login_register', 'setting_custom_login_lostpassword', 'setting_custom_login_recoverpassword', 'setting_custom_login_allow_direct_link', 'setting_custom_login_allow_api', 'setting_custom_login_allow_server_auth', 'setting_custom_login_direct_link_expire', 'setting_custom_login_info', 'setting_custom_login_email_admin_registration', 'setting_custom_login_email_registration', 'setting_custom_login_email_lost_password', 'setting_custom_login_redirect_after_login_page', 'setting_custom_login_redirect_after_login', 'setting_custom_login_debug'),
			'meta' => array('meta_login_auth'),
			'tables' => array('custom_login'),
		));
	}
}