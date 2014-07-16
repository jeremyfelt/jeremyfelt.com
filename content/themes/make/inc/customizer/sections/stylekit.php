<?php

if ( ! function_exists( 'ttfmake_customizer_stylekit' ) ) :
/**
 * Configure settings and controls for the Kits section.
 *
 * @since  1.0.3.
 *
 * @param  object    $wp_customize    The global customizer object.
 * @param  string    $section         The section name.
 * @return void
 */
function ttfmake_customizer_stylekit( $wp_customize, $section ) {
	$priority       = new TTFMAKE_Prioritizer();
	$control_prefix = 'ttfmake_';
	$setting_prefix = str_replace( $control_prefix, '', $section );

	// Style Kits info
	$setting_id = $setting_prefix . '-info';
	$wp_customize->add_control(
		new TTFMAKE_Customize_Misc_Control(
			$wp_customize,
			$control_prefix . $setting_id,
			array(
				'section'     => $section,
				'type'        => 'text',
				'description' => sprintf(
					__( '%s to quickly apply designer-picked style choices (fonts, layout, colors) to your website.', 'make' ),
					sprintf(
						'<a href="%1$s" target="_blank">%2$s</a>',
						esc_url( ttfmake_get_plus_link( 'style-kits' ) ),
						__( 'Upgrade to Make Plus', 'make' )
					)
				),
				'priority'    => $priority->add()
			)
		)
	);

	// Style Kits heading
	$setting_id = $setting_prefix . '-heading';
	$wp_customize->add_control(
		new TTFMAKE_Customize_Misc_Control(
			$wp_customize,
			$control_prefix . $setting_id,
			array(
				'section'     => $section,
				'type'        => 'heading',
				'label' => __( 'Kits', 'make' ),
				'priority'    => $priority->add()
			)
		)
	);

	// Style Kits dropdown
	$setting_id = $setting_prefix . '-dropdown';
	$wp_customize->add_control(
		new TTFMAKE_Customize_Misc_Control(
			$wp_customize,
			$control_prefix . $setting_id,
			array(
				'section'     => $section,
				'type'        => 'text',
				'description' => '
					<select>
						<option selected="selected" disabled="disabled">--- ' . __( "Choose a kit", "make" ) . ' ---</option>
						<option disabled="disabled">' . __( "Light", "make" ) . '</option>
						<option disabled="disabled">' . __( "Dark", "make" ) . '</option>
						<option disabled="disabled">' . __( "Modern", "make" ) . '</option>
						<option disabled="disabled">' . __( "Creative", "make" ) . '</option>
						<option disabled="disabled">' . __( "Vintage", "make" ) . '</option>
					</select>
				',
				'priority'    => $priority->add()
			)
		)
	);
}
endif;