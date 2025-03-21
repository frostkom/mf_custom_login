jQuery(function($)
{
	$("#loginform .api_custom_login_nonce").each(function()
	{
		var dom_obj = $(this);

		$.ajax(
		{
			url: script_form.ajax_url,
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
					dom_obj.html(data.response);
				}
			}
		});
	});

	var dom_obj = $("#loginform .submit input[name='redirect_to'], .widget.login_form .form_button input[name='redirect_to'], .widget.login_form .wp-block-button input[name='redirect_to']");

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