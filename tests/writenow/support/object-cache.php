<?php
/**
 * A test-only object cache drop-in that lets the real Batcache run locally.
 *
 * Production uses memcached, which is not available here. Batcache needs a
 * cache that persists between requests, so this stores the `batcache` group
 * in files and keeps every other group in memory for the request, exactly
 * like core's default cache. The tests use it to prove that authenticated
 * WriteNow responses are never stored or served by Batcache.
 *
 * It is linked into the local test site only. It is never deployed.
 *
 * @package jeremyfelt
 */

// phpcs:disable WordPress.WP.AlternativeFunctions, WordPress.PHP.DiscouragedPHPFunctions, Universal.Files.SeparateFunctionsFromOO, Generic.Files.OneObjectStructurePerFile

require_once ABSPATH . 'wp-includes/class-wp-object-cache.php';

/**
 * Core's object cache, with the `batcache` group kept in files.
 */
class WriteNow_Test_Object_Cache extends WP_Object_Cache {

	/**
	 * Groups stored in files.
	 *
	 * @var string[]
	 */
	private $file_groups = [ 'batcache' ];

	/**
	 * Determine whether a group is stored in files.
	 *
	 * @param string $group The group.
	 * @return bool True if it is.
	 */
	private function is_file_group( $group ): bool {
		return in_array( (string) $group, $this->file_groups, true );
	}

	/**
	 * Provide the file that holds a key.
	 *
	 * @param int|string $key   The key.
	 * @param string     $group The group.
	 * @return string The file path.
	 */
	private function path( $key, $group ): string {
		$dir = defined( 'WRITENOW_TEST_CACHE_DIR' ) ? WRITENOW_TEST_CACHE_DIR : sys_get_temp_dir() . '/writenow-object-cache/';

		if ( ! is_dir( $dir ) ) {
			mkdir( $dir, 0777, true );
		}

		return $dir . md5( $group . ':' . $key ) . '.cache';
	}

	/**
	 * Read a stored value.
	 *
	 * @param int|string $key   The key.
	 * @param string     $group The group.
	 * @param bool       $found Whether the value was found.
	 * @return mixed The value, or false.
	 */
	private function read( $key, $group, &$found ) {
		$found = false;
		$path  = $this->path( $key, $group );

		if ( ! is_file( $path ) ) {
			return false;
		}

		$stored = unserialize( (string) file_get_contents( $path ) );

		if ( ! is_array( $stored ) || ( $stored['expires'] && $stored['expires'] < time() ) ) {
			return false;
		}

		$found = true;

		return $stored['value'];
	}

	/**
	 * Write a value.
	 *
	 * @param int|string $key    The key.
	 * @param mixed      $data   The value.
	 * @param string     $group  The group.
	 * @param int        $expire Seconds until it expires, 0 for never.
	 * @param bool       $only_new Whether to write only if the key is absent.
	 * @return bool True if written.
	 */
	private function write( $key, $data, $group, $expire, $only_new = false ): bool {
		$path = $this->path( $key, $group );

		if ( $only_new ) {
			$this->read( $key, $group, $found );

			if ( $found ) {
				return false;
			}

			if ( is_file( $path ) ) {
				unlink( $path );
			}

			$handle = @fopen( $path, 'x' ); // phpcs:ignore WordPress.PHP.NoSilencedErrors

			if ( false === $handle ) {
				return false;
			}

			fclose( $handle );
		}

		$stored = serialize(
			[
				'expires' => $expire ? time() + (int) $expire : 0,
				'value'   => $data,
			]
		);

		return false !== file_put_contents( $path . '.tmp', $stored ) && rename( $path . '.tmp', $path );
	}

	/**
	 * Add a value if the key is absent.
	 *
	 * @param int|string $key    The key.
	 * @param mixed      $data   The value.
	 * @param string     $group  The group.
	 * @param int        $expire Seconds until it expires.
	 * @return bool True if added.
	 */
	public function add( $key, $data, $group = 'default', $expire = 0 ) {
		return $this->is_file_group( $group ) ? $this->write( $key, $data, $group, $expire, true ) : parent::add( $key, $data, $group, $expire );
	}

	/**
	 * Set a value.
	 *
	 * @param int|string $key    The key.
	 * @param mixed      $data   The value.
	 * @param string     $group  The group.
	 * @param int        $expire Seconds until it expires.
	 * @return bool True if set.
	 */
	public function set( $key, $data, $group = 'default', $expire = 0 ) {
		return $this->is_file_group( $group ) ? $this->write( $key, $data, $group, $expire ) : parent::set( $key, $data, $group, $expire );
	}

	/**
	 * Replace a value if the key exists.
	 *
	 * @param int|string $key    The key.
	 * @param mixed      $data   The value.
	 * @param string     $group  The group.
	 * @param int        $expire Seconds until it expires.
	 * @return bool True if replaced.
	 */
	public function replace( $key, $data, $group = 'default', $expire = 0 ) {
		if ( ! $this->is_file_group( $group ) ) {
			return parent::replace( $key, $data, $group, $expire );
		}

		$this->read( $key, $group, $found );

		return $found && $this->write( $key, $data, $group, $expire );
	}

	/**
	 * Get a value.
	 *
	 * @param int|string $key   The key.
	 * @param string     $group The group.
	 * @param bool       $force Unused.
	 * @param bool|null  $found Whether the value was found.
	 * @return mixed The value, or false.
	 */
	public function get( $key, $group = 'default', $force = false, &$found = null ) {
		return $this->is_file_group( $group ) ? $this->read( $key, $group, $found ) : parent::get( $key, $group, $force, $found );
	}

