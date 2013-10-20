<?php
/**
 * @package Snap Child
 */

add_filter( 'jetpack_enable_open_graph', 'jf_disable_jetpack_opengraph', 999 );
add_filter( 'jetpack_enable_opengraph', 'jf_disable_jetpack_opengraph', 999 );
/**
 * Jetpack does a blind output of opengraph data, which conflicts with Yoast SEO
 * 
 * @return bool false to ensure that Jetpack does not try to output opengraph tagging
 */
function jf_disable_jetpack_opengraph() {
	return false;
}

add_action( 'after_setup_theme', 'jf_support_post_formats', 99 );
/**
 * Snap doesn't support post formats by default, but I'd still like to track them
 * when I create posts as part of a longer term strategy to have some kind of
 * unique display or handling for each.
 */
function jf_support_post_formats() {
	add_theme_support( 'post-formats', array( 'aside', 'image', 'gallery', 'link', 'quote', 'status', 'video', 'audio', 'chat' ) );
}

add_action( 'wp_enqueue_scripts', 'jf_enqueue_styles', 9 );
/**
 * Enqueue the parent theme style and custom Google fonts before the child theme
 * stylesheet is loaded.
 */
function jf_enqueue_styles() {
	wp_enqueue_style( 'jf-snap-fonts', 'http://fonts.googleapis.com/css?family=News+Cycle:400,700|Oxygen:400,700|Raleway:400,700' );
	wp_enqueue_style( 'snap-parent-style', get_template_directory_uri() . '/style.css' );
}