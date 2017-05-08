<?php

namespace Soter\Options;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Options_Provider implements ServiceProviderInterface {
	const RESULTS_KEY = 'soter_results';
	const SETTINGS_KEY = 'soter_settings';

	public function boot( Container $container ) {
		if ( ! is_admin() ) {
			return;
		}

		add_action( 'admin_init', [ $container['options.page'], 'admin_init' ] );
		add_action( 'admin_menu', [ $container['options.page'], 'admin_menu' ] );
	}

	public function register( Container $container ) {
		$container['options.page'] = function( Container $c ) {
			return new Options_Page( $c['options.settings'], $c['views.plugin'] );
		};

		$container['options.results'] = function( Container $c ) {
			$results = new List_Option( self::RESULTS_KEY );
			$results->init();

			return $results;
		};

		$container['options.settings'] = function( Container $c ) {
			$settings = new Map_Option( self::SETTINGS_KEY );
			$settings->init();

			return $settings;
		};
	}
}
