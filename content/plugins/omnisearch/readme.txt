=== Global Admin Search ===
Contributors: georgestephanis, japh, lessbloat
Tags: omnisearch, core-plugins
Requires at least: 3.5
Tested up to: 3.7
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This is a proposal for inclusion in Core in 3.8

== Description ==

More details forthcoming.

== Changelog ==

= 0.9.1 =

* Rewrote WP_Search_Posts class to be descended from WP_Posts_List_Table rather than WP_List_Table
* If there's no items, don't display the table, just a message
* If there's no items, also don't display the link to search that data type
* Miscellaneous tidying

= 0.9 =

* Changed name / namespaces to just Search
* Removed admin menu item
* Removed dashboard widget
* Removed MP6 styles and merged into normal styles, as we can assume MP6
* Removed namespace calls, as it's going into core
* Fixed some whitespace stuffs
* ???

= 0.8.1 =

* Add in Dashboard Widget