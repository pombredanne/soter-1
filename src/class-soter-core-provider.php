<?php

namespace Soter;

use Pimple\Container;
use Soter_Core\Checker;
use Soter_Core\Api_Client;
use Soter_Core\WP_Http_Client;
use Soter_Core\WP_Transient_Cache;
use Pimple\ServiceProviderInterface;

class Soter_Core_Provider implements ServiceProviderInterface {
	public function register( Container $container ) {
		$container['api'] = function( Container $c ) {
			return new Api_Client( $c['http'], $c['cache'] );
		};

		$container['cache'] = function( Container $c ) {
			return new WP_Transient_Cache( $c['prefix'] );
		};

		$container['checker'] = function( Container $c ) {
			return new Checker( $c['api'] );
		};

		$container['http'] = function( Container $c ) {
			return new WP_Http_Client( $c['user-agent'] );
		};
	}
}
