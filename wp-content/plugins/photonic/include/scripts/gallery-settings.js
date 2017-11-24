(function($) {
	var media = wp.media;

	// Wrap the render() function to append controls.
	media.view.Settings.Gallery = media.view.Settings.Gallery.extend({
		render: function() {
			var $el = this.$el;

			media.view.Settings.prototype.render.apply( this, arguments );

			if (Photonic_Admin_JS.shortcode != 'gallery') {
				return;
			}

			// Append the type template and update the settings.
			$el.append( media.template( 'photonic-editor-default' ) );
			$el.find('.photonic-form .hint').remove();

			//media.gallery.defaults.type = 'default'; // lil hack that lets media know there's a type attribute.
			//this.update.apply( this, ['type'] );

			var node = tinymce.activeEditor.selection.getNode();
			var shortcode = wp.mce.views.getText(node);
			var shortcodeObj = wp.shortcode.next(Photonic_Admin_JS.shortcode, shortcode);
			var attrs = shortcodeObj.shortcode.attrs.named;
			//var attrs = this.model.attributes;

			var gallery = this;

			$.each(attrs, function(name, value) {
				media.gallery.defaults[name] = '';
				gallery.update.apply( gallery, [name] );

				var field = $el.find('.photonic-form [name="' + name + '"]');
				if (field.length > 0) {
					field = field[0];
					if (field.nodeName == 'INPUT') {
						field.value = value;
					}
					else if (field.nodeName == 'SELECT') {
						var defaultValue = field.value;
						var options = field.children;
						var oneSelected = false;
						$.each(options, function(i, v) {
							options[i].selected = options[i].value == value;
							if (options[i].value == value) {
								oneSelected = true;
							}
						});
						if (!oneSelected) {
							$.each(options, function(i, v) {
								options[i].selected = options[i].value == defaultValue;
								if (options[i].value == defaultValue) {
									return false;
								}
							});
						}
					}
				}
			});

			var inputFields = $el.find('.photonic-form input, .photonic-form select');
			$(inputFields).on('change', function() {
				var insertCode = '[' + Photonic_Admin_JS.shortcode + ' type="default"';
				//var newCode = e.data;
				$.each(inputFields, function(idx, obj) {
					if (obj.value != '') {
						insertCode += ' ' + obj.name + "='" + $('<div/>').text(decodeURIComponent(obj.value)).html() + "'";
					}
					gallery.model.attributes[obj.name] = obj.value;
				});
				insertCode += ']';
				$(node).attr('data-wpview-text', encodeURIComponent(insertCode));
			});
			return this;
		}
	});
})(jQuery);
