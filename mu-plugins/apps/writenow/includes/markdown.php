<?php
/**
 * Convert the light markdown typed on a phone into block markup.
 *
 * These are pure functions: they make no WordPress calls, so they behave the
 * same in a unit test as on the site. The output is serialized exactly as
 * the block editor serializes the same blocks, so a note opens in the editor
 * without a validation warning.
 *
 * What is understood:
 *
 * - Paragraphs separated by blank lines. A single newline becomes `<br>`.
 * - `**bold**`, `*italic*`, `_italic_` and `` `code` ``.
 * - `[text](https://…)` links, and bare http(s) URLs, which are linked.
 * - A known oEmbed URL alone on its line becomes an embed block.
 * - Lines starting `- ` or `* ` (bulleted) or `1. ` (numbered) are lists.
 * - Lines starting `> ` are a quote.
 * - Fenced code with three backticks.
 * - A backslash before one of \ ` * _ [ ] makes it literal.
 *
 * A line that starts with `#` is ordinary text: notes have no headings and
 * hashtags are far more likely. All text is HTML-escaped, so raw HTML shows
 * as text. Anything unrecognised becomes a paragraph. The converter never
 * throws and never drops text.
 *
 * @package jeremyfelt
 */

namespace JeremyFelt\Apps\WriteNow\Markdown;

/**
 * The longest input the converter processes as markdown. Anything longer is
 * still kept, as escaped paragraphs, so nothing is lost.
 */
const MAX_MARKDOWN_LENGTH = 100000;

/**
 * Convert markdown to block markup.
 *
 * @param string $markdown The markdown source.
 * @param array  $options {
 *     Optional. Conversion options.
 *
 *     @type array[] $embed_providers Providers recognised for embeds, in the
 *                                    shape returned by get_default_embed_providers().
 * }
 * @return string Serialized block markup. An empty string for blank input.
 */
function to_blocks( string $markdown, array $options = [] ): string {
	$providers = $options['embed_providers'] ?? get_default_embed_providers();
	$markdown  = normalize( $markdown );

	if ( '' === trim( $markdown ) ) {
		return '';
	}

	if ( strlen( $markdown ) > MAX_MARKDOWN_LENGTH ) {
		return serialize_blocks_list( plain_paragraphs( $markdown ) );
	}

	return serialize_blocks_list( parse_lines( explode( "\n", $markdown ), $providers, 0 ) );
}

/**
 * Normalize line endings and remove characters that cannot appear in text.
 *
 * @param string $markdown The raw markdown.
 * @return string The normalized markdown.
 */
function normalize( string $markdown ): string {
	$markdown = str_replace( [ "\r\n", "\r" ], "\n", $markdown );

	// NUL is used internally as a placeholder delimiter and never carries meaning.
	$markdown = str_replace( "\0", '', $markdown );

	// A byte order mark is invisible and would otherwise end up in the first paragraph.
	if ( 0 === strpos( $markdown, "\xEF\xBB\xBF" ) ) {
		$markdown = substr( $markdown, 3 );
	}

	return $markdown;
}

/**
 * Provide the oEmbed providers the converter recognises.
 *
 * Each entry gives the block attributes the block editor assigns to that
 * provider's embed. `class_name` is only set where the editor would always
 * produce the same aspect ratio class.
 *
 * @return array[] A list of providers, each with `pattern`, `slug`, `type`,
 *                 `responsive` and `class_name`.
 */
