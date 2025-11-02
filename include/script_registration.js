jQuery(function($)
{
	var dom_obj_registration = $(".widget.registration_form");

	dom_obj_registration.find(".api_custom_login_nonce").each(function()
	{
		var dom_obj = $(this);

		$.ajax(
		{
			url: script_custom_login_registration.ajax_url,
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
					dom_obj.html(data.html).parents(".widget.registration_form").find("button").removeAttr('disabled');
				}
			}
		});
	});

	if(dom_obj_registration.length > 0)
	{
		dom_obj_registration.find("p > input").each(function()
		{
			$(this).addClass('mf_form_field').parent("p").addClass('form_textfield').children("br").remove();
		});
	}
});