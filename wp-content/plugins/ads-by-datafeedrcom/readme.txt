=== Ads by datafeedr.com ===

Contributors: datafeedr.com
Tags: ads, random ads, rotating ads, datafeedr, advertisements, advertising, banner ads, banners, adsense, google adsense
Requires at least: 3.5
Tested up to: 4.8
Stable tag: 1.0.12
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Add and display ads randomly on your blog. Sort ads randomly, limit the number of ads that appear, display ads in posts, widgets or template files.

== Description ==

The Ads plugin allows you to add advertisements to your blog.  You can add advertisements to your posts, pages or Text Widgets via the shortcode.  You can also add ads to your site by adding a function to your theme's template files.

You have full control over how many ads get displayed as well as their sort order.

[youtube http://www.youtube.com/watch?v=tPL8ND0nh4o]

= Simple Usage (Shortcode) = 

`[dfads params='']`

= Simple Usage (PHP) = 

`<?php echo dfads(); ?>`

= Advanced Usage (Shortcode) = 

`[dfads params='groups=3&limit=2']`

= Advanced Usage (PHP) = 

`<?php echo dfads( 'groups=3&limit=2' ); ?>`

= Additional Features =

* **Set impression limit** - Set how many times ad should appear before being removed from display.
* **Set start date** - Set the date the ad should not appear before.
* **Set end date** - Set the date the ad should not appear after.
* **Shortcode** - Add ads to posts, pages or Text Widget using the [dfads] shortcode.
* **PHP Function** - Embed ads in your template files with dfads() function.
* **Supports caching** - Impressions are counted and ads are ordered displayed randomly even if you have enabled a caching plugin. (*Note: Doesn’t work if your site’s visitor has JavaScript disabled in their browser.*)
* **Supports All Types of Ads** - Add Google Adsense, banners, images, text, in-house ads, videos, etc...  If you can add it to a Post, you can add it as an ad.
* **Ad Groups** - Set up 'ad groups' to display different groups of ads in different places. (*Example: sidebar, footer, leaderboard, 150x150, etc...*)
* **No Impression Count for Admins** - Enable or disable impression counting when viewing the site as an Administrator. 

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload the `ads-by-datafeedrcom` folder to the `/wp-content/plugins/` directory
1. Activate the *Ads by datafeedr.com* plugin through the 'Plugins' menu in WordPress
1. Configure the plugin by going to Settings > Ads.
1. Insert the shortcode into your posts, pages or Text widget or by adding the PHP template code to your theme's template files.

Check out [datafeedr.com](http://datafeedr.com/dfads/ "Ads by datafeedr.com documentation") for a full list of available parameters and their usages.

== Frequently Asked Questions ==

= Does this plugin track clicks? =

No. You'll need to use another click tracking plugin or service.

== Screenshots ==
1. Add a new ad
2. Configure the ad's settings
3. View a list of all of the ads you've created
4. Configure plugin's settings
5. Generate a shortcode
6. Generate PHP code
7. Add shortcode to Text widget
8. View ads on site

== Video Tutorials ==

View the full video tutorial playlist [here](http://www.youtube.com/playlist?list=PL-WvgpJEzoeZoqFLMVcXgSry-S6iqKo8K&feature=view_all "Video Playlist on YouTube").

= Create a new ad and display a group of ads in a widget  =

[youtube http://www.youtube.com/watch?v=tPL8ND0nh4o]

= Display a group of ads in a post using the shortcode  =

[youtube http://www.youtube.com/watch?v=jdeB_HjkYJU]

= Display a group of ads above a post's content using the PHP function  =

[youtube http://www.youtube.com/watch?v=eQR5HCEAvYQ]

= Configure your ads to count impressions and order randomly even when using a caching plugin  =

[youtube http://www.youtube.com/watch?v=M3v_1vtnh9c]

= Documents most of the parameters of a shortcode or function  =

[youtube http://www.youtube.com/watch?v=MU87O6J9zSo]

== Changelog ==

= 1.0.12 - 2017-06-02 =
* Changed methods with same name as their class to proper __construct() methods.
* Fixed undefined index issues.
* Fixed non-static method being called statically.
* Removed sidebar in admin interface.

= 1.0.11 - 2016-04-01 =
* Removed some dead links from the admin page.

= 1.0.10 =
* Fixed more undefined indexes.

= 1.0.9 =
* Fixed undefined indexes.
* Added "static" to static methods to meet Strict Standards.

= 1.0.8 =
* Fixed Warning: Illegal string offset 'dfads_enable_shortcodes_in_widgets' Errors.


