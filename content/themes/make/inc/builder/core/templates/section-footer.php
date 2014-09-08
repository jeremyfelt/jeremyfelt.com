<?php
/**
 * @package Make
 */

/**
 * Filter the builder section footer links.
 *
 * @since 1.2.3.
 *
 * @param array    $links    The list of footer links.
 */
$links = apply_filters( 'make_builder_section_footer_links', array(
	100 => array(
		'href'  => '#',
		'class' => 'ttfmake-section-remove',
		'label' => __( 'Remove this section', 'make' )
	)
) );
ksort( $links );
?>
		<?php $i = 1; foreach ( $links as $link ) : ?>
		<?php
			$href  = ( isset( $link['href'] ) ) ? ' href="' . esc_url( $link['href'] ) . '"' : '';
			$id    = ( isset( $link['id'] ) ) ? ' id="' . esc_attr( $link['id'] ) . '"' : '';
			$label = ( isset( $link['label'] ) ) ? esc_html( $link['label'] ) : '';

			// Set up the class value with a base class
			$class_base = ' class="ttfmake-builder-section-footer-link';
			$class      = ( isset( $link['class'] ) ) ? $class_base . ' ' . esc_attr( $link['class'] ) . '"' : '"';
		?>
		<?php
		/**
		 * Execute code before the section footer links are displayed.
		 *
		 * @since 1.2.3.
		 */
		do_action( 'make_before_builder_footer_links' );
		?>
		<a<?php echo $href . $id . $class; ?>>
			<?php echo $label; ?>
		</a><?php if ( $i < count( $links ) ) : ?>&nbsp;&#124;&nbsp;<?php endif; ?>
		<?php $i++; endforeach; ?>
		<?php
		/**
		 * Execute code after the section footer links are displayed.
		 *
		 * @since 1.2.3.
		 */
		do_action( 'make_after_builder_footer_links' );
		?>
	</div>
<?php if ( ! isset( $ttfmake_is_js_template ) || true !== $ttfmake_is_js_template ) : ?>
</div>
<?php endif; ?>