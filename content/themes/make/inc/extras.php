<?php
/**
 * @package Make
 */

if ( ! function_exists( 'ttfmake_page_menu_args' ) ) :
/**
 * Get our wp_nav_menu() fallback, wp_page_menu(), to show a home link.
 *
 * @since  1.0.0.
 *
 * @param  array    $args    Configuration arguments.
 * @return array             Modified page menu args.
 */
function ttfmake_page_menu_args( $args ) {
	$args['show_home'] = true;
	return $args;
}
endif;

add_filter( 'wp_page_menu_args', 'ttfmake_page_menu_args' );

if ( ! function_exists( 'ttfmake_body_classes' ) ) :
/**
 * Adds custom classes to the array of body classes.
 *
 * @since  1.0.0.
 *
 * @param  array    $classes    Classes for the body element.
 * @return array                Modified class list.
 */
function ttfmake_body_classes( $classes ) {
	// Left Sidebar
	if ( true === ttfmake_has_sidebar( 'left' ) ) {
		$classes[] = 'has-left-sidebar';
	}

	// Right Sidebar
	if ( true === ttfmake_has_sidebar( 'right' ) ) {
		$classes[] = 'has-right-sidebar';
	}

	return $classes;
}
endif;

add_filter( 'body_class', 'ttfmake_body_classes' );

if ( ! function_exists( 'ttfmake_wp_title' ) ) :
/**
 * Filters wp_title to print a neat <title> tag based on what is being viewed.
 *
 * @since  1.0.0.
 *
 * @param  string    $title    Default title text for current view.
 * @param  string    $sep      Optional separator.
 *
 * @return string              The filtered title.
 */
function ttfmake_wp_title( $title, $sep ) {
	global $page, $paged;

	if ( is_feed() ) {
		return $title;
	}

	// Add the blog name
	$title .= get_bloginfo( 'name' );

	// Add the blog description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) ) {
		$title .= " $sep $site_description";
	}

	// Add a page number if necessary:
	if ( $paged >= 2 || $page >= 2 ) {
		$title .= " $sep " . sprintf( __( 'Page %s', 'make' ), max( $paged, $page ) );
	}

	return $title;
}
endif;

add_filter( 'wp_title', 'ttfmake_wp_title', 10, 2 );

if ( ! function_exists( 'ttfmake_setup_author' ) ) :
/**
 * Sets the authordata global when viewing an author archive.
 *
 * This provides backwards compatibility with
 * http://core.trac.wordpress.org/changeset/25574
 *
 * It removes the need to call the_post() and rewind_posts() in an author
 * template to print information about the author.
 *
 * @global WP_Query $wp_query WordPress Query object.
 *
 * @since  1.0.0.
 *
 * @return void
 */
function ttfmake_setup_author() {
	global $wp_query;

	if ( ! isset( $GLOBALS['authordata'] ) && $wp_query->is_author() && isset( $wp_query->post ) ) {
		$GLOBALS['authordata'] = get_userdata( $wp_query->post->post_author );
	}
}
endif;

add_action( 'wp', 'ttfmake_setup_author' );

if ( ! function_exists( 'sanitize_hex_color' ) ) :
/**
 * Sanitizes a hex color.
 *
 * This is a copy of the core function for use when the customizer is not being shown.
 *
 * @since  1.0.0.
 *
 * @param  string         $color    The proposed color.
 * @return string|null              The sanitized color.
 */
function sanitize_hex_color( $color ) {
	if ( '' === $color ) {
		return '';
	}

	// 3 or 6 hex digits, or the empty string.
	if ( preg_match('|^#([A-Fa-f0-9]{3}){1,2}$|', $color ) ) {
		return $color;
	}

	return null;
}
endif;

if ( ! function_exists( 'sanitize_hex_color_no_hash' ) ) :
/**
 * Sanitizes a hex color without a hash. Use sanitize_hex_color() when possible.
 *
 * This is a copy of the core function for use when the customizer is not being shown.
 *
 * @since  1.0.0.
 *
 * @param  string         $color    The proposed color.
 * @return string|null              The sanitized color.
 */
