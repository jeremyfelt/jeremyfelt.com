<?php
/**
 * Tests over real HTTP against the local server, with Basic auth through
 * Application Passwords and the repository's Batcache in front.
 *
 * @package jeremyfelt
 */

namespace JeremyFelt\Apps\WriteNow\Tests;

/**
 * Send a JSON request to the WriteNow API.
 *
 * @param string     $method The method.
 * @param string     $route  The route below /writenow/v1.
 * @param string     $role   A test user's role, or '' for no credentials.
 * @param array|null $body   A JSON body.
 * @return array The response from http().
 */
function api( string $method, string $route, string $role = 'admin', ?array $body = null ): array {
	$headers = [ 'Accept' => 'application/json' ];

	if ( '' !== $role ) {
		$headers['Authorization'] = basic_auth( $role );
	}

	if ( null !== $body ) {
		$headers['Content-Type'] = 'application/json';
	}

	return http( $method, '/wp-json/writenow/v1' . $route, $headers, null === $body ? null : (string) wp_json_encode( $body ) );
}

/**
 * Assert a response forbids caching.
 *
 * @param array  $response The response from http().
 * @param string $what     What is being checked.
 */
function assert_not_cacheable( array $response, string $what ): void {
	$cache_control = implode( ', ', $response['headers']['cache-control'] ?? [] );

	assert_contains( 'no-store', $cache_control, "$what: Cache-Control" );
	assert_contains( 'private', $cache_control, "$what: Cache-Control" );
	assert_not_contains( 'max-age=3', $cache_control, "$what: no Batcache max-age" );
	assert_contains( 'Authorization', implode( ', ', $response['headers']['vary'] ?? [] ), "$what: Vary" );
}

/**
 * Basic auth with an Application Password works over HTTP for every route.
 */
function test_http_basic_auth_end_to_end(): void {
	$status = api( 'GET', '/status' );
	assert_same( 200, $status['status'], $status['body'] );
	assert_same( 'admin', $status['json']['user']['name'] );
	assert_not_cacheable( $status, 'GET /status' );

	$like = api(
		'POST',
		'/likes',
		'admin',
		[
			'url'   => unique_url( 'https://shortcut.example/' ),
			'title' => 'From a Shortcut',
		]
	);
	assert_same( 201, $like['status'], $like['body'] );
	assert_same( 'Liked: From a Shortcut', $like['json']['message'] );

	$note = api(
		'POST',
		'/notes',
		'admin',
		[
			'content' => 'Over **HTTP**',
			'status'  => 'publish',
		]
	);
	assert_same( 201, $note['status'], $note['body'] );
	assert_same( 'Posted.', $note['json']['message'] );

	$read = api( 'GET', '/notes/' . $note['json']['id'] );
	assert_same( 200, $read['status'] );
	assert_same( 'Over **HTTP**', $read['json']['content'] );

	// The published note is on the site, as the theme renders it.
	$page = http( 'GET', (string) wp_parse_url( $note['json']['link'], PHP_URL_PATH ) );
	assert_same( 200, $page['status'], 'The note permalink resolves' );
	assert_contains( 'Over <strong>HTTP</strong>', $page['body'] );

	$liked = http( 'GET', (string) wp_parse_url( $like['json']['link'], PHP_URL_PATH ) );
	assert_same( 200, $liked['status'], 'The like permalink resolves' );
	assert_contains( 'class="u-like-of"', $liked['body'] );
}

/**
 * Wrong or missing credentials get a 401 with a readable message, and nothing is cached.
 */
function test_http_refusals(): void {
	$anonymous = api( 'GET', '/recent', '' );
	assert_same( 401, $anonymous['status'] );
	assert_same( 'writenow_not_connected', $anonymous['json']['code'] );
	assert_not_cacheable( $anonymous, 'Anonymous GET /recent' );

	$wrong = http(
		'GET',
		'/wp-json/writenow/v1/status',
		[ 'Authorization' => 'Basic ' . base64_encode( 'admin:not the password' ) ] // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
	);
	assert_same( 401, $wrong['status'] );
	assert_not_contains( 'not the password', $wrong['body'], 'The password is not echoed' );
	assert_not_cacheable( $wrong, 'Wrong password' );

	$subscriber = api( 'POST', '/likes', 'subscriber', [ 'url' => 'https://example.com/' ] );
	assert_same( 403, $subscriber['status'] );
}

/**
 * The real Batcache never stores or serves an authenticated WriteNow response,
 * and never serves an anonymous refusal to an authenticated request.
 */
