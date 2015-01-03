<?php
/**
 * @package Make
 */

if ( ! function_exists( 'ttfmake_customizer_init' ) ) :
/**
 * Load the customizer files and enqueue scripts
 *
 * @since  1.0.0.
 *
 * @return void
 */
function ttfmake_customizer_init() {
	$path = get_template_directory() . '/inc/customizer/';

	// Always load
	require_once( $path . 'controls.php' );
	require_once( $path . 'google-fonts.php' );
	require_once( $path . 'helpers.php' );
	require_once( $path . 'helpers-css.php' );
	require_once( $path . 'helpers-defaults.php' );
	require_once( $path . 'helpers-display.php' );
	require_once( $path . 'helpers-fonts.php' );
	require_once( $path . 'helpers-logo.php' );

	// Hook up functions
	add_action( 'customize_register', 'ttfmake_customizer_add_panels' );
	add_action( 'customize_register', 'ttfmake_customizer_add_sections' );
	add_action( 'customize_register', 'ttfmake_customizer_set_transport' );
	add_action( 'customize_preview_init', 'ttfmake_customizer_preview_script' );
	add_action( 'customize_preview_init', 'ttfmake_add_customizations' );
	add_action( 'customize_controls_enqueue_scripts', 'ttfmake_customizer_sections_script' );
	add_action( 'customize_controls_print_styles', 'ttfmake_customizer_admin_styles' );
}
endif;

add_action( 'after_setup_theme', 'ttfmake_customizer_init' );

if ( ! function_exists( 'ttfmake_customizer_supports_panels' ) ) :
/**
 * Detect support for Customizer panels.
 *
 * This feature was introduced in WP 4.0. The WP_Customize_Manager class is not loaded
 * outside of the Customizer, so this also looks for wp_validate_boolean(), another
 * function added in WP 4.0.
 *
 * @since  1.3.0.
 *
 * @return bool    Whether or not panels are supported.
 */
function ttfmake_customizer_supports_panels() {
	return ( class_exists( 'WP_Customize_Manager' ) && method_exists( 'WP_Customize_Manager', 'add_panel' ) ) || function_exists( 'wp_validate_boolean' );
}
endif;

if ( ! function_exists( 'ttfmake_customizer_get_panels' ) ) :
/**
 * Return an array of panel definitions.
 *
 * @since  1.3.0.
 *
 * @return array    The array of panel definitions.
 */
function ttfmake_customizer_get_panels() {
	$panels = array(
		'general'        => array( 'title' => __( 'General', 'make' ), 'priority' => 100 ),
		'typography'     => array( 'title' => __( 'Typography', 'make' ), 'priority' => 200 ),
		'color-scheme'   => array( 'title' => __( 'Color Scheme', 'make' ), 'priority' => 300 ),
		'header'         => array( 'title' => __( 'Header', 'make' ), 'priority' => 400 ),
		'content-layout' => array( 'title' => __( 'Content & Layout', 'make' ), 'priority' => 500 ),
		'footer'         => array( 'title' => __( 'Footer', 'make' ), 'priority' => 600 ),
	);

	/**
	 * Filter the array of panel definitions for the Customizer.
	 *
	 * @since 1.3.0.
	 *
	 * @param array    $panels    The array of panel definitions.
	 */
	return apply_filters( 'make_customizer_panels', $panels );
}
endif;

if ( ! function_exists( 'ttfmake_customizer_add_panels' ) ) :
/**
 * Register Customizer panels
 *
 * @since  1.3.0.
 *
 * @param  WP_Customize_Manager    $wp_customize    Customizer object.
 * @return void
 */
function ttfmake_customizer_add_panels( $wp_customize ) {
	// Panels are only available in WP 4.0+
	if ( ttfmake_customizer_supports_panels() ) {
		$priority = new TTFMAKE_Prioritizer( 1000, 100 );
		$theme_prefix = 'ttfmake_';

		// Get panel definitions
		$panels = ttfmake_customizer_get_panels();

		// Add panels
		foreach ( $panels as $panel => $data ) {
			// Determine priority
			if ( ! isset( $data['priority'] ) ) {
				$data['priority'] = $priority->add();
			}

			// Add panel
			$wp_customize->add_panel( $theme_prefix . $panel, $data );
		}

		// Re-prioritize and rename the Widgets panel
		if ( ! isset( $wp_customize->get_panel( 'widgets' )->priority ) ) {
			$wp_customize->add_panel( 'widgets' );
		}
		$wp_customize->get_panel( 'widgets' )->priority = $priority->add();
		$wp_customize->get_panel( 'widgets' )->title = __( 'Sidebars & Widgets', 'make' );
	}
}
endif;

