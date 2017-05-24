<?php
/**
 * Run scheduled transient cleanups.
 *
 * @package soter
 */

namespace Soter\Jobs;

use Soter_Core\Cache_Interface;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * This class handles expired cache garbage collection.
 *
 * It is intended for cleaning up transients that would otherwise be left lingering
 * after uninstalling a plugin or theme.
 *
 * This is probably not necessary and will likely be removed soon.
 *
 * WP already cleans out expired transients on DB upgrade which should be sufficient.
 */
class Collect_Transient_Garbage {
	/**
	 * Cache instance.
	 *
	 * @var Cache_Interface
	 */
	protected $cache;

	/**
	 * Class constructor.
	 *
	 * @param Cache_Interface $cache Cache instance.
	 */
	public function __construct( Cache_Interface $cache ) {
		$this->cache = $cache;
	}

	/**
	 * Deletes all expired cache entries.
	 */
	public function run() {
		$this->cache->flush_expired();
	}
}
