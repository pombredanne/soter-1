<?php
/**
 * Directory template locator implementation.
 *
 * @package soter
 */

namespace SSNepenthe\Soter\Views;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * This class searches for templates within a specified directory.
 */
class Dir_Template_Locator implements Template_Locator_Interface {
	/**
	 * Directory to search in.
	 *
	 * @var string
	 */
	protected $dir;

	/**
	 * Class constructor.
	 *
	 * @param string $dir Directory to search in.
	 */
	public function __construct( $dir ) {
		$this->dir = realpath( $dir );
	}

	/**
	 * Return the highest priority template available with the given directory.
	 *
	 * @param  string[] $templates List of template files.
	 *
	 * @return string
	 */
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
