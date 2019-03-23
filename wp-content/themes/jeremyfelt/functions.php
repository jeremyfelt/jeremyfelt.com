<?php

add_action( 'wp_enqueue_scripts', 'jf_enqueue_parent_styles' );
function jf_enqueue_parent_styles() {
	wp_enqueue_style( 'jf-parent-style', get_template_directory_uri() . '/style.css' );
}

/**
 * Prints HTML with meta information for the categories, tags and comments.
 */
function jf_twentyseventeen_entry_footer() {

	/* translators: used between list items, there is a space after the comma */
	$separate_meta = __( ', ', 'twentyseventeen' );

	// Get Categories for posts.
	$categories_list = get_the_category_list( $separate_meta );

	// Get Tags for posts.
	$tags_list = get_the_tag_list( '', $separate_meta );

	// We don't want to output .entry-footer if it will be empty, so make sure its not.
	if ( ( ( twentyseventeen_categorized_blog() && $categories_list ) || $tags_list ) || get_edit_post_link() ) {

		echo '<footer class="entry-footer">';

			if ( 'post' === get_post_type() ) {
				if ( ( $categories_list && twentyseventeen_categorized_blog() ) || $tags_list ) {
					echo '<span class="cat-tags-links">';

						// Make sure there's more than one category before displaying.
						if ( $categories_list && twentyseventeen_categorized_blog() ) {
							echo '<span class="cat-links">' . twentyseventeen_get_svg( array( 'icon' => 'folder-open' ) ) . '<span class="screen-reader-text">' . __( 'Categories', 'twentyseventeen' ) . '</span>' . $categories_list . '</span>';
						}

						if ( $tags_list ) {
							echo '<span class="tags-links">' . twentyseventeen_get_svg( array( 'icon' => 'hashtag' ) ) . '<span class="screen-reader-text">' . __( 'Tags', 'twentyseventeen' ) . '</span>' . $tags_list . '</span>';
						}

					echo '</span>';
				}
			}

			?>
			<span class="cc-copyright" style="display: block; margin-top: 2rem;">
				"<?php the_title(); ?>", unless otherwise expressly stated, is licensed under a <a href="http://creativecommons.org/licenses/by-nc-sa/3.0/us/">Creative Commons Attribution-Noncommercial-Share Alike 3.0 United States License</a>.
			</span>
			<?php
			twentyseventeen_edit_link();

		echo '</footer> <!-- .entry-footer -->';
	}
}

class WSUWP_Embed_CodePen {
	public function __construct() {
		add_shortcode( 'wsu_codepen', array( $this, 'display_wsu_codepen' ) );
	}

	public function display_wsu_codepen( $atts ) {
		$defaults = array(
			'height' => 300,
			'theme_id' => 0,
			'pen' => '',
			'tab' => 'css',
			'user' => '',
		);
		$atts = shortcode_atts( $defaults, $atts );

		wp_enqueue_script( 'wsu-codepen', 'https://assets.codepen.io/assets/embed/ei.js', array(), false, true );

		ob_start();
		?><div data-height="<?php echo absint( $atts['height'] ); ?>"
		       data-theme-id="<?php echo absint( $atts['theme_id'] ); ?>"
		       data-slug-hash="<?php echo esc_attr( $atts['pen'] ); ?>"
		       data-default-tab="<?php echo esc_attr( $atts['tab'] ); ?>"
		       data-user="<?php echo esc_attr( $atts['user'] ); ?>"
		       class='codepen'></div>
		<?php
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}
}
new WSUWP_Embed_CodePen();
