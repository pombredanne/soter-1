<?php

namespace Soter\Listeners;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Listeners_Provider implements ServiceProviderInterface {
	public function boot( Container $container ) {
		if ( ! $this->doing_cron() ) {
			return;
		}

		$this->boot_email_listener( $container );

		add_action(
			'soter_core_check_packages_complete',
			[ $container['listeners.log_ids'], 'log_vulnerability_ids' ]
		);
		add_action(
			'soter_core_check_packages_complete',
			[ $container['listeners.store_vuln'], 'store_vulnerabilities' ]
		);
	}

	protected function boot_email_listener( Container $container ) {
		if ( ! $container['options.manager']->enable_email() ) {
			return;
		}

		add_action(
			'soter_core_check_packages_complete',
			[ $container['listeners.send_mail'], 'send_email' ]
		);
	}

	public function register( Container $container ) {
		$container['listeners.log_ids'] = function( Container $c ) {
			return new Log_Vulnerability_Ids( $c['options.manager'] );
		};

		$container['listeners.send_mail'] = function( Container $c ) {
			return new Send_Vulnerable_Packages_Email(
				$c['views.overridable'],
				$c['options.manager']
			);
		};

		$container['listeners.store_vuln'] = function( Container $c ) {
			return new Store_Vulnerabilities;
		};
	}

	protected function doing_cron() {
		return defined( 'DOING_CRON' ) && DOING_CRON;
	}
}
