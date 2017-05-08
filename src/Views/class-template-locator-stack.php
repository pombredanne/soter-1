<?php
/**
 * Delegates to an arbitrary number of template locating strategies.
 *
 * @package soter
 */

namespace Soter\Views;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * This class accepts any number of template locators and delegates to each until an
 * appropriate template file has been found.
 */
class Template_Locator_Stack implements Template_Locator_Interface {
	/**
	 * List of template locators.
	 *
	 * @var Template_Locator_Interface[]
	 */
	protected $stack = [];

	/**
	 * Class constructor.
	 *
	 * @param Template_Locator_Interface[] $locators List of template locators.
	 */
	public function __construct( array $locators = [] ) {
		foreach ( $locators as $locator ) {
			$this->push( $locator );
		}
	}

	/**
	 * Loops through template locators returning the first found template.
	 *
	 * @param  string[] $templates List of template files.
	 *
	 * @return string
	 */
	public function locate( array $templates ) {
		foreach ( $this->stack as $locator ) {
			if ( $template = $locator->locate( $templates ) ) {
				return $template;
			}
		}

		return '';
	}

	/**
	 * Adds a template locator to the stack.
	 *
	 * @param  Template_Locator_Interface $locator Template locator.
	 */
	public function push( Template_Locator_Interface $locator ) {
		$this->stack[] = $locator;
	}
}
