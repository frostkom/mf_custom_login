jQuery(function($)
{
	var dom_obj = $("#loginform .submit input[name='redirect_to'], .widget.login_form .form_button input[name='redirect_to']");

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