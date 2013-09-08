<?php
/*
Plugin Name: WordPress Linkblogger
Plugin URI: http://github.com/jeremyfelt/WordPress-Linkblogger
Description: Allows 'retweeting' of content from supported linkblogs
Version: 0.1
Author: Jeremy Felt
Author URI: http://jeremyfelt.com
License: GPL2
*/

/*  Copyright 2012 Jeremy Felt (email: jeremy.felt@gmail.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

add_action( 'init', 'jf_capture_linkblog_request' );
function jf_capture_linkblog_request() {

	if ( isset( $_SERVER['HTTP_REFERER'] ) && '' != $_SERVER['HTTP_REFERER'] && isset( $_GET['link'] ) && isset( $_GET['title'] ) && isset( $_GET['description'] ) )
		$url_data = parse_url( $_SERVER['HTTP_REFERER'] );
	else
		return;

	$valid_referral_hosts = array( 'tabs.mediahackers.org' );

	if ( ! isset( $url_data['host'] ) || ! in_array( $url_data['host'], $valid_referral_hosts ) )
		return;

	if ( ! is_user_logged_in() )
		return;

	$post_content = wp_kses_post( $_GET['description'] );
	$post_content .= '<br><br>Article: <a href="' . esc_url_raw( $_GET['link'] ) . '" title="' . sanitize_text_field( $_GET['title'] ) . '">' . esc_url_raw( $_GET['link'] ) . '</a>';
	$post_content .= '<br>Link Via: <a href="' . esc_url_raw( $_SERVER['HTTP_REFERER'] ) . '">' . esc_url_raw( $_SERVER['HTTP_REFERER'] ) . '</a>';

	$new_post_id = wp_insert_post( array(
	                                    'post_type' => 'post',
	                                    'post_content' => $post_content,
	                                    'post_title' => sanitize_text_field( $_GET['title'] ),
	                                    'post_status' => 'publish',
	                               ));

	if ( is_wp_error( $new_post_id ) || 0 == absint( $new_post_id ) )
		return;

	update_post_meta( $new_post_id, 'linkblog_url', esc_url_raw( $_GET['link'] ) );

	wp_redirect( $_SERVER['HTTP_REFERER'] );
}