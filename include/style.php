<?php

header("Content-Type: text/css; charset=utf-8");

if(!defined('ABSPATH'))
{
	$folder = str_replace("/wp-content/plugins/mf_custom_login/include", "/", dirname(__FILE__));

	require_once($folder."wp-load.php");
}

$settings_custom_login_page = get_option('settings_custom_login_page');

$login_logo_css = $login_mobile_logo_css = "";

list($options_params, $options) = get_params();

if(get_option('setting_custom_login_display_theme_logo') == 'yes')
{
	if($options['header_logo'] != '')
	{
		if($options['header_logo'] != '')
		{
			$login_logo_css = "background-image: url(".$options['header_logo'].");
			background-size: cover;
			width: 100%;";
		}

		if($options['header_mobile_logo'] != '')
		{
			$login_mobile_logo_css = "background-image: url(".$options['header_mobile_logo'].");
			background-size: cover;
			width: 100%;";
		}
	}

	else
	{
		$options_fonts = get_theme_fonts();

		echo show_font_face($options_params, $options_fonts, $options);

		$login_logo_css = "background: none;"
		.render_css(array('property' => 'font-family', 'value' => 'logo_font'))
		."font-size: 40px;
		font-weight: bold;"
		.render_css(array('property' => 'color', 'value' => 'logo_color'))
		."height: auto;
		text-indent: unset;
		width: auto;";
	}
}

else
{
	$setting_custom_login_custom_logo = get_option('setting_custom_login_custom_logo');

	if($setting_custom_login_custom_logo != '')
	{
		$login_logo_css = "background-image: url(".$setting_custom_login_custom_logo.");
		background-size: cover;
		width: 100%;";
	}
}

echo "@media all
{";

	if($login_mobile_logo_css != '')
	{
		echo ".login h1 a
		{"
			.$login_mobile_logo_css
		."}";
	}

	if($settings_custom_login_page > 0)
	{
		echo "#mf_custom_login
		{
			background: #fff;
			box-shadow: 0 1px 3px rgba(0, 0, 0, .13);
			margin-bottom: 20px;
			padding: 26px 24px;
		}

			#mf_custom_login > h2
			{
				line-height: 1.3em;
			}

			#mf_custom_login > p
			{
				margin-top: 1em;
			}

			#mf_custom_login img
			{
				max-width: 100%;
			}";
	}

echo "}

@media (min-width: 740px)
{";

	if($login_logo_css != '')
	{
		echo ".login h1 a
		{"
			.$login_logo_css
		."}";
	}

	if($settings_custom_login_page > 0)
	{
		echo "#login
		{
			position: relative;
			width: 740px;
		}

			#mf_custom_login, .message, #loginform, #nav, #backtoblog
			{
				box-sizing: border-box;
			}

			#mf_custom_login
			{
				float: right;
				margin-left: 4%;
				width: 48%;
			}

			.message
			{
				margin-bottom: 20px;
			}

		#loginform
		{
			margin-top: 0;
		}

		.message, #loginform, #nav, #backtoblog
		{
			clear: left;
			float: left;
			width: 48%;
		}";
	}

echo "}";