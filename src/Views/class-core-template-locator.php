<?php
/**
 * Core template locator implementation.
 *
 * @package soter
 */

namespace Soter\Views;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * This class uses the WordPress core locate_template() function to locate templates.
 */
class Core_Template_Locator implements Template_Locator_Interface {
	/**
	 * Return the highest priority template available from a list of template files.
	 *
	 * @param  string[] $templates List of template files.
	 *
	 * @return string
	 */
	public function locate( array $templates ) {
		return locate_template( $templates );
	}
}
