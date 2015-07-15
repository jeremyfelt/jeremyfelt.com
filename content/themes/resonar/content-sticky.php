<?php
/**
 * The default template for displaying content
 *
 * Used for both single and index/archive/search.
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
				<header class="entry-header">
					<div class="entry-header-inner">
						<div class="entry-date">
							<?php resonar_entry_date(); ?>
						</div>

						<?php the_title( sprintf( '<h2 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' ); ?>
					</div>
				</header>
			</div>
		</div>
	<?php else : ?>
		<header class="entry-header">
			<div class="entry-header-inner">
				<div class="entry-date">
					<?php resonar_entry_date(); ?>
				</div>

				<?php the_title( sprintf( '<h2 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' ); ?>
			</div>
		</header>
	<?php endif; ?>
</article><!-- #post-## -->
