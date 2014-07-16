<?php
/**
 * @package Make
 */
global $ttfmake_current_location;

$author_layout_key = 'layout-' . ttfmake_get_view() . '-post-author-location';
$author_option     = ttfmake_sanitize_choice( get_theme_mod( $author_layout_key, ttfmake_get_default( $author_layout_key ) ), $author_layout_key );

$date_layout_key = 'layout-' . ttfmake_get_view() . '-post-date-location';
$date_option     = ttfmake_sanitize_choice( get_theme_mod( $date_layout_key, ttfmake_get_default( $date_layout_key ) ), $date_layout_key );

$comment_count_layout_key = 'layout-' . ttfmake_get_view() . '-comment-count-location';
$comment_count_option     = ttfmake_sanitize_choice( get_theme_mod( $comment_count_layout_key, ttfmake_get_default( $comment_count_layout_key ) ), $comment_count_layout_key );

if ( $ttfmake_current_location === $author_option ) :
	get_template_part( 'partials/entry', 'author' );
endif;

if ( $ttfmake_current_location === $comment_count_option ) :
	get_template_part( 'partials/entry', 'comment-count' );
endif;

if ( $ttfmake_current_location === $date_option ) :
	get_template_part( 'partials/entry', 'date' );
endif;