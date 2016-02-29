<?php
/**
 * Convenience functionality for a wpvulndb.com API request.
 *
 * @package soter
 */

namespace SSNepenthe\Soter\WPVulnDB;

use SSNepenthe\ComposerUtilities\WordPress\Package;

/**
 * This class provides the details needed for an API request based on a package.
 */
class ApiRequest {
	/**
	 * Full API endpoint.
	 *
	 * @var string
	 */
	protected $endpoint;

	/**
	 * API route based on package type.
	 *
	 * @var string
	 */
	protected $path;

	/**
	 * Package slug.
	 *
	 * @var string
	 */
	protected $slug;

	/**
	 * Set up the object.
	 *
	 * @param WordPressPackage $package An individual WordPress package.
	 */
	public function __construct( Package $package ) {
		$this->path = $this->get_path( $package );
		$this->slug = $this->get_slug( $package );

		$this->endpoint = sprintf( '%s/%s', $this->path, $this->slug );
	}

	/**
	 * Get the endpoint for this package.
	 *
	 * @return string
	 */
	public function endpoint() {
		return $this->endpoint;
	}

	/**
	 * Get the route for this package.
	 *
	 * @return string
	 */
	public function path() {
		return $this->path;
	}

	/**
	 * Get the slug for this package.
	 *
	 * @return string
	 */
	public function slug() {
		return $this->slug;
	}

	/**
	 * Determine the correct route for this package.
	 *
	 * @param  WordPressPackage $package An individual WordPress package.
	 *
	 * @return string
	 */
	protected function get_path( Package $package ) {
		if ( $package->is_wp_plugin() ) {
			return 'plugins';
		}

		if ( $package->is_wp_core() ) {
			return 'wordpresses';
		}

		if ( $package->is_wp_theme() ) {
			return 'themes';
		}

		// Should never be the case, but just to be safe...
		return '';
	}

	/**
	 * Determine the correct package slug.
	 *
	 * @param  WordPressPackage $package An individual WordPress package.
	 *
	 * @return string
	 */
	protected function get_slug( Package $package ) {
		if ( $package->is_wp_core() ) {
			return str_replace( '.', '', $package->version() );
		}

		list( $vendor, $name ) = explode( '/', $package->name() );

		return $name;
	}
}
