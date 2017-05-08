<?php
/**
 * Template locator interface.
 *
 * @package soter
 */

namespace Soter\Views;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * This interface defines the contract for a template locator implementation.
 */
interface Template_Locator_Interface {
	/**
	 * Return the highest priority template available from a list of templates.
	 *
	 * @param  string[] $templates List of template files.
	 *
	 * @return string              Template file or empty string if none found.
	 */
	public function locate( array $templates );
}
