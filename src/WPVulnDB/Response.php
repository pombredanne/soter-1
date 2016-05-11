<?php
/**
 * Response wrapper.
 *
 * @package soter
 */

namespace SSNepenthe\Soter\WPVulnDB;

/**
 * This class defines a wrapper for a response from WPVulnDB.
 */
class Response {
	/**
	 * Response body.
	 *
	 * @var string
	 */
	protected $body;

	/**
	 * JSON decoded response.
	 *
	 * @var \stdClass
	 */
	protected $object;

	/**
	 * Status code.
	 *
	 * @var int
	 */
	protected $status_code;

	/**
	 * Response constructor.
	 *
	 * @param array  $response HTTP response, status code at index 0, body at 1.
	 * @param string $slug     Package slug.
	 *
	 * @throws \InvalidArgumentException When $slug is not a string.
	 * @throws \RuntimeException When there is an error decoding the response.
	 */
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

	/**
	 * __get magic method.
	 *
	 * @param  string $key Property key.
	 *
	 * @return mixed
	 */
	public function __get( $key ) {
		if ( isset( $this->object->{$key} ) ) {
			return $this->object->{$key};
		}

		return null;
	}

	/**
	 * Retrieve formatted advisories for a given package version.
	 *
	 * @param  null|string $version Package version.
	 *
	 * @return array                Formatted advisories.
	 *
	 * @throws \InvalidArgumentException When $version is not string or null.
	 */
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

	/**
	 * Determine whether this instance represents a non-200 status response.
	 *
	 * @return boolean
	 */
	public function is_error() {
		return isset( $this->object->error );
	}

	/**
	 * Status code getter.
	 *
	 * @return int
	 */
	public function status_code() {
		return $this->status_code;
	}

	/**
	 * Retrieve a list of all vulnerabilities affecting a given package version.
	 *
	 * @param  null|string $version Package version.
	 *
	 * @return array                List of vulnerability objects.
	 *
	 * @throws \InvalidArgumentException When $version is not string or null.
	 */
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

	/**
	 * Formats an advisory string from a vulnerability object.
	 *
	 * @param  Vulnerability $vulnerability Package vulnerability.
	 *
	 * @return string                       Formatted advisory.
	 */
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

	/**
	 * Instantiates a Vulnerability object from a given \stdClass vulnerability.
	 *
	 * @param  \stdClass $vulnerability JSON decoded vulnerability.
	 *
	 * @return Vulnerability
	 */
	protected function instantiate_vulnerability( \stdClass $vulnerability ) {
		return new Vulnerability( $vulnerability );
	}
}
