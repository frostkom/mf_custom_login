<?php

class mf_custom_login
{
	function __construct()
	{
		$this->error = "";
	}

	function cron_base()
	{
		global $wpdb;

		$obj_cron = new mf_cron();
		$obj_cron->start(__CLASS__);

		if($obj_cron->is_running == false)
		{
			if(get_option('setting_custom_login_allow_direct_link') == 'yes')
			{
				$setting_custom_login_direct_link_expire = get_option('setting_custom_login_direct_link_expire');

				if($setting_custom_login_direct_link_expire > 0)
				{
					$users = get_users(array('fields' => array('ID')));

					$obj_custom_login = new mf_custom_login();

					foreach($users as $user)
					{
						$meta_login_auth = get_user_meta($user->ID, 'meta_login_auth', true);

						if($meta_login_auth != '')
						{
							list($meta_date, $rest) = explode("_", $meta_login_auth);

							if($meta_date < date("YmdHis", strtotime("-".$setting_custom_login_direct_link_expire." minute")))
							{
								delete_user_meta($user->ID, 'meta_login_auth');

								//do_log("Removed meta_login_auth for ".$user->ID);
							}
						}
					}
				}
			}
		}

		$obj_cron->end();
	}

	function do_login($data = array())
	{
		if(!isset($data['user_login'])){		$data['user_login'] = '';}
		if(!isset($data['user_pass'])){			$data['user_pass'] = '';}
		if(!isset($data['user_remember'])){		$data['user_remember'] = '';}
		if(!isset($data['redirect_to'])){		$data['redirect_to'] = '';}

		$data['user_login'] = strtolower($data['user_login']);

		$secure_cookie = '';

		$user = wp_signon(array('user_login' => $data['user_login'], 'user_password' => $data['user_pass'], 'remember' => $data['user_remember']), $secure_cookie);

		$requested_redirect_to = check_var('redirect_to');

		$data['redirect_to'] = apply_filters('login_redirect', $data['redirect_to'], $requested_redirect_to, $user);

		if(!is_wp_error($user))
		{
			if(empty($data['redirect_to']) || $data['redirect_to'] == 'wp-admin/' || $data['redirect_to'] == admin_url())
			{
				// If the user doesn't belong to a blog, send them to user admin. If the user can't edit posts, send them to their profile.
				if(is_multisite() && !get_active_blog_for_user($user->ID) && !is_super_admin($user->ID))
				{
					$data['redirect_to'] = user_admin_url();
				}

				else if(is_multisite() && !$user->has_cap('read'))
				{
					$data['redirect_to'] = get_dashboard_url($user->ID);
				}

				else if(!$user->has_cap('edit_posts'))
				{
					$data['redirect_to'] = $user->has_cap('read') ? admin_url('profile.php') : home_url();
				}
			}

			return array(
				'success' => true,
				'redirect' => $data['redirect_to'],
			);
		}

		else
		{
			foreach($user->errors as $error)
			{
				//$error_text = $error[0];

				return array(
					'success' => false,
					'error' => $error[0],
				);

				break;
			}
		}
	}

	function check_if_logged_in($data = array())
	{
		global $error_text;

		if(!isset($data['redirect'])){	$data['redirect'] = false;}

		if(is_user_logged_in())
		{
			if($data['redirect'] == true)
			{
				mf_redirect(admin_url());
			}

			else
			{
				$user_data = get_userdata(get_current_user_id());

				$done_text = sprintf(__("You are already logged in as %s. Would you like to go to %sadmin%s or %slog out %s?", 'lang_login'), $user_data->user_login, "<a href='".admin_url()."'>", "</a>", "<a href='".wp_logout_url()."'>", "</a>");
			}
		}
	}

	function settings_custom_login()
	{
		$options_area = __FUNCTION__;

		add_settings_section($options_area, "", array($this, $options_area."_callback"), BASE_OPTIONS_PAGE);

		$has_login_widget = (apply_filters('get_widget_search', 'login-widget') > 0);
		$users_can_register = get_option('users_can_register');
		$has_registration_widget = ($users_can_register ? (apply_filters('get_widget_search', 'registration-widget') > 0) : false);
		$has_lost_password_post_widget = (apply_filters('get_widget_search', 'lost-password-widget'));

		$arr_settings = array();

		if($has_login_widget == false && $has_registration_widget == false && $has_lost_password_post_widget == false)
		{
			if(is_plugin_active('mf_theme_core/index.php'))
			{
				$arr_settings['setting_custom_login_display_theme_logo'] = __("Display Theme Logo", 'lang_login');
			}

			if(get_option('setting_custom_login_display_theme_logo') != 'yes')
			{
				$arr_settings['setting_custom_login_custom_logo'] = __("Custom Logo", 'lang_login');
			}
		}

		if($has_login_widget == false)
		{
			$arr_settings['setting_custom_login_page'] = __("Login", 'lang_login');
		}

		if(is_multisite())
		{
			$arr_settings['users_can_register'] = __("Allow Registration", 'lang_login');

			if($users_can_register)
			{
				$arr_settings['default_role'] = __("Default Role", 'lang_login');

				if($has_registration_widget == false)
				{
					$arr_settings['setting_custom_login_register'] = __("Register", 'lang_login');
				}
			}
		}

		else if($users_can_register && $has_registration_widget == false)
		{
			$arr_settings['setting_custom_login_register'] = __("Register", 'lang_login');
		}

		if($has_lost_password_post_widget == false)
		{
			$arr_settings['setting_custom_login_lostpassword'] = __("Lost Password", 'lang_login');
			$arr_settings['setting_custom_login_recoverpassword'] = __("Recover Password", 'lang_login');
		}

		if(is_plugin_active('mf_auth/index.php') == false || get_option('setting_auth_active') == 'no')
		{
			$arr_settings['setting_custom_login_allow_direct_link'] = __("Allow Direct Link to Login", 'lang_login');

			if(get_option('setting_custom_login_allow_direct_link') == 'yes')
			{
				$arr_settings['setting_custom_login_direct_link_expire'] = __("Direct Link Expires After", 'lang_login');
				$arr_settings['setting_custom_login_direct_link_expire_after_login'] = __("Direct Link Expires After Login", 'lang_login');
			}
		}

		else
		{
			delete_option('setting_custom_login_allow_direct_link');
		}

		if(substr(get_home_url(), 0, 5) == 'https')
		{
			$arr_settings['setting_custom_login_allow_api'] = __("Allow API Login", 'lang_login');

			if(isset($_SERVER['PHP_AUTH_USER']))
			{
				$arr_settings['setting_custom_login_allow_server_auth'] = __("Allow Server Authentication", 'lang_login');
			}
		}

		else
		{
			delete_option('setting_custom_login_allow_api');
			delete_option('setting_custom_login_allow_server_auth');
		}

		$arr_settings['setting_custom_login_info'] = __("Information", 'lang_login');

		if(get_option('users_can_register'))
		{
			$arr_settings['setting_custom_login_email_admin_registration'] = __("Registration Email Content to Admin", 'lang_login');
			$arr_settings['setting_custom_login_email_registration'] = __("Registration Email Content", 'lang_login');
		}

		$arr_settings['setting_custom_login_email_lost_password'] = __("Lost Password Email Content", 'lang_login');

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

		echo show_select(array('data' => $arr_data, 'name' => $setting_key, 'value' => $option, 'suffix' => get_option_page_suffix(array('value' => $option)), 'description' => __("The content from this page is displayed next to the login screen", 'lang_login')));
	}

