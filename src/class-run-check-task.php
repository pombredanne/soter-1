<?php

namespace SSNepenthe\Soter;

class Run_Check_Task {
	const HOOK = 'SSNepenthe\\Soter\\run_check';

	protected $checker;
	protected $notifier;
	protected $results;

	public function __construct(
		Checker $checker,
		Notifier_Interface $notifier,
		List_Option $results
	) {
		$this->checker = $checker;
		$this->notifier = $notifier;
		$this->results = $results;
	}

	public function init() {
		add_action( self::HOOK, [ $this, 'run_task' ] );
	}

	public function run_task() {
		if ( ! defined( 'DOING_CRON' ) || ! DOING_CRON ) {
			return;
		}

		// @todo Move to checker?
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$messages = $this->checker->get_messages();

		if ( empty( $messages ) ) {
			return;
		}

		$this->results->reset();

		foreach ( $messages as $message ) {
			$this->results->add( $message );
		}

		$this->results->save();

		$this->notifier->set_data( $messages );
		$this->notifier->notify();
	}
}
