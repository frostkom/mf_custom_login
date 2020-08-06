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
				obj.selector.empty();

				if(obj.button.is("a"))
				{
					obj.button.addClass('hide');
				}

				else
				{
					obj.button.addClass('is_disabled'); /*.attr('disabled', true)*/
				}

				if(data.success)
				{
					obj.selector.html(data.message);
				}

				else
				{
					obj.selector.html(data.error);
				}
			}
		});

		return false;
	}

	$(document).on('click', "button[name='btnDirectLoginCreate']", function(e)
	{
		run_ajax(
		{
			'button': $(e.currentTarget),
			'action': 'create_direct_login',
			'user_id': $(e.currentTarget).attr('data-user-id'),
			'selector': $("#direct_login_debug")
		});
	});

	$(document).on('click', "button[name='btnDirectLoginRevoke']", function(e)
	{
		run_ajax(
		{
			'button': $(e.currentTarget),
			'action': 'revoke_direct_login',
			'user_id': $(e.currentTarget).attr('data-user-id'),
			'selector': $("#direct_login_debug")
		});
	});
});