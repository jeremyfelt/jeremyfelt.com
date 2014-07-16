<?php
/**
 * @package Make
 */

$sidebar_id = esc_attr( apply_filters( 'ttfmake_sidebar_left', 'sidebar-left' ) );
?>
<section id="sidebar-left" class="widget-area <?php echo $sidebar_id; ?> <?php echo ( is_active_sidebar( $sidebar_id ) ) ? 'active' : 'inactive'; ?>" role="complementary">
	<?php if ( ! dynamic_sidebar( $sidebar_id ) ) : ?>
		&nbsp;
	<?php endif; ?>
</section>