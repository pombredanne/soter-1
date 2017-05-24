<?php

namespace Soter\Notifiers;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Notifiers_Provider implements ServiceProviderInterface {
	public function boot( Container $container ) {
		add_action(
			'soter_check_complete',
			[ $container->proxy( 'notifiers.send_mail' ), 'notify' ],
			10,
			2
		);
	}

	public function register( Container $container ) {
		$container['notifiers.send_mail'] = function( Container $c ) {
			return new Email_Notifier(
				$c['plates'],
				$c['options.manager']
			);
		};
	}
}
