=== MiniMeta Widget ===
Contributors: danielhuesken
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=daniel%40huesken-net%2ede&item_name=Daniel%20Huesken%20Plugin%20Donation&item_number=MiniMeta%20Widget&no_shipping=0&no_note=1&tax=0&currency_code=EUR&lc=DE&bn=PP%2dDonationsBF&charset=UTF%2d8
Tags: widget, sidebar, meta, admin, links, login, gravatar
Requires at least: 2.8
Tested up to: 3.0.0
Stable tag: 4.5.3

"Mini" Version of the WordPress Meta Widget.

== Description ==

"Mini" Version of the WP Meta Widget

* Different logon types (Form,Link)
* Uses redirection for logon/logout 
* All standard WP Links can enabled/disabled
* Additional Admin Links (from Plugins too)
* Display Blog Links
* build in style sheet support

Only 2.8+

== Installation ==

1. Download MiniMeta Widget.
1. Decompress and upload the contents of the archive into /wp-content/plugins/.
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= Plugin Hooks are tested =

MiniMeta Version later 2.7.0 needed:&lt;br /&gt;
[Semisecure Login](http://wordpress.org/extend/plugins/semisecure-login/)<br />
[Raz-Captcha](http://wordpress.org/extend/plugins/raz-captcha/)<br />
[Chap Secure Login](http://wordpress.org/extend/plugins/chap-secure-login/)<br />

= Raz-Captcha Plugin hooks do not work =

In the Plugin file are dependings for files where the plugin work. This dependings must be commentet out.


== Screenshots ==

1. MiniMeta Widget with Login form
2. Some links of MiniMeta Widget when logged in
3. MiniMeta Widget Options Page

== Changelog ==
= 4.5.3 =
* Now Link Name for RSS Feeds can changed

= 4.5.2 =
* Added flattr Button in Help

= 4.5.1 =
* Fixed bug for clean install on config save

= 4.5.0 =
* new config page design
* removed backword compatibilty lower than WP 2.8
* liitel fixes

= 4.2.5 =
* fixed widged removed after Plugin update
* add text bevore and after user identity in title
* fixed global for $wp_version
* fixed bug in adminlinks generation of links

= 4.2.4 =
* removed useless classes
* Prevent direct file loading
* spell check
* add links on plugin page

= 4.2.3 =
* widget not displayed on when logged out

= 4.2.2 =
* Bugfix for Plugin activate function
* Bugfix for option not Displaying on Pagesa

= 4.2.1 =
* More translations
* Bug fix for Admin Links
* Fixed Options not saved Problems

= 4.2.0 =
* &lt;UL&gt; improvements
* Links ar sortable
* class support for styles
*  WP 2.8 WP_Widget class usage
* Use Link Name Dashboard or Site Admin for Link: Site Admin
* Form in ul li tags

= 4.1.0 =
* Extra File for WP 2.6 compatibility
* Use some more WP 2.7 functions
* Load Scripts in footer WP 2.8
* Display gravatar
* Display Links by category's
* Use of contextual help
* Reworked Config Page for more intuitive
* Removed K2 SBM Support
* Some bug fixes

= 4.0.2 =
* Some bug fixes and improvements

= 4.0.1 = 
* Bufix for WP autoupdate
* Option Page per function
* Style for login form input fields

= 4.0.0 =
* New Plugin file structure
* Support for Sidebar Widget with function
* Uninstall function as form and for WP 2.7
* Best K2SBM detection
* Display own Links from Blog Links
* Complete new Optionspage

= 3.5.9 =
* Bug fix for no topics
* New language file selection   
 
= 3.5.8 =
* Path fix for wp 2.5

= 3.5.7 =
* K2RC7 Capability older SBM don't work
* https path compatibility

= 3.5.6 =
* Lang Path fix

= 3.5.5 =
* Fixes for K2 1.0-RC6
* WP 2.6 Plugin dir capability

= 3.5.4 =
* admin_head corrections
* adminlink generation improvements
* more &lt;ul&gt; xhtml fixes

= 3.5.3 =
* Adminlinks creation for menus with empty submenus and menus without same submenu link

= 3.5.2 =
* Now Hopeful complete bug fix at adminlinks selection

= 3.5.1 =
* Add message to notify when WP is lower than 2.5
* Fix bug at adminlinks selection

= 3.5.0 =
* Full Compatibility to WP 2.5
* Added Option to disable topics for Admin Links
* Added enable/disable WordPress Cookie test
* Added enable/disable login/ou redirect
* &lt;ul&gt; xhtml fixes
* Loginform and LoginLink at same time
* Automatic Admin Links creation as Admin on Plugins Tab. minimeta-adminlinks.php no more needed.

= 3.0.1 =
* Bugfix &lt;/Optiongroup&gt;
* Bugfix for &lt;li&gt; (thx David Potter)
* Grammar fixes
* Cookie handling for login fix
* custom style not load fix
* CSS style fix for thems

= 3.0.0 =
* Better/full support for K2 SBM
* Placing the widget up to 9 times
* removed WP-Admin Links Plugin Support
* Integrated Owen Admin Links
* Style Sheet Support
* Support for own Style Sheet and Admin Links

= 2.7.1 =
* Grammar fixes (thx Joe)
* Updated German Localization (thx Joe)
* readded link to Your Profile

= 2.7.0 =
* Support for WP Admin Links (http://wordpress.org/extend/plugins/wp-admin-links/)
* Some more Code Cleanup
* Changed MinMeta.php to minimeta-widget.php
* Added plugin hooks for login form
* Tested with: 
* * Semisecure Login (http://wordpress.org/extend/plugins/semisecure-login/)
* * Raz-Captcha (http://wordpress.org/extend/plugins/raz-captcha/) delete site deepening's
* * Chap Secure Login (http://wordpress.org/extend/plugins/chap-secure-login/)

= 2.6.5 =
* removed Update check because its integrated in WP 2.3

= 2.6.4 =
* Compatibility for Sidebar Modules and K2 SBM
* Added German Translation (Only not in WordPress strings)

= 2.6.3 =
* Code Cleanup
* Fixed some bugs

= 2.6.2 =
* Added User Identity function to Title
* Removed Your Profile form Links and add the link to Title

= 2.6.1 =
* Added Update check

= 2.6.0 =
* More Admin Links Plugin/comments/User
* All links can now enabled/disabled for login/logout
* Cleaned up Options page

= 2.5.1 =
* Small Bug fix in new post entry

= 2.5.0 =	
* Added links for New Page/Post
* Added Translation functionality
* Added deleting options on deactivating plugin

= 2.0.0 =
* enable/disable links
* Different Login Types
* Login/Logout with redirect
			  
=  1.0.0 =
* Initial Release