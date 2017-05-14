<?php

namespace Soter\Jobs;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Jobs_Provider implements ServiceProviderInterface {
	public function boot( Container $container ) {
		if ( ! $this->doing_cron() ) {
			return;
		}

		add_action(
			$container['jobs.check_site']->get_hook(),
			[ $container['jobs.check_site'], 'run' ]
		);
		add_action(
			$container['jobs.gc_posts']->get_hook(),
			[ $container['jobs.gc_posts'], 'run' ]
		);
		add_action(
			$container['jobs.gc_transients']->get_hook(),
			[ $container['jobs.gc_transients'], 'run' ]
		);
	}

	public function register( Container $container ) {
		$container['jobs.check_site'] = function( Container $c ) {
			return new Check_Site( $c['core.checker'], $c['options.manager'] );
		};

		$container['jobs.gc_posts'] = function( Container $c ) {
			return new Collect_Vulnerability_Garbage(
				$c['options.manager']->vulnerabilities()
			);
		};

		$container['jobs.gc_transients'] = function( Container $c ) {
			return new Collect_Transient_Garbage( $c['prefix'] );
		};
	}

	protected function doing_cron() {
		return defined( 'DOING_CRON' ) && DOING_CRON;
	}
}
