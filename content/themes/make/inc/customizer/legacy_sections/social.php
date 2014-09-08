<?php
/**
 * @package Make
 */

if ( ! function_exists( 'ttfmake_customizer_social' ) ) :
/**
 * Configure settings and controls for the Social section
 *
 * @since 1.0.0
 *
 * @param  object    $wp_customize    The global customizer object.
 * @param  string    $section         The section name.
 * @return void
 */
function ttfmake_customizer_social( $wp_customize, $section ) {
	$priority       = new TTFMAKE_Prioritizer();
	$control_prefix = 'ttfmake_';
	$setting_prefix = str_replace( $control_prefix, '', $section );

	// Social description
	$setting_id = $setting_prefix . '-description';
	$wp_customize->add_control(
		new TTFMAKE_Customize_Misc_Control(
			$wp_customize,
			$control_prefix . $setting_id,
			array(
				'section'     => $section,
				'type'        => 'text',
				'description' => __( 'Enter the complete URL to your profile for each service below that you would like to share.', 'make' ),
				'priority'    => $priority->add()
			)
		)
	);

	// Facebook
	$setting_id = $setting_prefix . '-facebook';
	$wp_customize->add_setting(
		$setting_id,
		array(
			'default'           => ttfmake_get_default( $setting_id ),
			'type'              => 'theme_mod',
			'sanitize_callback' => 'esc_url_raw',
		)
	);
	$wp_customize->add_control(
		$control_prefix . $setting_id,
		array(
			'settings' => $setting_id,
			'section'  => $section,
			'label'    => 'Facebook', // brand names not translated
			'type'     => 'text',
			'priority' => $priority->add()
		)
	);

	// Twitter
	$setting_id = $setting_prefix . '-twitter';
	$wp_customize->add_setting(
		$setting_id,
		array(
			'default'           => ttfmake_get_default( $setting_id ),
			'type'              => 'theme_mod',
			'sanitize_callback' => 'esc_url_raw',
		)
	);
	$wp_customize->add_control(
		$control_prefix . $setting_id,
		array(
			'settings' => $setting_id,
			'section'  => $section,
			'label'    => 'Twitter', // brand names not translated
			'type'     => 'text',
			'priority' => $priority->add()
		)
	);

	// Google +
	$setting_id = $setting_prefix . '-google-plus-square';
	$wp_customize->add_setting(
		$setting_id,
		array(
			'default'           => ttfmake_get_default( $setting_id ),
			'type'              => 'theme_mod',
			'sanitize_callback' => 'esc_url_raw',
		)
	);
	$wp_customize->add_control(
		$control_prefix . $setting_id,
		array(
			'settings' => $setting_id,
			'section'  => $section,
			'label'    => 'Google +', // brand names not translated
			'type'     => 'text',
			'priority' => $priority->add()
		)
	);

	// LinkedIn
	$setting_id = $setting_prefix . '-linkedin';
	$wp_customize->add_setting(
		$setting_id,
		array(
			'default'           => ttfmake_get_default( $setting_id ),
			'type'              => 'theme_mod',
			'sanitize_callback' => 'esc_url_raw',
		)
	);
	$wp_customize->add_control(
		$control_prefix . $setting_id,
		array(
			'settings' => $setting_id,
			'section'  => $section,
			'label'    => 'LinkedIn', // brand names not translated
			'type'     => 'text',
			'priority' => $priority->add()
		)
	);

	// Instagram
	$setting_id = $setting_prefix . '-instagram';
	$wp_customize->add_setting(
		$setting_id,
		array(
			'default'           => ttfmake_get_default( $setting_id ),
			'type'              => 'theme_mod',
			'sanitize_callback' => 'esc_url_raw',
		)
	);
	$wp_customize->add_control(
		$control_prefix . $setting_id,
		array(
			'settings' => $setting_id,
			'section'  => $section,
			'label'    => 'Instagram', // brand names not translated
			'type'     => 'text',
			'priority' => $priority->add()
		)
	);

	// Flickr
	$setting_id = $setting_prefix . '-flickr';
	$wp_customize->add_setting(
		$setting_id,
		array(
			'default'           => ttfmake_get_default( $setting_id ),
			'type'              => 'theme_mod',
			'sanitize_callback' => 'esc_url_raw',
		)
	);
	$wp_customize->add_control(
		$control_prefix . $setting_id,
		array(
			'settings' => $setting_id,
			'section'  => $section,
			'label'    => 'Flickr', // brand names not translated
			'type'     => 'text',
			'priority' => $priority->add()
		)
	);

	// YouTube
	$setting_id = $setting_prefix . '-youtube';
	$wp_customize->add_setting(
		$setting_id,
		array(
			'default'           => ttfmake_get_default( $setting_id ),
			'type'              => 'theme_mod',
			'sanitize_callback' => 'esc_url_raw',
		)
	);
	$wp_customize->add_control(
		$control_prefix . $setting_id,
		array(
			'settings' => $setting_id,
			'section'  => $section,
			'label'    => 'YouTube', // brand names not translated
			'type'     => 'text',
			'priority' => $priority->add()
		)
	);

	// Vimeo
	$setting_id = $setting_prefix . '-vimeo-square';
	$wp_customize->add_setting(
		$setting_id,
		array(
			'default'           => ttfmake_get_default( $setting_id ),
			'type'              => 'theme_mod',
			'sanitize_callback' => 'esc_url_raw',
		)
	);
	$wp_customize->add_control(
		$control_prefix . $setting_id,
		array(
			'settings' => $setting_id,
			'section'  => $section,
			'label'    => 'Vimeo', // brand names not translated
			'type'     => 'text',
			'priority' => $priority->add()
		)
	);

	// Pinterest
	$setting_id = $setting_prefix . '-pinterest';
	$wp_customize->add_setting(
		$setting_id,
		array(
			'default'           => ttfmake_get_default( $setting_id ),
			'type'              => 'theme_mod',
			'sanitize_callback' => 'esc_url_raw',
		)
	);
	$wp_customize->add_control(
		$control_prefix . $setting_id,
		array(
			'settings' => $setting_id,
			'section'  => $section,
			'label'    => 'Pinterest', // brand names not translated
			'type'     => 'text',
			'priority' => $priority->add()
		)
	);

	// Custom alternate
	$setting_id = $setting_prefix . '-custom-alternate';
	$wp_customize->add_control(
		new TTFMAKE_Customize_Misc_Control(
			$wp_customize,
			$control_prefix . $setting_id,
			array(
				'section'     => $section,
				'type'        => 'text',
				'description' => sprintf(
					__( 'If you would like to add a social profile that is not listed above, or change the order of the icons, use %s.', 'make' ),
					sprintf(
						'<a href="' . esc_url( 'https://thethemefoundry.com/tutorials/make/#social-profiles-and-rss' ) . '">%s</a>',
						__( 'this alternate method', 'make' )
					)
				),
				'priority'    => $priority->add()
			)
		)
	);

	// Email
	$setting_id = $setting_prefix . '-email';
	$wp_customize->add_setting(
		$setting_id,
		array(
			'default'           => ttfmake_get_default( $setting_id ),
			'type'              => 'theme_mod',
			'sanitize_callback' => 'sanitize_email',
		)
	);
	$wp_customize->add_control(
		$control_prefix . $setting_id,
		array(
			'settings' => $setting_id,
			'section'  => $section,
			'label'    => __( 'Email', 'make' ),
			'type'     => 'text',
			'priority' => $priority->add()
		)
	);

	// RSS Heading
	$setting_id = $setting_prefix . '-rss-heading';
	$wp_customize->add_control(
		new TTFMAKE_Customize_Misc_Control(
			$wp_customize,
			$control_prefix . $setting_id,
			array(
				'section'     => $section,
				'type'        => 'heading',
				'label' => __( 'Default RSS', 'make' ),
				'priority'    => $priority->add()
			)
		)
	);

	// Hide RSS
	$setting_id = $setting_prefix . '-hide-rss';
	$wp_customize->add_setting(
		$setting_id,
		array(
			'default'           => ttfmake_get_default( $setting_id ),
			'type'              => 'theme_mod',
			'sanitize_callback' => 'absint'
		)
	);
	$wp_customize->add_control(
		$control_prefix . $setting_id,
		array(
			'settings' => $setting_id,
			'section'  => $section,
			'label'    => __( 'Hide default RSS feed link', 'make' ),
			'type'     => 'checkbox',
			'priority' => $priority->add()
		)
	);

	// Custom RSS
	$setting_id = $setting_prefix . '-custom-rss';
	$wp_customize->add_setting(
		$setting_id,
		array(
			'default'           => ttfmake_get_default( $setting_id ),
			'type'              => 'theme_mod',
			'sanitize_callback' => 'esc_url_raw',
		)
	);
	$wp_customize->add_control(
		$control_prefix . $setting_id,
		array(
			'settings' => $setting_id,
			'section'  => $section,
			'label'    => __( 'Custom RSS URL (replaces default)', 'make' ),
			'type'     => 'text',
			'priority' => $priority->add()
		)
	);
}
endif;