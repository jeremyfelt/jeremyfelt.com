<?php
/**
 * Sample implementation of the Custom Header feature
 * http://codex.wordpress.org/Custom_Headers
 *
 * You can add an optional custom header image to header.php like so ...
 *
 * @package Resonar
 */

/**
 * Set up the WordPress core custom header feature.
 *
 * @uses resonar_header_style()
 * @uses resonar_admin_header_style()
 * @uses resonar_admin_header_image()
 */
function resonar_custom_header_setup() {
	add_theme_support( 'custom-header', apply_filters( 'resonar_custom_header_args', array(
		'default-image'          => '',
		'default-text-color'     => '232323',
		'width'                  => 2000,
		'height'                 => 320,
		'flex-height'            => true,
		'wp-head-callback'       => 'resonar_header_style',
		'admin-head-callback'    => 'resonar_admin_header_style',
		'admin-preview-callback' => 'resonar_admin_header_image',
	) ) );
}
add_action( 'after_setup_theme', 'resonar_custom_header_setup' );

if ( ! function_exists( 'resonar_header_style' ) ) :
/**
 * Styles the header image and text displayed on the blog
 *
 * @see resonar_custom_header_setup().
 */
function resonar_header_style() {
	$header_text_color = get_header_textcolor();
	$header_image = get_header_image();

	// If no custom options for text are set, let's bail
	// get_header_textcolor() options: HEADER_TEXTCOLOR is default, hide text (returns 'blank') or any hex value
	if ( HEADER_TEXTCOLOR == $header_text_color && empty( $header_image ) ) {
		return;
	}

	// If we get this far, we have custom styles. Let's do this.
	?>
	<style type="text/css">
	<?php
		// Has the text been hidden?
		if ( 'blank' == $header_text_color ) :
	?>
		.site-title,
		.site-description {
			position: absolute;
			clip: rect(1px, 1px, 1px, 1px);
		}
	<?php
		// If the user has set a custom color for the text use that
		else :
	?>
		.site-title a,
		.site-title a:hover,
		.site-title a:focus,
		.site-description {
			color: #<?php echo esc_attr( $header_text_color ); ?>;
		}
	<?php endif; ?>
	</style>
	<?php
}
endif; // resonar_header_style

if ( ! function_exists( 'resonar_admin_header_style' ) ) :
/**
 * Styles the header image displayed on the Appearance > Header admin panel.
 *
 * @see resonar_custom_header_setup().
 */
function resonar_admin_header_style() {
?>
	<style type="text/css">
		.appearance_page_custom-header #headimg {
			border: none;
		}
		.displaying-header-wrapper {
			background-color: #fff;
			padding: 32px;
		}

		#headimg h1 {
			font-family: Lato, sans-serif;;
		}

		#desc {
			font-family: "Libre Baskerville", georgia, serif;
		}

		#headimg h1 {
			font-size: 26px;
			font-weight: 900;
			line-height: 32px;
			margin: 0;
		}

		#headimg h1 a {
			color: #232323;
			text-decoration: none;
		}

		#desc {
			color: #232323;
			display: none;
			font-size: 13px;
			font-weight: 400;
			line-height: 20px;
			margin: 4px 0 0 0;
			opacity: 0.7;
		}

		#headimg img {
			vertical-align: middle;
		}

		.displaying-header-image img {
			width: 100%;
			height: auto;
		}
	</style>
<?php
}
endif; // resonar_admin_header_style

if ( ! function_exists( 'resonar_admin_header_image' ) ) :
/**
 * Custom header image markup displayed on the Appearance > Header admin panel.
 *
 * @see resonar_custom_header_setup().
 */
function resonar_admin_header_image() {
	$style = sprintf( ' style="color:#%s;"', get_header_textcolor() );
?>
	<div id="headimg">
		<?php if ( get_header_image() ) : ?>
		<div class="displaying-header-image">
			<img src="<?php header_image(); ?>" alt="">
		</div>
		<?php endif; ?>
		<div class="displaying-header-wrapper">
			<h1 class="displaying-header-text"><a id="name"<?php echo $style; ?> onclick="return false;" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a></h1>
			<div class="displaying-header-text" id="desc"<?php echo $style; ?>><?php bloginfo( 'description' ); ?></div>
		</div>
	</div>
<?php
}
endif; // resonar_admin_header_image
