<?php

namespace SSNepenthe\Soter\WPVulnDB;

class ApiResponse {
	protected $reponse;

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

		$json = json_decode( $response_body );

		if ( is_null( $json ) ) {
			throw new \InvalidArgumentException(
				sprintf(
                    'Argument 1 passed to %s could not be json decoded.',
                    __METHOD__,
                    gettype( $json )
                )
			);
		}

		$this->response = $json;
	}

	public function is_vulnerable( $version ) {
		$vulnerable = false;

		foreach ( $this->response as $key => $value ) {
			if ( ! empty( $value->vulnerabilities ) ) {
				foreach ( $value->vulnerabilities as $vulnerability ) {
					if ( version_compare( $version, $vulnerability->fixed_in, '<' ) ) {
						$vulnerable = true;
					}
				}
			}
		}

		return $vulnerable;
	}
}
