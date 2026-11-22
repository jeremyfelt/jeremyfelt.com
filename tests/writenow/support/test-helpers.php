<?php
/**
 * Plugin Name: WriteNow test helpers
 * Description: Test-only. Answers outbound HTTP requests locally so the test site never touches the network.
 *
 * Requests to hosts under `.test` get canned responses that the title lookup
 * tests rely on. Every other outbound request fails, as if offline. This
 * plugin exists only on the local test site built by tests/writenow/run.sh.
 *
 * @package jeremyfelt
 */

namespace JeremyFelt\Apps\WriteNow\Tests\Helpers;

add_filter( 'pre_http_request', __NAMESPACE__ . '\answer_locally', 1, 3 );
add_action( 'admin_init', __NAMESPACE__ . '\skip_update_checks', 0 );
add_action( 'transition_post_status', __NAMESPACE__ . '\record_transition', 10, 3 );
add_action( 'publish_shortnote', __NAMESPACE__ . '\record_publish_hook' );
add_filter( 'wp_insert_post_data', __NAMESPACE__ . '\end_request_on_marker', 999 );

/**
 * End the request part way through saving a note whose content carries a
 * marker, the way a fatal error, a time limit or a plugin calling exit
 * would. `finally` blocks do not run; shutdown functions do.
 *
 * @param array $data Post data about to be saved.
 * @return array The unchanged data.
 */
function end_request_on_marker( $data ) {
	if ( is_array( $data ) && false !== strpos( (string) ( $data['post_content'] ?? '' ), 'WRITENOW-TEST-EXIT' ) ) {
		exit;
	}

	return $data;
}

/**
 * Record every status change of a note or like, so the end-to-end tests can
 * check that publishing fired once, from draft, as it does in wp-admin.
 *
 * @param string   $new_status The new status.
 * @param string   $old_status The old status.
 * @param \WP_Post $post       The post.
 */
function record_transition( $new_status, $old_status, $post ): void {
	if ( ! $post instanceof \WP_Post || ! in_array( $post->post_type, [ 'shortnote', 'like' ], true ) ) {
		return;
	}

	$all                = (array) get_option( 'writenow_test_transitions', [] );
	$all[ $post->ID ][] = $old_status . '>' . $new_status;
	update_option( 'writenow_test_transitions', $all, false );
}

/**
 * Record each run of the `publish_shortnote` hook, which is what Shortnotes
 * uses to schedule webmentions.
 *
 * @param int $post_id The note.
 */
function record_publish_hook( $post_id ): void {
	$all                     = (array) get_option( 'writenow_test_transitions', [] );
	$all[ (int) $post_id ][] = 'publish_shortnote';
	update_option( 'writenow_test_transitions', $all, false );
}

/**
 * Skip wp-admin's update checks, which would only fail offline and log warnings.
 */
function skip_update_checks(): void {
	remove_action( 'admin_init', '_maybe_update_core' );
	remove_action( 'admin_init', '_maybe_update_plugins' );
	remove_action( 'admin_init', '_maybe_update_themes' );
}

/**
 * Answer an outbound HTTP request without the network.
 *
 * @param false|array|\WP_Error $response A preempted response.
 * @param array                 $args     Request arguments.
 * @param string                $url      The request URL.
 * @return array|\WP_Error A canned response, or an error.
 */
function answer_locally( $response, $args, $url ) {
	unset( $args );

	if ( false !== $response ) {
		return $response;
	}

	$host = (string) wp_parse_url( $url, PHP_URL_HOST );
	$path = (string) wp_parse_url( $url, PHP_URL_PATH );

	if ( 'titles.test' === $host ) {
		$pages = [
			'/og'        => '<html><head><title>The HTML title</title><meta content="An &amp; OG title" property="og:title"></head><body></body></html>',
			'/title'     => "<html><head><title>\n  Just a   <b>title</b> &mdash; here\n</title></head></html>",
			'/notitle'   => '<html><head></head><body>Nothing</body></html>',
			'/long'      => '<html><head><title>' . str_repeat( 'Long title ', 40 ) . '</title></head></html>',
			'/latin1'    => '<html><head><meta charset="iso-8859-1"><title>Caf' . "\xE9" . '</title></head></html>',
			'/nocharset' => '<html><head><title>Na' . "\xEF" . 've</title></head></html>',
			'/notfound'  => '',
		];

		if ( '/notfound' === $path || ! isset( $pages[ $path ] ) ) {
			return [
				'headers'  => [ 'content-type' => 'text/html' ],
				'body'     => 'Not found',
				'response' => [
					'code'    => 404,
					'message' => 'Not Found',
				],
				'cookies'  => [],
				'filename' => null,
			];
		}

		return [
			'headers'  => [ 'content-type' => '/latin1' === $path ? 'text/html' : 'text/html; charset=utf-8' ],
			'body'     => $pages[ $path ],
			'response' => [
				'code'    => 200,
				'message' => 'OK',
			],
			'cookies'  => [],
			'filename' => null,
		];
	}

	if ( 'pdf.test' === $host ) {
		return [
			'headers'  => [ 'content-type' => 'application/pdf' ],
			'body'     => '%PDF-1.4 <title>Not a page</title>',
			'response' => [
				'code'    => 200,
				'message' => 'OK',
			],
			'cookies'  => [],
			'filename' => null,
		];
	}

	return new \WP_Error( 'writenow_test_offline', 'Outbound HTTP is disabled on the WriteNow test site.' );
}