function sanitize_hex_color_no_hash( $color ) {
	$color = ltrim( $color, '#' );

	if ( '' === $color ) {
		return '';
	}

	return sanitize_hex_color( '#' . $color ) ? $color : null;
}
endif;

if ( ! function_exists( 'maybe_hash_hex_color' ) ) :
/**
 * Ensures that any hex color is properly hashed.
 *
 * This is a copy of the core function for use when the customizer is not being shown.
 *
 * @since  1.0.0.
 *
 * @param  string         $color    The proposed color.
 * @return string|null              The sanitized color.
 */
function maybe_hash_hex_color( $color ) {
	if ( $unhashed = sanitize_hex_color_no_hash( $color ) ) {
		return '#' . $unhashed;
	}

	return $color;
}
endif;

if ( ! function_exists( 'ttfmake_excerpt_more' ) ) :
/**
 * Modify the excerpt suffix
 *
 * @since 1.0.0.
 *
 * @param string $more
 *
 * @return string
 */
function ttfmake_excerpt_more( $more ) {
	return ' &hellip;';
}
endif;

add_filter( 'excerpt_more', 'ttfmake_excerpt_more' );

if ( ! function_exists( 'ttfmake_get_view' ) ) :
/**
 * Determine the current view.
 *
 * For use with view-related theme options.
 *
 * @since  1.0.0.
 *
 * @return string    The string representing the current view.
 */
function ttfmake_get_view() {
	// Post types
	$post_types = get_post_types(
		array(
			'public' => true,
			'_builtin' => false
		)
	);
	$post_types[] = 'post';

	// Post parent
	$parent_post_type = '';
	if ( is_attachment() ) {
		$post_parent      = get_post()->post_parent;
		$parent_post_type = get_post_type( $post_parent );
	}

	$view = 'post';

	// Blog
	if ( is_home() ) {
		$view = 'blog';
	}
	// Archives
	else if ( is_archive() ) {
		$view = 'archive';
	}
	// Search results
	else if ( is_search() ) {
		$view = 'search';
	}
	// Posts and public custom post types
	else if ( is_singular( $post_types ) || ( is_attachment() && in_array( $parent_post_type, $post_types ) ) ) {
		$view = 'post';
	}
	// Pages
	else if ( is_page() || ( is_attachment() && 'page' === $parent_post_type ) ) {
		$view = 'page';
	}

	/**
	 * Allow developers to dynamically change the view.
	 *
	 * @since 1.2.3.
	 *
	 * @param string    $view                The view name.
	 * @param string    $parent_post_type    The post type for the parent post of the current post.
	 */
	return apply_filters( 'make_get_view', $view, $parent_post_type );
}
endif;

if ( ! function_exists( 'ttfmake_has_sidebar' ) ) :
/**
 * Determine if the current view should show a sidebar in the given location.
 *
 * @since  1.0.0.
 *
 * @param  string    $location    The location to test for.
 * @return bool                   Whether or not the location has a sidebar.
 */
function ttfmake_has_sidebar( $location ) {
	global $wp_registered_sidebars;

	// Validate the sidebar location
	if ( ! in_array( 'sidebar-' . $location, array_keys( $wp_registered_sidebars ) ) ) {
		return false;
	}

	// Get the view
	$view = ttfmake_get_view();

	// Get the relevant option
	$show_sidebar = (bool) get_theme_mod( 'layout-' . $view . '-sidebar-' . $location, ttfmake_get_default( 'layout-' . $view . '-sidebar-' . $location ) );

	// Builder template doesn't support sidebars
	if ( 'page' === $view && 'template-builder.php' === get_page_template_slug() ) {
		$show_sidebar = false;
	}

	/**
	 * Allow developers to dynamically changed the result of the "has sidebar" check.
	 *
	 * @since 1.2.3.
	 *
	 * @param bool      $show_sidebar    Whether or not to show the sidebar.
	 * @param string    $location        The location of the sidebar being evaluated.
	 * @param string    $view            The view name.
	 */

	return apply_filters( 'make_has_sidebar', $show_sidebar, $location, $view );
}
endif;