if ( ! function_exists( 'ttfmake_customizer_get_sections' ) ) :
/**
 * Return the master array of Customizer sections
 *
 * @since  1.3.0.
 *
 * @return array    The master array of Customizer sections
 */
function ttfmake_customizer_get_sections() {
	/**
	 * Filter the array of section definitions for the Customizer.
	 *
	 * This filter is used to compile a master array of section definitions for each
	 * panel in the Customizer.
	 *
	 * @since 1.3.0.
	 *
	 * @param array    $sections    The array of section definitions.
	 */
	return apply_filters( 'make_customizer_sections', array() );
}
endif;

if ( ! function_exists( 'ttfmake_customizer_add_sections' ) ) :
/**
 * Add sections and controls to the customizer.
 *
 * Hooked to 'customize_register' via ttfmake_customizer_init().
 *
 * @since  1.0.0.
 *
 * @param  WP_Customize_Manager    $wp_customize    Theme Customizer object.
 * @return void
 */
function ttfmake_customizer_add_sections( $wp_customize ) {
	// Use legacy sections instead if no panel support
	if ( ! ttfmake_customizer_supports_panels() ) {
		ttfmake_customizer_add_legacy_sections( $wp_customize );
		return;
	}

	$theme_prefix = 'ttfmake_';
	$default_path = get_template_directory() . '/inc/customizer/sections';
	$panels       = ttfmake_customizer_get_panels();

	// Load built-in section mods
	$builtin_mods = array(
		'background',
		'navigation',
		'site-title-tagline',
		'static-front-page',
	);
	if ( ! ttfmake_is_plus() ) {
		$builtin_mods[] = 'stylekit';
	}

	foreach ( $builtin_mods as $slug ) {
		$file = trailingslashit( $default_path ) . $slug . '.php';
		if ( file_exists( $file ) ) {
			require_once( $file );
		}
	}

	// Load section definition files
	foreach ( $panels as $panel => $data ) {
		if ( ! isset( $data['path'] ) ) {
			$data['path'] = $default_path;
		}

		$file = trailingslashit( $data['path'] ) . $panel . '.php';

		if ( file_exists( $file ) ) {
			require_once( $file );
		}
	}

	// Compile the section definitions
	$sections = ttfmake_customizer_get_sections();

	// Register each section and add its options
	$priority = array();
	foreach ( $sections as $section => $data ) {
		// Get the non-prefixed ID of the current section's panel
		$panel = ( isset( $data['panel'] ) ) ? str_replace( $theme_prefix, '', $data['panel'] ) : 'none';

		// Store the options
		if ( isset( $data['options'] ) ) {
			$options = $data['options'];
			unset( $data['options'] );
		}

		// Determine the priority
		if ( ! isset( $data['priority'] ) ) {
			$panel_priority = ( 'none' !== $panel && isset( $panels[ $panel ]['priority'] ) ) ? $panels[ $panel ]['priority'] : 1000;

			// Create a separate priority counter for each panel, and one for sections without a panel
			if ( ! isset( $priority[ $panel ] ) ) {
				$priority[ $panel ] = new TTFMAKE_Prioritizer( $panel_priority, 10 );
			}

			$data['priority'] = $priority[ $panel ]->add();
		}

		// Adjust section title if no panel support
		if ( ! ttfmake_customizer_supports_panels() && isset( $data['panel'] ) ) {
			$existing_title = ( isset( $data['title'] ) ) ? $data['title'] : ucfirst( $section );
			$panel_prefix = ( isset( $panels[ $panel ]['title'] ) ) ? $panels[ $panel ]['title'] . ': ' : '';
			$data['title'] = $panel_prefix . $existing_title;
		}

		// Register section
		$wp_customize->add_section( $theme_prefix . $section, $data );

		// Back compatibility
		if ( isset( $data['path'] ) ) {
			$file = trailingslashit( $data[ 'path' ] ) . $section . '.php';
			if ( file_exists( $file ) ) {
				// First load the file
				require_once( $file );

				// Then add the section
				$section_callback = 'ttfmake_customizer_';
				$section_callback .= ( strpos( $section, '-' ) ) ? str_replace( '-', '_', $section ) : $section;
				if ( function_exists( $section_callback ) ) {
					// Callback to populate the section
					call_user_func_array(
						$section_callback,
						array(
							$wp_customize,
							$theme_prefix . $section,
						)
					);
				}
			}
		}

		// Add options to the section
		if ( isset( $options ) ) {
			ttfmake_customizer_add_section_options( $theme_prefix . $section, $options );
			unset( $options );
		}
	}
}
endif;

