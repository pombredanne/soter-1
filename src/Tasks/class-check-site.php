<?php
/**
 * Run scheduled site checks.
 *
 * @package soter
 */

namespace Soter\Tasks;

use Soter_Core\Checker;
use Soter\Options\Options_Manager;

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

	protected $options;

	/**
	 * Class constructor.
	 *
	 * @param Checker $checker Checker instance.
	 */
	public function __construct( Checker $checker, Options_Manager $options ) {
		$this->checker = $checker;
		$this->options = $options;
	}

	/**
	 * Run the site check.
	 */
	public function run_task() {
		// This is it - Logging and notification is handled by dedicated listeners.
		try {
			$this->checker->check_site( $this->options->ignored_packages() );
		} catch ( \RuntimeException $e ) {
			// @todo How to handle HTTP error? Ignore? Log? Email user?
		}
	}
}