	/**
	 * Delete a value.
	 *
	 * @param int|string $key        The key.
	 * @param string     $group      The group.
	 * @param bool       $deprecated Unused.
	 * @return bool True if deleted.
	 */
	public function delete( $key, $group = 'default', $deprecated = false ) {
		if ( ! $this->is_file_group( $group ) ) {
			return parent::delete( $key, $group, $deprecated );
		}

		$path = $this->path( $key, $group );

		return is_file( $path ) && unlink( $path );
	}

	/**
	 * Increment a numeric value.
	 *
	 * @param int|string $key    The key.
	 * @param int        $offset The amount.
	 * @param string     $group  The group.
	 * @return int|false The new value, or false.
	 */
	public function incr( $key, $offset = 1, $group = 'default' ) {
		if ( ! $this->is_file_group( $group ) ) {
			return parent::incr( $key, $offset, $group );
		}

		$value = $this->read( $key, $group, $found );

		if ( ! $found ) {
			return false;
		}

		$value = max( 0, (int) $value + (int) $offset );
		$this->write( $key, $value, $group, 0 );

		return $value;
	}

	/**
	 * Decrement a numeric value.
	 *
	 * @param int|string $key    The key.
	 * @param int        $offset The amount.
	 * @param string     $group  The group.
	 * @return int|false The new value, or false.
	 */
	public function decr( $key, $offset = 1, $group = 'default' ) {
		return $this->is_file_group( $group ) ? $this->incr( $key, -$offset, $group ) : parent::decr( $key, $offset, $group );
	}
}

/**
 * Set up the object cache.
 */
function wp_cache_init() {
	$GLOBALS['wp_object_cache'] = new WriteNow_Test_Object_Cache();
}

/**
 * Add to the cache.
 *
 * @param int|string $key    The key.
 * @param mixed      $data   The value.
 * @param string     $group  The group.
 * @param int        $expire Seconds until it expires.
 * @return bool True on success.
 */
function wp_cache_add( $key, $data, $group = '', $expire = 0 ) {
	return $GLOBALS['wp_object_cache']->add( $key, $data, $group, (int) $expire );
}

/**
 * Replace in the cache.
 *
 * @param int|string $key    The key.
 * @param mixed      $data   The value.
 * @param string     $group  The group.
 * @param int        $expire Seconds until it expires.
 * @return bool True on success.
 */
function wp_cache_replace( $key, $data, $group = '', $expire = 0 ) {
	return $GLOBALS['wp_object_cache']->replace( $key, $data, $group, (int) $expire );
}

/**
 * Set in the cache.
 *
 * @param int|string $key    The key.
 * @param mixed      $data   The value.
 * @param string     $group  The group.
 * @param int        $expire Seconds until it expires.
 * @return bool True on success.
 */
function wp_cache_set( $key, $data, $group = '', $expire = 0 ) {
	return $GLOBALS['wp_object_cache']->set( $key, $data, $group, (int) $expire );
}

/**
 * Get from the cache.
 *
 * @param int|string $key   The key.
 * @param string     $group The group.
 * @param bool       $force Unused.
 * @param bool|null  $found Whether the value was found.
 * @return mixed The value, or false.
 */
function wp_cache_get( $key, $group = '', $force = false, &$found = null ) {
	return $GLOBALS['wp_object_cache']->get( $key, $group, $force, $found );
}

/**
 * Delete from the cache.
 *
 * @param int|string $key   The key.
 * @param string     $group The group.
 * @return bool True on success.
 */
function wp_cache_delete( $key, $group = '' ) {
	return $GLOBALS['wp_object_cache']->delete( $key, $group );
}

/**
 * Increment a cached number.
 *
 * @param int|string $key    The key.
 * @param int        $offset The amount.
 * @param string     $group  The group.
 * @return int|false The new value, or false.
 */
function wp_cache_incr( $key, $offset = 1, $group = '' ) {
	return $GLOBALS['wp_object_cache']->incr( $key, $offset, $group );
}

/**
 * Decrement a cached number.
 *
 * @param int|string $key    The key.
 * @param int        $offset The amount.
 * @param string     $group  The group.
 * @return int|false The new value, or false.
 */
function wp_cache_decr( $key, $offset = 1, $group = '' ) {
	return $GLOBALS['wp_object_cache']->decr( $key, $offset, $group );
}

/**
 * Flush the in-memory cache. Files are left alone.
 *
 * @return bool True.
 */
function wp_cache_flush() {
	return $GLOBALS['wp_object_cache']->flush();
}

/**
 * Close the cache.
 *
 * @return bool True.
 */
function wp_cache_close() {
	return true;
}

/**
 * Add global groups.
 *
 * @param string|string[] $groups Groups.
 */
function wp_cache_add_global_groups( $groups ) {
	$GLOBALS['wp_object_cache']->add_global_groups( $groups );
}

/**
 * Add non-persistent groups. Everything but the file groups is non-persistent here.
 *
 * @param string|string[] $groups Groups.
 */
function wp_cache_add_non_persistent_groups( $groups ) {
	unset( $groups );
}

/**
 * Switch blogs.
 *
 * @param int $blog_id The blog ID.
 */
function wp_cache_switch_to_blog( $blog_id ) {
	$GLOBALS['wp_object_cache']->switch_to_blog( $blog_id );
}

/**
 * Reset the cache.
 */
function wp_cache_reset() {
	$GLOBALS['wp_object_cache']->reset();
}
