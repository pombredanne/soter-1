<?php
/**
 * Notifier interface
 *
 * @package soter
 */

namespace Soter;

use Soter_Core\Vulnerabilities;

/**
 * Defines the notifier interface.
 */
interface Notifier_Interface {
	/**
	 * Check whether this notifier is currently enabled.
	 *
	 * @return boolean
	 */
	public function is_enabled();

	/**
	 * Prepare and send a notification.
	 *
	 * @param  Vulnerabilities $vulnerabilities List of vulnerabilities.
	 *
	 * @return void
	 */
	public function notify( Vulnerabilities $vulnerabilities );
}
