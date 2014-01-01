<?php
/**
 * @package Basis Child
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
