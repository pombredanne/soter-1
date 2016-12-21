<?php

namespace SSNepenthe\Soter;

use SSNepenthe\Soter\Checker;
use SSNepenthe\Soter\Mailers\WP_Mail;
use SSNepenthe\Soter\Options\Results;
use SSNepenthe\Soter\Options\Settings;

class Run_Check_Task {
	const HOOK = 'SSNepenthe\\Soter\\run_check';

	protected $checker;
	protected $results;
	protected $settings;

	public function __construct(
		Checker $checker,
		Results $results,
		Settings $settings
	) {
		$this->checker = $checker;
		$this->results = $results;
		$this->settings = $settings;
	}

	public function init() {
		add_action( self::HOOK, [ $this, 'run_task' ] );
	}

	public function run_task() {
		if ( ! defined( 'DOING_CRON' ) || ! DOING_CRON ) {
			return;
		}

		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$vulnerabilities = $this->checker->check();

		$this->results->set_from_vulnerabilities_array( $vulnerabilities );
		$this->results->save();

		$mailer = new WP_Mail(
			$vulnerabilities,
			$this->settings
		);
		$mailer->maybe_send();
	}
}
