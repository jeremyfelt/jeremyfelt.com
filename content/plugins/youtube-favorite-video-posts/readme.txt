=== YouTube Favorite Video Posts ===

Contributors: jeremyfelt
Donate link: http://jeremyfelt.com/wordpress/plugins/youtube-favorite-video-posts/
Tags: youtube, custom post type, embed, video, rss, feed
Requires at least: 3.2.1
Tested up to: 3.5
Stable tag: 1.1

YouTube Favorite Video Posts grabs videos you mark as favorites in YouTube and adds them to WordPress under a custom post type.

== Description ==

YouTube Favorite Video Posts works in the background to grab videos you mark as favorites in YouTube. The feed is parsed into
new posts in WordPress and videos are automatically embedded in the content of those posts.

Once this data is available, you can add support to your theme to display your latest favorite videos for your readers.

Settings are available for:

* YouTube Username
    * The most important. From this the plugin will determine the RSS feed to use for your favorites.
* Embed Width & Embed Height
    * These values are applied to the embedded iframe code that is inserted into your post content.
* Max Items To Fetch
    * If you aren't a regular YouTube favoriter, you may want to reduce this so that your server doesn't process the same items over and over again.
* Post Type
    * By default a new custom post type for YouTube favorites has been added. You can change this to any of your other custom post types or the WordPress default 'post'.
* Default Post Status
    * Choose to publish the new posts immediately, or save them as drafts for later processing.
* Feed Fetch Interval
    * Defaults to hourly, but can be changed to any interval registered with your WordPress setup.

Filters are available for:

* 'yfvp_new_video_embed_code' - Alter or replace the video embed code before the new post is created
* 'yfvp_new_video_item_title' - Alter or replace the video title before the new post is created

Checkout the [example code for the new filters] (http://jeremyfelt.com/wordpress/2012/05/12/filters-in-youtube-favorite-video-posts).

== Installation ==

1. Upload 'youtube-favorite-video-posts' to your plugin directory, usually 'wp-content/plugins/', or install automatically via your WordPress admin page.
1. Activate YouTube Favorite Video Posts in your plugin menu.
1. Add your YouTube username and change other options using the YouTube Videos menu under Settings in your admin page. (*See Screenshot*)

That's it!

== Frequently Asked Questions ==

= Why aren't there any FAQs? =

*  Because nobody has asked a question yet.

== Screenshots ==

1. An overview of the YouTube Favorite Video Posts settings screen.

== Changelog ==
= 1.1 =

* Smarter hashing of uniqueness, should handle Youtube video title changes
* Better original title handling. You can now modify the title through a filter without worrying about it causing duplicates
* Code cleanup

= 1.0 =

* Add filters to allow other themes and plugins to change the post content and title before saving
* Allow the selection of any registered interval for Cron tasks
* Better internationalization, everything is now attached to the youtube-favorite-video-posts text domain
* Better handling of events when Youtube name has not yet been provided
* Code cleanup, move everything to a class
* Front and back end documentation cleanup
* Confirmed support for upcoming WordPress 3.4 release

= 0.3 =

* Because of the default SimplePie cache, new items were only being pulled every 12 hours (at the least). Modified to make this feed fresh for every check.

= 0.2 =

* Video titles with double quotes were not saving correctly. Added appropriate escaping.
* General code cleanup.

= 0.1 =

* In which a plugin begins its life.

== Upgrade Notice ==
= 1.1 =

* Upgrades some of the hashing and video handling to allow for better future customization.

= 1.0 =

* A bunch of great updates. Definitely upgrade.

= 0.3 =

* Things should feel nicer now. :)

= 0.1 =

* Initial installation.