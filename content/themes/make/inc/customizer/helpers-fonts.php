<?php
/**
 * @package Make
 */

if ( ! function_exists( 'ttfmake_font_get_relative_sizes' ) ) :
/**
 * Return an array of percentages to use when calculating certain font sizes.
 *
 * @since  1.3.0.
 *
 * @return array    The percentage value relative to another specific size
 */
function ttfmake_font_get_relative_sizes() {
	/**
	 * Filter the array of relative font sizes.
	 *
	 * Each array item defines a percentage by which to scale a font size compared
	 * to some other font size. Most of these were deprecated in version 1.3.0.
	 *
	 * @since 1.0.0.
	 *
	 * @param array    $sizes    The array of relative sizes.
	 */
	return apply_filters( 'make_font_relative_size', array(
		// Relative to navigation font size
		'sub-menu'     => 93,  // Deprecated in 1.3.0.
		// Relative to header font size
		'h1'           => 100, // Deprecated in 1.3.0.
		'h2'           => 74,  // Deprecated in 1.3.0.
		'h3'           => 52,  // Deprecated in 1.3.0.
		'h4'           => 52,  // Deprecated in 1.3.0.
		'h5'           => 35,  // Deprecated in 1.3.0.
		'h6'           => 30,  // Deprecated in 1.3.0.
		'post-title'   => 74,
		// Relative to widget font size
		'widget-title' => 100,
		// Relative to body font size
		'comments'     => 88,
		'comment-date' => 82,
	) );
}
endif;

if ( ! function_exists( 'ttfmake_css_fonts' ) ) :
/**
 * Build the CSS rules for the custom fonts
 *
 * @since  1.0.0
 *
 * @return void
 */
