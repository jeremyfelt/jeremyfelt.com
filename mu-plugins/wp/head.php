<?php
/**
 * Filter things in WordPress that output additional markup
 * in the document HEAD.
 *
 * @package jeremyfelt
 */

namespace JeremyFelt\WP\Head;

add_action( 'plugins_loaded', __NAMESPACE__ . '\remove_default_actions' );

/**
 * Remove other default actions that output unnecessary elements or do
 * unnecessary things.
 */
function remove_default_actions() {

	// There is a very close to zero chance I will ever use Windows Live Writer.
	remove_action( 'wp_head', 'wlwmanifest_link' );

	// Remove EditURI link for XML-RPC hinting.
	remove_action( 'wp_head', 'rsd_link' );

	// Remove the generator tag.
	remove_action( 'wp_head', 'wp_generator' );

	// I can't ever imagine wanting a shortlink in today's internet, but that may
	// just be me.
	remove_action( 'wp_head', 'wp_shortlink_wp_head', 10, 0 );
	remove_action( 'template_redirect', 'wp_shortlink_header', 11, 0 );

	// No need to advertise the REST API, it's still there.
	remove_action( 'wp_head', 'rest_output_link_wp_head' );

	// Remove embed information on single views.
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
}
