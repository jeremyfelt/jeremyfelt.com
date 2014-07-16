<?php
/**
 * @package Make
 */
get_header();
?>

<main id="site-main" class="site-main" role="main">
	<article class="error-404 not-found">
		<header class="entry-header">
			<h1 class="entry-title">
				<?php _e( 'Oops! That page can&rsquo;t be found.', 'make' ); ?>
			</h1>
		</header>

		<div class="entry-content">
			<p>
				<?php _e( 'Maybe try searching this website:', 'make' ); ?>
			</p>
			<?php get_search_form(); ?>
		</div>
	</article>
</main>

<?php get_footer(); ?>