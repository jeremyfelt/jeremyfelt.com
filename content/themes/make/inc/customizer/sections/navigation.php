<?php
/**
 * @package Make
 */

if ( ! function_exists( 'ttfmake_customizer_navigation' ) ) :
/**
 * Configure settings and controls for the Navigation section.
 *
 * @since  1.0.0.
 *
 * @return void
 */
function ttfmake_customizer_navigation() {
	global $wp_customize;

	$priority       = new TTFMAKE_Prioritizer();
	$control_prefix = 'ttfmake_';
	$section        = 'nav';

	// Menu Label
	$setting_id = 'navigation-mobile-label';
	$wp_customize->add_setting(
		$setting_id,
		array(
			'default'           => ttfmake_get_default( $setting_id ),
			'type'              => 'theme_mod',
			'sanitize_callback' => 'esc_html',
			'theme_supports'    => 'menus',
			'transport'         => 'postMessage' // Asynchronous preview
		)
	);
	$wp_customize->add_control(
		$control_prefix . $setting_id,
		array(
			'settings' => $setting_id,
			'section'  => $section,
			'label'    => __( 'Mobile Menu Label', 'make' ),
			'type'     => 'text',
			'priority' => $priority->add()
		)
	);

	// Menu Label info
	$setting_id = 'navigation-mobile-lable-info';
	$wp_customize->add_control(
		new TTFMAKE_Customize_Misc_Control(
			$wp_customize,
			$control_prefix . $setting_id,
			array(
				'section'     => $section,
				'type'        => 'text',
				'description' => __( 'Resize your browser window to preview the mobile menu label.', 'make' ),
				'priority'    => $priority->add()
			)
		)
	);
}
endif;

add_action( 'customize_register', 'ttfmake_customizer_navigation', 20 );