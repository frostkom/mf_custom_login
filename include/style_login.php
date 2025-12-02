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
if(wp_is_block_theme() == false)
{
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

	if($login_logo_css != '')
	{
		$out_media_mobile .= ".login #login h1 a
		{"
			.$login_logo_css
		."}";
	}
}
############################

// Widget Styles
############################
$out_media_all .= ".login_form .login_actions
{
	align-items: baseline;
	display: flex;
	gap: 1em;
}";

if(is_plugin_active("secure-passkeys/secure-passkeys.php"))
{
	$out_media_all .= ".widget.login_form .notice
	{
		border-width: .1em;
		border-style: solid;
		border-radius: .5em;
		margin-bottom: 1em;
		padding: 1em 1.5em;
	}

		.widget.login_form .notice.notice-error
		{
			background: #ecc8c5;
			border-color: #b32f2d;
			color: #b32f2d;
		}

		.widget.login_form .notice.notice-success
		{
			background: #def2d6;
			border-color: #5a7052;
			color: #5a7052;
		}
	
	.widget.login_form .secure-passkey-login-wrapper
	{
		border-top: 0;
		margin-top: 0;
		padding-top: 0;
		text-align: left;
	}";
}
############################

echo $out_media_all;

if($out_media_mobile != '')
{
	echo "@media (min-width: 740px){".$out_media_mobile."}";
}