<?php
/*
Plugin Name: Goodreads Import
Plugin URI: http://www.jeremyfelt.com/wordpress/plugins/goodreads-import/
Description: Imports your Goodreads data into WordPress
Version: 0.1
Author: Jeremy Felt
Author URI: http://www.jeremyfelt.com
License: GPL2
*/

/*  Copyright 2011 Jeremy Felt (email: jeremy.felt@gmail.com)

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

add_action( 'init', 'gi_create_goodreads_type' );
function gi_create_goodreads_type() {
    register_post_type( 'gi_goodreads',
        array(
            'labels' => array(
                'name' => __( 'Goodreads' ),
                'singular_name' => __( 'Goodreads' ),
                'all_items' => __( 'All Goodreads' ),
                'add_new_item' => __( 'Add New Goodreads' ),
                'edit_item' => __( 'Edit Goodreads' ),
                'new_item' => __( 'New Goodreads' ),
                'view_item' => __( 'View Goodreads' ),
                'search_items' => __( 'Search Goodreads' ),
                'not_found' => __( 'No Goodreads found' ),
                'not_found_in_trash' => __( 'No Goodreads found in trash' ),
            ),
        'description' => 'Goodreads updates captured through the Goodreads Import plugin!',
        'public' => true,
        'menu_position' => 5,
        'hierarchical' => false,
        'supports' => array (
                'title',
                'editor',
                'author',
                'thumbnail',
            ),
        'has_archive' => true,
        'rewrite' => array( 'slug' => 'goodreads' ),
        )
    );
}