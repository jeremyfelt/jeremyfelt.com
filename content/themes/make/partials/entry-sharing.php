<?php
/**
 * @package Make
 */

// Jetpack Sharing buttons
if ( function_exists( 'sharing_display' ) ) {
	sharing_display( '', true );
}

// Jetpack Like button
if ( class_exists( 'Jetpack_Likes' ) ) {
	$custom_likes = new Jetpack_Likes;
	echo $custom_likes->post_likes( '' );
}