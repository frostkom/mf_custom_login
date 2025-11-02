jQuery(function($)
{
	var dom_obj_registration = $(".widget.lost_form");

	dom_obj_registration.find(".api_custom_login_nonce").each(function()
	{
		var dom_obj = $(this);

		$.ajax(
		{
			url: script_custom_login_lost.ajax_url,
			type: 'post',
			dataType: 'json',
			data:
			{
				action: 'api_custom_login_nonce',
				type: dom_obj.attr('rel')
			},
			success: function(data)
			{
				if(data.success)
				{
					dom_obj.html(data.html).parents(".widget.lost_form").find("button").removeAttr('disabled');
				}
			}
		});
	});
});