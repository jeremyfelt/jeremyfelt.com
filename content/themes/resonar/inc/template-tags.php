<?php
/**
 * Custom template tags for this theme.
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @package Resonar
 */

if ( ! function_exists( 'resonar_comment_nav' ) ) :
/**
 * Display navigation to next/previous comments when applicable.
 *
 * @since Resonar 1.0
 */
function resonar_comment_nav() {
	// Are there comments to navigate through?
	if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) :
	?>
	<nav class="navigation comment-navigation" role="navigation">
		<h2 class="screen-reader-text"><?php _e( 'Comment navigation', 'resonar' ); ?></h2>
		<div class="nav-links">
			<?php
				if ( $prev_link = get_previous_comments_link( __( 'Older Comments', 'resonar' ) ) ) :
					printf( '<div class="nav-previous">%s</div>', $prev_link );
				endif;

				if ( $next_link = get_next_comments_link( __( 'Newer Comments', 'resonar' ) ) ) :
					printf( '<div class="nav-next">%s</div>', $next_link );
				endif;
			?>
		</div><!-- .nav-links -->
	</nav><!-- .comment-navigation -->
	<?php
	endif;
}
endif;

if ( ! function_exists( 'resonar_entry_date' ) ) :
/**
 * Prints HTML with meta information for the post date.
 *
 * @since Resonar 1.0
 */
function resonar_entry_date() {
	if ( is_sticky() && is_home() && ! is_paged() ) {
		printf( '<span class="sticky-post">%s</span>', __( 'Featured', 'resonar' ) );
	}

	if ( in_array( get_post_type(), array( 'post', 'attachment' ) ) ) {
		$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';

		if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
			$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
		}

		$time_string = sprintf( $time_string,
			esc_attr( get_the_date( 'c' ) ),
			get_the_date(),
			esc_attr( get_the_modified_date( 'c' ) ),
			get_the_modified_date()
		);

		printf( '<span class="posted-on"><span class="screen-reader-text">%1$s </span><a href="%2$s" rel="bookmark">%3$s</a></span>',
			_x( 'Posted on', 'Used before publish date.', 'resonar' ),
			esc_url( get_permalink() ),
			$time_string
		);
	}
}
endif;

if ( ! function_exists( 'resonar_entry_meta' ) ) :
/**
 * Prints HTML with meta information for the categories, tags.
 *
 * @since Resonar 1.0
 */
function resonar_entry_meta() {
	if ( 'post' == get_post_type() ) {
		$categories_list = get_the_category_list( _x( ', ', 'Used between list items, there is a space after the comma.', 'resonar' ) );
		if ( $categories_list && resonar_categorized_blog() ) {
			printf( '<span class="cat-links"><span class="screen-reader-text">%1$s </span>%2$s</span>',
				_x( 'Categories', 'Used before category names.', 'resonar' ),
				$categories_list
			);
		}

		$tags_list = get_the_tag_list( '', _x( ', ', 'Used between list items, there is a space after the comma.', 'resonar' ) );
		if ( $tags_list ) {
			printf( '<span class="tags-links"><span class="screen-reader-text">%1$s </span>%2$s</span>',
				_x( 'Tags', 'Used before tag names.', 'resonar' ),
				$tags_list
			);
		}
	}

	if ( 'jetpack-portfolio' == get_post_type() ) {
		$project_types_list = get_the_term_list( $post->ID, 'jetpack-portfolio-type', '', _x( ', ', 'Used between list items, there is a space after the comma.', 'resonar' ), '' );
		if ( $project_types_list ) {
			printf( '<span class="cat-links"><span class="screen-reader-text">%1$s </span>%2$s</span>',
				_x( 'Project Types', 'Used before project type names.', 'resonar' ),
				$project_types_list
			);
		}

		$project_tag_list = get_the_term_list( $post->ID, 'jetpack-portfolio-tag', '', _x( ', ', 'Used between list items, there is a space after the comma.', 'resonar' ), '' );
		if ( $project_tag_list ) {
			printf( '<span class="tags-links"><span class="screen-reader-text">%1$s </span>%2$s</span>',
				_x( 'Project Tags', 'Used before project tag names.', 'resonar' ),
				$project_tag_list
			);
		}
	}

	if ( is_attachment() && wp_attachment_is_image() ) {
		// Retrieve attachment metadata.
		$metadata = wp_get_attachment_metadata();

		printf( '<span class="full-size-link"><span class="screen-reader-text">%1$s </span><a href="%2$s">%3$s &times; %4$s</a></span>',
			_x( 'Full size', 'Used before full size attachment link.', 'resonar' ),
			esc_url( wp_get_attachment_url() ),
			$metadata['width'],
			$metadata['height']
		);
	}

	if ( ! post_password_required() && ( comments_open() || get_comments_number() ) ) {
		echo '<span class="comments-link">';
		comments_popup_link( __( 'Leave a comment', 'resonar' ), __( '1 Comment', 'resonar' ), __( '% Comments', 'resonar' ) );
		echo '</span>';
	}

}
endif;

/**
 * Determine whether blog/site has more than one category.
 *
 * @since Resonar 1.0
 *
 * @return bool True of there is more than one category, false otherwise.
 */
function resonar_categorized_blog() {
	if ( false === ( $all_the_cool_cats = get_transient( 'resonar_categories' ) ) ) {
		// Create an array of all the categories that are attached to posts.
		$all_the_cool_cats = get_categories( array(
			'fields'     => 'ids',
			'hide_empty' => 1,

			// We only need to know if there is more than one category.
			'number'     => 2,
		) );

		// Count the number of categories that are attached to the posts.
		$all_the_cool_cats = count( $all_the_cool_cats );

		set_transient( 'resonar_categories', $all_the_cool_cats );
	}

	if ( $all_the_cool_cats > 1 ) {
		// This blog has more than 1 category so resonar_categorized_blog should return true.
		return true;
	} else {
		// This blog has only 1 category so resonar_categorized_blog should return false.
		return false;
	}
}

/**
 * Flush out the transients used in {@see resonar_categorized_blog()}.
 *
 * @since Resonar 1.0
 */
function resonar_category_transient_flusher() {
	// Like, beat it. Dig?
	delete_transient( 'resonar_categories' );
}
add_action( 'edit_category', 'resonar_category_transient_flusher' );
add_action( 'save_post',     'resonar_category_transient_flusher' );


if ( ! function_exists( 'resonar_excerpt_more' ) && ! is_admin() ) :
/**
 * Replaces "[...]" (appended to automatically generated excerpts) with ... and a 'Continue reading' link.
 *
 * @since Resonar 1.0
 *
 * @return string 'Continue reading' link prepended with an ellipsis.
 */
function resonar_excerpt_more( $more ) {
	$link = sprintf( '<a href="%1$s" class="more-link">%2$s</a>',
		esc_url( get_permalink( get_the_ID() ) ),
		/* translators: %s: Name of current post */
		sprintf( __( 'Continue reading %s', 'resonar' ), '<span class="screen-reader-text">' . get_the_title( get_the_ID() ) . '</span>' )
		);
	return ' &hellip; ' . $link;
}
add_filter( 'excerpt_more', 'resonar_excerpt_more' );
endif;