if ( ! function_exists( 'ttfmake_customizer_add_section_options' ) ) :
/**
 * Register settings and controls for a section.
 *
 * @since  1.3.0.
 *
 * @param  string    $section             Section ID
 * @param  array     $args                Array of setting and control definitions
 * @param  int       $initial_priority    The initial priority to use for controls
 * @return int                            The last priority value assigned
 */
function ttfmake_customizer_add_section_options( $section, $args, $initial_priority = 100 ) {
	global $wp_customize;

	$priority = new TTFMAKE_Prioritizer( $initial_priority, 5 );
	$theme_prefix = 'ttfmake_';

	foreach ( $args as $setting_id => $option ) {
		// Add setting
		if ( isset( $option['setting'] ) ) {
			$defaults = array(
				'type'                 => 'theme_mod',
				'capability'           => 'edit_theme_options',
				'theme_supports'       => '',
				'default'              => ttfmake_get_default( $setting_id ),
				'transport'            => 'refresh',
				'sanitize_callback'    => '',
				'sanitize_js_callback' => '',
			);
			$setting = wp_parse_args( $option['setting'], $defaults );

			// Add the setting arguments inline so Theme Check can verify the presence of sanitize_callback
			$wp_customize->add_setting( $setting_id, array(
				'type'                 => $setting['type'],
				'capability'           => $setting['capability'],
				'theme_supports'       => $setting['theme_supports'],
				'default'              => $setting['default'],
				'transport'            => $setting['transport'],
				'sanitize_callback'    => $setting['sanitize_callback'],
				'sanitize_js_callback' => $setting['sanitize_js_callback'],
			) );
		}

		// Add control
		if ( isset( $option['control'] ) ) {
			$control_id = $theme_prefix . $setting_id;

			$defaults = array(
				'settings' => $setting_id,
				'section'  => $section,
				'priority' => $priority->add(),
			);

			if ( ! isset( $option['setting'] ) ) {
				unset( $defaults['settings'] );
			}

			$control = wp_parse_args( $option['control'], $defaults );

			// Check for a specialized control class
			if ( isset( $control['control_type'] ) ) {
				$class = $control['control_type'];

				if ( class_exists( $class ) ) {
					unset( $control['control_type'] );

					// Dynamically generate a new class instance
					$reflection = new ReflectionClass( $class );
					$class_instance = $reflection->newInstanceArgs( array( $wp_customize, $control_id, $control ) );

					$wp_customize->add_control( $class_instance );
				}
			} else {
				$wp_customize->add_control( $control_id, $control );
			}
		}
	}

	return $priority->get();
}
endif;

if ( ! function_exists( 'ttfmake_customizer_add_legacy_sections' ) ) :
/**
 * Add the old sections and controls to the customizer for WP installations with no panel support.
 *
 * Hooked to 'customize_register' via ttfmake_customizer_init().
 *
 * @since  1.3.0.
 *
 * @param  WP_Customize_Manager    $wp_customize    Theme Customizer object.
 * @return void
 */
