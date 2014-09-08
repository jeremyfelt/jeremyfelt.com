<?php
/**
 * @package Make
 */

if ( ! function_exists( 'ttfmake_customizer_staticfrontpage' ) ) :
/**
 * Configure settings and controls for the Static Front Page section.
 *
 * @since  1.3.0.
 *
 * @return void
 */
function ttfmake_customizer_staticfrontpage() {
	global $wp_customize;
	$theme_prefix = 'ttfmake_';
	$section_id   = 'static_front_page';
	$section      = $wp_customize->get_section( $section_id );
	$priority     = new TTFMAKE_Prioritizer( 10, 5 );

	// Move Static Front Page section to General panel
	$section->panel = $theme_prefix . 'general';

	// Set Static Front Page section priority
	$social_priority = $wp_customize->get_section( $theme_prefix . 'social' )->priority;
	$section->priority = $social_priority + 5;

	// Adjust section title if no panel support
	if ( ! ttfmake_customizer_supports_panels() ) {
		$panels = ttfmake_customizer_get_panels();
		if ( isset( $panels['general']['title'] ) ) {
			$section->title = $panels['general']['title'] . ': ' . $section->title;
		}
	}
}
endif;

add_action( 'customize_register', 'ttfmake_customizer_staticfrontpage', 20 );