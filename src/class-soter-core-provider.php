<?php
/**
 * Soter_Core_Provider class.
 *
 * @package soter
 */

namespace Soter;

use Pimple\Container;
use Soter_Core\Checker;
use Soter_Core\Api_Client;
use Soter_Core\WP_Http_Client;
use Soter_Core\Cached_Http_Client;
use Soter_Core\WP_Package_Manager;
use Soter_Core\WP_Transient_Cache;
use Pimple\ServiceProviderInterface;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Defines the soter core provider class.
 */
class Soter_Core_Provider implements ServiceProviderInterface {
	/**
	 * Provider-specific registration logic.
	 *
	 * @param  Container $container Plugin container instance.
	 *
	 * @return void
	 */
	public function register( Container $container ) {
		$container['api_client'] = function( Container $c ) {
			return new Api_Client( $c['http'] );
		};

		$container['cache'] = function( Container $c ) {
			return new WP_Transient_Cache( $c['prefix'], HOUR_IN_SECONDS );
		};

		$container['checker'] = function( Container $c ) {
			return new Checker( $c['api_client'], $c['package_manager'] );
		};

		$container['http'] = function( Container $c ) {
			return new Cached_Http_Client(
				new WP_Http_Client( $c['user_agent'] ),
				$c['cache']
			);
		};

		$container['package_manager'] = function( Container $c ) {
			return new WP_Package_Manager;
		};
	}
}
