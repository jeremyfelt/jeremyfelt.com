<?php
/**
 * @package Make
 */

if ( ! function_exists( 'ttfmake_comment' ) ) :
/**
 * Template for comments and pingbacks.
 *
 * Used as a callback by wp_list_comments() for displaying the comments.
 *
 * @since  1.0.0.
 *
 * @param  array    $comment    The current comment object.
 * @param  array    $args       The comment configuration arguments.
 * @param  mixed    $depth      Depth of the current comment.
 * @return void
 */
function ttfmake_comment( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;

	if ( 'pingback' == $comment->comment_type || 'trackback' == $comment->comment_type ) : ?>

	<li id="comment-<?php comment_ID(); ?>" <?php comment_class(); ?>>
		<div class="comment-body">
			<?php _e( 'Pingback:', 'make' ); ?> <?php comment_author_link(); ?> <?php edit_comment_link( __( 'Edit', 'make' ), '<span class="edit-link">', '</span>' ); ?>
		</div>

	<?php else : ?>

	<li id="comment-<?php comment_ID(); ?>" <?php comment_class( empty( $args['has_children'] ) ? '' : 'comment-parent' ); ?>>
		<article id="div-comment-<?php comment_ID(); ?>" class="comment-body">
			<header class="comment-header">
				<?php // Avatar
				if ( 0 != $args['avatar_size'] ) :
					echo get_avatar( $comment, $args['avatar_size'] );
				endif;
				?>
				<div class="comment-date">
					<a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>">
						<time datetime="<?php comment_time( 'c' ); ?>">
							<?php
							printf(
								_x( '%1$s at %2$s', '1: date, 2: time', 'make' ),
								get_comment_date(),
								get_comment_time()
							);
							?>
						</time>
					</a>
				</div>
				<div class="comment-author vcard">
					<?php
					printf(
						'%1$s <span class="says">%2$s</span>',
						sprintf(
							'<cite class="fn">%s</cite>',
							get_comment_author_link()
						),
						_x( 'says:', 'e.g. Bob says hello.', 'make' )
					);
					?>
				</div>

				<?php if ( '0' == $comment->comment_approved ) : ?>
				<p class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'make' ); ?></p>
				<?php endif; ?>
			</header>

			<div class="comment-content">
				<?php comment_text(); ?>
			</div>

			<?php
			comment_reply_link( array_merge( $args, array(
				'add_below' => 'div-comment',
				'depth'     => $depth,
				'max_depth' => $args['max_depth'],
				'before'    => '<footer class="comment-reply">',
				'after'     => '</footer>',
			) ) );
			?>
		</article>

	<?php endif;
}
endif;

if ( ! function_exists( 'ttfmake_categorized_blog' ) ) :
/**
 * Returns true if a blog has more than 1 category.
 *
 * @since  1.0.0.
 *
 * @return bool    Determine if the site has more than one active category.
 */
function ttfmake_categorized_blog() {
	if ( false === ( $all_the_cool_cats = get_transient( 'all_the_cool_cats' ) ) ) {
		// Create an array of all the categories that are attached to posts.
		$all_the_cool_cats = get_categories( array(
			'hide_empty' => 1,
		) );

		// Count the number of categories that are attached to the posts.
		$all_the_cool_cats = count( $all_the_cool_cats );

		set_transient( 'all_the_cool_cats', $all_the_cool_cats, DAY_IN_SECONDS );
	}

	if ( '1' != $all_the_cool_cats ) {
		// This blog has more than 1 category so ttfmake_categorized_blog should return true.
		return true;
	} else {
		// This blog has only 1 category so ttfmake_categorized_blog should return false.
		return false;
	}
}
endif;

if ( ! function_exists( 'ttfmake_category_transient_flusher' ) ) :
/**
 * Flush out the transients used in ttfmake_categorized_blog.
 *
 * @since  1.0.0.
 *
 * @return void
 */
function ttfmake_category_transient_flusher() {
	delete_transient( 'all_the_cool_cats' );
	ttfmake_categorized_blog();
}
endif;

add_action( 'edit_category', 'ttfmake_category_transient_flusher' );
add_action( 'save_post',     'ttfmake_category_transient_flusher' );

if ( ! function_exists( 'ttfmake_get_read_more' ) ) :
/**
 * Return a read more link
 *
 * Use '%s' as a placeholder for the post URL.
 *
 * @since  1.0.0.
 *
 * @param  string    $before    HTML before the text.
 * @param  string    $after     HTML after the text.
 * @return string               Full read more HTML.
 */
function ttfmake_get_read_more( $before = '<a class="read-more" href="%s">', $after = '</a>' ) {
	if ( strpos( $before, '%s' ) ) {
		$before = sprintf(
			$before,
			get_permalink()
		);
	}

	$more = apply_filters( 'ttfmake_read_more_text', __( 'Read more', 'make' ) );

	return $before . $more . $after;
}
endif;

