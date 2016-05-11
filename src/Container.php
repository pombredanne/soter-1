<?php

namespace SSNepenthe\Soter;

use Doctrine\Common\Cache\FilesystemCache;
use League\Container\Argument\RawArgument;

class Container {
	protected static $instance = null;

	protected $container;

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

	private function __clone() {}

	private function __wakeup() {}

	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new static;
		}

		return static::$instance;
	}

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
