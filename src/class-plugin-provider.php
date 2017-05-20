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

	public function activate() {
		wp_schedule_event( time(), 'twicedaily', Check_Site::get_hook() );

		register_uninstall_hook( $this->file, [ __CLASS__, 'uninstall' ] );
	}

	public function deactivate() {
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

		// Not perfect - only deletes transients that have already expired.
		( new Collect_Transient_Garbage( 'soter' ) )->run();
	}

	public function register( Container $container ) {
		// @todo Better way to handle this? It is needed in ->activate().
		$this->file = $container['file'];

		register_activation_hook( $container['file'], [ $this, 'activate' ] );
		register_deactivation_hook( $container['file'], [ $this, 'deactivate' ] );

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