function test_http_batcache_never_caches_private_responses(): void {
	// Control: Batcache is active and caches a public page after two hits.
	$shell = [];

	for ( $i = 0; $i < 4; $i++ ) {
		$shell[] = http( 'GET', '/writenow/?batcache-control=' . getmypid() );
	}

	assert_contains( 'served from batcache', end( $shell )['body'], 'Batcache is running in this test, so the checks below mean something' );

	// Authenticated reads, enough times for Batcache to want to cache them.
	$path = '/recent?per_page=2&batcache-test=' . getmypid();

	for ( $i = 0; $i < 4; $i++ ) {
		$private = api( 'GET', $path );
		assert_same( 200, $private['status'] );
		assert_not_contains( 'batcache', $private['body'] );
	}

	$anonymous = api( 'GET', $path, '' );
	assert_same( 401, $anonymous['status'], 'An anonymous request gets a 401, not the cached list: ' . $anonymous['body'] );

	// Anonymous refusals first, then an authenticated request to the same URL.
	$path = '/status?batcache-test=' . getmypid();

	for ( $i = 0; $i < 4; $i++ ) {
		assert_same( 401, api( 'GET', $path, '' )['status'] );
	}

	assert_same( 200, api( 'GET', $path )['status'], 'An authenticated request is not served a cached 401' );

	// The same holds for core routes read with an Application Password, which the app uses.
	for ( $i = 0; $i < 4; $i++ ) {
		$me = http( 'GET', '/wp-json/wp/v2/users/me?context=edit&batcache-test=' . getmypid(), [ 'Authorization' => basic_auth( 'admin' ) ] );
		assert_same( 200, $me['status'] );
	}

	$me = http( 'GET', '/wp-json/wp/v2/users/me?context=edit&batcache-test=' . getmypid() );
	assert_same( 401, $me['status'], 'Core user data read over Basic auth is not cached for others' );

	// Nothing Batcache stored contains private data.
	$stored_files = glob( getenv( 'WRITENOW_TEST_SITE' ) . '/object-cache/*.cache' );

	foreach ( false === $stored_files ? [] : $stored_files as $file ) {
		$stored = (string) file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		assert_not_contains( 'writenow\/v1', $stored, 'No WriteNow response is stored' );
		assert_not_contains( 'admin@example.com', $stored, 'No user data is stored' );
	}
}

/**
 * Concurrent copies of the same request create exactly one post.
 */
