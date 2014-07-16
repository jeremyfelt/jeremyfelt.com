<?php
/**
 * @package Make
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="entry-content">
		<?php get_template_part( 'partials/entry', 'title' ); ?>
		<?php get_template_part( 'partials/entry', 'content' ); ?>
		<p><?php _e( 'Download this file:', 'make' ); ?></p>
		<p><a href="<?php echo esc_url( wp_get_attachment_url() ); ?>" class="ttfmake-button ttfmake-download ttfmake-success"><?php echo esc_html( basename( $post->guid ) ); ?></a></p>
		<?php get_template_part( 'partials/entry', 'sharing' ); ?>
	</div>
</article>
