<?php
/**
 * Load API functions, register scripts and actions, etc.
 *
 * @package gutenberg
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Silence is golden.' );
}

// These files only need to be loaded if within a rest server instance
// which this class will exist if that is the case.
if ( class_exists( 'WP_REST_Controller' ) ) {
	/**
	* Start: Include for phase 2
	*/
	if ( ! class_exists( 'WP_REST_Widget_Updater_Controller' ) ) {
		require dirname( __FILE__ ) . '/class-wp-rest-widget-updater-controller.php';
	}
	/**
	* End: Include for phase 2
	*/
	require dirname( __FILE__ ) . '/rest-api.php';
}

require dirname( __FILE__ ) . '/client-assets.php';
require dirname( __FILE__ ) . '/i18n.php';
require dirname( __FILE__ ) . '/demo.php';
require dirname( __FILE__ ) . '/widgets.php';
require dirname( __FILE__ ) . '/widgets-page.php';

// Register server-side code for individual blocks.
if ( ! function_exists( 'render_block_core_archives' ) ) {
	require dirname( __FILE__ ) . '/../packages/block-library/src/archives/index.php';
}
if ( ! function_exists( 'render_block_core_block' ) ) {
	require dirname( __FILE__ ) . '/../packages/block-library/src/block/index.php';
}
if ( ! function_exists( 'render_block_core_categories' ) ) {
	require dirname( __FILE__ ) . '/../packages/block-library/src/categories/index.php';
}
// Currently merged in core as `gutenberg_render_block_core_latest_comments`,
// expected to change soon.
if ( ! function_exists( 'render_block_core_calendar' ) ) {
	require dirname( __FILE__ ) . '/../packages/block-library/src/calendar/index.php';
}
if ( ! function_exists( 'render_block_core_latest_comments' )
	&& ! function_exists( 'gutenberg_render_block_core_latest_comments' ) ) {
	require dirname( __FILE__ ) . '/../packages/block-library/src/latest-comments/index.php';
}
if ( ! function_exists( 'render_block_core_latest_posts' ) ) {
	require dirname( __FILE__ ) . '/../packages/block-library/src/latest-posts/index.php';
}


/**
 * Start: Include for phase 2
 */
if ( ! function_exists( 'render_block_legacy_widget' ) ) {
	require dirname( __FILE__ ) . '/../packages/block-library/src/legacy-widget/index.php';
}
/**
 * End: Include for phase 2
 */

if ( ! function_exists( 'render_block_core_rss' ) ) {
	require dirname( __FILE__ ) . '/../packages/block-library/src/rss/index.php';
}
if ( ! function_exists( 'render_block_core_shortcode' ) ) {
	require dirname( __FILE__ ) . '/../packages/block-library/src/shortcode/index.php';
}
if ( ! function_exists( 'render_block_core_tag_cloud' ) ) {
	require dirname( __FILE__ ) . '/../packages/block-library/src/tag-cloud/index.php';
}
if ( ! function_exists( 'render_block_core_search' ) ) {
	require dirname( __FILE__ ) . '/../packages/block-library/src/search/index.php';
}
