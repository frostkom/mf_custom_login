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

if(!is_plugin_active("mf_form/index.php")) // We don't need duplicates of this code
{
	$out_media_all .= ".form_textfield.form_check
	{
        height: 0;
        left: 0;
		opacity: 0;
		position: absolute;
		top: 0;
        width: 0;
		z-index: -1;
	}";
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
		width: 740px !important;
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
if(!isset($obj_custom_login))
{
	$obj_custom_login = new mf_custom_login();
}

if(!($obj_custom_login->login_id > 0))
{
	$obj_custom_login->login_id = apply_filters('get_block_search', 0, 'mf/customlogin');
}

if($obj_custom_login->login_id > 0)
{
	/*if(wp_is_block_theme() == false)
	{
		$out_media_all .= ".login_form
		{
			margin: 0 auto;
			max-width: 400px;
		}";
	}*/

		$out_media_all .= ".login_form form .flex_flow .form_button, .login_form form .flex_flow .wp-block-button
		{
			text-align: right;
		}

			.login_form form .flex_flow .form_button button:last-of-type, .login_form form .flex_flow .wp-block-button button:last-of-type
			{
				margin-right: 0 !important;
			}";

		/* Has to be more specific to not screw up BankID */
		/*.login_form form p
		{
			margin-top: .5em;
			text-align: center;
		}*/

	$out_media_all .= ".login_form + .widget_text
	{
		background: #f7f7f7;
		margin: 0 auto;
		padding: .2em .4em 0;"
		//."max-width: 400px;"
	."}";
}

if(!($obj_custom_login->registration_id > 0))
{
	$obj_custom_login->registration_id = apply_filters('get_block_search', 0, 'mf/customregistration');
}

if($obj_custom_login->registration_id > 0)
{
	$out_media_all .= ".registration_form .small
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

if(!($obj_custom_login->lost_password_id > 0))
{
	$obj_custom_login->lost_password_id = apply_filters('get_block_search', 0, 'mf/customlost');
}

if($obj_custom_login->lost_password_id > 0)
{
	$out_media_all .= ".lost_password_form form button
	{
		width: 100%;
	}

	.lost_password_form form p
	{
		margin-top: .5em;
		text-align: center;
	}";
}

if(!($obj_custom_login->logged_in_id > 0))
{
	$obj_custom_login->logged_in_id = apply_filters('get_block_search', 0, 'mf/customloggedin');
}

if($obj_custom_login->logged_in_id > 0)
{
	$out_media_all .= "header .logged_in_info > .section
	{
		display: flex;
		float: right;
	}

		header .logged_in_info > .section > *
		{
			display: block;
			flex: 1 1 0;
		}

			header .logged_in_info > .section > * + *
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
			display: block;
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

// Always make sure that something is echoed so that the cache does not notify
/*if($out_media_all != '')
{*/
	echo "@media all{".$out_media_all."}";
//}

if($out_media_mobile != '')
{
	echo "@media (min-width: 740px){".$out_media_mobile."}";
}