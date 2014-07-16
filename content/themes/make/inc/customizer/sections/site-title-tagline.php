<?php
/**
 * @package Make
 */

if ( ! function_exists( 'ttfmake_customizer_sitetitletagline' ) ) :
/**
 * Configure settings and controls for the Site Title & Tagline section.
 *
 * @since  1.0.0.
 *
 * @return void
 */
function ttfmake_customizer_sitetitletagline() {
	global $wp_customize;

	$priority       = new TTFMAKE_Prioritizer( 10, 1 );
	$control_prefix = 'ttfmake_';
	$section        = 'title_tagline';

	// Site title color
	$setting_id = 'color-site-title';
	$wp_customize->add_setting(
		$setting_id,
		array(
			'default'           => ttfmake_get_default( $setting_id ),
			'type'              => 'theme_mod',
			'sanitize_callback' => 'maybe_hash_hex_color',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize,
			$control_prefix . $setting_id,
			array(
				'settings' => $setting_id,
				'section'  => $section,
				'label'    => __( 'Site Title Color', 'make' ),
				'priority' => $priority->add()
			)
		)
	);

	// Change priority for Site Title
	$site_title           = $wp_customize->get_control( 'blogname' );
	$site_title->priority = $priority->add();

	// Hide Site Title
	$setting_id = 'hide-site-title';
	$wp_customize->add_setting(
		$setting_id,
		array(
			'default'           => ttfmake_get_default( $setting_id ),
			'type'              => 'theme_mod',
			'sanitize_callback' => 'absint',
		)
	);
	$wp_customize->add_control(
		$control_prefix . $setting_id,
		array(
			'settings' => $setting_id,
			'section'  => $section,
			'label'    => __( 'Hide Site Title', 'make' ),
			'type'     => 'checkbox',
			'priority' => $priority->add()
		)
	);

	// Change priority for Tagline
	$site_description = $wp_customize->get_control( 'blogdescription' );
	$site_description->priority = $priority->add();

	// Hide Tagline
	$setting_id = 'hide-tagline';
	$wp_customize->add_setting(
		$setting_id,
		array(
			'default'           => ttfmake_get_default( $setting_id ),
			'type'              => 'theme_mod',
			'sanitize_callback' => 'absint',
		)
	);
	$wp_customize->add_control(
		$control_prefix . $setting_id,
		array(
			'settings' => $setting_id,
			'section'  => $section,
			'label'    => __( 'Hide Tagline', 'make' ),
			'type'     => 'checkbox',
			'priority' => $priority->add()
		)
	);
}
endif;

add_action( 'customize_register', 'ttfmake_customizer_sitetitletagline', 20 );