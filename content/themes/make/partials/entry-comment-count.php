<?php
/**
 * @package Make
 */

$comment_count_key    = 'layout-' . ttfmake_get_view() . '-comment-count';
$comment_count_option = ttfmake_sanitize_choice( get_theme_mod( $comment_count_key, ttfmake_get_default( $comment_count_key ) ), $comment_count_key );
?>

<?php if ( 'none' !== $comment_count_option ) : ?>
	<div class="entry-comment-count">
	<?php if ( 'icon' === $comment_count_option ) : ?>
		<a href="<?php comments_link(); ?>">
			<?php
			comments_number(
				'<span class="comment-count-icon zero">0</span>',
				'<span class="comment-count-icon one">1</span>',
				'<span class="comment-count-icon many">%</span>'
			);
			?>
		</a>
	<?php else : ?>
		<a href="<?php comments_link(); ?>">
			<?php comments_number(); ?>
		</a>
	<?php endif; ?>
	</div>
<?php endif;