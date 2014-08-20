<?php
/**
 * @package Make
 */

get_header();
global $post;
?>

<?php ttfmake_maybe_show_sidebar( 'left' ); ?>

<main id="site-main" class="site-main" role="main">
<?php if ( have_posts() ) : ?>

	<?php while ( have_posts() ) : the_post(); ?>
		<?php get_template_part( 'partials/content', apply_filters( 'ttfmake_template_content_page', 'page', $post ) ); ?>
		<?php get_template_part( 'partials/content', 'comments' ); ?>
	<?php endwhile; ?>

<?php endif; ?>
</main>

<?php ttfmake_maybe_show_sidebar( 'right' ); ?>

<?php get_footer(); ?>
