<?php
/**
 * @package Make
 */

if ( ! function_exists( 'ttfmake_customizer_define_colorscheme_sections' ) ) :
/**
 * Define the sections and settings for the General panel
 *
 * @since  1.3.0.
 *
 * @param  array    $sections    The master array of Customizer sections
 * @return array                 The augmented master array
 */
function ttfmake_customizer_define_colorscheme_sections( $sections ) {
	$panel = 'ttfmake_color-scheme';
	$colorscheme_sections = array();

	/**
	 * General
	 */
	$colorscheme_sections['color'] = array(
		'panel'   => $panel,
		'title'   => __( 'General', 'make' ),
		'options' => array(
			'color-primary'   => array(
				'setting' => array(
					'sanitize_callback' => 'maybe_hash_hex_color',
				),
				'control' => array(
					'control_type' => 'WP_Customize_Color_Control',
					'label'        => __( 'Primary Color', 'make' ),
				),
			),
			'color-secondary' => array(
				'setting' => array(
					'sanitize_callback' => 'maybe_hash_hex_color',
				),
				'control' => array(
					'control_type' => 'WP_Customize_Color_Control',
					'label'        => __( 'Secondary Color', 'make' ),
				),
			),
			'color-text'      => array(
				'setting' => array(
					'sanitize_callback' => 'maybe_hash_hex_color',
				),
				'control' => array(
					'control_type' => 'WP_Customize_Color_Control',
					'label'        => __( 'Text Color', 'make' ),
				),
			),
			'color-detail'    => array(
				'setting' => array(
					'sanitize_callback' => 'maybe_hash_hex_color',
				),
				'control' => array(
					'control_type' => 'WP_Customize_Color_Control',
					'label'        => __( 'Detail Color', 'make' ),
				),
			),
		),
	);

	/**
	 * Background
	 */
	$colorscheme_sections['color-background'] = array(
		'panel'   => $panel,
		'title'   => __( 'Background', 'make' ),
		'options' => array(
			'main-background-color' => array(
				'setting' => array(
					'sanitize_callback' => 'maybe_hash_hex_color',
				),
				'control' => array(
					'control_type' => 'WP_Customize_Color_Control',
					'label'        => __( 'Main Column Background Color', 'make' ),
				),
			),
		),
	);

	/**
	 * Header
	 */
	$colorscheme_sections['color-header'] = array(
		'panel'   => $panel,
		'title'   => __( 'Header', 'make' ),
		'options' => array(
			'header-bar-background-color' => array(
				'setting' => array(
					'sanitize_callback' => 'maybe_hash_hex_color',
				),
				'control' => array(
					'control_type' => 'WP_Customize_Color_Control',
					'label'        => __( 'Header Bar Background Color', 'make' ),
				),
			),
			'header-bar-text-color'       => array(
				'setting' => array(
					'sanitize_callback' => 'maybe_hash_hex_color',
				),
				'control' => array(
					'control_type' => 'WP_Customize_Color_Control',
					'label'        => __( 'Header Bar Text Color', 'make' ),
				),
			),
			'header-bar-border-color'     => array(
				'setting' => array(
					'sanitize_callback' => 'maybe_hash_hex_color',
				),
				'control' => array(
					'control_type' => 'WP_Customize_Color_Control',
					'label'        => __( 'Header Bar Border Color', 'make' ),
				),
			),
			'header-color-line'           => array(
				'control' => array(
					'control_type' => 'TTFMAKE_Customize_Misc_Control',
					'type'         => 'line',
				),
			),
			'header-background-color'     => array(
				'setting' => array(
					'sanitize_callback' => 'maybe_hash_hex_color',
				),
				'control' => array(
					'control_type' => 'WP_Customize_Color_Control',
					'label'        => __( 'Header Background Color', 'make' ),
				),
			),
			'header-text-color'           => array(
				'setting' => array(
					'sanitize_callback' => 'maybe_hash_hex_color',
				),
				'control' => array(
					'control_type' => 'WP_Customize_Color_Control',
					'label'        => __( 'Header Text Color', 'make' ),
				),
			),
			'header-element-color-line'   => array(
				'control' => array(
					'control_type' => 'TTFMAKE_Customize_Misc_Control',
					'type'         => 'line',
				),
			),
			'color-site-title'            => array(
				'setting' => array(
					'sanitize_callback' => 'maybe_hash_hex_color',
				),
				'control' => array(
					'control_type' => 'WP_Customize_Color_Control',
					'label'        => __( 'Site Title Color', 'make' ),
				),
			),
		),
	);

	/**
	 * Sidebars
	 *
	 * TODO
	 */

	/**
	 * Widgets
	 *
	 * TODO
	 */

	/**
	 * Footer
	 */
	$colorscheme_sections['color-footer'] = array(
		'panel'   => $panel,
		'title'   => __( 'Footer', 'make' ),
		'options' => array(
			'footer-background-color' => array(
				'setting' => array(
					'sanitize_callback' => 'maybe_hash_hex_color',
				),
				'control' => array(
					'control_type' => 'WP_Customize_Color_Control',
					'label'        => __( 'Footer Background Color', 'make' ),
				),
			),
			'footer-text-color'       => array(
				'setting' => array(
					'sanitize_callback' => 'maybe_hash_hex_color',
				),
				'control' => array(
					'control_type' => 'WP_Customize_Color_Control',
					'label'        => __( 'Footer Text Color', 'make' ),
				),
			),
			'footer-border-color'     => array(
				'setting' => array(
					'sanitize_callback' => 'maybe_hash_hex_color',
				),
				'control' => array(
					'control_type' => 'WP_Customize_Color_Control',
					'label'        => __( 'Footer Border Color', 'make' ),
				),
			),
		),
	);

	/**
	 * Filter the definitions for the controls in the Color Scheme panel of the Customizer.
	 *
	 * @since 1.3.0.
	 *
	 * @param array    $colorscheme_sections    The array of definitions.
	 */
	$colorscheme_sections = apply_filters( 'make_customizer_colorscheme_sections', $colorscheme_sections );

	// Merge with master array
	return array_merge( $sections, $colorscheme_sections );
}
endif;

add_filter( 'make_customizer_sections', 'ttfmake_customizer_define_colorscheme_sections' );