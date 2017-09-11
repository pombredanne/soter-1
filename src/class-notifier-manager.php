<?php
/**
 * Notifier manager class.
 *
 * @package soter
 */

namespace Soter;

use Soter_Core\Vulnerabilities;

/**
 * Defines the notifier manager class.
 */
class Notifier_Manager {
	/**
	 * List of registered notifiers.
	 *
	 * @var array
	 */
	protected $notifiers = [];

	/**
	 * Options instance.
	 *
	 * @var Options_Manager
	 */
	protected $options;

	/**
	 * Class constructor.
	 *
	 * @param Options_Manager $options   Options instance.
	 * @param array           $notifiers List of notifiers for registration.
	 */
	public function __construct( Options_Manager $options, array $notifiers = [] ) {
		$this->options = $options;

		foreach ( $notifiers as $notifier ) {
			$this->add( $notifier );
		}
	}

	/**
	 * Add or register a new notifier instance.
	 *
	 * @param Notifier_Interface $notifier Notifier instance.
	 *
	 * @return void
	 */
	public function add( Notifier_Interface $notifier ) {
		$this->notifiers[] = $notifier;
	}

	/**
	 * Invoke the notify method on all active notifiers.
	 *
	 * @param  Vulnerabilities $vulnerabilities List of vulnerabilities.
	 *
	 * @return void
	 */
	public function notify( Vulnerabilities $vulnerabilities ) {
		if ( ! $this->should_notify( $vulnerabilities ) ) {
			return;
		}

		foreach ( $this->notifiers as $notifier ) {
			if ( $notifier->is_enabled() ) {
				$notifier->notify( $vulnerabilities );
			}
		}
	}

	/**
	 * Determine whether a notifications should be sent.
	 *
	 * @param  Vulnerabilities $vulnerabilities List of vulnerabilities.
	 *
	 * @return boolean
	 */
	protected function should_notify( Vulnerabilities $vulnerabilities ) {
		if ( $vulnerabilities->is_empty() ) {
			return false;
		}

		if ( $this->options->should_nag ) {
			return true;
		}

		return $vulnerabilities->hash() !== $this->options->last_scan_hash;
	}
}