if ( ! function_exists( 'ttfmake_sidebar_description' ) ) :
/**
 * Output a sidebar description that reflects its current status.
 *
 * @since  1.0.0.
 *
 * @param  string    $sidebar_id    The sidebar to look up the description for.
 * @return string                   The description.
 */
function ttfmake_sidebar_description( $sidebar_id ) {
	$description = '';

	// Footer sidebars
	if ( false !== strpos( $sidebar_id, 'footer-' ) ) {
		$column = (int) str_replace( 'footer-', '', $sidebar_id );
		$column_count = (int) get_theme_mod( 'footer-widget-areas', ttfmake_get_default( 'footer-widget-areas' ) );

		if ( $column > $column_count ) {
			$description = __( 'This widget area is currently disabled. Enable it in the "Footer" panel of the Customizer.', 'make' );
		}
	}
	// Other sidebars
	else if ( false !== strpos( $sidebar_id, 'sidebar-' ) ) {
		$location = str_replace( 'sidebar-', '', $sidebar_id );

		$enabled_views = ttfmake_sidebar_list_enabled( $location );

		// Not enabled anywhere
		if ( empty( $enabled_views ) ) {
			$description = __( 'This widget area is currently disabled. Enable it in the "Content & Layout" panel of the Customizer.', 'make' );
		}
		// List enabled views
		else {
			$description = sprintf(
				__( 'This widget area is currently enabled for the following views: %s. Change this in the "Content & Layout" panel of the Customizer.', 'make' ),
				esc_html( implode( _x( ', ', 'list item separator', 'make' ), $enabled_views ) )
			);
		}
	}

	return esc_html( $description );
}
endif;

if ( ! function_exists( 'ttfmake_sidebar_list_enabled' ) ) :
/**
 * Compile a list of views where a particular sidebar is enabled.
 *
 * @since  1.0.0.
 *
 * @param  string    $location    The sidebar to look up.
 * @return array                  The sidebar's current locations.
 */
function ttfmake_sidebar_list_enabled( $location ) {
	$enabled_views = array();

	$views = array(
		'blog'    => __( 'Blog (Post Page)', 'make' ),
		'archive' => __( 'Archives', 'make' ),
		'search'  => __( 'Search Results', 'make' ),
		'post'    => __( 'Posts', 'make' ),
		'page'    => __( 'Pages', 'make' ),
	);

	foreach ( $views as $view => $label ) {
		$option = (bool) get_theme_mod( 'layout-' . $view . '-sidebar-' . $location, ttfmake_get_default( 'layout-' . $view . '-sidebar-' . $location ) );
		if ( true === $option ) {
			$enabled_views[] = $label;
		}
	}

	/**
	 * Filter the list of sidebars that are available for a specific location.
	 *
	 * @since 1.2.3.
	 *
	 * @param array    $enabled_views    The list of views enabled for the sidebar.
	 * @param string   $location         The location of the sidebar being evaulated.
	 */
	return apply_filters( 'make_sidebar_list_enabled', $enabled_views, $location );
}
endif;

/**
 * Generate a link to the Make info page.
 *
 * @since  1.0.6.
 *
 * @param  string    $component    The component where the link is located.
 * @return string                  The link.
 */
function ttfmake_get_plus_link( $component ) {
	$url = 'https://thethemefoundry.com/wordpress-themes/make/#make-table';
	return esc_url( $url );
}

/**
 * Add notice if Make Plus is installed as a theme.
 *
 * @since  1.1.2.
 *
 * @param  string         $source           File source location.
 * @param  string         $remote_source    Remove file source location.
 * @param  WP_Upgrader    $upgrader         WP_Upgrader instance.
 * @return WP_Error                         Error or source on success.
 */
