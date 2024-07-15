(function()
{
	var __ = wp.i18n.__,
		el = wp.element.createElement,
		registerBlockType = wp.blocks.registerBlockType,
		SelectControl = wp.components.SelectControl,
		TextControl = wp.components.TextControl;

	registerBlockType('mf/customlogin',
	{
		title: __("Custom Login", 'lang_custom_login'),
		description: __("Display a Custom Login", 'lang_custom_login'),
		icon: 'unlock', /* https://developer.wordpress.org/resource/dashicons/ */
		category: 'widgets', /* common, formatting, layout, widgets, embed */
		'attributes':
		{
			'align':
			{
				'type': 'string',
				'default': ''
			},
			'login_heading':
			{
                'type': 'string',
                'default': ''
            }

			/*.get_media_library(array('type' => 'image', 'name' => $this->get_field_name('login_image'), 'label' => __("Logo", 'lang_login'), 'value' => $instance['login_image']))
			.show_textfield(array('name' => $this->get_field_name('login_heading'), 'text' => __("Heading", 'lang_login'), 'value' => $instance['login_heading'], 'xtra' => " id='".$this->widget_ops['classname']."-title'"))
			.show_textarea(array('name' => $this->get_field_name('login_above_form'), 'text' => __("Content Above Form", 'lang_login'), 'value' => $instance['login_above_form']))*/
		},
		'supports':
		{
			'html': false,
			'multiple': false,
			'align': true,
			'spacing':
			{
				'margin': true,
				'padding': true
			},
			'color':
			{
				'background': true,
				'gradients': false,
				'text': true
			},
			'defaultStylePicker': true,
			'typography':
			{
				'fontSize': true,
				'lineHeight': true
			}
		},
		edit: function(props)
		{
			var arr_out = [];

			/* Text */
			/* ################### */
			arr_out.push(el(
				'div',
				{className: "wp_mf_block " + props.className},
				el(
					TextControl,
					{
						label: __("Heading", 'lang_custom_login'),
						type: 'text',
						value: props.attributes.login_heading,
						/*help: __("Description...", 'lang_custom_login'),*/
						onChange: function(value)
						{
							props.setAttributes({login_heading: value});
						}
					}
				)
			));
			/* ################### */

			/* Select */
			/* ################### */
			var arr_options = [];

			jQuery.each(script_custom_login_block_wp.login_id, function(index, value)
			{
				if(index == "")
				{
					index = 0;
				}

				arr_options.push({label: value, value: index});
			});

			arr_out.push(el(
				'div',
				{className: "wp_mf_block " + props.className},
				el(
					SelectControl,
					{
						label: __("List", 'lang_custom_login'),
						value: props.attributes.login_id,
						options: arr_options,
						onChange: function(value)
						{
							props.setAttributes({login_id: value});
						}
					}
				)
			));
			/* ################### */

			/*.get_media_library(array('type' => 'image', 'name' => $this->get_field_name('login_image'), 'label' => __("Logo", 'lang_login'), 'value' => $instance['login_image']))
			.show_textfield(array('name' => $this->get_field_name('login_heading'), 'text' => __("Heading", 'lang_login'), 'value' => $instance['login_heading'], 'xtra' => " id='".$this->widget_ops['classname']."-title'"))
			.show_textarea(array('name' => $this->get_field_name('login_above_form'), 'text' => __("Content Above Form", 'lang_login'), 'value' => $instance['login_above_form']))*/

			return arr_out;
		},

		save: function()
		{
			return null;
		}
	});

	registerBlockType('mf/customregistration',
	{
		title: __("Custom Login", 'lang_custom_login'),
		description: __("Display a Custom Login", 'lang_custom_login'),
		icon: 'unlock', /* https://developer.wordpress.org/resource/dashicons/ */
		category: 'widgets', /* common, formatting, layout, widgets, embed */
		'attributes':
		{
			'align':
			{
				'type': 'string',
				'default': ''
			},
			'login_heading':
			{
                'type': 'string',
                'default': ''
            }

			/*.get_media_library(array('type' => 'image', 'name' => $this->get_field_name('registration_image'), 'label' => __("Logo", 'lang_login'), 'value' => $instance['registration_image']))
			.show_textfield(array('name' => $this->get_field_name('registration_heading'), 'text' => __("Heading", 'lang_login'), 'value' => $instance['registration_heading'], 'xtra' => " id='".$this->widget_ops['classname']."-title'"))
			.show_select(array('data' => $this->get_roles_for_select(), 'name' => $this->get_field_name('registration_who_can'), 'text' => __("Who Can Register?", 'lang_login'), 'value' => $instance['registration_who_can']));
			echo show_select(array('data' => get_yes_no_for_select(), 'name' => $this->get_field_name('registration_collect_name'), 'text' => __("Collect full name from user", 'lang_login'), 'value' => $instance['registration_collect_name']));
			echo show_select(array('data' => $this->get_fields_for_select(), 'name' => $this->get_field_name('registration_fields')."[]", 'text' => __("Fields to Display", 'lang_login'), 'value' => $instance['registration_fields']))*/
		},
		'supports':
		{
			'html': false,
			'multiple': false,
			'align': true,
			'spacing':
			{
				'margin': true,
				'padding': true
			},
			'color':
			{
				'background': true,
				'gradients': false,
				'text': true
			},
			'defaultStylePicker': true,
			'typography':
			{
				'fontSize': true,
				'lineHeight': true
			}
		},
		edit: function(props)
		{
			var arr_out = [];

			/* Text */
			/* ################### */
			arr_out.push(el(
				'div',
				{className: "wp_mf_block " + props.className},
				el(
					TextControl,
					{
						label: __("Heading", 'lang_custom_login'),
						type: 'text',
						value: props.attributes.login_heading,
						/*help: __("Description...", 'lang_custom_login'),*/
						onChange: function(value)
						{
							props.setAttributes({login_heading: value});
						}
					}
				)
			));
			/* ################### */

			/* Select */
			/* ################### */
			var arr_options = [];

			jQuery.each(script_custom_login_block_wp.login_id, function(index, value)
			{
				if(index == "")
				{
					index = 0;
				}

				arr_options.push({label: value, value: index});
			});

			arr_out.push(el(
				'div',
				{className: "wp_mf_block " + props.className},
				el(
					SelectControl,
					{
						label: __("List", 'lang_custom_login'),
						value: props.attributes.login_id,
						options: arr_options,
						onChange: function(value)
						{
							props.setAttributes({login_id: value});
						}
					}
				)
			));
			/* ################### */

			/*.get_media_library(array('type' => 'image', 'name' => $this->get_field_name('registration_image'), 'label' => __("Logo", 'lang_login'), 'value' => $instance['registration_image']))
			.show_textfield(array('name' => $this->get_field_name('registration_heading'), 'text' => __("Heading", 'lang_login'), 'value' => $instance['registration_heading'], 'xtra' => " id='".$this->widget_ops['classname']."-title'"))
			.show_select(array('data' => $this->get_roles_for_select(), 'name' => $this->get_field_name('registration_who_can'), 'text' => __("Who Can Register?", 'lang_login'), 'value' => $instance['registration_who_can']));
			echo show_select(array('data' => get_yes_no_for_select(), 'name' => $this->get_field_name('registration_collect_name'), 'text' => __("Collect full name from user", 'lang_login'), 'value' => $instance['registration_collect_name']));
			echo show_select(array('data' => $this->get_fields_for_select(), 'name' => $this->get_field_name('registration_fields')."[]", 'text' => __("Fields to Display", 'lang_login'), 'value' => $instance['registration_fields']))*/

			return arr_out;
		},

		save: function()
		{
			return null;
		}
	});

	registerBlockType('mf/customlost',
	{
		title: __("Custom Login", 'lang_custom_login'),
		description: __("Display a Custom Login", 'lang_custom_login'),
		icon: 'unlock', /* https://developer.wordpress.org/resource/dashicons/ */
		category: 'widgets', /* common, formatting, layout, widgets, embed */
		'attributes':
		{
			'align':
			{
				'type': 'string',
				'default': ''
			},
			'login_heading':
			{
                'type': 'string',
                'default': ''
            }

			/*.get_media_library(array('type' => 'image', 'name' => $this->get_field_name('lost_password_image'), 'label' => __("Logo", 'lang_login'), 'value' => $instance['lost_password_image']))
			.show_textfield(array('name' => $this->get_field_name('lost_password_heading'), 'text' => __("Heading", 'lang_login'), 'value' => $instance['lost_password_heading'], 'xtra' => " id='".$this->widget_ops['classname']."-title'"))*/
		},
		'supports':
		{
			'html': false,
			'multiple': false,
			'align': true,
			'spacing':
			{
				'margin': true,
				'padding': true
			},
			'color':
			{
				'background': true,
				'gradients': false,
				'text': true
			},
			'defaultStylePicker': true,
			'typography':
			{
				'fontSize': true,
				'lineHeight': true
			}
		},
		edit: function(props)
		{
			var arr_out = [];

			/* Text */
			/* ################### */
			arr_out.push(el(
				'div',
				{className: "wp_mf_block " + props.className},
				el(
					TextControl,
					{
						label: __("Heading", 'lang_custom_login'),
						type: 'text',
						value: props.attributes.login_heading,
						/*help: __("Description...", 'lang_custom_login'),*/
						onChange: function(value)
						{
							props.setAttributes({login_heading: value});
						}
					}
				)
			));
			/* ################### */

			/* Select */
			/* ################### */
			var arr_options = [];

			jQuery.each(script_custom_login_block_wp.login_id, function(index, value)
			{
				if(index == "")
				{
					index = 0;
				}

				arr_options.push({label: value, value: index});
			});

			arr_out.push(el(
				'div',
				{className: "wp_mf_block " + props.className},
				el(
					SelectControl,
					{
						label: __("List", 'lang_custom_login'),
						value: props.attributes.login_id,
						options: arr_options,
						onChange: function(value)
						{
							props.setAttributes({login_id: value});
						}
					}
				)
			));
			/* ################### */

			/*.get_media_library(array('type' => 'image', 'name' => $this->get_field_name('lost_password_image'), 'label' => __("Logo", 'lang_login'), 'value' => $instance['lost_password_image']))
			.show_textfield(array('name' => $this->get_field_name('lost_password_heading'), 'text' => __("Heading", 'lang_login'), 'value' => $instance['lost_password_heading'], 'xtra' => " id='".$this->widget_ops['classname']."-title'"))*/

			return arr_out;
		},

		save: function()
		{
			return null;
		}
	});

	registerBlockType('mf/customloggedin',
	{
		title: __("Custom Login", 'lang_custom_login'),
		description: __("Display a Custom Login", 'lang_custom_login'),
		icon: 'unlock', /* https://developer.wordpress.org/resource/dashicons/ */
		category: 'widgets', /* common, formatting, layout, widgets, embed */
		'attributes':
		{
			'align':
			{
				'type': 'string',
				'default': ''
			},
			'login_heading':
			{
                'type': 'string',
                'default': ''
            }

			/*.show_select(array('data' => $this->get_user_info_for_select(), 'name' => $this->get_field_name('logged_in_info_display')."[]", 'value' => $instance['logged_in_info_display']))*/
		},
		'supports':
		{
			'html': false,
			'multiple': false,
			'align': true,
			'spacing':
			{
				'margin': true,
				'padding': true
			},
			'color':
			{
				'background': true,
				'gradients': false,
				'text': true
			},
			'defaultStylePicker': true,
			'typography':
			{
				'fontSize': true,
				'lineHeight': true
			}
		},
		edit: function(props)
		{
			var arr_out = [];

			/* Text */
			/* ################### */
			arr_out.push(el(
				'div',
				{className: "wp_mf_block " + props.className},
				el(
					TextControl,
					{
						label: __("Heading", 'lang_custom_login'),
						type: 'text',
						value: props.attributes.login_heading,
						/*help: __("Description...", 'lang_custom_login'),*/
						onChange: function(value)
						{
							props.setAttributes({login_heading: value});
						}
					}
				)
			));
			/* ################### */

			/* Select */
			/* ################### */
			var arr_options = [];

			jQuery.each(script_custom_login_block_wp.login_id, function(index, value)
			{
				if(index == "")
				{
					index = 0;
				}

				arr_options.push({label: value, value: index});
			});

			arr_out.push(el(
				'div',
				{className: "wp_mf_block " + props.className},
				el(
					SelectControl,
					{
						label: __("List", 'lang_custom_login'),
						value: props.attributes.login_id,
						options: arr_options,
						onChange: function(value)
						{
							props.setAttributes({login_id: value});
						}
					}
				)
			));
			/* ################### */

			/*.show_select(array('data' => $this->get_user_info_for_select(), 'name' => $this->get_field_name('logged_in_info_display')."[]", 'value' => $instance['logged_in_info_display']))*/

			return arr_out;
		},

		save: function()
		{
			return null;
		}
	});
})();