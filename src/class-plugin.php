<?php
/**
 * Plugin class.
 *
 * @package soter
 */

namespace Soter;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Defines the plugin class.
 */
class Plugin extends Container {
	/**
	 * List of registered providers.
	 *
	 * @var ServiceProviderInterface[]
	 */
	protected $providers = [];

	/**
	 * Cached proxy objects.
	 *
	 * @var Service_Proxy[]
	 */
	protected $proxies = [];

	/**
	 * Loops through all providers calling the activate method on each.
	 *
	 * @return void
	 */
	public function activate() {
		foreach ( $this->providers as $provider ) {
			if ( method_exists( $provider, 'activate' ) ) {
				$provider->activate( $this );
			}
		}
	}

	/**
	 * Loops through all providers calling the boot method on each.
	 *
	 * @return void
	 */
	public function boot() {
		foreach ( $this->providers as $provider ) {
			if ( method_exists( $provider, 'boot' ) ) {
				$provider->boot( $this );
			}
		}
	}

	/**
	 * Loops through all providers calling the deactivate method on each.
	 *
	 * @return void
	 */
	public function deactivate() {
		foreach ( $this->providers as $provider ) {
			if ( method_exists( $provider, 'deactivate' ) ) {
				$provider->deactivate( $this );
			}
		}
	}

	/**
	 * Gets a proxy object for a given container entry.
	 *
	 * @param  string $key Container entry key.
	 *
	 * @return Service_Proxy
	 */
	public function proxy( $key ) {
		if ( isset( $this->proxies[ $key ] ) ) {
			return $this->proxies[ $key ];
		}

		$this->proxies[ $key ] = new Service_Proxy( $this, $key );

		return $this->proxies[ $key ];
	}

	/**
	 * Registers a service provider.
	 *
	 * @param  ServiceProviderInterface $provider Provider instance.
	 * @param  array                    $values   Values to customize the provider.
	 *
	 * @return static
	 */
	public function register(
		ServiceProviderInterface $provider,
		array $values = array()
	) {
		parent::register( $provider, $values );

		$this->providers[] = $provider;

		return $this;
	}
}
