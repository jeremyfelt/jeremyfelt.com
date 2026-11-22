<?php
/**
 * Tests for creating, reading and updating notes.
 *
 * @package jeremyfelt
 */

namespace JeremyFelt\Apps\WriteNow\Tests;

use WP_REST_Request;

use function JeremyFelt\Apps\WriteNow\Markdown\to_blocks;

/**
 * Create a JPEG attachment, large enough to have a `large` size.
 *
 * @param int    $author The attachment's author.
 * @param int    $width  Width in pixels.
 * @param string $mime   `image/jpeg` or `text/plain` for a non-image.
 * @return int The attachment ID.
 */
function make_attachment( int $author = 1, int $width = 1600, string $mime = 'image/jpeg' ): int {
	if ( 'image/jpeg' === $mime ) {
		$image = imagecreatetruecolor( $width, (int) ( $width * 0.75 ) );
		imagefill( $image, 0, 0, (int) imagecolorallocate( $image, 30, 120, 200 ) );
		ob_start();
		imagejpeg( $image );
		$bits = (string) ob_get_clean();
		$name = 'writenow-' . wp_generate_password( 6, false ) . '.jpg';
	} else {
		$bits = 'Just text';
		$name = 'writenow-' . wp_generate_password( 6, false ) . '.txt';
	}

	add_filter( 'upload_mimes', fn( $mimes ) => $mimes + [ 'txt' => 'text/plain' ] );
	$upload = wp_upload_bits( $name, null, $bits );

	if ( ! empty( $upload['error'] ) ) {
		fail( 'Upload failed: ' . $upload['error'] );
	}

	$id = wp_insert_attachment(
		[
			'post_mime_type' => $mime,
			'post_title'     => $name,
			'post_status'    => 'inherit',
			'post_author'    => $author,
		],
		$upload['file']
	);

	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $upload['file'] ) );

	return $id;
}

/**
 * Count the notes that carry a client ID.
 *
 * @param string $client_id The client ID.
 * @return int The number of notes.
 */
function count_notes_with_client_id( string $client_id ): int {
	global $wpdb;

	return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->postmeta} pm JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE pm.meta_key = '_writenow_client_id' AND pm.meta_value = %s AND p.post_type = 'shortnote'", $client_id ) );
}

/**
 * Publishing a note gives it Shortnotes' slug, title and permalink.
 */
function test_publish_note_happy_path(): void {
	$markdown = "Hello from **WriteNow**. A \"quote\", a back\\slash and it's 100% fine.\n\nSecond paragraph.";
	$response = rest(
		'POST',
		'/notes',
		[
			'content' => $markdown,
			'status'  => 'publish',
		]
	);

	assert_status( 201, $response );
	$data = data( $response );
	$post = get_post( $data['id'] );

	assert_same( 'publish', $data['status'] );
	assert_same( 'Posted.', $data['message'] );
	assert_same( false, $data['editable'] );
	assert_same( 'shortnote', $post->post_type );
	assert_same( 1, (int) $post->post_author );
	assert_true( 1 === preg_match( '/^[0-9a-f]{4}\d{10}$/', $post->post_name ), 'Slug is four hex characters and a timestamp: ' . $post->post_name );
	assert_same( home_url( '/notes/' . $post->post_name . '/' ), $data['link'] );
	assert_same( admin_url( 'post.php?post=' . $post->ID . '&action=edit' ), $data['edit_link'] );
	// Shortnotes measures the slashed content, so quotes and backslashes count twice, as in the editor.
	assert_same( 'Note: Hello from WriteNow. A "quote", a back\\slas&hellip;', $post->post_title, 'Shortnotes generated the title' );
	assert_same( 'Note: Hello from WriteNow. A "quote", a back\\slas…', $data['title'], 'The response title is plain text' );
	assert_same( $markdown, get_post_meta( $post->ID, '_markdown_content', true ), 'Markdown is stored exactly as received' );
	assert_same( to_blocks( $markdown ), $post->post_content, 'Content is the converted markup' );
	assert_same( '', get_post_meta( $post->ID, 'shortnotes_reply_to_url', true ), 'Not a reply' );
}

/**
 * A note made through WriteNow matches one made the way the block editor makes it.
 */
