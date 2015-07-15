<?php
/**
 * The template used for displaying page content
 *
 * @package Resonar
 * @since Resonar 1.0
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php
		if ( has_post_thumbnail() && ! post_password_required() ) :
			$featuredimage = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'resonar-large' );
	?>
		<div class="entry-header-background" style="background-image:url(<?php echo esc_url( $featuredimage[0] ); ?>)">
			<div class="entry-header-wrapper">
				<header id="entry-header" class="entry-header">
					<div class="entry-header-inner">
						<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
					</div>
					<div class="scroll-indicator-wrapper">
						<a href="#" id="scroll-indicator" class="scroll-indicator"><span class="screen-reader-text"><?php _e( 'Scroll down to see more content', 'resonar' );?></span></a>
					</div>
				</header>
			</div>
		</div>
	<?php else : ?>
		<header class="entry-header">
			<div class="entry-header-inner">
				<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
			</div>
		</header>
	<?php endif; ?>

	<div class="entry-content-footer">
		<div class="entry-content">
			<?php the_content(); ?>
			<?php
				wp_link_pages( array(
					'before'      => '<div class="page-links"><span class="page-links-title">' . __( 'Pages:', 'resonar' ) . '</span>',
					'after'       => '</div>',
					'link_before' => '<span>',
					'link_after'  => '</span>',
					'pagelink'    => '<span class="screen-reader-text">' . __( 'Page', 'resonar' ) . ' </span>%',
					'separator'   => '<span class="screen-reader-text">, </span>',
				) );
			?>
		</div><!-- .entry-content -->

		<?php edit_post_link( __( 'Edit', 'resonar' ), '<footer class="entry-footer"><span class="edit-link">', '</span></footer><!-- .entry-footer -->' ); ?>
	</div>
</article><!-- #post-## -->
