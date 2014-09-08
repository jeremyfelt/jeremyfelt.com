<?php

if ( ! function_exists( 'ttfmake_customizer_stylekit' ) ) :
/**
 * Filter to add a new Customizer section
 *
 * This function takes the main array of Customizer sections and adds a new one
 * right before the first panel.
 *
 * @since  1.3.0.
 *
 * @param  array    $sections    The array of sections to add to the Customizer.
 * @return array                 The modified array of sections.
 */
function ttfmake_customizer_stylekit( $sections ) {
	global $wp_customize;
	$theme_prefix = 'ttfmake_';

	// Get priority of General panel
	$general_priority = $wp_customize->get_panel( $theme_prefix . 'general' )->priority;

	$sections['stylekit'] = array(
		'title' => __( 'Style Kits', 'make' ),
		'description' => sprintf(
			__( '%s to quickly apply designer-picked style choices (fonts, layout, colors) to your website.', 'make' ),
			sprintf(
				'<a href="%1$s" target="_blank">%2$s</a>',
				esc_url( ttfmake_get_plus_link( 'style-kits' ) ),
				__( 'Upgrade to Make Plus', 'make' )
			)
		),
		'priority' => $general_priority - 10,
		'options' => array(
			'stylekit-heading' => array(
				'control' => array(
					'control_type'		=> 'TTFMAKE_Customize_Misc_Control',
					'label'				=> __( 'Kits', 'make-plus' ),
					'type'				=> 'heading',
				),
			),
			'stylekit-dropdown' => array(
				'control' => array(
					'control_type'		=> 'TTFMAKE_Customize_Misc_Control',
					'type'				=> 'text',
					'description'		=> '
						<select>
							<option selected="selected" disabled="disabled">--- ' . __( "Choose a kit", "make" ) . ' ---</option>
							<option disabled="disabled">' . __( "Light", "make" ) . '</option>
							<option disabled="disabled">' . __( "Dark", "make" ) . '</option>
							<option disabled="disabled">' . __( "Modern", "make" ) . '</option>
							<option disabled="disabled">' . __( "Creative", "make" ) . '</option>
							<option disabled="disabled">' . __( "Vintage", "make" ) . '</option>
						</select>
					',
				),
			),
		),
	);

	return $sections;
}
endif;

add_filter( 'make_customizer_sections', 'ttfmake_customizer_stylekit' );