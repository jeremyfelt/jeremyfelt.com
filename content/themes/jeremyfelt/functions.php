<?php

add_action( 'wp_enqueue_scripts', 'jf_enqueue_parent_styles' );
function jf_enqueue_parent_styles() {
	wp_enqueue_style( 'jf-parent-style', get_template_directory_uri() . '/style.css' );
}

class WSUWP_Embed_CodePen {
	public function __construct() {
		add_shortcode( 'wsu_codepen', array( $this, 'display_wsu_codepen' ) );
	}

	public function display_wsu_codepen( $atts ) {
		$defaults = array(
			'height' => 300,
			'theme_id' => 0,
			'pen' => '',
			'tab' => 'css',
			'user' => '',
		);
		$atts = shortcode_atts( $defaults, $atts );

		wp_enqueue_script( 'wsu-codepen', 'https://assets.codepen.io/assets/embed/ei.js', array(), false, true );

		ob_start();
		?><div data-height="<?php echo absint( $atts['height'] ); ?>"
		       data-theme-id="<?php echo absint( $atts['theme_id'] ); ?>"
		       data-slug-hash="<?php echo esc_attr( $atts['pen'] ); ?>"
		       data-default-tab="<?php echo esc_attr( $atts['tab'] ); ?>"
		       data-user="<?php echo esc_attr( $atts['user'] ); ?>"
		       class='codepen'></div>
		<?php
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}
}
new WSUWP_Embed_CodePen();