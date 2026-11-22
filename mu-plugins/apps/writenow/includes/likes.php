<?php
/**
 * Create likes.
 *
 * The `like` post type is registered with `'supports' => false`, so the core
 * REST controller can set neither its title nor its URL. This writes the
 * same data the Likes meta box does, a title and `like_of_url`, so the Likes
 * plugin renders it and adds the URL to webmentions exactly as it does for
 * a like made in wp-admin.
 *
 * @package jeremyfelt
 */

namespace JeremyFelt\Apps\WriteNow\Likes;

use WP_Error;
use WP_Post;
use WP_REST_Request;
use WP_REST_Response;

use function JeremyFelt\Apps\WriteNow\REST\decode;
use function JeremyFelt\Apps\WriteNow\REST\error;
use function JeremyFelt\Apps\WriteNow\REST\get_like_post_type;
use function JeremyFelt\Apps\WriteNow\Lock\with_lock;

/**
 * The longest title stored for a like, in characters.
 */
const TITLE_MAX_LENGTH = 200;

/**
 * The most of a page read when looking up its title, in bytes.
 */
const TITLE_FETCH_LIMIT = 262144;

/**
 * Create a like, or return the existing like for the same URL.
 *
 * @param WP_REST_Request $request The request.
 * @return WP_REST_Response|WP_Error The like, or the reason it was not created.
 */
function create_like( WP_REST_Request $request ) {
	$submitted = trim( (string) $request->get_param( 'url' ) );
	$url       = validate_url( $submitted );

	if ( '' === $url ) {
		return error( 'writenow_invalid_url', 'That does not look like a web address. Liked URLs start with http:// or https://.', 400 );
	}

	$url        = strip_tracking_parameters( $url );
	$candidates = array_unique( [ $url, $submitted ] );
	$existing   = find_like( $candidates );

	if ( null !== $existing ) {
		return like_response( $existing, true );
	}

	$title = clean_title( (string) $request->get_param( 'title' ) );

	// Look the title up outside the lock: it can take a few seconds.
	if ( '' === $title ) {
		$title = fetch_title( $url );
	}

	if ( '' === $title ) {
		$title = $url;
	}

	return with_lock(
		'like:' . $url,
		function () use ( $url, $title, $candidates ) {
			$existing = find_like( $candidates );

			if ( null !== $existing ) {
				return like_response( $existing, true );
			}

			$post_id = wp_insert_post(
				wp_slash(
					[
						'post_type'   => get_like_post_type(),
						'post_status' => 'publish',
						'post_author' => get_current_user_id(),
						'post_title'  => $title,
						'meta_input'  => [
							'like_of_url' => $url,
						],
					]
				),
				true
			);

			if ( is_wp_error( $post_id ) ) {
				return error( 'writenow_save_failed', 'The like could not be saved. Try again.', 500 );
			}

			$post = get_post( $post_id );

			if ( ! $post instanceof WP_Post ) {
				return error( 'writenow_save_failed', 'The like could not be saved. Try again.', 500 );
			}

			return like_response( $post, false );
		}
	);
}

/**
 * Validate a submitted URL.
 *
 * @param string $url The trimmed URL.
 * @return string The URL, or an empty string if it is not an absolute http(s) URL.
 */
function validate_url( string $url ): string {
	if ( '' === $url || strlen( $url ) > 2048 || preg_match( '/\s/', $url ) ) {
		return '';
	}

	$scheme = wp_parse_url( $url, PHP_URL_SCHEME );
	$host   = wp_parse_url( $url, PHP_URL_HOST );

	if ( ! is_string( $scheme ) || ! in_array( strtolower( $scheme ), [ 'http', 'https' ], true ) || ! is_string( $host ) || '' === $host ) {
		return '';
	}

	return esc_url_raw( $url, [ 'http', 'https' ] );
}

/**
 * Remove tracking parameters from a URL.
 *
 * Every other part of the URL, including the order and encoding of the
 * remaining parameters and the fragment, is kept exactly as it was.
 *
 * @param string $url The URL.
 * @return string The URL without tracking parameters.
 */