if ( ! function_exists( 'ttfmake_maybe_show_site_region' ) ) :
/**
 * Output the site region (header or footer) markup if the current view calls for it.
 *
 * @since  1.0.0.
 *
 * @param  string    $region    Region to maybe show.
 * @return void
 */
function ttfmake_maybe_show_site_region( $region ) {
	if ( ! in_array( $region, array( 'header', 'footer' ) ) ) {
		return;
	}

	// Get the view
	$view = ttfmake_get_view();

	// Get the relevant option
	$hide_region = (bool) get_theme_mod( 'layout-' . $view . '-hide-' . $region, ttfmake_get_default( 'layout-' . $view . '-hide-' . $region ) );

	if ( true !== $hide_region ) {
		get_template_part(
			'partials/' . $region . '-layout',
			get_theme_mod( $region . '-layout', ttfmake_get_default( $region . '-layout' ) )
		);
	}
}
endif;

if ( ! function_exists( 'ttfmake_get_site_header_class' ) ) :
/**
 * Compile the classes for the site header
 *
 * @since 1.0.0.
 *
 * @return string
 */
function ttfmake_get_site_header_class() {
	// Base
	$class = 'site-header';

	// Layout
	$class .= ' header-layout-' . get_theme_mod( 'header-layout', ttfmake_get_default( 'header-layout' ) );

	// Title
	$hide_site_title = (int) get_theme_mod( 'hide-site-title', ttfmake_get_default( 'hide-site-title' ) );
	if ( 1 === $hide_site_title || ! get_bloginfo( 'name' ) ) {
		$class .= ' no-site-title';
	}

	// Tagline
	$hide_tagline    = (int) get_theme_mod( 'hide-tagline', ttfmake_get_default( 'hide-tagline' ) );
	if ( 1 === $hide_tagline || ! get_bloginfo( 'description' ) ) {
		$class .= ' no-site-tagline';
	}

	return esc_attr( $class );
}
endif;

if ( ! function_exists( 'ttfmake_maybe_show_sidebar' ) ) :
/**
 * Output the sidebar markup if the current view calls for it.
 *
 * The function is a wrapper for the get_sidebar() function. In this theme, the sidebars can be turned on and off for
 * different page views. It is important the the sidebar is *only included* if the user has set the option for it to
 * be included. As such, the get_sidebar() function needs to additional logic to determine whether or not to even
 * include the template.
 *
 * @since  1.0.0.
 *
 * @param  string    $location    The sidebar location (e.g., left, right).
 * @return void
 */
function ttfmake_maybe_show_sidebar( $location ) {
	// Get sidebar status
	$show_sidebar = ttfmake_has_sidebar( $location );

	// Output the sidebar
	if ( true === $show_sidebar ) {
		get_sidebar( $location );
	}
}
endif;

if ( ! function_exists( 'ttfmake_maybe_show_social_links' ) ) :
/**
 * Show the social links markup if the theme options and/or menus are configured for it.
 *
 * @since  1.0.0.
 *
 * @param  string    $region    The site region (header or footer).
 * @return void
 */
function ttfmake_maybe_show_social_links( $region ) {
	if ( ! in_array( $region, array( 'header', 'footer' ) ) ) {
		return;
	}

	$show_social = (bool) get_theme_mod( $region . '-show-social', ttfmake_get_default( $region . '-show-social' ) );

	if ( true === $show_social ) {
		// First look for the alternate custom menu method
		if ( has_nav_menu( 'social' ) ) {
			wp_nav_menu(
				array(
					'theme_location' => 'social',
					'container'      => false,
					'menu_id'        => '',
					'menu_class'     => 'social-menu social-links ' . $region . '-social-links',
					'depth'          => 1,
					'fallback_cb'    => '',
				)
			);
		}
		// Then look for the Customizer theme option method
		else {
			$social_links = ttfmake_get_social_links();
			if ( ! empty( $social_links ) ) { ?>
				<ul class="social-customizer social-links <?php echo $region; ?>-social-links">
				<?php foreach ( $social_links as $key => $link ) : ?>
					<li class="<?php echo esc_attr( $key ); ?>">
						<a href="<?php echo esc_url( $link['url'] ); ?>" title="<?php echo esc_attr( $link['title'] ); ?>">
							<i class="fa fa-fw <?php echo esc_attr( $link['class'] ); ?>"></i>
						</a>
					</li>
				<?php endforeach; ?>
				</ul>
			<?php }
		}
	}
}
endif;

