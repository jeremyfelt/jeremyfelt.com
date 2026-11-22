<?php
/**
 * Tests for GET /status and the permission rules every route shares.
 *
 * @package jeremyfelt
 */

namespace JeremyFelt\Apps\WriteNow\Tests;

/**
 * The real plugins are active, so both features are on.
 */
function test_status_reports_user_site_and_features(): void {
	$response = rest( 'GET', '/status' );
	assert_status( 200, $response );

	$data = data( $response );
	assert_same( 1, $data['user']['id'] );
	assert_same( 'admin', $data['user']['name'] );
	assert_same( 'WriteNow Test Site', $data['site']['name'] );
	assert_same( home_url(), $data['site']['url'] );
	assert_same(
		[
			'notes' => true,
			'likes' => true,
		],
		$data['features']
	);
	assert_same( 500, $data['note_soft_limit'] );
	assert_same( '1.0.0', $data['version'] );
}

/**
 * The plugins under test are the sibling checkouts, not copies.
 */
function test_real_plugins_are_active(): void {
	assert_true( function_exists( '\ShortNotes\PostType\Note\transform_content' ), 'Shortnotes is loaded' );
	assert_true( function_exists( '\Likes\PostType\Like\get_like_url' ), 'Likes is loaded' );
	assert_same( realpath( dirname( __DIR__, 4 ) . '/shortnotes/plugin.php' ), realpath( WP_PLUGIN_DIR . '/shortnotes/plugin.php' ), 'Shortnotes comes from the sibling checkout' );
	assert_same( realpath( dirname( __DIR__, 4 ) . '/likes/plugin.php' ), realpath( WP_PLUGIN_DIR . '/likes/plugin.php' ), 'Likes comes from the sibling checkout' );
	assert_true( post_type_exists( 'shortnote' ) && post_type_exists( 'like' ), 'Both post types are registered' );
}

/**
 * Every route refuses a logged-out request with 401.
 */
function test_every_route_requires_authentication(): void {
	$routes = [
		[ 'GET', '/status', null ],
		[ 'GET', '/recent', null ],
		[ 'POST', '/likes', [ 'url' => 'https://example.com/' ] ],
		[ 'POST', '/notes', [ 'content' => 'Hello' ] ],
		[ 'GET', '/notes/1', null ],
		[ 'POST', '/notes/1', [ 'content' => 'Hello' ] ],
	];

	foreach ( $routes as $route ) {
		$response = rest( $route[0], $route[1], $route[2], 0 );
		assert_status( 401, $response );
		assert_true( '' !== ( data( $response )['message'] ?? '' ), 'A message is included for ' . $route[1] );
	}
}

/**
 * A subscriber can authenticate but cannot use any route.
 */
function test_subscriber_is_refused_everywhere(): void {
	$subscriber = credentials( 'subscriber' )['id'];

	assert_status( 403, rest( 'GET', '/status', null, $subscriber ), 'writenow_forbidden' );
	assert_status( 403, rest( 'GET', '/recent', null, $subscriber ), 'writenow_forbidden' );
	assert_status( 403, rest( 'POST', '/likes', [ 'url' => 'https://example.com/sub' ], $subscriber ), 'writenow_forbidden' );
	assert_status( 403, rest( 'POST', '/notes', [ 'content' => 'Hi' ], $subscriber ), 'writenow_forbidden' );
}

/**
 * A contributor may save a note as a draft but not publish one or like.
 */
function test_contributor_can_draft_but_not_publish(): void {
	$contributor = credentials( 'contributor' )['id'];

	assert_status( 201, rest( 'POST', '/notes', [ 'content' => 'A contributor draft' ], $contributor ) );
	assert_status(
		403,
		rest(
			'POST',
			'/notes',
			[
				'content' => 'A contributor post',
				'status'  => 'publish',
			],
			$contributor
		),
		'writenow_forbidden'
	);
	assert_status( 403, rest( 'POST', '/likes', [ 'url' => 'https://example.com/contrib' ], $contributor ), 'writenow_forbidden' );
}

/**
 * An author cannot read or edit another user's draft.
 */
function test_author_cannot_edit_someone_elses_note(): void {
	$draft  = data( rest( 'POST', '/notes', [ 'content' => 'Admin draft' ] ) );
	$author = credentials( 'author' )['id'];

	assert_status( 403, rest( 'GET', '/notes/' . $draft['id'], null, $author ), 'writenow_forbidden' );
	assert_status( 403, rest( 'POST', '/notes/' . $draft['id'], [ 'content' => 'Hijacked' ], $author ), 'writenow_forbidden' );
	assert_same( 'Admin draft', get_post_meta( $draft['id'], '_markdown_content', true ), 'The draft is unchanged' );

	$recent = data( rest( 'GET', '/recent', null, $author ) );
	assert_same( [], array_values( array_filter( $recent, fn( $item ) => $item['id'] === $draft['id'] ) ), 'Another user\'s draft is not in Recent' );
}

/**
 * Routes for a missing post type report 501 and the feature as off.
 */
function test_missing_post_types_return_501(): void {
	global $wp_post_types;

	$saved = [ $wp_post_types['shortnote'], $wp_post_types['like'] ];
	unset( $wp_post_types['shortnote'], $wp_post_types['like'] );

	try {
		$status = data( rest( 'GET', '/status' ) );
		assert_same(
			[
				'notes' => false,
				'likes' => false,
			],
			$status['features']
		);

		assert_status( 501, rest( 'POST', '/notes', [ 'content' => 'x' ] ), 'writenow_feature_unavailable' );
		assert_status( 501, rest( 'POST', '/likes', [ 'url' => 'https://example.com/' ] ), 'writenow_feature_unavailable' );
		assert_status( 501, rest( 'GET', '/notes/1' ), 'writenow_feature_unavailable' );
		assert_status( 200, rest( 'GET', '/recent' ) );
		assert_same( [], data( rest( 'GET', '/recent' ) ) );
	} finally {
		$wp_post_types['shortnote'] = $saved[0];
		$wp_post_types['like']      = $saved[1];
	}
}
