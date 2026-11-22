<?php
/**
 * Register the `writenow/v1` REST API and the checks every route shares.
 *
 * Every route requires an authenticated user. Permission checks use the
 * capabilities of the post type involved. Errors are WP_Error objects whose
 * message is a sentence the app can show unchanged.
 *
 * @package jeremyfelt
 */

namespace JeremyFelt\Apps\WriteNow\REST;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

use const JeremyFelt\Apps\WriteNow\NOTE_SOFT_LIMIT;
use const JeremyFelt\Apps\WriteNow\REST_NAMESPACE;
use const JeremyFelt\Apps\WriteNow\VERSION;
use const JeremyFelt\Apps\WriteNow\Markdown\MAX_MARKDOWN_LENGTH;

add_action( 'rest_api_init', __NAMESPACE__ . '\register_routes' );

/**
 * Provide the post type used for notes.
 *
 * @return string The post type slug.
 */
function get_note_post_type(): string {
	return function_exists( '\ShortNotes\PostType\Note\get_slug' ) ? \ShortNotes\PostType\Note\get_slug() : 'shortnote';
}

/**
 * Provide the post type used for likes.
 *
 * @return string The post type slug.
 */
function get_like_post_type(): string {
	return function_exists( '\Likes\PostType\Like\get_slug' ) ? \Likes\PostType\Like\get_slug() : 'like';
}

/**
 * Build an error in the standard REST shape.
 *
 * @param string $code    A machine-readable code.
 * @param string $message A sentence that can be shown to Jeremy unchanged.
 * @param int    $status  The HTTP status.
 * @param array  $data    Extra data to include with the error.
 * @return WP_Error The error.
 */
function error( string $code, string $message, int $status, array $data = [] ): WP_Error {
	return new WP_Error( $code, $message, array_merge( $data, [ 'status' => $status ] ) );
}

/**
 * Check that the request comes from a signed-in user.
 *
 * @return true|WP_Error True, or an error with the status WordPress expects.
 */
function check_authenticated() {
	if ( is_user_logged_in() ) {
		return true;
	}

	return error(
		'writenow_not_connected',
		'WriteNow is not connected to your site. Connect it again in Settings.',
		rest_authorization_required_code()
	);
}

/**
 * Check that a post type exists and the current user has one of its
 * capabilities.
 *
 * @param string $post_type The post type.
 * @param string $cap       A key of the post type's `cap` object, such as `publish_posts`.
 * @return true|WP_Error True, or the reason the request is refused.
 */
function check_post_type_capability( string $post_type, string $cap ) {
	$authenticated = check_authenticated();

	if ( true !== $authenticated ) {
		return $authenticated;
	}

	$post_type_object = get_post_type_object( $post_type );

	if ( null === $post_type_object ) {
		$what = get_note_post_type() === $post_type ? 'Notes are' : 'Likes are';

		return error(
			'writenow_feature_unavailable',
			$what . ' not available on this site right now, because the plugin that provides them is not active.',
			501
		);
	}

	if ( ! isset( $post_type_object->cap->$cap ) || ! current_user_can( $post_type_object->cap->$cap ) ) {
		return error(
			'writenow_forbidden',
			'This account is not allowed to do that on this site.',
			rest_authorization_required_code()
		);
	}

	return true;
}

/**
 * Register the WriteNow REST routes.
 */
function register_routes(): void {
	register_rest_route(
		REST_NAMESPACE,
		'/status',
		[
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => __NAMESPACE__ . '\get_status',
			'permission_callback' => __NAMESPACE__ . '\can_use_app',
		]
	);

	register_rest_route(
		REST_NAMESPACE,
		'/likes',
		[
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'JeremyFelt\Apps\WriteNow\Likes\create_like',
			'permission_callback' => function () {
				return check_post_type_capability( get_like_post_type(), 'publish_posts' );
			},
			'args'                => [
				'url'   => [
					'type'     => 'string',
					'required' => true,
				],
				'title' => [
					'type'    => 'string',
					'default' => '',
				],
			],
		]
	);

	register_rest_route(
		REST_NAMESPACE,
		'/notes',
		[
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'JeremyFelt\Apps\WriteNow\Notes\create_note',
			'permission_callback' => __NAMESPACE__ . '\can_create_note',
			'args'                => get_note_args(),
		]
	);

	register_rest_route(
		REST_NAMESPACE,
		'/notes/(?P<id>\d+)',
		[
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => 'JeremyFelt\Apps\WriteNow\Notes\get_note',
				'permission_callback' => __NAMESPACE__ . '\can_edit_note',
			],
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => 'JeremyFelt\Apps\WriteNow\Notes\update_note',
				'permission_callback' => __NAMESPACE__ . '\can_edit_note',
				'args'                => get_note_args(),
			],
			'args' => [
				'id' => [
					'type'     => 'integer',
					'required' => true,
				],
			],
		]
	);

	register_rest_route(
		REST_NAMESPACE,
		'/recent',
		[
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'JeremyFelt\Apps\WriteNow\Recent\get_recent',
			'permission_callback' => __NAMESPACE__ . '\can_use_app',
			'args'                => [
				'per_page' => [
					'type'    => 'integer',
					'default' => 20,
					'minimum' => 1,
					'maximum' => 50,
				],
			],
		]
	);
}