function ttfmake_css_fonts() {
	// Use legacy function instead if no panel support
	if ( ! ttfmake_customizer_supports_panels() ) {
		ttfmake_css_legacy_fonts();
		return;
	}

	// Get relative sizes
	$percent = ttfmake_font_get_relative_sizes();

	/**
	 * Site Title
	 */
	$element = 'site-title';
	$selectors = array( '.site-title', '.font-site-title' );
	$declarations = ttfmake_parse_font_properties( $element );
	if ( ! empty( $declarations ) ) {
		ttfmake_get_css()->add( array( 'selectors' => $selectors, 'declarations' => $declarations, ) );
	}

	/**
	 * Site Tagline
	 */
	$element = 'site-tagline';
	$selectors = array( '.site-description', '.font-site-tagline' );
	$declarations = ttfmake_parse_font_properties( $element );
	if ( ! empty( $declarations ) ) {
		ttfmake_get_css()->add( array( 'selectors' => $selectors, 'declarations' => $declarations, ) );
	}

	/**
	 * Menu Item
	 */
	$element = 'nav';
	$selectors = array( '.site-navigation .menu li a', '.font-nav' );
	$declarations = ttfmake_parse_font_properties( $element );
	if ( ! empty( $declarations ) ) {
		ttfmake_get_css()->add( array( 'selectors' => $selectors, 'declarations' => $declarations, ) );
	}
	// Grandchild arrow position
	if ( isset( $declarations['font-size-px'] ) ) {
		ttfmake_get_css()->add( array(
			'selectors'    => array(
				'.site-navigation .menu .sub-menu .menu-item-has-children a:after',
				'.site-navigation .menu .children .menu-item-has-children a:after'
			),
			'declarations' => array(
				'top' => ( $declarations['font-size-px'] * 1.4 / 2 ) - 5 . 'px'
			),
			'media'        => 'screen and (min-width: 800px)'
		) );
	}

	/**
	 * Sub-Menu Item
	 */
	$element = 'subnav';
	$selectors = array( '.site-navigation .menu .sub-menu li a', '.site-navigation .menu .children li a' );
	$simplify_mobile = (bool) get_theme_mod( 'font-' . $element . '-mobile', ttfmake_get_default( 'font-' . $element . '-mobile' ) );
	if ( ! $simplify_mobile ) {
		$subnav_family = get_theme_mod( 'font-family-' . $element, ttfmake_get_default( 'font-family-' . $element ) );
		$subnav_size   = get_theme_mod( 'font-size-' . $element, ttfmake_get_default( 'font-size-' . $element ) );
		$declarations = array(
			'font-family'	=> ttfmake_get_font_stack( $subnav_family ),
			'font-size-px'	=> absint( $subnav_size ) . 'px',
			'font-size-rem'	=> ttfmake_convert_px_to_rem( $subnav_size ),
		);
		$media = 'all';
	} else {
		$declarations = ttfmake_parse_font_properties( $element );
		$media = 'screen and (min-width: 800px)';
	}
	if ( ! empty( $declarations ) ) {
		ttfmake_get_css()->add( array( 'selectors' => $selectors, 'declarations' => $declarations, 'media' => $media ) );
	}

	/**
	 * H1
	 */
	$element = 'h1';
	$selectors = array( 'h1', '.font-header' );
	$declarations = ttfmake_parse_font_properties( $element );
	if ( ! empty( $declarations ) ) {
		ttfmake_get_css()->add( array( 'selectors' => $selectors, 'declarations' => $declarations, ) );
	}

	/**
	 * H2
	 */
	$element = 'h2';
	$selectors = array( 'h2' );
	$declarations = ttfmake_parse_font_properties( $element );
	if ( ! empty( $declarations ) ) {
		ttfmake_get_css()->add( array( 'selectors' => $selectors, 'declarations' => $declarations, ) );
	}
	// Post title with two sidebars
	if ( isset( $declarations['font-size-px'] ) ) {
		ttfmake_get_css()->add( array(
			'selectors'    => array( '.has-left-sidebar.has-right-sidebar .entry-title' ),
			'declarations' => array(
				'font-size-px'  => ttfmake_get_relative_font_size( $declarations['font-size-px'], $percent[ 'post-title' ] ) . 'px',
				'font-size-rem' => ttfmake_convert_px_to_rem( ttfmake_get_relative_font_size( $declarations['font-size-px'], $percent[ 'post-title' ] ) ) . 'rem'
			),
			'media'        => 'screen and (min-width: 800px)'
		) );
	}

	/**
	 * H3
	 */
	$element = 'h3';
	$selectors = array( 'h3', '.builder-text-content .widget-title' );
	$declarations = ttfmake_parse_font_properties( $element );
	if ( ! empty( $declarations ) ) {
		ttfmake_get_css()->add( array( 'selectors' => $selectors, 'declarations' => $declarations, ) );
	}

	/**
	 * H4
	 */
	$element = 'h4';
	$selectors = array( 'h4' );
	$declarations = ttfmake_parse_font_properties( $element );
	if ( ! empty( $declarations ) ) {
		ttfmake_get_css()->add( array( 'selectors' => $selectors, 'declarations' => $declarations, ) );
	}

	/**
	 * H5
	 */
	$element = 'h5';
	$selectors = array( 'h5' );
	$declarations = ttfmake_parse_font_properties( $element );
	if ( ! empty( $declarations ) ) {
		ttfmake_get_css()->add( array( 'selectors' => $selectors, 'declarations' => $declarations, ) );
	}

	/**
	 * H6
	 */
	$element = 'h6';
	$selectors = array( 'h6' );
	$declarations = ttfmake_parse_font_properties( $element );
	if ( ! empty( $declarations ) ) {
		ttfmake_get_css()->add( array( 'selectors' => $selectors, 'declarations' => $declarations, ) );
	}

	/**
	 * Widgets
	 */
	$element = 'widget';
	$selectors = array( '.widget', '.font-widget' );
	$declarations = ttfmake_parse_font_properties( $element );
	if ( ! empty( $declarations ) ) {
		ttfmake_get_css()->add( array( 'selectors' => $selectors, 'declarations' => $declarations, ) );
	}
	// Widget title
	if ( isset( $declarations['font-size-px'] ) ) {
		ttfmake_get_css()->add( array(
			'selectors'    => array( '.widget-title' ),
			'declarations' => array(
				'font-size-px'  => ttfmake_get_relative_font_size( $declarations['font-size-px'], $percent[ 'widget-title' ] ) . 'px',
				'font-size-rem' => ttfmake_convert_px_to_rem( ttfmake_get_relative_font_size( $declarations['font-size-px'], $percent[ 'widget-title' ] ) ) . 'rem'
			)
		) );
	}

	/**
	 * Body
	 */
	$element = 'body';
	$selectors = array( 'body', '.font-body' );
	$declarations = ttfmake_parse_font_properties( $element );
	if ( ! empty( $declarations ) ) {
		ttfmake_get_css()->add( array( 'selectors' => $selectors, 'declarations' => $declarations, ) );
	}
	// Comments
	if ( isset( $declarations['font-size-px'] ) ) {
		ttfmake_get_css()->add( array(
			'selectors'    => array( '#comments' ),
			'declarations' => array(
				'font-size-px'  => ttfmake_get_relative_font_size( $declarations['font-size-px'], $percent[ 'comments' ] ) . 'px',
				'font-size-rem' => ttfmake_convert_px_to_rem( ttfmake_get_relative_font_size( $declarations['font-size-px'], $percent[ 'comments' ] ) ) . 'rem'
			)
		) );
		// Comment date
		ttfmake_get_css()->add( array(
			'selectors'    => array( '.comment-date' ),
			'declarations' => array(
				'font-size-px'  => ttfmake_get_relative_font_size( $declarations['font-size-px'], $percent[ 'comment-date' ] ) . 'px',
				'font-size-rem' => ttfmake_convert_px_to_rem( ttfmake_get_relative_font_size( $declarations['font-size-px'], $percent[ 'comment-date' ] ) ) . 'rem'
			)
		) );
	}
}
endif;

