<?php
/**
 * Tests for POST /likes.
 *
 * Outbound HTTP on the test site is answered by support/test-helpers.php:
 * hosts under `titles.test` return canned pages and everything else fails.
 *
 * @package jeremyfelt
 */

namespace JeremyFelt\Apps\WriteNow\Tests;

use function JeremyFelt\Apps\WriteNow\Likes\strip_tracking_parameters;

/**
 * A unique URL for a test.
 *
 * @param string $base The URL to make unique.
 * @return string The URL with a unique path segment.
 */
function unique_url( string $base = 'https://example.com/' ): string {
	return $base . 'post-' . wp_generate_password( 8, false, false );
}

/**
 * A like is stored the way the Likes meta box stores one, and renders.
 */
function test_like_happy_path(): void {
	$url        = unique_url();
	$seen       = [];
	$on_publish = function ( $post_id ) use ( &$seen ) {
		$seen[] = get_post_meta( $post_id, 'like_of_url', true );
	};

	add_action( 'publish_like', $on_publish );
	$response = rest(
		'POST',
		'/likes',
		[
			'url'   => "  $url  ",
			'title' => 'A <b>post</b> title',
		]
	);
	remove_action( 'publish_like', $on_publish );

	assert_status( 201, $response );
	$data = data( $response );
	$post = get_post( $data['id'] );

	assert_same( $url, $data['url'] );
	assert_same( 'A post title', $data['title'], 'The supplied title is sanitized' );
	assert_same( false, $data['duplicate'] );
	assert_same( 'Liked: A post title', $data['message'] );
	assert_same( 'like', $post->post_type );
	assert_same( 'publish', $post->post_status );
	assert_same( 'A post title', $post->post_title );
	assert_same( 'a-post-title', substr( $post->post_name, 0, 12 ) );
	assert_same( get_permalink( $post ), $data['link'] );
	assert_contains( '/liked/a-post-title', $data['link'] );
	assert_same( $url, get_post_meta( $post->ID, 'like_of_url', true ) );
	assert_same( [ $url ], $seen, 'like_of_url was saved before the publish hooks ran' );
	assert_same( $url, likes( 'PostType\Like\get_like_url', $post->ID ), 'The Likes plugin reads the URL' );

	// The Likes plugin renders the like and adds its URL to webmentions.
	$GLOBALS['post'] = $post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	setup_postdata( $post );
	$content = apply_filters( 'the_content', '' );
	wp_reset_postdata();

	assert_contains( '<a class="u-like-of" href="' . $url . '">A post title</a>', $content );
	assert_contains( '<span class="like-of-domain">example.com</span>', $content );
	assert_contains( $url, implode( ' ', apply_filters( 'webmention_links', [], $post->ID ) ) );
}

/**
 * Tracking parameters are removed and everything else is kept exactly.
 */
function test_tracking_parameters_are_stripped(): void {
	$cases = [
		'https://example.com/p?utm_source=x&id=5&fbclid=abc&UTM_Medium=y#frag' => 'https://example.com/p?id=5#frag',
		'https://example.com/p?utm_source=x&gclid=1&mc_cid=2&mc_eid=3&igshid=4' => 'https://example.com/p',
		'https://example.com/p?a=1&b=%20two&c'            => 'https://example.com/p?a=1&b=%20two&c',
		'https://example.com/p?utm=keep&utmost=keep'      => 'https://example.com/p?utm=keep&utmost=keep',
		'https://example.com/p#utm_source=fragment-stays' => 'https://example.com/p#utm_source=fragment-stays',
		'https://example.com/p?&&a=1&utm_x=2&'            => 'https://example.com/p?a=1',
		'https://example.com/'                            => 'https://example.com/',
	];

	foreach ( $cases as $input => $expected ) {
		assert_same( $expected, strip_tracking_parameters( $input ), "Stripping $input" );
	}

	$filter = fn( $parameters ) => array_merge( $parameters, [ 'ref' ] );
	add_filter( 'writenow_tracking_parameters', $filter );
	assert_same( 'https://example.com/p?id=1', strip_tracking_parameters( 'https://example.com/p?ref=hn&id=1' ), 'The list is filterable' );
	remove_filter( 'writenow_tracking_parameters', $filter );

	$url  = unique_url();
	$data = data(
		rest(
			'POST',
			'/likes',
			[
				'url'   => $url . '?utm_source=share&keep=1',
				'title' => 'Tracked',
			]
		)
	);
	assert_same( $url . '?keep=1', $data['url'] );
	assert_same( $url . '?keep=1', get_post_meta( $data['id'], 'like_of_url', true ) );
}

