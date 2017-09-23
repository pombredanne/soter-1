<?php
/**
 * Plugin_Provider class.
 *
 * @package soter
 */

namespace Soter;

use Pimple\Container;
use Soter_Core\Checker;
use League\Plates\Engine;
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
 * Defines the plugin provider class.
 */
class Plugin_Provider implements ServiceProviderInterface {
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
	 * Provider-specific boot logic.
	 *
	 * @param  Container $container Plugin container instance.
	 *
	 * @return void
	 */
	public function boot( Container $container ) {
		add_action( 'admin_init', [ $container->proxy( 'options_page' ), 'admin_init' ] );
		add_action( 'admin_menu', [ $container->proxy( 'options_page' ), 'admin_menu' ] );

		add_action(
			'admin_notices',
			[
				$container->proxy( 'options_page' ),
				'print_notice_when_no_notifiers_active',
			]
		);

		add_action( 'admin_init', [ $container['options_manager'], 'register_settings' ] );
		add_action( 'soter_run_check', [ $container->proxy( 'check_site_job' ), 'run' ] );
		add_action(
			'soter_site_check_complete',
			[ $container->proxy( 'notifier_manager' ), 'notify' ]
		);

		$this->boot_upgrader( $container );
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
			return new Check_Site_Job(
				new Checker( new Api_Client( $c['http'] ), new WP_Package_Manager() ),
				$c['options_manager']
			);
		};

		$container['http'] = function( Container $c ) {
			return new Cached_Http_Client(
				new WP_Http_Client( $c['user_agent'] ),
				new WP_Transient_Cache( $c['prefix'], HOUR_IN_SECONDS )
			);
		};

		$container['notifier_manager'] = function( Container $c ) {
			$options = $c['options_manager'];

			return new Notifier_Manager( $options, [
				new Email_Notifier( $c['plates'], $options ),
				new Slack_Notifier( $options, $c['user_agent'] ),
			] );
		};

		$container['options_manager'] = function( Container $c ) {
			return new Options_Manager( new Options_Store( $c['prefix'] ) );
		};

		$container['options_page'] = function( Container $c ) {
			return new Options_Page( $c['options_manager'], $c['plates'] );
		};

		$container['plates'] = function( Container $c ) {
			$engine = new Engine( $c['dir'] . '/templates' );

			// Drop the file extension so we can also load .css files.
			$engine->setFileExtension( null );
			$engine->loadExtension( new Button_Extension() );
			$engine->addData(
				[
					'plugin_url' => $c['url'],
					'site_name' => get_bloginfo( 'name' ),
					'site_url' => site_url(),
				],
				[
					'emails/html/error.php',
					'emails/html/vulnerable.php',
					'emails/partials/footer.php',
					'emails/partials/header.php',
					'emails/text/error.php',
					'emails/text/vulnerable.php',
				]
			);

			return $engine;
		};

		$container['user_agent'] = function( Container $c ) {
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
	 * Boots the upgrader object on cron or admin requests.
	 *
	 * @param  Container $container Plugin container instance.
	 *
	 * @return void
	 */
	protected function boot_upgrader( Container $container ) {
		if ( ! $this->doing_cron() && ! is_admin() ) {
			return;
		}

		$upgrader = new Upgrader( $container['options_manager'] );

		add_action( 'init', [ $upgrader, 'perform_upgrade' ] );
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
