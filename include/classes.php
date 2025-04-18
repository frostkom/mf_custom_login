<?php

class mf_custom_login
{
	var $post_type = 'mf_login';
	var $meta_prefix;
	var $error;
	var $login_send_hash;
	var $username;
	var $auth;

	function __construct()
	{
		$this->meta_prefix = $this->post_type.'_';

		/*if(get_site_option('setting_custom_login_prevent_direct_access', 'yes') == 'yes')
		{*/
			$this->login_send_hash = md5((defined('AUTH_SALT') ? AUTH_SALT : '').'login_send_'.apply_filters('get_current_visitor_ip', $_SERVER['REMOTE_ADDR']).'_'.date("Ymd"));
		//}
	}

	/*function get_wp_login_action_for_select()
	{
		return array(
			'' => "-- ".__("Choose Here", 'lang_login')." --",
			'301' => __("Redirect to New Page", 'lang_login'),
			'404' => __("Return Error", 'lang_login'),
		);
	}*/

	function cron_base()
	{
		global $wpdb;

		$obj_cron = new mf_cron();
		$obj_cron->start(__CLASS__);

		if($obj_cron->is_running == false)
		{
			mf_uninstall_plugin(array(
				'options' => array('setting_custom_login_wp_login_action', 'setting_custom_login_limit_attempts', 'setting_custom_login_limit_minutes', 'setting_custom_login_prevent_direct_access'),
				'tables' => array('custom_login'),
			));

			if(get_option('setting_custom_login_allow_direct_link') == 'yes')
			{
				$setting_custom_login_direct_link_expire = get_option('setting_custom_login_direct_link_expire');

				if($setting_custom_login_direct_link_expire > 0)
				{
					$users = get_users(array('fields' => array('ID')));

					foreach($users as $user)
					{
						$meta_login_auth = get_user_meta($user->ID, 'meta_login_auth', true);

						if($meta_login_auth != '')
						{
							list($meta_date, $rest) = explode("_", $meta_login_auth);

							if($meta_date < date("YmdHis", strtotime("-".$setting_custom_login_direct_link_expire." minute")))
							{
								delete_user_meta($user->ID, 'meta_login_auth');
							}
						}
					}
				}
			}

			//$wpdb->query("DELETE FROM ".$wpdb->base_prefix."custom_login WHERE loginCreated < DATE_SUB(NOW(), INTERVAL 1 MONTH)");
		}

		$obj_cron->end();
	}

	function block_render_login_callback($attributes)
	{
		global $wpdb, $done_text, $error_text;

		ob_start();

		$action = check_var('action');
		$redirect_to = check_var('redirect_to', 'char', true, admin_url());

		$user_login = check_var('user_login'); // log -> user_login
		$user_pass = check_var('user_pass'); // pwd -> user_pass
		$user_remember = check_var('rememberme', 'char', true, 'forever');

		echo "<div".parse_block_attributes(array('class' => "widget login_form", 'attributes' => $attributes)).">";

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
					if(isset($_POST['btnSendLogin']))
					{
						/*$setting_custom_login_limit_attempts = get_site_option_or_default('setting_custom_login_limit_attempts', 20);
						$setting_custom_login_limit_minutes = get_site_option_or_default('setting_custom_login_limit_minutes', 15);

						$wpdb->get_results($wpdb->prepare("SELECT loginID FROM ".$wpdb->base_prefix."custom_login WHERE loginIP = %s AND loginStatus = %s AND loginCreated > DATE_SUB(NOW(), INTERVAL ".$setting_custom_login_limit_minutes." MINUTE)", apply_filters('get_current_visitor_ip', $_SERVER['REMOTE_ADDR']), 'failure'));
						$login_failed_attempts = $wpdb->num_rows;

						if($login_failed_attempts < $setting_custom_login_limit_attempts)
						{*/
							if(get_option('setting_custom_login_debug') == 'yes')
							{
								echo "<p>".__("I'm trying to log you in...", 'lang_login')."</p>";
							}

							$result = $this->do_login(array('user_login' => $user_login, 'user_pass' => $user_pass, 'user_remember' => $user_remember, 'redirect_to' => $redirect_to));

							if($result['success'] == true)
							{
								if(get_option('setting_custom_login_debug') == 'yes')
								{
									echo "<p>".__("I'm redirecting you...", 'lang_login')."</p>";
								}

								mf_redirect($result['redirect']);
							}

							else
							{
								$error_text = $result['error'];

								//$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->base_prefix."custom_login SET loginIP = %s, loginStatus = %s, loginUsername = %s, loginCreated = NOW()", apply_filters('get_current_visitor_ip', $_SERVER['REMOTE_ADDR']), 'failure', $user_login));
							}
						/*}

						else
						{
							$error_text = sprintf(__("You have exceeded the limit of %d logins in the last %d minutes. Please try again later.", 'lang_login'), $setting_custom_login_limit_attempts, $setting_custom_login_limit_minutes);
						}*/
					}
				break;
			}

			echo "<form method='post' action='".wp_login_url()."' id='loginform' class='mf_form'>"
				.get_notification(array('add_container' => true))
				.show_textfield(array('name' => 'user_login', 'text' => __("Username or E-mail", 'lang_login'), 'value' => $user_login, 'placeholder' => "abc123 / ".get_placeholder_email(), 'required' => true)) // log -> user_login
				.show_password_field(array('name' => 'user_pass', 'text' => __("Password"), 'value' => $user_pass, 'required' => true)); // pwd -> user_pass

				do_action('login_form');

				echo "<div class='login_actions'>"
					.show_checkbox(array('name' => 'rememberme', 'text' => __("Remember Me", 'lang_login'), 'value' => $user_remember))
					."<div".get_form_button_classes().">"
						.show_button(array('name' => 'btnSendLogin', 'text' => __("Log In", 'lang_login')))
						.input_hidden(array('name' => 'redirect_to', 'value' => esc_attr($redirect_to)))
					."</div>
				</div>
			</form>";

			if(is_user_logged_in())
			{
				echo "<p>".__("Are you already logged in?", 'lang_login')." <a href='".admin_url()."'>".__("Go to admin", 'lang_login')."</a></p>";
			}

			else
			{
				echo "<p>".__("Have you forgotten your login credentials?", 'lang_login')." <a href='".wp_lostpassword_url().($user_login != '' ? "?user_login=".$user_login : '')."'>".__("Click here", 'lang_login')."</a></p>";
			}

			if(get_option('users_can_register'))
			{
				echo "<p>".__("Do not have an account?", 'lang_login')." <a href='".wp_registration_url()."'>".__("Register", 'lang_login')."</a></p>";
			}

		echo "</div>";

		$out = ob_get_contents();
		ob_end_clean();

