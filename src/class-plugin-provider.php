<?php

namespace Soter;

use Pimple\Container;
use Soter\Jobs\Check_Site;
use Soter\Options\Options_Provider;
use Pimple\ServiceProviderInterface;
use Soter\Jobs\Collect_Transient_Garbage;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Plugin_Provider implements ServiceProviderInterface {
	protected $file;

	public function boot( Container $container ) {
		if ( ! $this->doing_cron() && ! is_admin() ) {
			return;
		}

		add_action( 'init', [ $container['upgrader'], 'perform_upgrade' ] );
	}

	public function activate( Container $container ) {
		if ( false === wp_next_scheduled( Check_Site::get_hook() ) ) {
			wp_schedule_event( time(), 'twicedaily', Check_Site::get_hook() );
		}

		register_uninstall_hook( $container['file'], [ __CLASS__, 'uninstall' ] );
	}

	public function deactivate( Container $container ) {
		wp_clear_scheduled_hook( Check_Site::get_hook() );
	}

	/**
	 * @todo Move to standalone uninstall script.
	 */
	public static function uninstall() {
		$options = [
			'soter_email_address',
			'soter_email_type',
			'soter_ignored_plugins',
			'soter_ignored_themes',
			'soter_installed_version',
		];

		foreach ( $options as $option ) {
			delete_option( $option );
		}

		( new Soter_Core\WP_Transient_Cache( $GLOBALS['wpdb'], 'soter' ) )->flush();
	}

	public function register( Container $container ) {
		$container['upgrader'] = function( Container $c ) {
			return new Upgrader( $c['options.manager'] );
		};

		$container['user-agent'] = function( Container $c ) {
			return sprintf(
				'%s (%s) | %s | v%s | %s',
				get_bloginfo( 'name' ),
				get_home_url(),
				$c['name'],
				$c['version'],
				$c['url']
			);
		};
	}

	protected function doing_cron() {
		return defined( 'DOING_CRON' ) && DOING_CRON;
	}
}
