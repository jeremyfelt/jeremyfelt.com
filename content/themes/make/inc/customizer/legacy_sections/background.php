<?php
/**
 * @package Make
 */

if ( ! function_exists( 'ttfmake_customizer_background' ) ) :
/**
 * Configure settings and controls for the Background section.
 *
 * @since  1.0.0.
 *
 * @return void
 */
function ttfmake_customizer_background() {
	global $wp_customize;

	$priority       = new TTFMAKE_Prioritizer( 10, 5 );
	$control_prefix = 'ttfmake_';
	$section        = 'background_image';

	// Rename Background Image section to Background
	$wp_customize->get_section( $section )->title = __( 'Background', 'make' );

	// Move Background Color to Background section
	$wp_customize->get_control( 'background_color' )->section = $section;

	// Background note
	$setting_id = 'background-info';
	$wp_customize->add_control(
		new TTFMAKE_Customize_Misc_Control(
			$wp_customize,
			$control_prefix . $setting_id,
			array(
				'section'     => $section,
				'type'        => 'text',
				'description' => __( 'With the Site Layout option (under <em>General</em>) set to "Full Width", the background color and image will not be visible.', 'make' ),
				'priority'    => $priority->add()
			)
		)
	);

	// Reset priorities on existing controls
	$wp_customize->get_control( 'background_color' )->priority = $priority->add();
	$wp_customize->get_control( 'background_image' )->priority = $priority->add();
	$wp_customize->get_control( 'background_repeat' )->priority = $priority->add();
	$wp_customize->get_control( 'background_position_x' )->priority = $priority->add();
	$wp_customize->get_control( 'background_attachment' )->priority = $priority->add();

	// Background Size
	$setting_id = 'background_size';
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
}
endif;

add_action( 'customize_register', 'ttfmake_customizer_background', 20 );