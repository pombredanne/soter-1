<?php

namespace SSNepenthe\Soter\WPVulnDB;

class ApiResponse {
	protected $response;
	protected $vulnerabilities = [];

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

	public function is_vulnerable( $version ) {
		return ! empty( $this->vulnerabilities( $version ) );
	}

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
