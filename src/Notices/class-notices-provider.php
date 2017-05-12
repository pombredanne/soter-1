<?php

namespace Soter\Notices;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Notices_Provider implements ServiceProviderInterface {
	public function boot( Container $container ) {
		if ( ! is_admin() ) {
			return;
		}

		add_action(
			'admin_enqueue_scripts',
			[ $container['notices.abbreviated'], 'print_dismiss_notice_script' ]
		);
		add_action(
			'admin_notices',
			[ $container['notices.abbreviated'], 'print_notice' ]
		);
		add_action(
			'admin_notices',
			[ $container['notices.vulnerable'], 'print_notice' ]
		);
	}

	public function register( Container $container ) {
		$container['notices.abbreviated'] = function( Container $c ) {
			return new Vulnerable_Site_Abbreviated(
				$c['views.plugin'],
				$c['options.manager']->vulnerabilities()
			);
		};

		$container['notices.vulnerable'] = function( Container $c ) {
			return new Vulnerable_Site(
				$c['views.plugin'],
				$c['cache'],
				$c['options.manager']->vulnerabilities()
			);
		};
	}
}
