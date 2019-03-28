jQuery(function($)
{
	function run_ajax(obj)
	{
		obj.selector.html("<i class='fa fa-spinner fa-spin fa-2x'></i>");

		$.ajax(
		{
			type: "post",
			dataType: "json",
			url: script_login_profile.ajax_url,
			data: {
				action: obj.action,
				user_id: obj.user_id
			},
			success: function(data)
			{
				obj.selector.empty();

				if(obj.button.is('a'))
				{
					obj.button.addClass('hide');
				}

				else
				{
					obj.button.attr('disabled', true);
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

	$(document).on('click', "button[name='btnDirectLogin']", function(e)
	{
		run_ajax(
		{
			'button': $(e.currentTarget),
			'action': 'get_direct_login_url',
			'user_id': $(e.currentTarget).attr('data-user-id'),
			'selector': $("#direct_login_debug")
		});
	});
});