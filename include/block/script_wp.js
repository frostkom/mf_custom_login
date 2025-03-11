(function()
{
	var el = wp.element.createElement,
		registerBlockType = wp.blocks.registerBlockType,
		SelectControl = wp.components.SelectControl,
		TextControl = wp.components.TextControl,
		MediaUpload = wp.blockEditor.MediaUpload,
	    Button = wp.components.Button,
		MediaUploadCheck = wp.blockEditor.MediaUploadCheck,
		InspectorControls = wp.blockEditor.InspectorControls;

	registerBlockType('mf/customlogin',
	{
		title: script_custom_login_block_wp.block_title,
		description: script_custom_login_block_wp.block_description,
		icon: 'lock',
		category: 'widgets',
		'attributes':
		{
			'align':
			{
				'type': 'string',
				'default': ''
			}
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
			},
			"__experimentalBorder":
			{
				"radius": true
			}
		},
		edit: function(props)
		{
			return el(
				'div',
				{className: 'wp_mf_block_container'},
				[
					el(
						'strong',
						{className: props.className},
						script_custom_login_block_wp.block_title
					)
				]
			);
		},

		save: function()
		{
			return null;
		}
	});

	registerBlockType('mf/customregistration',
	{
		title: script_custom_login_block_wp.block_title2,
		description: script_custom_login_block_wp.block_description2,
		icon: 'lock',
		category: 'widgets',
		'attributes':
		{
			'align':
			{
				'type': 'string',
				'default': ''
			},
			'registration_who_can':
			{
                'type': 'string',
                'default': ''
            },
			'registration_collect_name':
			{
                'type': 'string',
                'default': ''
            },
			'registration_fields':
			{
                'type': 'array',
                'default': ''
            }
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
			},
			"__experimentalBorder":
			{
				"radius": true
			}
		},
		edit: function(props)
		{
			return el(
				'div',
				{className: 'wp_mf_block_container'},
				[
					el(
						InspectorControls,
						'div',
						el(
							SelectControl,
							{
								label: script_custom_login_block_wp.registration_who_can_label,
								value: props.attributes.registration_who_can,
								options: convert_php_array_to_block_js(script_custom_login_block_wp.registration_who_can),
								onChange: function(value)
								{
									props.setAttributes({registration_who_can: value});
								}
							}
						),
						el(
							SelectControl,
							{
								label: script_custom_login_block_wp.registration_collect_name_label,
								value: props.attributes.registration_collect_name,
								options: convert_php_array_to_block_js(script_custom_login_block_wp.yes_no_for_select),
								onChange: function(value)
								{
									props.setAttributes({registration_collect_name: value});
								}
							}
						),
						el(
							SelectControl,
							{
								label: script_custom_login_block_wp.registration_fields_label,
								value: props.attributes.registration_fields,
								options: convert_php_array_to_block_js(script_custom_login_block_wp.registration_fields),
								multiple: true,
								onChange: function(value)
								{
									props.setAttributes({registration_fields: value});
								}
							}
						)
					),
					el(
						'strong',
						{className: props.className},
						script_custom_login_block_wp.block_title2
					)
				]
			);
		},

		save: function()
		{
			return null;
		}
	});

	registerBlockType('mf/customlost',
	{
		title: script_custom_login_block_wp.block_title3,
		description: script_custom_login_block_wp.block_description3,
		icon: 'email',
		category: 'widgets',
		'attributes':
		{
			'align':
			{
				'type': 'string',
				'default': ''
			}
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
			},
			"__experimentalBorder":
			{
				"radius": true
			}
		},
		edit: function(props)
		{
			return el(
				'div',
				{className: 'wp_mf_block_container'},
				[
					el(
						'strong',
						{className: props.className},
						script_custom_login_block_wp.block_title3
					)
				]
			);
		},

		save: function()
		{
			return null;
		}
	});

	registerBlockType('mf/customloggedin',
	{
		title: script_custom_login_block_wp.block_title4,
		description: script_custom_login_block_wp.block_description4,
		icon: 'unlock',
		category: 'widgets',
		'attributes':
		{
			'align':
			{
				'type': 'string',
				'default': ''
			},
			'logged_in_info_display':
			{
                'type': 'array',
                'default': ''
            }
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
			},
			"__experimentalBorder":
			{
				"radius": true
			}
		},
		edit: function(props)
		{
			return el(
				'div',
				{className: 'wp_mf_block_container'},
				[
					el(
						InspectorControls,
						'div',
						el(
							SelectControl,
							{
								label: script_custom_login_block_wp.logged_in_info_display_label,
								value: props.attributes.logged_in_info_display,
								options: convert_php_array_to_block_js(script_custom_login_block_wp.logged_in_info_display),
								multiple: true,
								onChange: function(value)
								{
									props.setAttributes({logged_in_info_display: value});
								}
							}
						)
					),
					el(
						'strong',
						{className: props.className},
						script_custom_login_block_wp.block_title4
					)
				]
			);
		},
		save: function()
		{
			return null;
		}
	});
})();