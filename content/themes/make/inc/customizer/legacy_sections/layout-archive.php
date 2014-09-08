<?php
/**
 * @package Make
 */

if ( ! function_exists( 'ttfmake_customizer_layout_archive' ) ) :
/**
 * Configure settings and controls for the Layout: Archives section.
 *
 * @since  1.0.0.
 *
 * @param  object    $wp_customize    The global customizer object.
 * @param  string    $section         The section name.
 * @return void
 */
function ttfmake_customizer_layout_archive( $wp_customize, $section ) {
	$priority       = new TTFMAKE_Prioritizer();
	$control_prefix = 'ttfmake_';
	$setting_prefix = str_replace( $control_prefix, '', $section );

	// Header, Footer, Sidebars heading
	$setting_id = $setting_prefix . '-heading-sidebars';
	$wp_customize->add_control(
		new TTFMAKE_Customize_Misc_Control(
			$wp_customize,
			$control_prefix . $setting_id,
			array(
				'section'  => $section,
				'type'     => 'heading',
				'label'    => __( 'Header, Footer, Sidebars', 'make' ),
				'priority' => $priority->add()
			)
		)
	);

	// Hide site header
	$setting_id = $setting_prefix . '-hide-header';
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
			'label'    => __( 'Hide site header', 'make' ),
			'type'     => 'checkbox',
			'priority' => $priority->add()
		)
	);

	// Hide site footer
	$setting_id = $setting_prefix . '-hide-footer';
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
			'label'    => __( 'Hide site footer', 'make' ),
			'type'     => 'checkbox',
			'priority' => $priority->add()
		)
	);

	// Left sidebar
	$setting_id = $setting_prefix . '-sidebar-left';
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
			'label'    => __( 'Show left sidebar', 'make' ),
			'type'     => 'checkbox',
			'priority' => $priority->add()
		)
	);

	// Right sidebar
	$setting_id = $setting_prefix . '-sidebar-right';
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
			'label'    => __( 'Show right sidebar', 'make' ),
			'type'     => 'checkbox',
			'priority' => $priority->add()
		)
	);

	// Featured images
	$setting_id = $setting_prefix . '-featured-images';
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
			'label'    => __( 'Featured Images', 'make' ),
			'type'     => 'select',
			'choices'  => ttfmake_get_choices( $setting_id ),
			'priority' => $priority->add()
		)
	);

	// Featured images alignment
	$setting_id = $setting_prefix . '-featured-images-alignment';
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
			'label'    => __( 'Featured Images Alignment', 'make' ),
			'type'     => 'select',
			'choices'  => ttfmake_get_choices( $setting_id ),
			'priority' => $priority->add()
		)
	);

	// Post date
	$setting_id = $setting_prefix . '-post-date';
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
			'label'    => __( 'Post Date', 'make' ),
			'type'     => 'select',
			'choices'  => ttfmake_get_choices( $setting_id ),
			'priority' => $priority->add()
		)
	);

	// Post date location
	$setting_id = $setting_prefix . '-post-date-location';
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
			'label'    => __( 'Post Date Location', 'make' ),
			'type'     => 'select',
			'choices'  => ttfmake_get_choices( $setting_id ),
			'priority' => $priority->add()
		)
	);

	// Post author
	$setting_id = $setting_prefix . '-post-author';
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
			'label'    => __( 'Post Author', 'make' ),
			'type'     => 'select',
			'choices'  => ttfmake_get_choices( $setting_id ),
			'priority' => $priority->add()
		)
	);

	// Post author location
	$setting_id = $setting_prefix . '-post-author-location';
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
			'label'    => __( 'Post Author Location', 'make' ),
			'type'     => 'select',
			'choices'  => ttfmake_get_choices( $setting_id ),
			'priority' => $priority->add()
		)
	);

	// Content heading
	$setting_id = $setting_prefix . '-heading-content';
	$wp_customize->add_control(
		new TTFMAKE_Customize_Misc_Control(
			$wp_customize,
			$control_prefix . $setting_id,
			array(
				'section'     => $section,
				'type'        => 'heading',
				'label' => __( 'Content', 'make' ),
				'priority'    => $priority->add()
			)
		)
	);

	// Auto excerpt
	$setting_id = $setting_prefix . '-auto-excerpt';
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
			'label'    => __( 'Generate excerpts automatically', 'make' ),
			'type'     => 'checkbox',
			'priority' => $priority->add()
		)
	);

	// Post meta heading
	$setting_id = $setting_prefix . '-heading-postmeta';
	$wp_customize->add_control(
		new TTFMAKE_Customize_Misc_Control(
			$wp_customize,
			$control_prefix . $setting_id,
			array(
				'section'  => $section,
				'type'     => 'heading',
				'label'    => __( 'Post Meta', 'make' ),
				'priority' => $priority->add()
			)
		)
	);

	// Show categories
	$setting_id = $setting_prefix . '-show-categories';
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
			'label'    => __( 'Show categories', 'make' ),
			'type'     => 'checkbox',
			'priority' => $priority->add()
		)
	);

	// Show tags
	$setting_id = $setting_prefix . '-show-tags';
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
			'label'    => __( 'Show tags', 'make' ),
			'type'     => 'checkbox',
			'priority' => $priority->add()
		)
	);

	// Comment count
	$setting_id = $setting_prefix . '-comment-count';
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
			'label'    => __( 'Comment Count', 'make' ),
			'type'     => 'select',
			'choices'  => ttfmake_get_choices( $setting_id ),
			'priority' => $priority->add()
		)
	);

	// Comment count location
	$setting_id = $setting_prefix . '-comment-count-location';
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
			'label'    => __( 'Comment Count Location', 'make' ),
			'type'     => 'select',
			'choices'  => ttfmake_get_choices( $setting_id ),
			'priority' => $priority->add()
		)
	);
}
endif;