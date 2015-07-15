<?php
/**
 * Resonar functions and definitions
 *
 * @package Resonar
 * @since Resonar 1.0
 */

/**
 * Set the content width based on the theme's design and stylesheet.
 */
if ( ! isset( $content_width ) ) {
	$content_width = 1088;
}

/**
 * Resonar only works in WordPress 4.1 or later.
 */
if ( version_compare( $GLOBALS['wp_version'], '4.1-alpha', '<' ) ) {
	require get_template_directory() . '/inc/back-compat.php';
}

if ( ! function_exists( 'resonar_setup' ) ) :
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function resonar_setup() {

	/*
	 * Make theme available for translation.
	 * Translations can be filed in the /languages/ directory.
	 * If you're building a theme based on Resonar, use a find and replace
	 * to change 'resonar' to the name of your theme in all the template files
	 */
	load_theme_textdomain( 'resonar', get_template_directory() . '/languages' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	/*
	 * Let WordPress manage the document title.
	 * By adding theme support, we declare that this theme does not use a
	 * hard-coded <title> tag in the document head, and expect WordPress to
	 * provide it for us.
	 */
	add_theme_support( 'title-tag' );

	/*
	 * Enable support for Post Thumbnails on posts and pages.
	 *
	 * @link http://codex.wordpress.org/Function_Reference/add_theme_support#Post_Thumbnails
	 */
	add_theme_support( 'post-thumbnails' );
	set_post_thumbnail_size( 256, 256, true );
	add_image_size( 'resonar-large', 2000, 1500, true  );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus( array(
		'primary' => __( 'Primary Menu',      'resonar' ),
		'social'  => __( 'Social Links Menu', 'resonar' ),
	) );

	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support( 'html5', array(
		'search-form', 'comment-form', 'comment-list', 'gallery', 'caption',
	) );

	// Set up the WordPress core custom background feature.
	add_theme_support( 'custom-background', apply_filters( 'resonar_custom_background_args', array(
		'default-color'    => 'ffffff',
		'wp-head-callback' => 'resonar_custom_background_cb'
	) ) );

	/*
	 * This theme styles the visual editor to resemble the theme style,
	 * specifically font, colors, icons, and column width.
	 */
	add_editor_style( array( 'editor-style.css', 'genericons/genericons.css', resonar_fonts_url() ) );
}
endif; // resonar_setup
add_action( 'after_setup_theme', 'resonar_setup' );

if ( ! function_exists( 'resonar_custom_background_cb' ) ) :
/**
 * Add a wp-head callback to the custom background
 *
 * @since Resonar 1.0.3
 */
function resonar_custom_background_cb() {
	$background_image = get_background_image();
	$color = get_background_color();

	if ( ! $background_image && ! $color ) {
		return;
	}
?>
	<style type="text/css" id="resonar-custom-background-css">
	<?php if ( ! empty ( $background_image ) ) { ?>
			body.custom-background {
				background-image: url(<?php echo esc_url( $background_image ); ?>);
			}

	<?php } elseif ( 'ffffff' != $color ) { ?>
			body.custom-background,
			.site-header .sub-menu li,
			.sidebar {
				background-color: #<?php echo esc_attr( $color ); ?>;
			}

			.site-header .nav-menu > li > .sub-menu:after {
				border-color: #<?php echo esc_attr( $color ); ?> transparent;
			}

			.pagination .prev:hover,
			.pagination .prev:focus,
			.pagination .next:hover,
			.pagination .next:focus,
			.widget_calendar tbody a,
			.widget_calendar tbody a:hover,
			.widget_calendar tbody a:focus,
			.page-links a,
			.page-links a:hover,
			.page-links a:focus {
				color: #<?php echo esc_attr( $color ); ?>;
			}
	<?php } ?>
	</style>
<?php
}
endif;

/**
 * Register widget area.
 *
 * @link http://codex.wordpress.org/Function_Reference/register_sidebar
 */
function resonar_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'Widget Area', 'resonar' ),
		'id'            => 'sidebar-1',
		'description'   => __( 'Add widgets here to appear in your sidebar.', 'resonar' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'resonar_widgets_init' );

if ( ! function_exists( 'resonar_fonts_url' ) ) :
/**
 * Register Google fonts for Resonar.
 *
 * @since Resonar 1.0
 *
 * @return string Google fonts URL for the theme.
 */