if ( ! function_exists( 'ttfmake_pre_wp_nav_menu_social' ) ) :
/**
 * Alternative output for wp_nav_menu for the 'social' menu location.
 *
 * @since  1.0.0.
 *
 * @param  string    $output    Output for the menu.
 * @param  object    $args      wp_nav_menu arguments.
 * @return string               Modified menu.
 */
function ttfmake_pre_wp_nav_menu_social( $output, $args ) {
	if ( ! $args->theme_location || 'social' !== $args->theme_location ) {
		return $output;
	}

	// Get the menu object
	$locations = get_nav_menu_locations();
	$menu      = wp_get_nav_menu_object( $locations[ $args->theme_location ] );

	if ( ! $menu || is_wp_error( $menu ) ) {
		return $output;
	}

	$output = '';

	// Get the menu items
	$menu_items = wp_get_nav_menu_items( $menu->term_id, array( 'update_post_term_cache' => false ) );

	// Set up the $menu_item variables
	_wp_menu_item_classes_by_context( $menu_items );

	// Sort the menu items
	$sorted_menu_items = array();
	foreach ( (array) $menu_items as $menu_item ) {
		$sorted_menu_items[ $menu_item->menu_order ] = $menu_item;
	}

	unset( $menu_items, $menu_item );

	// Supported social icons (filterable); [url pattern] => [css class]
	$supported_icons = apply_filters( 'ttfmake_supported_social_icons', array(
		'app.net'            => 'fa-adn',
		'behance.net'        => 'fa-behance',
		'bitbucket.org'      => 'fa-bitbucket',
		'codepen.io'         => 'fa-codepen',
		'delicious.com'      => 'fa-delicious',
		'deviantart.com'     => 'fa-deviantart',
		'digg.com'           => 'fa-digg',
		'dribbble.com'       => 'fa-dribbble',
		'facebook.com'       => 'fa-facebook',
		'flickr.com'         => 'fa-flickr',
		'foursquare.com'     => 'fa-foursquare',
		'github.com'         => 'fa-github',
		'gittip.com'         => 'fa-gittip',
		'plus.google.com'    => 'fa-google-plus-square',
		'instagram.com'      => 'fa-instagram',
		'jsfiddle.net'       => 'fa-jsfiddle',
		'linkedin.com'       => 'fa-linkedin',
		'pinterest.com'      => 'fa-pinterest',
		'qzone.qq.com'       => 'fa-qq',
		'reddit.com'         => 'fa-reddit',
		'renren.com'         => 'fa-renren',
		'soundcloud.com'     => 'fa-soundcloud',
		'spotify.com'        => 'fa-spotify',
		'stackexchange.com'  => 'fa-stack-exchange',
		'stackoverflow.com'  => 'fa-stack-overflow',
		'steamcommunity.com' => 'fa-steam',
		'stumbleupon.com'    => 'fa-stumbleupon',
		't.qq.com'           => 'fa-tencent-weibo',
		'trello.com'         => 'fa-trello',
		'tumblr.com'         => 'fa-tumblr',
		'twitter.com'        => 'fa-twitter',
		'vimeo.com'          => 'fa-vimeo-square',
		'vine.co'            => 'fa-vine',
		'vk.com'             => 'fa-vk',
		'weibo.com'          => 'fa-weibo',
		'weixin.qq.com'      => 'fa-weixin',
		'wordpress.com'      => 'fa-wordpress',
		'xing.com'           => 'fa-xing',
		'yahoo.com'          => 'fa-yahoo',
		'youtube.com'        => 'fa-youtube',
	) );

	// Process each menu item
	foreach ( $sorted_menu_items as $item ) {
		$item_output = '';

		// Look for matching icons
		foreach ( $supported_icons as $pattern => $class ) {
			if ( false !== strpos( $item->url, $pattern ) ) {
				$item_output .= '<li class="' . esc_attr( str_replace( 'fa-', '', $class ) ) . '">';
				$item_output .= '<a href="' . esc_url( $item->url ) . '" title="' . esc_attr( $item->title ) . '">';
				$item_output .= '<i class="fa fa-fw ' . esc_attr( $class ) . '"></i>';
				$item_output .= '</a></li>';

				break;
			}
		}

		// No matching icons
		if ( '' === $item_output ) {
			$item_output .= '<li class="external-link-square">';
			$item_output .= '<a href="' . esc_url( $item->url ) . '" title="' . esc_attr( $item->title ) . '">';
			$item_output .= '<i class="fa fa-fw fa-external-link-square"></i>';
			$item_output .= '</a></li>';
		}

		// Add item to list
		$output .= $item_output;
		unset( $item_output );
	}

	// If there are menu items, add a wrapper
	if ( '' !== $output ) {
		$output = '<ul class="' . esc_attr( $args->menu_class ) . '">' . $output . '</ul>';
	}

	return $output;
}
endif;

