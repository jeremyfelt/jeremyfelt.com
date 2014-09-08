<?php
/**
 * @package Make
 */

if ( ! function_exists( 'ttfmake_customizer_main' ) ) :
/**
 * Configure settings and controls for the Main section.
 *
 * @since  1.0.0.
 *
 * @param  object    $wp_customize    The global customizer object.
 * @param  string    $section         The section name.
 * @return void
 */
function ttfmake_customizer_main( $wp_customize, $section ) {
	$priority       = new TTFMAKE_Prioritizer();
	$control_prefix = 'ttfmake_';
	$setting_prefix = str_replace( $control_prefix, '', $section );

	// Background color
	$setting_id = $setting_prefix . '-background-color';
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
				'label'    => __( 'Background Color', 'make' ),
				'priority' => $priority->add()
			)
		)
	);

	// Background Image
	$setting_id = $setting_prefix . '-background-image';
	$wp_customize->add_setting(
		$setting_id,
		array(
			'default'           => ttfmake_get_default( $setting_id ),
			'type'              => 'theme_mod',
			'sanitize_callback' => 'esc_url_raw',
		)
	);
	$wp_customize->add_control(
		new TTFMAKE_Customize_Image_Control(
			$wp_customize,
			$control_prefix . $setting_id,
			array(
				'settings' => $setting_id,
				'section'  => $section,
				'label'    => __( 'Background Image', 'make' ),
				'priority' => $priority->add(),
				'context'  => $control_prefix . $setting_id
			)
		)
	);

	// Background Repeat
	$setting_id = $setting_prefix . '-background-repeat';
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
			'label'    => __( 'Background Repeat', 'make' ),
			'type'     => 'radio',
			'choices'  => ttfmake_get_choices( $setting_id ),
			'priority' => $priority->add()
		)
	);

	// Background Position
	$setting_id = $setting_prefix . '-background-position';
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
			'label'    => __( 'Background Position', 'make' ),
			'type'     => 'radio',
			'choices'  => ttfmake_get_choices( $setting_id ),
			'priority' => $priority->add()
		)
	);

	// Background Size
	$setting_id = $setting_prefix . '-background-size';
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
			'label'    => __( 'Background Size', 'make' ),
			'type'     => 'radio',
			'choices'  => ttfmake_get_choices( $setting_id ),
			'priority' => $priority->add()
		)
	);

	// Content options heading
	$setting_id = $setting_prefix . '-content-heading';
	$wp_customize->add_control(
		new TTFMAKE_Customize_Misc_Control(
			$wp_customize,
			$control_prefix . $setting_id,
			array(
				'section'     => $section,
				'type'        => 'heading',
				'label' => __( 'Content Options', 'make' ),
				'priority'    => $priority->add()
			)
		)
	);

	// Underline content links
	$setting_id = $setting_prefix . '-content-link-underline';
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
			'label'    => __( 'Underline links in content', 'make' ),
			'type'     => 'checkbox',
			'priority' => $priority->add()
		)
	);
}
endif;