	function users_can_register_callback()
	{
		$setting_key = get_setting_key(__FUNCTION__);
		$option = get_option($setting_key, '0');

		echo show_select(array('data' => get_yes_no_for_select(array('return_integer' => true)), 'name' => $setting_key, 'value' => $option));
	}

	function default_role_callback()
	{
		$setting_key = get_setting_key(__FUNCTION__);
		$option = get_option($setting_key);

		echo show_select(array('data' => get_roles_for_select(array('use_capability' => false)), 'name' => $setting_key, 'value' => $option));
	}

	function setting_custom_login_register_callback()
	{
		$setting_key = get_setting_key(__FUNCTION__);
		$option = get_option($setting_key);

		$arr_data = array();
		get_post_children(array('add_choose_here' => true), $arr_data);

		echo show_select(array('data' => $arr_data, 'name' => $setting_key, 'value' => $option, 'suffix' => get_option_page_suffix(array('value' => $option)), 'description' => __("The content from this page is displayed next to the register screen", 'lang_login')));
	}

	function setting_custom_login_lostpassword_callback()
	{
		$setting_key = get_setting_key(__FUNCTION__);
		$option = get_option($setting_key);

		$arr_data = array();
		get_post_children(array('add_choose_here' => true), $arr_data);

		echo show_select(array('data' => $arr_data, 'name' => $setting_key, 'value' => $option, 'suffix' => get_option_page_suffix(array('value' => $option)), 'description' => __("The content from this page is displayed next to the lost password screen", 'lang_login')));
	}

	function setting_custom_login_recoverpassword_callback()
	{
		$setting_key = get_setting_key(__FUNCTION__);
		$option = get_option($setting_key);

		$arr_data = array();
		get_post_children(array('add_choose_here' => true), $arr_data);

		echo show_select(array('data' => $arr_data, 'name' => $setting_key, 'value' => $option, 'suffix' => get_option_page_suffix(array('value' => $option)), 'description' => __("The content from this page is displayed next to the recover password screen", 'lang_login')));
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
		$option = get_option($setting_key);

		echo show_textfield(array('type' => 'number', 'name' => $setting_key, 'value' => $option, 'xtra' => "min='0' max='240'", 'suffix' => __("minutes", 'lang_login')." (".__("0 means never", 'lang_login').")"));
	}

	function setting_custom_login_direct_link_expire_after_login_callback()
	{
		$setting_key = get_setting_key(__FUNCTION__);
		$option = get_option($setting_key, 'yes');

		echo show_select(array('data' => get_yes_no_for_select(), 'name' => $setting_key, 'value' => $option));
	}

	function setting_custom_login_allow_api_callback()
	{
		$setting_key = get_setting_key(__FUNCTION__);
		$option = get_option($setting_key, 'no');

		echo show_select(array('data' => get_yes_no_for_select(), 'name' => $setting_key, 'value' => $option));

		if($option == 'yes')
		{
			$api_url = plugin_dir_url(__FILE__)."api/?action=login";

			$user_data = get_userdata(get_current_user_id());

			echo "<p>".sprintf(__("By sending a request to %s with %s and %s as %s you will get a success or failure as a response", 'lang_login'), "<code>".$api_url."</code>", "<code>user_login</code>", "<code>user_pass</code>", "<code>POST</code>")."</p>";
		}
	}

	function setting_custom_login_allow_server_auth_callback()
	{
		$setting_key = get_setting_key(__FUNCTION__);
		$option = get_option($setting_key, 'no');

		echo show_select(array('data' => get_yes_no_for_select(), 'name' => $setting_key, 'value' => $option));
	}

	function setting_custom_login_info_callback()
	{
		$tags = array("[first_name]", "[user_login]", "[username]", "[user_email]", "[blog_title]", "[site_url]", "[confirm_link]", "[login_link]");

		if(get_option('setting_custom_login_allow_direct_link') == 'yes')
		{
			$tags[] = "[direct_link]";

			if(apply_filters('get_widget_search', 'registration-widget') > 0)
			{
				$tags[] = "[direct_registration_link]";
			}
		}

		echo sprintf(__("To take advantage of dynamic data, you can use the following placeholders: %s", 'lang_login'), sprintf('<code>%s</code>', implode('</code>, <code>', $tags)));
	}

	function setting_custom_login_email_admin_registration_callback()
	{
		$setting_key = get_setting_key(__FUNCTION__);
		$option = get_option_or_default($setting_key, sprintf(__("A new user (%s) has been created", 'lang_login'), "[user_login]"));

		echo show_wp_editor(array('name' => $setting_key, 'value' => $option, 'editor_height' => 200));
	}

	function setting_custom_login_email_registration_callback()
	{
		$setting_key = get_setting_key(__FUNCTION__);
		$option = get_option_or_default($setting_key, __("Username", 'lang_login').": [user_login]\r\n\r\n"
		.__("To set your password, visit the following address", 'lang_login').": [confirm_link]");

		echo show_wp_editor(array('name' => $setting_key, 'value' => $option, 'editor_height' => 200));
	}

