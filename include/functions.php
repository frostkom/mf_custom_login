<?php

function init_custom_login()
{
	mf_enqueue_style('style_custom_login', plugin_dir_url(__FILE__)."style.css", get_plugin_version(__FILE__));
}

function message_custom_login($message)
{
	global $wpdb;

	mf_enqueue_script('script_custom_login', plugin_dir_url(__FILE__)."script.js", get_plugin_version(__FILE__));

	$post_title = __("You haven't set a text to be displayed here", 'lang_login');
	$post_content = "<a href='".admin_url("options-general.php?page=settings_mf_base#settings_custom_login")."'>".__("Choose a text by going to the settings page", 'lang_login')."</a>";

	$action = check_var('action');

	$post_id = 0;

	switch($action)
	{
		case 'register':
			$post_id = get_option('setting_custom_login_register');
		break;

		case 'lostpassword':
			$post_id = get_option('setting_custom_login_lostpassword');
		break;

		case 'rp':
			$post_id = get_option('setting_custom_login_recoverpassword');
		break;
	}

	if(!($post_id > 0))
	{
		$post_id = get_option('settings_custom_login_page');
	}

	if($post_id > 0)
	{
		$result = $wpdb->get_results($wpdb->prepare("SELECT post_title, post_content FROM ".$wpdb->posts." WHERE ID = '%d' AND post_type = 'page' AND post_status = 'publish'", $post_id));

		foreach($result as $r)
		{
			$post_title = $r->post_title;
			$post_content = apply_filters('the_content', $r->post_content);
		}
	}

	if($post_content != '')
	{
		$message .= "<div id='mf_custom_login'>
			<h2>".$post_title."</h2>
			<p>".$post_content."</p>
		</div>";
	}

	return $message;
}

function settings_custom_login()
{
	$options_area = __FUNCTION__;

	add_settings_section($options_area, "", $options_area."_callback", BASE_OPTIONS_PAGE);

	$arr_settings = array();
	$arr_settings['settings_custom_login_page'] = __("General", 'lang_login');

	if(get_option('settings_custom_login_page') > 0)
	{
		$arr_settings['setting_custom_login_register'] = __("Register", 'lang_login');
		$arr_settings['setting_custom_login_lostpassword'] = __("Lost Password", 'lang_login');
		$arr_settings['setting_custom_login_recoverpassword'] = __("Recover Password", 'lang_login');
	}

	show_settings_fields(array('area' => $options_area, 'settings' => $arr_settings));
}

function settings_custom_login_callback()
{
	$setting_key = get_setting_key(__FUNCTION__);

	echo settings_header($setting_key, __("Login", 'lang_login'));
}

function settings_custom_login_page_callback()
{
	$setting_key = get_setting_key(__FUNCTION__);
	$option = get_option($setting_key);

	$arr_data = array();
	get_post_children(array('add_choose_here' => true), $arr_data);

	echo show_select(array('data' => $arr_data, 'name' => $setting_key, 'value' => $option, 'suffix' => "<a href='".admin_url("post-new.php?post_type=page")."'><i class='fa fa-lg fa-plus'></i></a>", 'description' => __("The content from this page is displayed next to the login screen", 'lang_login')));
}

function setting_custom_login_register_callback()
{
	$setting_key = get_setting_key(__FUNCTION__);
	$option = get_option($setting_key);

	$arr_data = array();
	get_post_children(array('add_choose_here' => true), $arr_data);

	echo show_select(array('data' => $arr_data, 'name' => $setting_key, 'value' => $option, 'suffix' => "<a href='".admin_url("post-new.php?post_type=page")."'><i class='fa fa-lg fa-plus'></i></a>", 'description' => __("The content from this page is displayed next to the register screen", 'lang_login')));
}

function setting_custom_login_lostpassword_callback()
{
	$setting_key = get_setting_key(__FUNCTION__);
	$option = get_option($setting_key);

	$arr_data = array();
	get_post_children(array('add_choose_here' => true), $arr_data);

	echo show_select(array('data' => $arr_data, 'name' => $setting_key, 'value' => $option, 'suffix' => "<a href='".admin_url("post-new.php?post_type=page")."'><i class='fa fa-lg fa-plus'></i></a>", 'description' => __("The content from this page is displayed next to the lost password screen", 'lang_login')));
}

function setting_custom_login_recoverpassword_callback()
{
	$setting_key = get_setting_key(__FUNCTION__);
	$option = get_option($setting_key);

	$arr_data = array();
	get_post_children(array('add_choose_here' => true), $arr_data);

	echo show_select(array('data' => $arr_data, 'name' => $setting_key, 'value' => $option, 'suffix' => "<a href='".admin_url("post-new.php?post_type=page")."'><i class='fa fa-lg fa-plus'></i></a>", 'description' => __("The content from this page is displayed next to the recover password screen", 'lang_login')));
}