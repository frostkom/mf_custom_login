jQuery(function($)
{
	function run_ajax(obj)
	{
		obj.selector.html("<i class='fa fa-spinner fa-spin fa-2x'></i>");

		$.ajax(
		{
			url: script_login_profile.ajax_url,
			type: 'post',
			dataType: 'json',
			data: {
				action: obj.action,
				user_id: obj.user_id
			},
			success: function(data)
			{
				if(obj.button.is("a"))
				{
					obj.button.addClass('hide');
				}

				else
				{
					obj.button.addClass('is_disabled');
				}

				obj.selector.html(data.html);
			}
		});

		return false;
	}

	$(document).on('click', "button[name='btnDirectLoginCreate']", function(e)
	{
		run_ajax(
		{
			'button': $(e.currentTarget),
			'action': 'api_custom_login_direct_create',
			'user_id': $(e.currentTarget).attr('data-user-id'),
			'selector': $(".api_custom_login_direct_login")
		});
	});

	$(document).on('click', "button[name='btnDirectLoginRevoke']", function(e)
	{
		run_ajax(
		{
			'button': $(e.currentTarget),
			'action': 'api_custom_login_direct_revoke',
			'user_id': $(e.currentTarget).attr('data-user-id'),
			'selector': $(".api_custom_login_direct_login")
		});
	});
});