<?php

if(!defined('ABSPATH'))
{
	header("Content-Type: text/css; charset=utf-8");

	$folder = str_replace("/wp-content/plugins/mf_custom_login/include", "/", dirname(__FILE__));

	require_once($folder."wp-load.php");
}

$out_media_all = $out_media_mobile = "";

// Default WP Login
############################
$setting_custom_login_page = get_option('setting_custom_login_page');

$login_logo_css = $login_mobile_logo_css = "";

if(class_exists('mf_theme_core') && get_option('setting_custom_login_display_theme_logo') == 'yes')
{
	if(!isset($obj_theme_core))
	{
		$obj_theme_core = new mf_theme_core();
	}

	$obj_theme_core->get_params();

	if($obj_theme_core->options['header_logo'] != '')
	{
		$login_mobile_logo_css = $login_logo_css = "background-image: url(".$obj_theme_core->options['header_logo'].");
		background-size: contain;
		width: auto;";

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

if($login_mobile_logo_css != '')
{
	$out_media_all .= ".login #login h1 a
	{"
		.$login_mobile_logo_css
	."}";
}

if($setting_custom_login_page > 0)
{
	$out_media_all .= "#mf_custom_login
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

if($login_logo_css != '')
{
	$out_media_mobile .= ".login #login h1 a
	{"
		.$login_logo_css
	."}";
}

if($setting_custom_login_page > 0)
{
	$out_media_mobile .= ".login #login
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
############################

// Widget Styles
############################
if(apply_filters('get_widget_search', 'login-widget') > 0)
{
	$out_media_all .= ".login_form
	{
		margin: 0 auto;
		max-width: 400px;
	}

		.login_form form .flex_flow .form_button
		{
			text-align: right;
		}

			.login_form form .flex_flow .form_button button:last-of-type
			{
				margin-right: 0 !important;
			}

		.login_form form p
		{
			margin-top: .5em;
			text-align: center;
		}

	.login_form + .widget_text
	{
		background: #f7f7f7;
		margin: 0 auto;
		padding: .2em .4em 0;
		max-width: 400px;
	}";
}

if(apply_filters('get_widget_search', 'registration-widget') > 0)
{
	$out_media_all .= ".registration_form
	{
		margin: 0 auto;
		max-width: 400px;
	}

		.registration_form .small
		{
			font-size: .8em;
		}

		.registration_form form button
		{
			width: 100%;
		}

		.registration_form form p
		{
			margin-top: .5em;
			text-align: center;
		}";
}

if(apply_filters('get_widget_search', 'lost-password-widget') > 0)
{
	$out_media_all .= ".lost_password_form
	{
		margin: 0 auto;
		max-width: 400px;
	}

		.lost_password_form form button
		{
			width: 100%;
		}

		.lost_password_form form p
		{
			margin-top: .5em;
			text-align: center;
		}";
}

if(apply_filters('get_widget_search', 'logged-in-info-widget') > 0)
{
	$out_media_all .= "header .logged_in_info
	{
		display: -webkit-box;
		display: -ms-flexbox;
		display: -webkit-flex;
		display: flex;
		float: right;
	}

		header .logged_in_info > *
		{
			display: block;
			-webkit-box-flex: 1 1 0;
			-webkit-flex: 1 1 0;
			-ms-flex: 1 1 0;
			flex: 1 1 0;
		}

			header .logged_in_info > * + *
			{
				margin-left: 1em;
			}

		.logged_in_info ul
		{
			align-self: center;
			font-size: 1.4em;
			list-style: none;
		}

			.logged_in_info ul li
			{
				white-space: nowrap;
			}

				.logged_in_info ul li + li
				{
					margin-top: .3em;
				}

		.logged_in_info .logged_in_avatar
		{
			border: .2em solid #ccc;
			border-radius: 50%;
			height: 6em;
			overflow: hidden;
			width: 6em;
		}

			.logged_in_info .logged_in_avatar img
			{
				object-fit: cover;
				height: 100%;
				width: 100%;
			}";
}
############################

if($out_media_all != '')
{
	echo "@media all
	{"
		.$out_media_all
	."}";
}

if($out_media_mobile != '')
{
	echo "@media (min-width: 740px)
	{"
		.$out_media_mobile
	."}";
}