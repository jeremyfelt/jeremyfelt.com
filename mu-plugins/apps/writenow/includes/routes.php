<?php
/**
 * Serve the app shell, the web app manifest and the service worker.
 *
 * These are matched on the request path early in `parse_request` instead of
 * through rewrite rules, so deploying needs no permalink flush. The shell
 * does not load the theme or call wp_head(). It is the same for every
 * visitor and contains no user data, nonce or secret, so it is safe for
 * Batcache and the service worker to cache.
 *
 * The files in `app/` carry `{{NAME}}` tokens that are filled here. The
 * tokens are documented at the top of `app/shell.html`; get_tokens() lists
 * the values. When a file is missing, a minimal placeholder is served so the
 * server works on its own.
 *
 * @package jeremyfelt
 */

namespace JeremyFelt\Apps\WriteNow\Routes;

use WP;

use function JeremyFelt\Apps\WriteNow\get_app_dir;
use function JeremyFelt\Apps\WriteNow\get_app_url;
use function JeremyFelt\Apps\WriteNow\get_shell_url;

use const JeremyFelt\Apps\WriteNow\PATH;
use const JeremyFelt\Apps\WriteNow\VERSION;

add_action( 'parse_request', __NAMESPACE__ . '\maybe_serve', 0 );

/**
 * The Content-Security-Policy sent with the shell. Scripts, styles, fonts
 * and connections are limited to this origin. Images may also come from
 * data and blob URLs (previews) and any https host (uploads on a CDN).
 */
const CONTENT_SECURITY_POLICY = "default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self' data: blob: https:; font-src 'self'; connect-src 'self'; manifest-src 'self'; worker-src 'self'; object-src 'none'; base-uri 'none'; form-action 'self'; frame-ancestors 'none'";

/**
 * Determine which WriteNow route, if any, a request path is for.
 *
 * @param string $request_uri The request URI, with or without a query string.
 * @return string One of `shell`, `redirect`, `manifest`, `sw`, or an empty string.
 */
function match_route( string $request_uri ): string {
	$path = (string) wp_parse_url( $request_uri, PHP_URL_PATH );
	$home = untrailingslashit( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ) );
	$base = $home . '/' . PATH;

	$routes = [
		$base                           => 'redirect',
		$base . '/'                     => 'shell',
		$base . '/manifest.webmanifest' => 'manifest',
		$base . '/sw.js'                => 'sw',
	];

	return $routes[ $path ] ?? '';
}

/**
 * Serve a WriteNow route when the request is for one.
 *
 * @param WP $wp The WordPress environment, unused.
 */
function maybe_serve( $wp ): void {
	unset( $wp );

	$method = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) : 'GET';
	$uri    = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Only compared against known paths.
	$route  = match_route( (string) $uri );

	if ( '' === $route || ! in_array( $method, [ 'GET', 'HEAD' ], true ) ) {
		return;
	}

	$response = get_response( $route, (string) wp_parse_url( (string) $uri, PHP_URL_QUERY ) );

	status_header( $response['status'] );

	foreach ( $response['headers'] as $name => $value ) {
		header( $name . ': ' . $value );
	}

	if ( 'HEAD' !== $method ) {
		echo $response['body']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Values are escaped as they are filled in.
	}

	exit;
}

/**
 * Build the response for a WriteNow route.
 *
 * @param string $route The route from match_route().
 * @param string $query The request's query string, without the `?`.
 * @return array `status`, `headers` and `body`.
 */
function get_response( string $route, string $query = '' ): array {
	$common = [
		'X-Content-Type-Options' => 'nosniff',
		'X-Robots-Tag'           => 'noindex, nofollow',
	];

	if ( 'redirect' === $route ) {
		$location = get_shell_url() . ( '' === $query ? '' : '?' . $query );

		return [
			'status'  => 301,
			'headers' => $common + [ 'Location' => esc_url_raw( $location ) ],
			'body'    => '',
		];
	}

	if ( 'manifest' === $route ) {
		return [
			'status'  => 200,
			'headers' => $common + [ 'Content-Type' => 'application/manifest+json; charset=utf-8' ],
			'body'    => get_manifest(),
		];
	}

	if ( 'sw' === $route ) {
		// Browsers check for a new service worker on every launch. Keep it out of every cache.
		if ( function_exists( 'batcache_cancel' ) ) {
			batcache_cancel();
		}

		return [
			'status'  => 200,
			'headers' => $common + [
				'Content-Type'  => 'text/javascript; charset=utf-8',
				'Cache-Control' => 'no-cache',
			],
			'body'    => get_service_worker(),
		];
	}

	return [
		'status'  => 200,
		'headers' => $common + [
			'Content-Type'            => 'text/html; charset=utf-8',
			'Content-Security-Policy' => CONTENT_SECURITY_POLICY,
			'Referrer-Policy'         => 'strict-origin-when-cross-origin',
			'X-Frame-Options'         => 'DENY',
		],
		'body'    => get_shell(),
	];
}

/**
 * Provide the version shared by the shell, manifest and service worker.
 *
 * It changes whenever any file in `app/` is added, removed or modified, so a
 * deploy that changes the app gives it a new version.
 *
 * @return string A short version string.
 */
