(function (a) {
	a.create("tinymce.plugins.MoodsPlugin", {
		init: function (b, c) {
			b.addCommand("mceMoods", function () {
				b.windowManager.open({
					file: c + "/moods.htm",
					width: 400 + parseInt(b.getLang("moods.delta_width", 0)),
					height: 400 + parseInt(b.getLang("moods.delta_height", 0)),
					inline: 1
				},
				{
					plugin_url: c
				})
			});
			b.addButton("moods", {
				title: "Josh\'s Ultimate Moods",
				image: c + "/mybutton.png",
				cmd: "mceMoods"
			})
		},
		getInfo: function () {
			return {
				longname: "Moods",
				author: "Moxiecode Systems AB",
				authorurl: "http://tinymce.moxiecode.com",
				infourl: "http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/moods",
				version: a.majorVersion + "." + a.minorVersion
			}
		}
	});
	a.PluginManager.add("moods", a.plugins.MoodsPlugin)
})(tinymce);