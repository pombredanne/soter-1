<?php
/**
 * WP HTTP API Implementation.
 *
 * @package soter
 */

namespace SSNepenthe\Soter\HTTP;

/**
 * WP HTTP API implementation for use within WordPress.
 */
class WPClient extends BaseHTTPClient {
	/**
	 * Constructor.
	 *
	 * @param string $url_root   API URL root.
	 * @param string $user_agent Client user agent.
	 *
	 * @throws  \RuntimeException When used outside of a WordPress context.
	 */
	public function __construct( $url_root = null, $user_agent = null ) {
		if ( ! function_exists( 'wp_remote_get' ) ) {
			throw new \RuntimeException(
				'WPHttpClient can only be used within WordPress'
			);
		}

		parent::__construct( $url_root, $user_agent );
	}

	/**
	 * Send a GET request to the given endpoint.
	 *
	 * @param  string $endpoint Appended to $url_root to create the URL.
	 *
	 * @return array
	 *
	 * @throws \RuntimeException When $response is a WP_Error.
	 */
	public function get( $endpoint ) {
		$this->validate_get_args( $endpoint );

		$endpoint = ltrim( $endpoint, '/\\' );

		$url = sprintf( '%s/%s', $this->url_root, $endpoint );

		$args = [ 'user-agent' => $this->user_agent ];

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
