<?php
/**
 * Run scheduled site checks.
 *
 * @package soter
 */

namespace Soter\Jobs;

use Soter_Core\Checker;
use Soter\Options\Options_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * This class hooks the checker to our WP-Cron hook.
 */
class Check_Site {
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
	public function run() {
		try {
			$vulnerabilities = $this->checker->check_site(
				$this->options->ignored_packages()
			);

			$hash = $this->generate_scan_hash( $vulnerabilities );
			$has_changed = $hash !== $this->options->last_scan_hash();

			do_action(
				'soter_check_complete',
				$vulnerabilities,
				$has_changed
			);

			$this->options->set_last_scan_hash( $hash );
		} catch ( \RuntimeException $e ) {
			// @todo How to handle HTTP error? Ignore? Log? Email user?
		}
	}

	public static function get_hook() {
		return 'soter_run_check';
	}

	protected function generate_scan_hash( array $vulnerabilities ) {
		if ( empty( $vulnerabilities ) ) {
			return '';
		}

		$ids = array_map( 'intval', wp_list_pluck( $vulnerabilities, 'id' ) );
		sort( $ids );

		return hash( 'sha1', implode( ':', $ids ) );
	}
}
