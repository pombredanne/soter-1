<?php

namespace Soter;

use Soter_Core\Vulnerabilities;

class Notifier_Manager {
	protected $notifiers = [];
	protected $options;

	public function __construct( Options_Manager $options, array $notifiers = [] ) {
		$this->options = $options;

		foreach ( $notifiers as $notifier ) {
			$this->add( $notifier );
		}
	}

	public function add( Notifier_Interface $notifier ) {
		$this->notifiers[] = $notifier;
	}

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
