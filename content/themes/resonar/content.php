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
	<?php if ( has_post_thumbnail() && ! post_password_required() ) : ?>
		<a class="post-thumbnail" href="<?php the_permalink(); ?>" aria-hidden="true">
			<?php the_post_thumbnail( 'post-thumbnail', array( 'alt' => get_the_title() ) ); ?>
		</a>
	<?php endif; ?>

	<?php if ( 'post' == get_post_type() ) : ?>
		<div class="entry-date">
			<?php resonar_entry_date(); ?>
		</div><!-- .entry-date -->
	<?php endif; ?>

	<?php the_title( sprintf( '<header class="entry-header"><h2 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2></header>' ); ?>

	<div class="entry-summary">
		<?php the_excerpt(); ?>
	</div><!-- .entry-summary -->
</article><!-- #post-## -->
