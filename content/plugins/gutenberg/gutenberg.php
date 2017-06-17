<?php
/**
 * Plugin Name: Gutenberg
 * Plugin URI: https://github.com/WordPress/gutenberg
 * Description: Prototyping since 1440. This is the development plugin for the new block editor in core. <strong>Meant for development, do not run on real sites.</strong>
 * Version: 0.1.0
 * Author: Gutenberg Team
 *
 * @package gutenberg
 */

### BEGIN AUTO-GENERATED DEFINES
define( 'GUTENBERG_VERSION', '0.1.0' );
define( 'GUTENBERG_GIT_COMMIT', 'c80487a49c4387c67a962228f15fe8411e2a09ea' );
### END AUTO-GENERATED DEFINES

// Load API functions.
require_once dirname( __FILE__ ) . '/lib/blocks.php';
require_once dirname( __FILE__ ) . '/lib/client-assets.php';
require_once dirname( __FILE__ ) . '/lib/i18n.php';
require_once dirname( __FILE__ ) . '/lib/register.php';

// Register server-side code for individual blocks.
require_once dirname( __FILE__ ) . '/lib/blocks/latest-posts.php';
