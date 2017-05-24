<?php
/**
 * The main plugin bootstrap.
 *
 * @package soter
 */

namespace Soter;

use Closure;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * This class acts as the plugin bootstrap, handling WordPress and WP-Cron.
 */
class Plugin extends Container {
	protected $providers = [];

	protected $proxies = [];

	public function activate() {
		foreach ( $this->providers as $provider ) {
			if ( method_exists( $provider, 'activate' ) ) {
				$provider->activate( $this );
			}
		}
	}

	public function boot() {
		foreach ( $this->providers as $provider ) {
			if ( method_exists( $provider, 'boot' ) ) {
				$provider->boot( $this );
			}
		}
	}

	public function deactivate() {
		foreach ( $this->providers as $provider ) {
			if ( method_exists( $provider, 'deactivate' ) ) {
				$provider->deactivate( $this );
			}
		}
	}

	public function proxy( $key ) {
		if ( isset( $this->proxies[ $key ] ) ) {
			return $this->proxies[ $key ];
		}

		$this->proxies[ $key ] = new Service_Proxy( $this, $key );

		return $this->proxies[ $key ];
	}

	public function register(
		ServiceProviderInterface $provider,
		array $values = array()
	) {
		parent::register( $provider, $values );

		$this->providers[] = $provider;

		return $this;
	}
}
