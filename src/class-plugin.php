<?php

namespace SSNepenthe\Soter;

use SSNepenthe\Soter\Options\Page;

class Plugin {
	protected $file;

	public function __construct( $file ) {
		$this->file = $file;
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

		\WP_CLI::add_command(
			'security',
			'SSNepenthe\\Soter\\Command\\Security_Command'
		);
	}

	protected function cron_init() {
		$task = new Run_Check_Task;
		$task->init();
	}

	protected function plugin_init() {
		$features = [
			new Abbreviated_Admin_Notice_Notification,
			new Full_Admin_Notice_Notification,
			new Page,
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
