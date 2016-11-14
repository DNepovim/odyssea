=== Post Avatar ===
Contributors: garinungkadol
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=6NB7UMXLTSAJ6
Tags: post, avatars, images, image, thumbnail
Requires at least: 3.9
Tested up to: 4.1.1
Stable tag: 1.6.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Choose an avatar from a pre-defined list to include in a post. 

== Description == 
Allow authors to select an image from a pre-defined list while in the Post Editor screen. This image will be displayed together with a post. 

Post Avatar is similar in concept to Livejournal userpics wherein authors choose images uploaded by the site owner. Developed with Dominik Menke.

= Features =

* Easy selection of images from within the Post Editor screen.
* Scans images in sub-directories of the image option folder.
* Allows the following file types: .jpg, .jpeg, .gif and .png.
* Flexible with customizing avatar display.
  * Display avatars using the default HTML/CSS tags.
  * HTML/CSS tags can be edited from with the Settings screen.
  * Use template tags and custom filters within themes for advanced customization.
* International language support for Belorussian, Czech, Dutch, French, German, Hindi, Irish, Italian, Polish, Romanian, Serbo-Croatian, Spanish
* Does not display missing images.
* Can be further extended to show images for pages and custom post types

= Bug Submission and Forum Support =
[WordPress Forum](https://wordpress.org/support/plugin/post-avatar/)

[Post Avatar home page](http://garinungkadol.com/plugins/post-avatar/support/)

= Please Vote and Review =
Your votes and feedback are greatly appreciated. Thanks.


== Installation ==

= Manual Installation =

1. After downloading the zip file from WordPress.org, extract the contents onto your computer. This will create a folder, `post-avatar`, containing the plugin files.
2. Connect to your webserver and upload the `post-avatar` folder to where your plugins are installed, typically, `/wp-content/plugins`.
3. To activate the plugin go to the **Plugins** menu in your WordPress admin. Click "Activate" for the "Post Avatar" plugin

= Automatic Installation =

1. From the **Plugins** menu, click **Add New** and search for **Post Avatar**
2. Click "Install Now" so that WordPress will download and install the plugin
3. Once the installation is complete, you will be asked to click **Activate Plugin**

**See Also:** ["Installing Plugins" article on the WP Codex](http://codex.wordpress.org/Managing_Plugins#Installing_Plugins)

= Upgrading =

The Plugins section of your WordPress admin provides notification if there are new updates for the plugin.

For a manual upgrade, delete the folder `post-avatar` from the plugins folder and follow the new installation instructions.

For automatic upgrade, click on ** Upgrade Now**.


= Configuration =

You can change default options by going to the **Post Avatar** link in the **Settings** menu at your WordPress admin.

* **Path to Images Folder** - location of your images folder in relation to your WordPress installation. Defaults to `wp-content/uploads`.

* **Show avatar in post? Disable to use template tag.** - Tick this so that the chosen post avatar will be automatically displayed in your post, just prior to your post content. Enabled by default.	
	
* **Scan the images directory and its sub-directories** - Tick this to include images stored in sub-directories of the images folder during the avatar selection. Enabled by default.

* **Get image dimensions? Disable this feature if you encounter getimagesize errors** - Turned on by default, a tick mark here will determine the image's width and height. If you encounter any getimagesize errors, turn this feature off.

* **Show post avatars in RSS feeds?** - Turned off by default, place a tick mark here to include avatars in your RSS feeds.

* **Customize HTML/CSS** - 	These options help you further customize how post avatars will look.
	
	A. **Use this HTML before/after the post avatar image** - Enter the HTML code that will wrap around the image. Can be left blank. The plugin defaults to `<div class="postavatar"></div>`.
 
	Example: Before: `<div class="myimage">` / After: `</div>`
	Output: `<div class="myimage"><img src="http://mydomain.com/images/chosen-image.jpg" height="120" width="180" alt="post-title" /></div>`
	
	
	B. **CSS Class** - Define the CSS class associated with the post avatar image. Can be left blank. It is empty by default.

	Example: The class name is: postimage
	Output: `<img class="postimage" src="http://mydomain.com/images/chosen-image.jpg" height="120" width="180" alt="post-title" />`


Using both HTML and CSS class will result in the following output:
		`<div class="myimage"><img class="postimage" src="http://mydomain.com/images/chosen-image.jpg" height="120" width="180" alt="post-title" /></div>`
	
	
The "Advanced Customization" section of this readme has more advanced customization tips.		
		
= Usage =

**UPLOAD IMAGES**
	
The plugin does not handle image uploads. You will need to use your FTP or a file manager to upload photos. There are also a number of [plugins that handle file uploads](https://wordpress.org/plugins/tags/upload)

Upload images, using your preferred upload method, to the folder defined in the Post Avatar settings page.

**ADDING AN AVATAR TO A POST**

1. Go to the **Post Editor** screen. Below the content text area you will find the Post Avater section.

2. Select the image name from the dropdown list. You can also use the next and previous arrow buttons on either side of the dropdown to scroll through images.

3. Once you've made your selection, click **Publish** for a new post or **Update** for a previously saved one.


== Frequently Asked Questions ==

= Can I upload images with the Post Avatar plugin? =
No. Post Avatar does not support image uploads. It simply looks up the list of images that you've defined in the plugin settings. Images will need to be transferred via FTP or a file manager. 
However, there are a few workarounds:
	1. Make your image folder the same as your WordPress uploads folder to include images from the Media Library in the Post Avatar list. Or,
	2. Install a plugin that will let you upload images to a folder other than the WordPress uploads folder.

= How is Post Avatar different from Featured Images? =
Post Avatar pre-dates Featured Images by 3 years and while both attach an image to a post, Post Avatar makes use of the same images while Featured Images is more for a unique image.

I designed Post Avatar so I can selec images right from the Post Editor screen.
Also, I prefer to use images with smaller dimensions (usually 250px by 250px and lower).
Post Avatar is more efficient when used with a relatively short list of images (anything in the range of more thousands of images and I suggest you stay with Featured Images).

= I'm new to WordPress. How can I change the styling of the image I've chosen? =
You can make use of the HTML/CSS options in the Post Avatar settings page to make sure that the image's look is in line with your theme. See **Configuration** options.

= Can I have multiple Post Avatar boxes for different folder locations in the Editor screen? =
No. Post Avatar only displays a single list of images scanned from the folder you specified in the Post Avatar settings screen.

= Can I use Post Avatar with custom post types? =
Not by default. If you're comfortable with editing your theme's function.php file you can use the custom filter 'gklpa_allowed_post_types' to enable the Post Avatar functionality on custom post types and pages. Please see the Developer section for more details

== Screenshots ==
1. Post Avatar meta box in the Post Editor.
2. Post Avatar settings page.

== Changelog ==
= 1.6 =
* **03/22/2015**
* Version Upgrade: Minimum supported version is now 3.9
* Fixed: Javascript bug that prevented cycling forward to the start of image list (Uncaught TypeError: Cannot read property 'text' of undefined).
* Added: Custom filter to allow displaying Post Avatar meta boxes on post types other than posts. 
* Added: Custom filters to control image list and post avatar display.
* Added: Translations for Serb-Croatian, Romanian and Hindi. Thanks to Borisa Djuraskovic, Alexander Ovsov and Chandel
* Added: Upgrade facility to convert plugin options and handle future database/feature changes
* Revised: Changed plugin options into a single array. 
* Revised: Changed plugin settings to use the Settings API.
* Deprecated: `gkl_check_phpself()`, `gkl_validate_checked()`.
* General code refactoring in keeping with good practices e.g. common prefix labels, using appropriate hooks for enqueuing styles and scripts

= 1.5.1 =
* **02/04/2013**
* Fixed: Improved SEO for alt tag by removing titles with dashes. Now uses escaped text.

= 1.5 =
* **05/11/2012**
* Fixed: Performance issue resulting in "Maximum execution time" errors or slow loading page or footer scripts not working properly. Bug was caused by missing image folder.
* Revised: Change the hook where stylesheets are included. Using `wp_enqueue_scripts` instead of `wp_print_styles`.
* Revised: Using `wp_localize_script()` for JavaScript variables. Removed `gkl_admin_head()`.
* Revised: Logic change for enqueing of scripts in `gkl_display_css()`.
* Revised: Moved loading of plugin text domain to `gkl_init()`.
* Added: `esc_attr()` to all settings. 
* Removed: unneccessary option `gklpa_showinwritepage`.


= 1.4.2 =
* **07/13/2011**
* Added: Gaellic translation. Thanks to Ray.
* Fixed: Exclude revisions from post avatar saving routine


= 1.4.1 =
* **06/27/2011**
* Fixed: Spaces in image filenames were being removed by `esc_url`.


= 1.4 =
* **06/20/2011**
* Added: Improved security checks when saving post meta data and options as well as displaying data.
* Added: Activation hook to process capabilities and default options
* Added: Improved compatibility with WordPress 3.0 and above.
* Deprecated functions: `gkl_unescape_html` and `gkl_dev_override`
* Notice: This will be the last version to support PHP 4


= 1.3.2 =
* **04/13/2011**
* Added: Polish translation. Thanks to Meloniq.
* Fixed: Duplication of post avatar when "apply_filters" tag is used in other plugins.
* Fixed: Improved data validation. Now using wp_kses when validating HTML.


= 1.3.1 = 
* **08/23/2010**
* Added: French translation. Thanks to Mathieu Haratyk.


= 1.3 = 
* **05/14/2010**
* Version Upgrade: Removed usage of deprecated WordPress functions. This version supports WordPress 2.8 and greater.


= 1.2.7.1 =
* **03/11/2010**
* Added: Spanish translation. Thanks to gogollack.
* Added: Czech translation. Thanks to Lelkoun.


= 1.2.7 =
* **02/12/2010**
* Fixed: IE preview problems when reselecting an image. Thanks [spedney](http://wordpress.org/support/topic/305900)
* Fixed: removed border="0" in image display for XHTML compliance. Thanks [Jay August](http://wordpress.org/support/topic/352564)


= 1.2.5.4 =
* **08/21/2009**
* Added: Dutch translation. Thanks to Jay August.


= 1.2.5.3 =
* **08/06/2009**
* Added: Belorussian translation. Thanks to Fat Cower


= 1.2.5.2 =
* **06/02/2009**
* Added: Italian translation. Thanks to Gianni Diurno


= 1.2.5 =
* **12/15/2008)**
* Fixed: Incorrect display of css class
* Fixed: Bugs in image display (height/width switched up)
* Fixed: "Cannot modify header information" errors when saving posts when plugin is used in conjunction with search unleashed plugin
* Added: Theme developer override option for automatic avatar display
* Added: template tag `gkl_get_postavatar`, to return post avatar data in an array. 


= 1.2.4 =
* **03/31/2008**
* Added: Slideshow effect to navigate for next and previous images
* Fixed: Display of avatar in Write Post page and navigation effects work in IE6+
* Added: HTML for meta boxes in WordPress 2.5+
* Added: Option to include avatars in RSS feeds


= 1.2.3 = 
* **10/06/2007**
* Added: Role capabilities that allow admins, editors and authors to post avatars
* Added: HTML and CSS classes inside the options page
* Added: Include automatic avatar display in post excerpts
* Added: Option to display image dimensions
* Fixed: Stop avatars from displaying in feeds


= 1.2.2 =
* **02/12/2007**
* Fixed: Additional checks in updating posts to make sure that comment posting don't delete post avatars


= 1.2.1 = 
* **01/11/2007**
* Added: Compatibility for Wordpress 2.1
* Added: Option to display image automatically without have to use template tag


= 1.2 = 
* **12/09/2006**
* Added: Scan subdirectories for images
* Added: Created external scriptfile to make extending script easier
* Added: Check if PHP_SELF contains substring (for subdomain installations)
* Fixed: Improved image display in Write Post screen
* Added: Check image existence using absolute path instead of url (for those without `Allow_url_fopen`)


= 1.1 =
* **09/06/2006**
* Added: Live preview of avatar in Write Post screen (tested in Mozilla)
* Fixed: `gkl_postavatar` template tag produces correct (X)HTML
* Speed optimization
* Improved parameters ($before, $after, $class)
* Added: Translation support


= 1.0 = 
* **08/26/2006**
* Initial release


== Upgrade Notice ==
= 1.6 =
Please only update if you are using WordPress 3.9 and above.


= 1.5.1 =
Minor revision to improve SEO for image alt tags. Upgrade optional.


= 1.5 =
Fixed performance issue when image folder is missing and general improvements. 


= 1.4.2 =
Added Irish translation. Fixed issue with post avatars being saved twice when post revisions are on.


= 1.4.1 =
If your image filenames have spaces, you will need to upgrade.


= 1.4 =
Improved security. Please save Post Avatar Settings after upgrade.


= 1.3 =
This is a version update. Please only upgrade if you are using WordPress 2.8 or greater.


= 1.2.3 =
If you are upgrading from a previous version of Post Avatar, deactivate and activate the plugin to enable role capabilities.



== Advanced Customization  ==

= For Front End Display =

By default, the plugin hooks into the following filters:  [the_content()](http://codex.wordpress.org/Plugin_API/Filter_Reference/the_content) and [the_excerpt()](http://codex.wordpress.org/Plugin_API/Filter_Reference/the_content).


**OVERRIDE HTML DISPLAY USING FILTER HOOK: gklpa_the_postavatar**

The `gklpa_the_postavatar` filter takes two parameters:

1. `$post_avatar_text` - Original HTML display

2. `$post_avatar` - Post Avatar data in array format. The keys are:

	`avatar_url`: The URL to the image		
	`show_image_dim`: 1 indicates to show image dimensions, 0 to hide them				
	`image_height`: integer value of image height or null if image dimensions is turned off				
	`image_width`: integer value of image width or null if image dimensions is turned off				
	`post_id`: ID of current post				
	`post_title`: Post title for the image attribute				
	`image_name`: Image file name


Example: Display a default image if no avatar is selected

This example makes use of the HTML/CSS settings defined by the site admin.

`
	add_filter( 'gklpa_the_postavatar', 'prefix_show_default_image', 10, 2 );
	function prefix_show_default_image( $post_avatar_html, $post_avatar_array ){
		global $post, $gklpa_plugin_settings;
		
		// Display default image;
		if( is_null( $post_avatar_array ) ){
			if( !empty( $gklpa_plugin_settings['css_class'] ) {
				$css = 'class="' . $gkl_plugin_settings['css_class']. '"';
			}
			$post_avatar_html = $gklpa_plugin_settings['html_before' ] . '<img '. $css . ' src="http://wplatest.dev/images/default-image.jpg" alt="' . esc_attr(strip_tags($post->post_title) ) . '" />'. $gklpa_plugin_settings['html_after'];
		}
		return $post_avatar_html;
	}
`




**OVERRIDE HTML DISPLAY WITH CUSTOM CONTENT HOOK**

If you want to change the HTML completely or override the option to display avatars automatically, use the [remove_filter()](http://codex.wordpress.org/Function_Reference/remove_filter) like so:
`remove_filter('the_content', 'gkl_postavatar_filter', 99 );`
`remove_filter('the_excerpt', 'gkl_postavatar_filter', 99 );`

You can then define your own `the_content` filter function that makes use of the `gkl_postavatar()` or `gkl_get_postavatar()` functions

You will need to use the function `gkl_get_postavatar()` which takes the post object and returns the array of post avatar information.

1. `$post_avatar_array` - Post Avatar data in array format. The keys are:

	`avatar_url`: The URL to the image
	
	`show_image_dim`: 1 indicates to show image dimensions, 0 to hide them
	
	`image_height`: integer value of image height or null if image dimensions is turned off
	
	`image_width`: integer value of image width or null if image dimensions is turned off
	
	`post_id`: ID of current post
	
	`post_title`: Post title for the image attribute
	
	`image_name`: Image file name

Example:
`
	add_filter( 'the_content', 'my_custom_post_avatar' );
	function my_custom_post_avatar( $content ){
		global $post;

		$current_avatar = gkl_get_postavatar( $post );
		$html_before = '<span class="alignleft">';
		$html_after = '</span>';
		// Display default image
		if( is_null( $current_avatar ) ) {
			$image_url = 'http://wplatest.dev/images/default-image.jpg';
			$alt_text = esc_attr(strip_tags($post->post_title) );
		} else {
			$image_url = $current_avatar['avatar_url'];
			$alt_text = $current_avatar['post_title'];
		}
		$post_avatar_html = $html_before . '<img src="'. $image_url . '" alt="' . $alt_text . '" />'. $html_after;
			
		return $post_avatar_html;	
	}
`

**OVERRIDE HTML DISPLAY WITH template tag `gkl_postavatar`**

If you want the post avatar to appear outside of the content, e.g. with the entry's meta information, make use of the `gkl_postavatar()` template tag.

It takes four paramters:

	`class`: CSS class to use in the `<img>` tag.	
	`before`: HTML to appear before the image.	
	`after`: HTML to appear after the image.	
	`do_what`: Use `echo` to display the post avatar, `return` to pass it to a variable. Defaults to `echo`.
	
	
Example: In a template file:	
`
	<div class="entry-meta">	
	<?php gkl_postavatar('', "<span class='alignleft'>", "<span>" );?>

	-- more template tags here --
	</div>
`		
Or you can make your own template tag function like in the example for "Override HTML display with custom content hook", except you call the function directly in your template instead of hooking into `the_content()`.

= For Administration Screens =

**Add Post Avatar to Pages and Custom Post Types**

Use the filter hook `gklpa_allowed_post_types` to add further post types that you want the Post Avatar selection to appear on.

It takes an array of post type slugs as a parameter.

`
	add_filter( 'gklpa_allowed_post_types', 'prefix_my_custom_post_types' );
	function prefix_my_custom_post_types( $current_post_types ){
		$current_post_types = array( 'post', 'page', 'review', 'event' );
		return $current_post_types;
	}
`


**Enable Image Selection for Folder Outside of WordPress Installation**

By default, Post Avatar looks for your images folder in relation to your WordPress installation. If you want to move your folder elsewhere, use these pair of filter hooks: `gklpa_image_url` and `gklpa_image_dir`. They take a single parameter: Image folder url and absolute path to the image folder, respectively.

`
	add_filter( 'gklpa_image_url', 'prefix_change_folder_url' );
	function prefix_change_folder_url( $current_url ){
		return esc_url( 'http://mysite.com/images/' );
	}

	add_filter( 'gklpa_image_dir', 'prefix_change_folder_dir' );
	function prefix_change_folder_dir ){
		return '/user/public_html/images/';
	}
`

Please visit the [Post Avatar Page](http://www.garinungkadol.com/plugins/post-avatar/) for details on customizing the avatar display.

= Translations =

Post Avatar is translation-ready and supports a number of languages. If you can't find your language here, please consider contributing a language pack.

If you're interested, please check out the ["Codestyling Localization" plugin](http://wordpress.org/extend/plugins/codestyling-localization/) and for validating the ["Poedit Editor"](http://www.poedit.net/).

Send in your translations to vix@garinungkadol.com

Thanks to the following for their language packs.

* Belorussian (ru_RU) Fat Cower
* Czech (cz_CZ) Lelkoun
* Dutch (nl_NL) Jay August
* French (fr_FR) Mathieu Haratyk
* German (de_DE) Dominik Menke
* Hindi (hi_IN_Hindi) Outshine Solutions 
* Irish (ga_IR) Ray S.
* Italian (it_IT) Gianni Diurno
* Polish (pl_PL) Meloniq
* Romanian (ro_RO) Webhosting Geeks
* Serbo-Croatian (sr_RS) Webhosting Hub
* Spanish (es_ES) gogollack

