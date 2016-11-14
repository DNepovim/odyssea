=== WP Tracy ===
Contributors: hlavacm
Donate link: http://www.ktstudio.cz/
Tags: tracy, debugger
Version: 1.0.5
Requires at least: 4.0
Tested up to: 4.5
Stable tag: 4.5
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WP Tracy is a plugin that automatically inserts debugger (Nette) Tracy into WordPress. 

== Description ==

[Tracy](https://github.com/nette/tracy) is an excellent PHP debugger bar from [Nette](https://nette.org) PHP framework. 
[WP Tracy](https://github.com/ktstudio/wp-tracy) is simple implementation and integration of Tracy into WordPress for test environment. 
When it's activated, it automatically shows Tracy bar and displays within global WP constants and their values. 
It's great for local(host) development. 

WARNING: (WP) Tracy is in a production environment by default turned off ...

== Installation ==

1. Upload the `wp-tracy` folder to the `/wp-content/plugins/` directory
2. Activate the WP Tracy plugin through the 'Plugins' menu in WordPress
3. Profit!
4. You can optionally define PHP boolean constant WP_TRACY_CHECK_USER_LOGGED_IN...

== Frequently Asked Questions ==

= What is Tracy? =

[Tracy](https://github.com/nette/tracy) is debugger bar and useful PHP library and helper for a everyday programmer's use.

= What is WP Tracy? =

It is Tracy integration into the WP, including system information (global variables) of WordPress.

= Is WP Tracy active in the production environment? =

No... :)

= Has WP Tracy the visual settings? =

Not yet, there is only constant WP_TRACY_CHECK_USER_LOGGED_IN. So far, It's only a very simple implementation. Just activate and profit!

== Screenshots ==

1. (WP) Tracy bar auto-display after plugin activation 
2. Tracy exception screen 
3. WP versions constants
4. WP (Logged) User information
5. (global) WP Post information
6. (global) WP Query information
7. (global) WP DB information

== Changelog ==

= 1.0.5 =

* Initialization is now in scope of WP init action
* Added WP_TRACY_ENABLE_MODE and wp_tracy_panels_filter
* Update for Tracy 2.4.2

= 1.0.4 =

* Update for Tracy 2.4(.1)

= 1.0.3 =

* Added new get_queried_object() based panel
* Update Tracy 2.3.8 

= 1.0.2 =

* Update Tracy 2.3.7 

= 1.0.1 =

* DOING_AJAX check - for IE compatibility WordPress media upload, thanks to @ViliamKopecky
* Added constants for translation of error messages
* Removed Nice Name parameter from User panel

= 1.0 =

* The first version of plugin including (Nette) Tracy 2.3.5 

== Upgrade Notice ==

* There are no upgrade notice, this is the first version... 