function test_note_matches_one_made_in_the_block_editor(): void {
	require_once ABSPATH . 'wp-admin/includes/post.php';

	// The block editor: an auto-draft from post-new.php, then a REST save that publishes it.
	wp_set_current_user( 1 );
	$auto_draft = get_default_post_to_edit( 'shortnote', true );
	$request    = new WP_REST_Request( 'POST', '/wp/v2/shortnote/' . $auto_draft->ID );
	$request->set_header( 'content-type', 'application/json' );
	$request->set_body(
		(string) wp_json_encode(
			[
				'content' => to_blocks( 'Same words in both.' ),
				'status'  => 'publish',
			]
		)
	);
	$editor_response = rest_do_request( $request );
	assert_status( 200, $editor_response );
	$editor = get_post( $auto_draft->ID, ARRAY_A );

	$ours = get_post(
		data(
			rest(
				'POST',
				'/notes',
				[
					'content' => 'Same words in both.',
					'status'  => 'publish',
				]
			)
		)['id'],
		ARRAY_A
	);

	foreach ( [ 'post_author', 'post_content', 'post_title', 'post_excerpt', 'post_status', 'comment_status', 'ping_status', 'post_password', 'to_ping', 'pinged', 'post_parent', 'menu_order', 'post_type', 'post_mime_type', 'comment_count' ] as $field ) {
		assert_same( $editor[ $field ], $ours[ $field ], "Field $field matches the editor" );
	}

	foreach ( [ $editor, $ours ] as $post ) {
		assert_same( home_url( '/?post_type=shortnote&#038;p=' . $post['ID'] ), $post['guid'], 'The guid has the editor format' );
		assert_true( '0000-00-00 00:00:00' !== $post['post_date_gmt'], 'The publish date is set' );
	}

	assert_true( 1 === preg_match( '/^[0-9a-f]{4}\d{10}$/', $ours['post_name'] ), 'WriteNow uses the Shortnotes slug format: ' . $ours['post_name'] );

	if ( version_compare( $GLOBALS['wp_version'], '7.0', '>=' ) ) {
		// Since WordPress 7.0 an auto-draft of a post type without title support has an empty
		// title instead of "Auto Draft", so Shortnotes never assigns its slug in the editor.
		assert_same( 'note-same-words-in-both', $editor['post_name'], 'On WordPress 7.0 and later the editor no longer gets the Shortnotes slug' );
	} else {
		assert_true( 1 === preg_match( '/^[0-9a-f]{4}\d{10}$/', $editor['post_name'] ), 'The editor uses the Shortnotes slug format: ' . $editor['post_name'] );
	}

	// Core's publish handling queued the same work for both.
	foreach ( [ '_pingme', '_encloseme' ] as $key ) {
		assert_same( metadata_exists( 'post', $editor['ID'], $key ), metadata_exists( 'post', $ours['ID'], $key ), "Meta $key matches" );
	}
}

/**
 * A draft keeps the placeholder title and is editable.
 */
function test_save_draft(): void {
	$response = rest( 'POST', '/notes', [ 'content' => 'Just a draft' ] );

	assert_status( 201, $response );
	$data = data( $response );
	$post = get_post( $data['id'] );

	assert_same( 'draft', $data['status'], 'Status defaults to draft' );
	assert_same( 'Saved as a draft.', $data['message'] );
	assert_same( true, $data['editable'] );
	assert_same( 'Note', $post->post_title, 'Placeholder title until published' );
	assert_same( 'Note', $data['title'] );
	assert_same( home_url( '/?post_type=shortnote&p=' . $post->ID ), $data['link'] );
	assert_true( 1 === preg_match( '/^[0-9a-f]{4}\d{10}$/', $post->post_name ), 'A draft has its slug already' );
}

/**
 * Bad input is refused with a message and creates nothing.
 */
