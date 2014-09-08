<?php
/**
 * @package Make
 */

if ( ! function_exists( 'ttfmake_customizer_general' ) ) :
/**
 * Configure settings and controls for the General section
 *
 * @since  1.0.0.
 *
 * @param  object    $wp_customize    The global customizer object.
 * @param  string    $section         The section name.
 * @return void
 */
function ttfmake_customizer_general( $wp_customize, $section ) {
	$priority = new TTFMAKE_Prioritizer();
	$control_prefix = 'ttfmake_';
	$setting_prefix = str_replace( $control_prefix, '', $section );

	// Site Layout
	$setting_id = $setting_prefix . '-layout';
	$wp_customize->add_setting(
		$setting_id,
		array(
			'default'           => ttfmake_get_default( $setting_id ),
			'type'              => 'theme_mod',
			'sanitize_callback' => 'ttfmake_sanitize_choice',
		)
	);
	$wp_customize->add_control(
		$control_prefix . $setting_id,
		array(
			'settings' => $setting_id,
			'section'  => $section,
			'label'    => __( 'Site Layout', 'make' ),
			'type'     => 'radio',
			'choices'  => ttfmake_get_choices( $setting_id ),
			'priority' => $priority->add()
		)
	);

	// Sticky label
	$setting_id = $setting_prefix . '-sticky-label';
	$wp_customize->add_setting(
		$setting_id,
		array(
			'default'           => ttfmake_get_default( $setting_id ),
			'type'              => 'theme_mod',
			'sanitize_callback' => 'esc_html',
			'transport'         => 'postMessage' // Asynchronous preview
		)
	);
	$wp_customize->add_control(
		$control_prefix . $setting_id,
		array(
			'settings' => $setting_id,
			'section'  => $section,
			'label'    => __( 'Sticky Label', 'make' ),
			'type'     => 'text',
			'priority' => $priority->add()
		)
	);
}
endif;