function get_default_embed_providers(): array {
	return [
		[
			'pattern'    => '#^https?://((m|www)\.)?youtube\.com/watch\?#i',
			'slug'       => 'youtube',
			'type'       => 'video',
			'responsive' => true,
			'class_name' => 'wp-embed-aspect-16-9 wp-has-aspect-ratio',
		],
		[
			'pattern'    => '#^https?://youtu\.be/[^/?\#]+#i',
			'slug'       => 'youtube',
			'type'       => 'video',
			'responsive' => true,
			'class_name' => 'wp-embed-aspect-16-9 wp-has-aspect-ratio',
		],
		[
			'pattern'    => '#^https?://((m|www)\.)?youtube\.com/(playlist\?|shorts/|live/)#i',
			'slug'       => 'youtube',
			'type'       => 'video',
			'responsive' => true,
			'class_name' => '',
		],
		[
			'pattern'    => '#^https?://(www\.|player\.)?vimeo\.com/.+#i',
			'slug'       => 'vimeo',
			'type'       => 'video',
			'responsive' => true,
			'class_name' => '',
		],
		[
			'pattern'    => '#^https?://(open|play)\.spotify\.com/.+#i',
			'slug'       => 'spotify',
			'type'       => 'rich',
			'responsive' => true,
			'class_name' => '',
		],
		[
			'pattern'    => '#^https?://[a-z0-9-]+\.bandcamp\.com/(album|track)/.+#i',
			'slug'       => 'bandcamp',
			'type'       => 'rich',
			'responsive' => true,
			'class_name' => '',
		],
		[
			'pattern'    => '#^https?://bsky\.app/profile/[^/]+/post/[^/?\#]+#i',
			'slug'       => 'bluesky',
			'type'       => 'rich',
			'responsive' => false,
			'class_name' => '',
		],
		[
			// Mastodon-style status URLs: https://example.social/@name/123456789.
			'pattern'    => '#^https?://[a-z0-9.-]+\.[a-z]{2,}/@[a-z0-9_.]+(@[a-z0-9.-]+)?/\d+/?$#i',
			'slug'       => 'mastodon',
			'type'       => 'rich',
			'responsive' => false,
			'class_name' => '',
		],
	];
}

/**
 * Find the embed provider for a line that holds only a URL.
 *
 * @param string  $line      A line of markdown.
 * @param array[] $providers Recognised providers.
 * @return array|null The provider, with the URL added as `url`, or null.
 */
function match_embed( string $line, array $providers ): ?array {
	$url = trim( $line );

	if ( '' === $url || ! preg_match( '#^https?://[^\s<>"]+$#i', $url ) ) {
		return null;
	}

	foreach ( $providers as $provider ) {
		if ( isset( $provider['pattern'] ) && preg_match( $provider['pattern'], $url ) ) {
			$provider['url'] = $url;
			return $provider;
		}
	}

	return null;
}

/**
 * Recognise the start of a list item.
 *
 * @param string $line A line of markdown.
 * @return array|null `ordered`, `number` and `text` for a list item, or null.
 */
function match_list_item( string $line ): ?array {
	if ( preg_match( '/^\s*[-*]\s+(\S.*)$/u', $line, $matches ) ) {
		return [
			'ordered' => false,
			'number'  => 0,
			'text'    => $matches[1],
		];
	}

	if ( preg_match( '/^\s*(\d{1,9})[.)]\s+(\S.*)$/u', $line, $matches ) ) {
		return [
			'ordered' => true,
			'number'  => (int) $matches[1],
			'text'    => $matches[2],
		];
	}

	return null;
}

/**
 * Recognise a quote line.
 *
 * @param string $line A line of markdown.
 * @return string|null The line with its quote marker removed, or null.
 */
function match_quote( string $line ): ?string {
	if ( preg_match( '/^ {0,3}>[ \t]?(.*)$/u', $line, $matches ) ) {
		return $matches[1];
	}

	return null;
}

/**
 * Recognise an opening or closing code fence.
 *
 * @param string $line A line of markdown.
 * @return bool True if the line is a fence.
 */
function is_fence( string $line ): bool {
	return 1 === preg_match( '/^ {0,3}```/', $line );
}

/**
 * Determine whether a line starts a new block when it follows a paragraph line.
 *
 * Bulleted lists may interrupt a paragraph. A numbered list may only when it
 * starts at 1, so a sentence like "2025. What a year." stays text.
 *
 * @param string  $line      A line of markdown.
 * @param array[] $providers Recognised embed providers.
 * @return bool True if the line ends the paragraph before it.
 */
