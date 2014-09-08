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
	$theme_prefix = 'ttfmake_';
	$section_id = 'title_tagline';
	$section = $wp_customize->get_section( $section_id );
	$priority = new TTFMAKE_Prioritizer( 10, 5 );

	// Move Site Title & Tagline section to General panel
	$section->panel = $theme_prefix . 'general';

	// Set Site Title & Tagline section priority
	$logo_priority = $wp_customize->get_section( $theme_prefix . 'logo' )->priority;
	$section->priority = $logo_priority - 5;

	// Adjust section title if no panel support
	if ( ! ttfmake_customizer_supports_panels() ) {
		$panels = ttfmake_customizer_get_panels();
		if ( isset( $panels['general']['title'] ) ) {
			$section->title = $panels['general']['title'] . ': ' . $section->title;
		}
	}

	// Reset priorities on Site Title control
	$wp_customize->get_control( 'blogname' )->priority = $priority->add();

	// Hide Site Title option
	$options = array(
		'hide-site-title' => array(
			'setting' => array(
				'sanitize_callback' => 'absint',
			),
			'control' => array(
				'label' => __( 'Hide Site Title', 'make' ),
				'type'  => 'checkbox',
			),
		),
	);
	$new_priority = ttfmake_customizer_add_section_options( $section_id, $options, $priority->add() );
	$priority->set( $new_priority );

	// Reset priorities on Tagline control
	$wp_customize->get_control( 'blogdescription' )->priority = $priority->add();

	// Hide Tagline option
	$options = array(
		'hide-tagline' => array(
			'setting' => array(
				'sanitize_callback' => 'absint',
			),
			'control' => array(
				'label' => __( 'Hide Tagline', 'make' ),
				'type'  => 'checkbox',
			),
		),
	);
	$new_priority = ttfmake_customizer_add_section_options( $section_id, $options, $priority->add() );
	$priority->set( $new_priority );
}
endif;

add_action( 'customize_register', 'ttfmake_customizer_sitetitletagline', 20 );