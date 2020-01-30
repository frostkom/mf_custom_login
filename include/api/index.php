<?php

if(!defined('ABSPATH'))
{
	header('Content-Type: application/json');

	$folder = str_replace("/wp-content/plugins/mf_custom_login/include/api", "/", dirname(__FILE__));

	require_once($folder."wp-load.php");
}

$json_output = array();

$action = check_var('action', 'char');

switch($action)
{
	case 'login':
		if(is_ssl())
		{
			if(get_option('setting_custom_login_allow_api') == 'yes')
			{
				if(!is_user_logged_in())
				{
					$user_login = check_var('user_login', 'char', true, '', false, 'post');
					$user_pass = check_var('user_pass', 'char', true, '', false, 'post');

					if($user_login == '')
					{
						$user_login = check_var('username', 'char', true, '', false, 'post');
					}

					if($user_pass == '')
					{
						$user_pass = check_var('password', 'char', true, '', false, 'post');
					}

					$obj_custom_login = new mf_custom_login();

					$result = $obj_custom_login->do_login(array('user_login' => $user_login, 'user_pass' => $user_pass));

					$json_output = $result;

					if($user_login != '' && $user_pass != '' && $result['success'] == true)
					{
						header("Status: 200 OK");
					}

					else
					{
						header("Status: 401 Unauthorized");

						$json_output['error'] = sprintf(__("You have not provided the correct login credentials. They should be sent by %s with the variables %s and %s.", 'lang_login'), "POST", "user_login", "user_pass");
					}
				}

				else
				{
					header("Status: 401 Unauthorized");

					$json_output['error'] = sprintf(__("You are already logged in as %s", 'lang_login'), get_user_info());
				}
			}

			else
			{
				header("Status: 503 Forbidden");

				$json_output['error'] = __("The API login is inactivated on this site", 'lang_login');
			}
		}

		else
		{
			header("Status: 503 Forbidden");

			$json_output['error'] = __("You are not using SSL, therefor I cannot process your request", 'lang_login');
		}
	break;

	default:
		header("Status: 503 Unknown Action");

		$json_output['error'] = __("Unknown Action", 'lang_login').": ".$action;
	break;
}

echo json_encode($json_output);