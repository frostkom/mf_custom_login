<?php

if(!defined('ABSPATH'))
{
	header("Content-Type: text/css; charset=utf-8");

	$folder = str_replace("/wp-content/plugins/mf_custom_login/include", "/", dirname(__FILE__));

	require_once($folder."wp-load.php");
}

if(is_plugin_active('mf_theme_core/index.php'))
{
	if(!isset($obj_theme_core))
	{
		$obj_theme_core = new mf_theme_core();
	}
	
	$obj_theme_core->get_params();
}

$setting_custom_login_page = get_option('setting_custom_login_page');

$login_logo_css = $login_mobile_logo_css = "";

if(is_plugin_active('mf_theme_core/index.php') && get_option('setting_custom_login_display_theme_logo') == 'yes')
{
	if($obj_theme_core->options['header_logo'] != '')
	{
		if($obj_theme_core->options['header_logo'] != '')
		{
			$login_mobile_logo_css = $login_logo_css = "background-image: url(".$obj_theme_core->options['header_logo'].");
			background-size: contain;
			width: auto;";
		}

		if($obj_theme_core->options['header_mobile_logo'] != '')
		{
			$login_mobile_logo_css = "background-image: url(".$obj_theme_core->options['header_mobile_logo'].");
			background-size: contain;
			width: auto;";
		}
	}

	else
	{
		echo $obj_theme_core->show_font_face();

		$login_mobile_logo_css = "background: none;"
		.$obj_theme_core->render_css(array('property' => 'font-family', 'value' => 'logo_font'))
		."font-size: 40px;
		font-weight: bold;"
		.$obj_theme_core->render_css(array('property' => 'color', 'value' => 'logo_color'))
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
		background-size: contain;
		width: 100%;";
	}
}

if($login_mobile_logo_css != '' || $setting_custom_login_page > 0)
{
	echo "@media all
	{";

		if($login_mobile_logo_css != '')
		{
			echo ".login h1 a
			{"
				.$login_mobile_logo_css
			."}";
		}

		if($setting_custom_login_page > 0)
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

	echo "}";
}

echo "@media (min-width: 740px)
{";

	if($login_logo_css != '')
	{
		echo ".login h1 a
		{"
			.$login_logo_css
		."}";
	}

	if($setting_custom_login_page > 0)
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

		#mf_custom_login + #login_error
		{
			box-sizing: border-box;
			width: 48%;
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