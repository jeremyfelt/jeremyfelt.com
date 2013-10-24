<?php

/***********************************************************************************

  This component is in an experimental stage and it's behavior
  might change in the future!

************************************************************************************/

// register additonal MP6 color schemes
add_action( 'admin_init' , 'mp6_colors_register_schemes', 7 );
function mp6_colors_register_schemes() {

	global $_wp_admin_css_colors;

	// MP6 Light
	mp6_add_admin_colors( 'mp6-light', array(
		'label' => 'MP6 Light',
		'palette' => array( '#e5e5e5', '#999', '#d64e07', '#04a4cc' ),
		'icon' => array( 'base' => '#999', 'focus' => '#ccc', 'current' => '#ccc' ),
	) );

	// Blue
	mp6_add_admin_colors( 'blue', array(
		'label' => 'Blue',
		'palette' => array( '#096484', '#4796b3', '#52accc', '#74B6CE' ),
		'icon' =>  array( 'base' => '#e5f8ff', 'focus' => '#fff', 'current' => '#fff' ),
	) );

	// Seaweed
	mp6_add_admin_colors( 'seaweed', array(
		'label' => 'Seaweed',
		'palette' => array( '#10585C', '#15757a', '#a8c274', '#e78229' ),
		'icon' => array( 'base' => '#e1f9fa', 'focus' => '#fff', 'current' => '#fff' ),
	) );

	// Pixel
	mp6_add_admin_colors( 'pixel', array(
		'label' => 'Pixel',
		'palette' => array( '#46403C', '#59524c', '#c7a589', '#9ea476' ),
		'icon' => array( 'base' => '#f3f2f1', 'focus' => '#fff', 'current' => '#fff' ),
	) );

	// Ghostbusters
	mp6_add_admin_colors( 'ectoplasm', array(
		'label' => 'Ectoplasm',
		'palette' => array( '#413256', '#523f6d', '#a3b745', '#d46f15' ),
		'icon' => array( 'base' => '#ece6f6', 'focus' => '#fff', 'current' => '#fff' ),
	) );
	
	// Midnight
	mp6_add_admin_colors( 'midnight', array(
		'label' => 'Midnight',
		'palette' => array( '#25282b', '#363b3f', '#69a8bb', '#e14d43' ),
		'icon' => array( 'base' => '#f1f2f3', 'focus' => '#fff', 'current' => '#fff' ),
	) );

/*
	// Malibu Dreamhouse
	mp6_add_admin_colors( 'malibu-dreamhouse', array(
		'label' => 'Malibu Dreamhouse',
		'palette' => array( '#b476aa', '#c18db8', '#e5cfe1', '#70c0aa' ),
		'icon' => array( 'base' => '#f0e1ed', 'focus' => '#fff', 'current' => '#fff' ),
	) );

	// 80's Kid
	mp6_add_admin_colors( '80s-kid', array(
		'label' => '80\'s Kid',
		'palette' => array( '#0c4da1', '#ed5793', '#43db2a', '#f1f2f3' ),
		'icon' => array( 'base' => '#ebf0f6', 'focus' => '#fff', 'current' => '#fff' ),
	) );

	// Lioness
	mp6_add_admin_colors( 'lioness', array(
		'label' => 'Lioness',
		'palette' => array( '#78231d', '#bfa013', '#906c4d', '#f3f1f1' ),
		'icon' => array( 'base' => '#f5f2e2', 'focus' => '#fff', 'current' => '#fff' ),
	) );
*/

}

