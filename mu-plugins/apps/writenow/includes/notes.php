<?php
/**
 * Create, read and update notes.
 *
 * A note created here is meant to be indistinguishable from one written in
 * the block editor: it gets the same slug format and placeholder title, it
 * is saved with wp_insert_post() so Shortnotes' own filters generate the
 * title on publish and its publish hooks run, and all meta is passed through
 * `meta_input` so it is in place before those hooks fire.
 *
 * @package jeremyfelt
 */

namespace JeremyFelt\Apps\WriteNow\Notes;

use WP_Error;
use WP_Post;
use WP_REST_Request;
use WP_REST_Response;

use function JeremyFelt\Apps\WriteNow\REST\decode;
use function JeremyFelt\Apps\WriteNow\REST\error;
use function JeremyFelt\Apps\WriteNow\REST\get_note_post_type;
use function JeremyFelt\Apps\WriteNow\Lock\with_lock;
use function JeremyFelt\Apps\WriteNow\Markdown\image_block;
use function JeremyFelt\Apps\WriteNow\Markdown\to_blocks;
use function JeremyFelt\Apps\WriteNow\Markdown\get_default_embed_providers;

use const JeremyFelt\Apps\WriteNow\CLIENT_ID_META_KEY;
use const JeremyFelt\Apps\WriteNow\MARKDOWN_META_KEY;

/**
 * Reply meta keys registered by Shortnotes.
 */
const REPLY_META_KEYS = [ 'shortnotes_note_type', 'shortnotes_reply_to_url', 'shortnotes_reply_to_name' ];

/**
 * Create a note.
 *
 * When the request carries a `client_id` that an existing note already has,
 * that note is returned with a 200 instead of creating another, so the
 * outbox can retry safely.
 *
 * @param WP_REST_Request $request The request.
 * @return WP_REST_Response|WP_Error The note, or the reason it was not created.
 */
function create_note( WP_REST_Request $request ) {
	$input = get_input( $request );

	if ( is_wp_error( $input ) ) {
		return $input;
	}

	if ( '' === $input['client_id'] ) {
		return insert_note( $input );
	}

	return with_lock(
		'note:' . $input['client_id'],
		function () use ( $input ) {
			$existing = find_note_by_client_id( $input['client_id'] );

			if ( null === $existing ) {
				return insert_note( $input );
			}

			// The first attempt saved a draft but did not get as far as publishing it.
			if ( 'publish' === $input['status'] && is_editable( $existing ) ) {
				$saved = save_note( $existing, $input );

				return is_wp_error( $saved ) ? $saved : note_response( $saved, 200 );
			}

			return note_response( $existing, 200 );
		}
	);
}

/**
 * Return what the composer needs to reopen a note.
 *
 * @param WP_REST_Request $request The request.
 * @return WP_REST_Response The note.
 */
function get_note( WP_REST_Request $request ): WP_REST_Response {
	$post = get_post( (int) $request->get_param( 'id' ) );

	// The permission callback has already confirmed this is a note the user can edit.
	return new WP_REST_Response( prepare_note( $post ) );
}

/**
 * Update a note that is still editable in WriteNow.
 *
 * Publishing a draft is an update with `"status": "publish"`. A request that
 * repeats one that already succeeded, such as a retried publish, returns the
 * note with a 200 instead of a conflict.
 *
 * @param WP_REST_Request $request The request.
 * @return WP_REST_Response|WP_Error The note, or the reason it was not updated.
 */
function update_note( WP_REST_Request $request ) {
	$input = get_input( $request );

	if ( is_wp_error( $input ) ) {
		return $input;
	}

	$id = (int) $request->get_param( 'id' );

	return with_lock(
		'note-id:' . $id,
		function () use ( $id, $input ) {
			// Read the note inside the lock, in case a request that held it just published it.
			clean_post_cache( $id );
			$post = get_post( $id );

			if ( ! $post instanceof WP_Post ) {
				return error( 'writenow_note_not_found', 'That note could not be found. It may have been deleted.', 404 );
			}

			if ( ! is_editable( $post ) ) {
				if ( is_replay( $post, $input ) ) {
					return note_response( $post, 200 );
				}

				return error(
					'writenow_not_editable',
					'This note can no longer be changed in WriteNow. Edit it in wp-admin instead.',
					409,
					[ 'edit_link' => get_edit_link( $post ) ]
				);
			}

			$saved = save_note( $post, $input );

			return is_wp_error( $saved ) ? $saved : note_response( $saved, 200 );
		}
	);
}

