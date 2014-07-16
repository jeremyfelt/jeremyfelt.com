<?php
/**
 * @package Make
 */

get_header();
?>

<?php ttfmake_maybe_show_sidebar( 'left' ); ?>

<main id="site-main" class="site-main" role="main">
<?php if ( have_posts() ) : ?>

	<header class="section-header">
		<?php get_template_part( 'partials/section', 'title' ); ?>
		<?php get_template_part( 'partials/section', 'description' ); ?>
	</header>

	<?php while ( have_posts() ) : the_post(); ?>
		<?php get_template_part( 'partials/content', 'archive' ); ?>
	<?php endwhile; ?>

	<?php get_template_part( 'partials/nav', 'paging' ); ?>

<?php else : ?>
	<?php get_template_part( 'partials/content', 'none' ); ?>
<?php endif; ?>
</main>

<?php ttfmake_maybe_show_sidebar( 'right' ); ?>

<?php get_footer(); ?>
