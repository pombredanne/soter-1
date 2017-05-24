<?php

namespace Soter\Notifiers;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Notifiers_Provider implements ServiceProviderInterface {
	public function boot( Container $container ) {
		if ( ! $this->doing_cron() ) {
			return;
		}

		add_action(
			'soter_check_complete',
			[ $container['notifiers.send_mail'], 'send_email' ],
			10,
			2
		);
	}

	public function register( Container $container ) {
		$container['notifiers.send_mail'] = function( Container $c ) {
			return new Send_Vulnerable_Packages_Email(
				$c['plates'],
				$c['options.manager']
			);
		};
	}

	protected function doing_cron() {
		return defined( 'DOING_CRON' ) && DOING_CRON;
	}
}
