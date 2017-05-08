<?php
/**
 * The main plugin bootstrap.
 *
 * @package soter
 */

namespace SSNepenthe\Soter;

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
	protected $boot_queue = [];

	public function boot() {
		while ( count( $this->boot_queue ) ) {
			$provider = array_shift( $this->boot_queue );

			if ( method_exists( $provider, 'boot' ) ) {
				$provider->boot( $this );
			}
		}
	}

	public function register(
		ServiceProviderInterface $provider,
		array $values = array()
	) {
		parent::register( $provider, $values );

		$this->boot_queue[] = $provider;
	}
}
