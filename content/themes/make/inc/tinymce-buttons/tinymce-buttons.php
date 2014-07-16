<?php
/**
 * @package Make
 */

if ( ! class_exists( 'TTFMAKE_TinyMCE_Buttons' ) ) :
/**
 * Collector for builder sections.
 *
 * @since 1.0.0.
 *
 * Class TTFMAKE_TinyMCE_Buttons
 */
class TTFMAKE_TinyMCE_Buttons {
	/**
	 * The one instance of TTFMAKE_TinyMCE_Buttons.
	 *
	 * @since 1.0.0.
	 *
	 * @var   TTFMAKE_TinyMCE_Buttons
	 */
	private static $instance;

	/**
	 * Instantiate or return the one TTFMAKE_TinyMCE_Buttons instance.
	 *
	 * @since  1.0.0.
	 *
	 * @return TTFMAKE_TinyMCE_Buttons
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register the sections.
	 *
	 * @since  1.0.0.
	 *
	 * @return TTFMAKE_TinyMCE_Buttons
	 */
	public function __construct() {
		// Add the button
		add_action( 'admin_init', array( $this, 'add_button_button' ), 11 );

		// Reorder the hr button
		add_filter( 'tiny_mce_before_init', array( $this, 'tiny_mce_before_init' ), 20, 2 );

		// Add translations for plugin
		add_filter( 'wp_mce_translation', array( $this, 'wp_mce_translation' ), 10, 2 );

		// Add the CSS for the icon
		add_action( 'admin_print_styles-post.php', array( $this, 'admin_print_styles' ) );
		add_action( 'admin_print_styles-post-new.php', array( $this, 'admin_print_styles' ) );
	}

	/**
	 * Implement the TinyMCE button for creating a button.
	 *
	 * @since  1.0.0.
	 *
	 * @return void
	 */
	public function add_button_button() {
		if ( ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) ) {
			return;
		}

		add_filter( 'mce_external_plugins', array( $this, 'add_tinymce_plugin' ) );
		add_filter( 'mce_buttons', array( $this, 'register_mce_button' ), 10, 2 );
	}

	/**
	 * Implement the TinyMCE plugin for creating a button.
	 *
	 * @since  1.0.0.
	 *
	 * @param  array    $plugins    The current array of plugins.
	 * @return array                The modified plugins array.
	 */
	public function add_tinymce_plugin( $plugins ) {
		$plugins['ttfmake_mce_hr_button']     = get_template_directory_uri() .'/inc/tinymce-buttons/js/tinymce-hr.js';
		$plugins['ttfmake_mce_button_button'] = get_template_directory_uri() .'/inc/tinymce-buttons/js/tinymce-button.js';

		return $plugins;
	}

	/**
	 * Implement the TinyMCE button for creating a button.
	 *
	 * @since  1.0.0.
	 *
	 * @param  array     $buttons      The current array of plugins.
	 * @param  string    $editor_id    The ID for the current editor.
	 * @return array                   The modified plugins array.
	 */
	public function register_mce_button( $buttons, $editor_id ) {
		$buttons[] = 'ttfmake_mce_hr_button';
		$buttons[] = 'ttfmake_mce_button_button';

		return $buttons;
	}

	/**
	 * Position the new hr button in the place that the old hr usually resides.
	 *
	 * @since  1.0.0.
	 *
	 * @param  array     $mceInit      The configuration for the current editor.
	 * @param  string    $editor_id    The ID for the current editor.
	 * @return array                   The modified configuration array.
	 */
	public function tiny_mce_before_init( $mceInit, $editor_id ) {
		if ( ! empty( $mceInit['toolbar1'] ) ) {
			if ( in_array( 'hr', explode( ',', $mceInit['toolbar1'] ) ) ) {
				// Remove the current positioning of the new hr button
				$mceInit['toolbar1'] = str_replace( ',hr,', ',ttfmake_mce_hr_button,', $mceInit['toolbar1'] );

				// Remove the duplicated new hr button
				$pieces              = explode( ',', $mceInit['toolbar1'] );
				$pieces              = array_unique( $pieces );
				$mceInit['toolbar1'] = implode( ',', $pieces );
			}
		}

		return $mceInit;
	}

	/**
	 * Add translations for plugin.
	 *
	 * @since  1.0.0.
	 *
	 * @param  array     $mce_translation    Key/value pairs of strings.
	 * @param  string    $mce_locale         Locale.
	 * @return array                         The updated translation array.
	 */
	public function wp_mce_translation( $mce_translation, $mce_locale ) {
		$additional_items = array(
			'Add button'    => __( 'Add button', 'ttfmake' ),
			'Insert Button' => __( 'Insert Button', 'ttfmake' ),
			'Button text'   => __( 'Button text', 'ttfmake' ),
			'Button URL'    => __( 'Button URL', 'ttfmake' ),
			'Normal'        => __( 'Normal', 'ttfmake' ),
			'Alert'         => __( 'Alert', 'ttfmake' ),
			'Download'      => __( 'Download', 'ttfmake' ),
			'Color'         => __( 'Color', 'ttfmake' ),
			'Primary'       => __( 'Primary', 'ttfmake' ),
			'Secondary'     => __( 'Secondary', 'ttfmake' ),
			'Green'         => __( 'Green', 'ttfmake' ),
			'Red'           => __( 'Red', 'ttfmake' ),
			'Orange'        => __( 'Orange', 'ttfmake' ),
			'Style'         => __( 'Style', 'ttfmake' ),
			'Dotted'        => __( 'Dotted', 'ttfmake' ),
			'Double'        => __( 'Double', 'ttfmake' ),
		);

		return array_merge( $mce_translation, $additional_items );
	}

	/**
	 * Print CSS for the buttons.
	 *
	 * @since  1.0.0.
	 *
	 * @return void
	 */
	public function admin_print_styles() {
	?>
		<style type="text/css">
			i.mce-i-ttfmake-button-button {
				font: normal 20px/1 'dashicons';
				padding: 0;
				vertical-align: top;
				speak: none;
				-webkit-font-smoothing: antialiased;
				-moz-osx-font-smoothing: grayscale;
				margin-left: -2px;
				padding-right: 2px;
			}
			i.mce-i-ttfmake-button-button:before {
				content: '\f502';
			}
		</style>
	<?php
	}
}
endif;

/**
 * Instantiate or return the one TTFMAKE_TinyMCE_Buttons instance.
 *
 * @since  1.0.0.
 *
 * @return TTFMAKE_TinyMCE_Buttons
 */
function ttfmake_get_tinymce_buttons() {
	return TTFMAKE_TinyMCE_Buttons::instance();
}

add_action( 'admin_init', 'ttfmake_get_tinymce_buttons' );