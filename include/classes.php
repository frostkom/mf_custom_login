<?php

class mf_custom_login
{
	function __construct()
	{
		$this->error = "";
	}

	function login_init()
	{
		if(get_option('setting_custom_login_allow_direct_link') == 'yes')
		{
			$this->type = check_var('type');

			if($this->type == 'link')
			{
				$this->username = check_var('username');
				$this->auth = check_var('auth');

				if($this->username != '' && $this->auth != '' && $this->check_auth())
				{
					if($this->login($this->username))
					{
						mf_redirect(user_admin_url());
					}

					else
					{
						$this->error = sprintf(__("I could not log in %s for you. If the problem persists, please contact an admin", 'lang_login'), $this->username);
					}
				}

				else
				{
					$this->error = sprintf(__("It looks like the authorization key for %s has expired so I can not let you in", 'lang_login'), $this->username);
				}
			}
		}
	}
	
	function wp_login_errors($errors)
	{
		if($this->error != '')
		{
			$errors->add('error', $this->error, 'error');
		}

		return $errors;
	}

	function delete_meta($user_id)
	{
		delete_user_meta($user_id, 'meta_login_auth');
	}

	function check_auth()
	{
		global $wpdb;

		$user = get_user_by('login', $this->username);
		
		$meta_login_auth = get_user_meta($user->ID, 'meta_login_auth', true);

		if($this->auth == $meta_login_auth)
		{
			$setting_custom_login_direct_link_expire = get_option('setting_custom_login_direct_link_expire');

			if($setting_custom_login_direct_link_expire > 0)
			{
				list($meta_date, $rest) = explode("_", $meta_login_auth);

				if($meta_date > date("YmdHis", strtotime("-".$setting_custom_login_direct_link_expire." minute")))
				{
					return true;
				}

				else
				{
					return false;
				}
			}

			else
			{
				return true;
			}
		}

		else
		{
			return false;
		}
	}

	function login($username)
	{
		if(is_user_logged_in())
		{
			wp_logout();
		}

		add_filter('authenticate', array($this, 'allow_programmatic_login'), 10, 3); // hook in earlier than other callbacks to short-circuit them
		$user = wp_signon(array('user_login' => $username, 'remember' => true));
		remove_filter('authenticate', array($this, 'allow_programmatic_login'), 10);

		if(is_a($user, 'WP_User'))
		{
			//wp_clear_auth_cookie();
			wp_set_current_user($user->ID);
			//wp_set_auth_cookie($user->ID, true);

			if(is_user_logged_in())
			{
				return true;
			}
		}

		return false;
	}

	function allow_programmatic_login($user, $username, $password)
	{
		return get_user_by('login', $username);
	}

	function wp_login($username)
	{
		$user = get_user_by('login', $username);

		$this->delete_meta($user->ID);
	}

	function wp_logout()
	{
		$this->delete_meta(get_current_user_id());
	}

	function direct_link_text($key, $user_login, $user_data)
	{
		$setting_custom_login_direct_link_expire = get_option('setting_custom_login_direct_link_expire');

		if($setting_custom_login_direct_link_expire > 0)
		{
			$key = date("YmdHis")."_".$key;
		}

		$out = __("To login directly without setting a password, visit the following link", 'lang_login').":"
		."\r\n\r\n".network_site_url("wp-login.php?type=link&auth=".$key."&username=".rawurlencode($user_login), 'login')."\r\n";

		update_user_meta($user_data->ID, 'meta_login_auth', $key);

		return $out;
	}

	function retrieve_password_message($message, $key, $user_login, $user_data)
	{
		/*$message_temp = get_option('setting_custom_login_email_lost_password');

		if($message_temp != '')
		{
			$exclude = $include = array();
			$exclude[] = "[user_login]";		$include[] = $user_login;
			$exclude[] = "[user_email]";		$include[] = $user_data->user_email;
			$exclude[] = "[blogname]";			$include[] = get_option('blogname');
			$exclude[] = "[siteurl]";			$include[] = get_site_url();
			$exclude[] = "[loginurl]";			$include[] = network_site_url("wp-login.php?action=rp&key=".$key."&login=".rawurlencode($user_login), 'login');

			$message = str_replace($exclude, $include, $message_temp);
		}*/

		if(get_option('setting_custom_login_allow_direct_link') == 'yes')
		{
			$message .= "\r\n".$this->direct_link_text($key, $user_login, $user_data);
		}

		return $message;
	}

	function login_form()
	{
		if(get_option('setting_custom_login_allow_direct_link') == 'yes')
		{
			echo "<p id='direct_login_link' class='hide'>
				<a href='#'>".__("Lost Password? Click to get a secure direct link to login instantly", 'lang_login')."</a><br><br>
			</p>";
		}
	}

	function login_footer()
	{
		mf_enqueue_script('script_custom_login', plugin_dir_url(__FILE__)."script.js", array('ajax_url' => admin_url('admin-ajax.php'), 'allow_direct_link' => get_option('setting_custom_login_allow_direct_link')), get_plugin_version(__FILE__));
	}

	function send_direct_link_email()
	{
		$username = check_var('username');

		$user = get_user_by('login', $username);

		if($user->user_email != '')
		{
			$key = md5(AUTH_SALT.$username.$user->user_email);

			$mail_to = $user->user_email;
			$mail_subject = sprintf(__("[%s] Here comes you link to direct login", 'lang_login'), get_bloginfo('name'));
			$mail_content = $this->direct_link_text($key, $username, $user);

			$sent = send_email(array('to' => $mail_to, 'subject' => $mail_subject, 'content' => $mail_content));

			if($sent)
			{
				$result['success'] = true;
				$result['message'] = __("I successfully sent the message to your email. Follow the link in it, and you will be logged in before you know it", 'lang_login');
			}

			else
			{
				$result['error'] = __("I could not send the email", 'lang_login');
			}
		}

		else
		{
			$result['error'] = __("I could not find an email corresponding to the username you entered", 'lang_login');
		}

		header('Content-Type: application/json');
		echo json_encode($result);
		die();
	}
}