/**
 * Describe the body accepted when creating or updating a note.
 *
 * @return array[] REST argument definitions.
 */
function get_note_args(): array {
	return [
		'content'       => [
			'type'      => 'string',
			'default'   => '',
			'maxLength' => MAX_MARKDOWN_LENGTH,
		],
		'status'        => [
			'type'    => 'string',
			'enum'    => [ 'publish', 'draft' ],
			'default' => 'draft',
		],
		'reply_to_url'  => [
			'type'    => 'string',
			'default' => '',
		],
		'reply_to_name' => [
			'type'    => 'string',
			'default' => '',
		],
		'media'         => [
			'type'     => 'array',
			'default'  => [],
			'maxItems' => 20,
			'items'    => [
				'type'       => 'object',
				'properties' => [
					'id'  => [
						'type'     => 'integer',
						'required' => true,
					],
					'alt' => [
						'type'    => 'string',
						'default' => '',
					],
				],
			],
		],
		'client_id'     => [
			'type'    => 'string',
			'default' => '',
			'pattern' => '^[A-Za-z0-9_-]{0,64}$',
		],
	];
}

/**
 * Check that the current user may use WriteNow at all.
 *
 * @return true|WP_Error True, or the reason the request is refused.
 */
function can_use_app() {
	$authenticated = check_authenticated();

	if ( true !== $authenticated ) {
		return $authenticated;
	}

	if ( ! current_user_can( 'edit_posts' ) ) {
		return error(
			'writenow_forbidden',
			'This account is not allowed to post to this site.',
			rest_authorization_required_code()
		);
	}

	return true;
}

/**
 * Check that the current user may create a note with the requested status.
 *
 * Saving a draft needs the post type's `create_posts` capability, and
 * publishing also needs `publish_posts`.
 *
 * @param WP_REST_Request $request The request.
 * @return true|WP_Error True, or the reason the request is refused.
 */
function can_create_note( WP_REST_Request $request ) {
	$allowed = check_post_type_capability( get_note_post_type(), 'create_posts' );

	if ( true === $allowed && 'publish' === $request->get_param( 'status' ) ) {
		$allowed = check_post_type_capability( get_note_post_type(), 'publish_posts' );
	}

	return $allowed;
}

/**
 * Check that the current user may read and edit a specific note.
 *
 * @param WP_REST_Request $request The request.
 * @return true|WP_Error True, or the reason the request is refused.
 */
function can_edit_note( WP_REST_Request $request ) {
	$allowed = check_post_type_capability( get_note_post_type(), 'edit_posts' );

	if ( true !== $allowed ) {
		return $allowed;
	}

	$post = get_post( (int) $request->get_param( 'id' ) );

	if ( ! $post || get_note_post_type() !== $post->post_type || 'trash' === $post->post_status ) {
		return error( 'writenow_note_not_found', 'That note could not be found. It may have been deleted.', 404 );
	}

	if ( ! current_user_can( 'edit_post', $post->ID ) ) {
		return error( 'writenow_forbidden', 'This account is not allowed to edit that note.', 403 );
	}

	if ( 'publish' === $request->get_param( 'status' ) && WP_REST_Server::READABLE !== $request->get_method() ) {
		return check_post_type_capability( get_note_post_type(), 'publish_posts' );
	}

	return true;
}

/**
 * Confirm the credential and describe what is available.
 *
 * @return WP_REST_Response The status response.
 */
function get_status(): WP_REST_Response {
	$user = wp_get_current_user();

	/**
	 * Filters the character count past which the composer warns.
	 *
	 * @param int $limit The soft limit. Default 500.
	 */
	$soft_limit = (int) apply_filters( 'writenow_note_soft_limit', NOTE_SOFT_LIMIT );

	return new WP_REST_Response(
		[
			'user'            => [
				'id'   => $user->ID,
				'name' => decode( $user->display_name ),
			],
			'site'            => [
				'name' => decode( get_bloginfo( 'name' ) ),
				'url'  => home_url(),
			],
			'features'        => [
				'notes' => post_type_exists( get_note_post_type() ),
				'likes' => post_type_exists( get_like_post_type() ),
			],
			'note_soft_limit' => $soft_limit,
			'version'         => VERSION,
		]
	);
}

/**
 * Turn stored, HTML-encoded text into plain text for a JSON response.
 *
 * Titles and names are stored with entities such as `&amp;` and `&hellip;`.
 * JSON carries plain text, and the app inserts it as text, never as HTML.
 *
 * @param string $text Text that may contain HTML entities.
 * @return string Plain text.
 */
function decode( string $text ): string {
	return html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
}
