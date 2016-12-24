<?php

namespace SSNepenthe\Soter;

use WP_CLI;
use SSNepenthe\Soter\WPVulnDB\Client;

class Plugin {
	protected $checker;
	protected $file;
	protected $results;
	protected $settings;

	public function __construct( $file ) {
		$this->file = $file;

		$this->results = new List_Option( 'soter_results' );
		$this->results->init();

		$this->settings = new Map_Option( 'soter_settings' );
		$this->settings->init();

		$base_url = 'https://wpvulndb.com/api/v2';

		$site_name = get_bloginfo( 'name' );
		$plugin_name = 'Soter Vulnerability Scanner';
		$plugin_version = '0.4.0';
		$plugin_url = 'https://github.com/ssnepenthe/soter';

		$user_agent = sprintf(
			'%s | %s v%s | %s',
			$site_name,
			$plugin_name,
			$plugin_version,
			$plugin_url
		);

		$client = new Client(
			new WP_Http_Client( $base_url, $user_agent ),
			new WP_Transient_Cache( 'soter' )
		);
		$this->checker = new Checker( $client, $this->settings );
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

		$command = new Security_Command( $this->checker );

		WP_CLI::add_command( 'security', $command );
	}

	protected function cron_init() {
		$notifier = new Vulnerable_Email_Notifier(
			$this->settings,
			soter_template()
		);

		$tasks = [
			new Run_Check_Task( $this->checker, $notifier, $this->results ),
			new Garbage_Collection_Task,
		];

		foreach ( $tasks as $task ) {
			$task->init();
		}
	}

	protected function plugin_init() {
		$template = soter_template( false );

		$short_notice = new Vulnerable_Short_Admin_Notice_Notifier( $template );
		$short_notice->set_data( $this->results->all() );

		$full_notice = new Vulnerable_Full_Admin_Notice_Notifier( $template );
		$full_notice->set_data( $this->results->all() );

		$features = [
			$short_notice,
			$full_notice,
			new Options_Page( $this->settings, $template ),
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