add_action( 'make_css', 'ttfmake_css_fonts' );

if ( ! function_exists( 'ttfmake_parse_font_properties' ) ) :
/**
 * Cycle through the font options for the given element and collect an array
 * of option values that are non-default.
 *
 * @since  1.3.0.
 *
 * @param  string    $element    The element to parse the options for.
 * @return array                 An array of non-default CSS declarations.
 */
function ttfmake_parse_font_properties( $element ) {
	/**
	 * Filter the array of customizable font properties and their sanitization callbacks.
	 *
	 * css_property => sanitize_callback
	 *
	 * @since 1.3.0.
	 *
	 * @param array    $properties    The array of font properties and callbacks.
	 */
	$properties = apply_filters( 'make_css_font_properties', array(
		'font-family'	=> 'ttfmake_get_font_stack',
		'font-size'		=> 'absint',
	), $element );

	$declarations = array();
	foreach ( $properties as $property => $callback ) {
		$value = get_theme_mod( $property . '-' . $element, ttfmake_get_default( $property . '-' . $element ) );
		if ( false !== $value && $value !== ttfmake_get_default( $property . '-' . $element ) ) {
			$sanitized_value = call_user_func_array( $callback, array( $value ) );
			if ( 'font-size' === $property ) {
				$declarations[$property . '-px'] = $sanitized_value . 'px';
				$declarations[$property . '-rem'] = ttfmake_convert_px_to_rem( $sanitized_value ) . 'rem';
			} else {
				$declarations[$property] = $sanitized_value;
			}
		}
	}

	return $declarations;
}
endif;

if ( ! function_exists( 'ttfmake_get_font_stack' ) ) :
/**
 * Validate the font choice and get a font stack for it.
 *
 * @since  1.0.0.
 *
 * @param  string    $font    The 1st font in the stack.
 * @return string             The full font stack.
 */
function ttfmake_get_font_stack( $font ) {
	$all_fonts = ttfmake_get_all_fonts();

	// Sanitize font choice
	$font = ttfmake_sanitize_font_choice( $font );

	// Standard font
	if ( isset( $all_fonts[ $font ]['stack'] ) && ! empty( $all_fonts[ $font ]['stack'] ) ) {
		$stack = $all_fonts[ $font ]['stack'];
	} elseif ( in_array( $font, ttfmake_all_font_choices() ) ) {
		$stack = '"' . $font . '","Helvetica Neue",Helvetica,Arial,sans-serif';
	} else {
		$stack = '"Helvetica Neue",Helvetica,Arial,sans-serif';
	}

	/**
	 * Allow developers to filter the full font stack.
	 *
	 * @since 1.2.3.
	 *
	 * @param string    $stack    The font stack.
	 * @param string    $font     The font.
	 */
	return apply_filters( 'make_font_stack', $stack, $font );
}
endif;

if ( ! function_exists( 'ttfmake_get_relative_font_size' ) ) :
/**
 * Convert a font size to a relative size based on a starting value and percentage.
 *
 * @since  1.0.0.
 *
 * @param  mixed    $value         The value to base the final value on.
 * @param  mixed    $percentage    The percentage of change.
 * @return float                   The converted value.
 */
function ttfmake_get_relative_font_size( $value, $percentage ) {
	return round( (float) $value * ( $percentage / 100 ) );
}
endif;

if ( ! function_exists( 'ttfmake_convert_px_to_rem' ) ) :
/**
 * Given a px value, return a rem value.
 *
 * @since  1.0.0.
 *
 * @param  mixed    $px      The value to convert.
 * @param  mixed    $base    The font-size base for the rem conversion (deprecated).
 * @return float             The converted value.
 */
