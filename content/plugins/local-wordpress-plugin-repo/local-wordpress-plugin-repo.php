<?php
/*
Plugin Name: Local WordPress Plugin Repo
Plugin URI: http://jeremyfelt.com/wordpress/plugins/local-wordpress-plugin-repo/
Description: Regularly checks WordPress repository information for your plugins and stores in WordPress
Version: 0.9
Author: Jeremy Felt
Author URI: http://jeremyfelt.com
License: GPL2
*/

/*  Copyright 2012 Jeremy Felt (email: jeremy.felt@gmail.com)

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

class Local_WordPress_Plugin_Repo_Foghlaim {

	/**
	 * Post type slug to use for storing plugin information from the repo
	 *
	 * @access public
	 * @var string
	 */
	var $post_type = 'fog_lpr_plugin';

	public function __construct() {
		add_action( 'init', array( $this, 'create_content_type' ) );
		add_action( 'after_setup_theme', array( $this, 'event_scheduler' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_style' ) );
		add_action( 'fog_lpr_plugin_api_check', array( $this, 'check_plugin_api' ) );
		add_action( 'save_post', array( $this, 'save_meta_data' ), 10, 2 );
		add_action( 'save_post', array( $this, 'get_plugin_stats' ), 20, 2 );
		add_action( 'save_post', array( $this, 'get_plugin_support_feed' ), 30, 2 );
	}

	public function create_content_type() {
		register_post_type( $this->post_type,
			array(
			     'labels' => array(
				     'name' => 'WordPress Plugins',
				     'singular_name' => 'My Plugin',
				     'all_items' => 'All of My Plugins',
				     'add_new_item' => 'Add a Plugin',
				     'edit_item' => 'Edit a Plugin',
				     'view_item' => 'View My Plugins',
				     'new_item' => 'New Plugin',
				     'search_items' => 'Search My Plugins',
				     'not_found' => 'No Plugins found',
				     'not_found_in_trash' => 'No Plugins found in trash',
			     ),
			     'description' => 'My WordPress plugins in the plugin repository.',
			     'public' => true,
			     'hierarchical' => false,
			     'supports' => array(
				     'title',
				     'editor',
				     'author',
				     'thumbnail',
			     ),
			     'register_meta_box_cb' => array( $this, 'register_meta_boxes' ),
			     'has_archive' => true,
			     'rewrite' => array(
				     'slug' => 'wordpress/plugins',
				     'with_front' => false,
			     ),
			)
		);
	}

	public function event_scheduler() {
		if ( ! wp_next_scheduled( 'fog_lpr_plugin_api_check' ) )
			wp_schedule_event( time() + 600, 'twicedaily', 'fog_lpr_plugin_api_check' );
	}

	public function enqueue_admin_style() {
		if ( is_admin() )
			wp_enqueue_style( 'fog_lpr_admin', plugins_url( 'css/admin-style.css', __FILE__ ) );
	}

	function check_plugin_api() {
		$plugin_posts = new WP_Query( array(
		                                   'posts_per_page' => 25,
		                                   'post_type' => $this->post_type,
		                                   'no_found_rows' => true,
		                              ));

		if ( $plugin_posts->have_posts() ) {
			while ( $plugin_posts->have_posts() ) {
				$plugin_posts->the_post();
				$this->get_plugin_stats( get_the_ID(), 0, true );
				$this->get_plugin_support_feed( get_the_ID(), 0, true );
			}
		}
		wp_reset_postdata();
	}

	function register_meta_boxes( $post ) {
		add_meta_box( 'fog_lpr_plugin_slug', 'Plugin Slug', array( $this, 'display_plugin_slug_meta_box' ), $post->post_type, 'side', 'default' );
		add_meta_box( 'fog_lpr_donate_code', 'Plugin Donation Code', array( $this, 'display_plugin_donation_meta_box' ), $post->post_type, 'normal', 'default' );
		add_meta_box( 'fog_lpr_support_feed', 'Plugin Support Feed', array( $this, 'display_plugin_support_feed_meta_box' ), $post->post_type, 'normal', 'default' );
	}

	function display_plugin_slug_meta_box( $post ) {
		$current_slug = get_post_meta( $post->ID, '_fog_lpr_plugin_slug', true);
		$current_downloads = get_post_meta( $post->ID, '_fog_lpr_plugin_downloads', true );
		$current_version = get_post_meta( $post->ID, '_fog_lpr_plugin_version', true );

		wp_nonce_field( 'save-plugin-meta-data', '_fog_lpr_plugin_nonce' );
		?>
	<label for="plugin-slug-input">Plugin Slug:</label>
	<input id="plugin-slug-input" name="plugin_slug_input" type="text" style="width: 200px;" placeholder="plugin-slug-data" value="<?php echo esc_attr( $current_slug ); ?>" />
	<p class="help">Enter the slug used in the official WordPress plugin repository for your plugin.</p>
	<?php if ( ! empty( $current_slug ) ) : ?>
		<h4>Current Information:</h4>
		<ul style="margin-left: 15px;">
			<li>Repository URL: <a href="http://wordpress.org/extend/plugins/<?php echo esc_attr( $current_slug ); ?>">http://wordpress.org...plugins/<?php echo esc_attr( $current_slug ); ?></a></li>
			<li>Support Forum: <a href="http://wordpress.org/support/plugin/<?php echo esc_attr( $current_slug ); ?>">http://wordpress.org...plugin/<?php echo esc_attr( $current_slug ); ?></a></li>
			<li>Downloads: <?php echo esc_html( $current_downloads ); ?></li>
			<li>Version: <?php echo esc_html( $current_version ); ?></li>
		</ul>
		<?php endif;
	}

	function display_plugin_donation_meta_box( $post ) {
		$current_donation_display = get_post_meta( $post->ID, '_fog_lpr_plugin_donate_code', true );
		?>
	<label for="plugin-donate-code">HTML Donate Code:</label><br>
	<textarea id="plugin-donate-code" name="plugin_donate_code" style="width: 450px; height: 200px;"><?php echo esc_html( $current_donation_display ); ?></textarea>
	<p class="help">Enter the code that can be used to display the donation code for you plugin.</p>
	<?php
	}

	function display_plugin_support_feed_meta_box( $post ) {
		$plugin_forum_data = get_post_meta( $post->ID, '_fog_lpr_support_feed_data', true );

		if ( ! $plugin_forum_data )
			return;

		$current_support_feed_html = '';
		foreach ( $plugin_forum_data as $data ) {
			$current_support_feed_html .= '
						<div class="fog-support-feed-item">
							<div class="fog-support-feed-item-date">' . date( 'm/d/y H:i', $data[ 'last_date' ] ) . '</div>
							<div class="fog-support-feed-item-title"><a href="' . esc_url( $data[ 'link' ] ) . '">' . esc_html( $data[ 'subject' ] ) . '</a></div>
							<div class="fog-support-feed-item-author">' . esc_html( $data[ 'last_author' ] ) . '</div>
							<div class="fog-support-feed-item-count">' . esc_html( $data[ 'count' ] ) . ' messages</div>
						</div>';
		}

		echo '<div class="fog-support-feed-items">' . $current_support_feed_html . '</div>';
	}

	function save_meta_data( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return NULL;

		if ( ! isset( $_POST['_fog_lpr_plugin_nonce'] ) || ! wp_verify_nonce( $_POST['_fog_lpr_plugin_nonce'], 'save-plugin-meta-data' ) )
			return NULL;

		if ( 'auto-draft' == $post->post_status )
			return NULL;

		global $allowedposttags;
		$fog_lpr_allowedposttags = $allowedposttags;
		$fog_lpr_allowedposttags['input'] = array(
			'type'   => true,
			'name'   => true,
			'value'  => true,
			'border' => true,
			'src'    => true,
			'alt'    => true
		);

		if ( isset( $_POST['plugin_slug_input'] ) && '' != $_POST['plugin_slug_input'] )
			update_post_meta( $post_id, '_fog_lpr_plugin_slug', sanitize_title( $_POST['plugin_slug_input'] ) );
		else
			delete_post_meta( $post_id, '_fog_lpr_plugin_slug' );

		if ( isset( $_POST['plugin_donate_code'] ) && '' != $_POST['plugin_donate_code'] )
			update_post_meta( $post_id, '_fog_lpr_plugin_donate_code', wp_kses( $_POST['plugin_donate_code'], $fog_lpr_allowedposttags ) );
		else
			delete_post_meta( $post_id, '_fog_lpr_plugin_donate_code' );
	}

	function get_plugin_stats( $post_id, $post = 0, $manual = false ) {

		if ( ! empty( $post ) && $this->post_type != $post->post_type && ! $manual )
			return;

		$current_slug = get_post_meta( $post_id, '_fog_lpr_plugin_slug', true );

		if ( ! $current_slug )
			return;

		$api_url = 'http://api.wordpress.org/plugins/info/1.0/' . sanitize_key( $current_slug ) . '.php';
		$plugin_api_data = wp_remote_request( $api_url );

		if ( '200' != wp_remote_retrieve_response_code( $plugin_api_data ) )
			return;

		$plugin_api_body = unserialize( wp_remote_retrieve_body( $plugin_api_data ) );

		if ( isset( $plugin_api_body->name ) )
			update_post_meta( $post_id, '_fog_lpr_plugin_name', sanitize_text_field( $plugin_api_body->name ) );

		if ( isset( $plugin_api_body->downloaded ) )
			update_post_meta( $post_id, '_fog_lpr_plugin_downloads', absint( $plugin_api_body->downloaded ) );

		if ( isset( $plugin_api_body->version ) && is_numeric( $plugin_api_body->version ) )
			update_post_meta( $post_id, '_fog_lpr_plugin_version', $plugin_api_body->version );

	}

	function modify_simplepie_cache_lifetime() {
		return 600;
	}

	function get_plugin_support_feed( $post_id, $post = 0, $manual = false ) {

		if ( ! empty( $post ) && $this->post_type != $post->post_type && ! $manual )
			return;

		$current_slug = get_post_meta( $post_id, '_fog_lpr_plugin_slug', true );

		if ( ! $current_slug )
			return;

		$plugin_name = get_post_meta( $post_id, '_fog_lpr_plugin_name', true );

		$support_url = 'http://wordpress.org/support/rss/plugin/' . $current_slug;

		/*  Now fetch with the WordPress SimplePie function. */
		add_filter( 'wp_feed_cache_transient_lifetime', array( $this, 'modify_simplepie_cache_lifetime' ) );
		$support_feed = fetch_feed( $support_url );
		remove_filter( 'wp_feed_cache_transient_lifetime', array( $this, 'modify_simplepie_cache_lifetime' ) );

		if ( ! is_wp_error( $support_feed ) ) {
			$max_items = $support_feed->get_item_quantity( 30 );
			$support_items = $support_feed->get_items( 0, $max_items );
			$plugin_forum_data = array();

			foreach ( $support_items as $item ) {

				$after_author = strpos( $item->get_title(), 'on &quot;[Plugin' );
				$author_name = substr( $item->get_title(), 0, $after_author );

				if ( ! empty( $plugin_name ) ) {
					$before_plugin_name = strpos( $item->get_title(), $plugin_name . '] ' );
					if ( ! $before_plugin_name )
						continue;
					$true_subject = substr( $item->get_title(), ( $before_plugin_name + strlen( $plugin_name ) + 2 ) );
					$true_subject = substr( $true_subject, 0, -6 );
				} else {
					$true_subject = $item->get_title();
				}

				if ( isset( $plugin_forum_data[ $true_subject ] ) )
					$plugin_forum_data[ $true_subject ]['count']++;
				else
					$plugin_forum_data[ $true_subject ] = array(
						'count' => 1,
						'last_author' => $author_name,
						'subject' => $true_subject,
						'last_date' => strtotime( $item->get_date() ),
						'link' => $item->get_link()
					);
			}

		}

		if ( ! empty( $plugin_forum_data ) )
			update_post_meta( $post_id, '_fog_lpr_support_feed_data', $plugin_forum_data );
	}
}
new Local_WordPress_Plugin_Repo_Foghlaim();