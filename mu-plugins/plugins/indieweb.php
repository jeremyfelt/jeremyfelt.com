<?php

namespace JeremyFelt\Plugins\Indieweb;

add_action( 'plugins_loaded', __NAMESPACE__ . '\remove_indieweb_styles', 15 );
add_action( 'init', __NAMESPACE__ . '\remove_indieweb_added_on_init', 15 );
add_filter( 'pre_comment_approved', __NAMESPACE__ . '\set_comment_approved', 10, 2 );
add_filter( 'webmention_comment_form', __NAMESPACE__ . '\filter_webmention_comment_form_template' );

/**
 * Remove unneeded styles and scripts added by the IndieWeb plugin(s).
 *
 * In these cases, the site/theme has taken care of the display layer.
 */
function remove_indieweb_styles(): void {
	remove_action( 'wp_enqueue_scripts', array( 'IndieWeb_Plugin', 'enqueue_style' ) );
	remove_action( 'wp_enqueue_scripts', array( 'Semantic_Linkbacks_Plugin', 'enqueue_scripts' ) );
}

/**
 * Remove unneeded filters added by the IndieWeb plugin(s).
 */
function remove_indieweb_added_on_init(): void {

	// Remove the injection of the Semantic_Linkbacks_Walker_Comment walker when displaying
	// comments. This is added on the init action by the Semantic Linkbacks plugin.
	remove_filter( 'wp_list_comments_args', array( 'Linkbacks_Handler', 'filter_comment_args' ) );
}

/**
 * Determine if a webmention comment has been approved earlier in the
 * process of receiving the webmention.
 *
 * @param int   $approved The approval status. May be 1, 0, 'spam', 'trash', or WP_Error.
 * @param array $commentdata The list of data associated with the comment.
 *
 * @return int $approved The approval status. Accepts 1, 0, 'spam', 'trash', or WP_Error.
 */
function set_comment_approved( $approved, $commentdata ) {
	if ( 'webmention' !== $commentdata['comment_type'] ) {
		return $approved;
	}

	if ( 1 === $commentdata['comment_approved'] ) {
		return 1;
	}

	return $approved;
}

/**
 * Filter the template used for the Webmention form in the comments
 * template so that it can be overridden by the theme.
 *
 * @param string $template The location of the default template.
 * @return string The location of the template in the theme (or the default).
 */
function filter_webmention_comment_form_template( $template ) {
	$new_template = locate_template( 'templates/webmention-comment-form.php' );

	return '' === $new_template ? $template : $new_template;
}
