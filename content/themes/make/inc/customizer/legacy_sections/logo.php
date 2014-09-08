<?php
/**
 * @package Make
 */

if ( ! function_exists( 'ttfmake_customizer_logo' ) ) :
/**
 * Configure settings and controls for the Logo section.
 *
 * @since  1.0.0.
 *
 * @param  object    $wp_customize    The global customizer object.
 * @param  string    $section         The section name.
 * @return void
 */
function ttfmake_customizer_logo( $wp_customize, $section ) {
	$priority       = new TTFMAKE_Prioritizer();
	$control_prefix = 'ttfmake_';
	$setting_prefix = str_replace( $control_prefix, '', $section );

	// Regular Logo
	$setting_id = $setting_prefix . '-regular';
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
				'label'    => __( 'Regular Logo', 'make' ),
				'priority' => $priority->add(),
				'context'  => $control_prefix . $setting_id
			)
		)
	);

	// Retina Logo
	$setting_id = $setting_prefix . '-retina';
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
				'label'    => __( 'Retina Logo (2x)', 'make' ),
				'priority' => $priority->add(),
				'context'  => $control_prefix . $setting_id
			)
		)
	);

	// Retina info
	$setting_id = $setting_prefix . '-retina-info';
	$wp_customize->add_control(
		new TTFMAKE_Customize_Misc_Control(
			$wp_customize,
			$control_prefix . $setting_id,
			array(
				'section'     => $section,
				'type'        => 'text',
				'description' => __( 'The Retina Logo should be twice the size of the Regular Logo.', 'make' ),
				'priority'    => $priority->add()
			)
		)
	);

	// Favicon
	$setting_id = $setting_prefix . '-favicon';
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
				'settings'   => $setting_id,
				'section'    => $section,
				'label'      => __( 'Favicon', 'make' ),
				'priority'   => $priority->add(),
				'context'    => $control_prefix . $setting_id,
				'extensions' => array( 'png', 'ico' )
			)
		)
	);

	// Favicon info
	$setting_id = $setting_prefix . '-favicon-info';
	$wp_customize->add_control(
		new TTFMAKE_Customize_Misc_Control(
			$wp_customize,
			$control_prefix . $setting_id,
			array(
				'section'     => $section,
				'type'        => 'text',
				'description' => __( 'File must be <strong>.png</strong> or <strong>.ico</strong> format. Optimal dimensions: <strong>32px x 32px</strong>.', 'make' ),
				'priority'    => $priority->add()
			)
		)
	);

	// Apple Touch Icon
	$setting_id = $setting_prefix . '-apple-touch';
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
				'settings'   => $setting_id,
				'section'    => $section,
				'label'      => __( 'Apple Touch Icon', 'make' ),
				'priority'   => $priority->add(),
				'context'    => $control_prefix . $setting_id,
				'extensions' => array( 'png' )
			)
		)
	);

	// Apple Touch Icon info
	$setting_id = $setting_prefix . '-apple-touch-info';
	$wp_customize->add_control(
		new TTFMAKE_Customize_Misc_Control(
			$wp_customize,
			$control_prefix . $setting_id,
			array(
				'section'     => $section,
				'type'        => 'text',
				'description' => __( 'File must be <strong>.png</strong> format. Optimal dimensions: <strong>152px x 152px</strong>.', 'make' ),
				'priority'    => $priority->add()
			)
		)
	);
}
endif;