<?php
/**
 * Jetpack Compatibility File
 * See: http://jetpack.me/
 *
 * @package Resonar
 */

/**
 * Add theme support for Infinite Scroll.
 * See: http://jetpack.me/support/infinite-scroll/
 */
function resonar_jetpack_setup() {
	add_theme_support( 'infinite-scroll', array(
		'container' => 'main',
		'footer'    => 'page',
	) );
}
add_action( 'after_setup_theme', 'resonar_jetpack_setup' );

/**
 * Add support for the Site Logo
 *
 * @since Resonar 1.0
 */
function resonar_site_logo_init() {
	add_image_size( 'resonar-logo', 192, 192 );
	add_theme_support( 'site-logo', array( 'size' => 'resonar-logo' ) );
}
add_action( 'after_setup_theme', 'resonar_site_logo_init' );

/**
 * Return early if Site Logo is not available.
 *
 * @since Resonar 1.0
 */
function resonar_the_site_logo() {
	if ( ! function_exists( 'jetpack_the_site_logo' ) ) {
		return;
	} else {
		jetpack_the_site_logo();
	}
}

/**
 * Add theme support for Responsive Videos
 *
 * @since Resonar 1.0
 */
function resonar_responsive_videos_init() {
	add_theme_support( 'jetpack-responsive-videos' );
}
add_action( 'after_setup_theme', 'resonar_responsive_videos_init' );

/**
 * Overwritte default gallery widget content width.
 *
 * @since Resonar 1.0
 */
function resonar_gallery_widget_content_width( $width ) {
	return 576;
}
add_filter( 'gallery_widget_content_width', 'resonar_gallery_widget_content_width');
