<?php
/**
 * The template for displaying all Eventbrite events (index), and archives (sorted by organizer or venue).
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<div id="content" class="site-content" role="main">
			<header class="page-header">
				<div class="wrap">
					<h1 class="page-title">
						<?php the_title(); ?>
					</h1>
				</div>
			</header><!-- .page-header -->

			<?php
				// Set up and call our Eventbrite query.
				$events = new Eventbrite_Query( apply_filters( 'eventbrite_query_args', array(
					// 'display_private' => false, // boolean
					// 'limit' => null,            // integer
					// 'organizer_id' => null,     // integer
					// 'p' => null,                // integer
					// 'post__not_in' => null,     // array of integers
					// 'venue_id' => null,         // integer
				) ) );

				if ( $events->have_posts() ) :
					while ( $events->have_posts() ) : $events->the_post(); ?>

						<article id="event-<?php the_ID(); ?>" <?php post_class( 'clear' ); ?>>
							<div class="entry-wrap wrap clear">

								<header class="entry-header">
									<?php the_title( sprintf( '<h1 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h1>' ); ?>
								</header><!-- .entry-header -->

								<footer class="entry-meta">
									<?php the_post_thumbnail(); ?>

									<?php eventbrite_event_meta(); ?>

									<?php eventbrite_edit_post_link( __( 'Edit', 'ryu' ), '<span class="edit-link">', '</span>' ); ?>
								</footer><!-- .entry-meta -->

								<div class="entry-content clear">
									<?php eventbrite_ticket_form_widget(); ?>
								</div><!-- .entry-content -->

								<span class="entry-format-badge theme-genericon"><span class="screen-reader-text"><?php _e( 'Eventbrite event', 'ryu' ); ?></span></span>
							</div><!-- .entry-wrap -->
						</article><!-- #post-## -->

					<?php endwhile;

					// Previous/next post navigation.
					eventbrite_paging_nav( $events );

				else :
					// If no content, include the "No posts found" template.
					get_template_part( 'content', 'none' );

				endif;

				// Return $post to its rightful owner.
				wp_reset_postdata();
			?>

		</div><!-- #content -->
	</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
