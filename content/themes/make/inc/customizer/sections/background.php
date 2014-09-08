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
	$theme_prefix = 'ttfmake_';
	$section_id = 'background_image';
	$section = $wp_customize->get_section( $section_id );
	$priority = new TTFMAKE_Prioritizer( 10, 5 );

	// Move and rename Background Color control to General section of Color Scheme panel
	$wp_customize->get_control( 'background_color' )->section = $theme_prefix . 'color-background';
	$wp_customize->get_control( 'background_color' )->label = __( 'Site Background Color', 'make' );

	// Move Background Image section to General panel
	$section->panel = $theme_prefix . 'general';

	// Set Background Image section priority
	$logo_priority = $wp_customize->get_section( $theme_prefix . 'logo' )->priority;
	$section->priority = $logo_priority + 5;

	// Adjust section title if no panel support
	if ( ! ttfmake_customizer_supports_panels() ) {
		$panels = ttfmake_customizer_get_panels();
		if ( isset( $panels['general']['title'] ) ) {
			$section->title = $panels['general']['title'] . ': ' . $section->title;
		}
	}

	// Rename Background Image controls
	$wp_customize->get_control( 'background_image' )->label = __( 'Site Background Image', 'make' );
	$wp_customize->get_control( 'background_repeat' )->label = __( 'Site Background Repeat', 'make' );
	$wp_customize->get_control( 'background_position_x' )->label = __( 'Site Background Position', 'make' );
	$wp_customize->get_control( 'background_attachment' )->label = __( 'Site Background Attachment', 'make' );

	// Reset priorities on existing controls
	$wp_customize->get_control( 'background_image' )->priority = $priority->add();
	$wp_customize->get_control( 'background_repeat' )->priority = $priority->add();
	$wp_customize->get_control( 'background_position_x' )->priority = $priority->add();
	$wp_customize->get_control( 'background_attachment' )->priority = $priority->add();

	// Add new option for Site background image
	$options = array(
		'background_size' => array(
			'setting' => array(
				'sanitize_callback' => 'ttfmake_sanitize_choice',
			),
			'control' => array(
				'label'   => __( 'Site Background Size', 'make' ),
				'type'    => 'radio',
				'choices' => ttfmake_get_choices( 'background_size' ),
			),
		),
	);
	$new_priority = ttfmake_customizer_add_section_options( $section_id, $options, $priority->add() );
	$priority->set( $new_priority );

	// Add options for Main Column background image
	$options = array(
		'main-background-image' => array(
			'setting' => array(
				'sanitize_callback' => 'esc_url_raw',
			),
			'control' => array(
				'control_type' => 'TTFMAKE_Customize_Image_Control',
				'label'        => __( 'Main Column Background Image', 'make' ),
				'context'      => $theme_prefix . 'main-background-image',
			),
		),
		'main-background-repeat' => array(
			'setting' => array(
				'sanitize_callback' => 'ttfmake_sanitize_choice',
			),
			'control' => array(
				'label'   => __( 'Main Column Background Repeat', 'make' ),
				'type'    => 'radio',
				'choices' => ttfmake_get_choices( 'main-background-repeat' ),
			),
		),
		'main-background-position' => array(
			'setting' => array(
				'sanitize_callback' => 'ttfmake_sanitize_choice',
			),
			'control' => array(
				'label'   => __( 'Main Column Background Position', 'make' ),
				'type'    => 'radio',
				'choices' => ttfmake_get_choices( 'main-background-position' ),
			),
		),
		'main-background-size' => array(
			'setting' => array(
				'sanitize_callback' => 'ttfmake_sanitize_choice',
			),
			'control' => array(
				'label'   => __( 'Main Column Background Size', 'make' ),
				'type'    => 'radio',
				'choices' => ttfmake_get_choices( 'main-background-size' ),
			),
		),
	);
	$new_priority = ttfmake_customizer_add_section_options( $section_id, $options, $priority->add() );
	$priority->set( $new_priority );
}
endif;

add_action( 'customize_register', 'ttfmake_customizer_background', 20 );