function test_invalid_note_input(): void {
	$before = wp_count_posts( 'shortnote' );

	assert_status( 400, rest( 'POST', '/notes', [ 'content' => '' ] ), 'writenow_empty_note' );
	assert_status( 400, rest( 'POST', '/notes', [ 'content' => " \n\t " ] ), 'writenow_empty_note' );
	assert_status(
		400,
		rest(
			'POST',
			'/notes',
			[
				'content' => 'x',
				'status'  => 'future',
			]
		),
		'rest_invalid_param'
	);
	assert_status(
		400,
		rest(
			'POST',
			'/notes',
			[
				'content' => 'x',
				'status'  => 'private',
			]
		),
		'rest_invalid_param'
	);
	assert_status(
		400,
		rest(
			'POST',
			'/notes',
			[
				'content'      => 'x',
				'reply_to_url' => 'ftp://example.com/',
			]
		),
		'writenow_invalid_url'
	);
	assert_status(
		400,
		rest(
			'POST',
			'/notes',
			[
				'content'      => 'x',
				'reply_to_url' => 'javascript:alert(1)',
			]
		),
		'writenow_invalid_url'
	);
	assert_status(
		400,
		rest(
			'POST',
			'/notes',
			[
				'content'   => 'x',
				'client_id' => 'has spaces and <tags>',
			]
		),
		'rest_invalid_param'
	);
	assert_status(
		400,
		rest(
			'POST',
			'/notes',
			[
				'content' => 'x',
				'media'   => [ [ 'id' => 999999 ] ],
			]
		),
		'writenow_invalid_media'
	);

	clean_post_cache( 0 );
	wp_cache_delete( _count_posts_cache_key( 'shortnote' ), 'counts' );
	assert_same( (array) $before, (array) wp_count_posts( 'shortnote' ), 'Nothing was created' );

	$message = data( rest( 'POST', '/notes', [ 'content' => '' ] ) )['message'];
	assert_same( 'Write something or add an image before posting.', $message );
}

/**
 * A retried create with the same client_id returns the first note.
 */
function test_client_id_makes_create_idempotent(): void {
	$client_id = wp_generate_uuid4();
	$body      = [
		'content'   => 'Sent twice',
		'status'    => 'publish',
		'client_id' => $client_id,
	];

	$first  = rest( 'POST', '/notes', $body );
	$second = rest( 'POST', '/notes', $body );
	$third  = rest( 'POST', '/notes', array_merge( $body, [ 'content' => 'Changed on retry' ] ) );

	assert_status( 201, $first );
	assert_status( 200, $second );
	assert_status( 200, $third );
	assert_same( data( $first )['id'], data( $second )['id'] );
	assert_same( data( $first )['id'], data( $third )['id'] );
	assert_same( 'Posted.', data( $second )['message'] );
	assert_same( 1, count_notes_with_client_id( $client_id ), 'Exactly one note' );
	assert_same( 'Sent twice', get_post_meta( data( $first )['id'], '_markdown_content', true ), 'A replay does not change a published note' );
}

/**
 * A retry finishes a publish that only got as far as the draft.
 */
function test_client_id_retry_completes_an_unfinished_publish(): void {
	$client_id = wp_generate_uuid4();
	$draft     = data(
		rest(
			'POST',
			'/notes',
			[
				'content'   => 'Halfway',
				'client_id' => $client_id,
			]
		)
	);

	$retry = rest(
		'POST',
		'/notes',
		[
			'content'   => 'Halfway',
			'status'    => 'publish',
			'client_id' => $client_id,
		]
	);

	assert_status( 200, $retry );
	assert_same( $draft['id'], data( $retry )['id'] );
	assert_same( 'publish', get_post_status( $draft['id'] ) );
	assert_same( 1, count_notes_with_client_id( $client_id ) );
}

/**
 * A trashed note still counts, so a late retry does not resurrect it.
 */
function test_client_id_of_trashed_note_is_not_recreated(): void {
	$client_id = wp_generate_uuid4();
	$note      = data(
		rest(
			'POST',
			'/notes',
			[
				'content'   => 'Doomed',
				'client_id' => $client_id,
			]
		)
	);
	wp_trash_post( $note['id'] );

	$retry = rest(
		'POST',
		'/notes',
		[
			'content'   => 'Doomed',
			'client_id' => $client_id,
		]
	);

	assert_status( 200, $retry );
	assert_same( $note['id'], data( $retry )['id'] );
	assert_same( 1, count_notes_with_client_id( $client_id ) );
}

/**
 * Reply fields become Shortnotes' reply meta, and the site shows the reply.
 */