function ttfmake_convert_px_to_rem( $px, $base = 0 ) {
	return (float) $px / 10;
}
endif;

if ( ! function_exists( 'ttfmake_get_font_property_option_keys' ) ) :
/**
 * Return all the option keys for the specified font property.
 *
 * @since  1.3.0.
 *
 * @param  string    $property    The font property to search for.
 * @return array                  Array of matching font option keys.
 */
function ttfmake_get_font_property_option_keys( $property ) {
	$all_keys = array_keys( ttfmake_option_defaults() );

	$font_keys = array();
	foreach ( $all_keys as $key ) {
		if ( preg_match( '/^font-' . $property . '-/', $key ) ) {
			$font_keys[] = $key;
		}
	}

	return $font_keys;
}
endif;

if ( ! function_exists( 'ttfmake_get_google_font_uri' ) ) :
/**
 * Build the HTTP request URL for Google Fonts.
 *
 * @since  1.0.0.
 *
 * @return string    The URL for including Google Fonts.
 */
function ttfmake_get_google_font_uri() {
	// Grab the font choices
	if ( ttfmake_customizer_supports_panels() ) {
		$font_keys = array(
			'font-family-site-title',
			'font-family-site-tagline',
			'font-family-nav',
			'font-family-subnav',
			'font-family-widget',
			'font-family-h1',
			'font-family-h2',
			'font-family-h3',
			'font-family-h4',
			'font-family-h5',
			'font-family-h6',
			'font-family-body',
		);
	} else {
		$font_keys = array(
			'font-site-title',
			'font-header',
			'font-body',
		);
	}
	$fonts = array();
	foreach ( $font_keys as $key ) {
		$fonts[] = get_theme_mod( $key, ttfmake_get_default( $key ) );
	}

	// De-dupe the fonts
	$fonts         = array_unique( $fonts );
	$allowed_fonts = ttfmake_get_google_fonts();
	$family        = array();

	// Validate each font and convert to URL format
	foreach ( $fonts as $font ) {
		$font = trim( $font );

		// Verify that the font exists
		if ( array_key_exists( $font, $allowed_fonts ) ) {
			// Build the family name and variant string (e.g., "Open+Sans:regular,italic,700")
			$family[] = urlencode( $font . ':' . join( ',', ttfmake_choose_google_font_variants( $font, $allowed_fonts[ $font ]['variants'] ) ) );
		}
	}

	// Convert from array to string
	if ( empty( $family ) ) {
		return '';
	} else {
		$request = '//fonts.googleapis.com/css?family=' . implode( '|', $family );
	}

	// Load the font subset
	$subset = get_theme_mod( 'font-subset', ttfmake_get_default( 'font-subset' ) );

	if ( 'all' === $subset ) {
		$subsets_available = ttfmake_get_google_font_subsets();

		// Remove the all set
		unset( $subsets_available['all'] );

		// Build the array
		$subsets = array_keys( $subsets_available );
	} else {
		$subsets = array(
			'latin',
			$subset,
		);
	}

	// Append the subset string
	if ( ! empty( $subsets ) ) {
		$request .= urlencode( '&subset=' . join( ',', $subsets ) );
	}

	/**
	 * Filter the Google Fonts URL.
	 *
	 * @since 1.2.3.
	 *
	 * @param string    $url    The URL to retrieve the Google Fonts.
	 */
	return apply_filters( 'make_get_google_font_uri', esc_url( $request ) );
}
endif;

if ( ! function_exists( 'ttfmake_choose_google_font_variants' ) ) :
/**
 * Given a font, chose the variants to load for the theme.
 *
 * Attempts to load regular, italic, and 700. If regular is not found, the first variant in the family is chosen. italic
 * and 700 are only loaded if found. No fallbacks are loaded for those fonts.
 *
 * @since  1.0.0.
 *
 * @param  string    $font        The font to load variants for.
 * @param  array     $variants    The variants for the font.
 * @return array                  The chosen variants.
 */
function ttfmake_choose_google_font_variants( $font, $variants = array() ) {
	$chosen_variants = array();
	if ( empty( $variants ) ) {
		$fonts = ttfmake_get_google_fonts();

		if ( array_key_exists( $font, $fonts ) ) {
			$variants = $fonts[ $font ]['variants'];
		}
	}

	// If a "regular" variant is not found, get the first variant
	if ( ! in_array( 'regular', $variants ) ) {
		$chosen_variants[] = $variants[0];
	} else {
		$chosen_variants[] = 'regular';
	}

	// Only add "italic" if it exists
	if ( in_array( 'italic', $variants ) ) {
		$chosen_variants[] = 'italic';
	}

	// Only add "700" if it exists
	if ( in_array( '700', $variants ) ) {
		$chosen_variants[] = '700';
	}

	/**
	 * Allow developers to alter the font variant choice.
	 *
	 * @since 1.2.3.
	 *
	 * @param array     $variants    The list of variants for a font.
	 * @param string    $font        The font to load variants for.
	 * @param array     $variants    The variants for the font.
	 */
	return apply_filters( 'make_font_variants', array_unique( $chosen_variants ), $font, $variants );
}
endif;

