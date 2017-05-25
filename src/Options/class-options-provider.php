<?php
/**
 * Options_Provider class.
 *
 * @package soter
 */

namespace Soter\Options;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Defines the options provider class.
 */
class Options_Provider implements ServiceProviderInterface {
	/**
	 * Provider-specific boot logic.
	 *
	 * @param  Container $container Plugin container instance.
	 *
	 * @return void
	 */
	public function boot( Container $container ) {
		add_action( 'init', [ $container['options.manager'], 'register_settings' ] );

		add_action(
			'admin_init',
			[ $container->proxy( 'options.page' ), 'admin_init' ]
		);
		add_action(
			'admin_menu',
			[ $container->proxy( 'options.page' ), 'admin_menu' ]
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
		$container['options.manager'] = function( Container $c ) {
			return new Options_Manager( $c['options.store'] );
		};

		$container['options.page'] = function( Container $c ) {
			return new Options_Page( $c['options.manager'], $c['plates'] );
		};

		$container['options.store'] = function( Container $c ) {
			return new Options_Store( $c['prefix'] );
		};
	}
}