function interrupts_paragraph( string $line, array $providers ): bool {
	if ( is_fence( $line ) || null !== match_embed( $line, $providers ) ) {
		return true;
	}

	$quote = match_quote( $line );

	if ( null !== $quote && '' !== trim( $quote ) ) {
		return true;
	}

	$item = match_list_item( $line );

	return null !== $item && ( ! $item['ordered'] || 1 === $item['number'] );
}

/**
 * Parse lines of markdown into a list of serialized blocks.
 *
 * @param string[] $lines     Lines of markdown, without line endings.
 * @param array[]  $providers Recognised embed providers.
 * @param int      $depth     Quote nesting depth, which is limited.
 * @return string[] Serialized blocks.
 */
function parse_lines( array $lines, array $providers, int $depth ): array {
	$blocks = [];
	$count  = count( $lines );
	$i      = 0;

	while ( $i < $count ) {
		$line = $lines[ $i ];

		if ( '' === trim( $line ) ) {
			++$i;
			continue;
		}

		// Fenced code runs to the closing fence, or to the end when there is none.
		if ( is_fence( $line ) ) {
			$code = [];
			++$i;

			while ( $i < $count && ! is_fence( $lines[ $i ] ) ) {
				$code[] = $lines[ $i ];
				++$i;
			}

			++$i; // Skip the closing fence.

			$text = implode( "\n", $code );

			// A fence with nothing inside it is kept as the text that was typed.
			$blocks[] = '' === trim( $text ) ? paragraph_block( escape_html( trim( $line ) ) ) : code_block( $text );
			continue;
		}

		$embed = match_embed( $line, $providers );

		if ( null !== $embed ) {
			$blocks[] = embed_block( $embed );
			++$i;
			continue;
		}

		$quote = match_quote( $line );

		if ( null !== $quote && $depth < 3 ) {
			$quoted = [];

			while ( $i < $count ) {
				$inner = match_quote( $lines[ $i ] );

				if ( null === $inner ) {
					break;
				}

				$quoted[] = $inner;
				++$i;
			}

			$inner_blocks = parse_lines( $quoted, $providers, $depth + 1 );

			// A quote marker with nothing after it is kept as typed.
			$blocks[] = [] === $inner_blocks ? paragraph_block( escape_html( trim( $line ) ) ) : quote_block( $inner_blocks );
			continue;
		}

		$item = match_list_item( $line );

		if ( null !== $item ) {
			$ordered = $item['ordered'];
			$start   = $item['number'];
			$items   = [];

			while ( $i < $count ) {
				$next = match_list_item( $lines[ $i ] );

				if ( null !== $next ) {
					if ( $next['ordered'] !== $ordered ) {
						break;
					}

					$items[] = [ $next['text'] ];
					++$i;
					continue;
				}

				// A plain line straight after an item continues that item.
				if ( '' !== trim( $lines[ $i ] ) && ! is_fence( $lines[ $i ] ) && null === match_quote( $lines[ $i ] ) && null === match_embed( $lines[ $i ], $providers ) ) {
					$items[ count( $items ) - 1 ][] = trim( $lines[ $i ] );
					++$i;
					continue;
				}

				break;
			}

			$blocks[] = list_block( $items, $ordered, $start );
			continue;
		}

		// Everything else is a paragraph, which runs until a blank line or a new block.
		$paragraph = [ $line ];
		++$i;

		while ( $i < $count && '' !== trim( $lines[ $i ] ) && ! interrupts_paragraph( $lines[ $i ], $providers ) ) {
			$paragraph[] = $lines[ $i ];
			++$i;
		}

		$blocks[] = paragraph_block( inline_lines( $paragraph ) );
	}

	return $blocks;
}

/**
 * Convert very long input into escaped paragraphs without markdown parsing.
 *
 * @param string $markdown The markdown source.
 * @return string[] Serialized paragraph blocks.
 */
function plain_paragraphs( string $markdown ): array {
	$blocks = [];

	$chunks = preg_split( '/\n\s*\n/', $markdown );

	foreach ( false === $chunks ? [ $markdown ] : $chunks as $chunk ) {
		if ( '' !== trim( $chunk ) ) {
			$blocks[] = paragraph_block( str_replace( "\n", '<br>', escape_html( trim( $chunk ) ) ) );
		}
	}

	return $blocks;
}

