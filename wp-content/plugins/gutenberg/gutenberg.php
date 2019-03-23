<?php
/**
 * Plugin Name: Gutenberg
 * Plugin URI: https://github.com/WordPress/gutenberg
 * Description: Printing since 1440. This is the development plugin for the new block editor in core.
 * Version: 5.2.0
 * Author: Gutenberg Team
 * Text Domain: gutenberg
 *
 * @package gutenberg
 */

### BEGIN AUTO-GENERATED DEFINES
define( 'GUTENBERG_VERSION', '5.2.0' );
define( 'GUTENBERG_GIT_COMMIT', '6de5263995fb574229fdd4b388f38a1006710746' );
### END AUTO-GENERATED DEFINES

gutenberg_pre_init();

/**
 * Project.
 *
 * The main entry point for the Gutenberg editor. Renders the editor on the
 * wp-admin page for the plugin.
 *
 * The gutenberg and gutenberg__editor classNames are left for backward compatibility.
 *
 * @since 0.1.0
 */
function the_gutenberg_project() {
	global $post_type_object;
	?>
	<noscript>
		<div class="error" style="position:absolute;top:32px;z-index:40"><p>
		<?php
		printf(
			/* translators: %s: https://wordpress.org/plugins/classic-editor/ */
			__( 'The Block Editor requires JavaScript. Please try the <a href="%s">Classic Editor plugin</a>.', 'gutenberg' ),
			__( 'https://wordpress.org/plugins/classic-editor/', 'gutenberg' )
		);
		?>
		</p></div>
	</noscript>
	<div class="block-editor gutenberg">
		<h1 class="screen-reader-text"><?php echo esc_html( $post_type_object->labels->edit_item ); ?></h1>
		<div id="editor" class="block-editor__container gutenberg__editor"></div>
		<div id="metaboxes" class="hidden">
			<?php the_block_editor_meta_boxes(); ?>
		</div>
	</div>
	<?php
}

/**
 * Gutenberg's Menu.
 *
 * Adds a new wp-admin menu page for the Gutenberg editor.
 *
 * @since 0.1.0
 */
function gutenberg_menu() {
	global $submenu;

	add_menu_page(
		'Gutenberg',
		'Gutenberg',
		'edit_posts',
		'gutenberg',
		'the_gutenberg_project',
		'dashicons-edit'
	);

	add_submenu_page(
		'gutenberg',
		__( 'Demo', 'gutenberg' ),
		__( 'Demo', 'gutenberg' ),
		'edit_posts',
		'gutenberg'
	);

	add_submenu_page(
		'gutenberg',
		__( 'Widgets (beta)', 'gutenberg' ),
		__( 'Widgets (beta)', 'gutenberg' ),
		'edit_theme_options',
		'gutenberg-widgets',
		'the_gutenberg_widgets'
	);

	if ( current_user_can( 'edit_posts' ) ) {
		$submenu['gutenberg'][] = array(
			__( 'Support', 'gutenberg' ),
			'edit_posts',
			__( 'https://wordpress.org/support/plugin/gutenberg', 'gutenberg' ),
		);

		$submenu['gutenberg'][] = array(
			__( 'Documentation', 'gutenberg' ),
			'edit_posts',
			'https://wordpress.org/gutenberg/handbook/',
		);
	}
}
add_action( 'admin_menu', 'gutenberg_menu' );

/**
 * Checks whether we're currently loading a Gutenberg page
 *
 * @return boolean Whether Gutenberg is being loaded.
 *
 * @since 3.1.0
 */
function is_gutenberg_page() {
	global $post;

	if ( ! is_admin() ) {
		return false;
	}

	/*
	 * There have been reports of specialized loading scenarios where `get_current_screen`
	 * does not exist. In these cases, it is safe to say we are not loading Gutenberg.
	 */
	if ( ! function_exists( 'get_current_screen' ) ) {
		return false;
	}

	if ( get_current_screen()->base !== 'post' ) {
		return false;
	}

	if ( ! use_block_editor_for_post( $post ) ) {
		return false;
	}

	return true;
}

