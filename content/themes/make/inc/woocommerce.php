<?php
/**
 * @package Make
 */

if ( ! function_exists( 'ttfmake_woocommerce_init' ) ) :
/**
 * Add theme support and remove default action hooks so we can replace them with our own.
 *
 * @since  1.0.0.
 *
 * @return void
 */
function ttfmake_woocommerce_init() {
	// Theme support
	add_theme_support( 'woocommerce' );

	// Content wrapper
	remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper' );
	remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end' );

	// Sidebar
	remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar' );
}
endif;

add_action( 'after_setup_theme', 'ttfmake_woocommerce_init' );

if ( ! function_exists( 'ttfmake_woocommerce_before_main_content' ) ) :
/**
 * Markup to show before the main WooCommerce content.
 *
 * @since  1.0.0.
 *
 * @return void
 */
function ttfmake_woocommerce_before_main_content() {
	// Left sidebar
	ttfmake_maybe_show_sidebar( 'left' );

	// Begin content wrapper
	?>
	<main id="site-main" class="site-main" role="main">
	<?php
}
endif;

add_action( 'woocommerce_before_main_content', 'ttfmake_woocommerce_before_main_content' );

if ( ! function_exists( 'ttfmake_woocommerce_after_main_content' ) ) :
/**
 * Markup to show after the main WooCommerce content
 *
 * @since  1.0.0.
 *
 * @return void
 */
function ttfmake_woocommerce_after_main_content() {
	// End content wrapper
	?>
	</main>
	<?php
	// Right sidebar
	ttfmake_maybe_show_sidebar( 'right' );
}
endif;

add_action( 'woocommerce_after_main_content', 'ttfmake_woocommerce_after_main_content' );