<?php
/**
 * Handle the likes post type.
 *
 * This should be a plugin of its own.
 *
 * @package jeremyfelt
 */

namespace JeremyFelt\Base\Likes;

add_action( 'init', __NAMESPACE__ . '\register_post_type', 10 );
add_action( 'save_post', __NAMESPACE__ . '\save_post', 10, 2 );
add_filter( 'wp_insert_post_data', __NAMESPACE__ . '\set_default_post_data', 10 );
add_filter( 'webmention_links', __NAMESPACE__ . '\filter_webmention_links', 10, 2 );

/**
 * Register the post type used to track likes.
 */
function register_post_type() {
	\register_post_type(
		'like',
		array(
			'labels'               => array(
				'name'          => 'Likes',
				'singular_name' => 'Like',
			),
			'public'               => true,
			'menu_position'        => 6,
			'menu_icon'            => 'dashicons-star-filled',
			'supports'             => array(
				'comments',
				'webmentions',
			),
			'register_meta_box_cb' => __NAMESPACE__ . '\register_meta_boxes',
			'has_archive'          => true,
			'rewrite'              => array(
				'slug' => 'liked',
			),
		)
	);
}

/**
 * Register the meta boxes used to store data for likes.
 *
 * @param WP_Post $post The current like being edited.
 */
function register_meta_boxes( $post ) {
	add_meta_box( 'like-data-primary', 'Like data', __NAMESPACE__ . '\display_meta_box', $post->post_type, 'normal', 'high' );
}

/**
 * Display the meta box used to capture like data.
 *
 * @param WP_Post $post The current like being edited.
 */
function display_meta_box( $post ) {
	$url   = get_post_meta( $post->ID, 'mf2_like-of', true );
	$url   = is_array( $url ) ? array_pop( $url ) : $url;
	$title = get_post_meta( $post->ID, 'like_title', true );
	$notes = get_post_meta( $post->ID, 'like_notes', true );

	wp_nonce_field( 'save-like-data', 'like_data_nonce' );
	?>
	<h3>URL</h3>
	<input class="widefat" type="text" id="like-url" name="like_url" value="<?php echo esc_url( $url ); ?>" />

	<h3>Title</h3>
	<input class="widefat" type="text" id="like-title" name="like_title" value="
	<?php
	if ( '' !== $title ) {
		echo esc_attr( $title ); }
	?>
	" />

	<h3>Notes</h3>
	<textarea class="widefat" rows="10" id="like-notes" name="like_notes"><?php echo esc_textarea( $notes ); ?></textarea>
	<?php
}

/**
 * Save meta data attached to a like.
 *
 * @param int     $post_id The ID of the current like.
 * @param WP_Post $post    The post object representing the current like.
 */
function save_post( $post_id, $post ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( 'auto-draft' === $post->post_status ) {
		return;
	}

	if ( ! isset( $_POST['like_data_nonce'] ) || ! wp_verify_nonce( $_POST['like_data_nonce'], 'save-like-data' ) ) {
		return;
	}

	if ( isset( $_POST['like_url'] ) && '' !== $_POST['like_url'] ) {
		update_post_meta( $post_id, 'mf2_like-of', esc_url_raw( $_POST['like_url'] ) );
	} elseif ( isset( $_POST['like_url'] ) && '' === $_POST['like_url'] ) {
		delete_post_meta( $post_id, 'mf2_like-of' );
	}

	if ( isset( $_POST['like_title'] ) && '' !== $_POST['like_title'] ) {
		update_post_meta( $post_id, 'like_title', sanitize_text_field( $_POST['like_title'] ) );
	} elseif ( isset( $_POST['like_title'] ) && '' === $_POST['like_title'] ) {
		delete_post_meta( $post_id, 'like_title' );
	}

	if ( isset( $_POST['like_notes'] ) && '' !== $_POST['like_notes'] ) {
		update_post_meta( $post_id, 'like_notes', wp_kses_post( $_POST['like_notes'] ) );
	} elseif ( isset( $_POST['like_notes'] ) && '' === $_POST['like_notes'] ) {
		delete_post_meta( $post_id, 'like_notes' );
	}
}

/**
 * Modify the defaults stored with a new like.
 *
 * @param array $post Current post data to store for the like.
 * @return array $post Modified post data to store.
 */
function set_default_post_data( $post ) {
	if ( 'like' === $post['post_type'] && '' === $post['post_name'] && 'Auto Draft' === $post['post_title'] ) {
		$post['post_title'] = 'Like';
		$post['post_name']  = gmdate( 'YmdHis' );
	}

	if ( 'like' === $post['post_type'] && isset( $_POST['like_title'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$post['post_title'] = sanitize_text_field( $_POST['like_title'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$post['post_name']  = sanitize_key( $_POST['like_title'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	}

	return $post;
}

/**
 * Add the URL associated with this like to the list of URLs to be
 * pinged with a webmention.
 *
 * @param array $urls    List of URLs to ping.
 * @param int   $post_id The current post ID.
 * @return array $urls Modified list of URLs to ping.
 */
function filter_webmention_links( $urls, $post_id ) {
	$post = get_post( $post_id );

	if ( 'like' === $post->post_type ) {
		$url = get_post_meta( $post_id, 'mf2_like-of', true );
		$url = is_array( $url ) ? array_pop( $url ) : $url;

		if ( '' !== $url ) {
			$urls[] = $url;
		}
	}

	return $urls;
}
