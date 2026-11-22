<?php
/**
 * WriteNow: a small home screen app for posting notes and likes.
 *
 * Everything WriteNow needs lives in this directory so that it can be lifted
 * into a standalone plugin later. The PHP in `includes/` serves the app shell
 * and the `writenow/v1` REST API. Everything a browser loads lives in `app/`.
 *
 * To rename the app, change PATH (the URL segment) and the display name in
 * `app/`. Nothing else depends on the name.
 *
 * @package jeremyfelt
 */

namespace JeremyFelt\Apps\WriteNow;

/**
 * The WriteNow version, reported by `GET /status` and shown in Settings.
 */
const VERSION = '1.0.0';

/**
 * The URL path segment the app is served from, relative to the home URL.
 */
const PATH = 'writenow';

/**
 * The REST API namespace.
 */
const REST_NAMESPACE = 'writenow/v1';

/**
 * Post meta key that holds the markdown a note was written in. This is the
 * key WriteWell used.
 */
const MARKDOWN_META_KEY = '_markdown_content';

/**
 * Post meta key that holds the client-generated ID used to make note
 * creation idempotent.
 */
const CLIENT_ID_META_KEY = '_writenow_client_id';

/**
 * Character count past which the composer warns. This is the usual Mastodon
 * limit, since notes are shared there.
 */
const NOTE_SOFT_LIMIT = 500;

/**
 * Provide the absolute path of the WriteNow directory, with a trailing slash.
 *
 * @return string The directory path.
 */
function get_dir(): string {
	return __DIR__ . '/';
}

/**
 * Provide the absolute path of the `app/` directory, with a trailing slash.
 *
 * @return string The directory path.
 */
function get_app_dir(): string {
	return get_dir() . 'app/';
}

/**
 * Provide the public URL of the `app/` directory, with a trailing slash.
 *
 * Static files are served by the web server straight from this URL.
 *
 * @return string The directory URL.
 */
function get_app_url(): string {
	return trailingslashit( plugins_url( 'app', __FILE__ ) );
}

/**
 * Provide the public URL of the app shell, with a trailing slash.
 *
 * @return string The shell URL.
 */
function get_shell_url(): string {
	return home_url( '/' . PATH . '/' );
}

require_once __DIR__ . '/includes/markdown.php';
require_once __DIR__ . '/includes/cache.php';
require_once __DIR__ . '/includes/lock.php';
require_once __DIR__ . '/includes/routes.php';
require_once __DIR__ . '/includes/rest.php';
require_once __DIR__ . '/includes/notes.php';
require_once __DIR__ . '/includes/likes.php';
require_once __DIR__ . '/includes/recent.php';
