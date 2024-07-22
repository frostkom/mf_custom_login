(function()
{
	var __ = wp.i18n.__,
		el = wp.element.createElement,
		registerBlockType = wp.blocks.registerBlockType,
		SelectControl = wp.components.SelectControl,
		TextControl = wp.components.TextControl,
		MediaUpload = wp.blockEditor.MediaUpload,
	    Button = wp.components.Button,
		MediaUploadCheck = wp.blockEditor.MediaUploadCheck;

	registerBlockType('mf/customlogin',
	{
		title: __("Custom Login", 'lang_login'),
		description: __("Display a Custom Login", 'lang_login'),
		icon: 'lock', /* https://developer.wordpress.org/resource/dashicons/ */
		category: 'widgets', /* common, formatting, layout, widgets, embed */
		'attributes':
		{
			'align':
			{
				'type': 'string',
				'default': ''
			},
			'login_image':
			{
                'type': 'string',
                'default': ''
            },
			'login_image_id':
			{
                'type': 'string',
                'default': ''
            },
			'login_heading':
			{
                'type': 'string',
                'default': ''
            },
			'login_above_form':
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
			}
		},
		edit: function(props)
		{
			var arr_out = [];

			/* Media */
			/* ################### */
			arr_out.push(el(
				'div',
				{className: "wp_mf_block " + props.className},
				el(
                    MediaUploadCheck,
                    {},
                    el(
                        MediaUpload,
                        {
                            onSelect: function(value)
							{
								props.setAttributes({login_image: value.url, login_image_id: value.id});
							},
                            allowedTypes: ['image'],
                            value: props.attributes.login_image_id,
                            render: function(obj)
							{
                                return el(
                                    Button,
                                    {
                                        onClick: obj.open
                                    },
                                    __("Logo", 'lang_login')
                                );
                            }
                        }
                    )
                ),
                props.attributes.login_image && el(
                    'img',
                    {
                        src: props.attributes.login_image,
                        alt: ''
                    }
                )
			));
			/* ################### */
			
			/* Text */
			/* ################### */
			arr_out.push(el(
				'div',
				{className: "wp_mf_block " + props.className},
				el(
					TextControl,
					{
						label: __("Heading", 'lang_login'),
						type: 'text',
						value: props.attributes.login_heading,
						onChange: function(value)
						{
							props.setAttributes({login_heading: value});
						}
					}
				)
			));
			/* ################### */

			/* Text */
			/* ################### */
			arr_out.push(el(
				'div',
				{className: "wp_mf_block " + props.className},
				el(
					TextControl,
					{
						label: __("Content Above Form", 'lang_login'),
						type: 'text',
						value: props.attributes.login_above_form,
						onChange: function(value)
						{
							props.setAttributes({login_above_form: value});
						}
					}
				)
			));
			/* ################### */

			return arr_out;
		},

		save: function()
		{
			return null;
		}
	});

	registerBlockType('mf/customregistration',
	{
		title: __("Custom Login", 'lang_login'),
		description: __("Display a Custom Login", 'lang_login'),
		icon: 'users', /* https://developer.wordpress.org/resource/dashicons/ */
		category: 'widgets', /* common, formatting, layout, widgets, embed */
		'attributes':
		{
			'align':
			{
				'type': 'string',
				'default': ''
			},
			'registration_image':
			{
                'type': 'string',
                'default': ''
            },
			'registration_image_id':
			{
                'type': 'string',
                'default': ''
            },
			'registration_heading':
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
			}
		},
		edit: function(props)
		{
			var arr_out = [];

			/* Media */
			/* ################### */
			arr_out.push(el(
				'div',
				{className: "wp_mf_block " + props.className},
				el(
                    MediaUploadCheck,
                    {},
                    el(
                        MediaUpload,
                        {
                            onSelect: function(value)
							{
								props.setAttributes({login_image: value.url, login_image_id: value.id});
							},
                            allowedTypes: ['image'],
                            value: props.attributes.login_image_id,
                            render: function(obj)
							{
                                return el(
                                    Button,
                                    {
                                        onClick: obj.open
                                    },
                                    __("Logo", 'lang_login')
                                );
                            }
                        }
                    )
                ),
                props.attributes.login_image && el(
                    'img',
                    {
                        src: props.attributes.login_image,
                        alt: ''
                    }
                )
			));
			/* ################### */

			/* Text */
			/* ################### */
			arr_out.push(el(
				'div',
				{className: "wp_mf_block " + props.className},
				el(
					TextControl,
					{
						label: __("Heading", 'lang_login'),
						type: 'text',
						value: props.attributes.login_heading,
						/*help: __("Description...", 'lang_login'),*/
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

			jQuery.each(script_custom_login_block_wp.registration_who_can, function(index, value)
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
						label: __("Who Can Register?", 'lang_login'),
						value: props.attributes.registration_who_can,
						options: arr_options,
						onChange: function(value)
						{
							props.setAttributes({registration_who_can: value});
						}
					}
				)
			));
			/* ################### */

			/* Select */
			/* ################### */
			var arr_options = [];

			jQuery.each(script_custom_login_block_wp.get_yes_no_for_select, function(index, value)
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
						label: __("Collect full name from user", 'lang_login'),
						value: props.attributes.registration_collect_name,
						options: arr_options,
						onChange: function(value)
						{
							props.setAttributes({registration_collect_name: value});
						}
					}
				)
			));
			/* ################### */

			/* Select */
			/* ################### */
			var arr_options = [];

			jQuery.each(script_custom_login_block_wp.registration_fields, function(index, value)
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
						label: __("Fields to Display", 'lang_login'),
						value: props.attributes.registration_fields,
						options: arr_options,
						multiple: true,
						onChange: function(value)
						{
							props.setAttributes({registration_fields: value});
						}
					}
				)
			));
			/* ################### */

			return arr_out;
		},

		save: function()
		{
			return null;
		}
	});

	registerBlockType('mf/customlost',
	{
		title: __("Custom Login", 'lang_login'),
		description: __("Display a Custom Login", 'lang_login'),
		icon: 'email', /* https://developer.wordpress.org/resource/dashicons/ */
		category: 'widgets', /* common, formatting, layout, widgets, embed */
		'attributes':
		{
			'align':
			{
				'type': 'string',
				'default': ''
			},
			'lost_password_image':
			{
                'type': 'string',
                'default': ''
            },
			'lost_password_image_id':
			{
                'type': 'string',
                'default': ''
            },
			'lost_password_heading':
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
			}
		},
		edit: function(props)
		{
			var arr_out = [];

			/* Media */
			/* ################### */
			arr_out.push(el(
				'div',
				{className: "wp_mf_block " + props.className},
				el(
                    MediaUploadCheck,
                    {},
                    el(
                        MediaUpload,
                        {
                            onSelect: function(value)
							{
								props.setAttributes({lost_password_image: value.url, lost_password_image_id: value.id});
							},
                            allowedTypes: ['image'],
                            value: props.attributes.lost_password_image_id,
                            render: function(obj)
							{
                                return el(
                                    Button,
                                    {
                                        onClick: obj.open
                                    },
                                    __("Logo", 'lang_login')
                                );
                            }
                        }
                    )
                ),
                props.attributes.lost_password_image && el(
                    'img',
                    {
                        src: props.attributes.lost_password_image,
                        alt: ''
                    }
                )
			));
			/* ################### */

			/* Text */
			/* ################### */
			arr_out.push(el(
				'div',
				{className: "wp_mf_block " + props.className},
				el(
					TextControl,
					{
						label: __("Heading", 'lang_login'),
						type: 'text',
						value: props.attributes.lost_password_heading,
						/*help: __("Description...", 'lang_login'),*/
						onChange: function(value)
						{
							props.setAttributes({lost_password_heading: value});
						}
					}
				)
			));
			/* ################### */

			return arr_out;
		},

		save: function()
		{
			return null;
		}
	});

	registerBlockType('mf/customloggedin',
	{
		title: __("Logged in Information", 'lang_login'),
		description: __("Display Information About the Logged in User", 'lang_login'),
		icon: 'unlock', /* https://developer.wordpress.org/resource/dashicons/ */
		category: 'widgets', /* common, formatting, layout, widgets, embed */
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
			}
		},
		edit: function(props)
		{
			var arr_out = [];

			/* Select */
			/* ################### */
			var arr_options = [];

			jQuery.each(script_custom_login_block_wp.logged_in_info_display, function(index, value)
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
						label: __("List", 'lang_login'),
						value: props.attributes.logged_in_info_display,
						options: arr_options,
						multiple: true,
						onChange: function(value)
						{
							props.setAttributes({logged_in_info_display: value});
						}
					}
				)
			));
			/* ################### */

			return arr_out;
		},

		save: function()
		{
			return null;
		}
	});
})();