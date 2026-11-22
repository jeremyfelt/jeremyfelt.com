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
			'/og'       => '<html><head><title>The HTML title</title><meta content="An &amp; OG title" property="og:title"></head><body></body></html>',
			'/title'    => "<html><head><title>\n  Just a   <b>title</b> &mdash; here\n</title></head></html>",
			'/notitle'  => '<html><head></head><body>Nothing</body></html>',
			'/long'     => '<html><head><title>' . str_repeat( 'Long title ', 40 ) . '</title></head></html>',
			'/latin1'   => '<html><head><meta charset="iso-8859-1"><title>Caf' . "\xE9" . '</title></head></html>',
			'/notfound' => '',
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
			'headers'  => [ 'content-type' => 'text/html; charset=utf-8' ],
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
