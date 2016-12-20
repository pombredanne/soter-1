<?php

namespace SSNepenthe\Soter;

use SSNepenthe\Soter\Checker;
use SSNepenthe\Soter\Mailers\WP_Mail;
use SSNepenthe\Soter\Options\Results;
use SSNepenthe\Soter\Options\Settings;

class Run_Check_Task {
	const HOOK = 'SSNepenthe\\Soter\\run_check';

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

		$results = new Results;
		$settings = new Settings;

		$checker = new Checker;
		$vulnerabilities = $checker->check();

		$results->set_from_vulnerabilities_array( $vulnerabilities );
		$results->save();

		$mailer = new WP_Mail(
			$vulnerabilities,
			$settings
		);
		$mailer->maybe_send();
	}
}
