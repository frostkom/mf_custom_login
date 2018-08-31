<?php

class mf_custom_login
{
	function __construct()
	{
		$this->error = "";
	}

	function settings_custom_login()
	{
		$options_area = __FUNCTION__;

		add_settings_section($options_area, "", array($this, $options_area."_callback"), BASE_OPTIONS_PAGE);

		$arr_settings = array();

		if(is_plugin_active('mf_theme_core/index.php'))
		{
			$arr_settings['setting_custom_login_display_theme_logo'] = __("Display Theme Logo", 'lang_login');
		}

		if(get_option('setting_custom_login_display_theme_logo') != 'yes')
		{
			$arr_settings['setting_custom_login_custom_logo'] = __("Custom Logo", 'lang_login');
		}

		$arr_settings['setting_custom_login_page'] = __("Login", 'lang_login');
		$arr_settings['setting_custom_login_register'] = __("Register", 'lang_login');
		$arr_settings['setting_custom_login_lostpassword'] = __("Lost Password", 'lang_login');
		$arr_settings['setting_custom_login_recoverpassword'] = __("Recover Password", 'lang_login');

		if(is_plugin_active('mf_auth/index.php') == false || get_option('setting_auth_active') == 'no')
		{
			$arr_settings['setting_custom_login_allow_direct_link'] = __("Allow Direct Link to Login", 'lang_login');

			if(get_option('setting_custom_login_allow_direct_link') == 'yes')
			{
				$arr_settings['setting_custom_login_direct_link_expire'] = __("Direct Link Expires After", 'lang_login');
			}
		}

		else
		{
			delete_option('setting_custom_login_allow_direct_link');
		}

		//$arr_settings['setting_custom_login_email_lost_password'] = __("Lost Password", 'lang_login');

		show_settings_fields(array('area' => $options_area, 'object' => $this, 'settings' => $arr_settings));
	}

	function settings_custom_login_callback()
	{
		$setting_key = get_setting_key(__FUNCTION__);

		echo settings_header($setting_key, __("Login", 'lang_login'));
	}

	function setting_custom_login_display_theme_logo_callback()
	{
		$setting_key = get_setting_key(__FUNCTION__);
		$option = get_option($setting_key, 'no');

		echo show_select(array('data' => get_yes_no_for_select(), 'name' => $setting_key, 'value' => $option));
	}

	function setting_custom_login_custom_logo_callback()
	{
		$setting_key = get_setting_key(__FUNCTION__);
		$option = get_option($setting_key);

		echo get_media_library(array('name' => $setting_key, 'value' => $option, 'type' => 'image'));
	}

	function setting_custom_login_page_callback()
	{
		$setting_key = get_setting_key(__FUNCTION__);
		$option = get_option($setting_key);

		$arr_data = array();
		get_post_children(array('add_choose_here' => true), $arr_data);

		echo show_select(array('data' => $arr_data, 'name' => $setting_key, 'value' => $option, 'suffix' => "<a href='".admin_url("post-new.php?post_type=page")."'><i class='fa fa-lg fa-plus'></i></a>", 'description' => __("The content from this page is displayed next to the login screen", 'lang_login')));
	}

	function setting_custom_login_register_callback()
	{
		$setting_key = get_setting_key(__FUNCTION__);
		$option = get_option($setting_key);

		$arr_data = array();
		get_post_children(array('add_choose_here' => true), $arr_data);

		echo show_select(array('data' => $arr_data, 'name' => $setting_key, 'value' => $option, 'suffix' => "<a href='".admin_url("post-new.php?post_type=page")."'><i class='fa fa-lg fa-plus'></i></a>", 'description' => __("The content from this page is displayed next to the register screen", 'lang_login')));
	}

	function setting_custom_login_lostpassword_callback()
	{
		$setting_key = get_setting_key(__FUNCTION__);
		$option = get_option($setting_key);

		$arr_data = array();
		get_post_children(array('add_choose_here' => true), $arr_data);

		echo show_select(array('data' => $arr_data, 'name' => $setting_key, 'value' => $option, 'suffix' => "<a href='".admin_url("post-new.php?post_type=page")."'><i class='fa fa-lg fa-plus'></i></a>", 'description' => __("The content from this page is displayed next to the lost password screen", 'lang_login')));
	}