function get_asset_version(): string {
	$dir = get_app_dir();

	if ( ! is_dir( $dir ) ) {
		return VERSION;
	}

	$parts    = [ VERSION ];
	$iterator = new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( $dir, \FilesystemIterator::SKIP_DOTS ) );

	foreach ( $iterator as $file ) {
		if ( $file instanceof \SplFileInfo && $file->isFile() ) {
			$parts[] = substr( $file->getPathname(), strlen( $dir ) ) . ':' . $file->getMTime() . ':' . $file->getSize();
		}
	}

	sort( $parts );

	return substr( md5( implode( '|', $parts ) ), 0, 12 );
}

/**
 * Provide the URL of a font file in the active theme.
 *
 * @param string $file A path relative to the theme directory.
 * @return string The URL, or an empty string when the theme has no such file.
 */
function get_theme_font_url( string $file ): string {
	return file_exists( get_theme_file_path( $file ) ) ? get_theme_file_uri( $file ) : '';
}

/**
 * Provide the values for the tokens in `app/` files.
 *
 * @return string[] Token names and their raw, unescaped values.
 */
function get_tokens(): array {
	$tokens = [
		'ASSET_URL'     => get_app_url(),
		'ASSET_VERSION' => get_asset_version(),
		'APP_VERSION'   => VERSION,
		'REST_URL'      => trailingslashit( rest_url() ),
		'ADMIN_URL'     => trailingslashit( admin_url() ),
		'LOGIN_URL'     => wp_login_url(),
		'FONT_SANS_URL' => get_theme_font_url( 'fonts/Inter-VariableFont_slnt,wght.woff2' ),
		'FONT_MONO_URL' => get_theme_font_url( 'fonts/MonaspaceArgon-Regular.woff' ),
	];

	/**
	 * Filters the values filled into the WriteNow shell, manifest and service worker.
	 *
	 * @param string[] $tokens Token names and raw values.
	 */
	return (array) apply_filters( 'writenow_tokens', $tokens );
}

/**
 * Replace `{{NAME}}` tokens in a template.
 *
 * Unknown tokens are left alone; the app treats an unfilled token as empty.
 *
 * @param string   $template The template.
 * @param string[] $values   Token names and values, already escaped for the context.
 * @return string The filled template.
 */
function fill( string $template, array $values ): string {
	$pairs = [];

	foreach ( $values as $name => $value ) {
		$pairs[ '{{' . $name . '}}' ] = (string) $value;
	}

	return strtr( $template, $pairs );
}

/**
 * Escape token values for use inside a double-quoted HTML attribute.
 *
 * URLs go through esc_url(), everything else through esc_attr().
 *
 * @param string[] $tokens Raw token values.
 * @return string[] Escaped values.
 */
function escape_for_html( array $tokens ): array {
	$escaped = [];

	foreach ( $tokens as $name => $value ) {
		$escaped[ $name ] = '_URL' === substr( (string) $name, -4 ) ? esc_url( (string) $value ) : esc_attr( (string) $value );
	}

	return $escaped;
}

/**
 * Escape token values for use inside a double-quoted JSON or JavaScript string.
 *
 * @param string[] $tokens Raw token values.
 * @return string[] Escaped values, without surrounding quotes.
 */
function escape_for_json_string( array $tokens ): array {
	$escaped = [];

	foreach ( $tokens as $name => $value ) {
		$json             = (string) wp_json_encode( (string) $value, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP );
		$escaped[ $name ] = substr( $json, 1, -1 );
	}

	return $escaped;
}

/**
 * Read a file from `app/`.
 *
 * @param string $file A path relative to `app/`.
 * @return string|null The contents, or null when the file is missing.
 */
function read_app_file( string $file ): ?string {
	$path = get_app_dir() . $file;

	if ( ! is_readable( $path ) ) {
		return null;
	}

	$contents = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- A local file.

	return false === $contents ? null : $contents;
}

/**
 * Build the app shell.
 *
 * @return string The HTML document.
 */
function get_shell(): string {
	$template = read_app_file( 'shell.html' );

	if ( null === $template ) {
		$template = '<!doctype html><html lang="en" data-rest-url="{{REST_URL}}"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"><meta name="robots" content="noindex"><title>WriteNow</title><link rel="manifest" href="./manifest.webmanifest"></head><body><p>WriteNow is being set up. Version {{APP_VERSION}}.</p></body></html>';
	}

	return fill( $template, escape_for_html( get_tokens() ) );
}

/**
 * Build the web app manifest.
 *
 * @return string The manifest JSON.
 */
function get_manifest(): string {
	$template = read_app_file( 'manifest.webmanifest' );

	if ( null === $template ) {
		$template = (string) wp_json_encode(
			[
				'name'             => 'WriteNow',
				'short_name'       => 'WriteNow',
				'start_url'        => './',
				'scope'            => './',
				'display'          => 'standalone',
				'background_color' => '#fbfbfb',
				'theme_color'      => '#fbfbfb',
				'icons'            => [],
			],
			JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
		);
	}

	return fill( $template, escape_for_json_string( get_tokens() ) );
}

/**
 * Build the service worker.
 *
 * @return string The service worker script.
 */
function get_service_worker(): string {
	$template = read_app_file( 'sw.js' );

	if ( null === $template ) {
		$template = "/* WriteNow service worker placeholder, version {{ASSET_VERSION}}. */\nself.addEventListener( 'install', () => self.skipWaiting() );\n";
	}

	return fill( $template, escape_for_json_string( get_tokens() ) );
}
