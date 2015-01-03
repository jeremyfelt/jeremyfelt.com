<?php
/**
 * @package Make
 */

if ( ! function_exists( 'ttfmake_customizer_define_footer_sections' ) ) :
/**
 * Define the sections and settings for the Footer panel
 *
 * @since  1.3.0.
 *
 * @param  array    $sections    The master array of Customizer sections
 * @return array                 The augmented master array
 */
function ttfmake_customizer_define_footer_sections( $sections ) {
	$theme_prefix = 'ttfmake_';
	$panel = 'ttfmake_footer';
	$footer_sections = array();

	/**
	 * Background Image
	 */
	$footer_sections['footer-background'] = array(
		'panel'   => $panel,
		'title'   => __( 'Background Image', 'make' ),
		'options' => array(
			'footer-background-image'    => array(
				'setting' => array(
					'sanitize_callback' => 'esc_url_raw',
				),
				'control' => array(
					'control_type' => 'TTFMAKE_Customize_Image_Control',
					'label'        => __( 'Footer Background Image', 'make' ),
					'context'      => $theme_prefix . 'footer-background-image',
				),
			),
			'footer-background-repeat'   => array(
				'setting' => array(
					'sanitize_callback' => 'ttfmake_sanitize_choice',
				),
				'control' => array(
					'label'   => __( 'Footer Background Repeat', 'make' ),
					'type'    => 'radio',
					'choices' => ttfmake_get_choices( 'footer-background-repeat' ),
				),
			),
			'footer-background-position' => array(
				'setting' => array(
					'sanitize_callback' => 'ttfmake_sanitize_choice',
				),
				'control' => array(
					'label'   => __( 'Footer Background Position', 'make' ),
					'type'    => 'radio',
					'choices' => ttfmake_get_choices( 'footer-background-position' ),
				),
			),
			'footer-background-size'     => array(
				'setting' => array(
					'sanitize_callback' => 'ttfmake_sanitize_choice',
				),
				'control' => array(
					'label'   => __( 'Footer Background Size', 'make' ),
					'type'    => 'radio',
					'choices' => ttfmake_get_choices( 'footer-background-size' ),
				),
			),
		),
	);

	/**
	 * Widget Areas
	 */
	$footer_sections['footer-widget'] = array(
		'panel' => $panel,
		'title' => __( 'Widget Areas', 'make' ),
		'options' => array(
			'footer-widget-areas' => array(
				'setting' => array(
					'sanitize_callback'	=> 'ttfmake_sanitize_choice',
				),
				'control' => array(
					'label'				=> __( 'Number of Widget Areas', 'make' ),
					'type'				=> 'select',
					'choices'			=> ttfmake_get_choices( 'footer-widget-areas' ),
				),
			),
		),
	);

	/**
	 * Layout
	 */
	$footer_sections['footer'] = array(
		'panel' => $panel,
		'title' => __( 'Layout', 'make' ),
		'options' => array(
			'footer-layout' => array(
				'setting' => array(
					'sanitize_callback'	=> 'ttfmake_sanitize_choice',
				),
				'control' => array(
					'label'				=> __( 'Footer Layout', 'make' ),
					'type'				=> 'select',
					'choices'			=> ttfmake_get_choices( 'footer-layout' ),
				),
			),
			'footer-layout-line' => array(
				'control' => array(
					'control_type'		=> 'TTFMAKE_Customize_Misc_Control',
					'type'				=> 'line',
				),
			),
			'footer-text' => array(
				'setting' => array(
					'sanitize_callback'	=> 'ttfmake_sanitize_text',
					'transport'			=> 'postMessage',
				),
				'control' => array(
					'label'				=> __( 'Footer Text', 'make' ),
					'type'				=> 'text',
				),
			),
			'footer-options-heading' => array(
				'control' => array(
					'control_type'		=> 'TTFMAKE_Customize_Misc_Control',
					'type'				=> 'heading',
					'label'				=> __( 'Social Icons', 'make' ),
				),
			),
			'footer-show-social' => array(
				'setting' => array(
					'sanitize_callback'	=> 'absint',
				),
				'control' => array(
					'label'				=> __( 'Show social icons in footer', 'make' ),
					'type'				=> 'checkbox',
				),
			),
		),
	);

	/**
	 * White Label
	 */
	if ( ! ttfmake_is_plus() ) {
		$footer_sections['footer-white-label'] = array(
			'panel'       => $panel,
			'title'       => __( 'White Label', 'make' ),
			'description' => __( 'Want to remove the theme byline from your website&#8217;s footer?', 'make' ),
			'options'     => array(
				'footer-white-label-text' => array(
					'control' => array(
						'control_type' => 'TTFMAKE_Customize_Misc_Control',
						'type'         => 'text',
						'description'  => sprintf(
							'<a href="%1$s" target="_blank">%2$s</a>',
							esc_url( ttfmake_get_plus_link( 'white-label' ) ),
							sprintf(
								__( 'Upgrade to %1$s', 'make' ),
								'Make Plus'
							)
						),
					),
				),
			),
		);
	}

	/**
	 * Filter the definitions for the controls in the Footer panel of the Customizer.
	 *
	 * @since 1.3.0.
	 *
	 * @param array    $footer_sections    The array of definitions.
	 */
	$footer_sections = apply_filters( 'make_customizer_footer_sections', $footer_sections );

	// Merge with master array
	return array_merge( $sections, $footer_sections );
}
endif;

add_filter( 'make_customizer_sections', 'ttfmake_customizer_define_footer_sections' );