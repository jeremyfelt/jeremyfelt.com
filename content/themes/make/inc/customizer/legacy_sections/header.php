<?php
/**
 * @package Make
 */

if ( ! function_exists( 'ttfmake_customizer_header' ) ) :
/**
 * Configure settings and controls for the Header section
 *
 * @since  1.0.0.
 *
 * @param  object    $wp_customize    The global customizer object.
 * @param  string    $section         The section name.
 * @return void
 */
function ttfmake_customizer_header( $wp_customize, $section ) {
	$priority       = new TTFMAKE_Prioritizer( 10, 5 );
	$control_prefix = 'ttfmake_';
	$setting_prefix = str_replace( $control_prefix, '', $section );

	// Header layout
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
			'label'    => __( 'Header Layout', 'make' ),
			'type'     => 'select',
			'choices'  => ttfmake_get_choices( $setting_id ),
			'priority' => $priority->add()
		)
	);

	// Branding position (Layouts 1 & 3 only)
	$setting_id = $setting_prefix . '-branding-position';
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
			'label'    => __( 'Show Title/Logo On', 'make' ),
			'type'     => 'select',
			'choices'  => ttfmake_get_choices( $setting_id ),
			'priority' => $priority->add()
		)
	);

	// Header layout line
	$setting_id = $setting_prefix . '-layout-line';
	$wp_customize->add_control(
		new TTFMAKE_Customize_Misc_Control(
			$wp_customize,
			$control_prefix . $setting_id,
			array(
				'section'     => $section,
				'type'        => 'line',
				'priority'    => $priority->add()
			)
		)
	);

	// Header Bar content layout
	$setting_id = $setting_prefix . '-bar-content-layout';
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
			'label'    => __( 'Header Bar Content Layout', 'make' ),
			'type'     => 'select',
			'choices'  => ttfmake_get_choices( $setting_id ),
			'priority' => $priority->add()
		)
	);

	// Header text
	$setting_id = $setting_prefix . '-text';
	$wp_customize->add_setting(
		$setting_id,
		array(
			'default'           => ttfmake_get_default( $setting_id ),
			'type'              => 'theme_mod',
			'sanitize_callback' => 'ttfmake_sanitize_text',
			'transport'         => 'postMessage' // Asynchronous preview
		)
	);
	$wp_customize->add_control(
		$control_prefix . $setting_id,
		array(
			'settings' => $setting_id,
			'section'  => $section,
			'label'    => __( 'Header Bar Text', 'make' ),
			'type'     => 'text',
			'priority' => $priority->add()
		)
	);

	// Header Bar Text color
	$setting_id = $setting_prefix . '-bar-text-color';
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
				'label'    => __( 'Header Bar Text Color', 'make' ),
				'priority' => $priority->add()
			)
		)
	);

	// Header Bar Border color
	$setting_id = $setting_prefix . '-bar-border-color';
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
				'label'    => __( 'Header Bar Border Color', 'make' ),
				'priority' => $priority->add()
			)
		)
	);

	// Header Bar Background color
	$setting_id = $setting_prefix . '-bar-background-color';
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
				'label'    => __( 'Header Bar Background Color', 'make' ),
				'priority' => $priority->add()
			)
		)
	);

	// Header bar line
	$setting_id = $setting_prefix . '-bar-line';
	$wp_customize->add_control(
		new TTFMAKE_Customize_Misc_Control(
			$wp_customize,
			$control_prefix . $setting_id,
			array(
				'section'     => $section,
				'type'        => 'line',
				'priority'    => $priority->add()
			)
		)
	);

	// Header Text color
	$setting_id = $setting_prefix . '-text-color';
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
				'label'    => __( 'Header Text Color', 'make' ),
				'priority' => $priority->add()
			)
		)
	);

	// Header Background Color
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
				'label'    => __( 'Header Background Color', 'make' ),
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
				'label'    => __( 'Header Background Image', 'make' ),
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

	// Header options heading
	$setting_id = $setting_prefix . '-options-heading';
	$wp_customize->add_control(
		new TTFMAKE_Customize_Misc_Control(
			$wp_customize,
			$control_prefix . $setting_id,
			array(
				'section'     => $section,
				'type'        => 'heading',
				'label' => __( 'Header Options', 'make' ),
				'priority'    => $priority->add()
			)
		)
	);

	// Show social icons
	$setting_id = $setting_prefix . '-show-social';
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
			'label'    => __( 'Show social icons in header bar', 'make' ),
			'type'     => 'checkbox',
			'priority' => $priority->add()
		)
	);

	// Show search field
	$setting_id = $setting_prefix . '-show-search';
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
			'label'    => __( 'Show search field in header bar', 'make' ),
			'type'     => 'checkbox',
			'priority' => $priority->add()
		)
	);
}
endif;