	function setting_custom_login_recoverpassword_callback()
	{
		$setting_key = get_setting_key(__FUNCTION__);
		$option = get_option($setting_key);

		$arr_data = array();
		get_post_children(array('add_choose_here' => true), $arr_data);

		echo show_select(array('data' => $arr_data, 'name' => $setting_key, 'value' => $option, 'suffix' => "<a href='".admin_url("post-new.php?post_type=page")."'><i class='fa fa-lg fa-plus'></i></a>", 'description' => __("The content from this page is displayed next to the recover password screen", 'lang_login')));
	}

	function setting_custom_login_allow_direct_link_callback()
	{
		$setting_key = get_setting_key(__FUNCTION__);
		$option = get_option($setting_key, 'no');

		echo show_select(array('data' => get_yes_no_for_select(), 'name' => $setting_key, 'value' => $option, 'suffix' => __("This will allow users to get a direct link to use instead of username and password", 'lang_login')));
	}

	function setting_custom_login_direct_link_expire_callback()
	{
		$setting_key = get_setting_key(__FUNCTION__);
		$option = get_option($setting_key, 'no');

		echo show_textfield(array('type' => 'number', 'name' => $setting_key, 'value' => $option, 'xtra' => "min='0' max='240'", 'suffix' => __("minutes", 'lang_login')." (".__("0 means never", 'lang_login').")"));
	}

	/*function setting_custom_login_email_lost_password_callback()
	{
		$setting_key = get_setting_key(__FUNCTION__);
		$option = get_option($setting_key);

		echo show_wp_editor(array('name' => $setting_key, 'value' => $option, 'placeholder' => "[user_login], [user_email], [blogname], [siteurl], [loginurl]", 'description' => __("This text replaces the original Lost Password email", 'lang_login')));
	}*/

