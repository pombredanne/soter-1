<?php

namespace Soter;

use Pimple\Container;
use Soter\Jobs\Check_Site;
use Soter\Options\Options_Provider;
use Pimple\ServiceProviderInterface;
use Soter\Jobs\Collect_Transient_Garbage;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Plugin_Provider implements ServiceProviderInterface {
	protected $file;

	public function boot( Container $container ) {
		if ( ! $this->doing_cron() && ! is_admin() ) {
			return;
		}

		add_action( 'init', [ $container['upgrader'], 'perform_upgrade' ] );
	}

	public function activate( Container $container ) {
		if ( false === wp_next_scheduled( Check_Site::get_hook() ) ) {
			wp_schedule_event( time(), 'twicedaily', Check_Site::get_hook() );
		}
	}

	public function deactivate( Container $container ) {
		wp_clear_scheduled_hook( Check_Site::get_hook() );
	}

	public function register( Container $container ) {
		$container['upgrader'] = function( Container $c ) {
			return new Upgrader( $c['options.manager'] );
		};

		$container['user-agent'] = function( Container $c ) {
			return sprintf(
				'%s (%s) | %s | v%s | %s',
				get_bloginfo( 'name' ),
				get_home_url(),
				$c['name'],
				$c['version'],
				$c['url']
			);
		};
	}

	protected function doing_cron() {
		return defined( 'DOING_CRON' ) && DOING_CRON;
	}
}