function ttfmake_customizer_add_legacy_sections( $wp_customize ) {
	$path         = get_template_directory() . '/inc/customizer/';
	$section_path = $path . 'legacy_sections/';

	// Get the custom controls
	require_once( $path . 'controls.php' );

	// Modifications for existing sections
	require_once( $section_path . 'background.php' );
	require_once( $section_path . 'navigation.php' );
	require_once( $section_path . 'site-title-tagline.php' );

	// List of new sections to add
	$sections = array(
		'general'        => array( 'title' => __( 'General', 'make' ), 'path' => $section_path ),
		'stylekit'       => array( 'title' => __( 'Style Kits', 'make' ), 'path' => $section_path ),
		'font'           => array( 'title' => __( 'Fonts', 'make' ), 'path' => $section_path ),
		'color'          => array( 'title' => __( 'Colors', 'make' ), 'path' => $section_path ),
		'header'         => array( 'title' => __( 'Header', 'make' ), 'path' => $section_path ),
		'logo'           => array( 'title' => __( 'Logo', 'make' ), 'path' => $section_path ),
		'main'           => array( 'title' => __( 'Main', 'make' ), 'path' => $section_path ),
		'layout-blog'    => array( 'title' => __( 'Layout: Blog (Posts Page)', 'make' ), 'path' => $section_path ),
		'layout-archive' => array( 'title' => __( 'Layout: Archives', 'make' ), 'path' => $section_path ),
		'layout-search'  => array( 'title' => __( 'Layout: Search Results', 'make' ), 'path' => $section_path ),
		'layout-post'    => array( 'title' => __( 'Layout: Posts', 'make' ), 'path' => $section_path ),
		'layout-page'    => array( 'title' => __( 'Layout: Pages', 'make' ), 'path' => $section_path ),
		'footer'         => array( 'title' => __( 'Footer', 'make' ), 'path' => $section_path ),
		'social'         => array( 'title' => __( 'Social Profiles &amp; RSS', 'make' ), 'path' => $section_path )
	);

	if ( ttfmake_is_plus() ) {
		unset( $sections['stylekit'] );
	}

	$sections = apply_filters( 'make_customizer_sections', $sections );

	// Priority for first section
	$priority = new TTFMAKE_Prioritizer( 0, 10 );

	// Add and populate each section, if it exists
	foreach ( $sections as $section => $data ) {
		$file = trailingslashit( $data[ 'path' ] ) . $section . '.php';

		if ( file_exists( $file ) ) {
			// First load the file
			require_once( $file );

			// Custom priorities for built-in sections
			if ( 'font' === $section ) {
				$wp_customize->get_section( 'background_image' )->priority = $priority->add();
			}

			if ( 'logo' === $section ) {
				$wp_customize->get_section( 'title_tagline' )->priority = $priority->add();
			}

			if ( 'main' === $section ) {
				$wp_customize->get_section( 'nav' )->priority = $priority->add();
			}

			if ( 'layout-blog' === $section ) {
				$wp_customize->get_section( 'static_front_page' )->priority = $priority->add();
			}

			// Then add the section
			$section_callback  = 'ttfmake_customizer_';
			$section_callback .= ( strpos( $section, '-' ) ) ? str_replace( '-', '_', $section ) : $section;

			if ( function_exists( $section_callback ) ) {
				$section_id = 'ttfmake_' . esc_attr( $section );

				// Sanitize the section title
				if ( ! isset( $data[ 'title' ] ) || ! $data[ 'title' ] ) {
					$data[ 'title' ] = ucfirst( esc_attr( $section ) );
				}

				// Add section
				$wp_customize->add_section(
					$section_id,
					array(
						'title'    => $data[ 'title' ],
						'priority' => $priority->add(),
					)
				);

				// Callback to populate the section
				call_user_func_array(
					$section_callback,
					array(
						$wp_customize,
						$section_id
					)
				);
			}
		}
	}
}
endif;

if ( ! function_exists( 'ttfmake_customizer_set_transport' ) ) :
/**
 * Add postMessage support for certain built-in settings in the Theme Customizer.
 *
 * Allows these settings to update asynchronously in the Preview pane.
 *
 * @since  1.0.0.
 *
 * @param  WP_Customize_Manager    $wp_customize    Theme Customizer object.
 * @return void
 */
function ttfmake_customizer_set_transport( $wp_customize ) {
	$wp_customize->get_setting( 'blogname' )->transport        = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport = 'postMessage';
}
endif;

if ( ! function_exists( 'ttfmake_customizer_preview_script' ) ) :
/**
 * Enqueue customizer preview script
 *
 * Hooked to 'customize_preview_init' via ttfmake_customizer_init()
 *
 * @since  1.0.0.
 *
 * @return void
 */
function ttfmake_customizer_preview_script() {
	wp_enqueue_script(
		'ttfmake-customizer-preview',
		get_template_directory_uri() . '/inc/customizer/js/customizer-preview' . TTFMAKE_SUFFIX . '.js',
		array( 'customize-preview' ),
		TTFMAKE_VERSION,
		true
	);
}
endif;

if ( ! function_exists( 'ttfmake_customizer_sections_script' ) ) :
/**
 * Enqueue customizer sections script
 *
 * Hooked to 'customize_controls_enqueue_scripts' via ttfmake_customizer_init()
 *
 * @since  1.0.0.
 *
 * @return void
 */
