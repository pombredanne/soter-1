<?php
/**
 * Wraps a response from the WPVulnDB API.
 *
 * @package soter
 */

namespace SSNepenthe\Soter\WPVulnDB;

use RuntimeException;

/**
 * This class provides some convenience functionality for WPVulnDB API responses.
 */
class ApiResponse {
	/**
	 * Raw JSON response.
	 *
	 * @var string
	 */
	protected $response;

	/**
	 * Decoded JSON response.
	 *
	 * @var stdClass
	 */
	protected $object;

	/**
	 * List of vulnerabilities associated with the request.
	 *
	 * @var array
	 */
	protected $vulnerabilities = [];

	/**
	 * Set up the object.
	 *
	 * @param string $response JSON response.
	 * @param string $slug     Package slug associated with the response.
	 *
	 * @throws RuntimeException If the response cannot be decoded properly.
	 */
	public function __construct( $response, $slug ) {
		$this->response = $response;

		$object = json_decode( $response );

		if ( ! $this->valid( $object ) ) {
			throw new RuntimeException( 'Response does not appear to be valid JSON' );
		}

		$this->object = $object->{$slug};
	}

	/**
	 * Get the response vulnerabilities.
	 *
	 * @return array.
	 */
	public function vulnerabilities() {
		return $this->object->vulnerabilities;
	}

	/**
	 * Get the advisories for a specific package version.
	 *
	 * @param  string $version The package version.
	 *
	 * @return array
	 */
	public function advisories_by_version( $version ) {
		if ( empty( $this->vulnerabilities_by_version( $version ) ) ) {
			return [ 'There are no known vulnerabilities in this package.' ];
		}

		$r = [];

		foreach ( $this->vulnerabilities_by_version( $version ) as $vulnerability ) {
			$urls = isset( $vulnerability->references->url ) ?
				implode( "\n", $vulnerability->references->url ) :
				'';

			$fixed = is_null( $vulnerability->fixed_in ) ?
				'Not fixed yet' :
				sprintf( 'Fixed in v%s', $vulnerability->fixed_in );

			$r[] = sprintf( "%s\n%s\n%s", $vulnerability->title, $urls, $fixed );
		}

		return $r;
	}

	/**
	 * Get a list of vulnerabilities for a specific package version.
	 *
	 * @param string $version Package version.
	 *
	 * @return array
	 */
	public function vulnerabilities_by_version( $version ) {
		if ( empty( $this->object->vulnerabilities ) ) {
			return [];
		}

		$vulnerabilities = [];

		foreach ( $this->object->vulnerabilities as $vulnerability ) {
			if ( is_null( $vulnerability->fixed_in ) ) {
				$vulnerabilities[] = $vulnerability;
				continue;
			}

			if ( version_compare( $version, $vulnerability->fixed_in, '<' ) ) {
				$vulnerabilities[] = $vulnerability;
				continue;
			}
		}

		return $vulnerabilities;
	}

	/**
	 * Check whether a JSON response was able to be decoded.
	 *
	 * @param  stdClass $object JSON decoded object.
	 *
	 * @return bool
	 */
	protected function valid( $object ) {
		if ( null === $object || JSON_ERROR_NONE !== json_last_error() ) {
			return false;
		}

		return true;
	}
}
