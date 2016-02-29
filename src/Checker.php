<?php
/**
 * Functionality for checking a composer.lock file against the WPVulnDB database.
 *
 * @package soter
 */

namespace SSNepenthe\Soter;

use Exception;
use SSNepenthe\ComposerUtilities\WordPress\Lock;
use SSNepenthe\Soter\Contracts\Http;
use SSNepenthe\Soter\WPVulnDB\ApiRequest;
use SSNepenthe\Soter\WPVulnDB\ApiResponse;

/**
 * This class retrieves all of your WordPress packages from a composer.lock file
 * and queries the WPVulnDB API to check them for security vulnerabilities.
 */
class Checker {
	/**
	 * Basic HTTP GET client.
	 *
	 * @var Http
	 */
	protected $client;

	/**
	 * Object representation of our composer.lock file.
	 *
	 * @var WordPressLock
	 */
	protected $lock;

	/**
	 * Messages explaining the current state of our packages.
	 *
	 * @var array
	 */
	protected $messages = [
		'error' => [],
		'ok' => [],
		'unknown' => [],
		'vulnerable' => [],
	];

	/**
	 * Set up the object.
	 *
	 * @param string $path Path to composer.lock file.
	 * @param Http $client Http instance for making GET requests.
	 */
	public function __construct( $path, Http $client ) {
		$this->lock = new Lock( $path );
		$this->client = $client;
	}

	/**
	 * Check the composer.lock file against the WPVulnDB API.
	 *
	 * @return array
	 */
	public function check() {
		foreach ( $this->wordpress_packages() as $package ) {
			try {
				$request = new ApiRequest( $package );

				$body = $this->client->get( $request->endpoint() );

				$response = new ApiResponse(
					$body,
					$package->is_wp_core() ? $package->version() : $request->slug()
				);

				$key = empty( $response->vulnerabilities_by_version(
					$package->version()
				) ) ? 'ok' : 'vulnerable';

				if (
					in_array( $package->version(), [ 'dev-master', 'dev-trunk' ] ) &&
					'vulnerable' === $key
				) {
					$key = 'unknown';
				}

				$this->messages[ $key ][ $package->name() ] = [
					'version' => $package->version(),
					'advisories' => $response->advisories_by_version(
						$package->version()
					),
				];
			} catch ( Exception $e ) {
				$this->messages['error'][ $package->name() ] = [
					'version' => $package->version(),
					'advisories' => [ $e->getMessage() ],
				];
			}
		}

		return $this->messages;
	}

	/**
	 * Determine if the supplied package is a WordPress package, excluding mu-plugins.
	 *
	 * @param LockPackage $package The package to check.
	 *
	 * @return boolean
	 */
	protected function is_wordpress_package( $package ) {
		if ( $package->is_wpackagist_package() || $package->is_wp_core() ) {
			return true;
		}

		return false;
	}

	/**
	 * Get an array of all WordPress packages, excluding mu-plugins.
	 *
	 * @return array
	 */
	protected function wordpress_packages() {
		$core = $this->lock->core_packages() ?: [];
		$plugin = $this->lock->plugin_packages() ?: [];
		$theme = $this->lock->theme_packages() ?: [];

		$packages = array_merge( $core, $plugin, $theme );

		$packages = array_filter(
			$packages,
			[ $this, 'is_wordpress_package' ]
		);

		return $packages;
	}
}
