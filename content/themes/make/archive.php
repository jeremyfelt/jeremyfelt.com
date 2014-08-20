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

	<header class="section-header">
		<?php get_template_part( 'partials/section', 'title' ); ?>
		<?php get_template_part( 'partials/section', 'description' ); ?>
	</header>

	<?php while ( have_posts() ) : the_post(); ?>
		<?php get_template_part( 'partials/content', apply_filters( 'ttfmake_template_content_archive', 'archive', $post ) ); ?>
	<?php endwhile; ?>

	<?php get_template_part( 'partials/nav', 'paging' ); ?>

<?php else : ?>
	<?php get_template_part( 'partials/content', 'none' ); ?>
<?php endif; ?>
</main>

<?php ttfmake_maybe_show_sidebar( 'right' ); ?>

<?php get_footer(); ?>