function test_reply_meta(): void {
	$data = data(
		rest(
			'POST',
			'/notes',
			[
				'content'       => 'Agreed!',
				'status'        => 'publish',
				'reply_to_url'  => '  https://example.com/a-post?x=1  ',
				'reply_to_name' => 'Someone <b>Else</b>',
			]
		)
	);

	assert_same( 'reply', get_post_meta( $data['id'], 'shortnotes_note_type', true ) );
	assert_same( 'https://example.com/a-post?x=1', get_post_meta( $data['id'], 'shortnotes_reply_to_url', true ) );
	assert_same( 'Someone Else', get_post_meta( $data['id'], 'shortnotes_reply_to_name', true ) );

	$markup = shortnotes( 'PostType\Note\get_reply_to_markup', get_post( $data['id'] ) );
	assert_contains( '<a class="u-in-reply-to" href="https://example.com/a-post?x=1">Someone Else</a>', $markup );

	// Shortnotes adds the reply URL to the webmentions it sends.
	assert_contains( 'https://example.com/a-post?x=1', implode( ' ', shortnotes( 'Webmention\filter_webmention_links', [], $data['id'] ) ) );

	$note = data( rest( 'GET', '/notes/' . $data['id'] ) );
	assert_same( 'https://example.com/a-post?x=1', $note['reply_to_url'] );
	assert_same( 'Someone Else', $note['reply_to_name'] );
}

/**
 * Meta, attachments and content are all in place when publish hooks run.
 */
function test_everything_is_in_place_before_publish_hooks(): void {
	$attachment = make_attachment();
	$seen       = [];
	$hook       = function ( $post_id ) use ( &$seen, $attachment ) {
		$seen[] = [
			'markdown' => get_post_meta( $post_id, '_markdown_content', true ),
			'reply'    => get_post_meta( $post_id, 'shortnotes_reply_to_url', true ),
			'parent'   => (int) get_post( $attachment )->post_parent,
			'content'  => get_post( $post_id )->post_content,
			'title'    => get_post( $post_id )->post_title,
		];
	};

	add_action( 'publish_shortnote', $hook );

	$data = data(
		rest(
			'POST',
			'/notes',
			[
				'content'      => 'With a picture',
				'status'       => 'publish',
				'reply_to_url' => 'https://example.com/reply-target',
				'media'        => [
					[
						'id'  => $attachment,
						'alt' => 'Blue',
					],
				],
			]
		)
	);

	remove_action( 'publish_shortnote', $hook );

	assert_same( 1, count( $seen ), 'The publish hook ran exactly once' );
	assert_same( 'With a picture', $seen[0]['markdown'] );
	assert_same( 'https://example.com/reply-target', $seen[0]['reply'] );
	assert_same( $data['id'], $seen[0]['parent'], 'The image was attached before publish' );
	assert_contains( 'wp-image-' . $attachment, $seen[0]['content'] );
	assert_same( 'Note: With a picture', $seen[0]['title'] );
}

/**
 * Images are appended in order with their alt text, attached and validated.
 */
function test_media_are_appended_attached_and_validated(): void {
	$first  = make_attachment();
	$second = make_attachment( 1, 400 );

	$data = data(
		rest(
			'POST',
			'/notes',
			[
				'content' => 'Two pictures',
				'media'   => [
					[
						'id'  => $first,
						'alt' => 'The "first" one & <more>',
					],
					[ 'id' => $second ],
					[
						'id'  => $first,
						'alt' => 'Repeated',
					],
				],
			]
		)
	);

	$post   = get_post( $data['id'] );
	$blocks = array_values( array_filter( parse_blocks( $post->post_content ), fn( $block ) => null !== $block['blockName'] ) );

	assert_same( [ 'core/paragraph', 'core/image', 'core/image' ], array_column( $blocks, 'blockName' ), 'Text, then each image once, in order' );
	assert_same( $first, $blocks[1]['attrs']['id'] );
	assert_same( 'large', $blocks[1]['attrs']['sizeSlug'] );
	assert_contains( 'src="' . wp_get_attachment_image_src( $first, 'large' )[0] . '"', $blocks[1]['innerHTML'], 'The large size is used' );
	assert_contains( 'alt="The &quot;first&quot; one &amp;"', $blocks[1]['innerHTML'], 'Alt text is sanitized and escaped' );
	assert_contains( 'src="' . wp_get_attachment_url( $second ) . '"', $blocks[2]['innerHTML'], 'A small image uses its full size' );
	assert_same( $post->post_content, serialize_blocks( parse_blocks( $post->post_content ) ), 'Round trip' );

	assert_same( $data['id'], (int) get_post( $first )->post_parent );
	assert_same( $data['id'], (int) get_post( $second )->post_parent );
	assert_same( 'The "first" one &', get_post_meta( $first, '_wp_attachment_image_alt', true ) );

	$note = data( rest( 'GET', '/notes/' . $data['id'] ) );
	assert_same( [ $first, $second ], array_column( $note['media'], 'id' ) );
	assert_same( 'The "first" one &', $note['media'][0]['alt'] );
	assert_same( wp_get_attachment_image_url( $first, 'thumbnail' ), $note['media'][0]['thumbnail'] );

	$text_file = make_attachment( 1, 0, 'text/plain' );
	assert_status(
		400,
		rest(
			'POST',
			'/notes',
			[
				'content' => 'x',
				'media'   => [ [ 'id' => $text_file ] ],
			]
		),
		'writenow_invalid_media'
	);
	assert_status(
		400,
		rest(
			'POST',
			'/notes',
			[
				'content' => 'x',
				'media'   => [ [ 'id' => $data['id'] ] ],
			]
		),
		'writenow_invalid_media'
	);

	$author = credentials( 'author' )['id'];
	assert_status(
		403,
		rest(
			'POST',
			'/notes',
			[
				'content' => 'x',
				'media'   => [ [ 'id' => make_attachment() ] ],
			],
			$author
		),
		'writenow_invalid_media'
	);
}

