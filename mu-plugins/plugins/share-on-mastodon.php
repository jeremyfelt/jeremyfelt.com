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
	$status = convert_to_markdown( $status );

	// Do what the plugin does to the title, but to the rendered content.
	$status = wp_strip_all_tags(
		html_entity_decode( $status, ENT_QUOTES | ENT_HTML5, get_bloginfo( 'charset' ) ) // Avoid double-encoded HTML entities.
	);

	return $status;
}

/**
 * Convert a basic set of HTML tags into their markdown equivalent.
 *
 * @param string $html The post content.
 * @return string Modified post content.
 */
function convert_to_markdown( string $html ) : string {
	$document = new \DOMDocument();
	@$document->loadHTML( $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

	$anchors = $document->getElementsByTagName( 'a' );
	$count   = $anchors->length - 1;

	for ( $count; $count > -1; $count-- ) {
		$node = $anchors->item( $count );

		$link_text = $node->nodeValue; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$link_href = $node->getAttribute( 'href' );

		if ( '' !== $link_href ) {
			$text_node = $document->createTextNode( '[' . $link_text . '](' . $link_href . ')' );

			$node->parentNode->replaceChild( $text_node, $node ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		}
	}

	return $document->saveHTML();
}
