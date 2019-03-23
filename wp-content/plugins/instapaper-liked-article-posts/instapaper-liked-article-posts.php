<?php
/*
Plugin Name: Instapaper Liked Article Posts
Plugin URI: http://www.jeremyfelt.com/wordpress/plugins/instapaper-liked-article-posts/
Description: Checks your Instapaper 'Liked' article RSS feed and creates new posts with that data. Another step towards owning your data.
Version: 0.3
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

/*  The stuff we only want to do as an admin. */
if ( is_admin() ){
    /*  Things happen when we activate and deactivate the plugin of course. */
    register_activation_hook( __FILE__, 'ilap_plugin_activation' );
    register_deactivation_hook( __FILE__, 'ilap_plugin_deactivation' );

    /*  Add a fancy icon to the custom post type page in wp-admin. */
    add_action( 'admin_head', 'ilap_edit_icon' );
    /*  Add our custom settings to the admin menu. */
    add_action( 'admin_menu', 'ilap_add_settings' );
    /*  Register all of the custom settings. */
    add_action( 'admin_init', 'ilap_register_settings' );
    /*  Make a pretty link for settings under the plugin information. */
    add_filter( 'plugin_action_links', 'ilap_plugin_action_links', 10, 2);
}

/*  Of course, the custom post type needs to be there for everyone, so we call this outside of the admin check. */
add_action( 'init', 'ilap_create_instapaper_type' );

/*  And we want to make sure the cron tasks are there for everyone as well. */
add_action( 'ilap_hourly_action', 'ilap_on_the_hour' );

function ilap_plugin_activation(){
    $current_ilap_options = get_option( 'ilap_options' );
    $ilap_options[ 'instapaper_rss_feed' ] = $current_ilap_options[ 'instapaper_rss_feed' ] ? $current_ilap_options[ 'instapaper_rss_feed' ] : '';
    $ilap_options[ 'post_type' ] = $current_ilap_options[ 'post_type' ] ? $current_ilap_options[ 'post_type' ] : 'ilap_instapaper';
    $ilap_options[ 'post_status' ] = $current_ilap_options[ 'post_status' ] ? $current_ilap_options[ 'post_status' ] : 'publish';
    $ilap_options[ 'fetch_interval' ] = $current_ilap_options[ 'fetch_interval' ] ? $current_ilap_options[ 'fetch_interval' ] : 'hourly';
    $ilap_options[ 'max_fetch_items' ] = $current_ilap_options[ 'max_fetch_items' ] ? $current_ilap_options[ 'max_fetch_items' ] : 5;
    add_option( 'ilap_options', $ilap_options );

    /*  Create the custom post type upon activation. */
    ilap_create_instapaper_type();

    /*  Flush the rewrite rules so that the new custom post type works. */
    flush_rewrite_rules( false );

    /*  Schedule the first CRON even to happen 30 seconds from now, then hourly after that. */
    wp_schedule_event( ( time() + 30 ) , 'hourly', 'ilap_hourly_action' );

}

function ilap_plugin_deactivation(){
    /*  If the plugin is deactivated, we want to clear our hourly CRON to keep things clean. */
    wp_clear_scheduled_hook( 'ilap_hourly_action' );
}

function ilap_plugin_action_links( $links, $file ){
    /*  Function gratefully taken (and barely modified) from Pippin Williamson's
        WPMods article: http://www.wpmods.com/adding-plugin-action-links/ */
    static $this_plugin;
    if ( ! $this_plugin ) {
        $this_plugin = plugin_basename( __FILE__ );
    }
    /* check to make sure we are on the correct plugin */
    if ( $file == $this_plugin ){
        $settings_path = '/wp-admin/options-general.php?page=instapaper-liked-article-posts-settings';
        $settings_link = '<a href="' . get_bloginfo( 'wpurl' ) . $settings_path . '">' . __( 'Settings', 'instapaper-liked-article-posts' ) . '</a>';
        array_unshift( $links, $settings_link );  // add the link to the list
    }
    return $links;
}

function ilap_edit_icon(){
    global $post_type;
    if ( 'ilap_instapaper' == $post_type ) {
        echo '<style>#icon-edit { background: url("' . plugins_url( 'images/instapaper-48.png', __FILE__ ) . '") no-repeat;
                                  background-size: 32px 32px; }</style>';
    }
}

