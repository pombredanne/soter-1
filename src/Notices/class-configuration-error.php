<?php

namespace Soter\Notices;

use Soter\Views\Template;

class Configuration_Error {
	protected $template;

	public function __construct( Template $template ) {
		$this->template = $template;
	}

	public function print_notice() {
		$this->template->output( 'notices/notifications-disabled' );
	}
}
