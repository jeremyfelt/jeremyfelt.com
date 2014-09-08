<?php
/**
 * @package Make
 */

/**
 * Filter the sidebar ID to allow developers to programmatically change the sidebar displayed.
 *
 * @since 1.2.3.
 *
 * @param string    $footer_id    The ID of the current footer being generated.
 */
$sidebar_id = apply_filters( 'make_footer_1', 'footer-1' );
$sidebar_id = esc_attr( $sidebar_id );
?>
<section id="footer-1" class="widget-area <?php echo $sidebar_id; ?> <?php echo ( is_active_sidebar( $sidebar_id ) ) ? 'active' : 'inactive'; ?>" role="complementary">
	<?php if ( ! dynamic_sidebar( $sidebar_id ) ) : ?>
		&nbsp;
	<?php endif; ?>
</section>