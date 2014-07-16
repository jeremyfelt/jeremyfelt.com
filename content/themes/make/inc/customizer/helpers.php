<?php
/**
 * @package Make
 */

if ( ! function_exists( 'ttfmake_sanitize_text' ) ) :
/**
 * Sanitize a string to allow only tags in the allowedtags array.
 *
 * @since  1.0.0.
 *
 * @param  string    $string    The unsanitized string.
 * @return string               The sanitized string.
 */
function ttfmake_sanitize_text( $string ) {
	global $allowedtags;
	return wp_kses( $string , $allowedtags );
}
endif;

if ( ! function_exists( 'ttfmake_sanitize_choice' ) ) :
/**
 * Sanitize a value from a list of allowed values.
 *
 * @since 1.0.0.
 *
 * @param  mixed    $value      The value to sanitize.
 * @param  mixed    $setting    The setting for which the sanitizing is occurring.
 * @return mixed                The sanitized value.
 */
function ttfmake_sanitize_choice( $value, $setting ) {
	if ( is_object( $setting ) ) {
		$setting = $setting->id;
	}

	$choices         = ttfmake_get_choices( $setting );
	$allowed_choices = array_keys( $choices );

	if ( ! in_array( $value, $allowed_choices ) ) {
		$value = ttfmake_get_default( $setting );
	}

	return $value;
}
endif;

if ( ! function_exists( 'ttfmake_get_choices' ) ) :
/**
 * Return the available choices for a given setting
 *
 * @since  1.0.0.
 *
 * @param  string|object    $setting    The setting to get options for.
 * @return array                        The options for the setting.
 */
