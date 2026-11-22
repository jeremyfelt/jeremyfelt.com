<?php
/**
 * A short-lived lock, so that two copies of the same request cannot both
 * create a post.
 *
 * A double tap, or the outbox flushing while the first attempt is still in
 * flight, can deliver the same note or like twice at nearly the same moment.
 * Both requests would look for an existing post, find none, and insert one
 * each. The lock is a row in the options table inserted with INSERT IGNORE,
 * which the table's unique key makes atomic, so it works with or without a
 * persistent object cache.
 *
 * @package jeremyfelt
 */

namespace JeremyFelt\Apps\WriteNow\Lock;

/**
 * Seconds after which a lock is considered abandoned, for example by a
 * request that died part way through.
 */
const LOCK_TIMEOUT = 30;

/**
 * Provide the option name used for a lock.
 *
 * @param string $key What is being locked.
 * @return string The option name.
 */
function get_option_name( string $key ): string {
	return '_writenow_lock_' . md5( $key );
}

/**
 * Try to take a lock.
 *
 * @param string $key What to lock, such as a client ID.
 * @return bool True if this request now holds the lock.
 */
function acquire( string $key ): bool {
	global $wpdb;

	$name = get_option_name( $key );
	$now  = time();

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- The atomic insert is the point; the options API cannot express it.
	$inserted = $wpdb->query( $wpdb->prepare( "INSERT IGNORE INTO {$wpdb->options} (option_name, option_value, autoload) VALUES (%s, %s, 'no')", $name, (string) $now ) );

	if ( 1 === $inserted ) {
		return true;
	}

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$held_since = (int) $wpdb->get_var( $wpdb->prepare( "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", $name ) );

	if ( $held_since > 0 && $now - $held_since <= LOCK_TIMEOUT ) {
		return false;
	}

	// The lock was abandoned. Take it over only if nobody else just did.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$updated = $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->options} SET option_value = %s WHERE option_name = %s AND option_value = %s", (string) $now, $name, (string) $held_since ) );

	return 1 === $updated;
}

/**
 * Release a lock taken with acquire().
 *
 * @param string $key What was locked.
 */
function release( string $key ): void {
	global $wpdb;

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name = %s", get_option_name( $key ) ) );
}

/**
 * Run a callback while holding a lock.
 *
 * When another request holds the lock, wait for it to finish, up to a few
 * seconds, then take the lock and run the callback. By then the other
 * request has created its post, and the callback's own duplicate check
 * finds it. If the lock is still held after waiting, return a retryable
 * 503 error rather than risk a duplicate.
 *
 * @param string   $key      What to lock.
 * @param callable $callback The work to do. Its return value is passed through.
 * @param int      $wait_ms  How long to wait for another holder, in milliseconds.
 * @return mixed The callback's return value, or a WP_Error if the lock was never free.
 */
function with_lock( string $key, callable $callback, int $wait_ms = 8000 ) {
	$waited = 0;

	while ( ! acquire( $key ) ) {
		if ( $waited >= $wait_ms ) {
			return new \WP_Error(
				'writenow_busy',
				'This is already being sent. Try again in a moment.',
				[ 'status' => 503 ]
			);
		}

		usleep( 250000 );
		$waited += 250;
	}

	try {
		return $callback();
	} finally {
		release( $key );
	}
}
