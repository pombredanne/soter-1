<?php
/**
 * Cache provider interface.
 *
 * @package soter
 */

namespace SSNepenthe\Soter\Cache;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Simple cache provider interface.
 */
interface Cache_Interface {
	/**
	 * Determine whether a given key exists in the cache.
	 */
	public function contains( $key );

	/**
	 * Retrieve a given key from the cache.
	 */
	public function fetch( $key );

	/**
	 * Save data to the cache.
	 */
	public function save( $key, $data, $lifetime );
}
