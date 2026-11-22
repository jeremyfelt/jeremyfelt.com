<?php
/**
 * Converter tests: every fixture's block markup, its round trip through
 * parse_blocks(), and what Shortnotes makes of it for Mastodon.
 *
 * @package jeremyfelt
 */

namespace JeremyFelt\Apps\WriteNow\Tests;

use function JeremyFelt\Apps\WriteNow\Markdown\image_block;
use function JeremyFelt\Apps\WriteNow\Markdown\serialize_attributes;
use function JeremyFelt\Apps\WriteNow\Markdown\to_blocks;

/**
 * Load the markdown fixtures.
 *
 * @return array[] Fixtures keyed by name: markdown, expected markup, expected text.
 */
function markdown_fixtures(): array {
	static $fixtures = null;

	if ( null === $fixtures ) {
		$fixtures = require dirname( __DIR__ ) . '/fixtures/markdown.php';
	}

	return $fixtures;
}

/**
 * Collect every block name in a parsed block tree.
 *
 * @param array[] $blocks Parsed blocks.
 * @return string[] Block names, without freeform whitespace.
 */
function block_names( array $blocks ): array {
	$names = [];

	foreach ( $blocks as $block ) {
		if ( null !== $block['blockName'] ) {
			$names[] = $block['blockName'];
		} elseif ( '' !== trim( $block['innerHTML'] ) ) {
			$names[] = 'freeform: ' . $block['innerHTML'];
		}

		$names = array_merge( $names, block_names( $block['innerBlocks'] ) );
	}

	return $names;
}

/**
 * Each fixture converts to exactly the expected markup.
 */
function test_markdown_fixtures_convert_to_expected_markup(): void {
	foreach ( markdown_fixtures() as $name => $fixture ) {
		assert_same( $fixture[1], to_blocks( $fixture[0] ), "Fixture: $name" );
	}
}

/**
 * Each expected output parses into known blocks and re-serializes unchanged,
 * and uses only blocks Shortnotes allows in a note.
 */
function test_markdown_fixtures_round_trip_through_parse_blocks(): void {
	$allowed = shortnotes( 'PostType\Note\filter_allowed_block_types', true, get_post( wp_insert_post( [ 'post_type' => 'shortnote' ] ) ) );

	foreach ( markdown_fixtures() as $name => $fixture ) {
		$markup = $fixture[1];
		$parsed = parse_blocks( $markup );

		assert_same( $markup, serialize_blocks( $parsed ), "Round trip: $name" );

		foreach ( block_names( $parsed ) as $block_name ) {
			assert_true( in_array( $block_name, (array) $allowed, true ), "Fixture $name uses $block_name, which a note does not allow" );
		}
	}
}

/**
 * Shortnotes' Mastodon text for each fixture is what we expect.
 */
function test_markdown_fixtures_through_transform_content(): void {
	foreach ( markdown_fixtures() as $name => $fixture ) {
		assert_same( $fixture[2], shortnotes( 'PostType\Note\transform_content', $fixture[1] ), "transform_content: $name" );
	}
}

/**
 * Block attributes are escaped exactly as core escapes them.
 */
function test_block_attributes_match_core_serialization(): void {
	$attributes = [
		'url'       => 'https://example.com/?a=1&b=<2>--"x"',
		'type'      => 'rich',
		'className' => 'a--b',
		'ordered'   => true,
		'start'     => 3,
		'emoji'     => '😀 ü',
	];

	assert_same( serialize_block_attributes( $attributes ), serialize_attributes( $attributes ) );
}

/**
 * An image block round-trips and escapes its attributes.
 */
function test_image_block_markup(): void {
	$markup = image_block( 42, 'https://example.com/a.jpg?x=1&y=2', 'A "quoted" <alt> & more', 'large' );

	assert_same(
		"<!-- wp:image {\"id\":42,\"sizeSlug\":\"large\",\"linkDestination\":\"none\"} -->\n<figure class=\"wp-block-image size-large\"><img src=\"https://example.com/a.jpg?x=1&amp;y=2\" alt=\"A &quot;quoted&quot; &lt;alt&gt; &amp; more\" class=\"wp-image-42\"/></figure>\n<!-- /wp:image -->",
		$markup
	);
	assert_same( $markup, serialize_blocks( parse_blocks( $markup ) ) );
}

/**
 * The converter never throws and never drops visible text, whatever it is given.
 */
function test_converter_survives_awkward_input(): void {
	$inputs = [
		str_repeat( '*', 5000 ),
		str_repeat( '_a', 3000 ),
		str_repeat( '[', 2000 ) . str_repeat( ']', 2000 ),
		str_repeat( '`', 999 ),
		str_repeat( "> \n", 50 ) . 'deep',
		str_repeat( '> ', 20 ) . 'nested',
		"\xC3\x28 invalid utf-8",
		"\u{FEFF}Leading byte order mark",
		str_repeat( 'Long text. ', 12000 ),
		'https://' . str_repeat( 'a', 3000 ) . '.com',
	];

	foreach ( $inputs as $input ) {
		$markup = to_blocks( $input );
		assert_same( $markup, serialize_blocks( parse_blocks( $markup ) ), 'Round trip of awkward input' );

		$text = html_entity_decode( wp_strip_all_tags( $markup ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );

		$words = preg_split( '/[\s*_`>\[\]]+/u', $input );

		foreach ( false === $words ? [] : $words as $word ) {
			if ( '' !== $word && 1 === preg_match( '//u', $word ) && strlen( $word ) < 200 ) {
				assert_contains( ltrim( $word, "\u{FEFF}" ), $text, 'No text is dropped' );
			}
		}
	}

	assert_contains( 'deep', to_blocks( str_repeat( "> \n", 50 ) . 'deep' ) );
	assert_contains( '�', to_blocks( "\xC3\x28 invalid utf-8" ), 'Invalid UTF-8 is replaced, not dropped' );
	assert_not_contains( "\u{FEFF}", to_blocks( "\u{FEFF}Leading byte order mark" ), 'A byte order mark is removed' );
}

/**
 * Write the fixtures where the block editor check can read them.
 */
function test_zz_export_fixtures_for_the_block_editor_check(): void {
	$markup = [];

	foreach ( markdown_fixtures() as $name => $fixture ) {
		if ( '' !== $fixture[1] ) {
			$markup[ $name ] = $fixture[1];
		}
	}

	$markup['image block'] = image_block( 42, 'https://example.com/a.jpg', 'Alt "text" & more', 'large' );

	$written = file_put_contents( getenv( 'WRITENOW_TEST_SITE' ) . '/block-fixtures.json', (string) wp_json_encode( $markup ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents

	assert_true( false !== $written, 'Fixtures were written' );
}
