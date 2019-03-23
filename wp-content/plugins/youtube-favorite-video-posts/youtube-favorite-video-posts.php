<?php
/*
Plugin Name: YouTube Favorite Video Posts
Plugin URI: http://jeremyfelt.com/wordpress/plugins/youtube-favorite-video-posts
Description: Checks your YouTube favorite videos RSS feed and creates new posts in a custom post type.
Version: 1.2
Author: Jeremy Felt
Author URI: http://jeremyfelt.com
License: GPL2
*/

/*  Copyright 2011-2013 Jeremy Felt (email: jeremy.felt@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class Youtube_Favorite_Video_Posts_Foghlaim {

	public function __construct() {
		//Things happen when we activate and deactivate the plugin of course.
		register_activation_hook( __FILE__, array( $this, 'activate_plugin' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate_plugin' ) );

		// Make a pretty link for settings under the plugin information.
		add_filter( 'plugin_action_links', array( $this, 'add_plugin_action_links' ), 10, 2 );

		// Add our custom settings to the admin menu.
		add_action( 'admin_head', array( $this, 'dashboard_style' ) );
		add_action( 'admin_menu', array( $this, 'add_settings' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_init', array( $this, 'add_languages' ) );

		// Register the jf_yfvp_youtube custom post type.
		add_action( 'init', array( $this, 'create_content_type' ) );

		// Our hook added when we schedule a WP Cron event.
		add_action( 'jf_yfvp_process_feed', array( $this, 'process_feed' ) );
	}

	/**
	 * When activating the plugin, register our custom post type and flush the rewrite
	 * rules if the option to use our custom post type has been selected.
	 */
	public function activate_plugin(){
		// Create the custom post type upon activation.
		$this->create_content_type();

		$current_options = get_option( 'jf_yfvp_options', array() );
		$valid_fetch_intervals = wp_get_schedules();

		/**
		 * If the custom post type provided by this plugin is selected, flush the rewrite
		 * rules so that the URLs can be pretty.
		 */
		if ( isset( $current_options['post_type'] ) && 'jf_yfvp_youtube' === $current_options['post_type'] ) {
			flush_rewrite_rules( false );
		}

		/**
		 * If a fetch interval has previously been selected, use that. Otherwise, we'll
		 * not schedule the event until settings save.
		 */
		if ( isset( $current_options['fetch_interval'] ) && in_array( $current_options['fetch_interval'], $valid_fetch_intervals ) ) {
			wp_schedule_event( ( time() + 120 ) , $current_options['fetch_interval'], 'jf_yfvp_process_feed' );
		}
	}

	/**
	 * When the plugin is deactivated, we want to make sure that the WP Cron event
	 * we have scheduled is cleared.
	 */
	public function deactivate_plugin(){
		wp_clear_scheduled_hook( 'jf_yfvp_process_feed' );
	}

	/**
	 * Add the text domain for plugin translation.
	 */
	public function add_languages() {
		load_plugin_textdomain( 'youtube-favorite-video-posts', false, basename( dirname( __FILE__ ) ) . '/lang' );
	}

	/**
	 * Add a link for the plugin settings page when viewing the general plugins display.
	 *
	 * Function gratefully borrowed from Pippin Williamson's WPMods article:
	 * http://www.wpmods.com/adding-plugin-action-links/
	 *
	 * @param array  $links Current array of links to be displayed under the plugin.
	 * @param string $file  The current plugin file being processed.
	 *
	 * @return array New array of links to be displayed under the plugin.
	 */
	public function add_plugin_action_links( $links, $file ){
		static $this_plugin;

		if ( ! $this_plugin ) {
			$this_plugin = plugin_basename( __FILE__ );
		}

		if ( $file == $this_plugin ) {
			$settings_link = '<a href="' . esc_url( site_url( '/wp-admin/options-general.php?page=youtube-favorite-video-posts-settings' ) ) . '">' . __( 'Settings', 'youtube-favorite-video-posts' ) . '</a>';
			array_unshift( $links, $settings_link );
		}
		return $links;
	}

	/**
	 * Add some style to the plugin with a YouTube icon at the top of the page.
	 */
	public function dashboard_style(){
		global $wp_version;

		if ( 1 !== version_compare( '3.8', $wp_version ) ) {
			?><style>#menu-posts-jf_yfvp_youtube .menu-icon-post div.wp-menu-image:before { content: '\f126'; }</style><?php
		}
	}

	/**
	 * Add the sub-menu item under the Settings top-level menu.
	 */
	public function add_settings(){
		add_options_page( __('YouTube Favorites', 'youtube-favorite-video-posts' ), __('YouTube Favorites', 'youtube-favorite-video-posts'), 'manage_options', 'youtube-favorite-video-posts-settings', array( $this, 'view_settings' ) );
	}

	/**
	 * Display the main settings view for Youtube Favorite Video Posts.
	 */
	public function view_settings(){
		?>
		<div class="wrap">
			<h2><?php _e( 'YouTube Favorite Video Posts', 'youtube-favorite-video-posts' ); ?></h2>
			<h3><?php _e( 'Overview', 'youtube-favorite-video-posts' ); ?>:</h3>
			<p style="margin-left:12px; max-width:640px;"><?php _e( 'The settings below will help determine where to check for your favorite YouTube videos, how often to look for them, and how they should be stored once new items are found.', 'youtube-favorite-video-posts' ); ?></p>
			<p style="margin-left:12px; max-width:640px;"><?php _e( 'The most important part of this process will be to determine the RSS feed for your favorite YouTube videos. To do this, your username <strong>must</strong> be filled out below. This can usually be found in the upper right hand corner of <a href="http://www.youtube.com">YouTube.com</a>.', 'youtube-favorite-video-posts' ); ?></p>
			<ol style="margin-left:36px;">
				<li><?php _e( 'Username must be filled in below. Email address will not work.', 'youtube-favorite-video-posts' ); ?></li>
				<li><?php _e( 'The embed width and height settings will be applied to the iframe in your post content.', 'youtube-favorite-video-posts' ); ?></li>
				<li><?php _e( 'If you would like to change the content or title before the new content is saved, you may be interested in the <a href="http://jeremyfelt.com/wordpress/2012/05/12/filters-in-youtube-favorite-video-posts/">available filters</a>.', 'youtube-favorite-video-posts' ); ?></li>
			</ol>
			<form method="POST" action="options.php">
				<?php
				settings_fields( 'jf_yfvp_options' );
				do_settings_sections( 'jf_yfvp' );
				?>
				<p class="submit"><input type="submit" class="button-primary" value="<?php _e( 'Save Changes', 'youtube-favorite-video-posts' ); ?>" /></p>
			</form>
		</div>
		<?php
	}

	/**
	 * Register the settings, sections, and fields that we want made available for
	 * the plugin.
	 */
	public function register_settings(){
		register_setting( 'jf_yfvp_options', 'jf_yfvp_options', array( $this, 'validate_options' ) );

		add_settings_section( 'jf_yfvp_section_main',      '', array( $this, 'main_section_text'      ), 'jf_yfvp' );
		add_settings_section( 'jf_yfvp_section_post_type', '', array( $this, 'post_type_section_text' ), 'jf_yfvp' );
		add_settings_section( 'jf_yfvp_section_interval',  '', array( $this, 'interval_section_text'  ), 'jf_yfvp' );

		add_settings_field( 'jf_yfvp_youtube_rss_feed', 'YouTube Username:',     array( $this, 'youtube_user_text'        ), 'jf_yfvp', 'jf_yfvp_section_main' );
		add_settings_field( 'jf_yfvp_embed_width',      'Default Embed Width:',  array( $this, 'embed_width_text'         ), 'jf_yfvp', 'jf_yfvp_section_main' );
		add_settings_field( 'jf_yfvp_embed_height',     'Default Embed Height:', array( $this, 'embed_height_text'        ), 'jf_yfvp', 'jf_yfvp_section_main' );
		add_settings_field( 'jf_yfvp_max_fetch_items',  'Max Items To Fetch:',   array( $this, 'max_fetch_items_text'     ), 'jf_yfvp', 'jf_yfvp_section_main' );

		add_settings_field( 'jf_yfvp_post_type',        'Post Type:',            array( $this, 'post_type_selection_text' ), 'jf_yfvp', 'jf_yfvp_section_post_type' );
		add_settings_field( 'jf_yfvp_post_status',      __( 'Default Post Status:', 'youtube-favorite-video-posts' ) , array( $this, 'post_status_selection_text' ), 'jf_yfvp', 'jf_yfvp_section_post_type' );

		add_settings_field( 'jf_yfvp_fetch_interval',   'Feed Fetch Interval: ', array( $this, 'fetch_interval_selection_text' ), 'jf_yfvp', 'jf_yfvp_section_interval' );
	}

	/**
	 * Always seems weird to have to include this.
	 */
	public function main_section_text() { }

	/**
	 * Describe the selection of a post type for the plugin to use.
	 */
	public function post_type_section_text() {
		?>
		<h3>Custom Or Default Post Type</h3>
		<p style="margin-left:12px; max-width: 640px;"><?php _e( 'A new custom post type that adds an \'youtube\' slug to new items has been added and selected by default. You can change this to any other available post type if you would like.', 'youtube-favorite-video-posts' ); ?></p>
		<?php
	}

	/**
	 * Describe the selection of the WP Cron interval we'll use.
	 */
	public function interval_section_text() {

		$next_scheduled_time = wp_next_scheduled( 'jf_yfvp_process_feed' );

		if ( $next_scheduled_time ) {
			$next_scheduled_time = $next_scheduled_time  + ( get_option( 'gmt_offset' ) * 3600 );
			$user_current_time = time() + ( get_option( 'gmt_offset' ) * 3600 );
			$time_till_cron = human_time_diff( $user_current_time, $next_scheduled_time );
			$next_cron_date = date( 'g:i:sa', $next_scheduled_time );
			?>
			<h3>RSS Fetch Frequency</h3>
			<p style="margin-left:12px; max-width: 630px;"><?php _e( 'This plugin currently depends on WP Cron operating fully as expected. In most cases, you should be able to select one of the intervals below and things will work. If not, please let <a href="http://www.jeremyfelt.com">me</a> know. By default, we check for new items on an hourly basis.', 'youtube-favorite-video-posts' ); ?></p>
			<p style="margin-left:12px; max-width: 630px;"><?php printf( __( 'Your Youtube favorites feed is scheduled to be loaded next in %1$s, at %2$s.', 'youtube-favorite-video-posts' ), $time_till_cron, $next_cron_date ); ?></p>
			<?php
		} else {
			?>
			<h3>RSS Fetch Frequency</h3>
			<p style="margin-left:12px; max-width: 630px;"><?php _e( 'An interval has not yet been saved. Please select the frequency with which you would like this plugin to check your favorite video feed. The default of Once Hourly will be used if you do not change the interval before saving.', 'youtube-favorite-video-posts' ); ?></p>
			<?php
		}
	}

	/**
	 * Provide an input for the embed width.
	 */
	public function embed_width_text() {
		$jf_yfvp_options = get_option( 'jf_yfvp_options', array() );

		if ( ! isset( $jf_yfvp_options['embed_width'] ) ) {
			$jf_yfvp_options['embed_width'] = 330;
		}
		?>
		<input style="width: 100px;" type="text" id="jf_yfvp_embed_width" name="jf_yfvp_options[embed_width]" value="<?php echo esc_attr( $jf_yfvp_options['embed_width'] ); ?>" />
		<?php
	}

	/**
	 * Provide in input for the embed height.
	 */
	public function embed_height_text() {
		$jf_yfvp_options = get_option( 'jf_yfvp_options', array() );

		if ( ! isset( $jf_yfvp_options['embed_height'] ) ) {
			$jf_yfvp_options['embed_height'] = 270;
		}
		?>
		<input style="width: 100px;" type="text" id="jf_yfvp_embed_height" name="jf_yfvp_options[embed_height]" value="<?php echo esc_attr( $jf_yfvp_options['embed_height'] ); ?>" />
		<?php
	}

	/**
	 * Provide an input for the YouTube username.
	 */
	public function youtube_user_text() {
		$jf_yfvp_options = get_option( 'jf_yfvp_options', array() );

		// If options have been saved before, but no name specified, toss up a warning.
		if ( ! empty( $jf_yfvp_options ) && empty( $jf_yfvp_options['youtube_rss_feed'] ) ) {
			?>
			<div class="error" style="width: 615px;padding: 10px;"><?php _e( 'It looks like a Youtube username has not yet been entered, even though other options have been saved. Please note that we are unable to fetch your favorite videos until a username is provided.', 'youtube-favorite-video-posts' ); ?></div>
			<?php
		}

		if ( ! isset( $jf_yfvp_options['youtube_rss_feed'] ) ) {
			$jf_yfvp_options['youtube_rss_feed'] = '';
		}
		?>
		<input style="width: 200px;" type="text" id="jf_yfvp_youtube_rss_feed" name="jf_yfvp_options[youtube_rss_feed]" value="<?php echo esc_attr( $jf_yfvp_options['youtube_rss_feed'] ); ?>" />
		<?php
	}

	/**
	 * Provide an input for the selection of post types.
	 */
	public function post_type_selection_text() {
		$jf_yfvp_options = get_option( 'jf_yfvp_options', array() );

		if ( ! isset( $jf_yfvp_options['post_type'] ) ) {
			$jf_yfvp_options['post_type'] = 'jf_yfvp_youtube';
		}

		$post_types = array_merge( get_post_types( array( '_builtin' => false ) ), array( 'post', 'link' ) );

		echo '<select id="jf_yfvp_post_type" name="jf_yfvp_options[post_type]">';

		foreach( $post_types as $pt ){
			echo '<option value="' . esc_attr( $pt ) . '" ' . selected( $jf_yfvp_options['post_type'], $pt, false ) . '>' . esc_html( $pt ) . '</option>';
		}

		echo '</select>';
	}

	/**
	 * Provide an input for the selection of post status.
	 */
	public function post_status_selection_text() {
		$jf_yfvp_options = get_option( 'jf_yfvp_options', array() );

		if ( ! isset( $jf_yfvp_options['post_status'] ) ) {
			$jf_yfvp_options['post_status'] = 'publish';
		}

		$post_statii = array( 'draft', 'publish', 'private' );

		echo '<select id="jf_yfvp_post_status" name="jf_yfvp_options[post_status]">';

		foreach( $post_statii as $ps ) {
			echo '<option value="' . esc_attr( $ps ) . '" ' . selected( $jf_yfvp_options['post_status'], $ps, false ) . '>' . esc_html( $ps ) . '</option>';
		}

		echo '</select>';
	}

	/**
	 * Provide an input to select the WP Cron interval to schedule the hook with.
	 */
	public function fetch_interval_selection_text() {
		$intervals = wp_get_schedules();

		$jf_yfvp_options = get_option( 'jf_yfvp_options', array() );

		if ( ! isset( $jf_yfvp_options['fetch_interval'] ) ) {
			$jf_yfvp_options['fetch_interval'] = 'hourly';
		}

		echo '<select id="jf_yfvp_fetch_interval" name="jf_yfvp_options[fetch_interval]">';

		foreach( $intervals as $i => $v ){
			echo '<option value="' . esc_attr( $i ) . '" ' . selected( $jf_yfvp_options['fetch_interval'], $i, false ) . '>' . esc_html( $v['display'] ) . '</option>';
		}

		echo '</select>';
	}

	/**
	 * Provide an input for the max number of items to fetch.
	 */
	public function max_fetch_items_text() {
		$jf_yfvp_options = get_option( 'jf_yfvp_options', array() );

		if ( ! isset( $jf_yfvp_options['max_fetch_items'] ) ) {
			$jf_yfvp_options['max_fetch_items'] = 5;
		}
		?>
		<input type="text" id="jf_yfvp_max_fetch_items" name="jf_yfvp_options[max_fetch_items]" value="<?php echo esc_attr( $jf_yfvp_options['max_fetch_items'] ); ?>" />
		<?php
	}

	/**
	 * Validate the options being saved for the plugin.
	 *
	 * @param array $input New values that the user is attempting to save.
	 *
	 * @return array Validated values that we pass on.
	 */
	public function validate_options( $input ) {

		$valid_post_status_options = array( 'draft', 'publish', 'private' );
		$valid_fetch_interval_options = wp_get_schedules();

		$valid_post_type_options = array_merge( get_post_types( array( '_builtin' => false ) ), array( 'post' ) );

		if( ! in_array( $input['post_status'], $valid_post_status_options ) ) {
			$input['post_status'] = 'publish';
		}

		if( ! in_array( $input['post_type'], $valid_post_type_options ) ) {
			$input['post_type'] = 'jf_yfvp_youtube';
		}

		if( ! array_key_exists( $input['fetch_interval'], $valid_fetch_interval_options ) ) {
			$input['fetch_interval'] = 'hourly';
		}

		/**
		 * It is possible the user just switched back to using our custom post type,
		 * so we should flush the rewrite rules.
		 */
		if ( 'jf_yfvp_youtube' === $input['post_type'] ) {
			flush_rewrite_rules( false );
		}

		// This seems to be the only place we can reset the scheduled Cron if the frequency is changed.
		wp_clear_scheduled_hook( 'jf_yfvp_process_feed' );
		wp_schedule_event( ( time() + 30 ) , $input['fetch_interval'], 'jf_yfvp_process_feed' );

		$input['max_fetch_items'] = absint( $input['max_fetch_items'] );
		$input['embed_width'] = absint( $input['embed_width'] );
		$input['embed_height'] = absint( $input['embed_height'] );

		return $input;
	}

	/**
	 * Register our custom post type–jf_yfvp_youtube–for possible use with the plugin.
	 */
	public function create_content_type() {
		$labels = array(
			'name' => __( 'YouTube', 'youtube-favorite-video-posts' ),
			'singular_name' => __( 'YouTube Favorite', 'youtube-favorite-video-posts' ),
			'all_items' => __( 'All YouTube Favorites', 'youtube-favorite-video-posts' ),
			'add_new_item' => __( 'Add YouTube Favorite', 'youtube-favorite-video-posts' ),
			'edit_item' => __( 'Edit YouTube Favorite', 'youtube-favorite-video-posts' ),
			'new_item' => __( 'New YouTube Favorite', 'youtube-favorite-video-posts' ),
			'view_item' => __( 'View YouTube Favorite', 'youtube-favorite-video-posts' ),
			'search_items' => __( 'Search YouTube Favorites', 'youtube-favorite-video-posts' ),
			'not_found' => __( 'No YouTube Favorites found', 'youtube-favorite-video-posts' ),
			'not_found_in_trash' => __( 'No YouTube Favorites found in trash', 'youtube-favorite-video-posts' ),
		);

		$args = array(
			'labels' => $labels,
			'description' => __( 'YouTube posts created by the YouTube Favorite Video Posts plugin.', 'youtube-favorite-video-posts' ),
			'public' => true,
			'menu_position' => 5,
			'hierarchical' => false,
			'supports' => array (
				'title',
				'editor',
				'author',
				'custom-fields',
				'comments',
				'revisions',
			),
			'has_archive' => true,
			'rewrite' => array(
				'slug' => 'youtube',
				'with_front' => false
			),
		);

		register_post_type( 'jf_yfvp_youtube', $args );
	}

	/**
	 * The default SimplePie cache lifetime is 12 hours. We really do want to update more
	 * frequently, so we'll make it 30 seconds during our update.
	 *
	 * @return int Number of seconds for the SimplePie cache to last.
	 */
	public function modify_simplepie_cache_lifetime() {
		return 30;
	}

	/**
	 * Grab the configured YouTube favorites RSS feed and create new posts based on that.
	 *
	 * @return mixed Only returns if leaving the function.
	 */
	public function process_feed() {
		$youtube_options = get_option( 'jf_yfvp_options', array() );

		// No username, no feed. No feed, no work.
		if ( empty( $youtube_options['youtube_rss_feed'] ) ) {
			return;
		}

		// The feed URL we'll be grabbing.
		$youtube_feed_url = 'http://gdata.youtube.com/feeds/base/users/' . esc_attr( $youtube_options['youtube_rss_feed'] ) . '/favorites?alt=rss';

		if ( isset( $youtube_options['post_type'] ) ) {
			$post_type = $youtube_options['post_type'];
		} else {
			$post_type = 'jf_yfvp_youtube';
		}

		if ( isset( $youtube_options['post_status'] ) ) {
			$post_status = $youtube_options['post_status'];
		} else {
			$post_status = 'publish';
		}

		if ( isset( $youtube_options['max_fetch_items'] ) ) {
			$max_fetch_items = absint( $youtube_options['max_fetch_items'] );
		} else {
			$max_fetch_items = 5;
		}

		// Now fetch with the WordPress SimplePie function.
		add_filter( 'wp_feed_cache_transient_lifetime', array( $this, 'modify_simplepie_cache_lifetime' ) );
		$youtube_feed = fetch_feed( $youtube_feed_url );
		remove_filter( 'wp_feed_cache_transient_lifetime', array( $this, 'modify_simplepie_cache_lifetime' ) );

		if ( ! is_wp_error( $youtube_feed ) ) {
			$max_items = $youtube_feed->get_item_quantity( $max_fetch_items );
			$youtube_items = $youtube_feed->get_items( 0, $max_items );
			foreach( $youtube_items as $item ) {
				// Hash the guid element from the RSS feed to determine uniqueness, since yeah... guid.
				$video_guid = md5( $item->get_id() );

				$video_token = substr( $item->get_id(), 43 );
				$old_item_hash = md5( $video_token );

				$video_embed_code = '<iframe width=\"' . absint( $youtube_options['embed_width'] ) .
					'\" height=\"' . absint( $youtube_options['embed_height'] ) .
					'\" src=\"http://www.youtube.com/embed/' .
					esc_attr( $video_token ) . '\" frameborder=\"0\" allowfullscreen></iframe>';

				// Allow other plugins or themes to alter or replace the post content before storing.
				$video_embed_code = apply_filters( 'yfvp_new_video_embed_code', $video_embed_code, $video_token );

				/**
				 * Allow other plugins or themes to alter or replace the post title before storing.
				 * Also, we're disabling the kses filters below, so we need to clean up the title as
				 * YouTube allows " and the like.
				 */
				$original_item_title = $item->get_title();
				$item_title = esc_html( apply_filters( 'yfvp_new_video_item_title', $original_item_title ) );

				/**
				 * Our previous hash management was ugly, so now we need to check for the
				 * existence of the old hash before checking the existence of the new hash.
				 * If we do happen to find an old hash, we'll update it immediately with
				 * the newer hash so that we can get rid of this code in the next release.
				 */
				$existing_old_item = get_posts( array(
				                                'post_type' => $post_type,
				                                'numberposts' => 1,
				                                'post_status' => array( 'publish', 'draft', 'private' ),
				                                'meta_query' => array(
					                                array(
						                                'key' => 'jf_yfvp_hash',
						                                'value' => $old_item_hash,
					                                ),
				                                ),
				                                ));

				if ( ! empty( $existing_old_item ) ) {
					update_post_meta( $existing_old_item[0]->ID, 'jf_yfvp_hash', $video_guid );
					$existing_items = $existing_old_item;
				} else {
					$existing_items = get_posts( array(
				                                  'post_type' => $post_type,
				                                  'numberposts' => 1,
				                                  'post_status' => array( 'publish', 'draft', 'private' ),
				                                  'meta_query' => array(
					                                  array(
						                                  'key' => 'jf_yfvp_hash',
						                                  'value' => $video_guid,
					                                  ),
				                                  ),
				                                 ));
				}

				// If we come back empty on our meta query, we should be ok to insert the video as normal.
				if ( empty ( $existing_items ) ) {

					$youtube_post = array(
						'post_title'   => $item_title,
						'post_content' => $video_embed_code,
						'post_author'  => 1,
						'post_status'  => $post_status,
						'post_type'    => $post_type,
						'filter'       => true,
					);

					kses_remove_filters();
					$item_post_id = wp_insert_post( $youtube_post );
					kses_init_filters();
					add_post_meta( $item_post_id, 'jf_yfvp_hash', $video_guid, true );
					add_post_meta( $item_post_id, 'jf_yfvp_video_token', $video_token, true );
					add_post_meta( $item_post_id, 'jf_yfvp_original_title', sanitize_title( $original_item_title ), true );
				} else {
					$original_meta_title = get_post_meta( $existing_items[0]->ID, 'jf_yfvp_original_title', true );
					/**
					 * If the current item's original title does not match the matched post's original title,
					 * update it with the current filtered version of the new item title. *whew*
					 */
					if ( $original_item_title !== $original_meta_title ) {
						wp_update_post( array( 'ID' => $existing_items[0]->ID, 'post_title' => $item_title ) );
						update_post_meta( $existing_items[0]->ID, 'jf_yfvp_original_title', $original_item_title );
					}
				}
			}
		}
	}
}
new Youtube_Favorite_Video_Posts_Foghlaim();