/**
 * An image already attached elsewhere stays attached there.
 */
function test_media_attached_elsewhere_is_not_moved(): void {
	$attachment = make_attachment();
	$other      = wp_insert_post(
		[
			'post_title'  => 'Other',
			'post_status' => 'publish',
		]
	);
	wp_update_post(
		[
			'ID'          => $attachment,
			'post_parent' => $other,
		]
	);

	rest( 'POST', '/notes', [ 'media' => [ [ 'id' => $attachment ] ] ] );

	assert_same( $other, (int) get_post( $attachment )->post_parent );
}

/**
 * A note that is only an image gets Shortnotes' image title.
 */
function test_image_only_note(): void {
	$data = data(
		rest(
			'POST',
			'/notes',
			[
				'content' => '',
				'status'  => 'publish',
				'media'   => [ [ 'id' => make_attachment() ] ],
			]
		)
	);

	assert_same( 'publish', $data['status'] );
	assert_true( 0 === strpos( $data['title'], 'Note: Image posted on ' ), 'Image title: ' . $data['title'] );
}

/**
 * Shortnotes cuts titles in bytes. A cut through a character is repaired.
 */
function test_title_cut_through_a_multibyte_character_is_repaired(): void {
	// 45 ASCII characters, then "é": byte 46 is the first half of the "é".
	$markdown = str_repeat( 'a', 45 ) . 'é and then some more words';

	// Shortnotes on its own produces invalid UTF-8 for this note.
	$raw = shortnotes( 'PostType\Note\generate_sub_title', '<p>' . $markdown . '</p>' );
	assert_true( 1 !== preg_match( '//u', $raw ), 'Shortnotes leaves half a character' );

	$response = rest(
		'POST',
		'/notes',
		[
			'content' => $markdown,
			'status'  => 'publish',
		]
	);
	assert_status( 201, $response );

	$title = get_post( data( $response )['id'] )->post_title;
	assert_same( 1, preg_match( '//u', $title ), 'The stored title is valid UTF-8' );
	assert_same( 'Note: ' . str_repeat( 'a', 45 ) . '&hellip;', $title );

	// The repair applies only while WriteNow saves: other notes are untouched.
	assert_true( ! has_filter( 'shortnotes_formatted_title', 'JeremyFelt\Apps\WriteNow\Notes\repair_formatted_title' ), 'The repair filter is removed afterwards' );
}

/**
 * GET returns what the composer needs, and editable follows the rules.
 */
