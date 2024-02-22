<?php
/**
 * Customizations of the writemore theme.
 *
 * @package jeremyfelt
 */

namespace JeremyFelt\Writemore;

add_filter( 'writemore_author_avatar_alt_text', __NAMESPACE__ . '\filter_author_avatar_alt_text' );

/**
 * Filter the alt text for the author avatar.
 *
 * @return string Modified alt text.
 */
function filter_author_avatar_alt_text(): string {
	return "Jeremy's profile photo: a selfie taken while walking through Berlin.";
}
