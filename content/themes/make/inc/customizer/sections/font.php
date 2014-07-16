<?php
/**
 * @package Make
 */

if ( ! function_exists( 'ttfmake_customizer_font' ) ) :
/**
 * Configure settings and controls for the Fonts section.
 *
 * @since  1.0.0.
 *
 * @param  object    $wp_customize    The global customizer object.
 * @param  string    $section         The section name.
 * @return void
 */
function ttfmake_customizer_font( $wp_customize, $section ) {
	$priority       = new TTFMAKE_Prioritizer();
	$control_prefix = 'ttfmake_';
	$setting_prefix = str_replace( $control_prefix, '', $section );

	// Google font info
	$setting_id = $setting_prefix . '-google-font-info';
	$wp_customize->add_control(
		new TTFMAKE_Customize_Misc_Control(
			$wp_customize,
			$control_prefix . $setting_id,
			array(
				'section'     => $section,
				'type'        => 'text',
				'description' => sprintf(
					__( 'The list of Google fonts is long! You can %s before making your choices.', 'make' ),
					sprintf(
						'<a href="%1$s" target="_blank">%2$s</a>',
						esc_url( 'http://www.google.com/fonts/' ),
						__( 'preview', 'make' )
					)
				),
				'priority'    => $priority->add()
			)
		)
	);

	// Site title font
	$setting_id = $setting_prefix . '-site-title';
	$wp_customize->add_setting(
		$setting_id,
		array(
			'default'           => ttfmake_get_default( $setting_id ),
			'type'              => 'theme_mod',
			'sanitize_callback' => 'ttfmake_sanitize_font_choice',
		)
	);
	$wp_customize->add_control(
		$control_prefix . $setting_id,
		array(
			'settings' => $setting_id,
			'section'  => $section,
			'label'    => __( 'Site Title', 'make' ),
			'type'     => 'select',
			'choices'  => ttfmake_all_font_choices(),
			'priority' => $priority->add()
		)
	);

	// Header font
	$setting_id = $setting_prefix . '-header';
	$wp_customize->add_setting(
		$setting_id,
		array(
			'default'           => ttfmake_get_default( $setting_id ),
			'type'              => 'theme_mod',
			'sanitize_callback' => 'ttfmake_sanitize_font_choice',
		)
	);
	$wp_customize->add_control(
		$control_prefix . $setting_id,
		array(
			'settings' => $setting_id,
			'section'  => $section,
			'label'    => __( 'Headers', 'make' ),
			'type'     => 'select',
			'choices'  => ttfmake_all_font_choices(),
			'priority' => $priority->add()
		)
	);

	// Body font
	$setting_id = $setting_prefix . '-body';
	$wp_customize->add_setting(
		$setting_id,
		array(
			'default'           => ttfmake_get_default( $setting_id ),
			'type'              => 'theme_mod',
			'sanitize_callback' => 'ttfmake_sanitize_font_choice',
		)
	);
	$wp_customize->add_control(
		$control_prefix . $setting_id,
		array(
			'settings' => $setting_id,
			'section'  => $section,
			'label'    => __( 'Body', 'make' ),
			'type'     => 'select',
			'choices'  => ttfmake_all_font_choices(),
			'priority' => $priority->add()
		)
	);

	if ( false === ttfmake_is_plus() ) {
		// Typekit information
		$setting_id = $setting_prefix . '-typekit-font-heading';
		$wp_customize->add_control(
			new TTFMAKE_Customize_Misc_Control(
				$wp_customize,
				$control_prefix . $setting_id,
				array(
					'section'  => $section,
					'type'     => 'heading',
					'label'    => __( 'Typekit', 'make' ),
					'priority' => 450
				)
			)
		);

		$setting_id = $setting_prefix . '-typekit-font-info';
		$wp_customize->add_control(
			new TTFMAKE_Customize_Misc_Control(
				$wp_customize,
				$control_prefix . $setting_id,
				array(
					'section'     => $section,
					'type'        => 'text',
					'description' => sprintf(
						__( 'Looking to add premium fonts from Typekit to your website? %s.', 'make' ),
						sprintf(
							'<a href="%1$s" target="_blank">%2$s</a>',
							esc_url( ttfmake_get_plus_link( 'typekit' ) ),
							sprintf(
								__( 'Upgrade to %1$s', 'make' ),
								'Make Plus'
							)
						)
					),
					'priority'    => 460
				)
			)
		);
	}

	// Font family line
	$setting_id = $setting_prefix . '-family-line';
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

	// Site title font size
	$setting_id = $setting_prefix . '-site-title-size';
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
			'label'    => __( 'Site Title Font Size (in px)', 'make' ),
			'type'     => 'text',
			'priority' => $priority->add()
		)
	);

	// Site title font size
	$setting_id = $setting_prefix . '-site-tagline-size';
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
			'label'    => __( 'Site Tagline Font Size (in px)', 'make' ),
			'type'     => 'text',
			'priority' => $priority->add()
		)
	);

	// Navigation font size
	$setting_id = $setting_prefix . '-nav-size';
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
			'label'    => __( 'Navigation Font Size (in px)', 'make' ),
			'type'     => 'text',
			'priority' => $priority->add()
		)
	);

	// Header font size
	$setting_id = $setting_prefix . '-header-size';
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
			'label'    => __( 'Header Font Size (in px)', 'make' ),
			'type'     => 'text',
			'priority' => $priority->add()
		)
	);

	// Widget font size
	$setting_id = $setting_prefix . '-widget-size';
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
			'label'    => __( 'Widget Font Size (in px)', 'make' ),
			'type'     => 'text',
			'priority' => $priority->add()
		)
	);

	// Body font size
	$setting_id = $setting_prefix . '-body-size';
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
			'label'    => __( 'Body Font Size (in px)', 'make' ),
			'type'     => 'text',
			'priority' => $priority->add()
		)
	);

	// Font size line
	$setting_id = $setting_prefix . '-size-line';
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

	// Character Subset
	$setting_id = $setting_prefix . '-subset';
	$wp_customize->add_setting(
		$setting_id,
		array(
			'default'           => ttfmake_get_default( $setting_id ),
			'type'              => 'theme_mod',
			'sanitize_callback' => 'ttfmake_sanitize_font_subset',
		)
	);
	$wp_customize->add_control(
		$control_prefix . $setting_id,
		array(
			'settings' => $setting_id,
			'section'  => $section,
			'label'    => __( 'Character Subset', 'make' ),
			'type'     => 'select',
			'choices'  => ttfmake_get_google_font_subsets(),
			'priority' => $priority->add()
		)
	);

	// Character subset info
	$setting_id = $setting_prefix . '-subset-info';
	$wp_customize->add_control(
		new TTFMAKE_Customize_Misc_Control(
			$wp_customize,
			$control_prefix . $setting_id,
			array(
				'section'     => $section,
				'type'        => 'text',
				'description' => __( 'Not all fonts provide each of these subsets.', 'make' ),
				'priority'    => $priority->add()
			)
		)
	);
}
endif;