/**
 * Convert the lines of a paragraph or list item to inline HTML.
 *
 * @param string[] $lines Lines of markdown.
 * @return string Inline HTML, with `<br>` between lines.
 */
function inline_lines( array $lines ): string {
	$lines = array_map( 'trim', $lines );

	return str_replace( "\n", '<br>', inline( implode( "\n", $lines ) ) );
}

/**
 * Convert inline markdown to HTML.
 *
 * Code spans, links, bare URLs and backslash escapes are found first and set
 * aside behind placeholders, the remaining text is escaped, emphasis is
 * applied, and the set-aside pieces are put back. Emphasis is matched
 * recursively, so the tags it produces are always properly nested.
 *
 * @param string $text       Markdown text.
 * @param bool   $allow_links Whether links may be created. False inside link text.
 * @return string Inline HTML.
 */
function inline( string $text, bool $allow_links = true ): string {
	$pieces = [];
	$out    = '';
	$length = strlen( $text );
	$plain  = '';
	$i      = 0;

	$stash = function ( string $html ) use ( &$pieces ): string {
		$pieces[] = $html;
		return "\0" . ( count( $pieces ) - 1 ) . "\0";
	};

	while ( $i < $length ) {
		$char = $text[ $i ];

		// Backslash escapes.
		if ( '\\' === $char && $i + 1 < $length && false !== strpos( '\\`*_[]', $text[ $i + 1 ] ) ) {
			$out  .= escape_html( $plain ) . $stash( escape_html( $text[ $i + 1 ] ) );
			$plain = '';
			$i    += 2;
			continue;
		}

		// Code spans.
		if ( '`' === $char ) {
			$run = strspn( $text, '`', $i );
			$end = strpos( $text, str_repeat( '`', $run ), $i + $run );

			// The closing run must be exactly as long as the opening run.
			while ( false !== $end && strspn( $text, '`', $end ) !== $run ) {
				$end = strpos( $text, str_repeat( '`', $run ), $end + strspn( $text, '`', $end ) );
			}

			if ( false !== $end && $end > $i + $run ) {
				$code  = substr( $text, $i + $run, $end - $i - $run );
				$out  .= escape_html( $plain ) . $stash( '<code>' . escape_html( $code ) . '</code>' );
				$plain = '';
				$i     = $end + $run;
				continue;
			}

			$plain .= str_repeat( '`', $run );
			$i     += $run;
			continue;
		}

		// Links written as [text](url).
		if ( '[' === $char && $allow_links ) {
			$link = match_link( $text, $i );

			if ( null !== $link ) {
				$out  .= escape_html( $plain ) . $stash( '<a href="' . escape_attribute( $link['url'] ) . '">' . inline( $link['text'], false ) . '</a>' );
				$plain = '';
				$i     = $link['end'];
				continue;
			}
		}

		// Bare URLs, which must not follow a letter or digit.
		if ( ( 'h' === $char || 'H' === $char ) && $allow_links && ! follows_word_character( $text, $i ) ) {
			$url = match_bare_url( $text, $i );

			if ( '' !== $url ) {
				$out  .= escape_html( $plain ) . $stash( '<a href="' . escape_attribute( $url ) . '">' . escape_html( $url ) . '</a>' );
				$plain = '';
				$i    += strlen( $url );
				continue;
			}
		}

		$plain .= $char;
		++$i;
	}

	$out .= escape_html( $plain );
	$out  = emphasis( $out );

	// Put set-aside pieces back. Pieces never contain placeholders of their own.
	$restored = preg_replace_callback(
		'/\x00(\d+)\x00/',
		function ( $matches ) use ( $pieces ) {
			return $pieces[ (int) $matches[1] ] ?? '';
		},
		$out
	);

	return is_string( $restored ) ? $restored : $out;
}

/**
 * Match a markdown link that starts at a given offset.
 *
 * The URL may contain balanced parentheses, as Wikipedia URLs often do, and
 * must use http or https.
 *
 * @param string $text   The text.
 * @param int    $offset Offset of the opening bracket.
 * @return array|null `text`, `url` and `end` (offset after the link), or null.
 */
