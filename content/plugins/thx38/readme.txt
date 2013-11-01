=== THX38 ===
Contributors: wordpressdotorg, matveb, samuelsidler, shaunandrews, melchoyce, shelob9
Requires at least: 3.6
Tested up to: 3.6
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Reimagining the theme experience in WordPress. Do not install this plugin. It is an experiment that breaks Appearance -> Themes.

== Description ==

This plugin is an ongoing experiment around improving the theme experience in WordPress. This is meant only for development work, and the brave of heart, as it totally breaks themes.php.

Discussions will happen at the WordPress make p2s. Check http://make.wordpress.org/ui/tag/thx38/ if you'd like to participate. All comments and contributions are welcome.

(Plugin assumes the mp6 UI.)

== Changelog ==

= 0.8.3 =
* Fix incorrect display of template instead of child theme when a theme has a parent theme.
* Adds a parent theme notice to the details views, and updates some styles.

= 0.8.2 =
* Add bulk-delete edit mode.
* Style updates.

= 0.8.1 =
* Bug fixes.
* Style improvements.

= 0.8 =
* Adds lazy-rendering of theme views as you scroll.
* Many bug fixes and style updates all around.
* Removes blur effects and improves performance.

= 0.7.1 =
* Adds live URL support using Backbone routes for themes and searches.
* Makes a lot of functions and data objects filterable.
* Fixes bugs.
* Responsive structure enhancements.
* Adds default screenshot fallback.
* Style improvements.

= 0.7 =
* Major update!
* Completely reworks how modal works and looks; theme actions are shown prominently at the bottom of the modal.
* Adds keyboard navigation (with arrow keys) to quickly browse through themes while on details view.
* Major JavaScript refactoring and cleanup.
* Adds "delete" theme functionality with confirm dialogue.
* Implements theme updates notices on theme grid, and update info on modal view.
* Adds theme count that updates immediately with search.
* Several style updates: theme blocks reworked, add new theme, hover styles with magnifyer glass, active theme more prominent, screenshot gallery.

= 0.6.2 =
* Many style and UX improvements with theme overlays.
* Adds modal experiment.

= 0.6.1 =
* Style improvements: screenshot gallery and active theme.

= 0.6 =
* Supports $_GET actions for theme switching and deletes.
* Theme action links abstracted.
* Style improvements.

= 0.5.1 =
* Adds theme-action buttons to activate and preview themes.

= 0.5 =
* First pass at browsing themes screen.
* Many style improvements.

= 0.4.3 =
* Adds theme author url.
* Converts screenshots gallery into dot-navigation.
* Style cleanup and improvements.

= 0.4.2 =
* Multisite path fix.

= 0.4.1 =
* Reworked screenshot galleries to always show a persistent row of screenshots.
* Puts an add-new link next to the screen title following the conventions of mp6.

= 0.4 =
* Multiple screenshots support in the form of 'screenshot-n.png' where n is a number in the range of 2-5.
* Single theme views show a complete screenshot gallery when available.
* Currently active theme displays a "customize" button on hover.
* A bunch of style improvements.
* Linking "add new theme" to core install-theme page (we'll use this for testing).

= 0.3 =
* Implements expanded single theme views as an overlay when clicking on a theme.
* Style updates.

= 0.2 =
* Implements basic search through your theme collection.
* Adds "add new theme" block.

= 0.1 =
* Initial pass.
* Sets up a Backbone.js rendering of installed theme base.
* Flexible design with current active theme showing first.