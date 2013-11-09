<?php

function mp6_widgets_enqueue_scripts() {

	wp_enqueue_script( 'mp6-widgets', plugins_url( 'scripts.js', __FILE__ ), array( 'admin-widgets' ), '20131030', true );
	wp_enqueue_style( 'mp6-widgets', plugins_url( 'styles.css', __FILE__ ) );

}

add_action( 'admin_enqueue_scripts', 'mp6_widgets_enqueue_scripts' );
