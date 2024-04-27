<?php
/**
 * Filter things in WordPress that output additional markup
 * in the document HEAD.
 *
 * @package jeremyfelt
 */

namespace JeremyFelt\WP\Head;

add_action( 'plugins_loaded', __NAMESPACE__ . '\remove_default_actions' );
add_filter( 'site_icon_meta_tags', __NAMESPACE__ . '\filter_site_icon_meta_tags' );

/**
 * Remove other default actions that output unnecessary elements or do
 * unnecessary things.
 */
function remove_default_actions(): void {

	// Remove EditURI link for XML-RPC hinting.
	remove_action( 'wp_head', 'rsd_link' );

	// Remove the generator tag.
	remove_action( 'wp_head', 'wp_generator' );

	// I can't ever imagine wanting a shortlink in today's internet, but that may
	// just be me.
	remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 );
	remove_action( 'template_redirect', 'wp_shortlink_header', 11 );

	// No need to advertise the REST API, it's still there.
	remove_action( 'wp_head', 'rest_output_link_wp_head' );

	// Remove embed information on single views.
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
}

/**
 * Filter out unnecessary meta tags for the site icon.
 *
 * @param string[] $meta_tags Array of meta tags.
 * @return string[] Modified list of meta tags.
 */
function filter_site_icon_meta_tags( array $meta_tags ): array {

	$meta_tags = array_filter(
		$meta_tags,
		function ( $tag ) {
			return ! mb_strpos( $tag, 'msapplication-TileImage' );
		}
	);

	return $meta_tags;
}
