<?php
/**
 * @package Make
 */

if ( ! function_exists( 'ttfmake_css_add_rules' ) ) :
/**
 * Process user options to generate CSS needed to implement the choices.
 *
 * This function reads in the options from theme mods and determines whether a CSS rule is needed to implement an
 * option. CSS is only written for choices that are non-default in order to avoid adding unnecessary CSS. All options
 * are also filterable allowing for more precise control via a child theme or plugin.
 *
 * Note that all CSS for options is present in this function except for the CSS for fonts and the logo, which require
 * a lot more code to implement.
 *
 * @since  1.0.0.
 *
 * @return void
 */
function ttfmake_css_add_rules() {
	/**
	 * Background section
	 */
	$site_background_image = get_theme_mod( 'background_image', ttfmake_get_default( 'background_image' ) );
	if ( ! empty( $site_background_image ) ) {
		// Note that most site background options are handled by internal WordPress functions
		$site_background_size = ttfmake_sanitize_choice( get_theme_mod( 'background_size', ttfmake_get_default( 'background_size' ) ), 'background-size' );

		ttfmake_get_css()->add( array(
			'selectors'    => array( 'body' ),
			'declarations' => array(
				'background-size' => $site_background_size
			)
		) );
	}

	/**
	 * Colors section
	 */
	// Get and escape options
	$color_primary   = maybe_hash_hex_color( get_theme_mod( 'color-primary', ttfmake_get_default( 'color-primary' ) ) );
	$color_secondary = maybe_hash_hex_color( get_theme_mod( 'color-secondary', ttfmake_get_default( 'color-secondary' ) ) );
	$color_text      = maybe_hash_hex_color( get_theme_mod( 'color-text', ttfmake_get_default( 'color-text' ) ) );
	$color_detail    = maybe_hash_hex_color( get_theme_mod( 'color-detail', ttfmake_get_default( 'color-detail' ) ) );

	// Primary color
	if ( $color_primary !== ttfmake_get_default( 'color-primary' ) ) {
		ttfmake_get_css()->add( array(
			'selectors'    => array( '.color-primary-text', 'a', '.entry-author-byline a.vcard', '.entry-footer a:hover', '.comment-form .required', 'ul.ttfmake-list-dot li:before', 'ol.ttfmake-list-dot li:before', '.entry-comment-count a:hover',
'.comment-count-icon a:hover' ),
			'declarations' => array(
				'color' => $color_primary
			)
		) );
		ttfmake_get_css()->add( array(
			'selectors'    => array( '.color-primary-background','.ttfmake-button.color-primary-background' ),
			'declarations' => array(
				'background-color' => $color_primary
			)
		) );
		ttfmake_get_css()->add( array(
			'selectors'    => array( '.color-primary-border' ),
			'declarations' => array(
				'border-color' => $color_primary
			)
		) );
		ttfmake_get_css()->add( array(
			'selectors'    => array( '.site-navigation ul.menu ul a:hover', '.site-navigation ul.menu ul a:focus', '.site-navigation .menu ul ul a:hover', '.site-navigation .menu ul ul a:focus' ),
			'declarations' => array(
				'background-color' => $color_primary
			),
			'media'        => 'screen and (min-width: 800px)'
		) );
	}

	// Secondary color
	if ( $color_secondary !== ttfmake_get_default( 'color-secondary' ) ) {
		ttfmake_get_css()->add( array(
			'selectors'    => array(
				'.color-secondary-text',
				'.builder-section-banner .cycle-pager',
				'.ttfmake-shortcode-slider .cycle-pager',
				'.builder-section-banner .cycle-prev:before',
				'.builder-section-banner .cycle-next:before',
				'.ttfmake-shortcode-slider .cycle-prev:before',
				'.ttfmake-shortcode-slider .cycle-next:before',
				'.ttfmake-shortcode-slider .cycle-caption',
			),
			'declarations' => array(
				'color' => $color_secondary
			)
		) );
		ttfmake_get_css()->add( array(
			'selectors'    => array(
				'.color-secondary-background',
				'blockquote.ttfmake-testimonial',
				'tt',
				'kbd',
				'pre',
				'code',
				'samp',
				'var',
				'textarea',
				'input[type="date"]',
				'input[type="datetime"]',
				'input[type="datetime-local"]',
				'input[type="email"]',
				'input[type="month"]',
				'input[type="number"]',
				'input[type="password"]',
				'input[type="search"]',
				'input[type="tel"]',
				'input[type="text"]',
				'input[type="time"]',
				'input[type="url"]',
				'input[type="week"]',
				'.ttfmake-button.color-secondary-background',
				'button.color-secondary-background',
				'input[type="button"].color-secondary-background',
				'input[type="reset"].color-secondary-background',
				'input[type="submit"].color-secondary-background',
				'.sticky-post-label',
				'.widget_tag_cloud a',
			),
			'declarations' => array(
				'background-color' => $color_secondary
			)
		) );
		ttfmake_get_css()->add( array(
			'selectors'    => array(
				'.site-navigation .menu .sub-menu',
				'.site-navigation .menu .children',
			),
			'declarations' => array(
				'background-color' => $color_secondary
			),
			'media'        => 'screen and (min-width: 800px)'
		) );
		ttfmake_get_css()->add( array(
			'selectors'    => array(
				'.color-secondary-border',
				'table',
				'table th',
				'table td',
				'.header-layout-3 .site-navigation .menu',
			),
			'declarations' => array(
				'border-color' => $color_secondary
			)
		) );
		ttfmake_get_css()->add( array(
			'selectors'    => array(
				'hr',
				'hr.ttfmake-line-dashed',
				'hr.ttfmake-line-double',
				'blockquote.ttfmake-testimonial:after',
			),
			'declarations' => array(
				'border-top-color' => $color_secondary
			)
		) );
		ttfmake_get_css()->add( array(
			'selectors'    => array(
				'.comment-body',
				'.post',
				'.widget li',
			),
			'declarations' => array(
				'border-bottom-color' => $color_secondary
			)
		) );
	}

	// Text color
	if ( $color_text !== ttfmake_get_default( 'color-text' ) ) {
		ttfmake_get_css()->add( array(
			'selectors'    => array( '.color-text', 'body', '.entry-date a', 'body', 'button', 'input', 'select', 'textarea', '[class*="navigation"] .nav-previous a', '[class*="navigation"] .nav-previous span', '[class*="navigation"] .nav-next a', '[class*="navigation"] .nav-next span' ),
			'declarations' => array(
				'color' => $color_text
			)
		) );
	}

	// Detail color
	if ( $color_detail !== ttfmake_get_default( 'color-detail' ) ) {
		ttfmake_get_css()->add( array(
			'selectors'    => array( '.color-detail-text', '.builder-section-banner .cycle-pager .cycle-pager-active', '.ttfmake-shortcode-slider .cycle-pager .cycle-pager-active', '.post-categories li:after', '.post-tags li:after', '.comment-count-icon:before', '.entry-comment-count a',
'.comment-count-icon a' ),
			'declarations' => array(
				'color' => $color_detail
			)
		) );
		ttfmake_get_css()->add( array(
			'selectors'    => array( '.site-navigation .menu-item-has-children a:after' ),
			'declarations' => array(
				'color' => $color_detail
			),
			'media'        => 'screen and (min-width: 800px)'
		) );
		ttfmake_get_css()->add( array(
			'selectors'    => array( '.site-navigation .menu .sub-menu a', '.site-navigation .menu .sub-menu a' ),
			'declarations' => array(
				'border-bottom-color' => $color_detail
			),
			'media'        => 'screen and (min-width: 800px)'
		) );
		ttfmake_get_css()->add( array(
			'selectors'    => array( '.color-detail-background' ),
			'declarations' => array(
				'background-color' => $color_detail
			)
		) );
		ttfmake_get_css()->add( array(
			'selectors'    => array( '.color-detail-border' ),
			'declarations' => array(
				'border-color' => $color_detail
			)
		) );
	}

	/**
	 * Header section
	 */
	// Get and escape options
	$header_text_color           = maybe_hash_hex_color( get_theme_mod( 'header-text-color', ttfmake_get_default( 'header-text-color' ) ) );
	$header_background_color     = maybe_hash_hex_color( get_theme_mod( 'header-background-color', ttfmake_get_default( 'header-background-color' ) ) );
	$header_background_image     = get_theme_mod( 'header-background-image', ttfmake_get_default( 'header-background-image' ) );
	$header_bar_text_color       = maybe_hash_hex_color( get_theme_mod( 'header-bar-text-color', ttfmake_get_default( 'header-bar-text-color' ) ) );
	$header_bar_border_color     = maybe_hash_hex_color( get_theme_mod( 'header-bar-border-color', ttfmake_get_default( 'header-bar-border-color' ) ) );
	$header_bar_background_color = maybe_hash_hex_color( get_theme_mod( 'header-bar-background-color', ttfmake_get_default( 'header-bar-background-color' ) ) );

	// Header text color
	if ( $header_text_color !== ttfmake_get_default( 'header-text-color' ) ) {
		ttfmake_get_css()->add( array(
			'selectors'    => array( '.site-header', '.site-header a', '.site-navigation .menu li a' ),
			'declarations' => array(
				'color' => $header_text_color
			)
		) );
	}

	// Header background color
	if ( $header_background_color !== ttfmake_get_default( 'header-background-color' ) ) {
		ttfmake_get_css()->add( array(
			'selectors'    => array( '.site-header-main' ),
			'declarations' => array(
				'background-color' => $header_background_color
			)
		) );
	}

	// Header background image
	if ( ! empty( $header_background_image ) ) {
		// Escape the background image URL properly
		$header_background_image = addcslashes( esc_url_raw( $header_background_image ), '"' );

		// Get and escape related options
		$header_background_size     = ttfmake_sanitize_choice( get_theme_mod( 'header-background-size', ttfmake_get_default( 'header-background-size' ) ), 'header-background-size' );
		$header_background_repeat   = ttfmake_sanitize_choice( get_theme_mod( 'header-background-repeat', ttfmake_get_default( 'header-background-repeat' ) ), 'header-background-repeat' );
		$header_background_position = ttfmake_sanitize_choice( get_theme_mod( 'header-background-position', ttfmake_get_default( 'header-background-position' ) ), 'header-background-position' );

		// All variables are escaped at this point
		ttfmake_get_css()->add( array(
			'selectors'    => array( '.site-header-main' ),
			'declarations' => array(
				'background-image'    => 'url("' . $header_background_image . '")',
				'background-size'     => $header_background_size,
				'background-repeat'   => $header_background_repeat,
				'background-position' => $header_background_position . ' center'
			)
		) );
	}

	// Header Bar text color
	if ( $header_bar_text_color !== ttfmake_get_default( 'header-bar-text-color' ) ) {
		ttfmake_get_css()->add( array(
			'selectors'    => array( '.header-bar' ),
			'declarations' => array(
				'color' => $header_bar_text_color
			)
		) );
	}

	// Header Bar border color
	if ( $header_bar_border_color !== ttfmake_get_default( 'header-bar-border-color' ) ) {
		ttfmake_get_css()->add( array(
			'selectors'    => array( '.header-bar', '.header-bar .search-form input', '.header-social-links li:first-of-type', '.header-social-links li a' ),
			'declarations' => array(
				'border-color' => $header_bar_border_color
			)
		) );
	}

	// Header Bar background color
	if ( $header_bar_background_color !== ttfmake_get_default( 'header-bar-background-color' ) ) {
		ttfmake_get_css()->add( array(
			'selectors'    => array( '.header-bar' ),
			'declarations' => array(
				'background-color' => $header_bar_background_color
			)
		) );
	}

	/**
	 * Site Title & Tagline section
	 */
	$color_site_title = maybe_hash_hex_color( get_theme_mod( 'color-site-title', ttfmake_get_default( 'color-site-title' ) ) );
	if ( $color_site_title !== ttfmake_get_default( 'color-site-title' ) || $header_text_color !== $color_site_title ) {
		ttfmake_get_css()->add( array(
			'selectors'    => array( '.site-header .site-title', '.site-header .site-title a' ),
			'declarations' => array(
				'color' => $color_site_title
			)
		) );
	}

	/**
	 * Main section
	 */
	// Get and escape options
	$main_background_color       = maybe_hash_hex_color( get_theme_mod( 'main-background-color', ttfmake_get_default( 'main-background-color' ) ) );
	$main_background_image       = get_theme_mod( 'main-background-image', ttfmake_get_default( 'main-background-image' ) );
	$main_content_link_underline = absint( get_theme_mod( 'main-content-link-underline', ttfmake_get_default( 'main-content-link-underline' ) ) );

	// Main background color
	if ( $main_background_color !== ttfmake_get_default( 'main-background-color' ) ) {
		ttfmake_get_css()->add( array(
			'selectors'    => array( '.site-content', 'body.mce-content-body' ),
			'declarations' => array(
				'background-color' => $main_background_color
			)
		) );
	}

	// Main background image
	if ( ! empty( $main_background_image ) ) {
		// Escape the background image URL properly
		$main_background_image = addcslashes( esc_url_raw( $main_background_image ), '"' );

		// Get and escape related options
		$main_background_size     = ttfmake_sanitize_choice( get_theme_mod( 'main-background-size', ttfmake_get_default( 'main-background-size' ) ), 'main-background-size' );
		$main_background_repeat   = ttfmake_sanitize_choice( get_theme_mod( 'main-background-repeat', ttfmake_get_default( 'main-background-repeat' ) ), 'main-background-repeat' );
		$main_background_position = ttfmake_sanitize_choice( get_theme_mod( 'main-background-position', ttfmake_get_default( 'main-background-position' ) ), 'main-background-position' );

		// All variables are escaped at this point
		ttfmake_get_css()->add( array(
			'selectors'    => array( '.site-content' ),
			'declarations' => array(
				'background-image'    => 'url("' . $main_background_image . '")',
				'background-size'     => $main_background_size,
				'background-repeat'   => $main_background_repeat,
				'background-position' => $main_background_position . ' top'
			)
		) );
	}

	// Main Content Link Underline
	if ( 1 === $main_content_link_underline ) {
		ttfmake_get_css()->add( array(
			'selectors'    => array( '.entry-content a' ),
			'declarations' => array(
				'text-decoration' => 'underline'
			)
		) );
	}

	/**
	 * Footer section
	 */
	// Get and escape options
	$footer_text_color       = maybe_hash_hex_color( get_theme_mod( 'footer-text-color', ttfmake_get_default( 'footer-text-color' ) ) );
	$footer_border_color     = maybe_hash_hex_color( get_theme_mod( 'footer-border-color', ttfmake_get_default( 'footer-border-color' ) ) );
	$footer_background_color = maybe_hash_hex_color( get_theme_mod( 'footer-background-color', ttfmake_get_default( 'footer-background-color' ) ) );
	$footer_background_image = get_theme_mod( 'footer-background-image', ttfmake_get_default( 'footer-background-image' ) );

	// Footer text color
	if ( $footer_text_color !== ttfmake_get_default( 'footer-text-color' ) ) {
		ttfmake_get_css()->add( array(
			'selectors'    => array( '.site-footer' ),
			'declarations' => array(
				'color' => $footer_text_color
			)
		) );
	}

	// Footer border color
	if ( $footer_border_color !== ttfmake_get_default( 'footer-border-color' ) ) {
		ttfmake_get_css()->add( array(
			'selectors'    => array( '.site-footer *:not(select)' ),
			'declarations' => array(
				'border-color' => $footer_border_color . ' !important'
			)
		) );
	}

	// Footer background color
	if ( $footer_background_color !== ttfmake_get_default( 'footer-background-color' ) ) {
		ttfmake_get_css()->add( array(
			'selectors'    => array( '.site-footer' ),
			'declarations' => array(
				'background-color' => $footer_background_color
			)
		) );
	}

	// Footer background image
	if ( ! empty( $footer_background_image ) ) {
		// Escape the background image URL properly
		$footer_background_image = addcslashes( esc_url_raw( $footer_background_image ), '"' );

		// Get and escape related options
		$footer_background_size     = ttfmake_sanitize_choice( get_theme_mod( 'footer-background-size', ttfmake_get_default( 'footer-background-size' ) ), 'footer-background-size' );
		$footer_background_repeat   = ttfmake_sanitize_choice( get_theme_mod( 'footer-background-repeat', ttfmake_get_default( 'footer-background-repeat' ) ), 'footer-background-repeat' );
		$footer_background_position = ttfmake_sanitize_choice( get_theme_mod( 'footer-background-position', ttfmake_get_default( 'footer-background-position' ) ), 'footer-background-position' );

		// All variables are escaped at this point
		ttfmake_get_css()->add( array(
			'selectors'    => array( '.site-footer' ),
			'declarations' => array(
				'background-image'    => 'url("' . $footer_background_image . '")',
				'background-size'     => $footer_background_size,
				'background-repeat'   => $footer_background_repeat,
				'background-position' => $footer_background_position . ' center'
			)
		) );
	}

	/**
	 * Featured image alignment
	 */
	$templates = array(
		'blog',
		'archive',
		'search',
		'post',
		'page'
	);

	foreach ( $templates as $template_name ) {
		$key       = 'layout-' . $template_name . '-featured-images-alignment';
		$default   = ttfmake_get_default( $key );
		$alignment = ttfmake_sanitize_choice( get_theme_mod( $key, $default ), $key );

		if ( $alignment !== $default ) {
			ttfmake_get_css()->add( array(
				'selectors'    => array( '.' . $template_name . ' .entry-header .entry-thumbnail' ),
				'declarations' => array(
					'text-align' => $alignment,
				)
			) );
		}
	}
}
endif;

add_action( 'ttfmake_css', 'ttfmake_css_add_rules' );

if ( ! function_exists( 'ttfmake_maybe_add_with_avatar_class' ) ) :
/**
 * Add a class to the bounding div if a post uses an avatar with the author byline.
 *
 * @since  1.0.11.
 *
 * @param  array     $classes    An array of post classes.
 * @param  string    $class      A comma-separated list of additional classes added to the post.
 * @param  int       $post_ID    The post ID.
 * @return array                 The modified post class array.
 */
function ttfmake_maybe_add_with_avatar_class( $classes, $class, $post_ID ) {
	$author_key    = 'layout-' . ttfmake_get_view() . '-post-author';
	$author_option = ttfmake_sanitize_choice( get_theme_mod( $author_key, ttfmake_get_default( $author_key ) ), $author_key );

	if ( 'avatar' === $author_option ) {
		$classes[] = 'has-author-avatar';
	}

	return $classes;
}
endif;

add_filter( 'post_class', 'ttfmake_maybe_add_with_avatar_class', 10, 3 );