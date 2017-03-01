<?php
/**
 * The main plugin bootstrap.
 *
 * @package soter
 */

namespace SSNepenthe\Soter;

use WP_CLI;
use Closure;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * This class acts as the plugin bootstrap, handling WordPress, WP-CLI and WP-Cron.
 */
class Plugin {
	/**
	 * Main plugin file path, used for (de)activation hooks.
	 *
	 * @var string
	 */
	protected $file;

	/**
	 * Container entries which are resolved on each call.
	 *
	 * @var Closure[]
	 */
	protected $entries = [];

	/**
	 * Container entries which are cached after first access.
	 *
	 * @var Closure[]
	 */
	protected $shared_entries = [];

	/**
	 * Cached values returned from $shared_entries.
	 *
	 * @var array
	 */
	protected $shared_cache = [];

	/**
	 * Class constructor.
	 *
	 * @param string $file Main plugin file path.
	 */
	public function __construct( $file ) {
		$this->file = (string) $file;

		$this->register_components();
	}

	/**
	 * Plugin activation hook - schedules WP-Cron event and registers uninstall hook.
	 */
	public function activate() {
		wp_schedule_event( time(), 'twicedaily', Tasks\Check_Site::HOOK );

		register_uninstall_hook( $this->file, [ __CLASS__, 'uninstall' ] );
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
		$this->upgrader_init();

		$this->admin_init();
		$this->cli_init();
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
	 * Adds an container entry.
	 *
	 * @param string  $key     Identifier for a given entry.
	 * @param Closure $closure Function to call to get the value of a given entry.
	 */
	protected function add( string $key, Closure $closure ) {
		$this->entries[ $key ] = $closure;
	}

	/**
	 * Initializes admin-specific plugin functionality.
	 */
	protected function admin_init() {
		if ( ! is_admin() ) {
			return;
		}

		$results = $this->resolve( 'results' );
		$template = $this->resolve( 'template', false );

		$features = [
			new Notices\Vulnerable_Site(
				$template,
				$this->resolve( 'cache' ),
				$results->all()
			),
			new Notices\Vulnerable_Site_Abbreviated( $template, $results->all() ),
			new Options\Options_Page( $this->resolve( 'settings' ), $template ),
		];

		foreach ( $features as $feature ) {
			$feature->init();
		}
	}

	/**
	 * Initializes CLI-specific plugin functionality.
	 */
	protected function cli_init() {
		if ( ! $this->is_cli_request() ) {
			return;
		}

		$commands = [
			'security' => new Commands\Security_Command(
				$this->resolve( 'checker' )
			),
		];

		foreach ( $commands as $name => $callable ) {
			WP_CLI::add_command( $name, $callable );
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
			new Tasks\Check_Site( $this->resolve( 'checker' ) ),
			new Tasks\Transient_Garbage_Collection( $this->resolve( 'prefix' ) ),
			new Tasks\Vulnerability_Garbage_Collection(
				$this->resolve( 'results' )->all()
			),
		];

		foreach ( $tasks as $task ) {
			$task->init();
		}
	}

	/**
	 * Determine if a given request comes from WP-CLI.
	 *
	 * @return boolean
	 */
	protected function is_cli_request() {
		return defined( 'WP_CLI' ) && WP_CLI;
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
		if ( ! $this->is_cron_request() && ! $this->is_cli_request() ) {
			return;
		}

		$settings = $this->resolve( 'settings' );

		$listeners = [
			new Listeners\Log_Vulnerability_Ids( $this->resolve( 'results' ) ),
			new Listeners\Send_Vulnerable_Packages_Email(
				$this->resolve( 'template' ),
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
		// Can't share instances b/c each gets different post check callbacks.
		$this->add( 'checker', function() {
			$settings = $this->resolve( 'settings' );

			return new Checker(
				$settings->get( 'ignored_plugins' ),
				$settings->get( 'ignored_themes' ),
				$this->resolve( 'api' )
			);
		} );

		// Can't share instances b/c some can't be overridable.
		$this->add( 'template', function( $overridable = true ) {
			$stack = new Views\Template_Locator_Stack;

			if ( $overridable ) {
				$stack->push( new Views\Core_Template_Locator );
			}

			$stack->push(
				new Views\Dir_Template_Locator( plugin_dir_path( $this->file ) )
			);

			return new Views\Template( $stack );
		} );

		$this->share( 'api', function() {
			return new WPScan\Api_Client(
				$this->resolve( 'http' ),
				$this->resolve( 'cache' )
			);
		} );

		$this->share( 'cache', function() {
			return new Cache\WP_Transient_Cache( $this->resolve( 'prefix' ) );
		} );

		$this->share( 'http', function() {
			return new Http\WP_Http_Client( $this->resolve( 'user-agent' ) );
		} );

		$this->share( 'prefix', function() {
			return 'soter';
		} );

		$this->share( 'results', function() {
			$results = new Options\List_Option( 'soter_results' );
			$results->init();

			return $results;
		} );

		$this->share( 'settings', function() {
			$settings = new Options\Map_Option( 'soter_settings' );
			$settings->init();

			return $settings;
		} );

		$this->share( 'url', function() {
			return 'https://github.com/ssnepenthe/soter';
		} );

		$this->share( 'user-agent', function() {
			return sprintf(
				'%s (%s) | Soter | v%s | %s',
				get_bloginfo( 'name' ),
				get_home_url(),
				$this->resolve( 'version' ),
				$this->resolve( 'url' )
			);
		} );

		$this->share( 'version', function() {
			return '0.4.0';
		} );
	}

	/**
	 * Resolves a given entry by first looking in the cache, then shared entries and
	 * finally generic entries.
	 *
	 * @param  string $key     Entry key to resolve.
	 * @param  mixed  ...$args Any args that should be passed to a given entry.
	 *
	 * @return mixed
	 */
	protected function resolve( string $key, ...$args ) {
		if ( isset( $this->shared_cache[ $key ] ) ) {
			return $this->shared_cache[ $key ];
		}

		if ( isset( $this->shared_entries[ $key ] ) ) {
			return $this->shared_cache[ $key ] = $this->shared_entries[ $key ]();
		}

		// Args are only used for non-shared entries.
		if ( isset( $this->entries[ $key ] ) ) {
			return $this->entries[ $key ]( ...$args );
		}

		// Potentially problematic to return null.
		return null;
	}

	/**
	 * Add an entry that should be cached.
	 *
	 * @param  string  $key     The key for a given entry.
	 * @param  Closure $closure The function to call to get the value of an entry.
	 */
	protected function share( string $key, Closure $closure ) {
		$this->shared_entries[ $key ] = $closure;
	}

	/**
	 * Initializes the plugin upgrader functionality.
	 */
	protected function upgrader_init() {
		if (
			! $this->is_cli_request()
			&& ! $this->is_cron_request()
			&& ! is_admin()
		) {
			return;
		}

		$upgrader = new Upgrader(
			$this->resolve( 'results' ),
			$this->resolve( 'settings' )
		);
		$upgrader->init();
	}
}
