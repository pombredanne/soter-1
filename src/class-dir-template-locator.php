<?php

namespace SSNepenthe\Soter;

class Dir_Template_Locator implements Template_Locator_Interface {
	protected $dir;

	public function __construct( $dir ) {
		$this->dir = realpath( $dir );
	}

	public function locate( array $templates ) {
		foreach ( $templates as $template ) {
			$template = trailingslashit( $this->dir ) . $template;

			if ( file_exists( $template ) ) {
				return $template;
			}
		}

		return '';
	}
}