function strip_tracking_parameters( string $url ): string {
	/**
	 * Filters the query parameters removed from liked URLs.
	 *
	 * A name ending in `*` matches any parameter that starts with the rest
	 * of the name. Matching is case-insensitive.
	 *
	 * @param string[] $parameters Parameter names.
	 */
	$parameters = (array) apply_filters( 'writenow_tracking_parameters', [ 'utm_*', 'fbclid', 'gclid', 'mc_cid', 'mc_eid', 'igshid' ] );

	$fragment = '';
	$hash     = strpos( $url, '#' );

	if ( false !== $hash ) {
		$fragment = substr( $url, $hash );
		$url      = substr( $url, 0, $hash );
	}

	$question = strpos( $url, '?' );

	if ( false === $question ) {
		return $url . $fragment;
	}

	$base  = substr( $url, 0, $question );
	$query = substr( $url, $question + 1 );
	$kept  = [];

	foreach ( explode( '&', $query ) as $pair ) {
		$name = strtolower( rawurldecode( explode( '=', $pair, 2 )[0] ) );

		if ( '' === $pair || ! is_tracking_parameter( $name, $parameters ) ) {
			$kept[] = $pair;
		}
	}

	$kept = array_filter(
		$kept,
		function ( $pair ) {
			return '' !== $pair;
		}
	);

	return $base . ( [] === $kept ? '' : '?' . implode( '&', $kept ) ) . $fragment;
}

/**
 * Determine whether a parameter name is on the tracking list.
 *
 * @param string   $name       A lowercase parameter name.
 * @param string[] $parameters Names from the filtered list.
 * @return bool True if it should be removed.
 */
function is_tracking_parameter( string $name, array $parameters ): bool {
	foreach ( $parameters as $parameter ) {
		$parameter = strtolower( (string) $parameter );

		if ( '*' === substr( $parameter, -1 ) ) {
			if ( 0 === strpos( $name, substr( $parameter, 0, -1 ) ) ) {
				return true;
			}
		} elseif ( $name === $parameter ) {
			return true;
		}
	}

	return false;
}

/**
 * Find a published like of any of the given URLs.
 *
 * Likes made in wp-admin store the URL in `like_of_url`. Likes made through
 * the Micropub plugin store it in `mf2_like-of`, as a single value or as a
 * serialized one-item array.
 *
 * @param string[] $urls URLs that count as the same like.
 * @return WP_Post|null The oldest matching like, or null.
 */
function find_like( array $urls ): ?WP_Post {
	$clauses = [ 'relation' => 'OR' ];

	foreach ( $urls as $url ) {
		$clauses[] = [
			'key'   => 'like_of_url',
			'value' => $url,
		];
		$clauses[] = [
			'key'   => 'mf2_like-of',
			'value' => $url,
		];
		$clauses[] = [
			'key'   => 'mf2_like-of',
			'value' => serialize( [ $url ] ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize -- Matches what update_post_meta() stores for an array.
		];
	}

	$posts = get_posts(
		[
			'post_type'        => get_like_post_type(),
			'post_status'      => 'publish',
			'meta_query'       => $clauses, // phpcs:ignore WordPress.DB.SlowDBQuery
			'posts_per_page'   => 1,
			'orderby'          => 'date',
			'order'            => 'ASC',
			'no_found_rows'    => true,
			'suppress_filters' => true,
		]
	);

	return isset( $posts[0] ) && $posts[0] instanceof WP_Post ? $posts[0] : null;
}

/**
 * Look up a page's title.
 *
 * Uses `og:title` when the page has one, then `<title>`. Any failure, from
 * a timeout to a page that is not HTML, returns an empty string so that
 * the caller falls back to the URL. A title lookup never fails a like.
 *
 * @param string $url The page URL.
 * @return string The title, or an empty string.
 */
function fetch_title( string $url ): string {
	$response = wp_safe_remote_get(
		$url,
		[
			'timeout'             => 5,
			'redirection'         => 3,
			'limit_response_size' => TITLE_FETCH_LIMIT,
			'headers'             => [
				'Accept' => 'text/html,application/xhtml+xml;q=0.9,*/*;q=0.1',
			],
		]
	);

	if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
		return '';
	}

	$type = (string) wp_remote_retrieve_header( $response, 'content-type' );

	if ( '' !== $type && false === stripos( $type, 'html' ) ) {
		return '';
	}

	return extract_title( (string) wp_remote_retrieve_body( $response ), $type );
}

