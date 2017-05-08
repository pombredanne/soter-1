<?php

namespace SSNepenthe\Soter;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use SSNepenthe\Soter\Tasks\Check_Site;
use SSNepenthe\Soter\Options\Options_Provider;
use SSNepenthe\Soter\Tasks\Transient_Garbage_Collection;
use SSNepenthe\Soter\Tasks\Vulnerability_Garbage_Collection;

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
		delete_option( Options_Provider::SETTINGS_KEY );
		delete_option( Options_Provider::RESULTS_KEY );

		$tasks = [
			// Not perfect - only deletes transients that have already expired.
			new Transient_Garbage_Collection( 'soter' ),
			// No param - defaults to empty array - all vulnerabilities are deleted.
			new Vulnerability_Garbage_Collection,
		];

		foreach ( $tasks as $task ) {
			$task->run_task();
		}
	}

	public function register( Container $container ) {
		$container['register_user_meta'] = function( Container $c ) {
			return new Register_User_Meta;
		};

		$container['register_vuln_cpt'] = function( Container $c ) {
			return new Register_Vulnerability_Post_Type;
		};

		$container['upgrader'] = function( Container $c ) {
			return new Upgrader( $c['options.results'], $c['options.settings'] );
		};

		$container['user-agent'] = function( Container $c ) {
			return sprintf(
				'%s (%s) | Soter | v%s | %s',
				get_bloginfo( 'name' ),
				get_home_url(),
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
