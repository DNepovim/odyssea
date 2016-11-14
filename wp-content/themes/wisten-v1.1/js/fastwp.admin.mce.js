(function() {
    tinymce.create('tinymce.plugins.FastWp', {
        /**
         * Initializes the plugin, this will be executed after the plugin has been created.
         * This call is done before the editor instance has finished it's initialization so use the onInit event
         * of the editor instance to intercept that event.
         *
         * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
         * @param {string} url Absolute URL to where the plugin is located.
         */
        init : function(ed, url) {
			ed.addCommand("fastwp_shortcodes", function ( a, params )
			{
				var popup = 'fastwp_shortcodes';
				// load thickbox
				// tb_show("Insert Shortcode", ajaxurl  + "?action=build_shortcodes&popup=" + popup + "&height=400&width=600&inlineId=divStart");
				tb_show("Insert Shortcode", ajaxurl  + "?action=build_shortcodes&popup=" + popup + "&height=100%&width=100%");
			
				jQuery('#TB_ajaxContent').css({height:'85%',width:'auto'});
				jQuery('.fwp-iris').wpColorPicker();
			});
			
            ed.addButton('fastwp_shortcodes', {
                title : 'Insert shortcode',
                cmd : 'fastwp_shortcodes',
                image : url + '/fastwp_shortcode_16.png'
            });
        },
        /**
         * Creates control instances based in the incomming name. This method is normally not
         * needed since the addButton method of the tinymce.Editor class is a more easy way of adding buttons
         * but you sometimes need to create more complex controls like listboxes, split buttons etc then this
         * method can be used to create those.
         *
         * @param {String} n Name of the control to create.
         * @param {tinymce.ControlManager} cm Control manager to use inorder to create new control.
         * @return {tinymce.ui.Control} New control instance or null if no control was created.
         */
        createControl : function(n, cm) {
            return null;
        },
        /**
         * Returns information about the plugin as a name/value array.
         * The current keys are longname, author, authorurl, infourl and version.
         *
         * @return {Object} Name/value array containing information about the plugin.
         */
        getInfo : function() {
            return {
                    longname : 'FastWp Buttons',
                    author : 'Ionut Stoica',
                    authorurl : 'http://ionut.me',
                    infourl : 'http://ionut.me',
                    version : "0.1"
            };
        }
    });
    // Register plugin
    tinymce.PluginManager.add('fwp_shortcodes', tinymce.plugins.FastWp);
})();