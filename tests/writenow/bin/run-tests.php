<?php
/**
 * Load the local test site and run every server test.
 *
 * Usage: php run-tests.php [filter]
 *
 * Expects WRITENOW_TEST_SITE (the site directory) and WRITENOW_TEST_URL
 * (where the site is being served) in the environment. run.sh sets both.
 *
 * @package jeremyfelt
 */

namespace JeremyFelt\Apps\WriteNow\Tests;

// phpcs:disable WordPress.WP.AlternativeFunctions

$site_dir = (string) getenv( 'WRITENOW_TEST_SITE' );

if ( '' === $site_dir || ! is_file( $site_dir . '/wp-config.php' ) ) {
	fwrite( STDERR, "Run the tests with tests/writenow/run.sh.\n" );
	exit( 1 );
}

define( 'WP_USE_THEMES', false );

$_SERVER['HTTP_HOST']   = (string) wp_parse_url_early( (string) getenv( 'WRITENOW_TEST_URL' ) );
$_SERVER['REQUEST_URI'] = '/';

/**
 * Read the host and port of a URL before WordPress is loaded.
 *
 * @param string $url The URL.
 * @return string `host:port`.
 */
function wp_parse_url_early( string $url ): string {
	$parts = parse_url( $url ); // phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url

	return ( $parts['host'] ?? '127.0.0.1' ) . ( isset( $parts['port'] ) ? ':' . $parts['port'] : '' );
}

require $site_dir . '/wordpress/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once dirname( __DIR__ ) . '/lib/harness.php';

// Read the filter only now: WordPress core uses a global named $filter while it loads.
$writenow_filter = $argv[1] ?? '';

foreach ( glob( dirname( __DIR__ ) . '/server/test-*.php' ) as $file ) {
	require_once $file;
}

$tests = array_filter(
	get_defined_functions()['user'],
	function ( $name ) use ( $writenow_filter ) {
		$prefix = strtolower( __NAMESPACE__ . '\\test_' );

		return 0 === strpos( $name, $prefix ) && ( '' === $writenow_filter || false !== strpos( $name, strtolower( $writenow_filter ) ) );
	}
);

sort( $tests );

echo 'WordPress ' . $GLOBALS['wp_version'] . ', PHP ' . PHP_VERSION . ', ' . count( $tests ) . " tests\n\n";

$failures = [];

foreach ( $tests as $test ) {
	$name = substr( $test, strlen( __NAMESPACE__ ) + 1 );

	try {
		$test();
		echo "  pass  $name\n";
	} catch ( \Throwable $e ) {
		$failures[ $name ] = $e;
		echo "  FAIL  $name\n";
	}

	wp_set_current_user( 0 );
}

foreach ( $failures as $name => $e ) {
	echo "\n--- $name\n" . $e->getMessage() . "\n";

	if ( ! $e instanceof Assertion_Failed ) {
		echo $e->getFile() . ':' . $e->getLine() . "\n" . $e->getTraceAsString() . "\n";
	}
}

$passed = count( $tests ) - count( $failures );

echo "\n$passed passed, " . count( $failures ) . " failed\n";

exit( [] === $failures ? 0 : 1 );
