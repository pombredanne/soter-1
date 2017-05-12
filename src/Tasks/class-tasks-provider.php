<?php

namespace Soter\Tasks;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Tasks_Provider implements ServiceProviderInterface {
	public function boot( Container $container ) {
		if ( ! $this->doing_cron() ) {
			return;
		}

		add_action(
			Check_Site::HOOK,
			[ $container['tasks.check_site'], 'run_task' ]
		);
		add_action(
			'wp_scheduled_delete',
			[ $container['tasks.gc_posts'], 'run_task' ]
		);
		add_action(
			'wp_scheduled_delete',
			[ $container['tasks.gc_transients'], 'run_task' ]
		);
	}

	public function register( Container $container ) {
		$container['tasks.check_site'] = function( Container $c ) {
			return new Check_Site( $c['checker'], $c['options.manager'] );
		};

		$container['tasks.gc_posts'] = function( Container $c ) {
			return new Vulnerability_Garbage_Collection(
				$c['options.manager']->vulnerabilities()
			);
		};

		$container['tasks.gc_transients'] = function( Container $c ) {
			return new Transient_Garbage_Collection( $c['prefix'] );
		};
	}

	protected function doing_cron() {
		return defined( 'DOING_CRON' ) && DOING_CRON;
	}
}
