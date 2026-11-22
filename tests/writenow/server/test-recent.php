<?php
/**
 * Tests for GET /recent.
 *
 * @package jeremyfelt
 */

namespace JeremyFelt\Apps\WriteNow\Tests;

/**
 * Notes in draft, future and publish, and published likes, newest first.
 */
function test_recent_lists_notes_and_likes_newest_first(): void {
	$like    = data(
		rest(
			'POST',
			'/likes',
			[
				'url'   => unique_url(),
				'title' => 'Recent like',
			]
		)
	);
	$draft   = data( rest( 'POST', '/notes', [ 'content' => "A **draft** in Recent\nwith two lines" ] ) );
	$publish = data(
		rest(
			'POST',
			'/notes',
			[
				'content' => 'Published in Recent',
				'status'  => 'publish',
			]
		)
	);
	$future  = wp_insert_post(
		[
			'post_type'     => 'shortnote',
			'post_status'   => 'future',
			'post_title'    => 'Note',
			'post_content'  => '<!-- wp:paragraph --><p>Scheduled</p><!-- /wp:paragraph -->',
			'post_date'     => gmdate( 'Y-m-d H:i:s', time() + DAY_IN_SECONDS ),
			'post_date_gmt' => gmdate( 'Y-m-d H:i:s', time() + DAY_IN_SECONDS ),
		]
	);
	$pending = wp_insert_post(
		[
			'post_type'    => 'shortnote',
			'post_status'  => 'pending',
			'post_content' => 'Pending',
		]
	);

	$items = data( rest( 'GET', '/recent', null, 1, [ 'per_page' => 50 ] ) );
	$ids   = array_column( $items, 'id' );

	foreach ( [ $like['id'], $draft['id'], $publish['id'], $future ] as $id ) {
		assert_true( in_array( $id, $ids, true ), "Item $id is listed" );
	}

	assert_true( ! in_array( $pending, $ids, true ), 'Pending notes are not listed' );

	$dates  = array_column( $items, 'date' );
	$sorted = $dates;
	rsort( $sorted );
	assert_same( $sorted, $dates, 'Newest first' );
	assert_same( $future, $items[0]['id'], 'The scheduled note is newest' );

	$by_id = array_column( $items, null, 'id' );

	$note = $by_id[ $draft['id'] ];
	assert_same( 'note', $note['type'] );
	assert_same( 'draft', $note['status'] );
	assert_same( 'A draft in Recent with two lines', $note['text'] );
	assert_same( true, $note['editable'] );
	assert_same( home_url( '/?post_type=shortnote&p=' . $draft['id'] ), $note['link'] );
	assert_same( admin_url( 'post.php?post=' . $draft['id'] . '&action=edit' ), $note['edit_link'] );
	assert_true( 1 === preg_match( '/^\d{4}-\d\d-\d\dT\d\d:\d\d:\d\d\+00:00$/', $note['date'] ), 'Dates are ISO 8601 in UTC: ' . $note['date'] );
	assert_same( 'future', $by_id[ $future ]['status'] );
	assert_same( false, $by_id[ $publish['id'] ]['editable'] );

	$liked = $by_id[ $like['id'] ];
	assert_same( 'like', $liked['type'] );
	assert_same( 'publish', $liked['status'] );
	assert_same( 'Recent like', $liked['text'] );
	assert_same( $like['url'], $liked['url'] );
	assert_same( $like['link'], $liked['link'] );
	assert_true( ! isset( $liked['editable'] ), 'Likes have no editable flag' );
}

/**
 * per_page defaults to 20 and is clamped to 1 to 50.
 */
function test_recent_per_page(): void {
	for ( $i = 0; $i < 52; $i++ ) {
		wp_insert_post(
			[
				'post_type'    => 'shortnote',
				'post_status'  => 'publish',
				'post_content' => "<!-- wp:paragraph --><p>Bulk $i</p><!-- /wp:paragraph -->",
			]
		);
	}

	assert_same( 20, count( data( rest( 'GET', '/recent' ) ) ) );
	assert_same( 3, count( data( rest( 'GET', '/recent', null, 1, [ 'per_page' => 3 ] ) ) ) );
	assert_same( 50, count( data( rest( 'GET', '/recent', null, 1, [ 'per_page' => 500 ] ) ) ) );
	assert_same( 1, count( data( rest( 'GET', '/recent', null, 1, [ 'per_page' => 0 ] ) ) ) );
}

/**
 * Text lines are plain, at most 140 characters, and fall back sensibly.
 */
function test_recent_text_lines(): void {
	$long  = data( rest( 'POST', '/notes', [ 'content' => str_repeat( 'Word & <b>more</b> ', 20 ) ] ) );
	$embed = data( rest( 'POST', '/notes', [ 'content' => 'https://www.youtube.com/watch?v=abc' ] ) );
	$image = data(
		rest(
			'POST',
			'/notes',
			[
				'media'  => [ [ 'id' => make_attachment() ] ],
				'status' => 'publish',
			]
		)
	);

	$by_id = array_column( data( rest( 'GET', '/recent', null, 1, [ 'per_page' => 50 ] ) ), null, 'id' );

	$text = $by_id[ $long['id'] ]['text'];
	assert_same( 140, mb_strlen( $text ) );
	assert_same( 0, strpos( $text, 'Word & <b>more</b> Word' ), 'Typed HTML is shown as text, decoded once' );
	assert_same( '…', mb_substr( $text, -1 ) );

	assert_same( 'https://www.youtube.com/watch?v=abc', $by_id[ $embed['id'] ]['text'], 'An embed shows its URL' );
	assert_same( 0, strpos( $by_id[ $image['id'] ]['text'], 'Note: Image posted on ' ), 'An image-only note shows its title' );
}
