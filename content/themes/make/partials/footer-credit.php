<?php
/**
 * @package Make
 */

$footer_text = ttfmake_sanitize_text( get_theme_mod( 'footer-text', ttfmake_get_default( 'footer-text' ) ) );
$footer_credit = apply_filters( 'ttfmake_show_footer_credit', true );
?>

<?php if ( $footer_text ) : ?>
<div class="footer-text">
	<?php echo $footer_text; ?>
</div>
<?php endif; ?>

<?php if ( true === $footer_credit ) : ?>
<div class="site-info">
	<span class="theme-name">Make: A WordPress template</span>
	<span class="theme-by"><?php _ex( 'by', 'attribution', 'make' ); ?></span>
	<span class="theme-author">
		<a title="The Theme Foundry <?php esc_attr_e( 'homepage', 'make' ); ?>" href="https://thethemefoundry.com/">
			The Theme Foundry
		</a>
	</span>
</div>
<?php endif; ?>
