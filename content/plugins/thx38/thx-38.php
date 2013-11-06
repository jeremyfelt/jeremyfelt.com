<?php
/*
Plugin Name: THX_38
Plugin URI:
Description: THX stands for THeme eXperience. A plugin that rebels against their rigidly controlled themes.php in search for hopeful freedom in WordPress 3.8, or beyond. <strong>This is only for development work and the brave of heart, as it totally breaks themes.php</strong>.
Version: 0.9
Author: THX_38 Team
*/

class THX_38 {

	function __construct() {

		add_action( 'load-themes.php',  array( $this, 'themes_screen' ) );
		add_action( 'admin_print_scripts-themes.php', array( $this, 'enqueue' ) );

		// Browse themes
		// add_action( 'load-theme-install.php',  array( $this, 'install_themes_screen' ) );
		// add_action( 'admin_print_scripts-theme-install.php', array( $this, 'enqueue' ) );

	}

	/**
	 * The main template file for the themes.php screen
	 *
	 * Replaces entire contents of themes.php
	 * @require admin-header.php and admin-footer.php
	 */
	function themes_screen() {

		// Bail if user has no capabilities
		if ( ! current_user_can( 'switch_themes' ) && ! current_user_can( 'edit_theme_options' ) )
			wp_die( __( 'Cheatin&#8217; uh?' ) );

		// Actions
		self::get_actions();

		// Admin header
		require_once( ABSPATH . 'wp-admin/admin-header.php' );

		// Display relevant messages
		self::update_messages();

		?>
		<div id="appearance" class="wrap">
			<h2><?php esc_html_e( 'Themes' ); ?><span id="theme-count" class="theme-count"></span><a href="<?php echo admin_url( 'theme-install.php' ); ?>" class="add-new-h2"><?php echo esc_html( _x( 'Add New', 'Add new theme' ) ); ?></a><span class="add-new-h2 themes-bulk-edit"><span class="edit">Manage</span><span class="done">Done...</span></span></h2>
		</div>
		<?php

		// Get the templates
		apply_filters( 'thx_theme_template', self::theme_template() );
		apply_filters( 'thx_search_template', self::search_template() );
		apply_filters( 'thx_theme_single_template', self::theme_single_template() );

		// Admin footer
		require( ABSPATH . 'wp-admin/admin-footer.php');
		exit;
	}

	/**
	 * Get the themes and prepare the JS object
	 * Sets attributes 'id' 'name' 'screenshot' 'description' 'author' 'version' 'active' ...
	 *
	 * @uses wp_get_themes self::get_current_theme
	 * @return array theme data
	 */
	protected function get_themes() {
		$themes = wp_get_themes( array(
			'allowed' => true
		) );

		$data = array();

		foreach( $themes as $slug => $theme ) {
			$data[] = apply_filters( 'thx_theme_data', array(
				'id'           => $slug,
				'name'         => $theme->get( 'Name' ),
				'screenshot'   => self::get_multiple_screenshots( $theme ),
				'description'  => $theme->get( 'Description' ),
				'author'       => $theme->get( 'Author' ),
				'authorURI'    => $theme->get( 'AuthorURI' ),
				'version'      => $theme->Version,
				'parent'       => self::display_parent_theme( $theme ),
				'active'       => ( $slug == self::get_current_theme() ) ? true : null,
				'hasUpdate'    => (bool) self::theme_update( $theme ),
				'update'       => self::theme_update( $theme ),
				'actions'      => array(
					'activate' => wp_nonce_url( 'themes.php?action=activate&amp;template=' . urlencode( $theme->Template ) . '&amp;stylesheet=' . urlencode( $slug ), 'switch-theme_' . $slug ),
					'customize'=> admin_url( 'customize.php?theme=' . $slug ),
					'delete'   => wp_nonce_url( 'themes.php?action=delete&amp;stylesheet=' . urlencode( $slug ), 'delete-theme_' . $slug ),
				),
			) );
		}

		$themes = $data;
		return apply_filters( 'thx_themes', $themes );
	}

	/**
	 * Get current theme
	 * @uses wp_get_theme
	 * @return string theme slug
	 */
	protected function get_current_theme() {
		$theme = wp_get_theme();
		return $theme->stylesheet;
	}

	/**
	 * If a theme is a child theme display its parent
	 * @param theme
	 * @return string
	 */
	protected function display_parent_theme( $theme ) {
		if ( ! $theme->parent() )
			return false;

		$parent = sprintf( __( 'This is a Child Theme of <strong>%s</strong>.' ), $theme->parent()->Name );
		return $parent;
	}

	/**
	 * Processes $_GET actions
	 * @uses self::switch_theme self::delete_theme
	 */
	protected function get_actions() {
		// Make sure we have capabilities
		if ( ! current_user_can( 'switch_themes' ) || ! isset( $_GET['action'] ) )
			return;

		self::switch_theme();
		self::delete_theme();
	}

	/**
	 * Switch theme action with $_GET['action']
	 * Redirects back to themes.php?activated=true on success
	 */
	protected function switch_theme() {
		if ( 'activate' == $_GET['action'] ) {
			check_admin_referer( 'switch-theme_' . $_GET['stylesheet'] );
			$theme = wp_get_theme( $_GET['stylesheet'] );

			// Check the theme exists and is allowed to use
			if ( ! $theme->exists() || ! $theme->is_allowed() )
				wp_die( __( 'Cheatin&#8217; uh?' ) );

			switch_theme( $theme->get_stylesheet() );
			wp_redirect( admin_url( 'themes.php?activated=true' ) );
			exit;
		}
	}

	/**
	 * Delete theme action with $_GET['action']
	 * Redirects back to themes.php?deleted=true on success
	 */
	protected function delete_theme() {
		if ( 'delete' == $_GET['action'] ) {
			check_admin_referer( 'delete-theme_' . $_GET['stylesheet'] );
			$theme = wp_get_theme( $_GET['stylesheet'] );

			// Check user has capabilities for this action
			// and that the theme exists
			if ( ! current_user_can( 'delete_themes' ) || ! $theme->exists() )
				wp_die( __( 'Cheatin&#8217; uh?' ) );

			delete_theme( $_GET['stylesheet'] );
			wp_redirect( admin_url( 'themes.php?deleted=true' ) );
			exit;
		}
	}

	/**
	 * Displays messages based on which action was performed
	 *
	 * @uses wp_get_theme
	 * @return html messages
	 */
	public function update_messages() {
		$theme = wp_get_theme();

		// Error message if theme is not valid
		if ( ! validate_current_theme() || isset( $_GET['broken'] ) ) {
			printf( '<div id="message1" class="updated"><p>' . __( 'The active theme is broken. Reverting to the default theme.' ) . '</p></div>' );
		}
		// Activation messages
		if ( isset( $_GET['activated'] ) ) {

			if ( isset( $_GET['previewed'] ) ) {
				$message = sprintf( __( 'Settings saved and theme activated. <a href="%s">Visit site</a>.' ), home_url( '/' ) );
				printf( '<div id="message2" class="updated"><p>' . $message . '</p></div>' );
			} else {
				$message = sprintf( __( '%s theme <strong>activated</strong>. <a href="%s">Visit site</a>.' ), $theme->name, home_url( '/' ) );
				printf( '<div id="message2" class="updated"><p>' . $message . '</p></div>' );
			}
		}
		// Theme deleted
		if ( isset( $_GET['deleted'] ) ) {
			$message = sprintf( __( 'Theme successfully <strong>deleted</strong>.' ) );
			printf( '<div id="message3" class="updated"><p>' . $message . '</p></div>' );
		}
	}

	/**
	 * Forks theme_update_available from wp-admin/includes/theme.php
	 * so we can pass update messages to the Backbone views.
	 * This coule hopefully become a native method somewhere else.
	 */
	protected function theme_update( $theme ) {
		static $themes_update;

		if ( ! current_user_can('update_themes' ) )
			return;

		if ( ! isset( $themes_update ) )
			$themes_update = get_site_transient( 'update_themes' );

		if ( ! is_a( $theme, 'WP_Theme' ) )
			return;

		$stylesheet = $theme->get_stylesheet();

		if ( isset($themes_update->response[ $stylesheet ]) ) {
			$update = $themes_update->response[ $stylesheet ];
			$theme_name = $theme->display('Name');
			$update_url = wp_nonce_url( 'update.php?action=upgrade-theme&amp;theme=' . urlencode($stylesheet), 'upgrade-theme_' . $stylesheet);
			$update_onclick = 'onclick="if ( confirm(\'' . esc_js( __("Updating this theme will lose any customizations you have made. 'Cancel' to stop, 'OK' to update.") ) . '\') ) {return true;}return false;"';

			if ( !is_multisite() ) {
				if ( ! current_user_can('update_themes') )
					$html = sprintf( '<p><strong>' . __('There is a new version of %1$s available. <a href="%2$s" class="thickbox" title="%1$s">View version %3$s details</a>.') . '</strong></p>', $theme_name, $update['url'], $update['new_version']);
				else if ( empty($update['package']) )
					$html = sprintf( '<p><strong>' . __('There is a new version of %1$s available. <a href="%2$s" class="thickbox" title="%1$s">View version %3$s details</a>. <em>Automatic update is unavailable for this theme.</em>') . '</strong></p>', $theme_name, $update['url'], $update['new_version']);
				else
					$html = sprintf( '<p><strong>' . __('There is a new version of %1$s available. <a href="%2$s" class="thickbox" title="%1$s">View version %3$s details</a> or <a href="%4$s" %5$s>update now</a>.') . '</strong></p>', $theme_name, $update['url'], $update['new_version'], $update_url, $update_onclick );
			}

			return $html;
		}
	}

	/**
	 * Enqueue scripts and styles
	 */
	public function enqueue() {

		// Relies on Backbone.js
		wp_enqueue_script( 'thx-38', plugins_url( 'thx-38.js', __FILE__ ), array( 'backbone' ), '20130817', true );
		wp_enqueue_style( 'thx-38', plugins_url( 'thx-38.css', __FILE__ ), array(), '20130817', 'screen' );

		// Passes the theme data and settings
		// These are the bones of the application
		wp_localize_script( 'thx-38', '_THX38', array(
			'themes'   => $this->get_themes(),
			'settings' => array(
				'isBrowsing'    => (bool) ( get_current_screen()->id == 'theme-install' ),
				'install_uri'   => admin_url( 'theme-install.php' ),
				'customizeURI'  => ( current_user_can( 'edit_theme_options' ) ) ? wp_customize_url() : null,
				'confirmDelete' => sprintf( __( "Are you sure you want to delete this theme?\n\nClick 'Cancel' to go back, 'OK' to confirm the delete." ) ),
				'root'          => apply_filters( 'thx_router_root', '/wp-admin/themes.php' ),
			),
			'i18n' => array(
				'active'          => __( 'Current Theme' ),
				'add_new'         => __( 'Add New Theme' ),
				'customize'       => __( 'Customize' ),
				'activate'        => __( 'Activate' ),
				'preview'         => __( 'Preview' ),
				'delete'          => __( 'Delete Theme' ),
				'updateAvailable' => __( 'Update Available' ),
			),
			'browse' => array(
				'sections' => apply_filters( 'thx_theme_sections', array(
					'featured' => __( 'Featured Themes' ),
					'popular'  => __( 'Popular Themes' ),
					'new'      => __( 'Newest Themes' ),
				) ),
				'publicThemes' => ( get_current_screen()->id == 'theme-install' ) ? $this->get_default_public_themes() : null,
			),
		) );
	}

	/**
	 * Method to get an array of all the screenshots a theme has
	 * It checks for files in the form of 'screenshot-n' at the root
	 * of a theme directory.
	 *
	 * Hardcoded to pngs for now.
	 *
	 * @param a theme object
	 * @returns array screenshot urls (first element is default screenshot)
	 */
	protected function get_multiple_screenshots( $theme ) {
		$base = $theme->get_stylesheet_directory_uri();
		$set = array( 2, 3, 4, 5 );

		// Screenshots array starts with default screenshot at position [0]
		$screenshots = array( $theme->get_screenshot() );

		// Check how many other screenshots a theme has
		foreach ( $set as $number ) {
			// Hard-coding file path for pngs...
			$file = '/screenshot-' . $number . '.png';
			$path = $theme->template_dir . $file;

			if ( ! file_exists( $path ) )
				continue;

			$screenshots[] = $base . $file;
		}

		// If there are no screenshots use a default image
		if ( ! $screenshots[0] )
			$screenshots[0] = self::get_default_screenshot();

		return $screenshots;
	}

	/**
	 * Theme action links.
	 * @todo check for capabilities and set up a more robust solution.
	 *
	 * @echo html
	 */
	function theme_actions_links() {
		$sections = array(
			'widgets.php' => __( 'Widgets' ),
			'nav-menus.php' => __( 'Menus' ),
		);
		foreach ( $sections as $url => $title ) {
			echo '<a class="button button-secondary" href="' . admin_url( $url ) . '">' . $title . '</a>';
		}
	}

	/**
	 * Gets default screenshot path
	 *
	 * @return url string
	 */
	public function get_default_screenshot() {
		return apply_filters( 'thx_default_screenshot_uri', plugins_url( 'default.png', __FILE__ ) );
	}

	/**
	 * The main template file for the theme-install.php screen
	 *
	 * Replaces entire contents of theme-install.php
	 * @require admin-header.php and admin-footer.php
	 */
	function install_themes_screen() {

		// Admin header
		require_once( ABSPATH . 'wp-admin/admin-header.php' );
		?>
		<div id="appearance" class="wrap">
			<h2><?php esc_html_e( 'Themes' ); ?><a href="<?php echo admin_url( 'themes.php' ); ?>" class="button button-secondary"><?php esc_html_e( 'Back to your themes' ); ?></a></h2>
			<div class="theme-categories"><span><?php esc_html_e( 'Categories:' ); ?></span> <a href="" class="current">All</a> <a href="">Photography</a> <a href="">Magazine</a> <a href="">Blogging</a>
		</div>
		<?php

		// Get the templates
		self::public_theme_template();
		self::search_template();
		self::public_theme_single_template();

		// Admin footer
		require( ABSPATH . 'wp-admin/admin-footer.php');
		exit;
	}

	/**
	 * Array containing the supported directory sections
	 *
	 * @return array
	 */
	protected function themes_directory_sections() {
		$sections = array(
			'featured' => __( 'Featured Themes' ),
			'popular'  => __( 'Popular Themes' ),
			'new'      => __( 'Newest Themes' ),
		);
		return $sections;
	}

	/**
	 * Gets public themes from the themes directory
	 * Used to populate the initial views
	 *
	 * @uses themes_api themes_directory_sections
	 * @return array with $theme objects
	 */
	protected function get_default_public_themes( $themes = array() ) {
		$sections = self::themes_directory_sections();
		$sections = array_keys( $sections );

		$args = array(
			'page' => 1,
			'per_page' => 4,
		);

		foreach ( $sections as $section ) {
			$args['browse'] = $section;
			$themes[ $section ] = themes_api( 'query_themes', $args );
		}

		return $themes;
	}

	/**
	 * Ajax request handler for public themes
	 *
	 * @uses get_public_themes
	 */
	public function ajax_puclic_themes() {
		$colors = self::get_public_themes( $_REQUEST );
		header( 'Content-Type: text/javascript' );
		echo json_encode( $response );
		die;
	}

	/**
	 * Gets public themes from the themes directory
	 *
	 * @uses get_public_themes
	 */
	public function get_public_themes( $args = array() ) {
		$defaults = array(
			'page' => 1,
			'per_page' => 4,
			'browse' => 'new',
		);

		$args = wp_parse_args( $args, $defaults );
		$themes = themes_api( 'query_themes', $args );
		return $themes;
	}


	/**
	 * These are the templates that will be used to render the final HTML
	 *
	 * ------------------------
	 * Underscores.js Templates
	 * ------------------------
	 */

	/**
	 * Underscores template for rendering the Theme views
	 */
	public function theme_template() {
		?>
		<script id="theme-template" type="text/template">
			<div class="theme-screenshot">
				<img src="<%= screenshot[0] %>" alt="" />
			</div>
			<h3 class="theme-name"><%= name %></h3>
			<div class="theme-actions">
			<% if ( active ) { %>
				<span class="current-label"><%= _THX38.i18n['active'] %></span>
				<% if ( _THX38.settings['customizeURI'] ) { %>
				<a class="button button-primary" href="<%= _THX38.settings['customizeURI'] %>"><%= _THX38.i18n['customize'] %></a>
				<% } %>
			<% } else { %>
				<a class="button button-primary activate" href="<%= actions['activate'] %>"><%= _THX38.i18n['activate'] %></a>
				<a class="button button-secondary preview" href="<%= actions['customize'] %>"><%= _THX38.i18n['preview'] %></a>
			<% } %>
			</div>
			<% if ( hasUpdate ) { %>
				<a class="theme-update"><%= _THX38.i18n['updateAvailable'] %></a>
			<% } %>
			<% if ( ! active ) { %>
				<a href="<%= actions['delete'] %>" class="delete-theme"></a>
			<% } %>
		</script>
		<?php
	}