function ilap_add_settings(){
    /*  Add the sub-menu item under the Settings top-level menu. */
    add_options_page( __('Instapaper Posts', 'instapaper-liked-article-posts' ), __('Instapaper Posts', 'instapaper-liked-article-posts'), 'manage_options', 'instapaper-liked-article-posts-settings', 'ilap_view_settings' );
}

function ilap_view_settings(){

    /*  Display the main settings view for Instapaper Liked Article Posts. */
    echo '<div class="wrap">
        <div class="icon32" id="icon-options-general"></div>
            <h2>' . __( 'Instapaper Liked Article Posts', 'instapaper-liked-article-posts' ) . '</h2>
            <h3>' . __( 'Overview', 'instapaper-liked-article-posts' ) . ':</h3>
            <p style="margin-left:12px;max-width:640px;">
            ' . __( 'The settings below will help determine where to check for your Instapaper Liked items, how often to
            look for them, and how they should be stored once new items are found.', 'instapaper-liked-article-posts' ) . '</p>
            <p style="margin-left:12px;max-width:640px;">
                The most important part of this process will be to determine the RSS feed for your Instapaper Liked items.
            </p>
            <ol style="margin-left:36px;">
                <li>Visit your <a href="http://www.instapaper.com/liked">Instapaper Liked items</a> page. <em>(http://www.instapaper.com/liked)</em></li>
                <li>Scroll to the bottom of that page.</li>
                <li>Look for the link labeled "This folder\'s RSS" next to the orange RSS icon.
                    <img src="' . plugins_url( '/images/rss.png', __FILE__ ) . '"></li>
                <li>Copy this link and paste it into the "Instapaper RSS Feed" setting below these instructions.</li>
            </ol>';

       echo '<form method="post" action="options.php">';

    settings_fields( 'ilap_options' );
    do_settings_sections( 'ilap' ); // Display the main section of settings.

    echo '<p class="submit"><input type="submit" class="button-primary" value="';
    _e( 'Save Changes', 'instapaper-liked-article-posts' );
    echo '" />
            </p>
            </form>
        </div>';
}

function ilap_register_settings(){
    /*  Register the settings we want available for this. */
    register_setting( 'ilap_options', 'ilap_options', 'ilap_options_validate' );
    add_settings_section( 'ilap_section_main', '', 'ilap_section_text', 'ilap' );
    add_settings_section( 'ilap_section_post_type', '', 'ilap_section_post_type_text', 'ilap' );
    add_settings_section( 'ilap_section_interval', '', 'ilap_section_interval_text', 'ilap' );
    add_settings_field( 'ilap_instapaper_rss_feed', 'Instapaper RSS Feed:', 'ilap_instapaper_rss_feed_text', 'ilap', 'ilap_section_main' );
    add_settings_field( 'ilap_max_fetch_items', 'Max Items To Fetch:', 'ilap_max_fetch_items_text', 'ilap', 'ilap_section_main' );
    add_settings_field( 'ilap_post_type', 'Post Type:', 'ilap_post_type_text', 'ilap', 'ilap_section_post_type' );
    add_settings_field( 'ilap_post_status', __( 'Default Post Status:', 'instapaper-liked-article-posts' ) , 'ilap_post_status_text', 'ilap', 'ilap_section_post_type' );
    add_settings_field( 'ilap_fetch_interval', 'Feed Fetch Interval: ', 'ilap_fetch_interval_text', 'ilap', 'ilap_section_interval' );
}

function ilap_section_text(){
    /*  Placeholder for later. Nothing really needed at the moment. */
}

function ilap_section_post_type_text(){
    echo '<h3>Custom Or Default Post Type</h3>
    <p style="margin-left:12px;max-width: 640px;">A new custom post type that adds an \'Instapaper\' slug to new items has been added and selected by default.
    You can change this to any other available post type if you would like.</p>';
}

function ilap_section_interval_text(){
    echo '<h3>RSS Fetch Frequency</h3>
        <p style="margin-left:12px;max-width: 630px;">This plugin currently depends on WP Cron operating fully as expected. In most cases, you should
        be able to select one of the intervals below and things will work as expected. If not, please let <a href="http://www.jeremyfelt.com">me</a> know. By
        default, we check for new items on an hourly basis.</p>';
    $seconds_till_cron = wp_next_scheduled( 'ilap_hourly_action' ) - time();
    $user_next_cron = date( 'H:i:sA', wp_next_scheduled( 'ilap_hourly_action' ) + ( get_option( 'gmt_offset' ) * 3600 ) );
    echo '<p style="margin-left:12px;">The next check is scheduled to run at ' . $user_next_cron . ', which occurs in ' . $seconds_till_cron . ' seconds.</p>';
}

function ilap_instapaper_rss_feed_text(){
    $ilap_options = get_option( 'ilap_options' );
    echo '<input style="width: 400px;" type="text" id="ilap_instapaper_rss_feed"
                             name="ilap_options[instapaper_rss_feed]"
                             value="' . esc_url( $ilap_options[ 'instapaper_rss_feed' ] ) . '">';
    echo '<br><em>http://www.instapaper.com/starred/rss/######/YYYYYYYYYYYYYY</em>';
}

function ilap_post_type_text(){
    $ilap_options = get_option( 'ilap_options' );
    $post_types = array( 'post', 'link' );
    $all_post_types = get_post_types( array( '_builtin' => false ) );

    foreach( $all_post_types as $p=>$k ){
        $post_types[] = $p;
    }

    echo '<select id="ilap_post_type" name="ilap_options[post_type]">';

    foreach( $post_types as $pt ){
        echo '<option value="' . $pt . '"';

        if ( $pt == $ilap_options[ 'post_type' ] ) echo ' selected="yes" ';

        echo '>' . $pt . '</option>';
    }
}

function ilap_post_status_text(){
    $ilap_options = get_option( 'ilap_options' );

    /*  TODO: Definitely a better way to do this. See above function and do that. */
    $s1 = '';
    $s2 = '';
    $s3 = '';

    if( 'draft' == $ilap_options[ 'post_status' ] ){
        $s1 = 'selected="yes"';
    }elseif( 'publish' == $ilap_options[ 'post_status' ] ){
        $s2 = 'selected="yes"';
    }elseif( 'private' == $ilap_options[ 'post_status' ] ){
        $s3 = 'selected="yes"';
    }else{
        $s2 = 'selected="yes"';
    }

    echo '<select id="ilap_post_status" name="ilap_options[post_status]">
            <option value="draft" ' . $s1 . '>draft</option>
            <option value="publish" ' . $s2 . '>publish</option>
            <option value="private" ' . $s3 . '>private</option>
          </select>';
}

function ilap_fetch_interval_text(){
    /* TODO: Custom intervals can be added to a WordPress install, so we should query those and offer as an option. */
    $intervals = array( 'hourly', 'twicedaily', 'daily' );
    $ilap_options = get_option( 'ilap_options' );

    echo '<select id="ilap_fetch_interval" name="ilap_options[fetch_interval]">';

    foreach( $intervals as $i ){
        echo '<option value="' . $i . '" ';

        if( $i == $ilap_options[ 'fetch_interval' ] ) echo 'selected="yes"';

        echo '>' . $i . '</option>';
    }

    echo '</select>';
}

function ilap_max_fetch_items_text(){
    $ilap_options = get_option( 'ilap_options' );
    echo '<input type="text"
                 id="ilap_max_fetch_items"
                 name="ilap_options[max_fetch_items]"
                 value="' . $ilap_options[ 'max_fetch_items' ] . '">';
}

function ilap_options_validate( $input ) {
    /*  Validation of a drop down. Hmm. Well, if it isn't on our list, we'll force it onto our list. */
    $valid_post_status_options = array( 'draft', 'publish', 'private' );
    $valid_post_type_options = array( 'post', 'link' );
    $valid_fetch_interval_options = array( 'hourly', 'twicedaily', 'daily' );

    $all_post_types = get_post_types( array( '_builtin' => false ) );
    foreach( $all_post_types as $p=>$k ){
        $valid_post_type_options[] = $p;
    }

    if( ! in_array( $input[ 'post_status' ], $valid_post_status_options ) )
        $input[ 'post_status' ] = 'draft';

    if( ! in_array( $input[ 'post_type' ], $valid_post_type_options ) )
        $input[ 'post_type' ] = 'ilap_instapaper';

    if( ! in_array( $input[ 'fetch_interval' ], $valid_fetch_interval_options ) )
        $input[ 'fetch_interval' ] = 'hourly';

    /*  This seems to be the only place we can reset the scheduled Cron if the frequency is changed, so here goes. */
    wp_clear_scheduled_hook( 'ilap_hourly_action' );
    wp_schedule_event( ( time() + 30 ) , $input[ 'fetch_interval' ], 'ilap_hourly_action' );

    $input[ 'max_fetch_items' ] = absint( $input[ 'max_fetch_items' ] );

    return $input;
}

function ilap_create_instapaper_type(){
    /*  Add the custom post type 'ilap_instapaper' to WordPress. */
    register_post_type( 'ilap_instapaper',
        array(
            'labels' => array(
                'name' => __( 'Instapaper' ),
                'singular_name' => __( 'Instapaper Like' ),
                'all_items' => __( 'All Instapaper Likes' ),
                'add_new_item' => __( 'Add Instapaper Like' ),
                'edit_item' => __( 'Edit Instapaper Like' ),
                'new_item' => __( 'New Instapaper Like' ),
                'view_item' => __( 'View Instapaper Like' ),
                'search_items' => __( 'Search Instapaper Likes' ),
                'not_found' => __( 'No Instapaper Likes found' ),
                'not_found_in_trash' => __( 'No Instapaper Likes found in trash' ),
            ),
        'description' => 'Instapaper posts created by the Instapaper Liked Article Posts plugin.',
        'public' => true,
        'menu_icon' => plugins_url( '/images/instapaper-16.png', __FILE__ ),
        'menu_position' => 5,
        'hierarchical' => false,
        'supports' => array (
            'title',
            'editor',
            'author',
            'custom-fields',
            'comments',
            'revisions',
            ),
        'has_archive' => true,
        'rewrite' => array(
            'slug' => 'instapaper',
            'with_front' => false
            ),
        )
    );
}

function ilap_modify_feed_cache( $seconds ) {
    /* We know we'll want the freshest version of the feed every time we check, so there's
        no reason to set a cache higher than hourly. Set to 30 seconds just in case we
        update the settings in order to trigger capture without causing any confusion. */
    return 30;
}

function ilap_on_the_hour(){
    /*  Grab the configured Instapaper Liked RSS feed and create new posts based on that. */

    /*  Go get some options! */
    $instapaper_options = get_option( 'ilap_options' );
    /*  The feed URL we'll be grabbing. */
    $instapaper_feed_url = $instapaper_options[ 'instapaper_rss_feed' ];
    /*  The post type we'll be saving as. We designed it to be custom, but why not allow anything. */
    $post_type = $instapaper_options[ 'post_type' ];
    /*  The post status we'll use. */
    $post_status = $instapaper_options[ 'post_status' ];

    /*  Add a quick filter to change the default SimplePie cache time */
    add_filter( 'wp_feed_cache_transient_lifetime', 'ilap_modify_feed_cache' );
    /*  Now fetch with the WordPress SimplePie function. */
    $instapaper_feed = fetch_feed( $instapaper_feed_url );
    /*  We don't want to change anybody else's feed caching, so remove the filter. */
    remove_filter( 'wp_feed_cache_transient_lifetime', 'ilap_modify_feed_cache' );

    if ( ! is_wp_error( $instapaper_feed ) ){
        /*  Feed looks like a good object, continue. */
        $max_items = $instapaper_feed->get_item_quantity( absint( $instapaper_options[ 'max_fetch_items' ] ) );
        $instapaper_items = $instapaper_feed->get_items(0, $max_items);
        foreach( $instapaper_items as $item ){
            $item_link = $item->get_link();
            $item_title = $item->get_title();
            $item_description = $item->get_description();
            $item_hash = md5( $item_description );

            $item_content = '<p><a href="' . $item_link . '">' . $item_title . '</a></p>
                <p>' . $item_description . '</p>';

            if ( get_page_by_title( $item_title, 'OBJECT', $post_type ) ){
                /*  Title already exists. */
                $existing_hash = get_post_meta( get_page_by_title( $item_title, 'OBJECT', $post_type )->ID, 'ilap_hash', true );

                if ( $item_hash == $existing_hash ){
                    $skip = 1;
                }else{
                    $skip = NULL;
                }
            }else{
                $skip = NULL;
            }

            if ( ! $skip ){

                $insta_post = array(
                    'post_title' => $item_title,
                    'post_content' => $item_content,
                    'post_author' => 1,
                    'post_status' => $post_status,
                    'post_type' => $post_type,
                );

                $item_post_id = wp_insert_post( $insta_post );
                add_post_meta( $item_post_id, 'ilap_hash', $item_hash, true );
            }
        }
    }else{
        /*  Uhhh, feels a little shady to die silently, but for now that's all we got. */
    }
}
