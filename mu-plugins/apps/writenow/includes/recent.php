<?php
/**
 * The Recent list: notes and likes, newest first.
 *
 * @package jeremyfelt
 */

namespace JeremyFelt\Apps\WriteNow\Recent;

use WP_Post;
use WP_REST_Request;
use WP_REST_Response;

use function JeremyFelt\Apps\WriteNow\REST\decode;
use function JeremyFelt\Apps\WriteNow\REST\get_like_post_type;
use function JeremyFelt\Apps\WriteNow\REST\get_note_post_type;
use function JeremyFelt\Apps\WriteNow\Likes\get_liked_url;
use function JeremyFelt\Apps\WriteNow\Notes\get_edit_link;
use function JeremyFelt\Apps\WriteNow\Notes\is_editable;

use const JeremyFelt\Apps\WriteNow\MARKDOWN_META_KEY;

/**
 * The longest text line in the Recent list, in characters.
 */
const TEXT_MAX_LENGTH = 140;

/**
 * List recent notes (drafts, scheduled and published) and published likes.
 *
 * @param WP_REST_Request $request The request.
 * @return WP_REST_Response The list.
 */
function get_recent( WP_REST_Request $request ): WP_REST_Response {
	$per_page = max( 1, min( 50, (int) $request->get_param( 'per_page' ) ) );
	$posts    = array_merge(
		query( get_note_post_type(), [ 'draft', 'future', 'publish' ], $per_page ),
		query( get_like_post_type(), [ 'publish' ], $per_page )
	);

	$items = [];

	foreach ( $posts as $post ) {
		if ( ! current_user_can( 'edit_post', $post->ID ) && 'publish' !== $post->post_status ) {
			continue;
		}

		$items[] = prepare_item( $post );
	}

	usort(
		$items,
		function ( $a, $b ) {
			$by_date = strcmp( $b['date'], $a['date'] );

			return 0 !== $by_date ? $by_date : $b['id'] <=> $a['id'];
		}
	);

	return new WP_REST_Response( array_slice( $items, 0, $per_page ) );
}

/**
 * Query the most recent posts of one type.
 *
 * @param string   $post_type The post type.
 * @param string[] $statuses  Post statuses to include.
 * @param int      $limit     The most posts to return.
 * @return WP_Post[] The posts, newest first.
 */
function query( string $post_type, array $statuses, int $limit ): array {
	if ( ! post_type_exists( $post_type ) ) {
		return [];
	}

	$posts = get_posts(
		[
			'post_type'        => $post_type,
			'post_status'      => $statuses,
			'posts_per_page'   => $limit,
			'orderby'          => [
				'date' => 'DESC',
				'ID'   => 'DESC',
			],
			'no_found_rows'    => true,
			'suppress_filters' => true,
		]
	);

	return array_filter(
		$posts,
		function ( $post ) {
			return $post instanceof WP_Post;
		}
	);
}

/**
 * Describe one post for the Recent list.
 *
 * @param WP_Post $post A note or a like.
 * @return array The item.
 */
function prepare_item( WP_Post $post ): array {
	$date = get_post_datetime( $post, 'date', 'local' );
	$date = $date ? $date->setTimezone( new \DateTimeZone( 'UTC' ) )->format( DATE_ATOM ) : '';

	if ( get_like_post_type() === $post->post_type ) {
		$url   = get_liked_url( $post );
		$title = decode( $post->post_title );

		return [
			'type'      => 'like',
			'id'        => $post->ID,
			'status'    => $post->post_status,
			'date'      => $date,
			'text'      => truncate( '' === $title ? $url : $title ),
			'url'       => $url,
			'link'      => (string) get_permalink( $post ),
			'edit_link' => get_edit_link( $post ),
		];
	}

	return [
		'type'      => 'note',
		'id'        => $post->ID,
		'status'    => $post->post_status,
		'date'      => $date,
		'text'      => get_note_text( $post ),
		'link'      => (string) get_permalink( $post ),
		'edit_link' => get_edit_link( $post ),
		'editable'  => is_editable( $post ),
	];
}

/**
 * Provide a plain-text line describing a note.
 *
 * Uses the note's content when it has text, and its title otherwise, which
 * covers notes that are only an image.
 *
 * @param WP_Post $post The note.
 * @return string Plain text, at most 140 characters.
 */
function get_note_text( WP_Post $post ): string {
	$text = '';

	foreach ( parse_blocks( $post->post_content ) as $block ) {
		$text .= ' ' . block_text( $block );
	}

	$text = to_plain_text( $text );

	if ( '' === $text && metadata_exists( 'post', $post->ID, MARKDOWN_META_KEY ) ) {
		$text = to_plain_text( (string) get_post_meta( $post->ID, MARKDOWN_META_KEY, true ) );
	}

	if ( '' === $text ) {
		$text = decode( $post->post_title );
	}

	return truncate( $text );
}

/**
 * Collect the visible text of a block and its inner blocks.
 *
 * Embeds contribute their URL, since their HTML is only a wrapper.
 *
 * @param array $block A parsed block.
 * @return string HTML text of the block.
 */
function block_text( array $block ): string {
	if ( 'core/embed' === $block['blockName'] ) {
		return (string) ( $block['attrs']['url'] ?? '' );
	}

	if ( [] === $block['innerBlocks'] ) {
		return (string) $block['innerHTML'];
	}

	$text = '';

	foreach ( $block['innerBlocks'] as $inner ) {
		$text .= ' ' . block_text( $inner );
	}

	return $text;
}

/**
 * Turn HTML into a single line of plain text.
 *
 * @param string $html HTML, or text.
 * @return string Plain text with single spaces.
 */
function to_plain_text( string $html ): string {
	$html = str_ireplace( [ '<br>', '<br/>', '<br />' ], ' ', $html );
	$text = decode( wp_strip_all_tags( $html ) );

	return trim( (string) preg_replace( '/\s+/u', ' ', $text ) );
}

/**
 * Shorten text to the Recent list's limit, with an ellipsis when cut.
 *
 * @param string $text Plain text.
 * @return string Text of at most 140 characters.
 */
function truncate( string $text ): string {
	if ( mb_strlen( $text ) <= TEXT_MAX_LENGTH ) {
		return $text;
	}

	return rtrim( mb_substr( $text, 0, TEXT_MAX_LENGTH - 1 ) ) . '…';
}
