<?php
/**
 * @package Make
 */

if ( ! function_exists( 'ttfmake_customizer_get_key_conversions' ) ) :
/**
 * Return an array of option key migration sets.
 *
 * @since  1.3.0.
 *
 * @return array    The list of key migration sets.
 */
function ttfmake_customizer_get_key_conversions() {
	/**
	 * Sets are defined by the theme version they pertain to:
	 * $theme_version => array
	 *     $old => $new
	 */
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