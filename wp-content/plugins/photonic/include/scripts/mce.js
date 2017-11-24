(function($) {
	tinymce.PluginManager.add('photonic', function(editor) {
		function html(cls, data, type) {
			data = window.encodeURIComponent(data);
			return '<img src="' + tinymce.Env.transparentSrc + '" class="wp-media mceItem ' + cls + '" ' + 'data-wp-media="' + data + '" data-mce-resize="false" data-mce-placeholder="1" alt="" title="Photonic ' + type + ' gallery" />';
		}

		function restoreMediaShortcodes(content) {
			function getAttr(str, name) {
				name = new RegExp(name + '=\"([^\"]+)\"').exec(str);
				return name ? window.decodeURIComponent(name[1]) : '';
			}

			var newContent;
			newContent = content.replace(/(?:<p(?: [^>]+)?>)*(<img [^>]+>)(?:<\/p>)*/g, function(match, image) {
				var data = getAttr(image, 'data-wp-media');
				if (data) {
					return '<p>' + data + '</p>';
				}

				return match;
			});
			return newContent;
		}

		editor.on('mouseup', function(event) {
			var dom = editor.dom,
				node = event.target;

			function unselect() {
				dom.removeClass(dom.select('img.wp-media-selected'), 'wp-media-selected');
			}

			if (node.nodeName === 'IMG' && dom.getAttrib(node, 'data-wp-media')) {
				// Don't trigger on right-click
				unselect();
			}
		});

		// Display gallery, audio or video instead of img in the element path
		editor.on('GetContent', function(event) {
			if (event.get) {
				event.content = restoreMediaShortcodes(event.content);
			}
		});

		// Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('...');
		editor.addCommand('Photonic_Gallery', function(ui, v) {
			var node = editor.selection.getNode();
			var type = v.type;

			var shortcode = wp.mce.views.getText(node);
			var shortcodeObj = wp.shortcode.next(Photonic_Admin_JS.shortcode, shortcode);
			var shortcodeAttr = shortcodeObj.shortcode.attrs.named;

			var template = $('#tmpl-photonic-editor-' + type).html();
			template = $(template);

			// First, set all the inputs and selects in the template to the shortcode values
			var inputs = template.find('input');
			$(inputs).each(function(idx, input) {
				if (shortcodeAttr[input.name] != undefined) {
					template.find('input[name="' + input.name + '"]').attr('value', shortcodeAttr[input.name]);
				}
			});

			var selects = template.find('select');
			$(selects).each(function(idx, select) {
				if (shortcodeAttr[select.name] != undefined) {
					template.find('select[name="' + select.name + '"] option[value="' + shortcodeAttr[select.name] + '"]').attr('selected', 'selected');
				}
			});

			// Passing the template to the WindowManager has issues retrieving values, so we now dynamically get the fields
			// The previous step is necessary, otherwise the shortcode values are not passed.
			var rows = template.find('label');
			var fields = [];
			$(rows).each(function(idx, row) {
				var label = $(row).find('span.label').text();
				var field = $(row).find('input,select');
				var tooltip = $(row).find('span.hint').text();
				if (field.length > 0) {
					var fieldObj = {
						type: field[0].nodeName == 'INPUT' ? 'textbox' : (field[0].nodeName == 'SELECT' ? 'listbox' : ''),
						name: field[0].name,
						label: label,
						value: field[0].value,
						tooltip: tooltip
					};
					if (field[0].nodeName == 'SELECT') {
						fieldObj.values = [];
						$(field[0]).children().each(function() {
							fieldObj.values[fieldObj.values.length] = {
								text: $(this).text(),
								value: this.value
							}
						});
					}
					fields[fields.length] = fieldObj;
				}
			});

			editor.windowManager.open({
				title: 'Photonic Shortcode Editor - ' + (type == 'wp' ? 'WP' : type.substr(0,1).toUpperCase() + type.substr(1)),
				id: 'photonic-gallery-editor',
				width: 800,
				height: 400,
				body: fields,
				onsubmit: function(e) {
					var insertCode = '[' + Photonic_Admin_JS.shortcode + ' type="' + (type == 'wp' ? 'default' : type) + '"';
					var newCode = e.data;
					$.each(newCode, function(idx, obj) {
						if (obj != '') {
							insertCode += ' ' + idx + "='" + $('<div/>').text(decodeURIComponent(obj)).html() + "'";
						}
					});
					insertCode += ']';
					$(node).attr('data-wpview-text', encodeURIComponent(insertCode));
				}
			});
		});

		function verifyHTML(string) {
			var settings = {};

			if (! window.tinymce) {
				return string.replace(/<[^>]+>/g, '');
			}

			if (! string || (string.indexOf('<') === -1 && string.indexOf('>') === -1)) {
				return string;
			}

			schema = schema || new window.tinymce.html.Schema(settings);
			parser = parser || new window.tinymce.html.DomParser(settings, schema);
			serializer = serializer || new window.tinymce.html.Serializer(settings, schema);

			return serializer.serialize(parser.parse(string, { forced_root_block: false }));
		}

		function getPhotonicType(img) {
			img = $(img);
			var type = 'default';
			if (img.hasClass('photonic-gallery-flickr')) {
				type = 'flickr';
			}
			else if (img.hasClass('photonic-gallery-picasa')) {
				type = 'picasa';
			}
			else if (img.hasClass('photonic-gallery-500px')) {
				type = '500px';
			}
			else if (img.hasClass('photonic-gallery-smugmug')) {
				type = 'smugmug';
			}
			else if (img.hasClass('photonic-gallery-zenfolio')) {
				type = 'zenfolio';
			}
			else if (img.hasClass('photonic-gallery-instagram')) {
				type = 'instagram';
			}
			return type;
		}

		wp.mce.photonic_view_renderer = _.extend({}, wp.media.gallery, {
			shortcode_string: Photonic_Admin_JS.shortcode,
			state: [ 'gallery-edit' ],
			template: wp.media.template('editor-gallery'),

			// Lifted verbatim from mce-view.js, "base" code
			edit: function(text, update) {
				var media = wp.media;
				var type = this.type;
				if (type == Photonic_Admin_JS.shortcode && type != 'gallery') {
					editor.execCommand('Photonic_Gallery', '', {type: 'default'});
					return;
				}

				var frame = media[ type ].edit(text);

				this.pausePlayers && this.pausePlayers();

				_.each(this.state, function(state) {
					frame.state(state).on('update', function(selection) {
						update(media[ type ].shortcode(selection).string(), type === 'gallery');
					});
				});

				frame.on('close', function() {
					frame.detach();
				});

				frame.open();
			},

			initialize: function() {
				var shortcodeAttr = this.shortcode.attrs.named;
				var type;
				if (shortcodeAttr['type'] == undefined) {
					type = Photonic_Admin_JS.default_gallery_type;
				}
				else {
					type = shortcodeAttr['type'];
				}
				if (type == 'default') {
					// Lifted, almost verbatim, from wp-includes/js/mce-view.js. This is the default gallery processing code.
					// If Photonic_Admin_JS.shortcode != 'gallery', the code from WP will be called anyway. Otherwise this code will be triggered
					// if type == 'default'.
					var media = wp.media;
					var attachments = media.gallery.attachments(this.shortcode, media.view.settings.post.id),
						attrs = this.shortcode.attrs.named,
						self = this;

					attachments.more()
						.done(function() {
							attachments = attachments.toJSON();

							_.each(attachments, function(attachment) {
								if (attachment.sizes) {
									if (attrs.size && attachment.sizes[ attrs.size ]) {
										attachment.thumbnail = attachment.sizes[ attrs.size ];
									} else if (attachment.sizes.thumbnail) {
										attachment.thumbnail = attachment.sizes.thumbnail;
									} else if (attachment.sizes.full) {
										attachment.thumbnail = attachment.sizes.full;
									}
								}
							});

							self.render(self.template({
								verifyHTML: verifyHTML,
								attachments: attachments,
								columns: attrs.columns ? parseInt(attrs.columns, 10) : media.galleryDefaults.columns
							}), attrs);
						})
						.fail(function(jqXHR, textStatus) {
							self.setError(textStatus);
						}
					);
				}
				else {
					this.content = html('wp-gallery photonic-gallery photonic-gallery-' + type, this.shortcode.string(), type == 'wp' ? 'WP' : type.substr(0,1).toUpperCase() + type.substr(1));
				}
			}
		});

		editor.addButton('wp_view_edit', {
			tooltip: 'Edit ', // trailing space is needed, used for context
			icon: 'dashicon dashicons-edit',
			onclick: function() {
				var node = editor.selection.getNode();
				if (editor.dom.hasClass(node, 'wpview')) {
					var img = $(node).find('img.photonic-gallery'); // Placeholder
					var div = $(node).find('div.gallery');

					if (!img.hasClass('photonic-gallery') && editor.dom.hasClass( node, 'wpview' )) { // Open Native Gallery editor if that is what is in "wpview".
						wp.mce.views.edit(editor, node);
					}
					else if ((div.length > 0 && Photonic_Admin_JS.shortcode != 'gallery') || img.hasClass('photonic-gallery')) {
						var type = getPhotonicType(img);
						editor.execCommand('Photonic_Gallery', '', {type: type});
					}
				}
			}
		});

		editor.on('click keyup', function(e) {
			if (e.target.nodeName == 'IMG' && e.target.className.indexOf('photonic-gallery') > -1) {
				e.preventDefault();
				var type = getPhotonicType(e.target);
				editor.execCommand('Photonic_Gallery', '', {type: type});
				return false;
			}
			else {
				return false;
			}
		});

		wp.mce.views.register(Photonic_Admin_JS.shortcode, wp.mce.photonic_view_renderer);
	});
})(jQuery);
