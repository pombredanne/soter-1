<?php
/**
 * WP HTTP API Implementation.
 *
 * @package soter
 */

namespace SSNepenthe\Soter;

/**
 * Simple HTTP client using WP HTTP API.
 */
class WP_Http_Client implements Http_Interface {
	protected $base_url;
	protected $user_agent;

	public function __construct( $base_url, $user_agent = null ) {
		$this->base_url = rtrim( (string) $base_url, '/\\' );
		$this->user_agent = (string) $user_agent;
	}

	/**
	 * Send a GET request to the given endpoint.
	 *
	 * @param  string $endpoint Appended to $base_url to create the URL.
	 *
	 * @return \WP_Error|array
	 */
	public function get( $endpoint ) {
		$url = sprintf( '%s/%s', $this->base_url, $endpoint );
		$args = [ 'user-agent' => $this->user_agent ];

		$response = wp_safe_remote_get( $url, $args );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		return [
			wp_remote_retrieve_response_code( $response ),
			wp_remote_retrieve_body( $response ),
		];
	}
}
