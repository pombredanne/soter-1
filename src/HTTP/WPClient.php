<?php
/**
 * WP HTTP API Implementation.
 *
 * @package soter
 */

namespace SSNepenthe\Soter\HTTP;

use SSNepenthe\Soter\Interfaces\HTTP;

/**
 * Simple HTTP client using WP HTTP API.
 */
class WPClient implements HTTP {
	/**
	 * Constructor.
	 *
	 * @throws  \RuntimeException When used outside of a WordPress context.
	 */
	public function __construct() {
		if ( ! function_exists( 'wp_remote_get' ) ) {
			throw new \RuntimeException(
				'WPHttpClient can only be used within WordPress'
			);
		}
	}

	/**
	 * Send a GET request to the given endpoint.
	 *
	 * @param  string $endpoint Appended to $url_root to create the URL.
	 *
	 * @return array
	 *
	 * @throws \InvalidArgumentException When endpoint is not a string.
	 * @throws \RuntimeException When $response is a WP_Error.
	 */
	public function get( $endpoint ) {
		if ( ! is_string( $endpoint ) ) {
			throw new \InvalidArgumentException( sprintf(
				'The endpoint parameter is required to be string, was: %s',
				gettype( $endpoint )
			) );
		}

		$endpoint = ltrim( $endpoint, '/\\' );

		$url = sprintf( 'https://wpvulndb.com/api/v2/%s', $endpoint );

		$name = 'Soter Security Checker';
		$version = '0.3.0';
		$soter_url = 'https://github.com/ssnepenthe/soter';

		$args = [
			'user-agent' => sprintf(
				'%s | v%s | %s',
				$name,
				$version,
				$soter_url
			),
		];

		$response = wp_safe_remote_get( $url, $args );

		if ( is_wp_error( $response ) ) {
			throw new \RuntimeException( sprintf(
				'WP Error: %s',
				$response->get_error_message()
			) );
		}

		return [
			wp_remote_retrieve_response_code( $response ),
			wp_remote_retrieve_headers( $response ),
			wp_remote_retrieve_body( $response ),
		];
	}
}
