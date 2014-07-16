<?php
/**
 * @package Make
 */

$date_key    = 'layout-' . ttfmake_get_view() . '-post-date';
$date_option = ttfmake_sanitize_choice( get_theme_mod( $date_key, ttfmake_get_default( $date_key ) ), $date_key );
?>

<?php if ( 'none' !== $date_option ) : ?>
<time class="entry-date" datetime="<?php the_time( 'c' ); ?>">
<?php if ( ! is_singular() ) : ?><a href="<?php the_permalink(); ?>" rel="bookmark"><?php endif; ?>
	<?php if ( 'absolute' === $date_option ) : ?>
		<?php echo get_the_date(); ?>
	<?php elseif ( 'relative' === $date_option ) : ?>
		<?php
		printf(
			__( '%s ago', 'make' ),
			human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) )
		)
		?>
	<?php endif; ?>
<?php if ( ! is_singular() ) : ?></a><?php endif; ?>
</time>
<?php endif; ?>