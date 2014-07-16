<?php
/**
 * @package Make
 */
?>

<?php if ( is_sticky() && $sticky_label = get_theme_mod( 'general-sticky-label', ttfmake_get_default( 'general-sticky-label' ) ) ) : ?>
	<div class="sticky-post-label-wrapper">
		<span class="sticky-post-label">
			<?php echo esc_html( wp_strip_all_tags( $sticky_label ) ); ?>
		</span>
	</div>
<?php endif;