=== Koko Analytics ===
Contributors: Ibericode, DvanKooten
Donate link: https://kokoanalytics.com/
Tags: analytics, statistics, stats
Requires at least: 4.6
Tested up to: 5.3
Stable tag: 1.0.5
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Requires PHP: 5.3

Privacy-friendly analytics for your WordPress site

== Description ==

Koko Analytics is a privacy-friendly analytics plugin for your WordPress site. It does not need any external services, so any data about your visitors is never shared with a third-party service.

Furthermore, your visitors' privacy is respected. Nothing that could lead back to a specific visitor is tracked and it is easy for visitors to opt-out of tracking altogether using a standardized browser setting called "Do Not Track".

Stop scrolling through pages of reports while collecting sensitive data about your visitors, both of which you probably do not need.
Koko Analytics allows you to focus on what is important and gives you all the essential metrics you need to improve your website, while truly respecting the privacy of your visitors.

### Features

- Plug and play. Just install and activate the plugin and stats will automatically be recorded.
- No external services. Data about visits to your website is yours and yours alone.
- No personal information or anything visitor specific is tracked.
- Blazingly fast. Handles thousands of daily visitors or sudden bursts of traffic without breaking a sweat.
- Counts all the essential metrics you need: visitors, pageviews and referrers for your site and each individiual page.
- Option to not use any cookies while still being able to detect returning visitors and unique pageviews, albeit less reliable.
- Option to exclude traffic from logged-in users (by user role).
- Option to automatically delete old data.
- Built-in blacklist to filter referrer spam.
- Compatible with pages served from cache.
- Compatible with AMP powered pages.
- GDPR compliant by design.
- Open-source (GPL-3.0+ licensed).

You can [contribute to Koko Analytics on GitHub](https://github.com/ibericode/koko-analytics) or by [translating the plugin into your language](https://translate.wordpress.org/projects/wp-plugins/koko-analytics).


== Installation ==

1. In your WordPress admin area, go to **Plugins > New Plugin**, search for **Koko Analytics** and click **Install now**.
1. Alternatively, [download the plugin files](https://downloads.wordpress.org/plugin/koko-analytics.trunk.zip) and upload the contents of `koko-analytics.zip` to your plugins directory, which usually is `/wp-content/plugins/`.
1. Activate the plugin. Koko Analytics will start recording stats right away.
1. Access your analytics by browsing to *Dashboard > Analytics* in your WordPress admin area.

== Frequently Asked Questions ==

#### Does this respect my visitor's privacy?
Absolutely, nothing that could lead back to the visitor is recorded. Furthermore, if the visitor has "Do Not Track" enabled in their browser, this is respected.

#### Does this use any external services?
No, the data never leaves your website. That's (part of) what makes Koko Analytics such a great choice if you value true privacy.

### Does this set any cookies?
By default, yes. But you can easily disable this in the plugin's settings. Without cookies, the plugin can still detect returning visitors and unique pageviews, albeit less reliable.

### Will this slow down my website?
No, the plugin is built in such a way that it never slows down your website for your visitors. If there is heavy lifting to be done, it is done in a background process.

In fact, because the plugin does not depend on any external services it is usually much faster than third-party analytics tools.


== Screenshots ==

1. Koko Analytics' dashboard to view your website statistics.
2. The settings page where you can exclude certain user roles from being counted.
3. A widget to show your most viewed posts (or any other post type) for a given period.
4. The dashboard widget to quickly show your site visits over the last 2 weeks.


== Changelog ==

#### 1.0.5 - Dec 30, 2019

- Add "today" option to date periods preset menu.
- Hide chart component when viewing just a single day of data.
- Automatically refresh data in dashboard every minute.
- Use human readable number format on chart's y-axes.
- Show chart elements even if outside of chart container.


#### 1.0.4 - Dec 13, 2019

- Fix referrer URL's not being saved correctly.
- Fix unique pageview detection
- Fix pretty number with only trailing zeroes.
- Fix bar chart not stacking properly.
- Improved display of Twitter or Android app referrers.
- Improved chart tooltip.
- Improved styling for small mobile screens.
- Trim trailing slashes from referrer URL's.
- Escape all strings coming from translation files.
- Filter out common bots by checking user agent in tracking script.


#### 1.0.3 - Dec 6, 2019

- Fix link to settings page from plugins overview page.
- Fix REST API URL's when not using pretty permalinks.
- Add support for tracking AMP-powered pages.
- Add setting to disable cookie usage.
- Handle network request errors on admin pages.
- Return HTTP 500 error when unable to write to buffer file.
- Simplify adding post title to post type statistics.
- Extend browser support to include older browsers.
- Handle filesystem errors in aggregation process.


#### 1.0.2 - Nov 22, 2019

- Add icons to datepickers to quickly cycle through selected date periods.
- Add capabilities `view_koko_analytics` and `manage_koko_analytics` to control whether a user role can view or manage statistics.
- Add setting to automatically delete data older than X months.
- Add menu item to WP Admin Bar.
- Update URL when date range changes so page can be refreshed or shared.
- Update browser history with chosen date ranges.
- Show total size of Koko Analytics' database tables on settings page.
- Improved animations when dashboard data updates.
- Improved column type constraints for storing data.
- Improved labels for chart x-axes.
- Consistent ordering of posts and referrers tables.
- Remove trailing `?` character from referrer URL's after query parameters are stripped.
- Fix retrieving post title when post type is excluded from search.


#### 1.0.1 - Nov 14, 2019

- Add dashboard widget showing site visits over last 14 days.
- Add widget for showing most viewed posts, pages or any other post type over a given period.
- Add `[koko_analytics_most_viewed_posts]` shortcode.
- Add pagination to tables showing top posts and top referrers.
- Add settings link to plugin row on plugins overview page in WP admin.
- Use ASCII for storing textual data. Fixes an issue with error message "specified key is too long" on some MySQL installations when the charset is `utf8mb4`.
- Remove all data when uninstalling the plugin. Thanks to [Santiago Degetau](https://profiles.wordpress.org/tausworks/).
- Improved memory usage when handling huge bursts of traffic.
- Load tracking script asynchronously.
- Styling improvements for the dashboard page.


#### 1.0.0 - Nov 4, 2019

Initial release.

