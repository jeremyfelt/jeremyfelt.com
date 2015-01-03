<?php
/**
 * @package Make
 */

if ( ! function_exists( 'ttfmake_customizer_define_typography_sections' ) ) :
/**
 * Define the sections and settings for the General panel
 *
 * @since  1.3.0.
 *
 * @param  array    $sections    The master array of Customizer sections
 * @return array                 The augmented master array
 */
function ttfmake_customizer_define_typography_sections( $sections ) {
	$panel = 'ttfmake_typography';
	$typography_sections = array();

	/**
	 * Typekit
	 */
	if ( ! ttfmake_is_plus() ) {
		$typography_sections['font-typekit'] = array(
			'panel'       => $panel,
			'title'       => __( 'Typekit', 'make' ),
			'description' => __( 'Looking to add premium fonts from Typekit to your website?', 'make' ),
			'options'     => array(
				'font-typekit-update-text' => array(
					'control' => array(
						'control_type' => 'TTFMAKE_Customize_Misc_Control',
						'type'         => 'text',
						'description'  => sprintf(
							'<a href="%1$s" target="_blank">%2$s</a>',
							esc_url( ttfmake_get_plus_link( 'typekit' ) ),
							sprintf(
								__( 'Upgrade to %1$s', 'make' ),
								'Make Plus'
							)
						),
					),
				)
			)
		);
	}

	/**
	 * Google Web Fonts
	 */
	$typography_sections['font-google'] = array(
		'panel'   => $panel,
		'title'   => __( 'Google Web Fonts', 'make' ),
		'options' => array(
			'font-subset'      => array(
				'setting' => array(
					'sanitize_callback' => 'ttfmake_sanitize_font_subset',
				),
				'control' => array(
					'label'   => __( 'Character Subset', 'make' ),
					'type'    => 'select',
					'choices' => ttfmake_get_google_font_subsets(),
				),
			),
			'font-subset-text' => array(
				'control' => array(
					'control_type' => 'TTFMAKE_Customize_Misc_Control',
					'type'         => 'text',
					'description'  => __( 'Not all fonts provide each of these subsets.', 'make' ),
				),
			),
		),
	);

	/**
	 * Site Title & Tagline
	 */
	$typography_sections['font-site-title-tagline'] = array(
		'panel'   => $panel,
		'title'   => __( 'Site Title &amp; Tagline', 'make' ),
		'options' => array(
			'font-family-site-title'   => array(
				'setting' => array(
					'sanitize_callback' => 'ttfmake_sanitize_font_choice',
				),
				'control' => array(
					'label'   => __( 'Site Title Font Family', 'make' ),
					'type'    => 'select',
					'choices' => ttfmake_font_choices_placeholder(),
				),
			),
			'font-size-site-title'     => array(
				'setting' => array(
					'sanitize_callback' => 'absint',
				),
				'control' => array(
					'label' => __( 'Site Title Font Size (in px)', 'make' ),
					'type'  => ( ttfmake_customizer_supports_panels() ) ? 'number' : 'text',
				),
			),
			'font-family-site-tagline' => array(
				'setting' => array(
					'sanitize_callback' => 'ttfmake_sanitize_font_choice',
				),
				'control' => array(
					'label'   => __( 'Site Tagline Font Family', 'make' ),
					'type'    => 'select',
					'choices' => ttfmake_font_choices_placeholder(),
				),
			),
			'font-size-site-tagline'   => array(
				'setting' => array(
					'sanitize_callback' => 'absint',
				),
				'control' => array(
					'label' => __( 'Site Tagline Font Size (in px)', 'make' ),
					'type'  => ( ttfmake_customizer_supports_panels() ) ? 'number' : 'text',
				),
			),
		)
	);

	/**
	 * Main Navigation
	 */
	$typography_sections['font-main-menu'] = array(
		'panel'   => $panel,
		'title'   => __( 'Main Menu', 'make' ),
		'options' => array(
			'font-family-nav'            => array(
				'setting' => array(
					'sanitize_callback' => 'ttfmake_sanitize_font_choice',
				),
				'control' => array(
					'label'   => __( 'Menu Item Font Family', 'make' ),
					'type'    => 'select',
					'choices' => ttfmake_font_choices_placeholder(),
				),
			),
			'font-size-nav'              => array(
				'setting' => array(
					'sanitize_callback' => 'absint',
				),
				'control' => array(
					'label' => __( 'Menu Item Font Size (in px)', 'make' ),
					'type'  => ( ttfmake_customizer_supports_panels() ) ? 'number' : 'text',
				),
			),
			'font-family-subnav'         => array(
				'setting' => array(
					'sanitize_callback' => 'ttfmake_sanitize_font_choice',
				),
				'control' => array(
					'label'   => __( 'Sub-Menu Item Font Family', 'make' ),
					'type'    => 'select',
					'choices' => ttfmake_font_choices_placeholder(),
				),
			),
			'font-size-subnav'           => array(
				'setting' => array(
					'sanitize_callback' => 'absint',
				),
				'control' => array(
					'label' => __( 'Sub-Menu Item Font Size (in px)', 'make' ),
					'type'  => ( ttfmake_customizer_supports_panels() ) ? 'number' : 'text',
				),
			),
			'font-subnav-option-heading' => array(
				'control' => array(
					'control_type' => 'TTFMAKE_Customize_Misc_Control',
					'type'         => 'heading',
					'label'        => __( 'Sub-Menu Item Options', 'make' ),
				),
			),
			'font-subnav-mobile'         => array(
				'setting' => array(
					'sanitize_callback' => 'absint',
				),
				'control' => array(
					'label' => __( 'Use Menu Item styles in mobile view', 'make' ),
					'type'  => 'checkbox',
				),
			),
		),
	);

	/**
	 * Widgets
	 */
	$typography_sections['font-widget'] = array(
		'panel'   => $panel,
		'title'   => __( 'Widgets', 'make' ),
		'options' => array(
			'font-family-widget' => array(
				'setting' => array(
					'sanitize_callback' => 'ttfmake_sanitize_font_choice',
				),
				'control' => array(
					'label'   => __( 'Widget Font Family', 'make' ),
					'type'    => 'select',
					'choices' => ttfmake_font_choices_placeholder(),
				),
			),
			'font-size-widget'   => array(
				'setting' => array(
					'sanitize_callback' => 'absint',
				),
				'control' => array(
					'label' => __( 'Widget Font Size (in px)', 'make' ),
					'type'  => ( ttfmake_customizer_supports_panels() ) ? 'number' : 'text',
				),
			),
		),
	);

	/**
	 * Headers & Body
	 */
	$typography_sections['font'] = array(
		'panel'   => $panel,
		'title'   => __( 'Headers &amp; Body', 'make' ),
		'options' => array(
			'font-family-h1'   => array(
				'setting' => array(
					'sanitize_callback' => 'ttfmake_sanitize_font_choice',
				),
				'control' => array(
					'label'   => __( 'H1 Font Family', 'make' ),
					'type'    => 'select',
					'choices' => ttfmake_font_choices_placeholder(),
				),
			),
			'font-size-h1'     => array(
				'setting' => array(
					'sanitize_callback' => 'absint',
				),
				'control' => array(
					'label' => __( 'H1 Font Size (in px)', 'make' ),
					'type'  => ( ttfmake_customizer_supports_panels() ) ? 'number' : 'text',
				),
			),
			'font-family-h2'   => array(
				'setting' => array(
					'sanitize_callback' => 'ttfmake_sanitize_font_choice',
				),
				'control' => array(
					'label'   => __( 'H2 Font Family', 'make' ),
					'type'    => 'select',
					'choices' => ttfmake_font_choices_placeholder(),
				),
			),
			'font-size-h2'     => array(
				'setting' => array(
					'sanitize_callback' => 'absint',
				),
				'control' => array(
					'label' => __( 'H2 Font Size (in px)', 'make' ),
					'type'  => ( ttfmake_customizer_supports_panels() ) ? 'number' : 'text',
				),
			),
			'font-family-h3'   => array(
				'setting' => array(
					'sanitize_callback' => 'ttfmake_sanitize_font_choice',
				),
				'control' => array(
					'label'   => __( 'H3 Font Family', 'make' ),
					'type'    => 'select',
					'choices' => ttfmake_font_choices_placeholder(),
				),
			),
			'font-size-h3'     => array(
				'setting' => array(
					'sanitize_callback' => 'absint',
				),
				'control' => array(
					'label' => __( 'H3 Font Size (in px)', 'make' ),
					'type'  => ( ttfmake_customizer_supports_panels() ) ? 'number' : 'text',
				),
			),
			'font-family-h4'   => array(
				'setting' => array(
					'sanitize_callback' => 'ttfmake_sanitize_font_choice',
				),
				'control' => array(
					'label'   => __( 'H4 Font Family', 'make' ),
					'type'    => 'select',
					'choices' => ttfmake_font_choices_placeholder(),
				),
			),
			'font-size-h4'     => array(
				'setting' => array(
					'sanitize_callback' => 'absint',
				),
				'control' => array(
					'label' => __( 'H4 Font Size (in px)', 'make' ),
					'type'  => ( ttfmake_customizer_supports_panels() ) ? 'number' : 'text',
				),
			),
			'font-family-h5'   => array(
				'setting' => array(
					'sanitize_callback' => 'ttfmake_sanitize_font_choice',
				),
				'control' => array(
					'label'   => __( 'H5 Font Family', 'make' ),
					'type'    => 'select',
					'choices' => ttfmake_font_choices_placeholder(),
				),
			),
			'font-size-h5'     => array(
				'setting' => array(
					'sanitize_callback' => 'absint',
				),
				'control' => array(
					'label' => __( 'H5 Font Size (in px)', 'make' ),
					'type'  => ( ttfmake_customizer_supports_panels() ) ? 'number' : 'text',
				),
			),
			'font-family-h6'   => array(
				'setting' => array(
					'sanitize_callback' => 'ttfmake_sanitize_font_choice',
				),
				'control' => array(
					'label'   => __( 'H6 Font Family', 'make' ),
					'type'    => 'select',
					'choices' => ttfmake_font_choices_placeholder(),
				),
			),
			'font-size-h6'     => array(
				'setting' => array(
					'sanitize_callback' => 'absint',
				),
				'control' => array(
					'label' => __( 'H6 Font Size (in px)', 'make' ),
					'type'  => ( ttfmake_customizer_supports_panels() ) ? 'number' : 'text',
				),
			),
			'font-family-body' => array(
				'setting' => array(
					'sanitize_callback' => 'ttfmake_sanitize_font_choice',
				),
				'control' => array(
					'label'   => __( 'Body Font Family', 'make' ),
					'type'    => 'select',
					'choices' => ttfmake_font_choices_placeholder(),
				),
			),
			'font-size-body'   => array(
				'setting' => array(
					'sanitize_callback' => 'absint',
				),
				'control' => array(
					'label' => __( 'Body Font Size (in px)', 'make' ),
					'type'  => ( ttfmake_customizer_supports_panels() ) ? 'number' : 'text',
				),
			),
		),
	);

	/**
	 * Filter the definitions for the controls in the Typography panel of the Customizer.
	 *
	 * @since 1.3.0.
	 *
	 * @param array    $typography_sections    The array of definitions.
	 */
	$typography_sections = apply_filters( 'make_customizer_typography_sections', $typography_sections );

	// Merge with master array
	return array_merge( $sections, $typography_sections );
}
endif;

add_filter( 'make_customizer_sections', 'ttfmake_customizer_define_typography_sections' );