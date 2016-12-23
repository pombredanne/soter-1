<?php
/**
 * Cache provider interface.
 *
 * @package soter
 */

namespace SSNepenthe\Soter;

/**
 * Simple cache provider interface.
 */
interface Cache_Interface {
	/**
	 * Determine whether a given key exists in the cache.
	 *
	 * @param  string $id Cache key.
	 *
	 * @return bool
	 */
	public function contains( $id );

	/**
	 * Retrieve a given key from the cache.
	 *
	 * @param  string $id Cache key.
	 *
	 * @return mixed
	 */
	public function fetch( $id );

	/**
	 * Save data to the cache.
	 *
	 * @param  string $id       Cache key.
	 * @param  mixed  $data     Data to save.
	 * @param  int    $lifetime Cache entry lifetime.
	 *
	 * @return bool
	 */
	public function save( $id, $data, $lifetime );
}