/**
 * Read and validate the note fields of a request.
 *
 * @param WP_REST_Request $request The request.
 * @return array|WP_Error The validated input, or the reason it is not valid.
 */
function get_input( WP_REST_Request $request ) {
	$content = (string) $request->get_param( 'content' );
	$status  = 'publish' === $request->get_param( 'status' ) ? 'publish' : 'draft';

	$reply_to_url  = trim( (string) $request->get_param( 'reply_to_url' ) );
	$reply_to_name = sanitize_text_field( (string) $request->get_param( 'reply_to_name' ) );

	if ( '' !== $reply_to_url ) {
		$reply_to_url = esc_url_raw( $reply_to_url, [ 'http', 'https' ] );

		if ( '' === $reply_to_url || ! wp_parse_url( $reply_to_url, PHP_URL_HOST ) ) {
			return error( 'writenow_invalid_url', 'The reply URL needs to be a web address starting with http:// or https://.', 400 );
		}
	}

	$media = get_media_input( (array) $request->get_param( 'media' ) );

	if ( is_wp_error( $media ) ) {
		return $media;
	}

	if ( '' === trim( $content ) && [] === $media ) {
		return error( 'writenow_empty_note', 'Write something or add an image before posting.', 400 );
	}

	return [
		'content'       => $content,
		'status'        => $status,
		'reply_to_url'  => $reply_to_url,
		'reply_to_name' => '' === $reply_to_url ? '' : $reply_to_name,
		'media'         => $media,
		'client_id'     => (string) $request->get_param( 'client_id' ),
	];
}

/**
 * Validate the media items of a request.
 *
 * Each must be an image attachment the current user may edit. Repeated IDs
 * are kept once, in their first position.
 *
 * @param array $items Items with `id` and optional `alt`.
 * @return array[]|WP_Error A list of `id` and `alt` pairs, or the reason one is not valid.
 */
function get_media_input( array $items ) {
	$media = [];

	foreach ( $items as $item ) {
		$id  = is_array( $item ) && isset( $item['id'] ) ? (int) $item['id'] : 0;
		$alt = is_array( $item ) && isset( $item['alt'] ) ? sanitize_text_field( (string) $item['alt'] ) : '';

		$attachment = 0 < $id ? get_post( $id ) : null;

		if ( ! $attachment instanceof WP_Post || 'attachment' !== $attachment->post_type || ! wp_attachment_is_image( $attachment ) ) {
			return error( 'writenow_invalid_media', 'One of the images could not be found on the site. Remove it and add it again.', 400 );
		}

		if ( ! current_user_can( 'edit_post', $id ) ) {
			return error( 'writenow_invalid_media', 'This account is not allowed to use one of the images. Remove it and add it again.', 403 );
		}

		if ( ! isset( $media[ $id ] ) ) {
			$media[ $id ] = [
				'id'  => $id,
				'alt' => $alt,
			];
		}
	}

	return array_values( $media );
}

/**
 * Insert a new note.
 *
 * The note is always created as a draft first and then published if that
 * was asked for, as the block editor does. This gives it the same `guid`
 * as an editor-made note (`?post_type=shortnote&p=…`, which feeds use), and
 * lets images be attached to it before anything that runs on publish.
 *
 * @param array $input Validated input from get_input().
 * @return WP_REST_Response|WP_Error The created note, or the reason it was not created.
 */
