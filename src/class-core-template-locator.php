<?php

namespace SSNepenthe\Soter;

class Core_Template_Locator implements Template_Locator_Interface {
	public function locate( array $templates ) {
		return locate_template( $templates );
	}
}
