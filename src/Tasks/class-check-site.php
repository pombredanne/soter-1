<?php

namespace SSNepenthe\Soter\Tasks;

use SSNepenthe\Soter\Checker;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Check_Site {
	const HOOK = 'soter_run_check';

	protected $checker;

	public function __construct( Checker $checker ) {
		$this->checker = $checker;
	}

	public function init() {
		add_action( self::HOOK, [ $this, 'run_task' ] );
	}

	public function run_task() {
		if ( ! defined( 'DOING_CRON' ) || ! DOING_CRON ) {
			return;
		}

		// This is it - Logging and notification is handled by dedicated listeners.
		$this->checker->check_site();
	}
}
