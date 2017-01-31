<?php

namespace SSNepenthe\Soter\Views;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

interface Template_Locator_Interface {
	public function locate( array $templates );
}
