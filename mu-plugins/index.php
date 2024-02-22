<?php
/**
 * @package jeremyfelt
 */

require_once __DIR__ . '/wp/emoji.php';
require_once __DIR__ . '/wp/head.php';
require_once __DIR__ . '/wp/styles.php';
require_once __DIR__ . '/wp/urls.php';

require_once __DIR__ . '/plugins/content-visibility.php';
require_once __DIR__ . '/plugins/indieweb.php';
require_once __DIR__ . '/plugins/micropub.php';

require_once __DIR__ . '/themes/writemore.php';

if ( file_exists( __DIR__ . '/local/hacks.php' ) ) {
	require_once __DIR__ . '/local/hacks.php';
}
