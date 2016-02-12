<?php

function message_custom_login($message)
{
	global $wpdb;

	$post_title = __("You haven't set a text to be displayed here", 'lang_login');
	$post_content = "<a href='/wp-admin/options-general.php?page=settings_mf_base#mf_custom_login_settings'>".__("Choose a text by going to the settings page", 'lang_login')."</a>";

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

function add_action_custom_login($links)
{
	$links[] = "<a href='".admin_url('options-general.php?page=settings_mf_base#settings_custom_login')."'>".__("Settings", 'lang_login')."</a>";

	return $links;
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
	echo settings_header('settings_custom_login', __("Login Message", 'lang_login'));
}

function settings_custom_login_page_callback()
{
	$current_post_id = get_option('settings_custom_login_page');

	echo "<select name='settings_custom_login_page' id='mf_custom_login_settings'>
		<option value=''>-- ".__("Choose page here", 'lang_login')." --</option>"
		.get_post_children(array('current_id' => $current_post_id))
	."</select>";
}