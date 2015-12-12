<?php
/**
 * Handle HTTP requests using the WordPress HTTP API.
 *
 * @package soter
 */

namespace SSNepenthe\Soter\Http;

use RuntimeException;
use SSNepenthe\Soter\Contracts\Http;

/**
 * This class can be used to make HTTP Get requests with the WP HTTP API.
 */
class WPClient implements Http {
	/**
	 * Set up the object.
	 *
	 * @param string $base_url   The base URL all requests will be built from.
	 * @param string $user_agent User agent to connect with.
	 *
	 * @throws RuntimeException If the WordPress HTTP functions do not exist.
	 */
	public function __construct( $base_url, $user_agent = null ) {
		if ( ! function_exists( 'wp_remote_get' ) ) {
			throw new RuntimeException( 'The WordPress HTTP API is required to use the WP client' );
		}

		$this->base_url = $base_url;
		$this->user_agent = is_null( $user_agent ) ?
			'Soter Security Checker - v0.1.0 - https://github.com/ssnepenthe/soter' :
			$user_agent;
	}

	/**
	 * Make a GET request to the given endpoint.
	 *
	 * @param  string $endpoint Endpoint to build the URL with.
	 *
	 * @return string JSON response.
	 */
	public function get( $endpoint = '' ) {
		$url = trailingslashit( $this->base_url ) . $endpoint;

		$args = [
			'user-agent' => $this->user_agent,
		];

		$response = wp_safe_remote_get( $url, $args );

		if ( is_wp_error( $response ) ) {
			throw new RuntimeException( sprintf( 'WP Error: %s', $response->get_error_message() ) );
		}

		$response_code = wp_remote_retrieve_response_code( $response );

		if ( 404 === $response_code ) {
			throw new RuntimeException( sprintf( 'The specified package/version does not exist at %s (HTTP 404)', $url ) );
		}

		if ( 200 !== $response_code ) {
			throw new RuntimeException( sprintf( 'Unknown error (HTTP %s)', $status_code ) );
		}

		return wp_remote_retrieve_body( $response );
	}
}
