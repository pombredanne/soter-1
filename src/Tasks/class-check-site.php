<?php
/**
 * Run scheduled site checks.
 *
 * @package soter
 */

namespace SSNepenthe\Soter\Tasks;

use SSNepenthe\Soter\Checker;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * This class hooks the checker to our WP-Cron hook.
 */
class Check_Site {
	const HOOK = 'soter_run_check';

	/**
	 * Checker instance.
	 *
	 * @var Checker
	 */
	protected $checker;

	/**
	 * Class constructor.
	 *
	 * @param Checker $checker Checker instance.
	 */
	public function __construct( Checker $checker ) {
		$this->checker = $checker;
	}

	/**
	 * Hooks the task functionality in to WordPress.
	 */
	public function init() {
		add_action( self::HOOK, [ $this, 'run_task' ] );
	}

	/**
	 * Run the site check.
	 */
	public function run_task() {
		// This is it - Logging and notification is handled by dedicated listeners.
		$this->checker->check_site();
	}
}
