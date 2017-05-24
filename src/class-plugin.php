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

	public function register(
		ServiceProviderInterface $provider,
		array $values = array()
	) {
		parent::register( $provider, $values );

		$this->providers[] = $provider;

		return $this;
	}
}
