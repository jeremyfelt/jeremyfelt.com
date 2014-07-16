<?php
/**
 * @package Make
 */

get_header();
?>

<?php ttfmake_maybe_show_sidebar( 'left' ); ?>

<main id="site-main" class="site-main" role="main">
<?php if ( have_posts() ) : ?>

	<?php while ( have_posts() ) : the_post(); ?>

		<?php
		if ( wp_attachment_is_image() ) :
			get_template_part( 'partials/content', 'image' );
		else :
			get_template_part( 'partials/content', 'attachment' );
		endif;
		?>

		<?php get_template_part( 'partials/content', 'comments' ); ?>

	<?php endwhile; ?>

<?php endif; ?>
</main>

<?php ttfmake_maybe_show_sidebar( 'right' ); ?>

<?php get_footer(); ?>