function test_get_note_and_editable_rules(): void {
	$draft = data( rest( 'POST', '/notes', [ 'content' => 'Reopen me' ] ) );
	$note  = data( rest( 'GET', '/notes/' . $draft['id'] ) );

	foreach ( [ 'id', 'status', 'content', 'reply_to_url', 'reply_to_name', 'media', 'link', 'edit_link', 'modified', 'editable' ] as $key ) {
		assert_true( array_key_exists( $key, $note ), "GET includes $key" );
	}

	assert_same( 'Reopen me', $note['content'] );
	assert_same( true, $note['editable'] );
	assert_true( 1 === preg_match( '/^\d{4}-\d\d-\d\dT\d\d:\d\d:\d\d\+00:00$/', $note['modified'] ), 'modified is ISO 8601 in UTC' );

	$published = data(
		rest(
			'POST',
			'/notes',
			[
				'content' => 'Out there',
				'status'  => 'publish',
			]
		)
	);
	assert_same( false, data( rest( 'GET', '/notes/' . $published['id'] ) )['editable'], 'Published notes are not editable' );

	// A draft written in the block editor has no markdown.
	$editor_draft = wp_insert_post(
		[
			'post_type'    => 'shortnote',
			'post_status'  => 'draft',
			'post_content' => to_blocks( 'From wp-admin' ),
		]
	);
	assert_same( false, data( rest( 'GET', '/notes/' . $editor_draft ) )['editable'], 'Drafts without markdown are not editable' );

	$like = wp_insert_post(
		[
			'post_type'   => 'like',
			'post_status' => 'publish',
			'post_title'  => 'A like',
		]
	);
	assert_status( 404, rest( 'GET', '/notes/' . $like ), 'writenow_note_not_found' );
	assert_status( 404, rest( 'GET', '/notes/99999999' ), 'writenow_note_not_found' );
}

/**
 * Drafts can be edited and published; anything else is a conflict.
 */
function test_update_and_publish_a_draft(): void {
	$attachment = make_attachment();
	$draft      = data(
		rest(
			'POST',
			'/notes',
			[
				'content'      => 'First version',
				'reply_to_url' => 'https://example.com/r',
			]
		)
	);
	$slug       = get_post( $draft['id'] )->post_name;

	$updated = rest(
		'POST',
		'/notes/' . $draft['id'],
		[
			'content' => "Second *version*\n- with a list",
			'media'   => [
				[
					'id'  => $attachment,
					'alt' => 'Alt',
				],
			],
		]
	);
	assert_status( 200, $updated );
	assert_same( 'Saved as a draft.', data( $updated )['message'] );
	assert_same( "Second *version*\n- with a list", get_post_meta( $draft['id'], '_markdown_content', true ) );
	assert_contains( '<em>version</em>', get_post( $draft['id'] )->post_content );
	assert_contains( 'wp-image-' . $attachment, get_post( $draft['id'] )->post_content );
	assert_same( '', get_post_meta( $draft['id'], 'shortnotes_reply_to_url', true ), 'Clearing the reply URL removes the reply meta' );
	assert_true( ! metadata_exists( 'post', $draft['id'], 'shortnotes_note_type' ), 'The note type is removed too' );
	assert_same( 'Note', get_post( $draft['id'] )->post_title );

	$published = rest(
		'POST',
		'/notes/' . $draft['id'],
		[
			'content' => 'Final version',
			'status'  => 'publish',
		]
	);
	assert_status( 200, $published );
	$data = data( $published );
	assert_same( 'publish', $data['status'] );
	assert_same( 'Posted.', $data['message'] );
	assert_same( 'Note: Final version', $data['title'] );
	assert_same( $slug, get_post( $draft['id'] )->post_name, 'The slug is kept' );
	assert_same( home_url( '/notes/' . $slug . '/' ), $data['link'] );
	assert_same( 1, count( array_filter( parse_blocks( get_post( $draft['id'] )->post_content ), fn( $block ) => null !== $block['blockName'] ) ), 'The image was removed with the update' );

	// Repeating the publish is harmless.
	assert_status(
		200,
		rest(
			'POST',
			'/notes/' . $draft['id'],
			[
				'content' => 'Final version',
				'status'  => 'publish',
			]
		)
	);

	// Changing a published note is not.
	$conflict = rest(
		'POST',
		'/notes/' . $draft['id'],
		[
			'content' => 'Sneaky edit',
			'status'  => 'publish',
		]
	);
	assert_status( 409, $conflict, 'writenow_not_editable' );
	assert_contains( 'wp-admin', data( $conflict )['message'] );
	assert_same( admin_url( 'post.php?post=' . $draft['id'] . '&action=edit' ), data( $conflict )['data']['edit_link'] );
	assert_same( 'Final version', get_post_meta( $draft['id'], '_markdown_content', true ) );

	$editor_draft = wp_insert_post(
		[
			'post_type'   => 'shortnote',
			'post_status' => 'draft',
			'post_title'  => 'Note',
		]
	);
	assert_status( 409, rest( 'POST', '/notes/' . $editor_draft, [ 'content' => 'x' ] ), 'writenow_not_editable' );
	assert_status( 400, rest( 'POST', '/notes/' . $draft['id'], [ 'content' => '' ] ), 'writenow_empty_note' );
}
