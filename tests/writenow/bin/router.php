<?php
/**
 * Router for PHP's built-in web server, so the test site behaves like a
 * site behind a normal web server: existing files are served as they are,
 * PHP files run, and every other path goes to WordPress's index.php.
 *
 * Usage: php -S 127.0.0.1:8881 -t <site>/wordpress router.php
 *
 * @package jeremyfelt
 */

$path = (string) parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH ); // phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url -- WordPress is not loaded yet.
$file = realpath( $_SERVER['DOCUMENT_ROOT'] . $path );

if ( false !== $file ) {
	if ( is_file( $file ) ) {
		return false;
	}

	if ( is_dir( $file ) && is_file( $file . '/index.php' ) && '/' !== $path ) {
		return false;
	}
}

$_SERVER['SCRIPT_NAME']     = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = $_SERVER['DOCUMENT_ROOT'] . '/index.php';
$_SERVER['PHP_SELF']        = '/index.php';

require $_SERVER['DOCUMENT_ROOT'] . '/index.php';
