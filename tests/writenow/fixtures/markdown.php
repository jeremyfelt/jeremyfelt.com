<?php
/**
 * Markdown fixtures for the converter: input, the exact block markup
 * expected, and the plain text Shortnotes' transform_content() should
 * produce from it for Share on Mastodon.
 *
 * Expected markup is written out by hand, in the shape the block editor
 * serializes, using the small helpers below to keep it readable.
 *
 * @package jeremyfelt
 */

namespace JeremyFelt\Apps\WriteNow\Tests\Fixtures;

/**
 * A paragraph block.
 *
 * @param string $html Inline HTML.
 * @return string Block markup.
 */
function p( string $html ): string {
	return "<!-- wp:paragraph -->\n<p>$html</p>\n<!-- /wp:paragraph -->";
}

/**
 * A list item block.
 *
 * @param string $html Inline HTML.
 * @return string Block markup.
 */
function li( string $html ): string {
	return "<!-- wp:list-item -->\n<li>$html</li>\n<!-- /wp:list-item -->";
}

/**
 * A bulleted list block.
 *
 * @param string ...$items List item blocks.
 * @return string Block markup.
 */
function ul( string ...$items ): string {
	return "<!-- wp:list -->\n<ul class=\"wp-block-list\">" . implode( "\n\n", $items ) . "</ul>\n<!-- /wp:list -->";
}

/**
 * A numbered list block.
 *
 * @param string $attrs Attributes JSON, such as `{"ordered":true}`.
 * @param string $open  The opening tag.
 * @param string ...$items List item blocks.
 * @return string Block markup.
 */
function ol( string $attrs, string $open, string ...$items ): string {
	return "<!-- wp:list $attrs -->\n$open" . implode( "\n\n", $items ) . "</ol>\n<!-- /wp:list -->";
}

/**
 * A quote block.
 *
 * @param string ...$blocks Inner blocks.
 * @return string Block markup.
 */
function quote( string ...$blocks ): string {
	return "<!-- wp:quote -->\n<blockquote class=\"wp-block-quote\">" . implode( "\n\n", $blocks ) . "</blockquote>\n<!-- /wp:quote -->";
}

/**
 * A code block.
 *
 * @param string $html Escaped code.
 * @return string Block markup.
 */
function code( string $html ): string {
	return "<!-- wp:code -->\n<pre class=\"wp-block-code\"><code>$html</code></pre>\n<!-- /wp:code -->";
}

/**
 * An embed block.
 *
 * @param string $url        The URL.
 * @param string $type       `video` or `rich`.
 * @param string $slug       The provider slug.
 * @param bool   $responsive Whether the embed is responsive.
 * @param string $class_name Extra classes.
 * @return string Block markup.
 */
function embed( string $url, string $type, string $slug, bool $responsive = true, string $class_name = '' ): string {
	$attrs = '{"url":"' . $url . '","type":"' . $type . '","providerNameSlug":"' . $slug . '"' . ( $responsive ? ',"responsive":true' : '' ) . ( $class_name ? ',"className":"' . $class_name . '"' : '' ) . '}';
	$class = "wp-block-embed is-type-$type is-provider-$slug wp-block-embed-$slug" . ( $class_name ? " $class_name" : '' );

	return "<!-- wp:embed $attrs -->\n<figure class=\"$class\"><div class=\"wp-block-embed__wrapper\">\n" . str_replace( '&', '&amp;', $url ) . "\n</div></figure>\n<!-- /wp:embed -->";
}

/**
 * Join blocks as the editor does.
 *
 * @param string ...$blocks Blocks.
 * @return string Block markup.
 */
function blocks( string ...$blocks ): string {
	return implode( "\n\n", $blocks );
}

const YOUTUBE_CLASS = 'wp-embed-aspect-16-9 wp-has-aspect-ratio';