function test_http_concurrent_duplicates_create_one_post(): void {
	$client_id = wp_generate_uuid4();
	$url       = unique_url( 'https://concurrent.example/' );
	$requests  = [];

	for ( $i = 0; $i < 4; $i++ ) {
		$requests[] = [
			'/notes',
			[
				'content'   => 'Tapped four times',
				'status'    => 'publish',
				'client_id' => $client_id,
			],
		];
		$requests[] = [
			'/likes',
			[
				'url'   => $url,
				'title' => 'Liked four times',
			],
		];
	}

	$multi   = curl_multi_init(); // phpcs:ignore WordPress.WP.AlternativeFunctions
	$handles = [];

	foreach ( $requests as $request ) {
		$handle = curl_init( getenv( 'WRITENOW_TEST_URL' ) . '/wp-json/writenow/v1' . $request[0] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_init
		curl_setopt_array( // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt_array
			$handle,
			[
				CURLOPT_POST           => true,
				CURLOPT_POSTFIELDS     => wp_json_encode( $request[1] ),
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT        => 30,
				CURLOPT_HTTPHEADER     => [ 'Content-Type: application/json', 'Authorization: ' . basic_auth( 'admin' ) ],
			]
		);
		curl_multi_add_handle( $multi, $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		$handles[] = [ $request[0], $handle ];
	}

	do {
		curl_multi_exec( $multi, $running ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		curl_multi_select( $multi ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	} while ( $running > 0 );

	$ids = [];

	foreach ( $handles as [ $route, $handle ] ) {
		$status = (int) curl_getinfo( $handle, CURLINFO_RESPONSE_CODE ); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_getinfo
		$json   = json_decode( (string) curl_multi_getcontent( $handle ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions

		assert_true( in_array( $status, [ 200, 201 ], true ), "$route answered $status: " . wp_json_encode( $json ) );
		$ids[ $route ][] = $json['id'];
	}

	curl_multi_close( $multi ); // phpcs:ignore WordPress.WP.AlternativeFunctions

	assert_same( 1, count( array_unique( $ids['/notes'] ) ), 'Every note response names the same note' );
	assert_same( 1, count( array_unique( $ids['/likes'] ) ), 'Every like response names the same like' );
	assert_same( 1, count_notes_with_client_id( $client_id ), 'Exactly one note in the database' );

	$likes = get_posts(
		[
			'post_type'   => 'like',
			'meta_key'    => 'like_of_url', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'  => $url, // phpcs:ignore WordPress.DB.SlowDBQuery
			'numberposts' => -1,
		]
	);
	assert_same( 1, count( $likes ), 'Exactly one like in the database' );
}

/**
 * The shell, manifest and service worker are served with their tokens filled.
 */
function test_http_front_end_routes(): void {
	$redirect = http( 'GET', '/writenow?like=https%3A%2F%2Fexample.com' );
	assert_same( 301, $redirect['status'] );
	assert_same( home_url( '/writenow/?like=https%3A%2F%2Fexample.com' ), header_value( $redirect, 'location' ), 'The query is kept' );

	$shell = http( 'GET', '/writenow/' );
	assert_same( 200, $shell['status'] );
	assert_same( 'text/html; charset=utf-8', header_value( $shell, 'content-type' ) );
	assert_contains( 'noindex', header_value( $shell, 'x-robots-tag' ) );
	assert_contains( "script-src 'self'", header_value( $shell, 'content-security-policy' ) );
	assert_contains( "frame-ancestors 'none'", header_value( $shell, 'content-security-policy' ) );
	assert_not_contains( 'wp-content/themes/writemore/style.css', $shell['body'], 'The theme is not loaded' );

	preg_match( '/data-asset-version="([^"]+)"/', $shell['body'], $version );
	assert_true( isset( $version[1] ) && 1 === preg_match( '/^[0-9a-f]{12}$/', $version[1] ), 'ASSET_VERSION is filled' );

	$tokens = \JeremyFelt\Apps\WriteNow\Routes\get_tokens();
	assert_contains( 'data-rest-url="' . esc_url( rest_url() ) . '"', $shell['body'] );
	assert_contains( 'data-admin-url="' . esc_url( admin_url() ) . '"', $shell['body'] );
	assert_contains( 'data-login-url="' . esc_url( wp_login_url() ) . '"', $shell['body'] );
	assert_contains( 'data-font-sans="' . esc_url( $tokens['FONT_SANS_URL'] ) . '"', $shell['body'] );
	assert_contains( 'fonts/Inter-VariableFont_slnt,wght.woff2', $tokens['FONT_SANS_URL'], 'The theme font is found' );
	assert_contains( 'href="' . esc_url( $tokens['ASSET_URL'] ) . 'app.css?ver=' . $version[1] . '"', $shell['body'] );
	assert_not_contains( '{{', preg_replace( '/<!--.*?-->/s', '', $shell['body'] ), 'Every token outside the comment is filled' );

	// Assets referenced by the shell exist at the URLs it uses.
	$css = http( 'GET', (string) wp_parse_url( $tokens['ASSET_URL'] . 'app.css', PHP_URL_PATH ) );
	assert_same( 200, $css['status'], 'app.css is served from the app directory URL' );

	// The shell is identical for everyone: no user data, nonce or secret.
	$as_admin = http( 'GET', '/writenow/', [ 'Authorization' => basic_auth( 'admin' ) ] );
	assert_same( preg_replace( '/<!--\s*generated.*$/s', '', $shell['body'] ), preg_replace( '/<!--\s*generated.*$/s', '', $as_admin['body'] ), 'Same shell with credentials' );
	assert_not_contains( 'admin@example.com', $shell['body'] );
	assert_not_contains( credentials( 'admin' )['password'], $as_admin['body'] );

	$manifest = http( 'GET', '/writenow/manifest.webmanifest' );
	assert_same( 200, $manifest['status'] );
	assert_same( 'application/manifest+json; charset=utf-8', header_value( $manifest, 'content-type' ) );
	assert_true( is_array( $manifest['json'] ), 'The manifest is valid JSON' );
	assert_same( 'WriteNow', $manifest['json']['name'] );
	assert_same( $tokens['ASSET_URL'] . 'icons/icon-192.png?ver=' . $version[1], $manifest['json']['icons'][0]['src'] );

	$worker = http( 'GET', '/writenow/sw.js' );
	assert_same( 200, $worker['status'] );
	assert_same( 'text/javascript; charset=utf-8', header_value( $worker, 'content-type' ) );
	assert_same( 'no-cache', header_value( $worker, 'cache-control' ) );
	assert_contains( 'const VERSION = "' . $version[1] . '";', $worker['body'], 'The service worker has the shell\'s version' );
	assert_contains( 'const ASSET_URL = clean( "' . $tokens['ASSET_URL'] . '" );', $worker['body'] );

	$file = getenv( 'WRITENOW_TEST_SITE' ) . '/sw-check.js';
	file_put_contents( $file, $worker['body'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
	exec( 'node --check ' . escapeshellarg( $file ) . ' 2>&1', $output, $code ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_exec
	assert_same( 0, $code, 'The filled service worker parses: ' . implode( "\n", $output ) );

	// Only GET and HEAD are served; other paths under /writenow/ are left to WordPress.
	assert_same( 404, http( 'GET', '/writenow/nothing-here' )['status'] );
	assert_same( 200, http( 'HEAD', '/writenow/' )['status'] );
}
