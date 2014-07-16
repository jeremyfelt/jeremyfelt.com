<?php
/**
 * @package Make
 */

// Footer
ob_start();
get_template_part( 'partials/entry', 'author' );
get_template_part( 'partials/entry', 'sharing' );
$entry_footer = trim( ob_get_clean() );
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class="entry-header">
		<?php get_template_part( 'partials/entry', 'thumbnail' ); ?>
	</header>

	<div class="entry-content">
		<?php get_template_part( 'partials/entry', 'title' ); ?>
		<?php get_template_part( 'partials/entry', 'content' ); ?>
		<?php if ( '' !== $exif_data = ttfmake_get_exif_data() ) : ?>
		<div class="entry-exif">
			<h4 class="entry-exif-label"><?php _e( 'Technical Details', 'make' ); ?></h4>
			<?php echo $exif_data; ?>
		</div>
		<?php endif; ?>
	</div>

	<?php if ( $entry_footer ) : ?>
	<footer class="entry-footer">
		<?php echo $entry_footer; ?>
	</footer>
	<?php endif; ?>
</article>