function ttfmake_check_package( $source, $remote_source, $upgrader ) {
	global $wp_filesystem;

	if ( ! isset( $_GET['action'] ) || 'upload-theme' !== $_GET['action'] ) {
		return $source;
	}

	if ( is_wp_error( $source ) ) {
		return $source;
	}

	// Check the folder contains a valid theme
	$working_directory = str_replace( $wp_filesystem->wp_content_dir(), trailingslashit( WP_CONTENT_DIR ), $source );
	if ( ! is_dir( $working_directory ) ) { // Sanity check, if the above fails, lets not prevent installation.
		return $source;
	}

	// A proper archive should have a style.css file in the single subdirectory
	if ( ! file_exists( $working_directory . 'style.css' ) && strpos( $source, 'make-plus-' ) >= 0 ) {
		return new WP_Error( 'incompatible_archive_theme_no_style', $upgrader->strings[ 'incompatible_archive' ], __( 'The uploaded package appears to be a plugin. PLEASE INSTALL AS A PLUGIN.', 'make' ) );
	}

	return $source;
}

add_filter( 'upgrader_source_selection', 'ttfmake_check_package', 9, 3 );

if ( ! function_exists( 'ttfmake_get_section_data' ) ) :
/**
 * Retrieve all of the data for the sections.
 *
 * @since  1.2.0.
 *
 * @param  string    $post_id    The post to retrieve the data from.
 * @return array                 The combined data.
 */
function ttfmake_get_section_data( $post_id ) {
	$ordered_data = array();
	$ids          = get_post_meta( $post_id, '_ttfmake-section-ids', true );
	$ids          = ( ! empty( $ids ) && is_array( $ids ) ) ? array_map( 'strval', $ids ) : $ids;
	$post_meta    = get_post_meta( $post_id );

	// Temp array of hashed keys
	$temp_data = array();

	// Any meta containing the old keys should be deleted
	if ( is_array( $post_meta ) ) {
		foreach ( $post_meta as $key => $value ) {
			// Only consider builder values
			if ( 0 === strpos( $key, '_ttfmake:' ) ) {
				// Get the individual pieces
				$temp_data[ str_replace( '_ttfmake:', '', $key ) ] = $value[0];
			}
		}
	}

	// Create multidimensional array from postmeta
	$data = ttfmake_create_array_from_meta_keys( $temp_data );

	// Reorder the data in the order specified by the section IDs
	if ( is_array( $ids ) ) {
		foreach ( $ids as $id ) {
			if ( isset( $data[ $id ] ) ) {
				$ordered_data[ $id ] = $data[ $id ];
			}
		}
	}

	/**
	 * Filter the section data for a post.
	 *
	 * @since 1.2.3.
	 *
	 * @param array    $ordered_data    The array of section data.
	 * @param int      $post_id         The post ID for the retrieved data.
	 */
	return apply_filters( 'make_get_section_data', $ordered_data, $post_id );
}
endif;

if ( ! function_exists( 'ttfmake_create_array_from_meta_keys' ) ) :
/**
 * Convert an array with array keys that map to a multidimensional array to the array.
 *
 * @since  1.2.0.
 *
 * @param  array    $arr    The array to convert.
 * @return array            The converted array.
 */
function ttfmake_create_array_from_meta_keys( $arr ) {
	// The new multidimensional array we will return
	$result = array();

	// Process each item of the input array
	foreach ( $arr as $key => $value ) {
		// Store a reference to the root of the array
		$current = & $result;

		// Split up the current item's key into its pieces
		$pieces = explode( ':', $key );

		/**
		 * For all but the last piece of the key, create a new sub-array (if necessary), and update the $current
		 * variable to a reference of that sub-array.
		 */
		for ( $i = 0; $i < count( $pieces ) - 1; $i++ ) {
			$step = $pieces[ $i ];
			if ( ! isset( $current[ $step ] ) ) {
				$current[ $step ] = array();
			}
			$current = & $current[ $step ];
		}

		// Add the current value into the final nested sub-array
		$current[ $pieces[ $i ] ] = $value;
	}

	// Return the result array
	return $result;
}
endif;

if ( ! function_exists( 'ttfmake_post_type_supports_builder' ) ) :
/**
 * Check if a post type supports the Make builder.
 *
 * @since  1.2.0.
 *
 * @param  string    $post_type    The post type to test.
 * @return bool                    True if the post type supports the builder; false if it does not.
 */
function ttfmake_post_type_supports_builder( $post_type ) {
	return post_type_supports( $post_type, 'make-builder' );
}
endif;