/**
 * Liking the same URL again returns the existing like.
 */
function test_like_dedupe(): void {
	$url   = unique_url();
	$first = rest(
		'POST',
		'/likes',
		[
			'url'   => $url,
			'title' => 'Once',
		]
	);
	assert_status( 201, $first );

	$again = rest( 'POST', '/likes', [ 'url' => $url . '?utm_campaign=again' ] );
	assert_status( 200, $again );
	assert_same( data( $first )['id'], data( $again )['id'] );
	assert_same( true, data( $again )['duplicate'] );
	assert_same( 'Already liked: Once', data( $again )['message'] );

	$likes = get_posts(
		[
			'post_type'   => 'like',
			'meta_key'    => 'like_of_url', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'  => $url, // phpcs:ignore WordPress.DB.SlowDBQuery
			'numberposts' => -1,
		]
	);
	assert_same( 1, count( $likes ), 'Exactly one like' );
}

/**
 * Likes made through Micropub, which stores mf2_like-of, count as duplicates.
 */
function test_like_dedupe_matches_micropub_likes(): void {
	foreach ( [ 'array', 'string' ] as $shape ) {
		$url     = unique_url();
		$like_id = wp_insert_post(
			[
				'post_type'   => 'like',
				'post_status' => 'publish',
				'post_title'  => 'From Micropub',
			]
		);
		add_post_meta( $like_id, 'mf2_like-of', 'array' === $shape ? [ $url ] : $url );

		$response = rest( 'POST', '/likes', [ 'url' => $url ] );
		assert_status( 200, $response );
		assert_same( $like_id, data( $response )['id'], "Matched a Micropub like stored as $shape" );
		assert_same( $url, data( $response )['url'] );
	}
}

/**
 * Only published likes count as duplicates.
 */
function test_unpublished_likes_are_not_duplicates(): void {
	$url = unique_url();
	wp_insert_post(
		[
			'post_type'   => 'like',
			'post_status' => 'draft',
			'post_title'  => 'Draft like',
			'meta_input'  => [ 'like_of_url' => $url ],
		]
	);

	assert_status(
		201,
		rest(
			'POST',
			'/likes',
			[
				'url'   => $url,
				'title' => 'New',
			]
		)
	);
}

/**
 * Without a title, the page's og:title or title is used, and any failure falls back to the URL.
 */