	function login_init()
	{
		$plugin_include_url = plugin_dir_url(__FILE__);
		$plugin_version = get_plugin_version(__FILE__);

		mf_enqueue_style('style_custom_login', $plugin_include_url."style.php", $plugin_version);
		mf_enqueue_script('script_custom_login', $plugin_include_url."script.js", array('ajax_url' => admin_url('admin-ajax.php'), 'allow_direct_link' => get_option('setting_custom_login_allow_direct_link')), $plugin_version);

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

	function login_message($message)
	{
		global $wpdb;

		$action = check_var('action');

		$post_id = 0;
		$post_title = $post_content = "";

		switch($action)
		{
			case 'register':
				$post_id = get_option('setting_custom_login_register');
			break;

			case 'lostpassword':
				$post_id = get_option('setting_custom_login_lostpassword');
			break;

			case 'rp':
				$post_id = get_option('setting_custom_login_recoverpassword');
			break;
		}

		if(!($post_id > 0))
		{
			$post_id = get_option('setting_custom_login_page');
		}

		if($post_id > 0)
		{
			$result = $wpdb->get_results($wpdb->prepare("SELECT post_title, post_content FROM ".$wpdb->posts." WHERE ID = '%d' AND post_type = 'page' AND post_status = 'publish'", $post_id));

			foreach($result as $r)
			{
				$post_title = $r->post_title;
				$post_content = apply_filters('the_content', $r->post_content);
			}
		}

		if($post_content != '')
		{
			$message .= "<div id='mf_custom_login'>
				<h2>".$post_title."</h2>
				<p>".$post_content."</p>
			</div>";
		}

		return $message;
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

		$out = __("To login directly without setting a password, visit the following link. The link is personal and can only be used once. If this link falls into the wrong hands and you haven't used it they will be able to login to your account without a password.", 'lang_login').":"
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

	function send_direct_link_email()
	{
		$username = check_var('username');

		$user = get_user_by('login', $username);

		if(isset($user->user_email) && $user->user_email != '')
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

	function widgets_init()
	{
		register_widget('widget_registration_form');
		register_widget('widget_login_form');
		register_widget('widget_lost_password_form');
	}
}

class widget_login_form extends WP_Widget
{
	function __construct()
	{
		$widget_ops = array(
			'classname' => 'login_form',
			'description' => __("Display a Login Form", 'lang_login')
		);

		$this->arr_default = array(
			'login_heading' => '',
		);

		parent::__construct('login-widget', __("Login Form", 'lang_login'), $widget_ops);
	}

	function widget($args, $instance)
	{
		global $wpdb, $error_text, $done_text;

		extract($args);

		$instance = wp_parse_args((array)$instance, $this->arr_default);

		if(is_user_logged_in())
		{
			mf_redirect(admin_url());
		}

		else
		{
			$user_login = check_var('log');
			$user_pass = check_var('pwd');
			$user_remember = check_var('rememberme', 'char', true, 'forever');

			echo $before_widget;

				if($instance['login_heading'] != '')
				{
					echo $before_title
						.$instance['login_heading']
					.$after_title;
				}

				/*$display_form = true;

				if(isset($_POST['btnSendLogin']))
				{
					if($user_login == '' || $user_pass == '')
					{
						$error_text = __("You have to enter both Username and Password, then I can process the login for you", 'lang_login');
					}

					else
					{
						$errors = register_new_user($user_login, $user_email);

						if(is_wp_error($errors))
						{
							foreach($errors->errors as $error)
							{
								$error_text = $error[0];
							}
						}

						else
						{
							$done_text = __("I processed the registration for you. You should have a message in your inbox shortly, with instructions on how to complete the registration.", 'lang_login');

							$display_form = false;
						}
					}
				}

				echo get_notification();

				if($display_form == true)
				{*/
					echo "<form method='post' action='".esc_url(site_url('wp-login.php', 'login_post'))."' class='mf_form'>"
						.show_textfield(array('name' => 'log', 'text' => __("Username or E-mail", 'lang_login'), 'value' => $user_login, 'required' => true))
						.show_password_field(array('name' => 'pwd', 'text' => __("Password", 'lang_login'), 'value' => $user_pass, 'required' => true))
						.show_checkbox(array('name' => 'rememberme', 'text' => __("Remember Me", 'lang_login'), 'value' => $user_remember))
						."<div class='form_button'>"
							.show_button(array('name' => 'btnSendLogin', 'text' => __("Log In", 'lang_login')))
						."</div>
					</form>";
				//}

			echo $after_widget;
		}
	}

	function update($new_instance, $old_instance)
	{
		$instance = $old_instance;

		$new_instance = wp_parse_args((array)$new_instance, $this->arr_default);

		$instance['login_heading'] = sanitize_text_field($new_instance['login_heading']);

		return $instance;
	}

	function form($instance)
	{
		global $wpdb;

		$instance = wp_parse_args((array)$instance, $this->arr_default);

		echo "<div class='mf_form'>"
			.show_textfield(array('name' => $this->get_field_name('login_heading'), 'text' => __("Heading", 'lang_login'), 'value' => $instance['login_heading'], 'xtra' => " id='registration-title'"))
		."</div>";
	}
}

class widget_registration_form extends WP_Widget
{
	function __construct()
	{
		$widget_ops = array(
			'classname' => 'registration_form',
			'description' => __("Display a Registration Form", 'lang_login')
		);

		$this->arr_default = array(
			'registration_heading' => '',
			'registration_collect_name' => 'no',
		);

		parent::__construct('registration-widget', __("Registration Form", 'lang_login'), $widget_ops);
	}

	function widget($args, $instance)
	{
		global $wpdb, $error_text, $done_text;

		extract($args);

		$instance = wp_parse_args((array)$instance, $this->arr_default);

		$user_login = check_var('user_login');
		$user_email = check_var('user_email', 'email');

		if($instance['registration_collect_name'] == 'yes')
		{
			$first_name = check_var('first_name');
			$last_name = check_var('last_name');
		}

		echo $before_widget;

			if($instance['registration_heading'] != '')
			{
				echo $before_title
					.$instance['registration_heading']
				.$after_title;
			}

			$display_form = true;

			if(isset($_POST['btnSendRegistration']))
			{
				if($user_login == '' || $user_email == '')
				{
					$error_text = __("You have to enter both Username and E-mail, then I can process the registration for you", 'lang_login');
				}

				else
				{
					$errors = register_new_user($user_login, $user_email);

					if(is_wp_error($errors))
					{
						foreach($errors->errors as $error)
						{
							$error_text = $error[0];
						}
					}

					else
					{
						$done_text = __("I processed the registration for you. You should have a message in your inbox shortly, with instructions on how to complete the registration.", 'lang_login');

						$display_form = false;
					}

					//add_user() //Needs 'user_email' -> 'email'

					/*INSERT INTO wp_users SET user_login = 'username', user_pass = MD5('password'), user_email = 'email', user_registered = NOW(), user_activation_key = '', user_status = '0';
					INSERT INTO wp_usermeta SET user_id = (id), meta_key = 'wp_capabilities', meta_value = 'a:1:{s:13:'administrator';s:1:'1';}';
					INSERT INTO wp_usermeta SET user_id = (id), meta_key = 'wp_user_level', meta_value = '10';*/

					/*$user_password = "";

					$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->users." SET user_login = %s, user_pass = MD5(%s), user_email = %s, user_registered = NOW(), user_activation_key = '', user_status = '0'", $user_login, $user_password, $user_email));
					$user_id = $wpdb->insert_id;

					update_user_meta($user_id, $wpdb->prefix.'capabilities', array(get_option('default_role')));
					update_user_meta($user_id, $wpdb->prefix.'user_level', );

					if($instance['registration_collect_name'] == 'yes')
					{
						update_user_meta($user_id, 'first_name', $first_name);
						update_user_meta($user_id, 'last_name', $last_name);
					}

					//Send email*/
				}
			}

			echo get_notification();

			if($display_form == true)
			{
				echo "<form method='post' action='' class='mf_form'>"
					.show_textfield(array('name' => 'user_login', 'text' => __("Username", 'lang_login'), 'value' => $user_login, 'required' => true))
					.show_textfield(array('name' => 'user_email', 'text' => __("E-mail", 'lang_login'), 'value' => $user_email, 'required' => true));

					if($instance['registration_collect_name'] == 'yes')
					{
						echo "<div class='flex_flow'>"
							.show_textfield(array('name' => 'first_name', 'text' => __("First Name", 'lang_login'), 'value' => $first_name, 'required' => true))
							.show_textfield(array('name' => 'last_name', 'text' => __("Last Name", 'lang_login'), 'value' => $last_name, 'required' => true))
						."</div>";
					}

					echo "<div class='form_button'>"
						.show_button(array('name' => 'btnSendRegistration', 'text' => __("Register", 'lang_login')))
					."</div>
				</form>";
			}

		echo $after_widget;
	}

	function update($new_instance, $old_instance)
	{
		$instance = $old_instance;

		$new_instance = wp_parse_args((array)$new_instance, $this->arr_default);

		$instance['registration_heading'] = sanitize_text_field($new_instance['registration_heading']);
		$instance['registration_collect_name'] = sanitize_text_field($new_instance['registration_collect_name']);

		return $instance;
	}

	function form($instance)
	{
		global $wpdb;

		$instance = wp_parse_args((array)$instance, $this->arr_default);

		echo "<div class='mf_form'>"
			.show_textfield(array('name' => $this->get_field_name('registration_heading'), 'text' => __("Heading", 'lang_login'), 'value' => $instance['registration_heading'], 'xtra' => " id='registration-title'"))
			.show_select(array('data' => get_yes_no_for_select(), 'name' => $this->get_field_name('registration_collect_name'), 'text' => __("Collect full name from user", 'lang_login'), 'value' => $instance['registration_collect_name']))
		."</div>";
	}
}

class widget_lost_password_form extends WP_Widget
{
	function __construct()
	{
		$widget_ops = array(
			'classname' => 'lost_password_form',
			'description' => __("Display a Lost Password Form", 'lang_login')
		);

		$this->arr_default = array(
			'lost_password_heading' => '',
		);

		parent::__construct('lost-password-widget', __("Lost Password Form", 'lang_login'), $widget_ops);
	}

	function retrieve_password($login)
	{
		$errors = new WP_Error();

		$user_data = get_user_by((strpos($login, '@') ? 'email' : 'login'), $login);

		if(!isset($user_data->user_login))
		{
			$errors->add('invalidcombo', __("Invalid Username or E-mail", 'lang_login'));

			return $errors;
		}

		else
		{
			$user_login = $user_data->user_login;
			$user_email = $user_data->user_email;
			$key = get_password_reset_key($user_data);

			if(is_wp_error($key))
			{
				return $key;
			}

			else
			{
				if(is_multisite())
				{
					$site_name = get_network()->site_name;
				}
				
				else
				{
					/*
					 * The blogname option is escaped with esc_html on the way into the database
					 * in sanitize_option we want to reverse this for the plain text arena of emails.
					 */
					$site_name = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
				}

				$lost_password_link = network_site_url("wp-login.php?action=rp&key=".$key."&login=".rawurlencode($user_login), 'login');

				$message = "<p>"
					.__("Someone has requested a password reset for the following account", 'lang_login').":<br>"
					//.__("Site Name", 'lang_login').": ".$site_name."<br>"
					.__("Username", 'lang_login').": ".$user_login
				."</p>"
				."<p>".__("If this was a mistake, just ignore this email and nothing will happen.", 'lang_login')."</p>"
				."<p>"
					.__("To reset your password, visit the following address").":<br>"
					."<a href='".$lost_password_link."'>".$lost_password_link."</a>
				</p>";

				$title = sprintf(__("[%s] Password Reset", 'lang_login'), $site_name);

				$title = apply_filters('retrieve_password_title', $title, $user_login, $user_data);
				$message = apply_filters('retrieve_password_message', $message, $key, $user_login, $user_data);

				if(send_email(array('to' => $user_email, 'subject' => wp_specialchars_decode($title), 'content' => $message)))
				{
					return true;
				}

				else
				{
					wp_die(__("The email could not be sent.", 'lang_login')."<br>\n".__("Possible reason: your host may have disabled the mail() function.", 'lang_login'));
				}
			}
		}
	}

	function widget($args, $instance)
	{
		global $wpdb, $error_text, $done_text;

		extract($args);

		$instance = wp_parse_args((array)$instance, $this->arr_default);

		$user_login = check_var('user_login');

		echo $before_widget;

			if($instance['lost_password_heading'] != '')
			{
				echo $before_title
					.$instance['lost_password_heading']
				.$after_title;
			}

			$display_form = true;

			if(isset($_POST['btnSendLostPassword']))
			{
				if($user_login == '')
				{
					$error_text = __("You have to enter the e-mail address, then I can process the request for you", 'lang_login');
				}

				else
				{
					$errors = $this->retrieve_password($user_login);
					
					if(is_wp_error($errors))
					{
						foreach($errors->errors as $error)
						{
							$error_text = $error[0];
						}
					}

					else
					{
						$done_text = __("I found the account that you were looking for. Please, check your inbox for the confirmation link.", 'lang_login');

						$display_form = false;
					}
				}
			}

			echo get_notification();

			if($display_form == true)
			{
				echo "<form method='post' action='' class='mf_form'>" //".esc_url(network_site_url('wp-login.php?action=lostpassword', 'login_post'))."
					.show_textfield(array('name' => 'user_login', 'text' => __("Username or E-mail", 'lang_login'), 'value' => $user_login, 'required' => true))
					."<div class='form_button'>"
						.show_button(array('name' => 'btnSendLostPassword', 'text' => __("Get New Password", 'lang_login')))
					."</div>
				</form>";
			}

		echo $after_widget;
	}

	function update($new_instance, $old_instance)
	{
		$instance = $old_instance;

		$new_instance = wp_parse_args((array)$new_instance, $this->arr_default);

		$instance['lost_password_heading'] = sanitize_text_field($new_instance['lost_password_heading']);

		return $instance;
	}

	function form($instance)
	{
		global $wpdb;

		$instance = wp_parse_args((array)$instance, $this->arr_default);

		echo "<div class='mf_form'>"
			.show_textfield(array('name' => $this->get_field_name('lost_password_heading'), 'text' => __("Heading", 'lang_login'), 'value' => $instance['lost_password_heading'], 'xtra' => " id='registration-title'"))
		."</div>";
	}
}