	function setting_custom_login_email_lost_password_callback()
	{
		$setting_key = get_setting_key(__FUNCTION__);
		$option = get_option_or_default($setting_key, __("Someone has requested a password reset for the following account.", 'lang_login')."\r\n"
		.__("Username", 'lang_login').": [user_login]\r\n\r\n"
		.__("If this was a mistake, just ignore this email and nothing will happen.", 'lang_login')."\r\n\r\n"
		.__("To reset your password, visit the following address", 'lang_login').": [confirm_link]");

		echo show_wp_editor(array('name' => $setting_key, 'value' => $option, 'editor_height' => 200));
	}

	function admin_init()
	{
		global $pagenow;

		if(in_array($pagenow, array('user-edit.php', 'profile.php')) && IS_ADMIN && get_option('setting_custom_login_allow_direct_link') == 'yes')
		{
			$plugin_include_url = plugin_dir_url(__FILE__);
			$plugin_version = get_plugin_version(__FILE__);

			mf_enqueue_script('script_login_profile', $plugin_include_url."script_profile.js", array('ajax_url' => admin_url('admin-ajax.php')), $plugin_version);
		}
	}

	function user_row_actions($actions, $user)
	{
		if(get_option('setting_custom_login_allow_direct_link') == 'yes' && current_user_can('edit_user') && isset($user->roles[0]) && $user->roles[0] != '')
		{
			$meta_login_auth = get_user_meta($user->ID, 'meta_login_auth', true);

			if($meta_login_auth != '')
			{
				$actions['direct_link'] = "<a href='".$this->direct_link_url(array('key' => $meta_login_auth, 'user_meta_exists' => true, 'user_data' => $user, 'type' => 'users'))."'>".__("Direct Link", 'lang_login')."</a>";
			}
		}

		return $actions;
	}

	function edit_user_profile($user)
	{
		if(IS_ADMIN && get_option('setting_custom_login_allow_direct_link') == 'yes')
		{
			echo "<table class='form-table'>
				<tr>
					<th><label>".__("Direct Link", 'lang_login')."</label></th>
					<td>";

						$meta_login_auth = get_user_meta($user->ID, 'meta_login_auth', true);

						if($meta_login_auth != '')
						{
							echo "<p><a href='".$this->direct_link_url(array('key' => $meta_login_auth, 'user_meta_exists' => true, 'user_data' => $user, 'type' => 'profile'))."'>".__("URL", 'lang_login')."</a></p>
							<div>"
								.show_submit(array('type' => 'button', 'name' => 'btnDirectLoginRevoke', 'text' => __("Revoke", 'lang_login'), 'class' => "button-secondary", 'xtra' => "data-user-id='".$user->ID."'"))
							."</div>";
						}

						else
						{
							echo show_submit(array('type' => 'button', 'name' => 'btnDirectLoginCreate', 'text' => __("Generate Now", 'lang_login'), 'class' => "button-secondary", 'xtra' => "data-user-id='".$user->ID."'"));
						}

						echo "<div id='direct_login_debug'></div>
					</td>
				</tr>
			</table>";
		}
	}

