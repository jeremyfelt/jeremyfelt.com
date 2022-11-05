<?php

namespace JeremyFelt\Plugins\ShareOnMastodon;

add_filter( 'share_on_mastodon_status', __NAMESPACE__ . '\filter_status_text', 10, 2 );

/**
 * Filter a toot so that the note content is used rather than the title.
 *
 * @param string   $status The status text.
 * @param \WP_Post $post   The post object.
 * @return string The modified status text.
 */
function filter_status_text( $status, $post ) {
	$status = apply_filters( 'the_content', $post->post_content );

	// Do what the plugin does to the title, but to the rendered content.
	$status = wp_strip_all_tags(
		html_entity_decode( $status, ENT_QUOTES | ENT_HTML5, get_bloginfo( 'charset' ) ) // Avoid double-encoded HTML entities.
	);

	return $status;
}
