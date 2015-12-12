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
	protected $response;

	protected $object;

	protected $vulnerabilities = [];

	/**
	 * Set up the object.
	 *
	 * @param string $response_body JSON response body from WPVulnDB.
	 *
	 * @throws \InvalidArgumentException If passed argument is not a string or invalid json.
	 */
	public function __construct( $response, $slug ) {
		// Verify json is valid?
		$this->response = $response;

		$object = json_decode( $response );

		if ( ! $this->valid( $object ) ) {
			throw new RuntimeException( 'Response does not appear to be valid JSON' );
		}

		$this->object = $object->{$slug};
	}

	public function vulnerabilities() {
		return $this->object->vulnerabilities;
	}

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
				"Not fixed yet" :
				sprintf( "Fixed in v%s", $vulnerability->fixed_in );

			$r[] = $vulnerability->title . $urls . $fixed;
		}

		return $r;
	}

	/**
	 * Get a list of vulnerabilities by version.
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

	protected function valid( $object ) {
		if ( null === $object || JSON_ERROR_NONE !== json_last_error() ) {
			return false;
		}

		return true;
	}
}
