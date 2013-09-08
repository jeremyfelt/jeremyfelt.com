<?php
/**
 * @package Snap Child
 */

add_action( 'wp_head', 'jf_add_header_meta' );
function jf_add_header_meta() {
	?>
	<link rel="me" type="text/html" href="http://www.google.com/profiles/jeremy.felt"/>
	<?php
}

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