return [
	'empty input'                   => [ '', '', '' ],
	'only whitespace'               => [ "  \n\t\n   ", '', '' ],
	'one paragraph'                 => [ 'Hello there.', p( 'Hello there.' ), 'Hello there.' ],
	'paragraphs and line breaks'    => [
		"First line\nsecond line\n\nNew paragraph",
		blocks( p( 'First line<br>second line' ), p( 'New paragraph' ) ),
		"First linesecond line\n\nNew paragraph",
	],
	'windows line endings'          => [ "One\r\nTwo\r\n\r\nThree", blocks( p( 'One<br>Two' ), p( 'Three' ) ), "OneTwo\n\nThree" ],
	'extra blank lines'             => [ "\n\nOne\n\n\n\nTwo\n\n", blocks( p( 'One' ), p( 'Two' ) ), "One\n\nTwo" ],
	'bold italic code'              => [
		'Some **bold**, *italic*, _also italic_ and `code`.',
		p( 'Some <strong>bold</strong>, <em>italic</em>, <em>also italic</em> and <code>code</code>.' ),
		'Some bold, italic, also italic and code.',
	],
	'nested emphasis'               => [ '***Both*** and **bold with *italic* inside**', p( '<strong><em>Both</em></strong> and <strong>bold with <em>italic</em> inside</strong>' ), 'Both and bold with italic inside' ],
	'emphasis across lines'         => [ "**bold\nstill bold**", p( '<strong>bold<br>still bold</strong>' ), 'boldstill bold' ],
	'unbalanced markers'            => [ 'An **unclosed bold and a lone * star and _under', p( 'An **unclosed bold and a lone * star and _under' ), 'An **unclosed bold and a lone * star and _under' ],
	'mis-nested markers'            => [ '**a *b** c*', p( '<strong>a *b</strong> c*' ), 'a *b c*' ],
	'snake case is not italic'      => [ 'Call some_function_name now', p( 'Call some_function_name now' ), 'Call some_function_name now' ],
	'unclosed code span'            => [ 'A `tick without a partner', p( 'A `tick without a partner' ), 'A `tick without a partner' ],
	'code span keeps markdown'      => [ 'Use `**not bold**` and `a < b`', p( 'Use <code>**not bold**</code> and <code>a &lt; b</code>' ), 'Use **not bold** and a < b' ],
	'backslash escapes'             => [ 'Literal \*stars\* and C:\Users', p( 'Literal *stars* and C:\Users' ), 'Literal *stars* and C:\Users' ],
	'markdown link'                 => [
		'Read [this post](https://example.com/post?a=1&b=2) today.',
		p( 'Read <a href="https://example.com/post?a=1&amp;b=2">this post</a> today.' ),
		'Read this post today. https://example.com/post?a=1&amp;b=2',
	],
	'link text with emphasis'       => [ '[**Bold** link](https://example.com/)', p( '<a href="https://example.com/"><strong>Bold</strong> link</a>' ), 'Bold link https://example.com/' ],
	'link with parentheses'         => [
		'See [Foo](https://en.wikipedia.org/wiki/Foo_(bar)).',
		p( 'See <a href="https://en.wikipedia.org/wiki/Foo_(bar)">Foo</a>.' ),
		'See Foo. https://en.wikipedia.org/wiki/Foo_(bar)',
	],
	'unsafe link is text'           => [ '[click](javascript:alert(1))', p( '[click](javascript:alert(1))' ), '[click](javascript:alert(1))' ],
	'bare url'                      => [
		'Look at https://example.com/a-page, it is good.',
		p( 'Look at <a href="https://example.com/a-page">https://example.com/a-page</a>, it is good.' ),
		'Look at https://example.com/a-page, it is good. https://example.com/a-page',
	],
	'bare url with parentheses'     => [
		'(see https://en.wikipedia.org/wiki/Foo_(bar))',
		p( '(see <a href="https://en.wikipedia.org/wiki/Foo_(bar)">https://en.wikipedia.org/wiki/Foo_(bar)</a>)' ),
		'(see https://en.wikipedia.org/wiki/Foo_(bar)) https://en.wikipedia.org/wiki/Foo_(bar)',
	],
	'bare url in bold'              => [ '**https://example.com/**', p( '<strong><a href="https://example.com/">https://example.com/</a></strong>' ), 'https://example.com/ https://example.com/' ],
	'url alone on its line'         => [
		'https://example.com/not-an-embed',
		p( '<a href="https://example.com/not-an-embed">https://example.com/not-an-embed</a>' ),
		'https://example.com/not-an-embed https://example.com/not-an-embed',
	],
	'youtube embed'                 => [ 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', embed( 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'video', 'youtube', true, YOUTUBE_CLASS ), 'https://www.youtube.com/watch?v=dQw4w9WgXcQ' ],
	'youtube short link'            => [ 'https://youtu.be/dQw4w9WgXcQ', embed( 'https://youtu.be/dQw4w9WgXcQ', 'video', 'youtube', true, YOUTUBE_CLASS ), 'https://youtu.be/dQw4w9WgXcQ' ],
	'vimeo embed'                   => [ 'https://vimeo.com/76979871', embed( 'https://vimeo.com/76979871', 'video', 'vimeo' ), 'https://vimeo.com/76979871' ],
	'spotify embed'                 => [ 'https://open.spotify.com/track/4uLU6hMCjMI75M1A2tKUQC', embed( 'https://open.spotify.com/track/4uLU6hMCjMI75M1A2tKUQC', 'rich', 'spotify' ), 'https://open.spotify.com/track/4uLU6hMCjMI75M1A2tKUQC' ],
	'bandcamp embed'                => [ 'https://artist.bandcamp.com/album/an-album', embed( 'https://artist.bandcamp.com/album/an-album', 'rich', 'bandcamp' ), 'https://artist.bandcamp.com/album/an-album' ],
	'bluesky embed'                 => [ 'https://bsky.app/profile/jeremyfelt.com/post/3kabc123', embed( 'https://bsky.app/profile/jeremyfelt.com/post/3kabc123', 'rich', 'bluesky', false ), 'https://bsky.app/profile/jeremyfelt.com/post/3kabc123' ],
	'mastodon embed'                => [ 'https://indieweb.social/@jeremyfelt/113456789012345678', embed( 'https://indieweb.social/@jeremyfelt/113456789012345678', 'rich', 'mastodon', false ), 'https://indieweb.social/@jeremyfelt/113456789012345678' ],
	'embed between paragraph lines' => [
		"Listen to this:\nhttps://open.spotify.com/track/abc\nSo good.",
		blocks( p( 'Listen to this:' ), embed( 'https://open.spotify.com/track/abc', 'rich', 'spotify' ), p( 'So good.' ) ),
		"Listen to this:\n\nhttps://open.spotify.com/track/abc\n\nSo good.",
	],
	'embed url inside text'         => [
		'Watch https://www.youtube.com/watch?v=abc now',
		p( 'Watch <a href="https://www.youtube.com/watch?v=abc">https://www.youtube.com/watch?v=abc</a> now' ),
		'Watch https://www.youtube.com/watch?v=abc now https://www.youtube.com/watch?v=abc',
	],
	'bulleted list'                 => [ "- One\n- Two\n* Three", ul( li( 'One' ), li( 'Two' ), li( 'Three' ) ), "- One\n- Two\n- Three" ],
	'list after paragraph line'     => [ "Things:\n- a **b**\n- c", blocks( p( 'Things:' ), ul( li( 'a <strong>b</strong>' ), li( 'c' ) ) ), "Things:\n\n- a b\n- c" ],
	'list item continuation'        => [ "- First item\n  carries on\n- Second", ul( li( 'First item<br>carries on' ), li( 'Second' ) ), "- First itemcarries on\n- Second" ],
	'numbered list'                 => [ "1. One\n2. Two", ol( '{"ordered":true}', '<ol class="wp-block-list">', li( 'One' ), li( 'Two' ) ), "- One\n- Two" ],
	'numbered list from three'      => [ "3. Three\n4. Four", ol( '{"ordered":true,"start":3}', '<ol start="3" class="wp-block-list">', li( 'Three' ), li( 'Four' ) ), "- Three\n- Four" ],
	'year in a sentence'            => [ "Looking back.\n2025. What a year.", p( 'Looking back.<br>2025. What a year.' ), 'Looking back.2025. What a year.' ],
	'bullets then numbers'          => [ "- a\n1. b", blocks( ul( li( 'a' ) ), ol( '{"ordered":true}', '<ol class="wp-block-list">', li( 'b' ) ) ), "- a\n\n- b" ],
	'quote'                         => [ "> A quoted line\n> and another\n\nAfter.", blocks( quote( p( 'A quoted line<br>and another' ) ), p( 'After.' ) ), "“A quoted lineand another”\n\nAfter." ],
	'quote with paragraphs'         => [ "> One\n>\n> Two", quote( p( 'One' ), p( 'Two' ) ), '“OneTwo”' ],
	'quote with a list'             => [ "> - a\n> - b", quote( ul( li( 'a' ), li( 'b' ) ) ), "“- a\n- b”" ],
	'lone quote marker'             => [ '>', p( '&gt;' ), '>' ],
	'fenced code'                   => [
		"```\nconst a = 1 < 2;\n[gallery]\nhttps://example.com\n```",
		code( "const a = 1 &lt; 2;\n&#91;gallery]\nhttps:&#47;&#47;example.com" ),
		"const a = 1 < 2;\n[gallery]\nhttps://example.com",
	],
	'fenced code with language'     => [ "```php\necho 1;\n```\nAfter", blocks( code( 'echo 1;' ), p( 'After' ) ), "echo 1;\n\nAfter" ],
	'unclosed fence'                => [ "```\ncode to the end\nstill code", code( "code to the end\nstill code" ), "code to the end\nstill code" ],
	'empty fence'                   => [ '```', p( '```' ), '```' ],
	'hashtag at line start'         => [ "#100DaysToOffload\n# Not a heading", p( '#100DaysToOffload<br># Not a heading' ), '#100DaysToOffload# Not a heading' ],
	'html is shown as text'         => [
		'<script>alert("x")</script> <b>bold?</b> & &amp;',
		p( '&lt;script&gt;alert("x")&lt;/script&gt; &lt;b&gt;bold?&lt;/b&gt; &amp; &amp;amp;' ),
		// Shortnotes decodes entities before stripping tags, so typed HTML vanishes from the Mastodon text.
		'bold? & &amp;',
	],
	'block delimiters as text'      => [ '<!-- wp:paragraph --> sneaky -->', p( '&lt;!-- wp:paragraph --&gt; sneaky --&gt;' ), 'sneaky -->' ],
	'emoji'                         => [ "😀 Emoji, ünïcödé and 中文 **粗体**\n👍🏽", p( '😀 Emoji, ünïcödé and 中文 <strong>粗体</strong><br>👍🏽' ), '😀 Emoji, ünïcödé and 中文 粗体👍🏽' ],
	'trailing spaces'               => [ "Line one   \n   line two  ", p( 'Line one<br>line two' ), 'Line oneline two' ],
	'nul bytes removed'             => [ "a\0b", p( 'ab' ), 'ab' ],
];
