<?php
/*
Plugin Name: Hide Inactive Jetpack Modules
Plugin URI: http://github.com
Description: Hides Jetpack modules that are marked as inactive, providing for a shorter list.
Version: 0.1
Author: Jeremy Felt
Author URI: http://jeremyfelt.com
License: GPL2
*/

add_action( 'admin_print_styles-toplevel_page_jetpack', 'hijm_admin_style', 999 );
function hijm_admin_style() {
	echo '<!-- Hide inactive Jetpack modules --><style>.jetpack-inactive, #vaultpress { display: none; }</style>';
}
