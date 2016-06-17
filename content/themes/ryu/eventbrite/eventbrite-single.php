<?php
/**
 * The Template for displaying all single Eventbrite events.
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<div id="content" class="site-content" role="main">
			<?php
				// Get our event based on the ID passed by query variable.
				$event = new Eventbrite_Query( array( 'p' => get_query_var( 'eventbrite_id' ) ) );

				if ( $event->have_posts() ) :
					while ( $event->have_posts() ) : $event->the_post(); ?>

						<article id="event-<?php the_ID(); ?>" <?php post_class( 'clear' ); ?>>
							<div class="entry-wrap wrap clear">

								<header class="entry-header">
									<h1 class="entry-title"><?php the_title(); ?></h1>
								</header><!-- .entry-header -->

								<footer class="entry-meta">
									<?php the_post_thumbnail(); ?>

									<?php eventbrite_event_meta(); ?>

									<?php eventbrite_edit_post_link( __( 'Edit', 'ryu' ), '<span class="edit-link">', '</span>' ); ?>
								</footer><!-- .entry-meta -->

								<div class="entry-content">
									<?php the_content(); ?>

									<?php eventbrite_ticket_form_widget(); ?>
								</div><!-- .entry-content -->

								<span class="entry-format-badge theme-genericon"><span class="screen-reader-text"><?php _e( 'Eventbrite event', 'ryu' ); ?></span></span>

							</div><!-- .entry-wrap -->
						</article><!-- #post-## -->

					<?php endwhile;

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