/**
 * Display a version notice and deactivate the Gutenberg plugin.
 *
 * @since 0.1.0
 */
function gutenberg_wordpress_version_notice() {
	echo '<div class="error"><p>';
	/* translators: %s: Minimum required version */
	printf( __( 'Gutenberg requires WordPress %s or later to function properly. Please upgrade WordPress before activating Gutenberg.', 'gutenberg' ), '5.0.0' );
	echo '</p></div>';

	deactivate_plugins( array( 'gutenberg/gutenberg.php' ) );
}

/**
 * Display a build notice.
 *
 * @since 0.1.0
 */
function gutenberg_build_files_notice() {
	echo '<div class="error"><p>';
	_e( 'Gutenberg development mode requires files to be built. Run <code>npm install</code> to install dependencies, <code>npm run build</code> to build the files or <code>npm run dev</code> to build the files and watch for changes. Read the <a href="https://github.com/WordPress/gutenberg/blob/master/CONTRIBUTING.md">contributing</a> file for more information.', 'gutenberg' );
	echo '</p></div>';
}

/**
 * Verify that we can initialize the Gutenberg editor , then load it.
 *
 * @since 1.5.0
 */
function gutenberg_pre_init() {
	if ( defined( 'GUTENBERG_DEVELOPMENT_MODE' ) && GUTENBERG_DEVELOPMENT_MODE && ! file_exists( dirname( __FILE__ ) . '/build/blocks' ) ) {
		add_action( 'admin_notices', 'gutenberg_build_files_notice' );
		return;
	}

	// Get unmodified $wp_version.
	include ABSPATH . WPINC . '/version.php';

	// Strip '-src' from the version string. Messes up version_compare().
	$version = str_replace( '-src', '', $wp_version );

	if ( version_compare( $version, '5.0.0', '<' ) ) {
		add_action( 'admin_notices', 'gutenberg_wordpress_version_notice' );
		return;
	}

	require_once dirname( __FILE__ ) . '/lib/load.php';

	add_filter( 'replace_editor', 'gutenberg_init', 10, 2 );
}

/**
 * Initialize Gutenberg.
 *
 * Load API functions, register scripts and actions, etc.
 *
 * @param  bool   $return Whether to replace the editor. Used in the `replace_editor` filter.
 * @param  object $post   The post to edit or an auto-draft.
 * @return bool   Whether Gutenberg was initialized.
 */
function gutenberg_init( $return, $post ) {
	if ( true === $return && current_filter() === 'replace_editor' ) {
		return $return;
	}

	if ( ! is_gutenberg_page() ) {
		return false;
	}

	// Instruct WordPress that this is the block editor. Without this, a call
	// to `is_block_editor()` would yield `false` while editing a post with
	// Gutenberg.
	//
	// [TODO]: This is temporary so long as Gutenberg is implemented to use
	// `replace_editor`, rather than allow `edit-form-blocks.php` from core to
	// take effect, where this would otherwise be assigned.
	get_current_screen()->is_block_editor( true );

	add_action( 'admin_enqueue_scripts', 'gutenberg_editor_scripts_and_styles' );
	add_filter( 'screen_options_show_screen', '__return_false' );

	/*
	 * Remove the emoji script as it is incompatible with both React and any
	 * contenteditable fields.
	 */
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );

	/*
	 * Ensure meta box functions are available to third-party code;
	 * includes/meta-boxes is typically loaded from edit-form-advanced.php.
	 */
	require_once ABSPATH . 'wp-admin/includes/meta-boxes.php';
	register_and_do_post_meta_boxes( $post );

	require_once ABSPATH . 'wp-admin/admin-header.php';
	the_gutenberg_project();

	return true;
}
