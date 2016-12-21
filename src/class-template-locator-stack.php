<?php

namespace SSNepenthe\Soter;

class Template_Locator_Stack implements Template_Locator_Interface {
	protected $stack = [];

	public function __construct( array $locators = [] ) {
		foreach ( $locators as $locator ) {
			$this->push( $locator );
		}
	}

	public function locate( array $templates ) {
		foreach ( $this->stack as $locator ) {
			if ( $template = $locator->locate( $templates ) ) {
				return $template;
			}
		}

		return '';
	}

	public function push( Template_Locator_Interface $locator ) {
		$this->stack[] = $locator;
	}
}
