<?php
/**
 * Customizations for Content Visibility.
 *
 * @package jeremyfelt
 */

namespace JeremyFelt\Plugins\ContentVisibility;

add_filter( 'content_visibility_load_public_css', '__return_false' );
