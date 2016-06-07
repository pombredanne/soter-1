<?php
/**
 * WP-CLI output formatter interface.
 *
 * @package soter
 */

namespace SSNepenthe\Soter\Interfaces;

/**
 * Formatter interface.
 */
interface Formatter {
	/**
	 * Outputs results to screen.
	 *
	 * @param  array $vulnerabilities Array of vulnerability objects.
	 */
	public function display_results( array $vulnerabilities );
}
