<?php
/**
 * Basic DI container.
 *
 * @package soter
 */

namespace SSNepenthe\Soter;

use Doctrine\Common\Cache\FilesystemCache;
use League\Container\Argument\RawArgument;

/**
 * This class provides easy access to an instance of the WPVulnDB client class.
 */
class Container {
	/**
	 * Singleton instance.
	 *
	 * @var null|Container
	 */
	protected static $instance = null;

	/**
	 * League container instance.
	 *
	 * @var \League\Container\Container
	 */
	protected $container;

	/**
	 * Container constructor.
	 */
	protected function __construct() {
		$this->container = new \League\Container\Container;

		$this->container->share( 'cache', FilesystemCache::class )
			->withArgument( new RawArgument(
				Config::get( 'cache.directory' )
			) );

		$this->container->share( 'http', Http\CurlClient::class );

		$this->container->share( 'client', WPVulnDB\Client::class )
			->withArgument( 'http' )
			->withArgument( 'cache' );
	}

	/**
	 * Private __clone method to prevent cloning the object.
	 */
	private function __clone() {
		// No diggity.
	}

	/**
	 * Private __wakeup method to prevent unserializing the object.
	 */
	private function __wakeup() {
		// No doubt.
	}

	/**
	 * Getter for singleton instance.
	 *
	 * @return Container
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new static;
		}

		return static::$instance;
	}

	/**
	 * Container getter.
	 *
	 * @param  string $key Container entry key.
	 *
	 * @return mixed
	 *
	 * @throws \InvalidArgumentException When $key is not a string.
	 */
	public static function get( $key ) {
		if ( ! is_string( $key ) ) {
			throw new \InvalidArgumentException( sprintf(
				'The key parameter is required to be string, was: %s',
				gettype( $key )
			) );
		}

		return static::instance()->container->get( $key );
	}
}
