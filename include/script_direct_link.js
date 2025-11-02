jQuery(function($)
{
	function check_credentials()
	{
		var has_username = ($("#user_login").val() != ''),
			has_password = ($("#user_pass").val() != ''),
			has_error = ($("#login_error:visible").length > 0);

		if(has_username && (!has_password || has_error))
		{
			$("#direct_login_link").removeClass('hide');
		}

		else
		{
			$("#direct_login_link").addClass('hide');
		}
	}

	check_credentials();

	$("#user_login, #user_pass").on('keyup', function()
	{
		check_credentials();
	});

	$("#direct_login_link").on('click', function()
	{
		var dom_user = $('#user_login').val();

		if(dom_user != '')
		{
			$.ajax(
			{
				url: script_custom_login_direct_link.ajax_url,
				type: 'post',
				dataType: 'json',
				data: {
					action: 'api_custom_login_direct_link_email',
					username: dom_user
				},
				success: function(data)
				{
					$("#login > .message, #login_error, #direct_login_link").remove();

					$("#loginform").before(data.html);
				}
			});
		}

		return false;
	});
});