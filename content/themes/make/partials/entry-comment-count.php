<?php
/**
 * @package Make
 */

$comment_count_key    = 'layout-' . ttfmake_get_view() . '-comment-count';
$comment_count_option = ttfmake_sanitize_choice( get_theme_mod( $comment_count_key, ttfmake_get_default( $comment_count_key ) ), $comment_count_key );

$comments_number = get_comments_number_text();
if ( 'icon' === $comment_count_option ) :
	$comments_number = get_comments_number_text(
		'<span class="comment-count-icon zero">0</span>',
		'<span class="comment-count-icon one">1</span>',
		'<span class="comment-count-icon many">%</span>'
	);
endif;
?>

<?php if ( 'none' !== $comment_count_option ) : ?>
	<div class="entry-comment-count">
		<a href="<?php comments_link(); ?>"><?php echo $comments_number; ?></a>
	</div>
<?php endif;