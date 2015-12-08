<?php
/**
 * Functionality for checking a composer.lock file against the WPVulnDB database.
 *
 * @package soter
 */

namespace SSNepenthe\Soter;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\TransferException;
use SSNepenthe\ComposerUtilities\WordPressLock;
use SSNepenthe\Soter\WPVulnDB\ApiResponse;

/**
 * This class retrieves all of your WordPress packages from a composer.lock file
 * and queries the WPVulnDB API to check them for security vulnerabilities.
 */
class Checker
{
	/**
	 * Guzzle Client Interface
	 *
	 * @var GuzzleHtp\ClientInterface
	 */
	protected $client;

	/**
	 * Object representation of our composer.lock file.
	 *
	 * @var SSNepenthe\ComposerUtilities\ComposerLock
	 */
	protected $lock;

	/**
	 * Messages explaining the current state of our packages.
	 *
	 * @var array
	 */
	protected $messages = [];

	/**
	 * Set up the object.
	 *
	 * @param string $lock Path to composer.lock file.
	 */
	public function __construct( $lock ) {
		$this->client = new Client(
			[
				'base_uri' => 'https://wpvulndb.com/api/v2/',
				'headers'  => [
					'User-Agent' => 'SSNepenthe/Soter:v0.1.0 - https://github.com/ssnepenthe/soter',
				],
			]
		);

		$this->lock = new WordPressLock( $lock );
	}

	/**
	 * Check the composer.lock file against the WPVulnDB API.
	 *
	 * @return array
	 */
	public function check() {
		foreach ( $this->wordpress_packages() as $package ) {
			list( $endpoint, $vendor, $slug ) = $this->get_route_info( $package );

			try {
				$response = $this->fetch( $endpoint, $slug );

				if ( $response->is_vulnerable( $package->version() ) ) {
					$this->messages[] = [
						'package' => $package->name(),
						'status' => 'VULNERABLE',
						'message' => implode( "\n", $response->vulnerabilities( $package->version() ) ),
					];
				} else {
					$this->messages[] = [
						'package' => $package->name(),
						'status' => 'SAFE',
						'message' => '',
					];
				}
			} catch ( ServerException $e ) {
				$this->messages[] = [
					'package' => $package->name(),
					'status' => 'ERROR',
					'message' => sprintf( 'Server error while checking %s', $package->name() ),
				];
			} catch ( ClientException $e ) {
				$this->messages[] = [
					'package' => $package->name(),
					'status' => 'ERROR',
					'message' => sprintf(
						'Received %s error while checking %s',
						$e->getResponse()->getStatusCode(),
						$package->name()
					),
				];
			} catch ( TransferException $e ) {
				$this->messages[] = [
					'package' => $package->name(),
					'status' => 'ERROR',
					'message' => sprintf( 'Error while checking %s', $package->name() ),
				];
			}
		}

		return $this->messages;
	}

	/**
	 * Put together the API uri and make a GET request to it.
	 *
	 * @param string $endpoint WPVulnDB API endpoint: 'wordpresses', 'plugins', or 'themes'.
	 * @param string $slug The package slug as used by wp.org.
	 *
	 * @return SSNepenthe\Soter\WPVulnDB\ApiResponse
	 */
	protected function fetch( $endpoint, $slug ) {
		$response = $this->client->get( $endpoint . '/' . $slug );

		return new ApiResponse( (string) $response->getBody() );
	}

	/**
	 * Get the necessary info to create our API uri.
	 *
	 * @param string $package Package name.
	 *
	 * @return array
	 */
	protected function get_route_info( $package ) {
		list( $vendor, $name ) = explode( '/', $package->name() );

		switch ( $package->type() ) {
			case 'wordpress-plugin':
				$endpoint = 'plugins';
				$slug = $name;
				break;
			case 'wordpress-theme':
				$endpoint = 'themes';
				$slug = $name;
				break;
			case 'wordpress-core':
				$endpoint = 'wordpresses';
				$slug = str_replace( '.', '', $package->version() );
				break;
		}

		return [ $endpoint, $vendor, $slug ];
	}

	/**
	 * Determine if the supplied package is a WordPress package, excluding mu-plugins.
	 *
	 * @param SSNepenthe\ComposerUtilities\LockPackage $package The package to check.
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
		$packages = array_merge(
			$this->lock->core_packages(),
			$this->lock->plugin_packages(),
			$this->lock->theme_packages()
		);

		$packages = array_filter(
			$packages,
			[ $this, 'is_wordpress_package' ]
		);

		return $packages;
	}
}
