<?php
/**
 * WP object cache wrapper.
 *
 * @package soter
 */

namespace SSNepenthe\Soter\Cache;

use SSNepenthe\Soter\Interfaces\Cache;

/**
 * WP object cache implementation for the Soter plugin. Meant for use with a
 * persistent object cache backend configured.
 */
class WPObjectCache implements Cache {
	const GROUP_KEY = 'ssn:wpvulndb';

	/**
	 * Constructor
	 *
	 * @throws  \RuntimeException When used outside of a WordPress context.
	 */
	public function __construct() {
		if ( ! function_exists( 'wp_cache_get' ) ) {
			throw new \RuntimeException(
				'WPObjectCache can only be used within WordPress'
			);
		}
	}

	/**
	 * Check if the given id exists in the cache.
	 *
	 * @param  string $id Cache key.
	 *
	 * @return bool
	 */
	public function contains( $id ) {
		if ( wp_cache_get( $id, self::GROUP_KEY ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Retrieve an entry from the cache by id.
	 *
	 * @param  string $id Cache key.
	 *
	 * @return bool
	 */
	public function fetch( $id ) {
		return wp_cache_get( $id, self::GROUP_KEY );
	}

	/**
	 * Save an entry to the cache.
	 *
	 * @param  string $id       Cache key.
	 * @param  mixed  $data     Data to save.
	 * @param  int    $lifetime How long to save the data for.
	 *
	 * @return bool
	 */
	public function save( $id, $data, $lifetime ) {
		return wp_cache_set( $id, $data, self::GROUP_KEY, $lifetime );
	}
}