/**
 * Add an admin color scheme.
 *
 * This helper function makes it easier to add color schemes to the admin by
 * abstracting out the steps needed to add icon colors, customizer css, etc.
 *
 * @since 2.0
 *
 * @param string $slug The color scheme slug. Used to generate the stylesheet paths, if not overriden.
 * @param array $args {
 *     An array of arguments.
 *     @type string 'label' The color scheme name, human-readable.
 *                          Defaults to slug.
 *     @type array 'palette' An array of CSS color definitions which are used to give the user a feel for the theme.
 *                           Default array().
 *     @type array 'icon' An array of colors used by svgpainter to generate the background, hover, and active
 *                        icon colors for custom SVG icons.
 *                        Default array('base' => '#eee', 'focus' => '#fff', 'current' => '#fff').
 *     @type array 'admin_path' Path to the admin stylesheet. Internally, MP6 uses the same file structure for
 *                              all schemes, allowing us to define the path based on the scheme's slug.
 *                              Default plugin_dir_path( __FILE__ ) . 'schemes/'. $args['slug'] .'/admin-colors.css'.
 *     @type array 'admin_url' URL to the admin stylesheet.
 *                             Default plugins_url( "schemes/$slug/admin-colors.css", __FILE__ ).
 *     @type array 'customizer_path' Path to the customizer stylesheet. Internally, MP6 uses the same file structure for
 *                                   all schemes, allowing us to define the path based on the scheme's slug.
 *                                   Default 'schemes/'. $args['slug'] .'/customizer.css'.
 *     @type array 'customizer_url' URL to the customizer stylesheet.
 *                                  Default plugins_url( "schemes/$slug/customizer.css", __FILE__ ).
 * }
 * @return boolean True if the admin stylesheet was found & the new theme was added; false if there was no slug,
 *                 or the stylesheet was not found.
 */
function mp6_add_admin_colors( $slug, $args = array() ) {

	global $wp_styles, $_wp_admin_css_colors;

	$defaults = array(
		'label' => $slug,
		'palette' => array(),
		'icon' =>  array( 'base' => '#eee', 'focus' => '#fff', 'current' => '#fff' ),
		'admin_path' => plugin_dir_path( __FILE__ ) . "schemes/$slug/admin-colors.css",
		'admin_url'  => plugins_url( "schemes/$slug/admin-colors.css", __FILE__ ),
		'customizer_path' => plugin_dir_path( __FILE__ ) . "schemes/$slug/customizer.css",
		'customizer_url'  => plugins_url( "schemes/$slug/customizer.css", __FILE__ ),
	);
	$args = wp_parse_args( $args, $defaults );

	if ( file_exists( $args[ 'admin_path' ] ) ) {

		wp_admin_css_color(
			$slug,
			$args[ 'label' ],
			$args[ 'admin_url' ],
			$args[ 'palette' ]
		);

		$_wp_admin_css_colors[ $slug ]->icon_colors = $args[ 'icon' ];

		if ( file_exists( $args[ 'customizer_path' ] ) ) {
			$_wp_admin_css_colors[ $slug ]->customzier = $args[ 'customizer_path' ];
			$_wp_admin_css_colors[ $slug ]->customzier_url = $args[ 'customizer_url' ];
		}

		// set modification time
		$wp_styles->registered[ 'colors' ]->ver = filemtime( $args[ 'admin_path' ] );

		return true;

	}

	return false;

}

// make sure `colors-mp6.css` gets enqueued
add_action( 'admin_init', 'mp6_colors_load_mp6_default_css', 20 );
function mp6_colors_load_mp6_default_css() {

	global $wp_styles, $_wp_admin_css_colors;

	$color_scheme = get_user_option( 'admin_color' );

	if ( $color_scheme == 'mp6' )
		return;

	// add `colors-mp6.css` and make it a dependency of the current color scheme
	$modtime = filemtime( realpath( dirname( __FILE__ ) . '/../../css/' . basename( $_wp_admin_css_colors[ 'mp6' ]->url ) ) );
	$wp_styles->add( 'colors-mp6', $_wp_admin_css_colors[ 'mp6' ]->url, false, $modtime );
	$wp_styles->registered[ 'colors' ]->deps[] = 'colors-mp6';

}

// turn `color_scheme->icon_colors` into `mp6_color_scheme` javascript variable
add_action( 'admin_head', 'mp6_colors_set_script_colors' );
function mp6_colors_set_script_colors() {

	global $_wp_admin_css_colors;

	$color_scheme = get_user_option( 'admin_color' );

	if ( isset( $_wp_admin_css_colors[ $color_scheme ]->icon_colors ) ) {
		echo '<script type="text/javascript">var mp6_color_scheme = ' . json_encode( array( 'icons' => $_wp_admin_css_colors[ $color_scheme ]->icon_colors ) ) . ";</script>\n";
	}

}