function test_like_title_lookup_and_fallback(): void {
	$cases = [
		'https://titles.test/og'        => 'An & OG title',
		'https://titles.test/title'     => 'Just a title — here',
		'https://titles.test/latin1'    => 'Café',
		'https://titles.test/nocharset' => 'Naïve',
		'https://titles.test/notitle'   => 'https://titles.test/notitle',
		'https://titles.test/notfound'  => 'https://titles.test/notfound',
		'https://pdf.test/file.pdf'     => 'https://pdf.test/file.pdf',
		'https://offline.example/'      => 'https://offline.example/',
	];

	foreach ( $cases as $url => $title ) {
		$response = rest( 'POST', '/likes', [ 'url' => $url ] );
		assert_status( 201, $response );
		assert_same( $title, data( $response )['title'], "Title for $url" );
		assert_same( $title, html_entity_decode( get_post( data( $response )['id'] )->post_title, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
	}

	$long = data( rest( 'POST', '/likes', [ 'url' => 'https://titles.test/long' ] ) );
	assert_same( 200, mb_strlen( $long['title'] ), 'Long titles are cut to 200 characters' );
	assert_same( '…', mb_substr( $long['title'], -1 ) );
}

/**
 * The title lookup uses a safe request with a short timeout and a size cap.
 */
function test_like_title_request_arguments(): void {
	$captured = [];
	$capture  = function ( $response, $args, $url ) use ( &$captured ) {
		$captured[] = [ $url, $args ];
		return new \WP_Error( 'http_request_failed', 'Simulated failure' );
	};

	add_filter( 'pre_http_request', $capture, 0, 3 );
	$url      = unique_url( 'https://somewhere.example/' );
	$response = rest( 'POST', '/likes', [ 'url' => $url ] );
	remove_filter( 'pre_http_request', $capture, 0 );

	// A failed lookup never fails the like.
	assert_status( 201, $response );
	assert_same( $url, data( $response )['title'] );
	assert_same( 1, count( $captured ) );
	assert_same( $url, $captured[0][0] );
	assert_same( 5, $captured[0][1]['timeout'] );
	assert_same( true, $captured[0][1]['reject_unsafe_urls'], 'wp_safe_remote_get() is used' );
	assert_true( $captured[0][1]['limit_response_size'] > 0 && $captured[0][1]['limit_response_size'] <= 1048576, 'The response size is capped' );

	// A supplied title means no lookup at all.
	$captured = [];
	add_filter( 'pre_http_request', $capture, 0, 3 );
	rest(
		'POST',
		'/likes',
		[
			'url'   => unique_url(),
			'title' => 'Given',
		]
	);
	remove_filter( 'pre_http_request', $capture, 0 );
	assert_same( [], $captured );
}

/**
 * Anything that is not an absolute http(s) URL is refused.
 */
function test_invalid_like_urls(): void {
	foreach ( [ '', '   ', 'not a url', 'example.com/page', 'ftp://example.com/', 'javascript:alert(1)', 'https://', 'http:///path', 'https://exa mple.com/' ] as $url ) {
		$response = rest( 'POST', '/likes', [ 'url' => $url ] );
		assert_status( 400, $response, 'writenow_invalid_url' );
		assert_contains( 'http', data( $response )['message'] );
	}

	assert_status( 400, rest( 'POST', '/likes', [] ), 'rest_missing_callback_param' );
}

/**
 * Insert a published post with one meta value straight into the database,
 * as another PHP process would, without touching this process's caches.
 *
 * @param string $post_type The post type.
 * @param string $meta_key  The meta key.
 * @param string $value     The meta value.
 * @return int The post ID.
 */
function insert_behind_the_cache( string $post_type, string $meta_key, string $value ): int {
	global $wpdb;

	$now = current_time( 'mysql' );
	// phpcs:disable WordPress.DB.DirectDatabaseQuery
	$wpdb->insert(
		$wpdb->posts,
		[
			'post_type'             => $post_type,
			'post_status'           => 'publish',
			'post_title'            => 'Made elsewhere',
			'post_content'          => '',
			'post_excerpt'          => '',
			'to_ping'               => '',
			'pinged'                => '',
			'post_content_filtered' => '',
			'post_date'             => $now,
			'post_date_gmt'         => get_gmt_from_date( $now ),
			'post_modified'         => $now,
			'post_modified_gmt'     => get_gmt_from_date( $now ),
		]
	);
	$id = (int) $wpdb->insert_id;
	$wpdb->insert(
		$wpdb->postmeta,
		[
			'post_id'    => $id,
			'meta_key'   => $meta_key, // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value' => $value, // phpcs:ignore WordPress.DB.SlowDBQuery
		]
	);
	// phpcs:enable

	return $id;
}

/**
 * The duplicate checks read the database, not a cached query result. Another
 * request may have created the post since this one last looked.
 */
function test_duplicate_checks_are_not_fooled_by_the_query_cache(): void {
	$url = unique_url();
	assert_same( null, \JeremyFelt\Apps\WriteNow\Likes\find_like( [ $url ] ), 'No like yet, and the empty result is now cached' );
	$like = insert_behind_the_cache( 'like', 'like_of_url', $url );
	assert_same( $like, \JeremyFelt\Apps\WriteNow\Likes\find_like( [ $url ] )->ID ?? null, 'The like made elsewhere is found' );

	$client_id = wp_generate_uuid4();
	assert_same( null, \JeremyFelt\Apps\WriteNow\Notes\find_note_by_client_id( $client_id ) );
	$note = insert_behind_the_cache( 'shortnote', '_writenow_client_id', $client_id );
	assert_same( $note, \JeremyFelt\Apps\WriteNow\Notes\find_note_by_client_id( $client_id )->ID ?? null, 'The note made elsewhere is found' );
}
