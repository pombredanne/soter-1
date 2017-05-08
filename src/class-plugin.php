<?php
/**
 * The main plugin bootstrap.
 *
 * @package soter
 */

namespace SSNepenthe\Soter;

use Closure;
use Pimple\Container;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * This class acts as the plugin bootstrap, handling WordPress and WP-Cron.
 */
class Plugin extends Container {
	/**
	 * Plugin activation hook - schedules WP-Cron event and registers uninstall hook.
	 */
	public function activate() {
		wp_schedule_event( time(), 'twicedaily', Tasks\Check_Site::HOOK );

		register_uninstall_hook( $this['file'], [ __CLASS__, 'uninstall' ] );
	}

	/**
	 * Deactivation hook - removes scheduled WP-Cron event.
	 */
	public function deactivate() {
		wp_clear_scheduled_hook( Tasks\Check_Site::HOOK );
	}

	/**
	 * Initializes/bootstraps the plugin.
	 */
	public function init() {
		$this->register_components();

		$this->upgrader_init();

		$this->admin_init();
		$this->cron_init();
		$this->listeners_init();
		$this->plugin_init();
	}

	/**
	 * Uninstall hook - deletes lingering options entries.
	 */
	public static function uninstall() {
		delete_option( 'soter_settings' );
		delete_option( 'soter_results' );

		$tasks = [
			// Not perfect - only deletes transients that have already expired.
			new Tasks\Transient_Garbage_Collection( 'soter' ),
			// No param - defaults to empty array - all vulnerabilities are deleted.
			new Tasks\Vulnerability_Garbage_Collection,
		];

		foreach ( $tasks as $task ) {
			$task->run_task();
		}
	}

	/**
	 * Initializes admin-specific plugin functionality.
	 */
	protected function admin_init() {
		if ( ! is_admin() ) {
			return;
		}

		$results = $this['results'];
		$template = $this['views.plugin'];

		$features = [
			new Notices\Vulnerable_Site(
				$template,
				$this['cache'],
				$results->all()
			),
			new Notices\Vulnerable_Site_Abbreviated( $template, $results->all() ),
			new Options\Options_Page( $this['settings'], $template ),
		];

		foreach ( $features as $feature ) {
			$feature->init();
		}
	}

	/**
	 * Initializes cron-specific plugin functionality.
	 */
	protected function cron_init() {
		if ( ! $this->is_cron_request() ) {
			return;
		}

		$tasks = [
			new Tasks\Check_Site( $this['checker'], $this['settings'] ),
			new Tasks\Transient_Garbage_Collection( $this['prefix'] ),
			new Tasks\Vulnerability_Garbage_Collection(
				$this['results']->all()
			),
		];

		foreach ( $tasks as $task ) {
			$task->init();
		}
	}

	/**
	 * Determine if a given request comes from WP-Cron.
	 *
	 * @return boolean
	 */
	protected function is_cron_request() {
		return defined( 'DOING_CRON' ) && DOING_CRON;
	}

	/**
	 * Initializes generic listener functionality.
	 */
	protected function listeners_init() {
		if ( ! $this->is_cron_request() ) {
			return;
		}

		$settings = $this['settings'];

		$listeners = [
			new Listeners\Log_Vulnerability_Ids( $this['results'] ),
			new Listeners\Send_Vulnerable_Packages_Email(
				$this['views.overridable'],
				$settings->get( 'enable_email', false ),
				$settings->get( 'html_email', false ),
				$settings->get( 'email_address', '' )
			),
			new Listeners\Store_Vulnerabilities,
		];

		foreach ( $listeners as $listener ) {
			$listener->init();
		}
	}

	/**
	 * Initializes global plugin functionality.
	 */
	protected function plugin_init() {
		$features = [
			new Register_User_Meta,
			new Register_Vulnerability_Post_Type,
		];

		foreach ( $features as $feature ) {
			$feature->init();
		}
	}

	/**
	 * Registers all needed values.
	 */
	protected function register_components() {
		$this['checker'] = function( Container $c ) {
			return new \Soter_Core\Checker( $c['api'] );
		};

		$this['views.core_locator'] = function( Container $c ) {
			return new Views\Core_Template_Locator;
		};

		$this['views.plugin_locator'] = function( Container $c ) {
			return new Views\Dir_Template_Locator( $c['dir'] );
		};

		$this['views.overridable'] = function( Container $c ) {
			return new Views\Template(
				new Views\Template_Locator_Stack( [
					$c['views.core_locator'],
					$c['views.plugin_locator']
				] )
			);
		};

		$this['views.plugin'] = function( Container $c ) {
			return new Views\Template(
				new Views\Template_Locator_Stack( [ $c['views.plugin_locator'] ] )
			);
		};

		$this['api'] = function( Container $c ) {
			return new \Soter_Core\Api_Client( $c['http'], $c['cache'] );
		};

		$this['cache'] = function( Container $c ) {
			return new \Soter_Core\WP_Transient_Cache( $c['prefix'] );
		};

		$this['http'] = function( Container $c ) {
			return new \Soter_Core\WP_Http_Client( $c['user-agent'] );
		};

		$this['results'] = function( Container $c ) {
			$results = new Options\List_Option( 'soter_results' );
			$results->init();

			return $results;
		};

		$this['settings'] = function( Container $c ) {
			$settings = new Options\Map_Option( 'soter_settings' );
			$settings->init();

			return $settings;
		};

		$this['user-agent'] = function( Container $c ) {
			return sprintf(
				'%s (%s) | Soter | v%s | %s',
				get_bloginfo( 'name' ),
				get_home_url(),
				$c['version'],
				$c['url']
			);
		};
	}

	/**
	 * Initializes the plugin upgrader functionality.
	 */
	protected function upgrader_init() {
		if ( ! $this->is_cron_request() && ! is_admin() ) {
			return;
		}

		$upgrader = new Upgrader(
			$this['results'],
			$this['settings']
		);
		$upgrader->init();
	}
}
