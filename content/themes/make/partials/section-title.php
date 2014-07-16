<?php
/**
 * @package Make
 */
?>

<h1 class="section-title">
	<?php
	if ( is_archive() ) :
		if ( is_category() ) :
			printf(
				__( 'From %s', 'make' ),
				'<strong>' . single_cat_title( '', false ) . '</strong>'
			);

		elseif ( is_tag() ) :
			printf(
				__( 'Tagged %s', 'make' ),
				'<strong>' . single_tag_title( '', false ) . '</strong>'
			);

		elseif ( is_day() ) :
			printf(
				__( 'From %s', 'make' ),
				'<strong>' . get_the_date() . '</strong>'
			);

		elseif ( is_month() ) :
			printf(
				__( 'From %s', 'make' ),
				'<strong>' . get_the_date( _x( 'F Y', 'monthly archives date format', 'make' ) ) . '</strong>'
			);

		elseif ( is_year() ) :
			printf(
				__( 'From %s', 'make' ),
				'<strong>' . get_the_date( _x( 'Y', 'yearly archives date format', 'make' ) ) . '</strong>'
			);

		elseif ( is_author() ) :
			printf(
				__( 'By %s', 'make' ),
				'<strong class="vcard">' . get_the_author() . '</strong>'
			);

		else :
			_e( 'Archive', 'make' );

		endif;

	elseif ( is_search() ) :
		printf(
			__( 'Search for %s', 'make' ),
			'<strong class="search-keyword">' . get_search_query() . '</strong>'
		);
		printf(
			' &#45; <span class="search-result">%s</span>',
			sprintf(
				_n( '%s result found', '%s results found', absint( $wp_query->found_posts ), 'make' ),
				absint( $wp_query->found_posts )
			)
		);

	endif;
	?>
</h1>