function match_link( string $text, int $offset ): ?array {
	$close = strpos( $text, '](', $offset + 1 );

	if ( false === $close ) {
		return null;
	}

	$label = substr( $text, $offset + 1, $close - $offset - 1 );

	if ( '' === trim( $label ) || false !== strpos( $label, '[' ) || false !== strpos( $label, "\n" ) ) {
		return null;
	}

	$depth  = 0;
	$length = strlen( $text );
	$start  = $close + 2;

	for ( $i = $start; $i < $length; $i++ ) {
		$char = $text[ $i ];

		if ( false !== strpos( " \t\n", $char ) ) {
			return null;
		}

		if ( '(' === $char ) {
			++$depth;
		} elseif ( ')' === $char ) {
			if ( 0 === $depth ) {
				$url = substr( $text, $start, $i - $start );

				if ( ! is_http_url( $url ) ) {
					return null;
				}

				return [
					'text' => $label,
					'url'  => $url,
					'end'  => $i + 1,
				];
			}

			--$depth;
		}
	}

	return null;
}

/**
 * Match a bare http(s) URL that starts at a given offset.
 *
 * Trailing punctuation that is more likely to end the sentence than the URL
 * is left out, and so is a closing parenthesis without a matching opening one.
 *
 * @param string $text   The text.
 * @param int    $offset The offset to match at.
 * @return string The URL, or an empty string.
 */
function match_bare_url( string $text, int $offset ): string {
	if ( ! preg_match( '#\Ghttps?://[^\s<>"\x00]+#i', $text, $matches, 0, $offset ) ) {
		return '';
	}

	$url = $matches[0];

	while ( '' !== $url ) {
		$last = substr( $url, -1 );

		if ( false !== strpos( '.,:;!?\'"*_~', $last ) ) {
			$url = substr( $url, 0, -1 );
		} elseif ( ')' === $last && substr_count( $url, '(' ) < substr_count( $url, ')' ) ) {
			$url = substr( $url, 0, -1 );
		} elseif ( ']' === $last && substr_count( $url, '[' ) < substr_count( $url, ']' ) ) {
			$url = substr( $url, 0, -1 );
		} else {
			break;
		}
	}

	return is_http_url( $url ) ? $url : '';
}

/**
 * Determine whether a string is an absolute http(s) URL with a host.
 *
 * @param string $url The candidate URL.
 * @return bool True if it is.
 */
function is_http_url( string $url ): bool {
	return 1 === preg_match( '#^https?://[^\s/?\#]+([/?\#]\S*)?$#i', $url );
}

/**
 * Determine whether the character before an offset is part of a word.
 *
 * Any byte of a multibyte character counts as a word character, which is
 * right for letters in other scripts and harmless for the rest.
 *
 * @param string $text   The text.
 * @param int    $offset The offset.
 * @return bool True if the previous character is a letter, digit or multibyte character.
 */
function follows_word_character( string $text, int $offset ): bool {
	if ( 0 === $offset ) {
		return false;
	}

	$previous = $text[ $offset - 1 ];

	return 1 === preg_match( '/[A-Za-z0-9]/', $previous ) || ord( $previous ) >= 0x80;
}

/**
 * Apply bold and italic to escaped text.
 *
 * The earliest opening marker that has a matching closing marker wins. Its
 * contents and the text after it are processed recursively, so the output is
 * always properly nested. Markers without a partner stay as typed.
 *
 * @param string $html Escaped text, possibly with placeholders.
 * @return string Text with `<strong>` and `<em>` applied.
 */
function emphasis( string $html ): string {
	$length = strlen( $html );

	for ( $i = 0; $i < $length; $i++ ) {
		$char = $html[ $i ];

		if ( '*' !== $char && '_' !== $char ) {
			continue;
		}

		$match = match_emphasis( $html, $i );

		if ( null === $match ) {
			// Skip the rest of this run of markers.
			while ( $i + 1 < $length && $html[ $i + 1 ] === $char ) {
				++$i;
			}
			continue;
		}

		return substr( $html, 0, $i )
			. '<' . $match['tag'] . '>' . emphasis( $match['inner'] ) . '</' . $match['tag'] . '>'
			. emphasis( substr( $html, $match['end'] ) );
	}

	return $html;
}

