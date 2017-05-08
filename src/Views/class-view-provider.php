<?php

namespace Soter\Views;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class View_Provider implements ServiceProviderInterface {
	public function register( Container $container ) {
		$container['views.core_locator'] = function( Container $c ) {
			return new Core_Template_Locator;
		};

		$container['views.plugin_locator'] = function( Container $c ) {
			return new Dir_Template_Locator( $c['dir'] );
		};

		$container['views.overridable'] = function( Container $c ) {
			return new Template(
				new Template_Locator_Stack( [
					$c['views.core_locator'],
					$c['views.plugin_locator']
				] )
			);
		};

		$container['views.plugin'] = function( Container $c ) {
			return new Template(
				new Template_Locator_Stack( [ $c['views.plugin_locator'] ] )
			);
		};
	}
}
