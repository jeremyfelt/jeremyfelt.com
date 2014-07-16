<?php
/**
 * @package Make
 */

if ( is_attachment() ) :
	// Always show post-header style image on Attachment view
	$thumb_option   = 'post-header';
	$thumbnail_id   = get_post()->ID;
	$thumbnail_size = 'full';
else:
	$thumb_key    = 'layout-' . ttfmake_get_view() . '-featured-images';
	$thumb_option = ttfmake_sanitize_choice( get_theme_mod( $thumb_key, ttfmake_get_default( $thumb_key ) ), $thumb_key );
	$thumbnail_id = get_post_thumbnail_id();

	if ( 'post-header' === $thumb_option ) :
		$thumbnail_size = 'large';
	else :
		$thumbnail_size = ( is_singular() ) ? 'medium' : 'thumbnail';
	endif;
endif;
?>

<?php if ( 'none' !== $thumb_option && ! empty( $thumbnail_id ) ) : ?>
<figure class="entry-thumbnail <?php if ( ! is_attachment() ) echo esc_attr( $thumb_option ); ?>">
	<?php if ( ! is_singular() ) : ?><a href="<?php the_permalink(); ?>" rel="bookmark"><?php endif; ?>
		<?php echo wp_get_attachment_image( $thumbnail_id, $thumbnail_size ); ?>
	<?php if ( ! is_singular() ) : ?></a><?php endif; ?>
	<?php if ( is_singular() && has_excerpt( $thumbnail_id ) ) : ?>
	<figcaption class="entry-thumbnail-caption">
		<?php echo ttfmake_sanitize_text( get_post( $thumbnail_id )->post_excerpt ); ?>
	</figcaption>
	<?php endif; ?>
</figure>
<?php endif; ?>