function resonar_fonts_url() {
	$fonts_url = '';
	$fonts     = array();
	$subsets   = 'latin,latin-ext';

	/* translators: If there are characters in your language that are not supported by Libre Baskerville, translate this to 'off'. Do not translate into your own language. */
	if ( 'off' !== _x( 'on', 'Libre Baskerville font: on or off', 'resonar' ) ) {
		$fonts[] = 'Libre Baskerville:400,700,400italic';
	}

	/* translators: If there are characters in your language that are not supported by Lato, translate this to 'off'. Do not translate into your own language. */
	if ( 'off' !== _x( 'on', 'Lato font: on or off', 'resonar' ) ) {
		$fonts[] = 'Lato:400,700,900,400italic,700italic,900italic';
	}

	/* translators: If there are characters in your language that are not supported by Playfair Display, translate this to 'off'. Do not translate into your own language. */
	if ( 'off' !== _x( 'on', 'Playfair Display font: on or off', 'resonar' ) ) {
		$fonts[] = 'Playfair Display:400,700,400italic,700italic';
	}

	/* translators: If there are characters in your language that are not supported by Inconsolata, translate this to 'off'. Do not translate into your own language. */
	if ( 'off' !== _x( 'on', 'Inconsolata font: on or off', 'resonar' ) ) {
		$fonts[] = 'Inconsolata:400';
	}

	/*
	 * Translators: To add an additional character subset specific to your language,
	 * translate this to 'cyrillic'. Do not translate into your own language.
	 */
	$subset = _x( 'no-subset', 'Add new subset (cyrillic)', 'resonar' );

	if ( 'cyrillic' == $subset ) {
		$subsets .= ',cyrillic';
	}

	if ( $fonts ) {
		$fonts_url = add_query_arg( array(
			'family' => urlencode( implode( '|', $fonts ) ),
			'subset' => urlencode( $subsets ),
		), '//fonts.googleapis.com/css' );
	}

	return $fonts_url;
}
endif;

/**
 * JavaScript Detection.
 *
 * Adds a `js` class to the root `<html>` element when JavaScript is detected.
 *
 * @since Resonar 1.0
 */
function resonar_javascript_detection() {
	echo "<script>(function(html){html.className = html.className.replace(/\bno-js\b/,'js')})(document.documentElement);</script>\n";
}
add_action( 'wp_head', 'resonar_javascript_detection', 0 );

/**
 * Enqueue scripts and styles.
 *
 * @since Resonar 1.0
 */
function resonar_scripts() {
	// Add custom fonts, used in the main stylesheet.
	wp_enqueue_style( 'resonar-fonts', resonar_fonts_url(), array(), null );

	// Add Genericons, used in the main stylesheet.
	wp_enqueue_style( 'genericons', get_template_directory_uri() . '/genericons/genericons.css', array(), '3.3' );

	// Load our main stylesheet.
	wp_enqueue_style( 'resonar-style', get_stylesheet_uri() );

	wp_enqueue_script( 'resonar-skip-link-focus-fix', get_template_directory_uri() . '/js/skip-link-focus-fix.js', array(), '20150302', true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	if ( is_singular() && wp_attachment_is_image() ) {
		wp_enqueue_script( 'resonar-keyboard-image-navigation', get_template_directory_uri() . '/js/keyboard-image-navigation.js', array( 'jquery' ), '20150302' );
	}

	wp_enqueue_script( 'resonar-script', get_template_directory_uri() . '/js/functions.js', array( 'jquery' ), '20150302', true );

	wp_localize_script( 'resonar-script', 'screenReaderText', array(
		'expand'   => '<span class="screen-reader-text">' . __( 'expand child menu', 'resonar' ) . '</span>',
		'collapse' => '<span class="screen-reader-text">' . __( 'collapse child menu', 'resonar' ) . '</span>',
	) );

	wp_localize_script( 'resonar-script', 'toggleButtonText', array(
		'menu'    => __( 'Menu', 'resonar' ),
		'widgets' => __( 'Widgets', 'resonar' ),
		'both'    => __( 'Menu &amp; Widgets', 'resonar' ),
	) );
}
add_action( 'wp_enqueue_scripts', 'resonar_scripts' );

/**
 * Enqueue Google fonts style to admin screen for custom header display.
 *
 * @since Resonar 1.0
 */
function resonar_admin_fonts() {
	wp_enqueue_style( 'resonar-fonts', resonar_fonts_url(), array(), null );
}
add_action( 'admin_print_scripts-appearance_page_custom-header', 'resonar_admin_fonts' );

/**
 * Add a `screen-reader-text` class to the search form's submit button.
 *
 * @since Resonar 1.0
 *
 * @param string $html Search form HTML.
 * @return string Modified search form HTML.
 */
function resonar_search_form_modify( $html ) {
	return str_replace( 'class="search-submit"', 'class="search-submit screen-reader-text"', $html );
}
add_filter( 'get_search_form', 'resonar_search_form_modify' );

/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function resonar_body_classes( $classes ) {
	if ( is_singular() ) {
		$classes[] = 'single';
	}

	if ( has_nav_menu( 'primary' ) ) {
		$classes[] = 'custom-menu';
	}

	return $classes;
}
add_filter( 'body_class', 'resonar_body_classes' );

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
require get_template_directory() . '/inc/jetpack.php';
