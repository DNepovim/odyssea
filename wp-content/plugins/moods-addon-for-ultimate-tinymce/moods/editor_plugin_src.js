/**
 * editor_plugin_src.js
 *
 * Copyright 2009, Moxiecode Systems AB
 * Released under LGPL License.
 *
 * License: http://tinymce.moxiecode.com/license
 * Contributing: http://tinymce.moxiecode.com/contributing
 */

(function(tinymce) {
	tinymce.create('tinymce.plugins.MoodsPlugin', {
		init : function(ed, url) {
			// Register commands
			ed.addCommand('mceMoods', function() {
				ed.windowManager.open({
					file : url + '/moods.htm',
					width : 400 + parseInt(ed.getLang('moods.delta_width', 0)),
					height : 400 + parseInt(ed.getLang('moods.delta_height', 0)),
					inline : 1
				}, {
					plugin_url : url
				});
			});

			// Register buttons
			ed.addButton('moods', {title : 'Josh\'s Ultimate Moods', image : url+'/mybutton.png', cmd : 'mceMoods'});
		},

		getInfo : function() {
			return {
				longname : 'Moods',
				author : 'Moxiecode Systems AB',
				authorurl : 'http://tinymce.moxiecode.com',
				infourl : 'http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/emotions',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('moods', tinymce.plugins.MoodsPlugin);
})(tinymce);