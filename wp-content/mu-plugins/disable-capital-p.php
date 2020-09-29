<?php
/**
 * Plugin Name:     Disable Capital P Dangit.
 * Description:     Some code in posts actually requires a lowercase P.
 * Author:          jeremyfelt
 * Author URI:      https://jeremyfelt.com
 * Version:         0.0.1
 */

remove_filter( 'the_title', 'capital_P_dangit', 11 );
remove_filter( 'the_content', 'capital_P_dangit', 11 );
remove_filter( 'comment_text', 'capital_P_dangit', 31 );
