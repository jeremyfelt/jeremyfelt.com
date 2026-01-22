<?php
/**
 * Block render template for the Quote Mention block.
 *
 * @package JeremyFelt\Plugins\BlockQuoteMentions
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block default content.
 * @var WP_Block $block      Block instance.
 */

$source_post_id   = $attributes['sourcePostId'] ?? 0;
$source_post_url  = $attributes['sourcePostUrl'] ?? '';
$source_post_title = $attributes['sourcePostTitle'] ?? '';
$quoted_content   = $attributes['quotedContentHtml'] ?? '';
$citation         = $attributes['citation'] ?? '';

// If no content, don't render anything.
if ( empty( $quoted_content ) && empty( $source_post_id ) ) {
	return;
}

// Get fresh URL and title if we have a post ID.
if ( $source_post_id > 0 ) {
	$source_post = get_post( $source_post_id );
	if ( $source_post ) {
		$source_post_url   = get_permalink( $source_post );
		$source_post_title = get_the_title( $source_post );
	}
}

// Build the citation text.
if ( empty( $citation ) && ! empty( $source_post_title ) ) {
	$citation = $source_post_title;
}

$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => 'wp-block-quote-mention',
	)
);
?>

<figure <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<blockquote class="wp-block-quote-mention__quote">
		<?php echo wp_kses_post( $quoted_content ); ?>
	</blockquote>
	<?php if ( ! empty( $citation ) || ! empty( $source_post_url ) ) : ?>
		<figcaption class="wp-block-quote-mention__citation">
			<?php if ( ! empty( $source_post_url ) ) : ?>
				<cite>
					<a href="<?php echo esc_url( $source_post_url ); ?>" class="wp-block-quote-mention__source-link">
						<?php echo esc_html( $citation ); ?>
					</a>
				</cite>
			<?php else : ?>
				<cite><?php echo esc_html( $citation ); ?></cite>
			<?php endif; ?>
		</figcaption>
	<?php endif; ?>
</figure>
