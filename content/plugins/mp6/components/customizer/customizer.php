<?php 
/**
 * Override the default customizer styles with MP6.
 */

// replace some default css files with ours
add_action( 'admin_init', 'mp6_replace_customizer_styles' );
function mp6_replace_customizer_styles() {

	global $wp_styles, $_wp_admin_css_colors;
	$color_scheme = get_user_option( 'admin_color' );
	if ( $color_scheme != 'mp6' &&
		isset( $_wp_admin_css_colors[ $color_scheme ]->customzier ) &&
		file_exists( $_wp_admin_css_colors[ $color_scheme ]->customzier ) 
	) {
		$wp_styles->registered[ 'customize-controls' ]->src = $_wp_admin_css_colors[ $color_scheme ]->customzier_url;
		//$wp_styles->registered[ 'customize-controls' ]->ver = filemtime( $_wp_admin_css_colors[ $color_scheme ]->customzier );
	} else {
		// Either we're looking a default MP6, or there is no customizer file in this theme.
		$wp_styles->registered[ 'customize-controls' ]->src = plugins_url( 'customizer.css', __FILE__ );
		$wp_styles->registered[ 'customize-controls' ]->ver = filemtime( plugin_dir_path( __FILE__ ) . 'customizer.css' );
	}

}
