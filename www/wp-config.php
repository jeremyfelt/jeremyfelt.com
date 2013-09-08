<?php
/**
 * Configure WordPress
 */

define( 'WP_MEMORY_LIMIT', '128M' );

if ( '10.10.12.13' === $_SERVER['SERVER_ADDR'] ) {
	include( dirname( __FILE__ ) . '/local-config.php' );
	define( 'WP_LOCAL_DEV', true );
} else {
	include( dirname( __FILE__ ) . '/remote-config.php' );
	define( 'WP_LOCAL_DEV', false );
}

$table_prefix  = 'wp_';

define('WPLANG', '');

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) )
	define( 'ABSPATH', dirname( __FILE__ ) . '/wordpress/' );

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );
