<?php
/**
 * @package Make
 */

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
	$action = 'ttf' . current_filter();
	$args   = func_get_args();
	do_action_ref_array( $action, $args );
}
endif;

if ( ! function_exists( 'ttfmake_customizer_get_key_conversions' ) ) :
/**
 * Return an array of option key migration sets.
 *
 * @since  1.3.0.
 *
 * @return array    The list of key migration sets.
 */
function ttfmake_customizer_get_key_conversions() {
	// $new_key => $old_key
	$conversions = array(
		'font-family-site-title' => 'font-site-title',
		'font-family-h1'         => 'font-header',
		'font-family-h2'         => 'font-header',
		'font-family-h3'         => 'font-header',
		'font-family-h4'         => 'font-header',
		'font-family-h5'         => 'font-header',
		'font-family-h6'         => 'font-header',
		'font-family-body'       => 'font-body',
		'font-size-site-title'   => 'font-site-title-size',
		'font-size-site-tagline' => 'font-site-tagline-size',
		'font-size-nav'          => 'font-nav-size',
		'font-size-h1'           => 'font-header-size',
		'font-size-h2'           => 'font-header-size',
		'font-size-h3'           => 'font-header-size',
		'font-size-h4'           => 'font-header-size',
		'font-size-h5'           => 'font-header-size',
		'font-size-h6'           => 'font-header-size',
		'font-size-widget'       => 'font-widget-size',
		'font-size-body'         => 'font-body-size',
	);

	/**
	 * Filter the array of Customizer option key conversions.
	 *
	 * The keys for some Customizer options have changed between versions. This array
	 * defines each change as $new_key => $old key.
	 *
	 * @since 1.3.0.
	 *
	 * @param array    $conversions    The array of key conversions.
	 */
	return apply_filters( 'make_customizer_key_conversions', $conversions );
}
endif;

if ( ! function_exists( 'ttfmake_customizer_convert_theme_mods' ) ) :
/**
 * Convert old theme mod values to their newer equivalents.
 *
 * @since  1.3.0.
 *
 * @return void
 */
function ttfmake_customizer_set_up_theme_mod_conversions() {
	// Don't run conversions if WordPress version doesn't support panels
	if ( ! ttfmake_customizer_supports_panels() ) {
		return;
	}

	// Set up the necessary filters
	foreach ( ttfmake_customizer_get_key_conversions() as $key => $value ) {
		add_filter( 'theme_mod_' . $key, 'ttfmake_customizer_convert_theme_mods_filter', 11 );
	}
}
endif;

add_action( 'after_setup_theme', 'ttfmake_customizer_set_up_theme_mod_conversions', 11 );

if ( ! function_exists( 'ttfmake_customizer_convert_theme_mods_filter' ) ) :
/**
 * Convert a new theme mod value from an old one.
 *
 * @since  1.3.0.
 *
 * @param  mixed    $value    The current value.
 * @return mixed              The modified value.
 */
function ttfmake_customizer_convert_theme_mods_filter( $value ) {
	$new_mod_name = str_replace( 'theme_mod_', '', current_filter() );
	$conversions  = ttfmake_customizer_get_key_conversions();
	$mods         = get_theme_mods();

	/**
	 * When previewing a page, the logic for this filter needs to change. Because the isset check in the conditional
	 * below will always fail if the new mod key is not set (i.e., the value isn't in the db yet), the default value,
	 * instead of the preview value will always show. Instead, when previewing, the value needs to be gotten from
	 * the `get_theme_mod()` call without this filter applied. This will give the new preview value. If it is not found,
	 * then the normal routine will be used.
	 */
	if ( ttfmake_is_preview() ) {
		remove_filter( current_filter(), 'ttfmake_customizer_convert_theme_mods_filter', 11 );
		$previewed_value = get_theme_mod( $new_mod_name, 'default-value' );
		add_filter( current_filter(), 'ttfmake_customizer_convert_theme_mods_filter', 11 );

		if ( 'default-value' !== $previewed_value ) {
			return $previewed_value;
		}
	}

	/**
	 * We only want to convert the value if the new mod is not in the mods array. This means that the value is not set
	 * and an attempt to get the value from an old key is warranted.
	 */
	if ( ! isset( $mods[ $new_mod_name ] ) ) {
		// Verify that this key should be converted
		if ( isset( $conversions[ $new_mod_name ] ) ) {
			$old_mod_name  = $conversions[ $new_mod_name ];
			$old_mod_value = get_theme_mod( $old_mod_name, 'default-value' );

			// The old value is indeed set
			if ( 'default-value' !== $old_mod_value ) {
				$value = $old_mod_value;

				// Now that we have the right old value, convert it if needed
				$value = ttfmake_customizer_convert_theme_mods_values( $old_mod_name, $new_mod_name, $value );
			}
		}
	}

	return $value;
}
endif;

if ( ! function_exists( 'ttfmake_customizer_convert_theme_mods_values' ) ) :
/**
 * This function converts values from old mods to values for new mods.
 *
 * @since  1.3.0.
 *
 * @param  string    $old_key    The old mod key.
 * @param  string    $new_key    The new mod key.
 * @param  mixed     $value      The value of the mod.
 * @return mixed                 The convert mod value.
 */
function ttfmake_customizer_convert_theme_mods_values( $old_key, $new_key, $value ) {
	if ( in_array( $old_key, array( 'font-header-size' ) ) ) {
		$percent = ttfmake_font_get_relative_sizes();
		$h       = preg_replace( '/font-size-(h\d)/', '$1', $new_key );
		$value   = ttfmake_get_relative_font_size( $value, $percent[$h] );
	}

	return $value;
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