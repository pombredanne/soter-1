<?php
/**
 * WPVulnDB response wrapper.
 *
 * @package soter
 */

namespace SSNepenthe\Soter\WPVulnDB;

/**
 * This class provides a simple wrapper for responses from WPVulnDB.
 */
class Response {
	/**
	 * Response body.
	 *
	 * @var string
	 */
	protected $body;

	/**
	 * Response headers.
	 *
	 * @var array
	 */
	protected $headers;

	/**
	 * JSON decoded response.
	 *
	 * @var \stdClass
	 */
	protected $object;

	/**
	 * Response status code.
	 *
	 * @var int
	 */
	protected $status;

	/**
	 * Cache of vulnerabilities by version.
	 *
	 * @var array
	 */
	protected $version_cache = [];

	/**
	 * Constructor.
	 *
	 * @param array  $response API response array:
	 *                         [0] status code,
	 *                         [1] headers array,
	 *                         [2] response body.
	 *
	 * @param string $slug     Theme/plugin slug or WordPress version.
	 *
	 * @throws  \InvalidArgumentException When slug is not a string.
	 * @throws  \RuntimeException When JSON response cannot be decoded.
	 */
	public function __construct( array $response, $slug ) {
		if ( ! is_string( $slug ) ) {
			throw new \InvalidArgumentException(
				sprintf(
					'The slug parameter is required to be string, was: %s',
					gettype( $slug )
				)
			);
		}

		list($this->status, $this->headers, $this->body) = $response;

		if ( 200 !== $this->status ) {
			$this->object = new \stdClass;
			$this->object->error = new \stdClass;
			$this->object->error->status_code = $this->status;
		} else {
			$object = json_decode( $this->body );

			if ( null === $object || JSON_ERROR_NONE !== json_last_error() ) {
				throw new \RuntimeException(
					'Response does not appear to be valid JSON'
				);
			}

			$this->object = $object->{$slug};

			if ( isset( $this->object->last_updated ) ) {
				$this->object->last_updated = new \DateTime(
					$this->object->last_updated
				);
			}

			$this->object->vulnerabilities = array_map(
				[ $this, 'instantiate_vulnerability' ],
				$this->object->vulnerabilities
			);
		}
	}

	/**
	 * Access all preoperties on the response.
	 *
	 * @param  string $key Response property key.
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
	 * Headers getter.
	 *
	 * @return array
	 */
	public function headers() {
		return $this->headers;
	}

	/**
	 * Determine whether this instance represents a non-200 response.
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
	public function status() {
		return $this->status;
	}

	/**
	 * Vulnerabilities getter.
	 *
	 * @return array
	 */
	public function vulnerabilities() {
		if ( $this->is_error() ) {
			return [];
		}

		return $this->object->vulnerabilities;
	}

	/**
	 * Get all vulnerabilities that affect a particular package version.
	 *
	 * @param  string|null $version Version string.
	 *
	 * @return array
	 *
	 * @throws \InvalidArgumentException When version is not string|null.
	 */
	public function vulnerabilities_by_version( $version = null ) {
		if ( ! is_null( $version ) && ! is_string( $version ) ) {
			throw new \InvalidArgumentException(
				'The version parameter is required to be string|null, was: %s',
				gettype( $version )
			);
		}

		if ( $this->is_error() || empty( $this->object->vulnerabilities ) ) {
			return [];
		}

		if ( is_null( $version ) ) {
			return $this->object->vulnerabilities;
		}

		if ( isset( $this->version_cache[ $version ] ) ) {
			return $this->version_cache[ $version ];
		}

		$vulnerabilities = [];

		foreach ( $this->object->vulnerabilities as $vulnerability ) {
			if ( $vulnerability->affects_version( $version ) ) {
				$vulnerabilities[] = $vulnerability;
			}
		}

		return $this->version_cache[ $version ] = $vulnerabilities;
	}

	/**
	 * Instantiate a vulnerability object from a JSON decoded vulnerability.
	 *
	 * @param  \stdClass $vulnerability Package vulnerability.
	 *
	 * @return Vulnerability
	 */
	protected function instantiate_vulnerability( \stdClass $vulnerability ) {
		return new Vulnerability( $vulnerability );
	}
}