function ttfmake_get_choices( $setting ) {
	if ( is_object( $setting ) ) {
		$setting = $setting->id;
	}

	$choices = array( 0 );

	switch ( $setting ) {
		case 'general-layout' :
			$choices = array(
				'full-width' => __( 'Full-width', 'make' ),
				'boxed'      => __( 'Boxed', 'make' )
			);
			break;
		case 'layout-blog-featured-images' :
		case 'layout-archive-featured-images' :
		case 'layout-search-featured-images' :
		case 'layout-post-featured-images' :
		case 'layout-page-featured-images' :
			$choices = array(
				'post-header' => __( 'Post header', 'make' ),
				'thumbnail'   => __( 'Thumbnail', 'make' ),
				'none'        => __( 'None', 'make' ),
			);
			break;
		case 'layout-blog-post-date' :
		case 'layout-archive-post-date' :
		case 'layout-search-post-date' :
		case 'layout-post-post-date' :
		case 'layout-page-post-date' :
			$week_ago = date( get_option( 'date_format' ), time() - WEEK_IN_SECONDS );
			$choices = array(
				'absolute' => sprintf( __( 'Absolute (%s)', 'make' ), $week_ago ),
				'relative' => __( 'Relative (1 week ago)', 'make' ),
				'none'     => __( 'None', 'make' ),
			);
			break;
		case 'layout-blog-post-author' :
		case 'layout-archive-post-author' :
		case 'layout-search-post-author' :
		case 'layout-post-post-author' :
		case 'layout-page-post-author' :
			$choices = array(
				'avatar' => __( 'With avatar', 'make' ),
				'name'   => __( 'Without avatar', 'make' ),
				'none'   => __( 'None', 'make' ),
			);
			break;
		case 'header-background-repeat' :
		case 'main-background-repeat' :
		case 'footer-background-repeat' :
			$choices = array(
				'no-repeat' => __( 'No Repeat', 'make' ),
				'repeat'    => __( 'Tile', 'make' ),
				'repeat-x'  => __( 'Tile Horizontally', 'make' ),
				'repeat-y'  => __( 'Tile Vertically', 'make' )
			);
			break;
		case 'header-background-position' :
		case 'main-background-position' :
		case 'footer-background-position' :
		case 'layout-blog-featured-images-alignment' :
		case 'layout-archive-featured-images-alignment' :
		case 'layout-search-featured-images-alignment' :
		case 'layout-post-featured-images-alignment' :
		case 'layout-page-featured-images-alignment' :
			$choices = array(
				'left'   => __( 'Left', 'make' ),
				'center' => __( 'Center', 'make' ),
				'right'  => __( 'Right', 'make' )
			);
			break;
		case 'background_size' :
		case 'header-background-size' :
		case 'main-background-size' :
		case 'footer-background-size' :
			$choices = array(
				'auto'    => __( 'Auto', 'make' ),
				'cover'   => __( 'Cover', 'make' ),
				'contain' => __( 'Contain', 'make' )
			);
			break;
		case 'header-bar-content-layout' :
			$choices = array(
				'default' => __( 'Default', 'make' ),
				'flipped' => __( 'Flipped', 'make' )
			);
			break;
		case 'header-layout' :
			$choices = array(
				1  => __( 'Traditional', 'make' ),
				2  => __( 'Centered', 'make' ),
				3  => __( 'Navigation Below', 'make' ),
			);
			break;
		case 'header-branding-position' :
			$choices = array(
				'left'  => __( 'Left', 'make' ),
				'right' => __( 'Right', 'make' )
			);
			break;
		case 'footer-widget-areas' :
			$choices = array(
				0 => _x( '0', 'footer widget area number', 'make' ),
				1 => _x( '1', 'footer widget area number', 'make' ),
				2 => _x( '2', 'footer widget area number', 'make' ),
				3 => _x( '3', 'footer widget area number', 'make' ),
				4 => _x( '4', 'footer widget area number', 'make' )
			);
			break;
		case 'footer-layout' :
			$choices = array(
				1  => __( 'Traditional', 'make' ),
				2  => __( 'Centered', 'make' ),
			);
			break;
		case 'layout-blog-post-date-location' :
		case 'layout-blog-post-author-location' :
		case 'layout-blog-comment-count-location' :
		case 'layout-archive-post-date-location' :
		case 'layout-archive-post-author-location' :
		case 'layout-archive-comment-count-location' :
		case 'layout-search-post-date-location' :
		case 'layout-search-post-author-location' :
		case 'layout-search-comment-count-location' :
		case 'layout-post-post-date-location' :
		case 'layout-post-post-author-location' :
		case 'layout-post-comment-count-location' :
		case 'layout-page-post-date-location' :
		case 'layout-page-post-author-location' :
		case 'layout-page-comment-count-location' :
			$choices = array(
				'top'            => __( 'Top', 'make' ),
				'before-content' => __( 'Before content', 'make' ),
				'post-footer'    => __( 'Post footer', 'make' ),
			);
			break;
		case 'layout-blog-comment-count' :
		case 'layout-archive-comment-count' :
		case 'layout-search-comment-count' :
		case 'layout-post-comment-count' :
		case 'layout-page-comment-count' :
			$choices = array(
				'icon' => __( 'With icon', 'make' ),
				'text' => __( 'With text', 'make' ),
				'none' => __( 'None' ),
			);
			break;
	}

	return apply_filters( 'ttfmake_setting_choices', $choices, $setting );
}
endif;

if ( ! function_exists( 'ttfmake_display_favicons' ) ) :
/**
 * Write the favicons to the head to implement the options.
 *
 * @since  1.0.0.
 *
 * @return void
 */
function ttfmake_display_favicons() {
	$logo_favicon = get_theme_mod( 'logo-favicon', ttfmake_get_default( 'logo-favicon' ) );
	if ( ! empty( $logo_favicon ) ) : ?>
		<link rel="icon" href="<?php echo esc_url( $logo_favicon ); ?>" />
	<?php endif;

	$logo_apple_touch = get_theme_mod( 'logo-apple-touch', ttfmake_get_default( 'logo-apple-touch' ) );
	if ( ! empty( $logo_apple_touch ) ) : ?>
		<link rel="apple-touch-icon" href="<?php echo esc_url( $logo_apple_touch ); ?>" />
	<?php endif;
}
endif;

