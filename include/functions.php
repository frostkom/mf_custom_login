<?php

function init_custom_login()
{
	wp_enqueue_style('style_custom_login', plugin_dir_url(__FILE__)."style.css");
}

function message_custom_login($message)
{
	global $wpdb;

	mf_enqueue_script('script_custom_login', plugin_dir_url(__FILE__)."/script.js");

	$post_title = __("You haven't set a text to be displayed here", 'lang_login');
	$post_content = "<a href='".admin_url("options-general.php?page=settings_mf_base#settings_custom_login")."'>".__("Choose a text by going to the settings page", 'lang_login')."</a>";

	$post_id = get_option('settings_custom_login_page');

	if($post_id > 0)
	{
		$result = $wpdb->get_results("SELECT post_title, post_content FROM ".$wpdb->posts." WHERE ID = '".$post_id."' AND post_type = 'page' AND post_status = 'publish'");

		foreach($result as $r)
		{
			$post_title = $r->post_title;
			$post_content = apply_filters('the_content', $r->post_content);
		}
	}

	$additional_message = "<div id='mf_custom_login'>
		<h2>".$post_title."</h2>
		<p>".$post_content."</p>
	</div>";

	if(empty($message))
	{
		return $additional_message;
	}
	
	else
	{
		return $message.$additional_message;
	}
}

function settings_custom_login()
{
	$options_area = "settings_custom_login";

	add_settings_section($options_area, "", $options_area."_callback", BASE_OPTIONS_PAGE);

	$arr_settings = array(
		"settings_custom_login_page" => __("Page", 'lang_login'),
	);

	foreach($arr_settings as $handle => $text)
	{
		add_settings_field($handle, $text, $handle."_callback", BASE_OPTIONS_PAGE, $options_area);

		register_setting(BASE_OPTIONS_PAGE, $handle);
	}
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

	$arr_data[] = array("", "-- ".__("Choose here", 'lang_login')." --");

	get_post_children(array('output_array' => true), $arr_data);

	echo show_select(array('data' => $arr_data, 'name' => $setting_key, 'compare' => $option, 'description' => __("The content from this page is displayed next to the login screen", 'lang_login')));

	/*echo "<select name='settings_custom_login_page'>
		<option value=''>-- ".__("Choose page here", 'lang_login')." --</option>"
		.get_post_children(array('current_id' => $option))
	."</select>";*/
}