/**
 * Match emphasis that opens at a given offset.
 *
 * @param string $html   Escaped text.
 * @param int    $offset Offset of the opening marker.
 * @return array|null `tag`, `inner` and `end`, or null.
 */
function match_emphasis( string $html, int $offset ): ?array {
	$patterns = [
		// **bold**
		[ 'strong', '/\G\*\*(?!\s)(.+?)(?<!\s)\*\*(?!\*)/su' ],
		// *italic*, not part of a ** pair.
		[ 'em', '/\G\*(?![\s*])(.+?)(?<![\s*])\*(?!\*)/su' ],
		// _italic_, only at word boundaries so snake_case stays as typed.
		[ 'em', '/\G_(?![\s_])(.+?)(?<![\s_])_(?![\p{L}\p{N}_])/su' ],
	];

	if ( '_' === $html[ $offset ] && ( follows_word_character( $html, $offset ) || ( $offset > 0 && '_' === $html[ $offset - 1 ] ) ) ) {
		return null;
	}

	foreach ( $patterns as $pattern ) {
		if ( 1 === preg_match( $pattern[1], $html, $matches, 0, $offset ) ) {
			return [
				'tag'   => $pattern[0],
				'inner' => $matches[1],
				'end'   => $offset + strlen( $matches[0] ),
			];
		}
	}

	return null;
}

/**
 * Escape text for HTML content, the way the block editor does.
 *
 * Only `&`, `<` and `>` are escaped, and existing entities are escaped too,
 * so "&amp;" typed by hand shows as typed. Invalid UTF-8 is replaced rather
 * than dropped.
 *
 * @param string $text Plain text.
 * @return string Escaped text.
 */
