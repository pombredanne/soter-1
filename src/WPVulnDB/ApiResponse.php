<?php
/**
 * Wraps a response from the WPVulnDB API.
 *
 * @package soter
 */

namespace SSNepenthe\Soter\WPVulnDB;

/**
 * This class provides some convenience functionality for WPVulnDB API responses.
 */
class ApiResponse {
	/**
	 * JSON decoded response.
	 *
	 * @var array
	 */
	protected $response;

	/**
	 * Array of vulnerabilities by version.
	 *
	 * @var array
	 */
	protected $vulnerabilities = [];

	/**
	 * Set up the object.
	 *
	 * @param string $response_body JSON response body from WPVulnDB.
	 *
	 * @throws \InvalidArgumentException If passed argument is not a string or invalid json.
	 */
	public function __construct( $response_body ) {
		if ( ! is_string( $response_body ) ) {
			throw new \InvalidArgumentException(
				sprintf(
					'Argument 1 passed to %s must be of type string, %s given.',
					__METHOD__,
					gettype( $response_body )
				)
			);
		}

		if ( is_null( $json = json_decode( $response_body, true ) ) ) {
			throw new \InvalidArgumentException(
				sprintf(
					'Argument 1 passed to %s could not be json decoded.',
					__METHOD__
				)
			);
		}

		$this->response = current( $json );
	}

	/**
	 * Determine if a package is vulnerable based on version.
	 *
	 * @param string $version Package version.
	 *
	 * @return boolean
	 */
	public function is_vulnerable( $version ) {
		return ! empty( $this->vulnerabilities( $version ) );
	}

	/**
	 * Get a list of vulnerabilities by version, generating if necessary.
	 *
	 * @param string $version Package version.
	 *
	 * @return array
	 */
	public function vulnerabilities( $version ) {
		if ( empty( $this->response['vulnerabilities'] ) ) {
			return;
		}

		if ( ! isset( $this->vulnerabilities[ $version ] ) ) {
			$this->vulnerabilities[ $version ] = [];

			foreach ( $this->response['vulnerabilities'] as $vulnerability ) {
				if (
					is_null( $vulnerability['fixed_in'] ) ||
					version_compare( $version, $vulnerability['fixed_in'], '<' )
				) {
					$fixed = is_null( $vulnerability['fixed_in'] ) ?
						'not yet fixed' :
						sprintf( 'fixed in %s', $vulnerability['fixed_in'] );

					$this->vulnerabilities[ $version ][] = sprintf(
						'%s vulnerability, %s',
						$vulnerability['vuln_type'],
						$fixed
					);
				}
			}
		}

		return $this->vulnerabilities[ $version ];
	}
}
