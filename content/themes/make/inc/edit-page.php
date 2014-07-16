<?php
/**
 * @package Make
 */

if ( ! function_exists( 'ttfmake_edit_page_script' ) ) :
/**
 * Enqueue scripts that run on the Edit Page screen
 *
 * @since  1.0.0.
 *
 * @return void
 */
function ttfmake_edit_page_script() {
	global $pagenow;

	wp_enqueue_script(
		'ttfmake-admin-edit-page',
		get_template_directory_uri() . '/js/admin/edit-page.js',
		array( 'jquery' ),
		TTFMAKE_VERSION,
		true
	);

	wp_localize_script(
		'ttfmake-admin-edit-page',
		'ttfmakeEditPageData',
		array(
			'featuredImage' => __( 'Featured images are not available for this page while using the current page template.', 'make' ),
			'pageNow'       => esc_js( $pagenow ),
		)
	);
}
endif;

add_action( 'admin_enqueue_scripts', 'ttfmake_edit_page_script' );

/**
 * Add a Make Plus metabox to each qualified post type edit screen
 *
 * @since  1.0.6.
 *
 * @return void
 */
function ttfmake_add_plus_metabox() {
	if ( ttfmake_is_plus() || ! is_super_admin() ) {
		return;
	}

	// Post types
	$post_types = get_post_types(
		array(
			'public' => true,
			'_builtin' => false
		)
	);
	$post_types[] = 'post';
	$post_types[] = 'page';

	// Add the metabox for each type
	foreach ( $post_types as $type ) {
		add_meta_box(
			'ttfmake-plus-metabox',
			__( 'Layout Settings', 'make' ),
			'ttfmake_render_plus_metabox',
			$type,
			'side',
			'default'
		);
	}
}

add_action( 'add_meta_boxes', 'ttfmake_add_plus_metabox' );

/**
 * Render the Make Plus metabox.
 *
 * @since 1.0.6.
 *
 * @param  object    $post    The current post object.
 * @return void
 */
function ttfmake_render_plus_metabox( $post ) {
	// Get the post type label
	$post_type = get_post_type_object( $post->post_type );
	$label = ( isset( $post_type->labels->singular_name ) ) ? $post_type->labels->singular_name : __( 'Post' );

	echo '<p class="howto">';
	printf(
		__( 'Looking to configure a unique layout for this %1$s? %2$s.', 'make' ),
		esc_html( strtolower( $label ) ),
		sprintf(
			'<a href="%1$s" target="_blank">%2$s</a>',
			esc_url( ttfmake_get_plus_link( 'layout-settings' ) ),
			sprintf(
				__( 'Upgrade to %s', 'make' ),
				'Make Plus'
			)
		)
	);
	echo '</p>';
}