/**
 * Extract a title from an HTML document.
 *
 * @param string $html         The document, or its first part.
 * @param string $content_type The Content-Type header, used to find the charset.
 * @return string The title, cleaned, or an empty string.
 */
function extract_title( string $html, string $content_type = '' ): string {
	$html = convert_to_utf8( $html, $content_type );

	if ( preg_match_all( '/<meta\s([^>]*)>/i', $html, $tags ) ) {
		foreach ( $tags[1] as $attributes ) {
			$parsed   = wp_kses_hair( $attributes, [ 'http', 'https' ] );
			$property = strtolower( $parsed['property']['value'] ?? ( $parsed['name']['value'] ?? '' ) );

			if ( 'og:title' === $property && isset( $parsed['content']['value'] ) ) {
				$title = clean_title( decode( $parsed['content']['value'] ) );

				if ( '' !== $title ) {
					return $title;
				}
			}
		}
	}

	if ( preg_match( '/<title[^>]*>(.*?)<\/title>/is', $html, $matches ) ) {
		return clean_title( decode( $matches[1] ) );
	}

	return '';
}

/**
 * Convert a fetched document to UTF-8 when it declares another charset.
 *
 * @param string $html         The document.
 * @param string $content_type The Content-Type header.
 * @return string The document in UTF-8, as well as can be managed.
 */
function convert_to_utf8( string $html, string $content_type ): string {
	$charset = '';

	if ( preg_match( '/charset=["\']?([a-z0-9_-]+)/i', $content_type, $matches ) ) {
		$charset = $matches[1];
	} elseif ( preg_match( '/<meta[^>]+charset=["\']?([a-z0-9_-]+)/i', $html, $matches ) ) {
		$charset = $matches[1];
	}

	if ( '' !== $charset && ! in_array( strtolower( $charset ), [ 'utf-8', 'utf8' ], true ) && function_exists( 'mb_convert_encoding' ) ) {
		$converted = @mb_convert_encoding( $html, 'UTF-8', $charset ); // phpcs:ignore WordPress.PHP.NoSilencedErrors -- An unknown charset is expected and handled.

		if ( is_string( $converted ) && '' !== $converted ) {
			return $converted;
		}
	}

	return $html;
}

/**
 * Clean a title for storage: plain text, single spaces, at most 200 characters.
 *
 * @param string $title A title, already decoded from HTML.
 * @return string The cleaned title.
 */
function clean_title( string $title ): string {
	$title = sanitize_text_field( $title );
	$title = trim( (string) preg_replace( '/\s+/u', ' ', $title ) );

	if ( mb_strlen( $title ) > TITLE_MAX_LENGTH ) {
		$title = rtrim( mb_substr( $title, 0, TITLE_MAX_LENGTH - 1 ) ) . '…';
	}

	return $title;
}

/**
 * Provide the URL a like is of.
 *
 * @param WP_Post $post The like.
 * @return string The liked URL.
 */
function get_liked_url( WP_Post $post ): string {
	if ( function_exists( '\Likes\PostType\Like\get_like_url' ) ) {
		return (string) \Likes\PostType\Like\get_like_url( $post->ID );
	}

	$url = get_post_meta( $post->ID, 'like_of_url', true );

	if ( ! $url ) {
		$url = get_post_meta( $post->ID, 'mf2_like-of', true );
	}

	return is_array( $url ) ? (string) array_pop( $url ) : (string) $url;
}

/**
 * Build the response for a like.
 *
 * @param WP_Post $post      The like.
 * @param bool    $duplicate Whether the like already existed.
 * @return WP_REST_Response The response: 200 for a duplicate, 201 when created.
 */
function like_response( WP_Post $post, bool $duplicate ): WP_REST_Response {
	$url   = get_liked_url( $post );
	$title = decode( $post->post_title );
	$title = '' === $title ? $url : $title;

	return new WP_REST_Response(
		[
			'id'        => $post->ID,
			'url'       => $url,
			'title'     => $title,
			'link'      => (string) get_permalink( $post ),
			'edit_link' => (string) ( get_edit_post_link( $post->ID, 'raw' ) ?? admin_url( 'post.php?post=' . $post->ID . '&action=edit' ) ),
			'duplicate' => $duplicate,
			'message'   => ( $duplicate ? 'Already liked: ' : 'Liked: ' ) . $title,
		],
		$duplicate ? 200 : 201
	);
}