add_filter( 'pre_wp_nav_menu', 'ttfmake_pre_wp_nav_menu_social', 10, 2 );

if ( ! function_exists( 'ttfmake_get_exif_data' ) ) :
/**
 * Get EXIF data from an attachment.
 *
 * @since  1.0.0.
 *
 * @param  int       $attachment_id    The attachment ID to get data from.
 * @return string                      The EXIF data.
 */
function ttfmake_get_exif_data( $attachment_id = 0 ) {
	// Validate attachment id
	if ( 0 === absint( $attachment_id ) ) {
		$attachment_id = get_post()->ID;
	}

	$output = '';

	$attachment_meta = wp_get_attachment_metadata( $attachment_id );
	$image_meta      = ( isset( $attachment_meta['image_meta'] ) ) ? array_filter( $attachment_meta['image_meta'], 'trim' ) : array();
	if ( ! empty( $image_meta ) ) {
		// Defaults
		$defaults = array(
			'aperture' => 0,
  			'camera' => '',
  			'created_timestamp' => 0,
  			'focal_length' => 0,
  			'iso' => 0,
  			'shutter_speed' => 0,
		);
		$image_meta = wp_parse_args( $image_meta, $defaults );

		// Convert the shutter speed to a fraction and add units
		if ( 0 !== $image_meta[ 'shutter_speed' ] ) {
			$raw_ss = floatval( $image_meta['shutter_speed'] );
			$denominator = 1 / $raw_ss;
			if ( $denominator > 1 ) {
				$decimal_places = 0;
				if ( in_array( number_format( $denominator, 1 ), array( 1.3, 1.5, 1.6, 2.5 ) ) ) {
					$decimal_places = 1;
				}
				$converted_ss = sprintf(
					'1/%1$s %2$s',
					number_format_i18n( $denominator, $decimal_places ),
					_x( 'second', 'time', 'make' )
				);
			} else {
				$converted_ss = sprintf(
					'%1$s %2$s',
					number_format_i18n( $raw_ss, 1 ),
					_x( 'seconds', 'time', 'make' )
				);
			}
			$image_meta['shutter_speed'] = apply_filters( 'ttfmake_exif_shutter_speed', $converted_ss, $image_meta['shutter_speed'] );
		}

		// Convert the aperture to an F-stop
		if ( 0 !== $image_meta[ 'aperture' ] ) {
			$f_stop = sprintf(
				'%1$s' . '%2$s',
				_x( 'f/', 'camera f-stop', 'make' ),
				number_format_i18n( pow( sqrt( 2 ), absint( $image_meta['aperture'] ) ) )
			);
			$image_meta['aperture'] = apply_filters( 'ttfmake_exif_aperture', $f_stop, $image_meta['aperture'] );
		}

		$output .= "<ul class=\"entry-exif-list\">\n";

		// Camera
		if ( ! empty( $image_meta['camera'] ) ) {
			$output .= '<li><span>' . _x( 'Camera:', 'camera setting', 'make' ) . '</span> ';
			$output .= esc_html( $image_meta['camera'] ) . "</li>\n";
		}

		// Creation Date
		if ( ! empty( $image_meta['created_timestamp'] ) ) {
			$output .= '<li><span>' . _x( 'Taken:', 'camera setting', 'make' ) . '</span> ';
			$date    = new DateTime( gmdate( "Y-m-d\TH:i:s\Z", $image_meta['created_timestamp'] ) );
			$output .= esc_html( $date->format( get_option( 'date_format' ) ) ) . "</li>\n";
		}

		// Focal length
		if ( ! empty( $image_meta['focal_length'] ) ) {
			$output .= '<li><span>' . _x( 'Focal length:', 'camera setting', 'make' ) . '</span> ';
			$output .= number_format_i18n( absint( $image_meta['focal_length'] ), 0 ) . _x( 'mm', 'millimeters', 'make' ) . "</li>\n";
		}

		// Aperture
		if ( ! empty( $image_meta['aperture'] ) ) {
			$output .= '<li><span>' . _x( 'Aperture:', 'camera setting', 'make' ) . '</span> ';
			$output .= esc_html( $image_meta['aperture'] ) . "</li>\n";
		}

		// Exposure
		if ( ! empty( $image_meta['shutter_speed'] ) ) {
			$output .= '<li><span>' . _x( 'Exposure:', 'camera setting', 'make' ) . '</span> ';
			$output .= esc_html( $image_meta['shutter_speed'] ) . "</li>\n";
		}

		// ISO
		if ( ! empty( $image_meta['iso'] ) ) {
			$output .= '<li><span>' . _x( 'ISO:', 'camera setting', 'oxford' ) . '</span> ';
			$output .= absint( $image_meta['iso'] ) . "</li>\n";
		}

		$output .= "</ul>\n";
	}

	return $output;
}
endif;