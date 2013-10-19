<?php
/**
 * Modify what is used when sending emails from WordPress
 */

add_filter( 'wp_mail_from', 'jf_mail_from' );
/**
 * Foghlaim.com has been setup with DKIM and there's probably a way to make it
 * so all the domains are, but I'm not ready to go that far. :) This makes it
 * easier to ensure that the emails from WordPress won't be caught by spam.
 *
 * @return string Mail address to use for 'from'
 */
function jf_mail_from() {
	return 'wordpress@foghlaim.com';
}

add_filter( 'wp_mail_from_name', 'jf_mail_from_name' );
/**
 * Several sites may share the same from email, so the name needs to differentiate
 * a bit between them.
 *
 * @return string From name
 */
function jf_mail_from_name() {
	return 'WordPress on Foghlaim';
}
