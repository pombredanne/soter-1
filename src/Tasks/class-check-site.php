<?php
/**
 * Run scheduled site checks.
 *
 * @package soter
 */

namespace SSNepenthe\Soter\Tasks;

use Soter_Core\Checker;
use SSNepenthe\Soter\Options\Map_Option;

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

	protected $settings;

	/**
	 * Class constructor.
	 *
	 * @param Checker $checker Checker instance.
	 */
	public function __construct( Checker $checker, Map_Option $settings ) {
		$this->checker = $checker;
		$this->settings = $settings;
	}

	/**
	 * Run the site check.
	 */
	public function run_task() {
		// This is it - Logging and notification is handled by dedicated listeners.
		$ignored = array_merge(
			$this->settings->get( 'ignored_plugins', [] ),
			$this->settings->get( 'ignored_themes', [] )
		);

		$this->checker->check_site( $ignored );
	}
}
