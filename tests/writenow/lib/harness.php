<?php
/**
 * A deliberately small test harness: assertions, an in-process REST client
 * and an HTTP client for the local test server.
 *
 * Tests are functions named `test_*` in the
 * `JeremyFelt\Apps\WriteNow\Tests` namespace. A test passes when it returns
 * without throwing.
 *
 * @package jeremyfelt
 */

namespace JeremyFelt\Apps\WriteNow\Tests;

use WP_REST_Request;
use WP_REST_Response;

require_once __DIR__ . '/class-assertion-failed.php';

// The HTTP client talks to the local server with cURL directly, so the tests see raw
// responses and headers without WordPress's HTTP API or the test site's network block.
// phpcs:disable WordPress.WP.AlternativeFunctions.curl_curl_init, WordPress.WP.AlternativeFunctions.curl_curl_setopt_array, WordPress.WP.AlternativeFunctions.curl_curl_setopt, WordPress.WP.AlternativeFunctions.curl_curl_exec, WordPress.WP.AlternativeFunctions.curl_curl_error, WordPress.WP.AlternativeFunctions.curl_curl_getinfo

/**
 * Fail with a message.
 *
 * @param string $message What went wrong.
 * @throws Assertion_Failed Always.
 */
function fail( string $message ): void {
	throw new Assertion_Failed( $message ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
}

/**
 * Describe a value for a failure message.
 *
 * @param mixed $value Any value.
 * @return string A readable representation.
 */
function show( $value ): string {
	return is_string( $value ) ? '"' . $value . '"' : (string) wp_json_encode( $value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
}

/**
 * Assert two values are identical.
 *
 * @param mixed  $expected The expected value.
 * @param mixed  $actual   The actual value.
 * @param string $message  What is being checked.
 */
function assert_same( $expected, $actual, string $message = '' ): void {
	if ( $expected !== $actual ) {
		fail( ( '' === $message ? 'Values differ' : $message ) . "\nExpected: " . show( $expected ) . "\nActual:   " . show( $actual ) );
	}
}

/**
 * Assert a condition holds.
 *
 * @param bool   $condition The condition.
 * @param string $message   What is being checked.
 */
function assert_true( bool $condition, string $message ): void {
	if ( ! $condition ) {
		fail( $message );
	}
}

/**
 * Assert a string contains another.
 *
 * @param string $needle   The expected substring.
 * @param string $haystack The string.
 * @param string $message  What is being checked.
 */
function assert_contains( string $needle, string $haystack, string $message = '' ): void {
	if ( false === strpos( $haystack, $needle ) ) {
		fail( ( '' === $message ? 'Substring not found' : $message ) . "\nLooking for: " . show( $needle ) . "\nIn: " . show( $haystack ) );
	}
}

/**
 * Assert a string does not contain another.
 *
 * @param string $needle   The unexpected substring.
 * @param string $haystack The string.
 * @param string $message  What is being checked.
 */
function assert_not_contains( string $needle, string $haystack, string $message = '' ): void {
	if ( false !== strpos( $haystack, $needle ) ) {
		fail( ( '' === $message ? 'Unexpected substring found' : $message ) . "\nFound: " . show( $needle ) . "\nIn: " . show( $haystack ) );
	}
}

/**
 * Dispatch a WriteNow REST request in this process, as a given user.
 *
 * @param string     $method  The HTTP method.
 * @param string     $route   The route below `/writenow/v1`, such as `/notes`.
 * @param array|null $body    A JSON body, or null for none.
 * @param int        $user_id The user to act as, 0 for logged out.
 * @param array      $query   Query parameters.
 * @return WP_REST_Response The response.
 */
function rest( string $method, string $route, ?array $body = null, int $user_id = 1, array $query = [] ): WP_REST_Response {
	wp_set_current_user( $user_id );

	$request = new WP_REST_Request( $method, '/writenow/v1' . $route );

	if ( null !== $body ) {
		$request->set_header( 'content-type', 'application/json' );
		$request->set_body( (string) wp_json_encode( $body ) );
	}

	$request->set_query_params( $query );

	$response = rest_do_request( $request );

	wp_set_current_user( 0 );

	return $response;
}

/**
 * Read a response's data as an array.
 *
 * @param WP_REST_Response $response The response.
 * @return array The data.
 */
function data( WP_REST_Response $response ): array {
	return (array) rest_get_server()->response_to_data( $response, false );
}

/**
 * Assert a REST response has a status, and optionally an error code.
 *
 * @param int              $status   The expected status.
 * @param WP_REST_Response $response The response.
 * @param string           $code     The expected error code, if any.
 */
function assert_status( int $status, WP_REST_Response $response, string $code = '' ): void {
	$data = data( $response );

	if ( $status !== $response->get_status() ) {
		fail( "Expected status $status, got " . $response->get_status() . ': ' . show( $data ) );
	}

	if ( '' !== $code ) {
		assert_same( $code, $data['code'] ?? null, 'Error code' );
	}
}

/**
 * Make an HTTP request to the local test server.
 *
 * @param string      $method  The HTTP method.
 * @param string      $path    The path, such as `/wp-json/writenow/v1/status`.
 * @param array       $headers Request headers as name => value.
 * @param string|null $body    The request body.
 * @return array `status`, `headers` (lowercase name => list of values), `body` and `json`.
 */
function http( string $method, string $path, array $headers = [], ?string $body = null ): array {
	$url    = rtrim( (string) getenv( 'WRITENOW_TEST_URL' ), '/' ) . $path;
	$handle = curl_init( $url );
	$lines  = [];

	foreach ( $headers as $name => $value ) {
		$lines[] = $name . ': ' . $value;
	}

	$received = [];

	curl_setopt_array(
		$handle,
		[
			CURLOPT_CUSTOMREQUEST  => $method,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER     => $lines,
			CURLOPT_TIMEOUT        => 30,
			CURLOPT_NOBODY         => 'HEAD' === $method,
			CURLOPT_HEADERFUNCTION => function ( $curl, $line ) use ( &$received ) {
				unset( $curl );
				$parts = explode( ':', $line, 2 );

				if ( 2 === count( $parts ) ) {
					$received[ strtolower( trim( $parts[0] ) ) ][] = trim( $parts[1] );
				}

				return strlen( $line );
			},
		]
	);

	if ( null !== $body ) {
		curl_setopt( $handle, CURLOPT_POSTFIELDS, $body );
	}

	$response = curl_exec( $handle );

	if ( false === $response ) {
		fail( 'HTTP request failed: ' . curl_error( $handle ) );
	}

	$status = (int) curl_getinfo( $handle, CURLINFO_RESPONSE_CODE );
	$json   = json_decode( (string) $response, true );

	return [
		'status'  => $status,
		'headers' => $received,
		'body'    => (string) $response,
		'json'    => is_array( $json ) ? $json : null,
	];
}

/**
 * Call a function from the Shortnotes plugin.
 *
 * Shortnotes lives in a sibling checkout outside the paths static analysis
 * reads, so the tests reach it by name, and fail clearly if it is missing.
 *
 * @param string $name     A function name relative to the `ShortNotes` namespace, such as `PostType\Note\transform_content`.
 * @param mixed  ...$args  Arguments.
 * @return mixed The function's return value.
 */
function shortnotes( string $name, ...$args ) {
	$function = '\\ShortNotes\\' . $name;

	if ( ! function_exists( $function ) ) {
		fail( "Shortnotes function $function is not available. Is the plugin active?" );
	}

	return call_user_func_array( $function, $args );
}

/**
 * Call a function from the Likes plugin, which also lives in a sibling checkout.
 *
 * @param string $name    A function name relative to the `Likes` namespace.
 * @param mixed  ...$args Arguments.
 * @return mixed The function's return value.
 */
function likes( string $name, ...$args ) {
	$function = '\\Likes\\' . $name;

	if ( ! function_exists( $function ) ) {
		fail( "Likes function $function is not available. Is the plugin active?" );
	}

	return call_user_func_array( $function, $args );
}

/**
 * Read the generated test credentials.
 *
 * @param string $role `admin`, `editor`, `author`, `contributor` or `subscriber`.
 * @return array `id`, `username` and `password`.
 */
function credentials( string $role ): array {
	static $all = null;

	if ( null === $all ) {
		$all = json_decode( (string) file_get_contents( getenv( 'WRITENOW_TEST_SITE' ) . '/credentials.json' ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	}

	return $all[ $role ];
}

/**
 * Build a Basic Authorization header value for a test user.
 *
 * @param string $role The user's role.
 * @return string The header value.
 */
function basic_auth( string $role ): string {
	$credentials = credentials( $role );

	return 'Basic ' . base64_encode( $credentials['username'] . ':' . $credentials['password'] ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
}

/**
 * Provide a header's first value from an http() response.
 *
 * @param array  $response The response from http().
 * @param string $name     The header name, lowercase.
 * @return string The value, or an empty string.
 */
function header_value( array $response, string $name ): string {
	return $response['headers'][ $name ][0] ?? '';
}
