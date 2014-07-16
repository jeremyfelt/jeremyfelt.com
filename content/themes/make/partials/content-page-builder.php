<?php
/**
 * @package Make
 */

// Header
ob_start();
get_template_part( 'partials/entry', 'meta-top' );
get_template_part( 'partials/entry', 'title' );
get_template_part( 'partials/entry', 'meta-before-content' );
$entry_header = trim( ob_get_clean() );

// Footer
ob_start();
get_template_part( 'partials/entry', 'meta-post-footer' );
get_template_part( 'partials/entry', 'sharing' );
$entry_footer = trim( ob_get_clean() );
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php if ( $entry_header ) : ?>
	<header class="entry-header">
		<?php echo $entry_header; ?>
	</header>
	<?php endif; ?>

	<div class="entry-content">
		<?php remove_filter( 'the_content', 'wpautop' ); ?>
		<?php get_template_part( 'partials/entry', 'content' ); ?>
		<?php add_filter( 'the_content', 'wpautop' ); ?>
	</div>

	<?php if ( $entry_footer ) : ?>
	<footer class="entry-footer">
		<?php echo $entry_footer; ?>
	</footer>
	<?php endif; ?>
</article>