if ( ! function_exists( 'ttfmake_sanitize_font_subset' ) ) :
/**
 * Sanitize the Character Subset choice.
 *
 * @since  1.0.0
 *
 * @param  string    $value    The value to sanitize.
 * @return array               The sanitized value.
 */
function ttfmake_sanitize_font_subset( $value ) {
	if ( ! array_key_exists( $value, ttfmake_get_google_font_subsets() ) ) {
		$value = 'latin';
	}

	/**
	 * Filter the sanitized subset choice.
	 *
	 * @since 1.2.3.
	 *
	 * @param string    $value    The chosen subset value.
	 */
	return apply_filters( 'make_sanitize_font_subset', $value );
}
endif;

if ( ! function_exists( 'ttfmake_get_google_font_subsets' ) ) :
/**
 * Retrieve the list of available Google font subsets.
 *
 * @since  1.0.0.
 *
 * @return array    The available subsets.
 */
function ttfmake_get_google_font_subsets() {
	/**
	 * Filter the list of supported Google Font subsets.
	 *
	 * @since 1.2.3.
	 *
	 * @param array    $subsets    The list of subsets.
	 */
	return apply_filters( 'make_get_google_font_subsets', array(
		'all'          => __( 'All', 'make' ),
		'cyrillic'     => __( 'Cyrillic', 'make' ),
		'cyrillic-ext' => __( 'Cyrillic Extended', 'make' ),
		'devanagari'   => __( 'Devanagari', 'make' ),
		'greek'        => __( 'Greek', 'make' ),
		'greek-ext'    => __( 'Greek Extended', 'make' ),
		'khmer'        => __( 'Khmer', 'make' ),
		'latin'        => __( 'Latin', 'make' ),
		'latin-ext'    => __( 'Latin Extended', 'make' ),
		'telugu'       => __( 'Telugu', 'make' ),
		'vietnamese'   => __( 'Vietnamese', 'make' ),
	) );
}
endif;

if ( ! function_exists( 'ttfmake_sanitize_font_choice' ) ) :
/**
 * Sanitize a font choice.
 *
 * @since  1.0.0.
 *
 * @param  string    $value    The font choice.
 * @return string              The sanitized font choice.
 */
function ttfmake_sanitize_font_choice( $value ) {
	if ( ! is_string( $value ) ) {
		// The array key is not a string, so the chosen option is not a real choice
		return '';
	} else if ( array_key_exists( $value, ttfmake_all_font_choices() ) ) {
		return $value;
	} else {
		return '';
	}

	/**
	 * Filter the sanitized font choice.
	 *
	 * @since 1.2.3.
	 *
	 * @param string    $value    The chosen font value.
	 */
	return apply_filters( 'make_sanitize_font_choice', $return );
}
endif;

if ( ! function_exists( 'ttfmake_font_choices_placeholder' ) ) :
/**
 * Add a placeholder for the large font choices array, which will be loaded
 * in via JavaScript.
 *
 * @since 1.3.0.
 *
 * @return array
 */
function ttfmake_font_choices_placeholder() {
	return array( 'placeholder' => __( 'Loading&hellip;', 'make' ) );
}
endif;

if ( ! function_exists( 'ttfmake_all_font_choices' ) ) :
/**
 * Packages the font choices into value/label pairs for use with the customizer.
 *
 * @since  1.0.0.
 *
 * @return array    The fonts in value/label pairs.
 */
function ttfmake_all_font_choices() {
	$fonts   = ttfmake_get_all_fonts();
	$choices = array();

	// Repackage the fonts into value/label pairs
	foreach ( $fonts as $key => $font ) {
		$choices[ $key ] = $font['label'];
	}

	/**
	 * Allow for developers to modify the full list of fonts.
	 *
	 * @since 1.2.3.
	 *
	 * @param array    $choices    The list of all fonts.
	 */
	return apply_filters( 'make_all_font_choices', $choices );
}
endif;