function insert_note( array $input ) {
	$meta = [
		MARKDOWN_META_KEY => $input['content'],
	];

	if ( '' !== $input['client_id'] ) {
		$meta[ CLIENT_ID_META_KEY ] = $input['client_id'];
	}

	$post_id = insert_or_update(
		[
			'post_type'    => get_note_post_type(),
			'post_status'  => 'draft',
			'post_author'  => get_current_user_id(),
			'post_title'   => get_placeholder_title(),
			'post_name'    => generate_slug(),
			'post_content' => build_content( $input['content'], $input['media'] ),
			'meta_input'   => array_merge( $meta, get_reply_meta( $input ) ),
		]
	);

	if ( is_wp_error( $post_id ) ) {
		return $post_id;
	}

	attach_media( $input['media'], $post_id );

	if ( 'draft' !== $input['status'] ) {
		$published = insert_or_update(
			[
				'ID'          => $post_id,
				'post_status' => $input['status'],
			]
		);

		if ( is_wp_error( $published ) ) {
			return $published;
		}
	}

	clean_post_cache( $post_id );
	$post = get_post( $post_id );

	if ( ! $post instanceof WP_Post ) {
		return error( 'writenow_save_failed', 'The note could not be saved. Try again.', 500 );
	}

	return note_response( $post, 201 );
}

/**
 * Save new content and status to an existing note.
 *
 * Images are attached and their alt text saved before the note is updated,
 * so that they are in place before any publish hooks run.
 *
 * @param WP_Post $post  The note.
 * @param array   $input Validated input from get_input().
 * @return WP_Post|WP_Error The saved note, or the reason it was not saved.
 */
function save_note( WP_Post $post, array $input ) {
	attach_media( $input['media'], $post->ID );

	$result = insert_or_update(
		[
			'ID'           => $post->ID,
			'post_status'  => $input['status'],
			'post_content' => build_content( $input['content'], $input['media'] ),
			'meta_input'   => array_merge( [ MARKDOWN_META_KEY => $input['content'] ], get_reply_meta( $input ) ),
		]
	);

	if ( is_wp_error( $result ) ) {
		return $result;
	}

	if ( '' === $input['reply_to_url'] ) {
		foreach ( REPLY_META_KEYS as $key ) {
			delete_post_meta( $post->ID, $key );
		}
	}

	clean_post_cache( $post->ID );
	$saved = get_post( $post->ID );

	return $saved instanceof WP_Post ? $saved : error( 'writenow_save_failed', 'The note could not be saved. Try again.', 500 );
}

/**
 * Insert or update a post through core, so every core and plugin hook runs.
 *
 * @param array $postarr Unslashed post data for wp_insert_post() or wp_update_post().
 * @return int|WP_Error The post ID, or an error with a message for Jeremy.
 */
function insert_or_update( array $postarr ) {
	add_filter( 'shortnotes_formatted_title', __NAMESPACE__ . '\repair_formatted_title', 99 );

	// Both functions expect slashed data and unslash it before saving.
	$result = isset( $postarr['ID'] ) ? wp_update_post( wp_slash( $postarr ), true ) : wp_insert_post( wp_slash( $postarr ), true );

	remove_filter( 'shortnotes_formatted_title', __NAMESPACE__ . '\repair_formatted_title', 99 );

	if ( is_wp_error( $result ) ) {
		return error( 'writenow_save_failed', 'The note could not be saved: ' . rtrim( $result->get_error_message(), '.' ) . '.', 500 );
	}

	return (int) $result;
}

/**
 * Repair a generated title that ends part way through a character.
 *
 * Shortnotes counts the title's length in characters but cuts it in bytes,
 * so a note with curly quotes, accents or emoji near the cut can produce a
 * title that is not valid UTF-8. MySQL then refuses to save the post. This
 * removes only the partial character, and is applied only while WriteNow is
 * saving a note.
 *
 * @param string $title The formatted title.
 * @return string The title as valid UTF-8.
 */
