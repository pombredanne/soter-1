<?php

namespace SSNepenthe\Soter\WPVulnDB;

class Response {
	protected $body;
	protected $object;
	protected $status_code;

	public function status_code() {
		return $this->status_code;
	}

	public function __construct( array $response, $slug ) {
		if ( ! is_string( $slug ) ) {
			throw new \InvalidArgumentException( sprintf(
				'The slug parameter is required to be string, was: %s',
				gettype( $slug )
			) );
		}

		list( $this->status_code, $this->body ) = $response;

		if ( 200 !== $this->status_code ) {
			$this->object = new \stdClass;
			$this->object->error = new \stdClass;
			$this->object->error->status_code = $this->status_code;
		} else {
			$object = json_decode( $this->body );

			if ( null === $object || JSON_ERROR_NONE !== json_last_error() ) {
				throw new \RuntimeException(
					'Response does not appear to be valid JSON'
				);
			}

			$this->object = $object->{$slug};

			$this->object->vulnerabilities = array_map(
				[ $this, 'instantiate_vulnerability' ],
				$this->object->vulnerabilities
			);
		}
	}

	public function __get( $key ) {
		if ( isset( $this->object->{$key} ) ) {
			return $this->object->{$key};
		}

		return null;
	}

	public function advisories_by_version( $version = null ) {
		if ( ! is_null( $version ) && ! is_string( $version ) ) {
			throw new \InvalidArgumentException(
				'The version parameter is required to be string|null, was: %s',
				gettype( $version )
			);
		}

		$vulnerabilities = $this->vulnerabilities_by_version( $version );

		if ( empty( $vulnerabilities ) ) {
			return $vulnerabilities;
		}

		$advisories = array_map(
			[ $this, 'build_advisories' ],
			$vulnerabilities
		);

		return $advisories;
	}

	public function is_error() {
		return isset( $this->object->error );
	}

	public function vulnerabilities_by_version( $version = null ) {
		if ( ! is_null( $version ) && ! is_string( $version ) ) {
			throw new \InvalidArgumentException(
				'The version parameter is required to be string|null, was: %s',
				gettype( $version )
			);
		}

		if ( is_null( $version ) || empty( $this->object->vulnerabilities ) ) {
			return $this->object->vulnerabilities;
		}

		$vulnerabilities = [];

		foreach ( $this->object->vulnerabilities as $vulnerability ) {
			if ( $vulnerability->affects_version( $version ) ) {
				$vulnerabilities[] = $vulnerability;
			}
		}

		return $vulnerabilities;
	}

	protected function build_advisories( Vulnerability $vulnerability ) {
		$urls = isset( $vulnerability->references->url ) ?
			implode( "\n", $vulnerability->references->url ) :
			'';

		$fixed = is_null( $vulnerability->fixed_in ) ?
			'Not fixed yet' :
			sprintf( 'Fixed in v%s', $vulnerability->fixed_in );

		return sprintf(
			"%s\n%s\n%s",
			$vulnerability->title,
			$urls,
			$fixed
		);
	}

	protected function instantiate_vulnerability( \stdClass $vulnerability ) {
		return new Vulnerability( $vulnerability );
	}
}
