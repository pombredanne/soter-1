<?php

namespace Soter\Jobs;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Jobs_Provider implements ServiceProviderInterface {
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

	public function register( Container $container ) {
		$container['jobs.check_site'] = function( Container $c ) {
			return new Check_Site( $c['core.checker'], $c['options.manager'] );
		};

		$container['jobs.gc_transients'] = function( Container $c ) {
			return new Collect_Transient_Garbage( $c['core.cache'] );
		};
	}
}