if ( ! function_exists( 'ttfmake_all_font_choices_js' ) ) :
/**
 * Compile the font choices for better handling as a JSON object
 *
 * @since 1.3.0.
 *
 * @return array
 */
function ttfmake_all_font_choices_js() {
	$fonts   = ttfmake_get_all_fonts();
	$choices = array();

	// Repackage the fonts into value/label pairs
	foreach ( $fonts as $key => $font ) {
		$choices[] = array( 'k' => $key, 'l' => $font['label'] );
	}

	return $choices;
}
endif;

if ( ! function_exists( 'ttfmake_get_all_fonts' ) ) :
/**
 * Compile font options from different sources.
 *
 * @since  1.0.0.
 *
 * @return array    All available fonts.
 */
function ttfmake_get_all_fonts() {
	$heading1       = array( 1 => array( 'label' => sprintf( '--- %s ---', __( 'Standard Fonts', 'make' ) ) ) );
	$standard_fonts = ttfmake_get_standard_fonts();
	$heading2       = array( 2 => array( 'label' => sprintf( '--- %s ---', __( 'Google Fonts', 'make' ) ) ) );
	$google_fonts   = ttfmake_get_google_fonts();

	/**
	 * Allow for developers to modify the full list of fonts.
	 *
	 * @since 1.2.3.
	 *
	 * @param array    $fonts    The list of all fonts.
	 */
	return apply_filters( 'make_all_fonts', array_merge( $heading1, $standard_fonts, $heading2, $google_fonts ) );
}
endif;

if ( ! function_exists( 'ttfmake_get_standard_fonts' ) ) :
/**
 * Return an array of standard websafe fonts.
 *
 * @since  1.0.0.
 *
 * @return array    Standard websafe fonts.
 */
function ttfmake_get_standard_fonts() {
	/**
	 * Allow for developers to modify the standard fonts.
	 *
	 * @since 1.2.3.
	 *
	 * @param array    $fonts    The list of standard fonts.
	 */
	return apply_filters( 'make_get_standard_fonts', array(
		'serif' => array(
			'label' => _x( 'Serif', 'font style', 'make' ),
			'stack' => 'Georgia,Times,"Times New Roman",serif'
		),
		'sans-serif' => array(
			'label' => _x( 'Sans Serif', 'font style', 'make' ),
			'stack' => '"Helvetica Neue",Helvetica,Arial,sans-serif'
		),
		'monospace' => array(
			'label' => _x( 'Monospaced', 'font style', 'make' ),
			'stack' => 'Monaco,"Lucida Sans Typewriter","Lucida Typewriter","Courier New",Courier,monospace'
		)
	) );
}
endif;

if ( ! function_exists( 'ttfmake_css_legacy_fonts' ) ) :
/**
 * Build the CSS rules for the custom fonts
 *
 * @since  1.0.0.
 *
 * @return void
 */
