<?php
/*
Plugin Name: React
Description: 💩 Reactions.
Version: 0.1-20160329
Text Domain: react
*/

define( 'REACT_URL', plugins_url( '', __FILE__ ) );

function react_load() {
	if ( ! class_exists( 'WP_REST_Posts_Controller' ) ) {
		return;
	}

	load_plugin_textdomain( 'react' );

	require_once( dirname( __FILE__ ) . '/lib/class-wp-rest-react-controller.php' );

	require_once( dirname( __FILE__ ) . '/lib/class-react.php' );

	add_action( 'init', array( 'React', 'init' ) );
}

add_action( 'plugins_loaded', 'react_load' );