		return $out;
	}

	function block_render_registration_callback($attributes)
	{
		global $error_text;

		if(!isset($attributes['registration_who_can'])){		$attributes['registration_who_can'] = '';}
		if(!isset($attributes['registration_collect_name'])){	$attributes['registration_collect_name'] = 'no';}
		if(!isset($attributes['registration_fields'])){			$attributes['registration_fields'] = array();}

		ob_start();

		$is_allowed = ((get_option('users_can_register') == 1 || is_user_logged_in()) && ($attributes['registration_who_can'] == '' || $attributes['registration_who_can'] == 0 || current_user_can($attributes['registration_who_can'])));

		$user_login = "";

		if(in_array('username', $attributes['registration_fields']))
		{
			$user_login = check_var('user_login');
		}

		$user_email = check_var('user_email', 'email');

		if($attributes['registration_collect_name'] == 'yes' || in_array('full_name', $attributes['registration_fields']))
		{
			$first_name = check_var('first_name');
			$last_name = check_var('last_name');
		}

		if(in_array('company', $attributes['registration_fields']))
		{
			$profile_company = check_var('profile_company');
		}

		$role = get_option('default_role');

		if(is_user_logged_in() && IS_ADMINISTRATOR)
		{
			$role = check_var('role', 'char', true, $role);
		}

		echo "<div".parse_block_attributes(array('class' => "widget login_form registration_form", 'attributes' => $attributes)).">";

			$display_form = true;

			if($is_allowed)
			{
				if(isset($_POST['btnSendRegistration']))
				{
					if($user_login == '')
					{
						if(in_array('full_name', $attributes['registration_fields']))
						{
							$user_login .= ($user_login != '' ? "_" : "").$first_name."_".$last_name;
						}

						if(in_array('company', $attributes['registration_fields']))
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

							if($attributes['registration_collect_name'] == 'yes' || in_array('full_name', $attributes['registration_fields']))
							{
								update_user_meta($user_id, 'first_name', $first_name);
								update_user_meta($user_id, 'last_name', $last_name);
							}

							if(in_array('company', $attributes['registration_fields']))
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

			echo get_notification(array('add_container' => true));

			if($display_form == true)
			{
				echo "<form method='post' action='' class='mf_form'>";

					if(in_array('username', $attributes['registration_fields']))
					{
						echo show_textfield(array('name' => 'user_login', 'text' => __("Username", 'lang_login'), 'value' => $user_login, 'placeholder' => "abc123", 'required' => true));
					}

					echo show_textfield(array('name' => 'user_email', 'text' => __("E-mail", 'lang_login'), 'value' => $user_email, 'placeholder' => get_placeholder_email(), 'required' => true));

					if($attributes['registration_collect_name'] == 'yes' || in_array('full_name', $attributes['registration_fields']))
					{
						echo "<div class='flex_flow'>"
							.show_textfield(array('name' => 'first_name', 'text' => __("First Name", 'lang_login'), 'value' => $first_name, 'placeholder' => "Jane", 'required' => true))
							.show_textfield(array('name' => 'last_name', 'text' => __("Last Name", 'lang_login'), 'value' => $last_name, 'placeholder' => "Doe", 'required' => true))
						."</div>";
					}

					if(in_array('company', $attributes['registration_fields']))
					{
						echo "<div class='flex_flow'>"
							.show_textfield(array('name' => 'profile_company', 'text' => __("Company", 'lang_login'), 'value' => $profile_company, 'required' => true))
						."</div>";
					}

					do_action('register_form');

					if(is_user_logged_in())
					{
						if(IS_ADMINISTRATOR)
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

					echo "<div".get_form_button_classes().">"
						.show_button(array('name' => 'btnSendRegistration', 'text' => __("Register", 'lang_login')))
					."</div>";

					if(is_user_logged_in())
					{
						echo "<p>".__("Are you already logged in?", 'lang_login')." <a href='".admin_url()."'>".__("Go to admin", 'lang_login')."</a></p>";
					}

					else
					{
						echo "<p>".__("Do you already have an account?", 'lang_login')." <a href='".wp_login_url()."'>".__("Log In", 'lang_login')."</a></p>";
					}

				echo "</form>";
			}

		echo "</div>";

		$out = ob_get_contents();
		ob_end_clean();

		return $out;
	}

	function block_render_lost_callback($attributes)
	{
		global $done_text, $error_text;

		ob_start();

		$action = check_var('action');

		echo "<div".parse_block_attributes(array('class' => "widget login_form lost_form", 'attributes' => $attributes)).">";

			//do_action('lost_password');

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

					echo get_notification(array('add_container' => true));

					if($display_form == true)
					{
						echo "<form method='post' action='".wp_lostpassword_url()."?action=rp' class='mf_form'>"
							.show_password_field(array('name' => 'user_pass', 'text' => __("New Password", 'lang_login'), 'value' => $user_pass, 'xtra' => " autocomplete='new-password'", 'description' => wp_get_password_hint()));

							do_action('resetpass_form', $user);

							echo "<div".get_form_button_classes().">"
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

					echo get_notification(array('add_container' => true));

					if($display_form == true)
					{
						echo "<form method='post' action='' class='mf_form'>"
							.show_textfield(array('name' => 'user_login', 'text' => __("Username or E-mail", 'lang_login'), 'value' => $user_login, 'placeholder' => "abc123 / ".get_placeholder_email(), 'required' => true));

							do_action('lostpassword_form');

							echo "<div".get_form_button_classes().">"
								.show_button(array('name' => 'btnSendLostPassword', 'text' => __("Get New Password", 'lang_login')))
							."</div>
						</form>";

						if(is_user_logged_in())
						{
							echo "<p>".__("Are you already logged in?", 'lang_login')." <a href='".admin_url()."'>".__("Go to admin", 'lang_login')."</a></p>";
						}

						else
						{
							echo "<p>".__("Do you already have an account?", 'lang_login')." <a href='".wp_login_url()."'>".__("Log In", 'lang_login')."</a></p>";
						}
					}
				break;
			}

		echo "</div>";

		$out = ob_get_contents();
		ob_end_clean();

		return $out;
	}

	function block_render_loggedin_callback($attributes)
	{
		if(!isset($attributes['logged_in_info_display'])){	$attributes['logged_in_info_display'] = "";}

		ob_start();

		if(is_user_logged_in())
		{
			echo "<div".parse_block_attributes(array('class' => "widget login_form logged_in_info", 'attributes' => $attributes)).">
				<div class='section'>";

					if(count($attributes['logged_in_info_display']) == 0 || in_array('name', $attributes['logged_in_info_display']) || in_array('profile', $attributes['logged_in_info_display']) || in_array('logout', $attributes['logged_in_info_display']))
					{
						echo "<ul>";

							if(count($attributes['logged_in_info_display']) == 0 || in_array('name', $attributes['logged_in_info_display']))
							{
								echo "<li>"
									.get_user_info();

									if(in_array('role', $attributes['logged_in_info_display']))
									{
										$arr_roles = get_roles_for_select(array('add_choose_here' => false, 'use_capability' => false));
										$user_role = get_current_user_role(get_current_user_id());

										echo " (".$arr_roles[$user_role].")";
									}

								echo "</li>";
							}

							if(count($attributes['logged_in_info_display']) == 0 || in_array('profile', $attributes['logged_in_info_display']))
							{
								echo "<li><a href='".get_edit_profile_url()."'>".__("Your Profile", 'lang_login')."</a></li>";
							}

							if(count($attributes['logged_in_info_display']) == 0 || in_array('logout', $attributes['logged_in_info_display']))
							{
								echo "<li><a href='".wp_logout_url()."'>".__("Log Out", 'lang_login')."</a></li>";
							}

						echo "</ul>";
					}

					if(count($attributes['logged_in_info_display']) == 0 || in_array('image', $attributes['logged_in_info_display']))
					{
						echo "<div>
							<div class='logged_in_avatar'>"
								.get_avatar(get_current_user_id(), 60, '', sprintf(__("Profile Image for %s", 'lang_login'), get_user_info()))
							."</div>
						</div>";
					}

				echo "</div>"
			.$after_widget;
		}

		$out = ob_get_contents();
		ob_end_clean();

		return $out;
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

	function init()
	{
		// Blocks
		#######################
		$plugin_include_url = plugin_dir_url(__FILE__);
		$plugin_version = get_plugin_version(__FILE__);

		wp_register_script('script_custom_login_block_wp', $plugin_include_url."block/script_wp.js", array('wp-blocks', 'wp-element', 'wp-components', 'wp-editor', 'wp-block-editor'), $plugin_version, true);

		wp_localize_script('script_custom_login_block_wp', 'script_custom_login_block_wp', array(
			'block_title' => __("Custom Login", 'lang_login'),
			'block_description' => __("Display a Custom Login", 'lang_login'),
			'block_title2' => __("Custom Registration", 'lang_login'),
			'block_description2' => __("Display a Custom Registration", 'lang_login'),
			'registration_who_can_label' => __("Who Can Register?", 'lang_login'),
			'registration_who_can' => $this->get_roles_for_select(),
			'registration_collect_name_label' => __("Collect full name from user", 'lang_login'),
			'yes_no_for_select' => get_yes_no_for_select(),
			'registration_fields_label' => __("Fields to Display", 'lang_login'),
			'registration_fields' => $this->get_fields_for_select(),
			'block_title3' => __("Custom Lost Password", 'lang_login'),
			'block_description3' => __("Display a Custom Lost Password", 'lang_login'),
			'block_title4' => __("Logged in Information", 'lang_login'),
			'block_description4' => __("Display Information About the Logged in User", 'lang_login'),
			'logged_in_info_display_label' => __("List", 'lang_login'),
			'logged_in_info_display' => $this->get_user_info_for_select(),
		));

		register_block_type('mf/customlogin', array(
			'editor_script' => 'script_custom_login_block_wp',
			'editor_style' => 'style_base_block_wp',
			'render_callback' => array($this, 'block_render_login_callback'),
			//'style' => 'style_base_block_wp',
		));

		register_block_type('mf/customregistration', array(
			'editor_script' => 'script_custom_login_block_wp',
			'editor_style' => 'style_base_block_wp',
			'render_callback' => array($this, 'block_render_registration_callback'),
			//'style' => 'style_base_block_wp',
		));

		register_block_type('mf/customlost', array(
			'editor_script' => 'script_custom_login_block_wp',
			'editor_style' => 'style_base_block_wp',
			'render_callback' => array($this, 'block_render_lost_callback'),
			//'style' => 'style_base_block_wp',
		));

		register_block_type('mf/customloggedin', array(
			'editor_script' => 'script_custom_login_block_wp',
			'editor_style' => 'style_base_block_wp',
			'render_callback' => array($this, 'block_render_loggedin_callback'),
			//'style' => 'style_base_block_wp',
		));
		#######################
	}

	function do_login($data = array())
	{
		$out = array(
			'success' => false,
		);

		if(!isset($data['user_login'])){		$data['user_login'] = '';}
		if(!isset($data['user_pass'])){			$data['user_pass'] = '';}
		if(!isset($data['user_remember'])){		$data['user_remember'] = '';}
		if(!isset($data['redirect_to'])){		$data['redirect_to'] = '';}

		$data['user_login'] = strtolower($data['user_login']);

		$secure_cookie = '';

		if(get_option('setting_custom_login_debug') == 'yes')
		{
			echo "<p>About to sign you in...</p>";
		}

		$user = wp_signon(array('user_login' => $data['user_login'], 'user_password' => $data['user_pass'], 'remember' => $data['user_remember']), $secure_cookie);

		if(get_option('setting_custom_login_debug') == 'yes')
		{
			echo "<p>Signed you in...</p>";
		}

		if(!is_wp_error($user))
		{
			if(get_option('setting_custom_login_debug') == 'yes')
			{
				echo "<p>No errors...</p>";
			}

			$requested_redirect_to = check_var('redirect_to');

			$data['redirect_to'] = apply_filters('login_redirect', $data['redirect_to'], $requested_redirect_to, $user);

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

			if(get_option('setting_custom_login_debug') == 'yes')
			{
				echo "<p>Redirect to ".$data['redirect_to']."...</p>";
			}

			$out['success'] = true;
			$out['redirect'] = $data['redirect_to'];
		}

		else
		{
			if(get_option('setting_custom_login_debug') == 'yes')
			{
				echo "<p>Errors...</p>";
			}

			foreach($user->errors as $error)
			{
				$out['error'] = $error[0];
				break;
			}
		}

		return $out;
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

	function site_transient_update_plugins($arr_plugins)
	{
		unset($arr_plugins->response['wp-smushit/wp-smush.php']);

		return $arr_plugins;
	}

	function settings_custom_login()
	{
		$options_area_orig = $options_area = __FUNCTION__;

		// Generic
		############################
		add_settings_section($options_area, "", array($this, $options_area."_callback"), BASE_OPTIONS_PAGE);

		$has_login_widget = (apply_filters('get_block_search', 0, 'mf/customlogin') > 0 || (int)apply_filters('get_widget_search', 'login-widget') > 0);
		$users_can_register = get_option('users_can_register');
		$has_registration_widget = ($users_can_register ? (apply_filters('get_block_search', 0, 'mf/customregistration') > 0 || (int)apply_filters('get_widget_search', 'registration-widget') > 0) : false);
		$has_lost_password_post_widget = (apply_filters('get_block_search', 0, 'mf/customlost') > 0 || (int)apply_filters('get_widget_search', 'lost-password-widget') > 0);

		$arr_settings = array();

		if(get_option('blog_public') == 0 || get_option('setting_no_public_pages') == 'yes' || get_option('setting_theme_core_login') == 'yes')
		{
			$arr_settings['setting_no_public_pages'] = __("Always redirect visitors to the login page", 'lang_login');

			if(get_option('setting_no_public_pages') != 'yes')
			{
				$arr_settings['setting_theme_core_login'] = __("Require login for public site", 'lang_login');
			}
		}

		if($has_login_widget == false && $has_registration_widget == false && $has_lost_password_post_widget == false)
		{
			if(is_plugin_active("mf_theme_core/index.php"))
			{
				$arr_settings['setting_custom_login_display_theme_logo'] = __("Display Theme Logo", 'lang_login');
			}

			else
			{
				delete_option('setting_custom_login_display_theme_logo');
			}

			if(get_option('setting_custom_login_display_theme_logo') != 'yes')
			{
				$arr_settings['setting_custom_login_custom_logo'] = __("Custom Logo", 'lang_login');
			}

			else
			{
				delete_option('setting_custom_login_custom_logo');
			}
		}

		if($has_login_widget)
		{
			//$arr_settings['setting_custom_login_wp_login_action'] = __("Action on Old Login Page", 'lang_login');

			delete_option('setting_custom_login_page');
		}

		else
		{
			$arr_settings['setting_custom_login_page'] = __("Login", 'lang_login');

			//delete_option('setting_custom_login_wp_login_action');
		}

		if(is_plugin_active("mf_auth/index.php") == false || get_option('setting_auth_active') == 'no')
		{
			$arr_settings['setting_custom_login_allow_direct_link'] = __("Allow Direct Link to Login", 'lang_login');

			if(get_option('setting_custom_login_allow_direct_link') == 'yes')
			{
				$arr_settings['setting_custom_login_direct_link_expire'] = __("Direct Link Expires After", 'lang_login');
				$arr_settings['setting_custom_login_direct_link_expire_after_login'] = __("Direct Link Expires After Login", 'lang_login');
			}

			else
			{
				delete_option('setting_custom_login_direct_link_expire');
				delete_option('setting_custom_login_direct_link_expire_after_login');
			}
		}

		else
		{
			delete_option('setting_custom_login_allow_direct_link');
			delete_option('setting_custom_login_direct_link_expire');
			delete_option('setting_custom_login_direct_link_expire_after_login');
		}

		if(substr(get_home_url(), 0, 5) == 'https')
		{
			$arr_settings['setting_custom_login_allow_api'] = __("Allow API Login", 'lang_login');

			if(isset($_SERVER['PHP_AUTH_USER']))
			{
				$arr_settings['setting_custom_login_allow_server_auth'] = __("Allow Server Authentication", 'lang_login');
			}

			else
			{
				delete_option('setting_custom_login_allow_server_auth');
			}
		}

		else
		{
			delete_option('setting_custom_login_allow_api');
			delete_option('setting_custom_login_allow_server_auth');
		}

		//$arr_settings['setting_custom_login_prevent_direct_access'] = __("Prevent Direct Access to Login, Registration etc.", 'lang_login');

		/*if(get_site_option('setting_custom_login_prevent_direct_access', 'yes') == 'yes')
		{*/
			$arr_settings['setting_custom_login_debug'] = " - ".__("Debug", 'lang_login');
		/*}

		else
		{
			delete_option('setting_custom_login_debug');
		}*/

		//$arr_settings['setting_custom_login_limit_attempts'] = __("Limit Attempts", 'lang_login');
		//$arr_settings['setting_custom_login_limit_minutes'] = __("Limit Minutes", 'lang_login');

		$arr_settings['setting_custom_login_redirect_after_login_page'] = __("Redirect After Login", 'lang_login');

		if(get_option('setting_custom_login_redirect_after_login_page') > 0)
		{
			$arr_settings['setting_custom_login_redirect_after_login'] = __("Who to Redirect", 'lang_login');
		}

		else
		{
			delete_option('setting_custom_login_redirect_after_login');
		}

		show_settings_fields(array('area' => $options_area, 'object' => $this, 'settings' => $arr_settings));
		############################

		// Registration
		############################
		$options_area = $options_area_orig."_registration";

		add_settings_section($options_area, "", array($this, $options_area."_callback"), BASE_OPTIONS_PAGE);

		$arr_settings = array();

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

				else
				{
					delete_option('setting_custom_login_register');
				}
			}

			else
			{
				delete_option('setting_custom_login_register');
			}
		}

		else if($users_can_register && $has_registration_widget == false)
		{
			$arr_settings['setting_custom_login_register'] = __("Register", 'lang_login');
		}

		else
		{
			delete_option('setting_custom_login_register');
		}

		if($users_can_register)
		{
			$arr_settings['setting_custom_login_info'] = __("Information", 'lang_login');

			$arr_settings['setting_custom_login_email_admin_registration'] = __("Registration Email Content to Admin", 'lang_login');
			$arr_settings['setting_custom_login_email_registration'] = __("Registration Email Content", 'lang_login');
		}

		else
		{
			delete_option('setting_custom_login_info');
			delete_option('setting_custom_login_email_admin_registration');
			delete_option('setting_custom_login_email_registration');
		}

		show_settings_fields(array('area' => $options_area, 'object' => $this, 'settings' => $arr_settings));
		############################

		// Password
		############################
		$options_area = $options_area_orig."_password";

		add_settings_section($options_area, "", array($this, $options_area."_callback"), BASE_OPTIONS_PAGE);

		$arr_settings = array();
		$arr_settings['setting_custom_login_info'] = __("Information", 'lang_login');

		if($has_lost_password_post_widget == false)
		{
			$arr_settings['setting_custom_login_lostpassword'] = __("Lost Password", 'lang_login');
			$arr_settings['setting_custom_login_recoverpassword'] = __("Recover Password", 'lang_login');
		}

		else
		{
			delete_option('setting_custom_login_lostpassword');
			delete_option('setting_custom_login_recoverpassword');
		}

		$arr_settings['setting_custom_login_email_lost_password_subject'] = __("Lost Password Email Subject", 'lang_login');
		$arr_settings['setting_custom_login_email_lost_password'] = __("Lost Password Email Content", 'lang_login');

		show_settings_fields(array('area' => $options_area, 'object' => $this, 'settings' => $arr_settings));
		############################
	}

	function settings_custom_login_callback()
	{
		$setting_key = get_setting_key(__FUNCTION__);

		echo settings_header($setting_key, __("Login", 'lang_login'));
	}

		function setting_no_public_pages_callback()
		{
			$setting_key = get_setting_key(__FUNCTION__);
			$option = get_option_or_default($setting_key, 'no');

			echo show_select(array('data' => get_yes_no_for_select(), 'name' => $setting_key, 'value' => $option));
		}

		function setting_theme_core_login_callback()
		{
			$setting_key = get_setting_key(__FUNCTION__);
			$option = get_option_or_default($setting_key, 'no');

			echo show_select(array('data' => get_yes_no_for_select(), 'name' => $setting_key, 'value' => $option));
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

			echo get_media_library(array('type' => 'image', 'name' => $setting_key, 'value' => $option));
		}

		/*function setting_custom_login_wp_login_action_callback()
		{
			$setting_key = get_setting_key(__FUNCTION__);
			$option = get_option($setting_key);

			echo show_select(array('data' => $this->get_wp_login_action_for_select(), 'name' => $setting_key, 'value' => $option));
		}*/

		function setting_custom_login_page_callback()
		{
			$setting_key = get_setting_key(__FUNCTION__);
			$option = get_option($setting_key);

			$arr_data = array();
			get_post_children(array('add_choose_here' => true), $arr_data);

			echo show_select(array('data' => $arr_data, 'name' => $setting_key, 'value' => $option, 'suffix' => get_option_page_suffix(array('value' => $option)), 'description' => __("The content from this page is displayed next to the login screen", 'lang_login')));
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
			$tags = array("[first_name]", "[user_login]", "[username]", "[user_email]", "[blog_title]", "[site_name]", "[site_url]", "[confirm_link]", "[login_link]");

			if(get_option('setting_custom_login_allow_direct_link') == 'yes')
			{
				$tags[] = "[direct_link]";

				if(apply_filters('get_block_search', 0, 'mf/customregistration') > 0 || (int)apply_filters('get_widget_search', 'registration-widget') > 0)
				{
					$tags[] = "[direct_registration_link]";
				}
			}

			echo sprintf(__("To take advantage of dynamic data, you can use the following placeholders: %s", 'lang_login'), sprintf('<code>%s</code>', implode('</code>, <code>', $tags)));
		}

		/*function setting_custom_login_prevent_direct_access_callback()
		{
			$setting_key = get_setting_key(__FUNCTION__);
			settings_save_site_wide($setting_key);
			$option = get_site_option($setting_key, get_option($setting_key, 'yes'));

			echo show_select(array('data' => get_yes_no_for_select(), 'name' => $setting_key, 'value' => $option));
		}*/

		/*function setting_custom_login_limit_attempts_callback()
		{
			$setting_key = get_setting_key(__FUNCTION__);
			settings_save_site_wide($setting_key);
			$option = get_site_option($setting_key, get_option($setting_key, 20));

			echo show_textfield(array('type' => 'number', 'name' => $setting_key, 'value' => $option, 'xtra' => "min='5' max='100'"));
		}

		function setting_custom_login_limit_minutes_callback()
		{
			$setting_key = get_setting_key(__FUNCTION__);
			settings_save_site_wide($setting_key);
			$option = get_site_option($setting_key, get_option($setting_key, 15));

			echo show_textfield(array('type' => 'number', 'name' => $setting_key, 'value' => $option, 'xtra' => "min='2' max='60'"));
		}*/

		function setting_custom_login_redirect_after_login_page_callback()
		{
			$setting_key = get_setting_key(__FUNCTION__);
			$option = get_option($setting_key);

			$arr_data = array();
			get_post_children(array('add_choose_here' => true), $arr_data);

			echo show_select(array('data' => $arr_data, 'name' => $setting_key, 'value' => $option, 'suffix' => get_option_page_suffix(array('value' => $option))));
		}

		function setting_custom_login_debug_callback()
		{
			$setting_key = get_setting_key(__FUNCTION__);
			$option = get_option($setting_key, 'no');

			list($option, $description) = setting_time_limit(array('key' => $setting_key, 'value' => $option, 'time_limit' => 24, 'return' => 'array'));

			echo show_select(array('data' => get_yes_no_for_select(), 'name' => $setting_key, 'value' => $option, 'description' => $description));
		}

		function setting_custom_login_redirect_after_login_callback()
		{
			$setting_key = get_setting_key(__FUNCTION__);
			$option = get_option($setting_key);

			echo show_select(array('data' => get_roles_for_select(array('add_choose_here' => false, 'use_capability' => false)), 'name' => $setting_key."[]", 'value' => $option, 'description' => __("Users with these roles will be redirected after login. If none are chosen, all are affected", 'lang_login')));
		}

	function settings_custom_login_registration_callback()
	{
		$setting_key = get_setting_key(__FUNCTION__);

		echo settings_header($setting_key, __("Login", 'lang_login')." - ".__("Registration", 'lang_login'));
	}

		function users_can_register_callback()
		{
			$setting_key = get_setting_key(__FUNCTION__);
			$option = get_option($setting_key, '0');

			$xtra = $description = "";

			if(is_multisite())
			{
				$xtra = " disabled";
				$description = sprintf(__("You can change this in the %sNetwork Settings%s", 'lang_login'), "<a href='".network_admin_url("settings.php")."'>", "</a>");
			}

			echo show_select(array('data' => get_yes_no_for_select(array('return_integer' => true)), 'name' => $setting_key, 'value' => $option, 'xtra' => $xtra, 'description' => $description));

			if($option == 1)
			{
				if(!(apply_filters('get_block_search', 0, 'mf/customregistration') > 0 || (int)apply_filters('get_widget_search', 'registration-widget') > 0))
				{
					echo "<p class='display_warning'>"
						."<i class='fa fa-exclamation-triangle yellow'></i> "
						.sprintf(__("You have not created a %spage for registration%s. Please do so and add the %sregistration widget%s to the page", 'lang_login'), "<a href='".admin_url("post-new.php?post_type=page")."'>", "</a>", "<a href='".admin_url("widgets.php")."'>", "</a>")
					."</p>";
				}
			}
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

	function settings_custom_login_password_callback()
	{
		$setting_key = get_setting_key(__FUNCTION__);

		echo settings_header($setting_key, __("Login", 'lang_login')." - ".__("Password", 'lang_login'));
	}

		function setting_custom_login_email_lost_password_subject_callback()
		{
			$setting_key = get_setting_key(__FUNCTION__);
			$option = get_option($setting_key);

			echo show_textfield(array('name' => $setting_key, 'value' => $option, 'placeholder' => sprintf(__("%s Password Reset", 'lang_login'), "[site_name]")));
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

	function admin_init()
	{
		global $pagenow;

		if(in_array($pagenow, array('user-edit.php', 'profile.php')) && IS_ADMINISTRATOR && get_option('setting_custom_login_allow_direct_link') == 'yes')
		{
			$plugin_include_url = plugin_dir_url(__FILE__);

			mf_enqueue_script('script_login_profile', $plugin_include_url."script_profile.js", array('ajax_url' => admin_url('admin-ajax.php')));
		}
	}

	function filter_sites_table_settings($arr_settings)
	{
		$arr_settings['settings_theme_core'] = array(
			'setting_no_public_pages' => array(
				'type' => 'bool',
				'global' => false,
				'icon' => "fas fa-lock",
				'name' => __("Always redirect visitors to the login page", 'lang_login'),
			),
			'setting_theme_core_login' => array(
				'type' => 'bool',
				'global' => false,
				'icon' => "fas fa-user-lock",
				'name' => __("Require login for public site", 'lang_login'),
			),
		);

		$arr_settings['settings_custom_login'] = array(
			'setting_custom_login_allow_direct_link' => array(
				'type' => 'bool',
				'global' => false,
				'icon' => "fas fa-link",
				'name' => __("Allow Direct Link to Login", 'lang_login'),
			),
			'setting_custom_login_allow_api' => array(
				'type' => 'bool',
				'global' => false,
				'icon' => "fas fa-project-diagram",
				'name' => __("Allow API Login", 'lang_login'),
			),
		);

		return $arr_settings;
	}

	function display_post_states($post_states, $post)
	{
		global $wpdb;

		$arr_page_types = array(
			'mf/customlogin' => __("Custom Login", 'lang_login'),
			'mf/customregistration' => __("Custom Registration", 'lang_login'),
			'mf/customlost' => __("Custom Lost Password", 'lang_login'),
			'mf/customloggedin' => __("Logged in Information", 'lang_login'),
		);

		foreach($arr_page_types as $handle => $label)
		{
			if(has_block($handle, $post))
			{
				list($prefix, $type) = explode("/", $handle);

				$post_states[$this->meta_prefix.$type] = $label;
			}
		}

		return $post_states;
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
		if(IS_ADMINISTRATOR && get_option('setting_custom_login_allow_direct_link') == 'yes')
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
								.show_button(array('type' => 'button', 'name' => 'btnDirectLoginRevoke', 'text' => __("Revoke", 'lang_login'), 'class' => "button-secondary", 'xtra' => "data-user-id='".$user->ID."'"))
							."</div>";
						}

						else
						{
							echo show_button(array('type' => 'button', 'name' => 'btnDirectLoginCreate', 'text' => __("Generate Now", 'lang_login'), 'class' => "button-secondary", 'xtra' => "data-user-id='".$user->ID."'"));
						}

						echo "<div class='api_custom_login_direct_login'></div>
					</td>
				</tr>
			</table>";
		}
	}

	function combined_head()
	{
		$plugin_include_url = plugin_dir_url(__FILE__);

		mf_enqueue_style('style_custom_login', $plugin_include_url."style.php");
		mf_enqueue_script('script_custom_login', $plugin_include_url."script.js", array('ajax_url' => admin_url('admin-ajax.php')));

		if(get_option('setting_custom_login_allow_direct_link') == 'yes')
		{
			mf_enqueue_script('script_custom_login_direct_link', $plugin_include_url."script_direct_link.js", array('ajax_url' => admin_url('admin-ajax.php')));

			switch(check_var('type'))
			{
				case 'link':
					$this->username = check_var('username');
					$this->auth = check_var('auth');

					if($this->username != '' && $this->auth != '' && $this->check_auth())
					{
						if($this->direct_link_login($this->username))
						{
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
				break;
			}
		}
	}

	function signup_header()
	{
		wp_redirect(wp_registration_url());
		die;
	}

	function login_headertext()
	{
		return get_bloginfo('name');
	}

	function get_login_redirect($redirect_to, $user_data)
	{
		$setting_custom_login_redirect_after_login_page = get_option('setting_custom_login_redirect_after_login_page');

		if($setting_custom_login_redirect_after_login_page > 0)
		{
			$setting_custom_login_redirect_after_login = get_option_or_default('setting_custom_login_redirect_after_login', array());
			$setting_fea_redirect_after_login = get_option_or_default('setting_fea_redirect_after_login', array());

			if(count($setting_custom_login_redirect_after_login) == 0 || isset($user_data->roles) && is_array($user_data->roles) && count(array_intersect($setting_fea_redirect_after_login, $user_data->roles)) > 0)
			{
				$post_url = get_permalink($setting_custom_login_redirect_after_login_page);

				if($post_url != '')
				{
					$redirect_to = $post_url;
				}
			}
		}

		return $redirect_to;
	}

	function get_log_message_base($data)
	{
		return ($data['user_login'] != '' ? $data['user_login'] : $data['user_email'])
			.", ".$_SERVER['REQUEST_URI']
			.", ".apply_filters('get_current_visitor_ip', $_SERVER['REMOTE_ADDR'])." + ".date("Ymd")." -> ";
	}

	function wp_authenticate_user($user_data)
	{
		/*if(get_site_option('setting_custom_login_prevent_direct_access', 'yes') == 'yes')
		{*/
			if(!isset($_POST['_hash_login_send']) || $_POST['_hash_login_send'] != $this->login_send_hash)
			{
				if(get_option('setting_custom_login_debug') == 'yes')
				{
					do_log("Login FAILURE ("
						.$this->get_log_message_base(array('user_login' => $user_data->data->user_login, 'user_email' => $user_data->data->user_email))
						.$this->login_send_hash." != ".(isset($_POST['_hash_login_send']) ? $_POST['_hash_login_send'] : "not set")
					.")");
				}

				$user_data = new WP_Error('invalid_check', __("I could not let you login since the request lacks information. If the problem persists, contact us and let us know what happened", 'lang_login')
					." (".(isset($_POST['_hash_login_send']) ? $_POST['_hash_login_send'] : "")." != ".$this->login_send_hash.")"
				);
			}

			else if(get_option('setting_custom_login_debug') == 'yes')
			{
				do_log("Login Allowed ("
					.$this->get_log_message_base(array('user_login' => $user_data->data->user_login, 'user_email' => $user_data->data->user_email))
					.$_POST['_hash_login_send']
				.")");
			}
		//}

		return $user_data;
	}

	function registration_errors($errors, $user_login, $user_email)
	{
		/*if(get_site_option('setting_custom_login_prevent_direct_access', 'yes') == 'yes')
		{*/
			if(!isset($_POST['_hash_registration_send']) || $_POST['_hash_registration_send'] != $this->login_send_hash)
			{
				if(get_option('setting_custom_login_debug') == 'yes')
				{
					do_log("Registration FAILURE ("
						.$this->get_log_message_base(array('user_login' => $user_login, 'user_email' => $user_email))
						.$_POST['_hash_registration_send']
					.")");
				}

				$errors->add('invalid_check', __("I could not let you register since the request lacks information. If the problem persists, contact us and let us know what happened", 'lang_login'));
			}

			else if(get_option('setting_custom_login_debug') == 'yes')
			{
				do_log("Registration Allowed ("
					.$this->get_log_message_base(array('user_login' => $user_login, 'user_email' => $user_email))
					.$_POST['_hash_registration_send']
				.")");
			}
		//}

		return $errors;
	}

	function lostpassword_post($errors, $user_data)
	{
		$has_errors = false;

		/*if(get_site_option('setting_custom_login_prevent_direct_access', 'yes') == 'yes')
		{*/
			if(!isset($_POST['_hash_lost_password_send']) || $_POST['_hash_lost_password_send'] != $this->login_send_hash)
			{
				if(get_option('setting_custom_login_debug') == 'yes')
				{
					do_log("Lost Password FAILURE ("
						.$this->get_log_message_base(array('user_login' => $user_data->data->user_login, 'user_email' => $user_data->data->user_email))
						.$_POST['_hash_lost_password_send']
					.")");
				}

				$errors->add('invalid_check', __("I could not let you request a news password since the request lacks information. If the problem persists, contact us and let us know what happened", 'lang_login'));

				$has_errors = true;
			}

			else if(get_option('setting_custom_login_debug') == 'yes')
			{
				do_log("Lost Password Allowed ("
					.$this->get_log_message_base(array('user_login' => $user_data->data->user_login, 'user_email' => $user_data->data->user_email))
					.$_POST['_hash_lost_password_send']
				.")");
			}
		//}

		return array($has_errors, $errors);
	}

	function get_404_page()
	{
		global $wp_query;

		$wp_query->set_404();
		status_header(404);
		get_template_part(404);
		exit;
	}

	function login_init()
	{
		/*if(strpos($_SERVER['REQUEST_URI'], "wp-login.php"))
		{
			$setting_custom_login_wp_login_action = get_option('setting_custom_login_wp_login_action');

			if($setting_custom_login_wp_login_action != '')
			{
				switch($setting_custom_login_wp_login_action)
				{
					case 301:
						wp_redirect(wp_login_url()."?redirect_to=".$_SERVER['REQUEST_URI']);
					break;

					case 404:
						$this->get_404_page();
					break;

					default:
						do_log(__FUNCTION__.": Unknown action ".$setting_custom_login_wp_login_action);
					break;
				}
			}
		}*/

		$action = check_var('action');

		switch($action)
		{
			case 'lostpassword':
			case 'logout':
				// Do nothing
			break;

			default:
				if(is_user_logged_in())
				{
					$redirect_to = (current_user_can('read') ? admin_url() : home_url());
					$user_data = get_userdata(get_current_user_id());

					wp_redirect($this->get_login_redirect($redirect_to, $user_data));
				}

				/*else if(strpos($_SERVER['REQUEST_URI'], "wp-login.php") && apply_filters('get_block_search', 0, 'mf/customlogin') > 0)
				{
					wp_redirect(wp_login_url()."?redirect_to=".$_SERVER['REQUEST_URI']);
				}*/
			break;
		}

		$this->combined_head();
	}

	function login_redirect($redirect_to, $request, $user_data)
	{
		// Just in case we have sent this variable along with the URL
		$redirect_to = check_var('redirect_to', 'char', true, $redirect_to);

		if($redirect_to == admin_url())
		{
			$redirect_to = $this->get_login_redirect($redirect_to, $user_data);
		}

		return $redirect_to;
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
			$result = $wpdb->get_results($wpdb->prepare("SELECT post_title, post_content FROM ".$wpdb->posts." WHERE ID = '%d' AND post_type = %s AND post_status = %s", $post_id, 'page', 'publish'));

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
		$out = false;

		$user = get_user_by('login', $this->username);

		if(isset($user->ID))
		{
			$meta_login_auth = get_user_meta($user->ID, 'meta_login_auth', true);

			if($this->auth == $meta_login_auth)
			{
				$setting_custom_login_direct_link_expire = get_option('setting_custom_login_direct_link_expire');

				if($setting_custom_login_direct_link_expire > 0)
				{
					list($meta_date, $rest) = explode("_", $meta_login_auth);

					if($meta_date > date("YmdHis", strtotime("-".$setting_custom_login_direct_link_expire." minute")))
					{
						$out = true;
					}
				}

				else
				{
					$out = true;
				}
			}
		}

		return $out;
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
			wp_set_current_user($user->ID);

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
		if(!isset($data['key'])){					$data['key'] = md5((defined('AUTH_SALT') ? AUTH_SALT : '').$data['user_data']->user_login.$data['user_data']->user_email);}
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

		$out = __("To login directly without setting a password, visit the following link. The link is personal and can only be used once. If this link falls into the wrong hands and you have not used it they will be able to login to your account without a password.", 'lang_login').":"
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

		// wp_new_user_notification_email
		$exclude[] = "[blog_title]";		$include[] = $blog_title;
		$exclude[] = "[site_url]";			$include[] = $site_url;
		$exclude[] = "[confirm_link]";		$include[] = $lost_password_url.(strpos($lost_password_url, "?") ? "&" : "?").$confirm_link_action;
		$exclude[] = "[login_link]";		$include[] = $login_url;

		if(get_option('setting_custom_login_allow_direct_link') == 'yes' && preg_match("/\[direct_link]/", $string))
		{
			$direct_link = $this->direct_link_url(array('user_data' => $user_data, 'type' => 'registration'));

			$exclude[] = "[direct_link]";		$include[] = $direct_link;

			$direct_registration_link = "";

			if(isset($user_data->roles) && in_array('administrator', $user_data->roles))
			{
				$registration_post_id = apply_filters('get_block_search', 0, 'mf/customregistration');

				if(!($registration_post_id > 0))
				{
					$registration_post_id = (int)apply_filters('get_widget_search', 'registration-widget');
				}

				if($registration_post_id > 0)
				{
					$registration_post_url = get_permalink($registration_post_id);
					$registration_post_url = str_replace(get_site_url(), "", $registration_post_url);

					$direct_registration_link = $direct_link.(strpos($direct_link, "?") ? "&" : "?")."redirect_to=".$registration_post_url;
				}
			}

			$exclude[] = "[direct_registration_link]";		$include[] = $direct_registration_link;
		}

		// retrieve_password_message
		$exclude[] = "[blogname]";			$include[] = $blog_title; // Replace with blog_title
		$exclude[] = "[siteurl]";			$include[] = $site_url; // Replace with site_url
		$exclude[] = "[loginurl]";			$include[] = $lost_password_url.(strpos($lost_password_url, "?") ? "&" : "?").$confirm_link_action; // Replace with confirm_link

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

	function retrieve_password_title($title, $user_login, $user_data)
	{
		$option = get_option('setting_custom_login_email_lost_password_subject');

		if($option != '')
		{
			$title = $option;
		}

		if(is_multisite())
		{
			$site_name = get_network()->site_name;
		}

		else
		{
			// The blogname option is escaped with esc_html on the way into the database in sanitize_option we want to reverse this for the plain text arena of emails
			$site_name = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
		}

		//$title = $this->email_replace_shortcodes($title, $user, $key);
		$title = str_replace("[site_name]", $site_name, $title);

		return $title;
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

		/*if(get_site_option('setting_custom_login_prevent_direct_access', 'yes') == 'yes')
		{*/
			echo "<div class='api_custom_login_nonce' rel='login'></div>";

			if(get_option('setting_custom_login_debug') == 'yes')
			{
				do_log("Login SET ("
					.$_SERVER['REQUEST_URI'].", "
					.apply_filters('get_current_visitor_ip', $_SERVER['REMOTE_ADDR'])." + ".date("Ymd")." -> ".$this->login_send_hash
				.")");
			}
		//}
	}

	function register_form()
	{
		/*if(get_site_option('setting_custom_login_prevent_direct_access', 'yes') == 'yes')
		{*/
			echo "<div class='api_custom_login_nonce' rel='registration'></div>";
		//}
	}

	function lostpassword_form()
	{
		/*if(get_site_option('setting_custom_login_prevent_direct_access', 'yes') == 'yes')
		{*/
			echo "<div class='api_custom_login_nonce' rel='lost_password'></div>";
		//}
	}

	function wp_head()
	{
		$this->combined_head();

		if(!is_user_logged_in())
		{
			if(get_option('setting_maintenance_page') > 0)
			{
				// Do nothing here...
			}

			else if(get_option('setting_no_public_pages') == 'yes')
			{
				mf_redirect(get_site_url()."/wp-admin/");
			}

			else if(get_option('setting_theme_core_login') == 'yes' && apply_filters('is_public_page', true))
			{
				mf_redirect(wp_login_url()."?redirect_to=".$_SERVER['REQUEST_URI']);
			}
		}
	}

	function body_class($classes)
	{
		global $post;

		if(isset($post) && isset($post->ID) && $post->ID > 0)
		{
			if(apply_filters('get_block_search', 0, 'mf/customlogin') > 0 || apply_filters('get_block_search', 0, 'mf/customregistration') > 0 || apply_filters('get_block_search', 0, 'mf/customlost') > 0 || (int)apply_filters('get_widget_search', 'login-widget') == $post->ID || (int)apply_filters('get_widget_search', 'registration-widget') == $post->ID || (int)apply_filters('get_widget_search', 'lost-password-widget') == $post->ID)
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
				$post_id = (int)apply_filters('get_widget_search', $widget_key);

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
		$post_id = apply_filters('get_block_search', 0, 'mf/customlogin');

		if(!($post_id > 0))
		{
			$post_id = (int)apply_filters('get_widget_search', 'login-widget');
		}

		if($post_id > 0)
		{
			@list($url_old, $query_string) = explode("?", $url);

			$url = get_permalink($post_id);

			if($query_string != '')
			{
				$url .= (strpos($query_string, "?") ? "&" : "?").$query_string;
			}
		}

		return $url;
	}

	function register_url($url)
	{
		if(is_user_logged_in() && IS_ADMINISTRATOR)
		{
			if(is_multisite() && IS_SUPER_ADMIN)
			{
				global $wpdb;

				$url = network_admin_url("site-users.php?id=".$wpdb->blogid."#add-existing-user");
			}

			else
			{
				$url = admin_url("user-new.php");
			}
		}

		else
		{
			$post_id = apply_filters('get_block_search', 0, 'mf/customregistration');

			if(!($post_id > 0))
			{
				$post_id = (int)apply_filters('get_widget_search', 'registration-widget');
			}

			if($post_id > 0)
			{
				$url = get_permalink($post_id);
			}
		}

		return $url;
	}

	function lostpassword_url($url)
	{
		$post_id = apply_filters('get_block_search', 0, 'mf/customlost');

		if(!($post_id > 0))
		{
			$post_id = (int)apply_filters('get_widget_search', 'lost-password-widget');
		}

		if($post_id > 0)
		{
			$url = get_permalink($post_id);
		}

		return $url;
	}

	function logout_url($url)
	{
		$post_id = apply_filters('get_block_search', 0, 'mf/customlogin');

		if(!($post_id > 0))
		{
			$post_id = (int)apply_filters('get_widget_search', 'login-widget');
		}

		if($post_id > 0)
		{
			$url = wp_nonce_url(get_permalink($post_id)."?action=logout", 'log-out');
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

	function api_custom_login_direct_create()
	{
		global $done_text, $error_text;

		$json_output = array(
			'success' => false,
		);

		$user_id = check_var('user_id');

		if($user_id > 0)
		{
			if(IS_ADMINISTRATOR && get_option('setting_custom_login_allow_direct_link') == 'yes')
			{
				$user_data = get_userdata($user_id);

				$json_output['success'] = true;
				$json_output['html'] = "<a href='".$this->direct_link_url(array('user_data' => $user_data, 'type' => 'profile'))."'>".__("URL", 'lang_login')."</a>";
			}

			else
			{
				$error_text = __("You do not have the rights to perform this action", 'lang_login');

				$json_output['html'] = get_notification();
			}
		}

		else
		{
			$error_text = __("There was no User ID attached to the request", 'lang_login');

			$json_output['html'] = get_notification();
		}

		header('Content-Type: application/json');
		echo json_encode($json_output);
		die();
	}

	function api_custom_login_direct_revoke()
	{
		global $done_text, $error_text;

		$json_output = array(
			'success' => false,
		);

		$user_id = check_var('user_id');

		if($user_id > 0)
		{
			if(IS_ADMINISTRATOR && get_option('setting_custom_login_allow_direct_link') == 'yes')
			{
				delete_user_meta($user_id, 'meta_login_auth');

				$json_output['success'] = true;
				$done_text = __("The direct login link has been revoked and can not be used anymore", 'lang_login');
			}

			else
			{
				$error_text = __("You do not have the rights to perform this action", 'lang_login');
			}
		}

		else
		{
			$error_text = __("There was no User ID attached to the request", 'lang_login');
		}

		$json_output['html'] = get_notification();

		header('Content-Type: application/json');
		echo json_encode($json_output);
		die();
	}

	function api_custom_login_direct_link_email()
	{
		global $done_text, $error_text;

		$json_output = array(
			'success' => false,
		);

		$username = check_var('username');

		$user = get_user_by('login', $username);

		if(isset($user->user_email) && $user->user_email != '')
		{
			$mail_to = $user->user_email;
			$mail_subject = sprintf(__("[%s] Here comes your link to direct login", 'lang_login'), get_bloginfo('name'));
			$mail_content = $this->direct_link_text(array('user_data' => $user));

			$sent = send_email(array('to' => $mail_to, 'subject' => $mail_subject, 'content' => $mail_content));

			if($sent)
			{
				$json_output['success'] = true;
				$done_text = __("I successfully sent the message to your email. Follow the link in it, and you will be logged in before you know it", 'lang_login');
			}

			else
			{
				$error_text = __("I could not send the email", 'lang_login');
			}
		}

		else
		{
			$error_text = __("I could not find an email corresponding to the username you entered", 'lang_login');
		}

		$json_output['html'] = get_notification();

		header('Content-Type: application/json');
		echo json_encode($json_output);
		die();
	}

	function api_custom_login_nonce()
	{
		$type = check_var('type');

		$json_output = array(
			'success' => true,
			'html' => input_hidden(array('name' => '_hash_'.$type."_send", 'value' => $this->login_send_hash)),
		);

		header("Content-Type: application/json");
		echo json_encode($json_output);
		die();
	}

	/*function filter_cache_ignore($array)
	{
		$arr_widget_search = array(
			'login-widget',
			'registration-widget',
			'lost-password-widget',
		);

		foreach($arr_widget_search as $widget_key)
		{
			$post_id = (int)apply_filters('get_widget_search', $widget_key);

			if($post_id > 0)
			{
				//$array[] = str_replace($site_url, "", rtrim(get_permalink($post_id), "/"));
				$array[] = "/".mf_get_post_content($post_id, 'post_name')."/";
			}
		}

		return $array;
	}*/

	function widgets_init()
	{
		if(wp_is_block_theme() == false)
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
}

class widget_login_form extends WP_Widget
{
	var $obj_custom_login;
	var $widget_ops;
	var $arr_default = array(
		'login_image' => '',
		'login_heading' => '',
		'login_above_form' => '',
	);

	function __construct()
	{
		$this->obj_custom_login = new mf_custom_login();

		$this->widget_ops = array(
			'classname' => 'login_form',
			'description' => __("Display a Login Form", 'lang_login'),
		);

		parent::__construct('login-widget', __("Login Form", 'lang_login'), $this->widget_ops);
	}

	function widget($args, $instance)
	{
		do_log(__CLASS__."->".__FUNCTION__."(): Add a block instead", 'publish', false);

		global $wpdb, $obj_custom_login, $error_text, $done_text;

		extract($args);
		$instance = wp_parse_args((array)$instance, $this->arr_default);

		$action = check_var('action');
		$redirect_to = check_var('redirect_to', 'char', true, admin_url());

		$user_login = check_var('user_login'); // log -> user_login
		$user_pass = check_var('user_pass'); // pwd -> user_pass
		$user_remember = check_var('rememberme', 'char', true, 'forever');

		if(!isset($_GET['fl_builder']))
		{
			do_action('login_init');
		}

		//do_action('login_head');
		//do_action('login_header');

		echo apply_filters('filter_before_widget', $before_widget);

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
					if(!isset($obj_custom_login))
					{
						$obj_custom_login = new mf_custom_login();
					}

					if(isset($_POST['btnSendLogin']))
					{
						/*$setting_custom_login_limit_attempts = get_site_option_or_default('setting_custom_login_limit_attempts', 20);
						$setting_custom_login_limit_minutes = get_site_option_or_default('setting_custom_login_limit_minutes', 15);

						$wpdb->get_results($wpdb->prepare("SELECT loginID FROM ".$wpdb->base_prefix."custom_login WHERE loginIP = %s AND loginStatus = %s AND loginCreated > DATE_SUB(NOW(), INTERVAL ".$setting_custom_login_limit_minutes." MINUTE)", apply_filters('get_current_visitor_ip', $_SERVER['REMOTE_ADDR']), 'failure'));
						$login_failed_attempts = $wpdb->num_rows;

						if($login_failed_attempts < $setting_custom_login_limit_attempts)
						{*/
							$result = $obj_custom_login->do_login(array('user_login' => $user_login, 'user_pass' => $user_pass, 'user_remember' => $user_remember, 'redirect_to' => $redirect_to));

							if($result['success'] == true)
							{
								mf_redirect($result['redirect']);
							}

							else
							{
								$error_text = $result['error'];

								//$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->base_prefix."custom_login SET loginIP = %s, loginStatus = %s, loginUsername = %s, loginCreated = NOW()", apply_filters('get_current_visitor_ip', $_SERVER['REMOTE_ADDR']), 'failure', $user_login));
							}
						/*}

						else
						{
							$error_text = sprintf(__("You have exceeded the limit of %d logins in the last %d minutes. Please try again later.", 'lang_login'), $setting_custom_login_limit_attempts, $setting_custom_login_limit_minutes);
						}*/
					}

					else if(!isset($_GET['fl_builder']))
					{
						$obj_custom_login->check_if_logged_in(array('redirect' => true));
					}
				break;
			}

			echo get_notification();

			if($instance['login_above_form'] != '')
			{
				echo apply_filters('the_content', $instance['login_above_form']);
			}

			echo "<form method='post' action='".wp_login_url()."' id='loginform' class='mf_form'>"
				.show_textfield(array('name' => 'user_login', 'text' => __("Username or E-mail", 'lang_login'), 'value' => $user_login, 'placeholder' => "abc123 / ".get_placeholder_email(), 'required' => true)) // log -> user_login
				.show_password_field(array('name' => 'user_pass', 'text' => __("Password"), 'value' => $user_pass, 'required' => true)); // pwd -> user_pass

				do_action('login_form');

				echo "<div class='login_actions flex_flow'>"
					.show_checkbox(array('name' => 'rememberme', 'text' => __("Remember Me", 'lang_login'), 'value' => $user_remember))
					."<div".get_form_button_classes().">"
						.show_button(array('name' => 'btnSendLogin', 'text' => __("Log In", 'lang_login')))
						.input_hidden(array('name' => 'redirect_to', 'value' => esc_attr($redirect_to)))
					."</div>
				</div>
			</form>
			<p id='lost_password_link'><a href='".wp_lostpassword_url().($user_login != '' ? "?user_login=".$user_login : '')."'>".__("Have you forgotten your login credentials?", 'lang_login')."</a></p>";

			if(get_option('users_can_register'))
			{
				echo "<p>".__("Do not have an account?", 'lang_login')." <a href='".wp_registration_url()."'>".__("Register", 'lang_login')."</a></p>";
			}

		echo $after_widget;

		//do_action('login_footer');
	}

	function update($new_instance, $old_instance)
	{
		$instance = $old_instance;
		$new_instance = wp_parse_args((array)$new_instance, $this->arr_default);

		$instance['login_image'] = sanitize_text_field($new_instance['login_image']);
		$instance['login_heading'] = sanitize_text_field($new_instance['login_heading']);
		$instance['login_above_form'] = sanitize_text_field($new_instance['login_above_form']);

		return $instance;
	}

	function form($instance)
	{
		$instance = wp_parse_args((array)$instance, $this->arr_default);

		echo "<div class='mf_form'>"
			.get_media_library(array('type' => 'image', 'name' => $this->get_field_name('login_image'), 'label' => __("Logo", 'lang_login'), 'value' => $instance['login_image']))
			.show_textfield(array('name' => $this->get_field_name('login_heading'), 'text' => __("Heading", 'lang_login'), 'value' => $instance['login_heading'], 'xtra' => " id='".$this->widget_ops['classname']."-title'"))
			.show_textarea(array('name' => $this->get_field_name('login_above_form'), 'text' => __("Content Above Form", 'lang_login'), 'value' => $instance['login_above_form']))
		."</div>";
	}
}

class widget_registration_form extends WP_Widget
{
	var $obj_custom_login;
	var $widget_ops;
	var $arr_default = array(
		'registration_image' => '',
		'registration_heading' => '',
		'registration_who_can' => '',
		'registration_collect_name' => 'no',
		'registration_fields' => array(), //'username'
	);

	function __construct()
	{
		$this->obj_custom_login = new mf_custom_login();

		$this->widget_ops = array(
			'classname' => 'registration_form',
			'description' => __("Display a Registration Form", 'lang_login'),
		);

		parent::__construct('registration-widget', __("Registration Form", 'lang_login'), $this->widget_ops);
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
		do_log(__CLASS__."->".__FUNCTION__."(): Add a block instead", 'publish', false);

		global $obj_custom_login, $error_text, $done_text;

		extract($args);
		$instance = wp_parse_args((array)$instance, $this->arr_default);

		if(!isset($obj_custom_login))
		{
			$obj_custom_login = new mf_custom_login();
		}

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

		if(is_user_logged_in() && IS_ADMINISTRATOR)
		{
			$role = check_var('role', 'char', true, $role);
		}

		echo apply_filters('filter_before_widget', $before_widget);

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
				echo "<form method='post' action='' class='mf_form'>";

					if(in_array('username', $instance['registration_fields']))
					{
						echo show_textfield(array('name' => 'user_login', 'text' => __("Username", 'lang_login'), 'value' => $user_login, 'placeholder' => "abc123", 'required' => true));
					}

					echo show_textfield(array('name' => 'user_email', 'text' => __("E-mail", 'lang_login'), 'value' => $user_email, 'placeholder' => get_placeholder_email(), 'required' => true));

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
						if(IS_ADMINISTRATOR)
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

					echo "<div".get_form_button_classes().">"
						.show_button(array('name' => 'btnSendRegistration', 'text' => __("Register", 'lang_login')))
					."</div>";

					if(is_user_logged_in() == false)
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
		$instance['registration_fields'] = (is_array($new_instance['registration_fields']) ? $new_instance['registration_fields'] : array());

		return $instance;
	}

	function form($instance)
	{
		$instance = wp_parse_args((array)$instance, $this->arr_default);

		echo "<div class='mf_form'>"
			.get_media_library(array('type' => 'image', 'name' => $this->get_field_name('registration_image'), 'label' => __("Logo", 'lang_login'), 'value' => $instance['registration_image']))
			.show_textfield(array('name' => $this->get_field_name('registration_heading'), 'text' => __("Heading", 'lang_login'), 'value' => $instance['registration_heading'], 'xtra' => " id='".$this->widget_ops['classname']."-title'"))
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
	var $obj_custom_login;
	var $widget_ops;
	var $arr_default = array(
		'lost_password_image' => '',
		'lost_password_heading' => '',
	);

	function __construct()
	{
		$this->obj_custom_login = new mf_custom_login();

		$this->widget_ops = array(
			'classname' => 'lost_password_form',
			'description' => __("Display a Lost Password Form", 'lang_login'),
		);

		parent::__construct('lost-password-widget', __("Lost Password Form", 'lang_login'), $this->widget_ops);
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
			list($has_errors, $errors) = $this->obj_custom_login->lostpassword_post($errors, $user_data);

			if($has_errors)
			{
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
					$title = sprintf(__("%s Password Reset", 'lang_login'), "[site_name]");
					$title = apply_filters('retrieve_password_title', $title, $user_login, $user_data);

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
	}

	function widget($args, $instance)
	{
		do_log(__CLASS__."->".__FUNCTION__."(): Add a block instead", 'publish', false);

		global $obj_custom_login, $error_text, $done_text;

		extract($args);
		$instance = wp_parse_args((array)$instance, $this->arr_default);

		$action = check_var('action');

		echo apply_filters('filter_before_widget', $before_widget);

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
							.show_password_field(array('name' => 'user_pass', 'text' => __("New Password", 'lang_login'), 'value' => $user_pass, 'xtra' => " autocomplete='new-password'", 'description' => wp_get_password_hint()));

							do_action('resetpass_form', $user);

							echo "<div".get_form_button_classes().">"
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
						if(!isset($obj_custom_login))
						{
							$obj_custom_login = new mf_custom_login();
						}

						$obj_custom_login->check_if_logged_in();
					}

					echo get_notification();

					if($display_form == true)
					{
						echo "<form method='post' action='' class='mf_form'>"
							.show_textfield(array('name' => 'user_login', 'text' => __("Username or E-mail", 'lang_login'), 'value' => $user_login, 'placeholder' => "abc123 / ".get_placeholder_email(), 'required' => true));

							do_action('lostpassword_form');

							echo "<div".get_form_button_classes().">"
								.show_button(array('name' => 'btnSendLostPassword', 'text' => __("Get New Password", 'lang_login')))
							."</div>
						</form>
						<p>".__("Do you already have an account?", 'lang_login')." <a href='".wp_login_url()."'>".__("Log In", 'lang_login')."</a></p>";
					}
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
			.get_media_library(array('type' => 'image', 'name' => $this->get_field_name('lost_password_image'), 'label' => __("Logo", 'lang_login'), 'value' => $instance['lost_password_image']))
			.show_textfield(array('name' => $this->get_field_name('lost_password_heading'), 'text' => __("Heading", 'lang_login'), 'value' => $instance['lost_password_heading'], 'xtra' => " id='".$this->widget_ops['classname']."-title'"))
		."</div>";
	}
}

class widget_logged_in_info extends WP_Widget
{
	var $obj_custom_login;
	var $widget_ops;
	var $arr_default = array(
		'logged_in_info_display' => array(),
	);

	function __construct()
	{
		$this->obj_custom_login = new mf_custom_login();

		$this->widget_ops = array(
			'classname' => 'logged_in_info',
			'description' => __("Display Information About the Logged in User", 'lang_login'),
		);

		parent::__construct(str_replace("_", "-", $this->widget_ops['classname']).'-widget', __("Logged in Information", 'lang_login'), $this->widget_ops);
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
		do_log(__CLASS__."->".__FUNCTION__."(): Add a block instead", 'publish', false);

		extract($args);
		$instance = wp_parse_args((array)$instance, $this->arr_default);

		if(is_user_logged_in())
		{
			echo apply_filters('filter_before_widget', $before_widget);

				echo "<div class='logged_in_info section'>";

					if(count($instance['logged_in_info_display']) == 0 || in_array('name', $instance['logged_in_info_display']) || in_array('profile', $instance['logged_in_info_display']) || in_array('logout', $instance['logged_in_info_display']))
					{
						echo "<ul>";

							if(count($instance['logged_in_info_display']) == 0 || in_array('name', $instance['logged_in_info_display']))
							{
								echo "<li>"
									.get_user_info();

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
						echo "<div>
							<div class='logged_in_avatar'>"
								.get_avatar(get_current_user_id(), 60, '', sprintf(__("Profile Image for %s", 'lang_login'), get_user_info()))
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

		$instance['logged_in_info_display'] = is_array($new_instance['logged_in_info_display']) ? $new_instance['logged_in_info_display'] : array();

		return $instance;
	}

	function form($instance)
	{
		$instance = wp_parse_args((array)$instance, $this->arr_default);

		echo "<div class='mf_form'>"
			.show_select(array('data' => $this->get_user_info_for_select(), 'name' => $this->get_field_name('logged_in_info_display')."[]", 'value' => $instance['logged_in_info_display']))
		."</div>";
	}
}