if ( ! function_exists( 'ttfmake_is_builder_page' ) ) :
/**
 * Determine if the post uses the builder or not.
 *
 * @since  1.2.0.
 *
 * @param  int     $post_id    The post to inspect.
 * @return bool                True if builder is used for post; false if it is not.
 */
function ttfmake_is_builder_page( $post_id = 0 ) {
	if ( empty( $post_id ) ) {
		$post_id = get_the_ID();
	}

	// Pages will use the template-builder.php template to denote that it is a builder page
	$has_builder_template = ( 'template-builder.php' === get_page_template_slug( $post_id ) );

	// Other post types will use meta data to support builder pages
	$has_builder_meta = ( 1 === (int) get_post_meta( $post_id, '_ttfmake-use-builder', true ) );

	$is_builder_page = $has_builder_template || $has_builder_meta;

	/**
	 * Allow a developer to dynamically change whether the post uses the builder or not.
	 *
	 * @since 1.2.3
	 *
	 * @param bool    $is_builder_page    Whether or not the post uses the builder.
	 * @param int     $post_id            The ID of post being evaluated.
	 */
	return apply_filters( 'make_is_builder_page', $is_builder_page, $post_id );
}
endif;

if ( ! function_exists( 'ttfmake_filter_backcompat' ) ) :
/**
 * Adds back compat for filters with changed names.
 *
 * In Make 1.2.3, filters were all changed from "ttfmake_" to "make_". In order to maintain back compatibility, the old
 * version of the filter needs to still be called. This function collects all of those changed filters and mirrors the
 * new filter so that the old filter name will still work.
 *
 * @since  1.2.3.
 *
 * @return void
 */
function ttfmake_filter_backcompat() {
	// All filters that need a name change
	$old_filters = array(
		'template_content_archive'     => 2,
		'fitvids_custom_selectors'     => 1,
		'template_content_page'        => 2,
		'template_content_search'      => 2,
		'footer_1'                     => 1,
		'footer_2'                     => 1,
		'footer_3'                     => 1,
		'footer_4'                     => 1,
		'sidebar_left'                 => 1,
		'sidebar_right'                => 1,
		'template_content_single'      => 2,
		'get_view'                     => 2,
		'has_sidebar'                  => 3,
		'read_more_text'               => 1,
		'supported_social_icons'       => 1,
		'exif_shutter_speed'           => 2,
		'exif_aperture'                => 2,
		'style_formats'                => 1,
		'prepare_data_section'         => 3,
		'insert_post_data_sections'    => 1,
		'section_classes'              => 2,
		'the_builder_content'          => 1,
		'builder_section_footer_links' => 1,
		'section_defaults'             => 1,
		'section_choices'              => 3,
		'gallery_class'                => 2,
		'builder_banner_class'         => 2,
		'customizer_sections'          => 1,
		'setting_defaults'             => 1,
		'font_relative_size'           => 1,
		'font_stack'                   => 2,
		'font_variants'                => 3,
		'all_fonts'                    => 1,
		'get_google_fonts'             => 1,
		'custom_logo_information'      => 1,
		'custom_logo_max_width'        => 1,
		'setting_choices'              => 2,
		'social_links'                 => 1,
		'show_footer_credit'           => 1,
		'is_plus'                      => 1,
	);

	foreach ( $old_filters as $filter => $args ) {
		add_filter( 'make_' . $filter, 'ttfmake_backcompat_filter', 10, $args );
	}
}
endif;

add_action( 'after_setup_theme', 'ttfmake_filter_backcompat', 1 );

if ( ! function_exists( 'ttfmake_backcompat_filter' ) ) :
/**
 * Prepends "ttf" to a filter name and calls that new filter variant.
 *
 * @since  1.2.3.
 *
 * @return mixed    The result of the filter.
 */
function ttfmake_backcompat_filter() {
	$filter = 'ttf' . current_filter();
	$args   = func_get_args();
	return apply_filters_ref_array( $filter, $args );
}
endif;