function ttfmake_css_legacy_fonts() {
	/**
	 * Font Families
	 */
	// Get and escape options
	$font_site_title       = get_theme_mod( 'font-site-title', ttfmake_get_default( 'font-site-title' ) );
	$font_site_title_stack = ttfmake_get_font_stack( $font_site_title );
	$font_header           = get_theme_mod( 'font-header', ttfmake_get_default( 'font-header' ) );
	$font_header_stack     = ttfmake_get_font_stack( $font_header );
	$font_body             = get_theme_mod( 'font-body', ttfmake_get_default( 'font-body' ) );
	$font_body_stack       = ttfmake_get_font_stack( $font_body );

	// Site Title Font
	if ( $font_site_title !== ttfmake_get_default( 'font-site-title' ) && '' !== $font_site_title_stack ) {
		ttfmake_get_css()->add( array(
			'selectors'    => array( '.site-title', '.font-site-title' ),
			'declarations' => array(
				'font-family' => $font_site_title_stack
			)
		) );
	}

	// Header Font
	if ( $font_header !== ttfmake_get_default( 'font-header' ) && '' !== $font_header_stack ) {
		ttfmake_get_css()->add( array(
			'selectors'    => array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', '.font-header' ),
			'declarations' => array(
				'font-family' => $font_header_stack
			)
		) );
	}

	// Body Font
	if ( $font_body !== ttfmake_get_default( 'font-body' ) && '' !== $font_body_stack ) {
		ttfmake_get_css()->add( array(
			'selectors'    => array( 'body', '.font-body' ),
			'declarations' => array(
				'font-family' => $font_body_stack
			)
		) );
	}

	/**
	 * Font Sizes
	 */
	// Get and escape options
	$font_site_title_size = absint( get_theme_mod( 'font-site-title-size', ttfmake_get_default( 'font-site-title-size' ) ) );
	$font_site_tagline_size = absint( get_theme_mod( 'font-site-tagline-size', ttfmake_get_default( 'font-site-tagline-size' ) ) );
	$font_nav_size        = absint( get_theme_mod( 'font-nav-size', ttfmake_get_default( 'font-nav-size' ) ) );
	$font_header_size     = absint( get_theme_mod( 'font-header-size', ttfmake_get_default( 'font-header-size' ) ) );
	$font_widget_size     = absint( get_theme_mod( 'font-widget-size', ttfmake_get_default( 'font-widget-size' ) ) );
	$font_body_size       = absint( get_theme_mod( 'font-body-size', ttfmake_get_default( 'font-body-size' ) ) );

	// Relative font sizes
	$percent = ttfmake_font_get_relative_sizes();

	// Site Title Font Size
	if ( $font_site_title_size !== ttfmake_get_default( 'font-site-title-size' ) ) {
		ttfmake_get_css()->add( array(
			'selectors'    => array( '.site-title', '.font-site-title' ),
			'declarations' => array(
				'font-size-px'  => $font_site_title_size . 'px',
				'font-size-rem' => ttfmake_convert_px_to_rem( $font_site_title_size ) . 'rem'
			)
		) );
	}

	// Site Tagline Font Size
	if ( $font_site_tagline_size !== ttfmake_get_default( 'font-site-tagline-size' ) ) {
		ttfmake_get_css()->add( array(
			'selectors'    => array( '.site-description', '.font-site-tagline' ),
			'declarations' => array(
				'font-size-px'  => $font_site_tagline_size . 'px',
				'font-size-rem' => ttfmake_convert_px_to_rem( $font_site_tagline_size ) . 'rem'
			)
		) );
	}

	// Navigation Font Size
	if ( $font_nav_size !== ttfmake_get_default( 'font-nav-size' ) ) {
		// Top level
		ttfmake_get_css()->add( array(
			'selectors'    => array( '.site-navigation .menu li a', '.font-nav' ),
			'declarations' => array(
				'font-size-px'  => $font_nav_size . 'px',
				'font-size-rem' => ttfmake_convert_px_to_rem( $font_nav_size ) . 'rem'
			)
		) );

		// Sub menu items
		ttfmake_get_css()->add( array(
			'selectors'    => array( '.site-navigation .menu .sub-menu li a', '.site-navigation .menu .children li a' ),
			'declarations' => array(
				'font-size-px'  => ttfmake_get_relative_font_size( $font_nav_size, $percent['sub-menu'] ) . 'px',
				'font-size-rem' => ttfmake_convert_px_to_rem( ttfmake_get_relative_font_size( $font_nav_size, $percent['sub-menu'] ) ) . 'rem'
			),
			'media'        => 'screen and (min-width: 800px)'
		) );

		// Grandchild arrow position
		ttfmake_get_css()->add( array(
			'selectors'    => array( '.site-navigation .menu .sub-menu .menu-item-has-children a:after', '.site-navigation .menu .children .menu-item-has-children a:after' ),
			'declarations' => array(
				'top' => ( $font_nav_size * 1.4 / 2 ) - 5 . 'px'
			),
			'media'        => 'screen and (min-width: 800px)'
		) );
	}

	// Header Font Sizes
	if ( $font_header_size !== ttfmake_get_default( 'font-header-size' ) ) {
		// h1
		ttfmake_get_css()->add( array(
			'selectors'    => array( 'h1', '.font-header' ),
			'declarations' => array(
				'font-size-px'  => $font_header_size . 'px',
				'font-size-rem' => ttfmake_convert_px_to_rem( $font_header_size ) . 'rem'
			)
		) );

		// h2
		ttfmake_get_css()->add( array(
			'selectors'    => array( 'h2' ),
			'declarations' => array(
				'font-size-px'  => ttfmake_get_relative_font_size( $font_header_size, $percent[ 'h2' ] ) . 'px',
				'font-size-rem' => ttfmake_convert_px_to_rem( ttfmake_get_relative_font_size( $font_header_size, $percent[ 'h2' ] ) ) . 'rem'
			)
		) );

		// h3
		ttfmake_get_css()->add( array(
			'selectors'    => array( 'h3', '.builder-text-content .widget-title' ),
			'declarations' => array(
				'font-size-px'  => ttfmake_get_relative_font_size( $font_header_size, $percent[ 'h3' ] ) . 'px',
				'font-size-rem' => ttfmake_convert_px_to_rem( ttfmake_get_relative_font_size( $font_header_size, $percent[ 'h3' ] ) ) . 'rem'
			)
		) );

		// h4
		ttfmake_get_css()->add( array(
			'selectors'    => array( 'h4' ),
			'declarations' => array(
				'font-size-px'  => ttfmake_get_relative_font_size( $font_header_size, $percent[ 'h4' ] ) . 'px',
				'font-size-rem' => ttfmake_convert_px_to_rem( ttfmake_get_relative_font_size( $font_header_size, $percent[ 'h4' ] ) ) . 'rem'
			)
		) );

		// h5
		ttfmake_get_css()->add( array(
			'selectors'    => array( 'h5' ),
			'declarations' => array(
				'font-size-px'  => ttfmake_get_relative_font_size( $font_header_size, $percent[ 'h5' ] ) . 'px',
				'font-size-rem' => ttfmake_convert_px_to_rem( ttfmake_get_relative_font_size( $font_header_size, $percent[ 'h5' ] ) ) . 'rem'
			)
		) );

		// h6
		ttfmake_get_css()->add( array(
			'selectors'    => array( 'h6' ),
			'declarations' => array(
				'font-size-px'  => ttfmake_get_relative_font_size( $font_header_size, $percent[ 'h6' ] ) . 'px',
				'font-size-rem' => ttfmake_convert_px_to_rem( ttfmake_get_relative_font_size( $font_header_size, $percent[ 'h6' ] ) ) . 'rem'
			)
		) );

		// Post title with two sidebars
		ttfmake_get_css()->add( array(
			'selectors'    => array( '.has-left-sidebar.has-right-sidebar .entry-title' ),
			'declarations' => array(
				'font-size-px'  => ttfmake_get_relative_font_size( $font_header_size, $percent[ 'post-title' ] ) . 'px',
				'font-size-rem' => ttfmake_convert_px_to_rem( ttfmake_get_relative_font_size( $font_header_size, $percent[ 'post-title' ] ) ) . 'rem'
			),
			'media'        => 'screen and (min-width: 800px)'
		) );
	}

	// Widget Font Size
	if ( $font_widget_size !== ttfmake_get_default( 'font-widget-size' ) ) {
		// Widget body
		ttfmake_get_css()->add( array(
			'selectors'    => array( '.widget', '.font-widget' ),
			'declarations' => array(
				'font-size-px'  => $font_widget_size . 'px',
				'font-size-rem' => ttfmake_convert_px_to_rem( $font_widget_size ) . 'rem'
			)
		) );

		// Widget title
		ttfmake_get_css()->add( array(
			'selectors'    => array( '.widget-title' ),
			'declarations' => array(
				'font-size-px'  => ttfmake_get_relative_font_size( $font_widget_size, $percent[ 'widget-title' ] ) . 'px',
				'font-size-rem' => ttfmake_convert_px_to_rem( ttfmake_get_relative_font_size( $font_widget_size, $percent[ 'widget-title' ] ) ) . 'rem'
			)
		) );
	}

	// Body Font Size
	if ( $font_body_size !== ttfmake_get_default( 'font-body-size' ) ) {
		// body
		ttfmake_get_css()->add( array(
			'selectors'    => array( 'body', '.font-body', '.builder-text-content .widget' ),
			'declarations' => array(
				'font-size-px'  => $font_body_size . 'px',
				'font-size-rem' => ttfmake_convert_px_to_rem( $font_body_size ) . 'rem'
			)
		) );

		// Comments
		ttfmake_get_css()->add( array(
			'selectors'    => array( '#comments' ),
			'declarations' => array(
				'font-size-px'  => ttfmake_get_relative_font_size( $font_body_size, $percent[ 'comments' ] ) . 'px',
				'font-size-rem' => ttfmake_convert_px_to_rem( ttfmake_get_relative_font_size( $font_body_size, $percent[ 'comments' ] ) ) . 'rem'
			)
		) );

		// Comment date
		ttfmake_get_css()->add( array(
			'selectors'    => array( '.comment-date' ),
			'declarations' => array(
				'font-size-px'  => ttfmake_get_relative_font_size( $font_body_size, $percent[ 'comment-date' ] ) . 'px',
				'font-size-rem' => ttfmake_convert_px_to_rem( ttfmake_get_relative_font_size( $font_body_size, $percent[ 'comment-date' ] ) ) . 'rem'
			)
		) );
	}
}
endif;
