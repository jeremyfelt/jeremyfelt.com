<?php

add_action( 'wp_enqueue_scripts', 'jf_enqueue_parent_styles' );
function jf_enqueue_parent_styles() {
	wp_enqueue_style( 'jf-parent-style', get_template_directory_uri() . '/style.css' );
}