<?php

namespace Soter;

use Pimple\Container;
use Soter\Tasks\Check_Site;
use Soter\Options\Options_Provider;
use Pimple\ServiceProviderInterface;
use Soter\Tasks\Transient_Garbage_Collection;
use Soter\Tasks\Vulnerability_Garbage_Collection;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Plugin_Provider implements ServiceProviderInterface {
	protected $file;

	public function boot( Container $container ) {
		// @todo Better way to handle this? It is needed in ->activate().
		$this->file = $container['file'];

		register_activation_hook( $container['file'], [ $this, 'activate' ] );
		register_deactivation_hook( $container['file'], [ $this, 'deactivate' ] );

		$this->boot_content_types( $container );
		$this->boot_upgrader( $container );
	}

	public function activate() {
		wp_schedule_event( time(), 'twicedaily', Check_Site::HOOK );

		register_uninstall_hook( $this->file, [ __CLASS__, 'uninstall' ] );
	}

	public function deactivate() {
		wp_clear_scheduled_hook( Check_Site::HOOK );
	}

	/**
	 * @todo Move to standalone uninstall script.
	 */
	public static function uninstall() {
		$options = [
			'soter_email_address',
			'soter_email_type',
			'soter_enable_email',
			'soter_enable_notices',
			'soter_ignored_plugins',
			'soter_ignored_themes',
			'soter_installed_version',
			'soter_ignored_vulnerabilities',
		];

		foreach ( $options as $option ) {
			delete_option( $option );
		}

		// Not perfect - only deletes transients that have already expired.
		( new Transient_Garbage_Collection( 'soter' ) )->run_task();

		// No param - defaults to empty array - all vulnerabilities are deleted.
		( new Vulnerability_Garbage_Collection )->run_task();
	}

	public function register( Container $container ) {
		$container['register_user_meta'] = function( Container $c ) {
			return new Register_User_Meta;
		};

		$container['register_vuln_cpt'] = function( Container $c ) {
			return new Register_Vulnerability_Post_Type;
		};

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

	protected function boot_content_types( Container $container ) {
		add_action( 'init', [ $container['register_user_meta'], 'register' ] );
		add_action( 'init', [ $container['register_vuln_cpt'], 'register' ] );
	}

	protected function boot_upgrader( Container $container ) {
		if ( ! $this->doing_cron() && ! is_admin() ) {
			return;
		}

		add_action( 'init', [ $container['upgrader'], 'perform_upgrade' ] );
	}

	protected function doing_cron() {
		return defined( 'DOING_CRON' ) && DOING_CRON;
	}
}
