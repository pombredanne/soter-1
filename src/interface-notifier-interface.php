<?php

namespace Soter;

use Soter_Core\Vulnerabilities;

interface Notifier_Interface {
	public function is_enabled();
	public function notify( Vulnerabilities $vulnerabilities );
}