	function combined_head()
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
					if($this->direct_link_login($this->username))
					{
						//$redirect_to = user_admin_url();
						$redirect_to = admin_url();

						$user = get_user_by('login', $this->username);

						$redirect_to = apply_filters('login_redirect', $redirect_to, $redirect_to, $user);

						mf_redirect($redirect_to);
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

	function login_headertext()
	{
		return get_bloginfo('name');
	}

	function login_init()
	{
		$this->combined_head();
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

	function check_auth()
	{
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

	function direct_link_login($username)
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
		if(get_option('setting_custom_login_direct_link_expire_after_login') != 'no')
		{
			$user = get_user_by('login', $username);

			delete_user_meta($user->ID, 'meta_login_auth');
		}
	}

	function wp_logout()
	{
		if(get_option('setting_custom_login_direct_link_expire_after_login') != 'no')
		{
			delete_user_meta(get_current_user_id(), 'meta_login_auth');
		}
	}

	function direct_link_url($data)
	{
		if(!isset($data['user_meta_exists'])){		$data['user_meta_exists'] = false;}
		if(!isset($data['key'])){					$data['key'] = md5(AUTH_SALT.$data['user_data']->user_login.$data['user_data']->user_email);}
		if(!isset($data['type'])){					$data['type'] = '';}

		if($data['user_meta_exists'] == false)
		{
			$setting_custom_login_direct_link_expire = get_option('setting_custom_login_direct_link_expire');

			if($setting_custom_login_direct_link_expire > 0)
			{
				$data['key'] = date("YmdHis")."_".$data['key'];
			}

			update_user_meta($data['user_data']->ID, 'meta_login_auth', $data['key']);
		}

		return network_site_url(apply_filters('filter_direct_link_url', "wp-login.php?type=link&auth=".$data['key']."&username=".rawurlencode($data['user_data']->user_login), $data), 'login');
	}

	function direct_link_text($data)
	{
		$data['type'] = 'login';

		$out = __("To login directly without setting a password, visit the following link. The link is personal and can only be used once. If this link falls into the wrong hands and you haven't used it they will be able to login to your account without a password.", 'lang_login').":"
		."\r\n\r\n".$this->direct_link_url($data)."\r\n";

		return $out;
	}

	function get_registration_key($user)
	{
		global $wpdb, $wp_hasher;

		$key = wp_generate_password(20, false);

		do_action('retrieve_password_key', $user->user_login, $key);

		if(empty($wp_hasher))
		{
			require_once ABSPATH.WPINC."/class-phpass.php";
			$wp_hasher = new PasswordHash(8, true);
		}

		$hashed = time().":".$wp_hasher->HashPassword($key);

		$wpdb->update($wpdb->users, array('user_activation_key' => $hashed), array('user_login' => $user->user_login));

		return $key;
	}

	function email_replace_shortcodes($string, $user_data, $key = '')
	{
		if($key == '')
		{
			$key = $this->get_registration_key($user_data);
		}

		$blog_title = get_option('blogname');
		$site_url = get_site_url();
		$login_url = wp_login_url();
		$lost_password_url = wp_lostpassword_url();
		$confirm_link_action = "action=rp&key=".$this->get_registration_key($user_data)."&login=".rawurlencode($user_data->user_login);

		$exclude = $include = array();
		$exclude[] = "[user_login]";		$include[] = $user_data->user_login;
		$exclude[] = "[first_name]";		$include[] = $user_data->first_name;
		$exclude[] = "[username]";			$include[] = $user_data->display_name;
		$exclude[] = "[user_email]";		$include[] = $user_data->user_email;

		//wp_new_user_notification_email
		$exclude[] = "[blog_title]";		$include[] = $blog_title;
		$exclude[] = "[site_url]";			$include[] = $site_url;
		$exclude[] = "[confirm_link]";		$include[] = $lost_password_url.(preg_match("/\?/", $lost_password_url) ? "&" : "?").$confirm_link_action;
		$exclude[] = "[login_link]";		$include[] = $login_url;

		if(get_option('setting_custom_login_allow_direct_link') == 'yes' && preg_match("/\[direct_link]/", $string))
		{
			$direct_link = $this->direct_link_url(array('user_data' => $user_data, 'type' => 'registration'));

			$exclude[] = "[direct_link]";		$include[] = $direct_link;

			$direct_registration_link = "";

			if(isset($user_data->roles) && in_array('administrator', $user_data->roles))
			{
				$registration_post_id = apply_filters('get_widget_search', 'registration-widget');

				if($registration_post_id > 0)
				{
					$registration_post_url = get_permalink($registration_post_id);
					$registration_post_url = str_replace(get_site_url(), "", $registration_post_url);

					$direct_registration_link = $direct_link.(preg_match("/\?/", $direct_link) ? "&" : "?")."redirect_to=".$registration_post_url;
				}
			}

			$exclude[] = "[direct_registration_link]";		$include[] = $direct_registration_link;
		}

		//retrieve_password_message
		$exclude[] = "[blogname]";			$include[] = $blog_title; //Replace with blog_title
		$exclude[] = "[siteurl]";			$include[] = $site_url; //Replace with site_url
		$exclude[] = "[loginurl]";			$include[] = $lost_password_url.(preg_match("/\?/", $lost_password_url) ? "&" : "?").$confirm_link_action; //Replace with confirm_link

		return str_replace($exclude, $include, $string);
	}

	function wp_new_user_notification_email_admin($array, $user_data)
	{
		$option = get_option('setting_custom_login_email_admin_registration');

		if($option != '')
		{
			$array['message'] = $this->email_replace_shortcodes($option, $user_data);
		}

		return $array;
	}

	function wp_new_user_notification_email($array, $user_data)
	{
		$option = get_option('setting_custom_login_email_registration');

		if($option != '')
		{
			$array['message'] = $this->email_replace_shortcodes($option, $user_data);
		}

		return $array;
	}

	function retrieve_password_message($message, $key, $user_login, $user)
	{
		$option = get_option('setting_custom_login_email_lost_password');

		if($option != '')
		{
			$message = $this->email_replace_shortcodes($option, $user, $key);
		}

		if(get_option('setting_custom_login_allow_direct_link') == 'yes')
		{
			$message .= "\r\n".$this->direct_link_text(array('key' => $key, 'user_data' => $user));
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

	function wp_head()
	{
		$post_id = apply_filters('get_widget_search', 'login-widget');

		if($post_id > 0)
		{
			$this->combined_head();
		}
	}

	function body_class($classes)
	{
		global $post;

		if(isset($post) && isset($post->ID) && $post->ID > 0)
		{
			if(apply_filters('get_widget_search', 'login-widget') == $post->ID || apply_filters('get_widget_search', 'registration-widget') == $post->ID || apply_filters('get_widget_search', 'lost-password-widget') == $post->ID)
			{
				$classes[] = "is_login_page";
			}
		}

		return $classes;
	}

	function is_public_page($out)
	{
		$site_url = get_site_url();
		@list($request_uri, $rest) = explode("?", $_SERVER['REQUEST_URI'], 2);

		$arr_widget_search = array('login-widget', 'registration-widget', 'lost-password-widget');

		foreach($arr_widget_search as $widget_key)
		{
			if($out == true)
			{
				$post_id = apply_filters('get_widget_search', $widget_key);

				if($post_id > 0)
				{
					if(str_replace($site_url, "", get_permalink($post_id)) == $request_uri)
					{
						$out = false;
					}
				}
			}
		}

		return $out;
	}

	function login_url($url)
	{
		$post_id = apply_filters('get_widget_search', 'login-widget');

		if($post_id > 0)
		{
			$url = get_permalink($post_id);
		}

		return $url;
	}

	function register_url($url)
	{
		$post_id = apply_filters('get_widget_search', 'registration-widget');

		if($post_id > 0)
		{
			$url = get_permalink($post_id);
		}

		return $url;
	}

	function lostpassword_url($url)
	{
		$post_id = apply_filters('get_widget_search', 'lost-password-widget');

		if($post_id > 0)
		{
			$url = get_permalink($post_id);
		}

		return $url;
	}

	function logout_url($url)
	{
		$post_id = apply_filters('get_widget_search', 'login-widget');

		if($post_id > 0)
		{
			$url = get_permalink($post_id)."?action=logout";
			$url = wp_nonce_url($url, 'log-out');
		}

		return $url;
	}

	function determine_current_user($user_id)
	{
		if(!($user_id > 0) && isset($_SERVER['PHP_AUTH_USER']) && $_SERVER['PHP_AUTH_USER'] != '' && isset($_SERVER['PHP_AUTH_PW']) && $_SERVER['PHP_AUTH_PW'] != '' && get_option('setting_custom_login_allow_server_auth') == 'yes')
		{
			$user_data = get_user_by('login', $_SERVER['PHP_AUTH_USER']);

			if(isset($user_data->ID) && $user_data->ID > 0)
			{
				// Would this even matter since you are allowed on the server?
				/*if(isset($user_data->user_pass) && wp_check_password($_SERVER['PHP_AUTH_PW'], $user_data->user_pass))
				{*/
					$user_id = $user_data->ID;
				//}
			}
		}

		return $user_id;
	}

	function create_direct_login()
	{
		$user_id = check_var('user_id');

		if($user_id > 0)
		{
			if(IS_ADMIN && get_option('setting_custom_login_allow_direct_link') == 'yes')
			{
				$user_data = get_userdata($user_id);

				$result['success'] = true;
				$result['message'] = "<a href='".$this->direct_link_url(array('user_data' => $user_data, 'type' => 'profile'))."'>".__("URL", 'lang_login')."</a>";
			}

			else
			{
				$result['error'] = __("You do not have the rights to perform this action", 'lang_login');
			}
		}

		else
		{
			$result['error'] = __("There was no User ID attached to the request", 'lang_login');
		}

		header('Content-Type: application/json');
		echo json_encode($result);
		die();
	}

	function revoke_direct_login()
	{
		$user_id = check_var('user_id');

		if($user_id > 0)
		{
			if(IS_ADMIN && get_option('setting_custom_login_allow_direct_link') == 'yes')
			{
				delete_user_meta($user_id, 'meta_login_auth');

				$result['success'] = true;
				$result['message'] = __("The direct login link has been revoked and can not be used anymore", 'lang_login');
			}

			else
			{
				$result['error'] = __("You do not have the rights to perform this action", 'lang_login');
			}
		}

		else
		{
			$result['error'] = __("There was no User ID attached to the request", 'lang_login');
		}

		header('Content-Type: application/json');
		echo json_encode($result);
		die();
	}

	function send_direct_link_email()
	{
		$username = check_var('username');

		$user = get_user_by('login', $username);

		if(isset($user->user_email) && $user->user_email != '')
		{
			$mail_to = $user->user_email;
			$mail_subject = sprintf(__("[%s] Here comes you link to direct login", 'lang_login'), get_bloginfo('name'));
			$mail_content = $this->direct_link_text(array('user_data' => $user));

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
		register_widget('widget_login_form');

		if(get_option('users_can_register'))
		{
			register_widget('widget_registration_form');
		}

		register_widget('widget_lost_password_form');
		register_widget('widget_logged_in_info');
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
			'login_image' => '',
			'login_heading' => '',
			//'login_above_form' => '',
		);

		parent::__construct('login-widget', __("Login Form", 'lang_login'), $widget_ops);
	}

	function widget($args, $instance)
	{
		global $error_text, $done_text;

		extract($args);
		$instance = wp_parse_args((array)$instance, $this->arr_default);

		$action = check_var('action');
		$redirect_to = check_var('redirect_to', 'char', true, admin_url());

		$user_login = check_var('log');
		$user_pass = check_var('pwd');
		$user_remember = check_var('rememberme', 'char', true, 'forever');

		//do_action('login_head');
		//do_action('login_header');

		echo $before_widget;

			if($instance['login_image'] != '')
			{
				echo "<p><img src='".$instance['login_image']."'></p>";
			}

			if($instance['login_heading'] != '')
			{
				$instance['login_heading'] = apply_filters('widget_title', $instance['login_heading'], $instance, $this->id_base);

				echo $before_title
					.$instance['login_heading']
				.$after_title;
			}

			switch($action)
			{
				case 'logout':
					if(is_user_logged_in())
					{
						check_admin_referer('log-out');

						wp_logout();

						$done_text = __("You have been successfully logged out", 'lang_login');
					}
				break;

				default:
					$obj_custom_login = new mf_custom_login();

					if(isset($_POST['btnSendLogin']))
					{
						$result = $obj_custom_login->do_login(array('user_login' => $user_login, 'user_pass' => $user_pass, 'user_remember' => $user_remember, 'redirect_to' => $redirect_to));

						if($result['success'] == true)
						{
							mf_redirect($result['redirect']);
						}

						else
						{
							$error_text = $result['error'];
						}
					}

					else
					{
						$obj_custom_login->check_if_logged_in(array('redirect' => true));
					}
				break;
			}

			echo get_notification();

			/*if($instance['login_above_form'] != '')
			{
				echo apply_filters('the_content', $instance['login_above_form']);
			}*/

			echo "<form method='post' action='".wp_login_url()."' class='mf_form'>"
				.show_textfield(array('name' => 'log', 'text' => __("Username or E-mail", 'lang_login'), 'value' => $user_login, 'placeholder' => "abc123 / name@domain.com", 'required' => true))
				.show_password_field(array('name' => 'pwd', 'text' => __("Password"), 'value' => $user_pass, 'required' => true));

				do_action('login_form');

				echo "<div class='flex_flow'>"
					.show_checkbox(array('name' => 'rememberme', 'text' => __("Remember Me", 'lang_login'), 'value' => $user_remember))
					."<div class='form_button'>"
						.show_button(array('name' => 'btnSendLogin', 'text' => __("Log In", 'lang_login')));

						/*if($interim_login)
						{
							echo input_hidden(array('name' => 'interim-login', 'value' => 1));
						}

						else
						{*/
							echo input_hidden(array('name' => 'redirect_to', 'value' => esc_attr($redirect_to)));
						/*}

						if($customize_login)
						{
							echo input_hidden(array('name' => 'customize-login', 'value' => 1));
						}*/

						//echo input_hidden(array('name' => 'testcookie', 'value' => 1));

					echo "</div>
				</div>
				<p><a href='".wp_lostpassword_url().($user_login != '' ? "?user_login=".$user_login : '')."'>".__("Have you forgotten your login credentials?", 'lang_login')."</a></p>";

				if(get_option('users_can_register'))
				{
					echo "<p>".__("Do not have an account?", 'lang_login')." <a href='".wp_registration_url()."'>".__("Register", 'lang_login')."</a></p>";
				}

			echo "</form>"
		.$after_widget;

		//do_action('login_footer');
	}

	function update($new_instance, $old_instance)
	{
		$instance = $old_instance;
		$new_instance = wp_parse_args((array)$new_instance, $this->arr_default);

		$instance['login_image'] = sanitize_text_field($new_instance['login_image']);
		$instance['login_heading'] = sanitize_text_field($new_instance['login_heading']);
		//$instance['login_above_form'] = sanitize_text_field($new_instance['login_above_form']);

		return $instance;
	}

	function form($instance)
	{
		$instance = wp_parse_args((array)$instance, $this->arr_default);

		echo "<div class='mf_form'>"
			.get_media_library(array('name' => $this->get_field_name('login_image'), 'value' => $instance['login_image'], 'type' => 'image'))
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
			'registration_image' => '',
			'registration_heading' => '',
			'registration_who_can' => '',
			//'registration_above_form' => '',
			'registration_collect_name' => 'no',
			'registration_fields' => array('username'),
		);

		parent::__construct('registration-widget', __("Registration Form", 'lang_login'), $widget_ops);
	}

	function get_roles_for_select()
	{
		$arr_data = array();
		$arr_data[''] = "-- ".__("All", 'lang_login')." --";

		$arr_data = get_roles_for_select(array('array' => $arr_data, 'add_choose_here' => false));

		return $arr_data;
	}

	function get_fields_for_select()
	{
		return array(
			'username' => __("Username", 'lang_login'),
			'full_name' => __("Full Name", 'lang_login'),
			'company' => __("Company", 'lang_login'),
		);
	}

	function widget($args, $instance)
	{
		global $error_text, $done_text;

		extract($args);
		$instance = wp_parse_args((array)$instance, $this->arr_default);

		$obj_custom_login = new mf_custom_login();
		$obj_custom_login->check_if_logged_in();

		$is_allowed = (!isset($instance['registration_who_can']) || $instance['registration_who_can'] == '' || current_user_can($instance['registration_who_can']));

		if(in_array('username', $instance['registration_fields']))
		{
			$user_login = check_var('user_login');
		}

		else
		{
			$user_login = "";
		}

		$user_email = check_var('user_email', 'email');

		if($instance['registration_collect_name'] == 'yes' || in_array('full_name', $instance['registration_fields']))
		{
			$first_name = check_var('first_name');
			$last_name = check_var('last_name');
		}

		if(in_array('company', $instance['registration_fields']))
		{
			$profile_company = check_var('profile_company');
		}

		$role = get_option('default_role');

		if(is_user_logged_in() && IS_ADMIN)
		{
			$role = check_var('role', 'char', true, $role);
		}

		echo $before_widget;

			if($instance['registration_image'] != '')
			{
				echo "<p><img src='".$instance['registration_image']."'></p>";
			}

			if($instance['registration_heading'] != '')
			{
				$instance['registration_heading'] = apply_filters('widget_title', $instance['registration_heading'], $instance, $this->id_base);

				echo $before_title
					.$instance['registration_heading']
				.$after_title;
			}

			$display_form = true;

			if($is_allowed)
			{
				if(isset($_POST['btnSendRegistration']))
				{
					if($user_login == '')
					{
						if(in_array('full_name', $instance['registration_fields']))
						{
							$user_login .= ($user_login != '' ? "_" : "").$first_name."_".$last_name;
						}

						if(in_array('company', $instance['registration_fields']))
						{
							$user_login .= ($user_login != '' ? "_" : "").$profile_company;
						}

						if($user_login == '')
						{
							$user_login = $user_email;
						}

						$user_login = sanitize_title_with_dashes(sanitize_title($user_login));
					}

					if($user_login == '' || $user_email == '')
					{
						$error_text = __("You have to enter both Username and E-mail, then I can process the registration for you", 'lang_login');
					}

					else
					{
						$user_login = strtolower($user_login);
						$user_email = strtolower($user_email);

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
							$user_id = $errors;

							$user = new WP_User($user_id);
							$user->set_role($role);

							if($instance['registration_collect_name'] == 'yes' || in_array('full_name', $instance['registration_fields']))
							{
								update_user_meta($user_id, 'first_name', $first_name);
								update_user_meta($user_id, 'last_name', $last_name);
							}

							if(in_array('company', $instance['registration_fields']))
							{
								update_user_meta($user_id, 'profile_company', $profile_company);
							}

							$done_text = __("I processed the registration for you. You should have a message in your inbox shortly, with login information.", 'lang_login');
							$display_form = false;
						}
					}
				}
			}

			else
			{
				$error_text = __("You do not have the rights to view this form", 'lang_login');
				$display_form = false;
			}

			echo get_notification();

			if($display_form == true)
			{
				/*if($instance['registration_above_form'] != '')
				{
					echo apply_filters('the_content', $instance['registration_above_form']);
				}*/

				echo "<form method='post' action='' class='mf_form'>";

					if(in_array('username', $instance['registration_fields']))
					{
						echo show_textfield(array('name' => 'user_login', 'text' => __("Username", 'lang_login'), 'value' => $user_login, 'placeholder' => "abc123", 'required' => true));
					}

					echo show_textfield(array('name' => 'user_email', 'text' => __("E-mail", 'lang_login'), 'value' => $user_email, 'placeholder' => "name@domain.com", 'required' => true));

					if($instance['registration_collect_name'] == 'yes' || in_array('full_name', $instance['registration_fields']))
					{
						echo "<div class='flex_flow'>"
							.show_textfield(array('name' => 'first_name', 'text' => __("First Name", 'lang_login'), 'value' => $first_name, 'placeholder' => "Jane", 'required' => true))
							.show_textfield(array('name' => 'last_name', 'text' => __("Last Name", 'lang_login'), 'value' => $last_name, 'placeholder' => "Doe", 'required' => true))
						."</div>";
					}

					if(in_array('company', $instance['registration_fields']))
					{
						echo "<div class='flex_flow'>"
							.show_textfield(array('name' => 'profile_company', 'text' => __("Company", 'lang_login'), 'value' => $profile_company, 'required' => true))
						."</div>";
					}

					do_action('register_form');

					if(is_user_logged_in())
					{
						if(IS_ADMIN)
						{
							$arr_data = get_roles_for_select(array('add_choose_here' => false, 'use_capability' => false, 'exclude' => array('administrator')));

							if(count($arr_data) > 1)
							{
								echo show_select(array('data' => $arr_data, 'name' => 'role', 'text' => __("Role", 'lang_login'), 'value' => $role));
							}

							else
							{
								foreach($arr_data as $key => $value)
								{
									echo input_hidden(array('name' => 'role', 'value' => $key));
								}
							}
						}
					}

					else
					{
						echo show_checkbox(array('text' => __("I consent to having this website store my submitted information, so that they can contact me if necessary", 'lang_login'), 'value' => 1, 'required' => true, 'xtra_class' => "small"));
					}

					echo "<div class='form_button'>"
						.show_button(array('name' => 'btnSendRegistration', 'text' => __("Register", 'lang_login')))
					."</div>";

					if(!is_user_logged_in())
					{
						echo "<p>".__("Do you already have an account?", 'lang_login')." <a href='".wp_login_url()."'>".__("Log In", 'lang_login')."</a></p>";
					}

				echo "</form>";
			}

		echo $after_widget;
	}

	function update($new_instance, $old_instance)
	{
		$instance = $old_instance;
		$new_instance = wp_parse_args((array)$new_instance, $this->arr_default);

		$instance['registration_image'] = sanitize_text_field($new_instance['registration_image']);
		$instance['registration_heading'] = sanitize_text_field($new_instance['registration_heading']);
		$instance['registration_who_can'] = sanitize_text_field($new_instance['registration_who_can']);
		$instance['registration_collect_name'] = sanitize_text_field($new_instance['registration_collect_name']);
		$instance['registration_fields'] = is_array($new_instance['registration_fields']) ? $new_instance['registration_fields'] : array();

		return $instance;
	}

	function form($instance)
	{
		$instance = wp_parse_args((array)$instance, $this->arr_default);

		echo "<div class='mf_form'>"
			.get_media_library(array('name' => $this->get_field_name('registration_image'), 'value' => $instance['registration_image'], 'type' => 'image'))
			.show_textfield(array('name' => $this->get_field_name('registration_heading'), 'text' => __("Heading", 'lang_login'), 'value' => $instance['registration_heading'], 'xtra' => " id='registration-title'"))
			.show_select(array('data' => $this->get_roles_for_select(), 'name' => $this->get_field_name('registration_who_can'), 'text' => __("Who Can Register?", 'lang_login'), 'value' => $instance['registration_who_can']));

			if($instance['registration_collect_name'] == 'yes' && (!is_array($instance['registration_fields']) || !in_array('full_name', $instance['registration_fields'])))
			{
				echo show_select(array('data' => get_yes_no_for_select(), 'name' => $this->get_field_name('registration_collect_name'), 'text' => __("Collect full name from user", 'lang_login'), 'value' => $instance['registration_collect_name']));
			}

			echo show_select(array('data' => $this->get_fields_for_select(), 'name' => $this->get_field_name('registration_fields')."[]", 'text' => __("Fields to Display", 'lang_login'), 'value' => $instance['registration_fields']))
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
			'lost_password_image' => '',
			'lost_password_heading' => '',
			//'lost_password_above_form' => '',
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
					.__("Username", 'lang_login').": ".$user_login
				."</p>
				<p>".__("If this was a mistake, just ignore this email and nothing will happen.", 'lang_login')."</p>
				<p>"
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
		global $error_text, $done_text;

		extract($args);
		$instance = wp_parse_args((array)$instance, $this->arr_default);

		$action = check_var('action');

		echo $before_widget;

			//do_action('lost_password');

			if($instance['lost_password_image'] != '')
			{
				echo "<p><img src='".$instance['lost_password_image']."'></p>";
			}

			if($instance['lost_password_heading'] != '')
			{
				$instance['lost_password_heading'] = apply_filters('widget_title', $instance['lost_password_heading'], $instance, $this->id_base);

				echo $before_title
					.$instance['lost_password_heading']
				.$after_title;
			}

			$display_form = true;

			switch($action)
			{
				case 'rp':
					$user_login = check_var('login');
					$user_key = check_var('key');
					$user_pass = check_var('user_pass');

					$user = '';

					if(isset($_POST['btnSendResetPassword']))
					{
						$user = check_password_reset_key($user_key, $user_login);

						if(isset($_POST['user_pass']) && !hash_equals($user_key, $_POST['key']))
						{
							$user = false;
						}

						if(!$user || is_wp_error($user))
						{
							if($user && $user->get_error_code() === 'expired_key')
							{
								$error_text = sprintf(__("The key that you used has expired. Please, %srequest a new key here%s", 'lang_login'), "<a href='".wp_lostpassword_url()."'>", "</a>");
							}

							else
							{
								$error_text = sprintf(__("The key that you used was invalid. Please, %srequest a new key here%s", 'lang_login'), "<a href='".wp_lostpassword_url()."'>", "</a>");
							}
						}

						$errors = new WP_Error();

						do_action('validate_password_reset', $errors, $user);

						if($error_text != '')
						{
							// Do nothing
						}

						else if(!$errors->get_error_code() && $user_pass != '')
						{
							reset_password($user, $user_pass);

							$done_text = __("Your password has been reset", 'lang_login')." <a href='".wp_login_url()."'>".__("Log In", 'lang_login')."</a>";

							$display_form = false;
						}
					}

					echo get_notification();

					if($display_form == true)
					{
						echo "<form method='post' action='".wp_lostpassword_url()."?action=rp' class='mf_form'>"
							.show_password_field(array('name' => 'user_pass', 'text' => __("New Password", 'lang_login'), 'value' => $user_pass, 'description' => wp_get_password_hint()));

							do_action('resetpass_form', $user);

							echo "<div class='form_button'>"
								.show_button(array('name' => 'btnSendResetPassword', 'text' => __("Reset Password", 'lang_login')))
								.input_hidden(array('name' => 'login', 'value' => $user_login, 'xtra' => " id='user_login'"))
								.input_hidden(array('name' => 'key', 'value' => $user_key))
							."</div>
						</form>";
					}
				break;

				default:
					$user_login = check_var('user_login');

					if(isset($_POST['btnSendLostPassword']))
					{
						if($user_login == '')
						{
							$error_text = __("You have to enter the e-mail address, then I can process the request for you", 'lang_login');
						}

						else
						{
							$user_login = strtolower($user_login);

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

					else
					{
						$obj_custom_login = new mf_custom_login();
						$obj_custom_login->check_if_logged_in();
					}

					echo get_notification();

					if($display_form == true)
					{
						/*if($instance['lost_password_above_form'] != '')
						{
							echo apply_filters('the_content', $instance['lost_password_above_form']);
						}*/

						echo "<form method='post' action='' class='mf_form'>" //".esc_url(network_site_url('wp-login.php?action=lostpassword', 'login_post'))."
							.show_textfield(array('name' => 'user_login', 'text' => __("Username or E-mail", 'lang_login'), 'value' => $user_login, 'placeholder' => "abc123 / name@domain.com", 'required' => true))
							."<div class='form_button'>"
								.show_button(array('name' => 'btnSendLostPassword', 'text' => __("Get New Password", 'lang_login')))
							."</div>
							<p>".__("Do you already have an account?", 'lang_login')." <a href='".wp_login_url()."'>".__("Log In", 'lang_login')."</a></p>
						</form>";
					}

					//do_action('lostpassword_form');
				break;
			}

		echo $after_widget;
	}

	function update($new_instance, $old_instance)
	{
		$instance = $old_instance;
		$new_instance = wp_parse_args((array)$new_instance, $this->arr_default);

		$instance['lost_password_image'] = sanitize_text_field($new_instance['lost_password_image']);
		$instance['lost_password_heading'] = sanitize_text_field($new_instance['lost_password_heading']);

		return $instance;
	}

	function form($instance)
	{
		$instance = wp_parse_args((array)$instance, $this->arr_default);

		echo "<div class='mf_form'>"
			.get_media_library(array('name' => $this->get_field_name('lost_password_image'), 'value' => $instance['lost_password_image'], 'type' => 'image'))
			.show_textfield(array('name' => $this->get_field_name('lost_password_heading'), 'text' => __("Heading", 'lang_login'), 'value' => $instance['lost_password_heading'], 'xtra' => " id='registration-title'"))
		."</div>";
	}
}

class widget_logged_in_info extends WP_Widget
{
	function __construct()
	{
		$widget_ops = array(
			'classname' => 'logged_in_info',
			'description' => __("Display Information About the Logged in User", 'lang_login')
		);

		$this->arr_default = array(
			//'logged_in_info_heading' => '',
			'logged_in_info_display' => array(),
		);

		parent::__construct('logged-in-info-widget', __("Logged in Information", 'lang_login'), $widget_ops);
	}

	function get_user_info_for_select()
	{
		$arr_data = array(
			'name' => __("Name", 'lang_login'),
			'role' => " - ".__("Role", 'lang_login'),
			'profile' => __("Profile", 'lang_login'),
			'logout' => __("Log Out", 'lang_login'),
			'image' => __("Image", 'lang_login'),
		);

		return $arr_data;
	}

	function widget($args, $instance)
	{
		extract($args);
		$instance = wp_parse_args((array)$instance, $this->arr_default);

		if(is_user_logged_in())
		{
			echo $before_widget;

				/*if($instance['logged_in_info_heading'] != '')
				{
					$instance['logged_in_info_heading'] = apply_filters('widget_title', $instance['logged_in_info_heading'], $instance, $this->id_base);

					echo $before_title
						.$instance['logged_in_info_heading']
					.$after_title;
				}*/

				echo "<div class='logged_in_info section'>";

					if(count($instance['logged_in_info_display']) == 0 || in_array('name', $instance['logged_in_info_display']) || in_array('profile', $instance['logged_in_info_display']) || in_array('logout', $instance['logged_in_info_display']))
					{
						echo "<ul>";

							if(count($instance['logged_in_info_display']) == 0 || in_array('name', $instance['logged_in_info_display']))
							{
								$user_data = get_userdata(get_current_user_id());
								$display_name = $user_data->display_name; //apply_filters('filter_admin_display_name', )

								echo "<li>"
									.$display_name; //<i class='fa fa-user'></i> 

									if(in_array('role', $instance['logged_in_info_display']))
									{
										$arr_roles = get_roles_for_select(array('add_choose_here' => false, 'use_capability' => false));
										$user_role = get_current_user_role(get_current_user_id());

										echo " (".$arr_roles[$user_role].")";
									}

								echo "</li>";
							}

							if(count($instance['logged_in_info_display']) == 0 || in_array('profile', $instance['logged_in_info_display']))
							{
								echo "<li><a href='".get_edit_profile_url()."'>".__("Your Profile", 'lang_login')."</a></li>";
							}

							if(count($instance['logged_in_info_display']) == 0 || in_array('logout', $instance['logged_in_info_display']))
							{
								echo "<li><a href='".wp_logout_url()."'>".__("Log Out", 'lang_login')."</a></li>";
							}

						echo "</ul>";
					}

					if(count($instance['logged_in_info_display']) == 0 || in_array('image', $instance['logged_in_info_display']))
					{
						$user_data = get_userdata(get_current_user_id());
						$display_name = $user_data->display_name;

						echo "<div>
							<div class='logged_in_avatar'>"
								.get_avatar(get_current_user_id(), 60, '', sprintf(__("Profile Image for %s", 'lang_login'), $display_name))
							."</div>
						</div>";
					}

				echo "</div>"
			.$after_widget;
		}
	}

	function update($new_instance, $old_instance)
	{
		$instance = $old_instance;
		$new_instance = wp_parse_args((array)$new_instance, $this->arr_default);

		//$instance['logged_in_info_heading'] = sanitize_text_field($new_instance['logged_in_info_heading']);
		$instance['logged_in_info_display'] = is_array($new_instance['logged_in_info_display']) ? $new_instance['logged_in_info_display'] : array();

		return $instance;
	}

	function form($instance)
	{
		$instance = wp_parse_args((array)$instance, $this->arr_default);

		echo "<div class='mf_form'>"
			//.show_textfield(array('name' => $this->get_field_name('logged_in_info_heading'), 'text' => __("Heading", 'lang_login'), 'value' => $instance['logged_in_info_heading'], 'xtra' => " id='registration-title'"))
			.show_select(array('data' => $this->get_user_info_for_select(), 'name' => $this->get_field_name('logged_in_info_display')."[]", 'value' => $instance['logged_in_info_display']))
		."</div>";
	}
}