<?php
/**
 * WPScan API response wrapper.
 *
 * @package soter
 */

namespace SSNepenthe\Soter\WPScan;

use DateTime;
use RuntimeException;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * This class provides a simple wrapper for responses from the WPScan API.
 */
class Response {
	/**
	 * Response body.
	 */
	protected $body;

	/**
	 * JSON decoded response.
	 */
	protected $data;

	protected $headers;

	/**
	 * Response status code.
	 */
	protected $status;

	/**
	 * Cache of vulnerabilities by version.
	 */
	protected $version_cache = [];

	public function __construct( $status, array $headers, $body ) {
		$this->status = intval( $status );
		$this->headers = $headers;
		$this->body = (string) $body;
		$this->data = $this->generate_data_array();
	}

	/**
	 * Magic getter, proxies all requests to the data array.
	 */
	public function __get( $key ) {
		if ( isset( $this->data[ $key ] ) ) {
			return $this->data[ $key ];
		}

		return null;
	}

	/**
	 * Determine whether this instance represents a non-200 response.
	 */
	public function is_error() {
		return isset( $this->data['error'] );
	}

	/**
	 * Vulnerabilities getter.
	 */
	public function vulnerabilities() {
		if ( $this->is_error() ) {
			return [];
		}

		return $this->data['vulnerabilities'];
	}

	/**
	 * Get all vulnerabilities that affect a particular package version.
	 */
	public function vulnerabilities_by_version( $version = null ) {
		if ( $this->is_error() || empty( $this->data['vulnerabilities'] ) ) {
			return [];
		}

		if ( is_null( $version ) ) {
			return $this->data['vulnerabilities'];
		}

		$version = (string) $version;

		if ( isset( $this->version_cache[ $version ] ) ) {
			return $this->version_cache[ $version ];
		}

		$vulnerabilities = [];

		foreach ( $this->data['vulnerabilities'] as $vulnerability ) {
			if ( $vulnerability->affects_version( $version ) ) {
				$vulnerabilities[] = $vulnerability;
			}
		}

		return $this->version_cache[ $version ] = $vulnerabilities;
	}

	protected function generate_data_array() {
		// May want to revisit - Non-200 does not automatically mean error.
		$response_ok = 200 === $this->status;
		$is_json = isset( $this->headers['content-type'] )
			&& false !== strpos( $this->headers['content-type'], 'application/json' );

		if ( $response_ok && $is_json ) {
			try {
				return $this->generate_ok_data_array();
			} catch ( RuntimeException $e ) {
				return $this->generate_error_data_array( null, $e->getMessage() );
			}
		}

		return $this->generate_error_data_array();
	}

	protected function generate_error_data_array( $code = null, $message = null ) {
		$code = is_null( $code ) ? $this->status : intval( $code );
		// Consider using status message as default message here?
		$message = is_null( $message ) ? 'Invalid endpoint' : (string) $message;

		return [
			'error' => compact( 'code', 'message' ),
		];
	}

	protected function generate_ok_data_array() {
		$decoded = json_decode( $this->body, true );

		if ( null === $decoded || JSON_ERROR_NONE !== json_last_error() ) {
			throw new RuntimeException(
				'Response does not appear to be valid JSON'
			);
		}

		$data = current( $decoded );
		$data['slug'] = key( $decoded );

		if ( isset( $data['last_updated'] ) ) {
			$data['last_updated'] = new DateTime(
				$data['last_updated']
			);
		}

		$data['vulnerabilities'] = array_map(
			function( array $vulnerability ) {
				return new Vulnerability( $vulnerability );
			},
			$data['vulnerabilities']
		);

		return $data;
	}
}