function escape_html( string $text ): string {
	return htmlspecialchars( $text, ENT_NOQUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8', true );
}

/**
 * Escape text for a double-quoted HTML attribute.
 *
 * @param string $text Plain text.
 * @return string Escaped text.
 */
function escape_attribute( string $text ): string {
	return htmlspecialchars( $text, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8', true );
}

/**
 * Serialize block attributes as the block editor does.
 *
 * This matches core's `serialize_block_attributes()`: characters that could
 * end the block comment or confuse an HTML parser are unicode-escaped.
 *
 * @param array $attributes Block attributes.
 * @return string JSON for the block comment.
 */
function serialize_attributes( array $attributes ): string {
	// json_encode() rather than wp_json_encode() keeps the converter free of WordPress calls.
	$json = (string) json_encode( $attributes, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR ); // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode

	$json = preg_replace( '/--/', '\\u002d\\u002d', $json ) ?? $json;
	$json = preg_replace( '/</', '\\u003c', $json ) ?? $json;
	$json = preg_replace( '/>/', '\\u003e', $json ) ?? $json;
	$json = preg_replace( '/&/', '\\u0026', $json ) ?? $json;

	// Regex: /\\"/ matches an escaped quote in the JSON.
	$json = preg_replace( '/\\\\"/', '\\u0022', $json ) ?? $json;

	return $json;
}

/**
 * Wrap HTML in block delimiters.
 *
 * @param string $name       Block name without the `core/` namespace.
 * @param string $html       The block's inner HTML.
 * @param array  $attributes Block attributes stored in the comment.
 * @return string The serialized block.
 */
function block( string $name, string $html, array $attributes = [] ): string {
	$attrs = [] === $attributes ? '' : ' ' . serialize_attributes( $attributes );

	return '<!-- wp:' . $name . $attrs . " -->\n" . $html . "\n<!-- /wp:" . $name . ' -->';
}

/**
 * Join serialized blocks the way the block editor does.
 *
 * @param string[] $blocks Serialized blocks.
 * @return string Block markup.
 */
function serialize_blocks_list( array $blocks ): string {
	return implode( "\n\n", $blocks );
}

/**
 * Build a paragraph block.
 *
 * @param string $html Inline HTML.
 * @return string The serialized block.
 */
function paragraph_block( string $html ): string {
	return block( 'paragraph', '<p>' . $html . '</p>' );
}

/**
 * Build a list block with list item inner blocks.
 *
 * @param array[] $items   Each item is a list of markdown lines.
 * @param bool    $ordered Whether the list is numbered.
 * @param int     $start   The first number of a numbered list.
 * @return string The serialized block.
 */
function list_block( array $items, bool $ordered, int $start ): string {
	$inner = [];

	foreach ( $items as $lines ) {
		$inner[] = block( 'list-item', '<li>' . inline_lines( $lines ) . '</li>' );
	}

	$attributes = [];
	$tag        = 'ul';
	$open       = '<ul class="wp-block-list">';

	if ( $ordered ) {
		$attributes['ordered'] = true;
		$tag                   = 'ol';
		$open                  = '<ol class="wp-block-list">';

		if ( 1 !== $start ) {
			$attributes['start'] = $start;
			$open                = '<ol start="' . $start . '" class="wp-block-list">';
		}
	}

	return block( 'list', $open . implode( "\n\n", $inner ) . '</' . $tag . '>', $attributes );
}

/**
 * Build a quote block around inner blocks.
 *
 * @param string[] $inner_blocks Serialized inner blocks.
 * @return string The serialized block.
 */
function quote_block( array $inner_blocks ): string {
	return block( 'quote', '<blockquote class="wp-block-quote">' . implode( "\n\n", $inner_blocks ) . '</blockquote>' );
}

/**
 * Build a code block.
 *
 * Like the block editor, opening square brackets are escaped so that they
 * cannot form a shortcode, and a URL alone on a line is escaped so that it
 * is not turned into an embed.
 *
 * @param string $code The code, as typed.
 * @return string The serialized block.
 */
function code_block( string $code ): string {
	$html = escape_html( $code );
	$html = str_replace( '[', '&#91;', $html );
	$html = preg_replace( '#^(\s*https?:)//([^\s<>"]+\s*)$#m', '$1&#47;&#47;$2', $html ) ?? $html;

	return block( 'code', '<pre class="wp-block-code"><code>' . $html . '</code></pre>' );
}

/**
 * Build an embed block.
 *
 * @param array $embed A provider from get_default_embed_providers() with `url`.
 * @return string The serialized block.
 */
function embed_block( array $embed ): string {
	$attributes = [
		'url'              => $embed['url'],
		'type'             => $embed['type'],
		'providerNameSlug' => $embed['slug'],
	];

	if ( ! empty( $embed['responsive'] ) ) {
		$attributes['responsive'] = true;
	}

	$class = 'wp-block-embed is-type-' . $embed['type'] . ' is-provider-' . $embed['slug'] . ' wp-block-embed-' . $embed['slug'];

	if ( ! empty( $embed['class_name'] ) ) {
		$attributes['className'] = $embed['class_name'];
		$class                  .= ' ' . $embed['class_name'];
	}

	$html = '<figure class="' . escape_attribute( $class ) . '"><div class="wp-block-embed__wrapper">' . "\n" . escape_html( $embed['url'] ) . "\n" . '</div></figure>';

	return block( 'embed', $html, $attributes );
}

/**
 * Build an image block for an attachment.
 *
 * @param int    $id        The attachment ID.
 * @param string $src       The image URL for the chosen size.
 * @param string $alt       Alternative text.
 * @param string $size_slug The image size, such as `large` or `full`.
 * @return string The serialized block.
 */
function image_block( int $id, string $src, string $alt, string $size_slug ): string {
	$attributes = [
		'id'              => $id,
		'sizeSlug'        => $size_slug,
		'linkDestination' => 'none',
	];

	$html = '<figure class="wp-block-image size-' . escape_attribute( $size_slug ) . '"><img src="' . escape_attribute( $src ) . '" alt="' . escape_attribute( $alt ) . '" class="wp-image-' . $id . '"/></figure>';

	return block( 'image', $html, $attributes );
}
