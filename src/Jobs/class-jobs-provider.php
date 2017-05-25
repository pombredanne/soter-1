<?php
/**
 * Jobs_Provider class.
 *
 * @package soter
 */

namespace Soter\Jobs;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Defines the jobs provider class.
 */
class Jobs_Provider implements ServiceProviderInterface {
	/**
	 * Provider-specific boot logic.
	 *
	 * @param  Container $container Plugin container instance.
	 *
	 * @return void
	 */
	public function boot( Container $container ) {
		add_action(
			'soter_run_check',
			[ $container->proxy( 'jobs.check_site' ), 'run' ]
		);
		add_action(
			'wp_scheduled_delete',
			[ $container->proxy( 'jobs.gc_transients' ), 'run' ]
		);
	}

	/**
	 * Plugin-specific registration logic.
	 *
	 * @param  Container $container Plugin container instance.
	 *
	 * @return void
	 */
	public function register( Container $container ) {
		$container['jobs.check_site'] = function( Container $c ) {
			return new Check_Site( $c['core.checker'], $c['options.manager'] );
		};

		$container['jobs.gc_transients'] = function( Container $c ) {
			return new Collect_Transient_Garbage( $c['core.cache'] );
		};
	}
}
