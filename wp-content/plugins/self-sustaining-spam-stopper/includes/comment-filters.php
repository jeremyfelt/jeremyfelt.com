<?php

namespace SSSS\CommentFilters;

add_filter( 'pre_comment_approved', __NAMESPACE__ . '\get_comment_status', 10, 2 );

/**
 * @param array $commentdata {
 *     Comment data.
 *
 *     @type string $comment_author       The name of the comment author.
 *     @type string $comment_author_email The comment author email address.
 *     @type string $comment_author_url   The comment author URL.
 *     @type string $comment_content      The content of the comment.
 *     @type string $comment_date         The date the comment was submitted. Default is the current time.
 *     @type string $comment_date_gmt     The date the comment was submitted in the GMT timezone.
 *                                        Default is `$comment_date` in the GMT timezone.
 *     @type int    $comment_parent       The ID of this comment's parent, if any. Default 0.
 *     @type int    $comment_post_ID      The ID of the post that relates to the comment.
 *     @type int    $user_id              The ID of the user who submitted the comment. Default 0.
 *     @type int    $user_ID              Kept for backward-compatibility. Use `$user_id` instead.
 *     @type string $comment_agent        Comment author user agent. Default is the value of 'HTTP_USER_AGENT'
 *                                        in the `$_SERVER` superglobal sent in the original request.
 *     @type string $comment_author_IP    Comment author IP address in IPv4 format. Default is the value of
 *                                        'REMOTE_ADDR' in the `$_SERVER` superglobal sent in the original request.
 * }
 */
function get_comment_status( $approved, $commentdata ) {
	if ( 'spam' === $approved ) {
		return $approved;
	}

	$user = wp_get_current_user();

	// Don't filter any comment left by a valid user.
	if ( $user->exists() ) {
		return $approved;
	}

	// A current pattern is spam comments that end in the pipe character. Likely because
	// some bot was written badly.
	if ( rtrim( trim( $commentdata['comment_content'] ), '|' ) !== $commentdata['comment_content'] ) {
		return 'spam';
	}

	$comment_content = implode( ' ', $commentdata );
	$comment_content = strtolower( $comment_content );

	// There are a few words that can always be considered spam.
	foreach ( \SSSS\Common\get_spam_word_list() as $word ) {

		// Anything containing a blacklisted word is marked as spam.
		if ( false !== strpos( $comment_content, $word ) ) {
			return 'spam';
		}
	}

	// A hazard guess that most sites don't deal with doses.
	if ( \SSSS\Common\contains_mg( $comment_content ) ) {
		return 'spam';
	}

	if ( \SSSS\Common\contains_sex_combo( $comment_content ) ) {
		return 'spam';
	}

	return $approved;
}
