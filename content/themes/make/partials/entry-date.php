<?php
/**
 * @package Make
 */

$date_key    = 'layout-' . ttfmake_get_view() . '-post-date';
$date_option = ttfmake_sanitize_choice( get_theme_mod( $date_key, ttfmake_get_default( $date_key ) ), $date_key );

// Get date string
$date_string = get_the_date();
if ( 'relative' === $date_option ) :
	$date_string = sprintf(
		_x( '%s ago', 'time period', 'make' ),
		human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) )
	);
endif;

// Add permalink if not single view
if ( ! is_singular() ) :
	$date_string = '<a href="' . get_permalink() . '" rel="bookmark">' . $date_string . '</a>';
endif;
?>

<?php if ( 'none' !== $date_option ) : ?>
<time class="entry-date published" datetime="<?php the_time( 'c' ); ?>"><?php echo $date_string; ?></time>
<?php endif; ?>