add_action( 'wp_head', 'ttfmake_display_favicons' );

if ( ! function_exists( 'ttfmake_body_layout_classes' ) ) :
/**
 * Add theme option body classes.
 *
 * @since  1.0.0.
 *
 * @param  array    $classes    Existing classes.
 * @return array                Modified classes.
 */
function ttfmake_body_layout_classes( $classes ) {
	// Full-width vs Boxed
	$classes[] = get_theme_mod( 'general-layout', ttfmake_get_default( 'general-layout' ) );

	// Header branding position
	if ( 'right' === get_theme_mod( 'header-branding-position', ttfmake_get_default( 'header-branding-position' ) ) ) {
		$classes[] = 'branding-right';
	}

	// Header Bar text position
	if ( 'flipped' === get_theme_mod( 'header-bar-content-layout', ttfmake_get_default( 'header-bar-content-layout' ) ) ) {
		$classes[] = 'header-bar-flipped';
	}

	return $classes;
}
endif;

add_filter( 'body_class', 'ttfmake_body_layout_classes' );

if ( ! function_exists( 'ttfmake_get_social_links' ) ) :
/**
 * Get the social links from options.
 *
 * @since  1.0.0.
 *
 * @return array    Keys are service names and the values are links.
 */
function ttfmake_get_social_links() {
	// Define default services; note that these are intentional non-translatable
	$default_services = array(
		'facebook' => array(
			'title' => 'Facebook',
			'class' => 'fa-facebook',
		),
		'twitter' => array(
			'title' => 'Twitter',
			'class' => 'fa-twitter',
		),
		'google-plus-square' => array(
			'title' => 'Google+',
			'class' => 'fa-google-plus-square',
		),
		'linkedin' => array(
			'title' => 'LinkedIn',
			'class' => 'fa-linkedin',
		),
		'instagram' => array(
			'title' => 'Instagram',
			'class' => 'fa-instagram',
		),
		'flickr' => array(
			'title' => 'Flickr',
			'class' => 'fa-flickr',
		),
		'youtube' => array(
			'title' => 'YouTube',
			'class' => 'fa-youtube',
		),
		'vimeo-square' => array(
			'title' => 'Vimeo',
			'class' => 'fa-vimeo-square',
		),
		'pinterest' => array(
			'title' => 'Pinterest',
			'class' => 'fa-pinterest',
		),
		'email' => array(
			'title' => __( 'Email', 'make' ),
			'class' => 'fa-envelope',
		),
		'rss' => array(
			'title' => __( 'RSS', 'make' ),
			'class' => 'fa-rss',
		),
	);

	// Set up the collector array
	$services_with_links = array();

	// Get the links for these services
	foreach ( $default_services as $service => $details ) {
		$url = get_theme_mod( 'social-' . $service, ttfmake_get_default( 'social-' . $service ) );
		if ( '' !== $url ) {
			$services_with_links[ $service ] = array(
				'title' => $details['title'],
				'url'   => $url,
				'class' => $details['class'],
			);
		}
	}

	// Special handling for RSS
	$hide_rss = (int) get_theme_mod( 'social-hide-rss', ttfmake_get_default( 'social-hide-rss' ) );
	if ( 0 === $hide_rss ) {
		$custom_rss = get_theme_mod( 'social-custom-rss', ttfmake_get_default( 'social-custom-rss' ) );
		if ( ! empty( $custom_rss ) ) {
			$services_with_links['rss']['url'] = $custom_rss;
		} else {
			$services_with_links['rss']['url'] = get_feed_link();
		}
	} else {
		unset( $services_with_links['rss'] );
	}

	// Properly set the email
	if ( isset( $services_with_links['email']['url'] ) ) {
		$services_with_links['email']['url'] = esc_url( 'mailto:' . $services_with_links['email']['url'] );
	}

	return apply_filters( 'ttfmake_social_links', $services_with_links );
}
endif;