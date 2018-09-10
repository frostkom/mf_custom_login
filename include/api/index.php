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
			$user_login = check_var('user_login');
			$user_pass = check_var('user_pass');

			$obj_custom_login = new mf_custom_login();

			$result = $obj_custom_login->do_login(array('user_login' => $user_login, 'user_pass' => $user_pass));

			$json_output = $result;

			if($result['success'] == true)
			{
				header("Status: 200 OK");
			}

			else
			{
				header("Status: 401 Unauthorized");
			}
		}

		else
		{
			header("Status: 503 Forbidden");

			$json_output['error'] = __("You are not using SSL, therefor I cannot process your request", 'lang_login').": ".$action;
		}
	break;

	default:
		header("Status: 503 Unknown Action");

		$json_output['error'] = __("Unknown Action", 'lang_login').": ".$action;
	break;
}

echo json_encode($json_output);