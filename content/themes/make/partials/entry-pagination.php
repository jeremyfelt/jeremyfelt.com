<?php
/**
 * @package Make
 */
?>

<?php
if ( function_exists( 'wp_pagenavi' ) ) :
	wp_pagenavi( array( 'type' => 'multipart' ) );
else :
	wp_link_pages( array(
		'before' => '<nav class="entry-pagination">' . __( 'Pages:', 'make' ),
		'after'  => '</nav>',
	) );
endif;
?>