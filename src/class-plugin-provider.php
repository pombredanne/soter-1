<?php
/**
 * Plugin_Provider class.
 *
 * @package soter
 */

namespace Soter;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Defines the plugin provider class.
 */
class Plugin_Provider implements ServiceProviderInterface {
	/**
	 * Provider-specific boot logic.
	 *
	 * @param  Container $container Plugin container instance.
	 *
	 * @return void
	 */
	public function boot( Container $container ) {
		add_action(
			'admin_init',
			[ $container->proxy( 'options_page' ), 'admin_init' ]
		);

		add_action(
			'admin_menu',
			[ $container->proxy( 'options_page' ), 'admin_menu' ]
		);

		add_action( 'init', [ $container['options_manager'], 'register_settings' ] );

		add_action(
			'soter_run_check',
			[ $container->proxy( 'check_site_job' ), 'run' ]
		);

		add_action(
			'soter_check_complete',
			[ $container->proxy( 'email_notifier' ), 'notify' ],
			10,
			2
		);

		add_action(
			'soter_check_complete',
			[ $container->proxy( 'slack_notifier' ), 'notify' ],
			10,
			2
		);

		if ( ! $this->doing_cron() && ! is_admin() ) {
			return;
		}

		add_action( 'init', [ $container['upgrader'], 'perform_upgrade' ] );
	}

	/**
	 * Provider-specific activation logic.
	 *
	 * @param  Container $container Plugin container instance.
	 *
	 * @return void
	 */
	public function activate( Container $container ) {
		if ( false === wp_next_scheduled( 'soter_run_check' ) ) {
			wp_schedule_event( time(), 'twicedaily', 'soter_run_check' );
		}
	}

	/**
	 * Provider-specific deactivation logic.
	 *
	 * @param  Container $container Plugin container instance.
	 *
	 * @return void
	 */
	public function deactivate( Container $container ) {
		wp_clear_scheduled_hook( 'soter_run_check' );
	}

	/**
	 * Provider-specific registration logic.
	 *
	 * @param  Container $container Plugin container instance.
	 *
	 * @return void
	 */
	public function register( Container $container ) {
		$container['check_site_job'] = function( Container $c ) {
			return new Check_Site_Job( $c['core.checker'], $c['options_manager'] );
		};

		$container['email_notifier'] = function( Container $c ) {
			return new Email_Notifier(
				$c['plates'],
				$c['options_manager']
			);
		};

		$container['options_manager'] = function( Container $c ) {
			return new Options_Manager( $c['options_store'] );
		};

		$container['options_page'] = function( Container $c ) {
			return new Options_Page( $c['options_manager'], $c['plates'] );
		};

		$container['options_store'] = function( Container $c ) {
			return new Options_Store( $c['prefix'] );
		};

		$container['slack_notifier'] = function( Container $c ) {
			return new Slack_Notifier(
				$c['options_manager'],
				$c['user-agent']
			);
		};

		$container['upgrader'] = function( Container $c ) {
			return new Upgrader( $c['options_manager'] );
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

	/**
	 * Check whether the current request is a cron request.
	 *
	 * @return boolean
	 */
	protected function doing_cron() {
		return defined( 'DOING_CRON' ) && DOING_CRON;
	}
}
