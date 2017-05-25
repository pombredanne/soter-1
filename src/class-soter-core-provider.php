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
		$container['core.api'] = function( Container $c ) {
			return new Api_Client( $c['core.http'] );
		};

		$container['core.cache'] = function( Container $c ) {
			return new WP_Transient_Cache(
				$c['wp.db'],
				$c['prefix'],
				HOUR_IN_SECONDS
			);
		};

		$container['core.checker'] = function( Container $c ) {
			return new Checker( $c['core.api'], $c['core.manager'] );
		};

		$container['core.http'] = function( Container $c ) {
			return new Cached_Http_Client(
				new WP_Http_Client( $c['user-agent'] ),
				$c['core.cache']
			);
		};

		$container['core.manager'] = function( Container $c ) {
			return new WP_Package_Manager;
		};

		$container['wp.db'] = function( Container $c ) {
			if ( ! isset( $GLOBALS['wpdb'] ) ) {
				require_wp_db();
			}

			return $GLOBALS['wpdb'];
		};
	}
}
