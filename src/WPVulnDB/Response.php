<?php

namespace SSNepenthe\Soter\WPVulnDB;

class Response {
	protected $response;
	protected $object;

	public function __construct( $response, $slug ) {
		$this->response = $response;

		$object = json_decode( $response );

		if ( ! $this->valid( $object ) ) {
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

	public function __get( $key ) {
		if ( isset( $this->object->{$key} ) ) {
			return $this->object->{$key};
		}

		return null;
	}

	public function advisories_by_version( $version = null ) {
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

	public function vulnerabilities_by_version( $version = null ) {
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

	protected function valid( $object ) {
		if ( null === $object || JSON_ERROR_NONE !== json_last_error() ) {
			return false;
		}

		return true;
	}
}
