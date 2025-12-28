jQuery(function($)
{
	$("#loginform .api_custom_login_nonce, .widget.login_form .api_custom_login_nonce").each(function()
	{
		var dom_obj = $(this);

		$.ajax(
		{
			url: script_custom_login_login.ajax_url,
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
					dom_obj.html(data.html).parents("#loginform, .widget.login_form").find("button").removeAttr('disabled');
				}
			}
		});
	});

	var dom_obj = $("#loginform .submit input[name='redirect_to'], .widget.login_form input[name='redirect_to']");

	if(dom_obj.length > 0)
	{
		var hash = location.hash.replace('#', '');

		if(hash != '')
		{
			var dom_obj_value = dom_obj.val();

			dom_obj.val(dom_obj_value + "#" + hash);
		}
	}
});