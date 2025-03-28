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
				dom_obj.html(data.html);
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

	var dom_obj_registration = $(".widget.registration_form");

	if(dom_obj_registration.length > 0)
	{
		dom_obj_registration.find("p > input").each(function()
		{
			$(this).addClass('mf_form_field').parent("p").addClass('form_textfield').children("br").remove();
		});
	}
});