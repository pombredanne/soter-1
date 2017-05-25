<?php
/**
 * Notifiers_Provider class.
 *
 * @package soter
 */

namespace Soter\Notifiers;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Defines the notifiers provider class.
 */
class Notifiers_Provider implements ServiceProviderInterface {
	/**
	 * Provider-specific boot logic.
	 *
	 * @param  Container $container Plugin container instance.
	 *
	 * @return void
	 */
	public function boot( Container $container ) {
		add_action(
			'soter_check_complete',
			[ $container->proxy( 'notifiers.send_mail' ), 'notify' ],
			10,
			2
		);
	}

	/**
	 * Provider-specific registration logic.
	 *
	 * @param  Container $container Plugin container instance.
	 *
	 * @return void
	 */
	public function register( Container $container ) {
		$container['notifiers.send_mail'] = function( Container $c ) {
			return new Email_Notifier(
				$c['plates'],
				$c['options.manager']
			);
		};
	}
}
