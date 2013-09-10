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