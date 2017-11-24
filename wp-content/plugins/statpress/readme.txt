=== Plugin Name ===
Contributors: 
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=PNMW3XZQ9677L
Tags: stats,statistics,widget,admin,sidebar,visits,visitors,pageview,user,agent,referrer,post,posts,spy,statistiche,ip2nation,country
Requires at least: 2.1
Tested up to: 3.6.1
Stable Tag: 1.4.3

StatPress is first real-time plugin dedicated to the management of statistics about blog visits. It collects information about visitors, spiders, search keywords, feeds, browsers etc.

== Description ==

The first real-time plugin dedicated to the management of statistics about blog visits. It collects information about visitors, spiders, search keywords, feeds, browsers etc.

Once the plugin StatPress has been activated it immediately starts to collect statistics information.
Using StatPress you could spy your visitors while they are surfing your blog or check which are the preferred pages, posts and categories.
In the Dashboard menu you will find the StatPress page where you could look up the statistics (overview or detailed).
StatPress also includes a widget one can possibly add to a sidebar (or easy PHP code if you can't use widgets!).

ip2nation support coping ip2nation.sql in StatPress folder!
You could download or update it from http://www.ip2nation.com/

= Support =

I will be happy to help you!


= What's new? =


= Ban IP =

You could ban IP list from stats editing def/banips.dat file.

= DB Table maintenance =

StatPress can automatically delete older records to allow the insertion of newer records when limited space is present.

= StatPress Widget / StatPress_Print function =

Widget is customizable. These are the available variables:

* %thistotalvisits% - this page, total visits
* %since% - Date of the first hit
* %visits% - Today visits
* %totalvisits% - Total visits
* %os% - Operative system
* %browser% - Browser
* %ip% - IP address
* %visitorsonline% - Counts all online visitors
* %usersonline% - Counts logged online visitors
* %toppost% - The most viewed Post
* %topbrowser% - The most used Browser
* %topos% - The most used O.S.

Now you could add these values everywhere! StatPress >=0.7.6 offers a new PHP function *StatPress_Print()*.
* i.e. StatPress_Print("%totalvisits% total visits.");


== Installation ==

- Upload "statpress" directory in wp-content/plugins/
- Activate it using WordPress plugins management page.

You are ready!!!


= Update =

* Deactivate StatPress plugin (no data lost!)
* Backup ALL your data
* Backup your custom DEFs files
* Override "statpress" directory in wp-content/plugins/
* Restore your custom DEFs files
* Re-activate it on your plugin management page
* In the Dashboard click "StatPress", then "StatPressUpdate" and wait until it will add/update db's content

== Frequently Asked Questions ==

= I've a problem. Where can I get help? =


== Screenshots ==
&middot; <a href="http://farm4.static.flickr.com/3409/3214830727_fe46a43f90_o.jpg">screenshot-1 - Overview</a><br>
&middot; <a href="http://farm4.static.flickr.com/3503/3256064748_5d908c3ee6_o.jpg">screenshot-2 - Details</a><br>
&middot; <a href="http://farm4.static.flickr.com/3492/3214830895_4556bb1faa_o.jpg">screenshot-3 - Spy</a><br>


== Updates ==


*Update from 0.1 to 0.2*

* Layout update
* iPhone and other new defs

*Update from 0.2 to 0.3 (15 Jul 2007)*

* Rss Feeds support!
* Layout update
* New defs

*Update from 0.3 to 0.4 (14 Sep 2007)*

* Customizable widget
* New defs

*Update from 0.4 to 0.5 (25 Sep 2007)*

* New "Overview"
* New defs

*Update from 0.5 to 0.5.2 (3 Oct 2007)*

* Solved (rare) compatibility issues - Thanks to Carlo A.

*Update from 0.5.2 to 0.5.3 (4 Oct 2007)*

* Solved setup compatibility issues - Thanks to Andrew

*Update from 0.5.3 to 0.6 (17 Oct 2007)*

* New interface layout
* Export to CSV
* MySQL table size in Overview

*Update from 0.6 to 0.7 (22 Oct 2007)*

* Unique visitors
* New graphs (and screenshots)

*Update from 0.7 to 0.7.1 (27 Oct 2007)*

* (one time) Automatically database table creation

*Update from 0.7.1 to 0.7.2 (30 Oct 2007)*

* Now "Last Pages" and "Pages" sections don't count spiders hits - Thanks to Maddler
* Page title decoded
* New spider defs - Thanks to Maddler

*Update from 0.7.2 to 0.7.3 (8 Nov 2007)*

* New IP banning (new file def/banips.dat)
* Functions updated, bugs resolved - Thanks to Maddler
* New "Overview"
* Updated defs

*Update from 0.7.3 to 0.7.4 (12 Nov 2007)*

* New Spy section
* Updated defs

*Update from 0.7.4 to 0.7.5 (14 Nov 2007)*

* New gfx look
* Updated defs

*Update from 0.7.5 to 0.7.6 (25 Nov 2007)*

* New SEARCH section!
* New StatPress_Print() function

*Update from 0.7.6 to 0.7.7 (28 Nov 2007)*

* New SEARCH section!
* New Options panel
* (Optionally) StatPress collects data about logged users
* New Widget variables: VISITORSONLINE and USERSONLINE

*Update from 0.7.7 to 0.8 (3 Dec 2007)*

* "Automatically delete visits older than..." option

*Update from 0.8 to 0.9 (10 Dec 2007)*

* Added search by IP
* New IP lookup service: hostip.info (spy with flags!)
* New spiders
* "Support" link in dashboard

*Update from 0.8 to 0.9.5 (16 Dec 2007)*

* Multilanguage (support English and Italian... could you help me?)
* Spy links fixed
* Update Overview graph with optional num.of days
* Update queries slashed

*Update from 0.9.5 to 0.9.6 (17 Dec 2007)*

* Spanish translation (Thanks to nv1962)

*Version 1.0*

* WP Date and Time settings support (UTC + timezone offset)

*Version 1.1*

* Time settings patch

*Version 1.2*

* French translation (Thanks to Joel Remaud)
* German translation (Thanks to Martin Bartels)
* Russian translation (Thanks to Aleksandr)
* Portuguese/Brazilian translation (Thanks to gmcosta)
* New option: Minimum "capability" to view stats
* Some Overview update
* 20071225: Dutch translation (Thanks to Matthijs www.hethaagseblog.nl)

*Version 1.2.1*

* Norwegian translation (Thanks to Selveste Radiohode)

*Version 1.2.2 (2 Jan 2008)*

* Resolved some bugs

*Version 1.2.3 (16 Jan 2008)*

* Two Turkish translation (Thanks to Singlemen http://berkant.info/blog/?p=618 and Resat)
* Swedish translation (Thanks to Bjšrn Felten)
* Path independent (Thanks to Christian Heim)
* Some new widget variables
* Some fixes

*Version 1.2.4 (10 Feb 2008)*

* New widget: TopPosts
* "Overview" optimization (Thanks to nexia)
* Security patch (Thanks to livibetter)
* .def updates

*Version 1.2.5 (14 Feb 2008)*

* New option "Do not collect spiders visits"
* Compatibility issue: remove jdmonthname() func
* New Last spiders table
* Selectable fields delimiter in CSV export

*Version 1.2.6 (20 Feb 2008)*

* TopPosts widget new href links (Thanks Flip and Frank)
* .def updates (Thanks to GT)
* Interface fixes

*Version 1.2.7 (27 Feb 2008)*

* New menu layout (top level)
* Updated TopPosts widget code (Thanks to crashdumpfr)
* New hook "send_headers"
* Js, plugins and themes doesn't count
* New spiders (Thanks to M66B)

*Version 1.2.8 (27 Feb 2008)*

* Some Feed issues resolved

*Version 1.2.9 (8 Mar 2008)*

* Feed issues resolved (Thanks to Frank http://www.webtlk.com )
* Comment Feeds support

*Version 1.2.9.1 (16 Apr 2008)*

* Search works again!
* defs updated (Thanks to all forum users!)

*Version 1.2.9.2 (23 Jun 2008)*

* XSS vulnerability patch (Thanks to rogeriopvl blog.rogeriopvl.com)

*Version 1.2.9.3 (21 Jan 2009)*

* Directory structure modified

*Version 1.2.9.4 (2 Feb 2009)*

* defs updated, some icons
* New "Last Agents"
* Bugs fixed

*Version 1.3 (4 Feb 2009)*

* News charts!
* Romanian translation (Thanks to Andrei Gavrilescu)
* Bugs fixed

*Version 1.3.1 (5 Feb 2009)*

* New 3D charts! PHP4/5 compatible. No CPU overload!

*Version 1.3.2*

* Collects browser language setting
* Crypts IP option (privacy regulation)
* Persian localization (thanks to Omid Pilevar)
* Danish localization (thanks to Georg S. Adamsen)
* DEFs updated
* Bugs fixed

*Version 1.3.3*

* Bugs fixed
* New localizations

*Version 1.3.4*

* 1.3.3 bug fixed

*Version 1.3.5 (18 Dec 2009)*

* Database optimization 
* Bugs fixed
* New localizations
* 2.9 compatibility
* New blog!

*Version 1.3.6 (19 Dec 2009)*

* Bugs fixed
* DEFs updated

*Version 1.4 (28 Dec 2009)*

* ip2nation support
* New country chart
* Bugs fixed
* DEFs updated

*Version 1.4.1 (5 Jan 2010)*

* Bugs fixed
* DEFs updated
* OSes icons

*Version 1.4.3 (2 Oct 2013)*

* Security fixes
