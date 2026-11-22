<?php
/**
 * Keep private responses out of the page cache.
 *
 * Batcache decides whether to cache a GET response before WordPress knows
 * who the user is. It skips requests with a WordPress cookie, an
 * `X-WP-Nonce` header or an `Origin` header, but it does not look at the
 * `Authorization` header, and it caches 4xx responses. WriteNow and the
 * Shortcuts authenticate with an Application Password over Basic auth and
 * omit cookies, so without this file an authenticated GET such as
 * `/wp-json/writenow/v1/recent` could be stored and served to anyone who
 * requests the same URL, and an anonymous 401 could be served to Jeremy.
 *
 * Batcache only stores a response when the request finishes without
 * `batcache_cancel()` having been called, so cancelling here, during the
 * request that generates the response, means it is never stored and so
 * never served. Cache-Control headers are sent as well for any proxy or
 * browser cache in between.
 *
 * @package jeremyfelt
 */

namespace JeremyFelt\Apps\WriteNow\Cache;

use WP_REST_Request;
use WP_REST_Response;

use const JeremyFelt\Apps\WriteNow\REST_NAMESPACE;

add_action( 'application_password_did_authenticate', __NAMESPACE__ . '\cancel_page_cache' );
add_filter( 'rest_pre_dispatch', __NAMESPACE__ . '\filter_rest_pre_dispatch', 0, 3 );
add_filter( 'rest_post_dispatch', __NAMESPACE__ . '\filter_rest_post_dispatch', 999, 3 );

/**
 * Tell Batcache not to store the response to the current request.
 */
function cancel_page_cache(): void {
	if ( function_exists( 'batcache_cancel' ) ) {
		batcache_cancel();
	}
}

/**
 * Determine whether a REST route belongs to WriteNow.
 *
 * @param string $route The REST route, such as `/writenow/v1/status`.
 * @return bool True for WriteNow routes.
 */
function is_writenow_route( string $route ): bool {
	return 0 === strpos( $route, '/' . REST_NAMESPACE . '/' ) || '/' . REST_NAMESPACE === $route;
}

/**
 * Determine whether a REST response must stay out of shared caches.
 *
 * That is every WriteNow response, success or error, every response to a
 * request that carried credentials, and every 401 or 403, whatever the
 * route, so that a refusal meant for an anonymous request is never served
 * to an authenticated one.
 *
 * @param WP_REST_Request $request The request.
 * @param int             $status  The response status, or 0 if not yet known.
 * @return bool True if the response is private.
 */
function is_private_response( WP_REST_Request $request, int $status ): bool {
	return is_writenow_route( $request->get_route() )
		|| null !== $request->get_header( 'authorization' )
		|| is_user_logged_in()
		|| 401 === $status
		|| 403 === $status;
}

/**
 * Cancel the page cache as early as possible for WriteNow requests.
 *
 * Doing this before dispatch means even a fatal error in a callback cannot
 * leave a cacheable response behind.
 *
 * @param mixed           $result  The pre-dispatch result, passed through.
 * @param \WP_REST_Server $server  The REST server.
 * @param WP_REST_Request $request The request.
 * @return mixed The unchanged result.
 */
function filter_rest_pre_dispatch( $result, $server, $request ) {
	if ( $request instanceof WP_REST_Request && is_private_response( $request, 0 ) ) {
		cancel_page_cache();
	}

	return $result;
}

/**
 * Send no-cache headers on private REST responses.
 *
 * @param mixed           $response The response.
 * @param \WP_REST_Server $server   The REST server.
 * @param WP_REST_Request $request  The request.
 * @return mixed The response, with headers added when it is private.
 */
function filter_rest_post_dispatch( $response, $server, $request ) {
	if ( ! $response instanceof WP_REST_Response || ! $request instanceof WP_REST_Request ) {
		return $response;
	}

	if ( ! is_private_response( $request, $response->get_status() ) ) {
		return $response;
	}

	cancel_page_cache();

	foreach ( wp_get_nocache_headers() as $name => $value ) {
		if ( '' !== $value && false !== $value ) {
			$response->header( $name, (string) $value );
		}
	}

	// A response that depends on the Authorization header must say so.
	$response->header( 'Vary', 'Authorization', false );

	return $response;
}
