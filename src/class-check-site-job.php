<?php
/**
 * Run scheduled site checks.
 *
 * @package soter
 */

namespace Soter;

use Soter_Core\Checker;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * This class hooks the checker to our WP-Cron hook.
 */
class Check_Site_Job {
	/**
	 * Checker instance.
	 *
	 * @var Checker
	 */
	protected $checker;

	/**
	 * Options manager instance.
	 *
	 * @var Options_Manager
	 */
	protected $options;

	/**
	 * Class constructor.
	 *
	 * @param Checker         $checker Checker instance.
	 * @param Options_Manager $options Options manager instance.
	 */
	public function __construct( Checker $checker, Options_Manager $options ) {
		$this->checker = $checker;
		$this->options = $options;
	}

	/**
	 * Run the site check.
	 *
	 * @return void
	 */
	public function run() {
		try {
			$vulnerabilities = $this->checker->check_site( $this->options->ignored_packages );

			do_action( 'soter_check_complete', $vulnerabilities );

			$this->options->get_store()->set( 'last_scan_hash', $vulnerabilities->hash() );
		} catch ( \RuntimeException $e ) {
			// @todo How to handle HTTP error? Ignore? Log? Email user?
		}
	}
}
