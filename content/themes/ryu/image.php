<?php
/**
 * The template for displaying image attachments.
 *
 * @package Ryu
 */

get_header();
$content_width = 1272;
?>
	<div id="primary" class="content-area image-attachment">
		<div id="content" class="site-content" role="main">

		<?php while ( have_posts() ) : the_post(); ?>

			<article id="post-<?php the_ID(); ?>" <?php post_class( 'clear' ); ?>>
				<div class="entry-wrap wrap clear">
					<div class="entry-content">
						<div class="entry-attachment">
							<div class="attachment">
								<?php ryu_the_attached_image(); ?>
							</div><!-- .attachment -->
						</div><!-- .entry-attachment -->

						<?php
							the_content();
							wp_link_pages( array(
								'before'      => '<div class="page-links"><span class="page-links-title">' . __( 'Pages:', 'ryu' ) . '</span>',
								'after'       => '</div>',
								'link_before' => '<span>',
								'link_after'  => '</span>',
							) );
						?>

						<div class="comment-status">
							<?php
								if ( comments_open() && pings_open() ) : // Comments and trackbacks open
									printf( __( '<a class="comment-link" href="#respond" title="Post a comment">Post a comment</a> or leave a trackback: <a class="trackback-link" href="%s" title="Trackback URL for your post" rel="trackback">Trackback URL</a>.', 'ryu' ), esc_url( get_trackback_url() ) );
								elseif ( ! comments_open() && pings_open() ) : // Only trackbacks open
									printf( __( 'Comments are closed, but you can leave a trackback: <a class="trackback-link" href="%s" title="Trackback URL for your post" rel="trackback">Trackback URL</a>.', 'ryu' ), esc_url( get_trackback_url() ) );
								elseif ( comments_open() && ! pings_open() ) : // Only comments open
									_e( 'Trackbacks are closed, but you can <a class="comment-link" href="#respond" title="Post a comment">post a comment</a>.', 'ryu' );
								elseif ( ! comments_open() && ! pings_open() ) : // Comments and trackbacks closed
									_e( 'Both comments and trackbacks are currently closed.', 'ryu' );
								endif;
							?>
						</div>
					</div><!-- .entry-content -->

					<header class="entry-header">
						<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
					</header><!-- .entry-header -->

					<footer class="entry-meta">
						<?php
							$metadata = wp_get_attachment_metadata();
							printf( __( '<span class="entry-date"><time class="entry-date" datetime="%1$s">%2$s</time></span><span class="full-size-link"><a href="%3$s" title="Link to full-size image">%4$s &times; %5$s</a></span><span class="parent-post-link"><a href="%6$s" title="Return to %7$s" rel="gallery">%8$s</a></span>', 'ryu' ),
								esc_attr( get_the_date( 'c' ) ),
								esc_html( get_the_date() ),
								esc_url( wp_get_attachment_url() ),
								$metadata['width'],
								$metadata['height'],
								esc_url( get_permalink( $post->post_parent ) ),
								esc_attr( strip_tags( get_the_title( $post->post_parent ) ) ),
								strip_tags( get_the_title( $post->post_parent ) )
							);

							edit_post_link( __( 'Edit', 'ryu' ), '<span class="edit-link">', '</span>' );
						?>
					</footer><!-- .entry-meta -->

					<?php if ( has_excerpt() ) : ?>
					<div class="entry-caption">
						<?php the_excerpt(); ?>
					</div><!-- .entry-caption -->
					<?php endif; ?>

				</div><!-- .entry-wrap -->
			</article><!-- #post-## -->

			<nav role="navigation" id="image-navigation" class="navigation-image clear double">
				<?php next_image_link( false, __( '<div class="next"><span class="meta-nav">&rarr;</span> <span class="text-nav">Next</span></div>', 'ryu' ) ); ?>
				<?php previous_image_link( false, __( '<div class="previous"><span class="meta-nav">&larr;</span> <span class="text-nav">Previous</span></div>', 'ryu' ) ); ?>
			</nav><!-- #image-navigation -->

			<?php
				// If comments are open or we have at least one comment, load up the comment template
				if ( comments_open() || '0' != get_comments_number() )
					comments_template();
			?>

		<?php endwhile; // end of the loop. ?>

		</div><!-- #content -->
	</div><!-- #primary -->

<?php get_footer(); ?>