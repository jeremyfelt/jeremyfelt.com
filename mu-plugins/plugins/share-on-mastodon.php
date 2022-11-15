<?php

namespace JeremyFelt\Plugins\ShareOnMastodon;

add_filter( 'share_on_mastodon_status', __NAMESPACE__ . '\filter_status_text', 10, 2 );

/**
 * Filter Mastodon toot args to include a reply to ID if it exists.
 *
 * @param array    $args The args sent with a new Mastodon post.
 * @param \WP_Post $post The post object.
 */
function filter_args( array $args, \WP_Post $post ) : array {
	if ( 'shortnote' !== $post->post_type ) {
		return $args;
	}

	$reply_to_url = get_post_meta( $post->ID, 'shortnotes_reply_to_url', true );

	if ( ! $reply_to_url ) {
		return $args;
	}

	$reply_host = wp_parse_url( $reply_to_url, PHP_URL_HOST );
	$mastodon   = get_user_meta( $post->post_author, 'mastodon', true );

	if ( ! $mastodon || $reply_host !== wp_parse_url( $mastodon, PHP_URL_HOST ) ) {
		return $args;
	}

	// https://mastodon.host/web/@user@users.host/{in_rely_to_id}
	$path = trim( wp_parse_url( $reply_to_url, PHP_URL_PATH ), '/' );
	$path = explode( '/', $path );

	if ( 3 !== count( $path ) || 'web' !== $path[0] || ! is_numeric( $path[2] ) ) {
		return $args;
	}

	$args['in_reply_to_id'] = $path[2];

	return $args;
}

/**
 * Filter a toot so that the note content is used rather than the title.
 *
 * @param string   $status The status text.
 * @param \WP_Post $post   The post object.
 * @return string The modified status text.
 */
function filter_status_text( $status, $post ) {
	$status = apply_filters( 'the_content', $post->post_content );
	$status = convert_anchors( $status );

	// Do what the plugin does to the title, but to the rendered content.
	$status = wp_strip_all_tags(
		html_entity_decode( $status, ENT_QUOTES | ENT_HTML5, get_bloginfo( 'charset' ) ) // Avoid double-encoded HTML entities.
	);

	add_filter(
		'share_on_mastodon_toot_args',
		function( $args ) use ( $post ) {
			return filter_args( $args, $post );
		}
	);

	return $status;
}

/**
 * Parse and move anchors to the end of post content.
 *
 * @param string $html The post content.
 * @return string Modified post content.
 */
function convert_anchors( string $html ) : string {
	// DomDocument may not parse "special" characters right, so convert anything not
	// known in ASCII to its HTML entity.
	// @see https://stackoverflow.com/questions/39148170/utf-8-with-php-domdocument-loadhtml/39148511#39148511
	$html = mb_convert_encoding( $html, 'HTML-ENTITIES', 'UTF-8' );

	$document = new \DOMDocument();
	@$document->loadHTML( $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

	$anchors = $document->getElementsByTagName( 'a' );
	$count   = $anchors->length - 1;

	// Track links in content.
	$link_hrefs = [];

	for ( $count; $count > -1; $count-- ) {
		$node = $anchors->item( $count );

		$link_text = $node->nodeValue; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$link_href = $node->getAttribute( 'href' );

		if ( '' !== $link_href ) {
			$text_node    = $document->createTextNode( $link_text );
			$link_hrefs[] = $link_href;

			$node->parentNode->replaceChild( $text_node, $node ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		}
	}

	return $document->saveHTML() . ' ' . implode( ' ', $link_hrefs );
}
