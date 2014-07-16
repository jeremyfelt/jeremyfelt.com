<?php
/**
 * @package Make
 */

$taxonomy_view   = ttfmake_get_view();
$category_key    = 'layout-' . $taxonomy_view . '-show-categories';
$tag_key         = 'layout-' . $taxonomy_view . '-show-tags';
$category_option = (bool) get_theme_mod( $category_key, ttfmake_get_default( $category_key ) );
$tag_option      = (bool) get_theme_mod( $tag_key, ttfmake_get_default( $tag_key ) );
?>

<?php if ( ( $category_option || $tag_option ) && ( ( has_category() && ttfmake_categorized_blog() ) || has_tag() ) ) : ?>
	<?php
	$category_list   = get_the_category_list();
	$tag_list        = get_the_tag_list( '<ul class="post-tags"><li>', "</li>\n<li>", '</li></ul>' ); // Replicates category output
	$taxonomy_output = '';

	// Categories
	if ( $category_option && $category_list ) :
		$taxonomy_output .= __( '<i class="fa fa-file"></i> ', 'make' ) . '%1$s';
	endif;

	// Tags
	if ( $tag_option && $tag_list ) :
		$taxonomy_output .= __( '<i class="fa fa-tag"></i> ', 'make' ) . '%2$s';
	endif;

	// Output
	printf(
		$taxonomy_output,
		$category_list,
		$tag_list
	);
	?>
<?php endif; ?>