<?php

namespace SSNepenthe\Soter;

use WP_CLI;
use SSNepenthe\Soter\Options\Page;
use SSNepenthe\Soter\HTTP\WP_Client;
use SSNepenthe\Soter\Formatters\Text;
use SSNepenthe\Soter\Options\Results;
use SSNepenthe\Soter\WPVulnDB\Client;
use SSNepenthe\Soter\Options\Settings;
use SSNepenthe\Soter\Command\Security_Command;

class Plugin {
	protected $checker;
	protected $client;
	protected $file;
	protected $results;
	protected $settings;

	public function __construct( $file ) {
		$this->file = $file;

		$this->results = new Results;
		$this->settings = new Settings;

		$this->client = new Client(
			new WP_Client,
			new WP_Transient_Cache( 'soter' )
		);
		$this->checker = new Checker( $this->client, $this->settings );
	}

	public function init() {
		$this->plugin_init();
		$this->cron_init();
		$this->cli_init();
	}

	protected function cli_init() {
		if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
			return;
		}

		$command = new Security_Command( $this->checker, new Text );

		WP_CLI::add_command( 'security', $command );
	}

	protected function cron_init() {
		$tasks = [
			new Run_Check_Task( $this->checker, $this->results, $this->settings ),
			new Garbage_Collection_Task,
		];

		foreach ( $tasks as $task ) {
			$task->init();
		}
	}

	protected function plugin_init() {
		$template = soter_template( false );

		$features = [
			new Abbreviated_Admin_Notice_Notification( $this->results, $template ),
			new Full_Admin_Notice_Notification( $this->results, $template ),
			new Page( $this->settings, $template ),
		];

		foreach ( $features as $feature ) {
			$feature->init();
		}
	}

	public function activate() {
		wp_schedule_event( time(), 'twicedaily', 'SSNepenthe\\Soter\\run_check' );

		register_uninstall_hook( $this->file, [ __CLASS__, 'uninstall' ] );
	}

	public function deactivate() {
		wp_clear_scheduled_hook( 'SSNepenthe\\Soter\\run_check' );
	}

	public static function uninstall() {
		delete_option( 'soter_settings' );
		delete_option( 'soter_results' );
	}
}