if ( ! function_exists( 'ttfmake_action_backcompat' ) ) :
/**
 * Adds back compat for actions with changed names.
 *
 * In Make 1.2.3, actions were all changed from "ttfmake_" to "make_". In order to maintain back compatibility, the old
 * version of the action needs to still be called. This function collects all of those changed actions and mirrors the
 * new filter so that the old filter name will still work.
 *
 * @since  1.2.3.
 *
 * @return void
 */
function ttfmake_action_backcompat() {
	// All filters that need a name change
	$old_actions = array(
		'section_text_before_columns_select' => 1,
		'section_text_after_columns_select'  => 1,
		'section_text_after_title'           => 1,
		'section_text_before_column'         => 2,
		'section_text_after_column'          => 2,
		'section_text_after_columns'         => 1,
		'css'                                => 1,
	);

	foreach ( $old_actions as $action => $args ) {
		add_action( 'make_' . $action, 'ttfmake_backcompat_action', 10, $args );
	}
}
endif;

add_action( 'after_setup_theme', 'ttfmake_action_backcompat', 1 );

if ( ! function_exists( 'ttfmake_backcompat_action' ) ) :
/**
 * Prepends "ttf" to a filter name and calls that new filter variant.
 *
 * @since  1.2.3.
 *
 * @return mixed    The result of the filter.
 */
function ttfmake_backcompat_action() {
	$action = 'ttf' . current_action();
	$args   = func_get_args();
	do_action_ref_array( $action, $args );
}
endif;

if ( ! function_exists( 'ttfmake_upgrade_notice' ) ) :
/**
 * Display a notice to inform the user to update Make Plus if not compatible with this release.
 *
 * @since  1.4.0.
 *
 * @return void.
 */
function ttfmake_upgrade_notice() {
	// Do not show to users who cannot manage plugins
	if ( false === current_user_can( 'edit_plugins' ) ) {
		return;
	}

	// Do not show if not a Make Plus user
	if ( ! ttfmake_is_plus() ) {
		return;
	}

	// Do not show if Make Plus version is 1.4.0 or greater
	$make_plus_version = ( function_exists( 'ttfmp_get_app' ) ) ? ttfmp_get_app()->version : 0;
	if ( true === version_compare( $make_plus_version, '1.4.0', '>=' ) ) {
		return;
	}

	// Do not show if upgrade notice is hidden by user
	if ( 1 === (int) get_theme_mod( 'hide-upgrade-notice' )  ) {
		return;
	}
?>
	<div id="message" class="error">
		<p>
			<?php
			printf(
				__(
					'Please <a href="%1$s"><em>update to Make Plus 1.4.0 or later</em></a> for compatibility with your version of Make. <a href="#" data-nonce="%2$s" class="%3$s">hide</a>',
					'make'
				),
				admin_url( 'update-core.php' ),
				wp_create_nonce( 'hide-notice' ),
				'ttfmake-hide-notice'
			);
			?>
		</p>
	</div>
	<script type="text/javascript">
		jQuery(document).ready(function ($) {
			$('.error').on('click', '.ttfmake-hide-notice', function (evt) {
				evt.preventDefault();

				var $target = $(evt.target),
					nonce = $target.attr('data-nonce'),
					$parent = $target.parents('.error');

				$parent.fadeOut('slow');

				$.post(
					ajaxurl,
					{
						action: 'ttfmake_hide_notice',
						nonce : nonce
					}
				);
			});
		});
	</script>
<?php
}
endif;

add_action( 'admin_notices', 'ttfmake_upgrade_notice' );

if ( ! function_exists( 'ttfmake_hide_upgrade_notice' ) ) :
/**
 * Callback for hiding upgrade notice.
 *
 * @since  1.4.0.
 *
 * @return void.
 */
function ttfmake_hide_upgrade_notice() {
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'hide-notice' ) ) {
		return;
	}

	// Set flag to no longer show the notice
	set_theme_mod( 'hide-upgrade-notice', 1 );

	// Return a success response.
	echo 1;
	wp_die();
}
endif;

add_action( 'wp_ajax_ttfmake_hide_notice', 'ttfmake_hide_upgrade_notice' );
