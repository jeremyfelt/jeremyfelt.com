<?php
/*
Plugin Name: Global Admin Search
Plugin URI: https://github.com/georgestephanis/omnisearch
Description: Enables a Global Admin Search in WordPress. Feature pitch for Core in 3.8.
Author: George Stephanis
Version: 0.9.1
Author URI: http://stephanis.info/
*/

if ( is_admin() ) {
	require_once( dirname(__FILE__) . '/wp-admin/includes/class-wp-search.php' );
}