function repair_formatted_title( $title ) {
	$title = (string) $title;

	if ( 1 === preg_match( '//u', $title ) ) {
		return $title;
	}

	$suffix = '&hellip;';
	$has    = substr( $title, -strlen( $suffix ) ) === $suffix;
	$head   = $has ? substr( $title, 0, -strlen( $suffix ) ) : $title;

	// A UTF-8 character is at most four bytes, so at most three can be left over.
	$tries = 0;

	while ( $tries < 3 && 1 !== preg_match( '//u', $head ) ) {
		$head = substr( $head, 0, -1 );
		++$tries;
	}

	if ( 1 !== preg_match( '//u', $head ) ) {
		$head = htmlspecialchars_decode( htmlspecialchars( $head, ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8' ), ENT_NOQUOTES );
	}

	return $head . ( $has ? $suffix : '' );
}

/**
 * Build the reply meta for a note.
 *
 * @param array $input Validated input from get_input().
 * @return array Meta keys and values, empty when the note is not a reply.
 */
function get_reply_meta( array $input ): array {
	if ( '' === $input['reply_to_url'] ) {
		return [];
	}

	return [
		'shortnotes_note_type'     => 'reply',
		'shortnotes_reply_to_url'  => $input['reply_to_url'],
		'shortnotes_reply_to_name' => $input['reply_to_name'],
	];
}

/**
 * Attach images to a note and save their alt text.
 *
 * An image already attached to a different post is left attached there.
 *
 * @param array[] $media   Validated media items.
 * @param int     $post_id The note's ID.
 */
function attach_media( array $media, int $post_id ): void {
	foreach ( $media as $item ) {
		$attachment = get_post( $item['id'] );

		if ( ! $attachment instanceof WP_Post ) {
			continue;
		}

		if ( 0 === (int) $attachment->post_parent ) {
			wp_update_post(
				[
					'ID'          => $attachment->ID,
					'post_parent' => $post_id,
				]
			);
		}

		update_post_meta( $attachment->ID, '_wp_attachment_image_alt', wp_slash( $item['alt'] ) );
	}
}

/**
 * Build a note's block markup from its markdown and images.
 *
 * @param string  $markdown The markdown.
 * @param array[] $media    Validated media items, in order.
 * @return string Block markup.
 */
function build_content( string $markdown, array $media ): string {
	/**
	 * Filters the oEmbed providers WriteNow turns into embed blocks.
	 *
	 * @param array[] $providers Providers in the shape of
	 *                           JeremyFelt\Apps\WriteNow\Markdown\get_default_embed_providers().
	 */
	$providers = apply_filters( 'writenow_embed_providers', get_default_embed_providers() );

	$blocks = [];
	$text   = to_blocks( $markdown, [ 'embed_providers' => (array) $providers ] );

	if ( '' !== $text ) {
		$blocks[] = $text;
	}

	foreach ( $media as $item ) {
		$image = wp_get_attachment_image_src( $item['id'], 'large' );

		if ( ! $image ) {
			continue;
		}

		$blocks[] = image_block( $item['id'], $image[0], $item['alt'], 'large' );
	}

	return implode( "\n\n", $blocks );
}

/**
 * Generate a slug in the format Shortnotes gives notes made in the editor.
 *
 * @return string The slug, such as `ab121764040689`.
 */
function generate_slug(): string {
	return substr( wp_generate_uuid4(), 0, 4 ) . time();
}

/**
 * Provide the placeholder title Shortnotes gives a note until it is published.
 *
 * @return string The placeholder title.
 */
function get_placeholder_title(): string {
	return function_exists( '\ShortNotes\PostType\Note\get_placeholder_title' ) ? \ShortNotes\PostType\Note\get_placeholder_title() : 'Note';
}

/**
 * Find the note created with a client ID.
 *
 * Trashed notes count, so a retry never recreates a note Jeremy deleted.
 *
 * @param string $client_id The client ID.
 * @return WP_Post|null The note, or null.
 */
function find_note_by_client_id( string $client_id ): ?WP_Post {
	$posts = get_posts(
		[
			'post_type'        => get_note_post_type(),
			'post_status'      => [ 'publish', 'future', 'draft', 'pending', 'private', 'trash' ],
			'meta_key'         => CLIENT_ID_META_KEY, // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'       => $client_id, // phpcs:ignore WordPress.DB.SlowDBQuery
			'posts_per_page'   => 1,
			'orderby'          => 'ID',
			'order'            => 'ASC',
			'no_found_rows'    => true,
			'suppress_filters' => true,
		]
	);

	return isset( $posts[0] ) && $posts[0] instanceof WP_Post ? $posts[0] : null;
}

/**
 * Determine whether a note can still be edited in WriteNow.
 *
 * Only drafts that have stored markdown are editable. Anything else is
 * edited in wp-admin.
 *
 * @param WP_Post $post The note.
 * @return bool True if editable.
 */
function is_editable( WP_Post $post ): bool {
	return get_note_post_type() === $post->post_type
		&& 'draft' === $post->post_status
		&& metadata_exists( 'post', $post->ID, MARKDOWN_META_KEY );
}

/**
 * Determine whether an update repeats the note's current state.
 *
 * @param WP_Post $post  The note.
 * @param array   $input Validated input from get_input().
 * @return bool True if the note already has this status and markdown.
 */
function is_replay( WP_Post $post, array $input ): bool {
	return $input['status'] === $post->post_status
		&& metadata_exists( 'post', $post->ID, MARKDOWN_META_KEY )
		&& get_post_meta( $post->ID, MARKDOWN_META_KEY, true ) === $input['content'];
}

/**
 * Provide the wp-admin edit URL for a post.
 *
 * @param WP_Post $post The post.
 * @return string The edit URL.
 */
function get_edit_link( WP_Post $post ): string {
	$link = get_edit_post_link( $post->ID, 'raw' );

	return $link ? $link : admin_url( 'post.php?post=' . $post->ID . '&action=edit' );
}

/**
 * List the images in a note, in order.
 *
 * @param WP_Post $post The note.
 * @return array[] Items with `id`, `alt` and `thumbnail`.
 */
function get_note_media( WP_Post $post ): array {
	$media = [];

	foreach ( parse_blocks( $post->post_content ) as $block ) {
		if ( 'core/image' !== $block['blockName'] || empty( $block['attrs']['id'] ) ) {
			continue;
		}

		$id  = (int) $block['attrs']['id'];
		$alt = preg_match( '/\salt="([^"]*)"/', (string) $block['innerHTML'], $matches ) ? decode( $matches[1] ) : '';

		$thumbnail = wp_get_attachment_image_url( $id, 'thumbnail' );

		$media[] = [
			'id'        => $id,
			'alt'       => $alt,
			'thumbnail' => $thumbnail ? $thumbnail : (string) wp_get_attachment_url( $id ),
		];
	}

	return $media;
}

/**
 * Describe a note for the app.
 *
 * @param WP_Post $post The note.
 * @return array The note's fields.
 */
function prepare_note( WP_Post $post ): array {
	// A new draft's GMT dates are zero, so read the local date and convert it.
	$modified = get_post_datetime( $post, 'modified', 'local' );

	return [
		'id'            => $post->ID,
		'status'        => $post->post_status,
		'title'         => decode( $post->post_title ),
		'content'       => (string) get_post_meta( $post->ID, MARKDOWN_META_KEY, true ),
		'reply_to_url'  => (string) get_post_meta( $post->ID, 'shortnotes_reply_to_url', true ),
		'reply_to_name' => (string) get_post_meta( $post->ID, 'shortnotes_reply_to_name', true ),
		'media'         => get_note_media( $post ),
		'link'          => (string) get_permalink( $post ),
		'edit_link'     => get_edit_link( $post ),
		'modified'      => $modified ? $modified->setTimezone( new \DateTimeZone( 'UTC' ) )->format( DATE_ATOM ) : '',
		'editable'      => is_editable( $post ),
	];
}

/**
 * Build the response for a saved note.
 *
 * @param WP_Post $post   The note.
 * @param int     $status The HTTP status: 201 when created, 200 otherwise.
 * @return WP_REST_Response The response.
 */
function note_response( WP_Post $post, int $status ): WP_REST_Response {
	$data = prepare_note( $post );

	if ( 'publish' === $post->post_status ) {
		$data['message'] = 'Posted.';
	} elseif ( 'future' === $post->post_status ) {
		$data['message'] = 'Scheduled.';
	} else {
		$data['message'] = 'Saved as a draft.';
	}

	return new WP_REST_Response( $data, $status );
}
