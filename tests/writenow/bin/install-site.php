<?php
/**
 * Install WordPress into a site prepared by setup-site.php, activate the
 * real plugins, and create test users with Application Passwords.
 *
 * Usage: php install-site.php <site-dir>
 *
 * The generated credentials are written to `<site-dir>/credentials.json`,
 * outside the repository. They exist only for this local site.
 *
 * @package jeremyfelt
 */

// phpcs:disable WordPress.WP.AlternativeFunctions

if ( 'cli' !== PHP_SAPI || $argc < 2 ) {
	fwrite( STDERR, "Usage: php install-site.php <site-dir>\n" );
	exit( 1 );
}

$site_dir = rtrim( $argv[1], '/' );

define( 'WP_INSTALLING', true );

$_SERVER['HTTP_HOST']   = '127.0.0.1';
$_SERVER['REQUEST_URI'] = '/';

require $site_dir . '/wordpress/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/upgrade.php';
require_once ABSPATH . 'wp-admin/includes/plugin.php';

// There is no mail server here, and nobody to email.
add_filter( 'pre_wp_mail', '__return_false' );

wp_install( 'WriteNow Test Site', 'admin', 'admin@example.com', false, '', 'password' );

update_option( 'permalink_structure', '/%year%/%monthnum%/%day%/%postname%/' );
update_option( 'timezone_string', 'America/Los_Angeles' );

foreach ( [ 'sqlite-database-integration/load.php', 'shortnotes/plugin.php', 'likes/plugin.php', 'writenow-test-helpers.php' ] as $plugin ) {
	$activated = activate_plugin( $plugin );

	if ( is_wp_error( $activated ) ) {
		fwrite( STDERR, "Could not activate $plugin: " . $activated->get_error_message() . "\n" );
		exit( 1 );
	}
}

switch_theme( 'writemore' );

$credentials = [];

/**
 * Create an Application Password and record it.
 *
 * @param WP_User $user  The user.
 * @param string  $label A key for credentials.json.
 * @param array   $store The credentials collected so far.
 */
function add_app_password( WP_User $user, string $label, array &$store ): void {
	/** @var array|WP_Error $created It is a WP_Error when Application Passwords are unavailable. */
	$created = WP_Application_Passwords::create_new_application_password( $user->ID, [ 'name' => 'WriteNow tests ' . $label ] );

	if ( is_wp_error( $created ) ) {
		fwrite( STDERR, 'Could not create an application password: ' . $created->get_error_message() . "\n" );
		exit( 1 );
	}

	$store[ $label ] = [
		'id'       => $user->ID,
		'username' => $user->user_login,
		'password' => $created[0],
	];
}

$admin = get_user_by( 'login', 'admin' );
add_app_password( $admin, 'admin', $credentials );

foreach ( [ 'editor', 'author', 'contributor', 'subscriber' ] as $role ) {
	$user_id = wp_insert_user(
		[
			'user_login' => $role,
			'user_pass'  => 'password',
			'user_email' => $role . '@example.com',
			'role'       => $role,
		]
	);

	if ( is_wp_error( $user_id ) ) {
		fwrite( STDERR, "Could not create $role: " . $user_id->get_error_message() . "\n" );
		exit( 1 );
	}

	add_app_password( get_user_by( 'id', $user_id ), $role, $credentials );
}

file_put_contents( $site_dir . '/credentials.json', wp_json_encode( $credentials, JSON_PRETTY_PRINT ) );

// Shortnotes and Likes register their post types on init, which ran before they were
// active. Deleting the stored rules makes the next request rebuild them with both.
delete_option( 'rewrite_rules' );

echo "Installed WordPress {$GLOBALS['wp_version']} at " . home_url() . "\n";
