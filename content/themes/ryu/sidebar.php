<?php
/**
 * The Sidebar containing the main widget areas.
 *
 * @package Ryu
 */
?>
<div id="secondary" role="complementary"<?php ( function_exists( 'ryu_top_sidebar_class' ) ) ? ryu_top_sidebar_class() : ''; ?>>
	<?php if ( is_active_sidebar( 'sidebar-1' ) ) : ?>
	<div id="top-sidebar-one" class="widget-area" role="complementary">
		<?php dynamic_sidebar( 'sidebar-1' ); ?>
	</div><!-- #first .widget-area -->
	<?php endif; ?>

	<?php if ( is_active_sidebar( 'sidebar-2' ) ) : ?>
	<div id="top-sidebar-two" class="widget-area" role="complementary">
		<?php dynamic_sidebar( 'sidebar-2' ); ?>
	</div><!-- #second .widget-area -->
	<?php endif; ?>

	<?php if ( is_active_sidebar( 'sidebar-3' ) ) : ?>
	<div id="top-sidebar-three" class="widget-area" role="complementary">
		<?php dynamic_sidebar( 'sidebar-3' ); ?>
	</div><!-- #third .widget-area -->
	<?php endif; ?>

	<?php if ( is_active_sidebar( 'sidebar-4' ) ) : ?>
	<div id="top-sidebar-four" class="widget-area" role="complementary">
		<?php dynamic_sidebar( 'sidebar-4' ); ?>
	</div><!-- #four .widget-area -->
	<?php endif; ?>
</div><!-- #secondary -->