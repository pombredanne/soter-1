<?php
/**
 * Simple mailer interface.
 *
 * @package soter
 */

namespace SSNepenthe\Soter\Interfaces;

/**
 * Mailer interface.
 */
interface Mailer {
	/**
	 * Sends a notification if enabled by user.
	 */
	public function maybe_send();
}
