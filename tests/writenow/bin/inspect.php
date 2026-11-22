<?php
/**
 * Look inside the local test site from the command line, for the
 * end-to-end tests. Each command prints one JSON document.
 *
 * Usage: php inspect.php <command> [arguments]
 *
 *   post <id>                     A post as WordPress stores and renders it.
 *   notes-with-markdown <text>    IDs of notes whose stored markdown is exactly <text>.
 *   note-by-slug <slug>           The ID of the note with this slug, or null.
 *   likes-of <url>                IDs of likes (any status) whose like_of_url is <url>.
 *   app-passwords <login>         Names of a user's Application Passwords.
 *   revoke <login> <prefix>       Delete a user's Application Passwords whose names start with <prefix>.
 *   lock-rows                     WriteNow lock rows left in the options table.
 *
 * Expects WRITENOW_TEST_SITE and WRITENOW_TEST_URL, as run.sh sets them.
 * The local test site only; never pointed at a real site.
 *
 * @package jeremyfelt
 */

namespace JeremyFelt\Apps\WriteNow\Tests\Inspect;

// phpcs:disable WordPress.WP.AlternativeFunctions, WordPress.DB.DirectDatabaseQuery

$site_dir = (string) getenv( 'WRITENOW_TEST_SITE' );

if ( '' === $site_dir || ! is_file( $site_dir . '/wp-config.php' ) ) {
	fwrite( STDERR, "Set WRITENOW_TEST_SITE, as tests/writenow/run.sh does.\n" );
	exit( 1 );
}

$parts = parse_url( (string) getenv( 'WRITENOW_TEST_URL' ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url -- WordPress is not loaded yet.

$_SERVER['HTTP_HOST']   = ( $parts['host'] ?? '127.0.0.1' ) . ( isset( $parts['port'] ) ? ':' . $parts['port'] : '' );
$_SERVER['REQUEST_URI'] = '/';

define( 'WP_USE_THEMES', false );

require $site_dir . '/wordpress/wp-load.php';

global $wpdb;

$command = $argv[1] ?? '';
$args    = array_slice( $argv, 2 );

/**
 * Print a value as JSON and stop.
 *
 * @param mixed $value The value.
 */
function out( $value ): void {
	echo wp_json_encode( $value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ), "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit( 0 );
}

/**
 * Reduce parsed blocks to what the tests compare.
 *
 * @param array[] $blocks Blocks from parse_blocks().
 * @return array[] Block names, attributes, inner HTML and inner blocks.
 */
function blocks( array $blocks ): array {
	$result = [];

	foreach ( $blocks as $block ) {
		if ( null === $block['blockName'] && '' === trim( $block['innerHTML'] ) ) {
			continue;
		}

		$result[] = [
			'name'   => $block['blockName'],
			'attrs'  => $block['attrs'],
			'html'   => trim( $block['innerHTML'] ),
			'blocks' => blocks( $block['innerBlocks'] ),
		];
	}

	return $result;
}

switch ( $command ) {
	case 'post':
		$post = get_post( (int) ( $args[0] ?? 0 ) );

		if ( ! $post ) {
			out( null );
		}

		$meta = [];

		foreach ( get_post_meta( $post->ID ) as $key => $values ) {
			$meta[ $key ] = 1 === count( $values ) ? $values[0] : $values;
		}

		// Render as the theme would, inside the loop.
		$GLOBALS['post'] = $post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
		setup_postdata( $post );
		$rendered = apply_filters( 'the_content', $post->post_content );
		wp_reset_postdata();

		$attachments = [];

		foreach ( get_children(
			[
				'post_parent' => $post->ID,
				'post_type'   => 'attachment',
			]
		) as $child ) {
			$file          = (string) get_attached_file( $child->ID );
			$attachments[] = [
				'id'        => $child->ID,
				'mime'      => $child->post_mime_type,
				'alt'       => get_post_meta( $child->ID, '_wp_attachment_image_alt', true ),
				'file'      => $file,
				'has_exif'  => is_readable( $file ) && false !== strpos( (string) file_get_contents( $file, false, null, 0, 65536 ), 'Exif' ),
				'dimension' => is_readable( $file ) ? array_slice( (array) getimagesize( $file ), 0, 2 ) : null,
			];
		}

		$transitions = get_option( 'writenow_test_transitions', [] );

		out(
			[
				'id'          => $post->ID,
				'type'        => $post->post_type,
				'status'      => $post->post_status,
				'title'       => $post->post_title,
				'slug'        => $post->post_name,
				'guid'        => $post->guid,
				'author'      => (int) $post->post_author,
				'date_gmt'    => $post->post_date_gmt,
				'content'     => $post->post_content,
				'blocks'      => blocks( parse_blocks( $post->post_content ) ),
				'reserialize' => serialize_blocks( parse_blocks( $post->post_content ) ) === $post->post_content,
				'mastodon'    => function_exists( '\ShortNotes\PostType\Note\transform_content' ) ? \ShortNotes\PostType\Note\transform_content( $post->post_content ) : null,
				'rendered'    => $rendered,
				'meta'        => $meta,
				'permalink'   => get_permalink( $post ),
				'attachments' => $attachments,
				'transitions' => $transitions[ $post->ID ] ?? [],
				'webmentions' => (bool) wp_next_scheduled( 'send_shortnote_webmentions', [ $post->ID ] ),
			]
		);
		break;

	case 'notes-with-markdown':
		out(
			array_map(
				'intval',
				$wpdb->get_col(
					$wpdb->prepare(
						"SELECT p.ID FROM {$wpdb->posts} p JOIN {$wpdb->postmeta} m ON m.post_id = p.ID WHERE p.post_type = 'shortnote' AND m.meta_key = '_markdown_content' AND m.meta_value = %s ORDER BY p.ID",
						$args[0] ?? ''
					)
				)
			)
		);
		break;

	case 'note-by-slug':
		$found = get_posts(
			[
				'post_type'   => 'shortnote',
				'name'        => $args[0] ?? '',
				'post_status' => 'any',
				'fields'      => 'ids',
			]
		);
		out( $found ? (int) $found[0] : null );
		break;

	case 'likes-of':
		out(
			array_map(
				'intval',
				$wpdb->get_col(
					$wpdb->prepare(
						"SELECT p.ID FROM {$wpdb->posts} p JOIN {$wpdb->postmeta} m ON m.post_id = p.ID WHERE p.post_type = 'like' AND m.meta_key = 'like_of_url' AND m.meta_value = %s ORDER BY p.ID",
						$args[0] ?? ''
					)
				)
			)
		);
		break;

	case 'app-passwords':
		$user = get_user_by( 'login', $args[0] ?? '' );
		out( $user ? array_column( \WP_Application_Passwords::get_user_application_passwords( $user->ID ), 'name' ) : null );
		break;

	case 'revoke':
		$user    = get_user_by( 'login', $args[0] ?? '' );
		$prefix  = $args[1] ?? '';
		$revoked = [];

		if ( $user && '' !== $prefix ) {
			foreach ( \WP_Application_Passwords::get_user_application_passwords( $user->ID ) as $item ) {
				if ( 0 === strpos( $item['name'], $prefix ) ) {
					\WP_Application_Passwords::delete_application_password( $user->ID, $item['uuid'] );
					$revoked[] = $item['name'];
				}
			}
		}

		out( $revoked );
		break;

	case 'lock-rows':
		out( $wpdb->get_col( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '\\_writenow\\_lock\\_%'" ) );
		break;

	default:
		fwrite( STDERR, "Unknown command: $command\n" );
		exit( 1 );
}