// enqueue new color scheme picker (on profile/edit-user screen)
add_action( 'admin_enqueue_scripts', 'mp6_colors_enqueue_picker' );
function mp6_colors_enqueue_picker() {

	if ( ! in_array( get_current_screen()->base, apply_filters( 'mp6_colors_allowed_pages', array( 'profile', 'user-edit', 'profile-network', 'user-edit-network' ) ) ) )
		return;

	wp_enqueue_style( 'mp6-color-scheme-picker', plugins_url( 'picker/style.css', __FILE__ ) );
	wp_enqueue_script( 'mp6-color-scheme-picker', plugins_url( 'picker/scripts.js', __FILE__ ), array( 'user-profile' ) );

}

add_action( 'wp_ajax_mp6_save_user_scheme', 'mp6_colors_ajax_save_user_scheme' );
function mp6_colors_ajax_save_user_scheme() {

	global $_wp_admin_css_colors;

	$user_id = intval( $_POST[ 'user_id' ] ); // can be replaced by `get_current_user_id` if setting is only save for own profile
	$color_scheme = $_POST[ 'color_scheme' ];

	if ( ! get_user_by( 'id', $user_id ) )
		wp_die( -1 );

	if ( ! isset( $_wp_admin_css_colors[ $color_scheme ] ) )
		wp_die( -1 );

	update_user_option( $user_id, 'admin_color', $color_scheme, true );
	wp_die( 0 );

}

// replace default color scheme picker
remove_action( 'admin_color_scheme_picker', 'admin_color_scheme_picker' );
add_action( 'admin_color_scheme_picker', 'mp6_colors_scheme_picker' );
function mp6_colors_scheme_picker() {
	global $_wp_admin_css_colors, $user_id;
	ksort($_wp_admin_css_colors);
?>

	<fieldset id="color-picker">
		<legend class="screen-reader-text"><span><?php _e( 'Admin Color Scheme' ); ?></span></legend>

<?php
	$current_color = get_user_option( 'admin_color', $user_id );

	if ( empty( $current_color ) )
		$current_color = 'mp6';

	$color_info = $_wp_admin_css_colors[$current_color];
?>

		<div class="dropdown dropdown-current">
			<div class="picker-dropdown"></div>
			<label for="admin_color_<?php echo esc_attr( $current_color ); ?>"><?php echo esc_html( $color_info->name ); ?></label>
			<table class="color-palette">
				<tr>
				<?php foreach ( $color_info->colors as $html_color ): ?>
					<td style="background-color: <?php echo esc_attr( $html_color ); ?>" title="<?php echo esc_attr( $current_color ); ?>">&nbsp;</td>
				<?php endforeach; ?>
				</tr>
			</table>
		</div>

		<div class="dropdown dropdown-container">

		<?php foreach ( $_wp_admin_css_colors as $color => $color_info ) : ?>

			<div class="color-option <?php echo ( $color == $current_color ) ? 'selected' : ''; ?>">
				<input name="admin_color" id="admin_color_<?php echo esc_attr( $color ); ?>" type="radio" value="<?php echo esc_attr( $color ); ?>" class="tog" <?php checked( $color, $current_color ); ?> />
				<input type="hidden" class="css_url" value="<?php echo esc_attr( $color_info->url ); ?>" />
				<input type="hidden" class="icon_colors" value="<?php echo esc_attr( json_encode( array( 'icons' => $color_info->icon_colors ) ) ); ?>" />
				<label for="admin_color_<?php echo esc_attr( $color ); ?>"><?php echo esc_html( $color_info->name ); ?></label>
				<table class="color-palette">
					<tr>
					<?php foreach ( $color_info->colors as $html_color ): ?>
						<td style="background-color: <?php echo esc_attr( $html_color ); ?>" title="<?php echo esc_attr( $color ); ?>">&nbsp;</td>
					<?php endforeach; ?>
					</tr>
				</table>
			</div>

		<?php endforeach; ?>

		</div>

	</fieldset>

<?php
}
