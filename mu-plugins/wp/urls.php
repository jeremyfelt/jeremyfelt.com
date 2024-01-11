<?php
/**
 * Modify default URLs in WordPress.
 *
 * @package jeremyfelt
 */

namespace JeremyFelt\WP\URLs;

add_action( 'template_redirect', __NAMESPACE__ . '\redirect_author_archives' );

/**
 * Redirect author archives to the homepage.
 */
function redirect_author_archives(): void {
	if ( is_author() ) {
		wp_safe_redirect( home_url(), 301 );
		exit;
	}
}
