jQuery(function($)
{
	/*$("html, body").scrollTop(0);*/

	function check_credentials()
	{
		var has_username = $("#user_login").val() != '',
			has_password = $("#user_pass").val() != '',
			has_error = $("#login_error:visible").length > 0;

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
				url: script_custom_login.ajax_url,
				type: 'post',
				dataType: 'json',
				data: {
					action: "send_direct_link_email",
					username: dom_user
				},
				success: function(data)
				{
					$("#login > .message, #login_error, #direct_login_link").remove();

					if(data.success)
					{
						$("#loginform").before("<p class='message'>" + data.message + "</p>");
					}

					else
					{
						$("#loginform").before("<div id='login_error'>" + data.error + "</div>");
					}
				}
			});
		}

		return false;
	});
});