function ttfmake_customizer_sections_script() {
	wp_enqueue_script(
		'ttfmake-customizer-sections',
		get_template_directory_uri() . '/inc/customizer/js/customizer-sections' . TTFMAKE_SUFFIX . '.js',
		array( 'customize-controls' ),
		TTFMAKE_VERSION,
		true
	);

	// Collect localization data
	$data = array(
		'fontOptions'		=> ttfmake_get_font_property_option_keys( 'family' ),
		'allFontChoices'	=> ttfmake_all_font_choices_js(),
	);

	// Add localization strings for Upgrade button
	if ( ! ttfmake_is_plus() ) {
		$localize = array(
			'plusURL'			=> esc_url( ttfmake_get_plus_link( 'customize-head' ) ),
			'plusLabel'			=> __( 'Upgrade to Make Plus', 'make' ),
		);
		$data = $data + $localize;
	}

	// Localize the script
	wp_localize_script(
		'ttfmake-customizer-sections',
		'ttfmakeCustomizerL10n',
		$data
	);
}
endif;

if ( ! function_exists( 'ttfmake_customizer_admin_styles' ) ) :
/**
 * Styles for our Customizer sections and controls. Prints in the <head>
 *
 * @since  1.0.0.
 *
 * @return void
 */
function ttfmake_customizer_admin_styles() {
?>
	<style type="text/css">
		.customize-control.customize-control-heading {
			margin-top: 6px;
			margin-bottom: -2px;
		}
		.customize-control.customize-control-line {
			margin-top: 10px;
			margin-bottom: 6px;
		}
	</style>
<?php
}
endif;

if ( ! function_exists( 'ttfmake_add_customizations' ) ) :
/**
 * Make sure the 'make_css' action only runs once.
 *
 * @since  1.0.0.
 *
 * @return void
 */
function ttfmake_add_customizations() {
	/**
	 * The hook used to add CSS rules for the generated inline CSS.
	 *
	 * This hook is the correct hook to use for adding CSS styles to the group of selectors and properties that will be
	 * added to inline CSS that is printed in the head. Hooking elsewhere may lead to rules not being registered
	 * correctly for the CSS generation. Most Customizer options will use this hook to register additional CSS rules.
	 *
	 * @since 1.2.3.
	 */
	do_action( 'make_css' );
}
endif;

add_action( 'admin_init', 'ttfmake_add_customizations' );

if ( ! function_exists( 'ttfmake_display_customizations' ) ) :
/**
 * Generates the style tag and CSS needed for the theme options.
 *
 * By using the "make_css" filter, different components can print CSS in the header. It is organized this way to
 * ensure that there is only one "style" tag and not a proliferation of them.
 *
 * @since  1.0.0.
 *
 * @return void
 */
function ttfmake_display_customizations() {
	/**
	 * The hook used to add CSS rules for the generated inline CSS.
	 *
	 * This hook is the correct hook to use for adding CSS styles to the group of selectors and properties that will be
	 * added to inline CSS that is printed in the head. Hooking elsewhere may lead to rules not being registered
	 * correctly for the CSS generation. Most Customizer options will use this hook to register additional CSS rules.
	 *
	 * @since 1.2.3.
	 */
	do_action( 'make_css' );

	// Echo the rules
	$css = ttfmake_get_css()->build();
	if ( ! empty( $css ) ) {
		echo "\n<!-- Begin Make Custom CSS -->\n<style type=\"text/css\" id=\"ttfmake-custom-css\">\n";
		echo $css;
		echo "\n</style>\n<!-- End Make Custom CSS -->\n";
	}
}
endif;

add_action( 'wp_head', 'ttfmake_display_customizations', 11 );

if ( ! function_exists( 'ttfmake_ajax_display_customizations' ) ) :
/**
 * Generates the theme option CSS as an Ajax response.
 *
 * @since  1.0.0.
 *
 * @return void
 */
function ttfmake_ajax_display_customizations() {
	// Make sure this is an Ajax request
	if ( ! defined( 'DOING_AJAX' ) || true !== DOING_AJAX ) {
		return;
	}

	// Set the content type
	header( "Content-Type: text/css" );

	// Echo the rules
	echo ttfmake_get_css()->build();

	// End the Ajax response
	die();
}
endif;

add_action( 'wp_ajax_ttfmake-css', 'ttfmake_ajax_display_customizations' );

if ( ! function_exists( 'ttfmake_mce_css' ) ) :
/**
 * Make sure theme option CSS is added to TinyMCE last, to override other styles.
 *
 * @since  1.0.0.
 *
 * @param  string    $stylesheets    List of stylesheets added to TinyMCE.
 * @return string                    Modified list of stylesheets.
 */
function ttfmake_mce_css( $stylesheets ) {
	if ( ttfmake_get_css()->build() ) {
		$stylesheets .= ',' . add_query_arg( 'action', 'ttfmake-css', admin_url( 'admin-ajax.php' ) );
	}

	return $stylesheets;
}
endif;

add_filter( 'mce_css', 'ttfmake_mce_css', 99 );