	/**
	 * Underscores template for search form
	 */
	public function search_template() {
		?>
		<script id="theme-search-template" type="text/template">
			<input type="text" name="theme-search" id="theme-search" placeholder="<?php esc_attr_e( 'Search...' ); ?>" />
		</script>
	<?php
	}

	/**
	 * Underscores template for single Theme views
	 * Displays full theme information, including description,
	 * author, version, larger screenshots.
	 */
	public function theme_single_template() {
		?>
		<script id="theme-single-template" type="text/template">
				<div class="theme-backdrop"></div>
				<div class="theme-wrap">
					<div alt="f158" class="back dashicons dashicons-no"></div>
					<div alt="Show previous theme" class="left dashicons dashicons-no"></div>
					<div alt="Show next theme" class="right dashicons dashicons-no"></div>

					<div class="theme-screenshots" id="theme-screenshots">
						<div class="screenshot first"><img src="<%= screenshot[0] %>" alt="" /></div>
					<%
						if ( _.size( screenshot ) > 1 ) {
							_.each ( screenshot, function( image ) {
					%>
							<div class="screenshot thumb"><img src="<%= image %>" alt="" /></div>
					<%
							});
						}
					%>
					</div>

					<div class="theme-info">
						<h3 class="theme-name"><%= name %><span class="theme-version">v<%= version %></span></h3>
						<h4 class="theme-author">By <a href="<%= authorURI %>"><%= author %></a></h4>
						<% if ( hasUpdate ) { %>
						<div class="theme-update-message">
							<a class="theme-update"><%= _THX38.i18n['updateAvailable'] %></a>
							<p><%= update %></p>
						</div>
						<% } %>
						<p class="theme-description"><%= description %></p>
						<% if ( parent ) { %>
							<p class="parent-theme"><%= parent %></p>
						<% } %>
					</div>
				</div>
				<div class="theme-actions">
					<div class="active-theme">
						<a href="<%= _THX38.settings['customizeURI'] %>" class="button button-primary"><%= _THX38.i18n['customize'] %></a>
						<?php self::theme_actions_links(); ?>
					</div>
					<div class="inactive-theme">
						<a href="<%= actions['activate'] %>" class="button button-primary"><%= _THX38.i18n['activate'] %></a>
						<a href="<%= actions['customize'] %>" class="button button-secondary"><%= _THX38.i18n['preview'] %></a>
					</div>
					<% if ( ! active ) { %>
						<a href="<%= actions['delete'] %>" class="delete-theme"><%= _THX38.i18n['delete'] %></a>
					<% } %>
				</div>
		</script>
	<?php
	}

	/**
	 * Underscores template for rendering the Theme views
	 * on the browse directory
	 */
	public function public_theme_template() {
		?>
		<script id="public-theme-template" type="text/template">
			<div class="theme-screenshot">
				<img src="<%= screenshot_url %>" alt="" />
			</div>
			<h3 class="theme-name"><%= name %></h3>
			<a class="button button-secondary preview"><?php esc_html_e( 'Install' ); ?></a>
		</script>
		<?php
	}

	/**
	 * Underscores template for single Theme views from the public directory
	 * Displays full theme information, including description,
	 * author, version, larger screenshots.
	 */
	public function public_theme_single_template() {
		?>
		<script id="public-theme-single-template" type="text/template">
			<div id="theme-overlay">
				<h2 class="back button"><?php esc_html_e( 'Back to Themes' ); ?></h2>
				<div class="theme-wrap">
					<h3 class="theme-name"><%= name %><span class="theme-version"><%= version %></span></h3>
					<h4 class="theme-author">By <%= author %></h4>

					<div class="theme-screenshots" id="theme-screenshots">
						<div class="screenshot first"><img src="<%= screenshot_url %>" alt="" /></div>
					</div>

					<p class="theme-description"><%= description %></p>
				</div>
			</div>
		</script>
	<?php
	}

}

/**
 * Initialize
 */
new THX_38;