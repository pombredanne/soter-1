<?php
/**
 * WP HTTP API Implementation.
 *
 * @package soter
 */

namespace SSNepenthe\Soter\Http;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Simple HTTP client using WP HTTP API.
 */
class WP_Http_Client implements Http_Interface {
	protected $user_agent;

	public function __construct( $user_agent ) {
		$this->user_agent = (string) $user_agent;
	}

	/**
	 * Send a GET request to the given URL.
	 */
	public function get( $url ) {
		$args = [ 'user-agent' => $this->user_agent ];

		$response = wp_safe_remote_get( $url, $args );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		return [
			wp_remote_retrieve_response_code( $response ),
			wp_remote_retrieve_headers( $response )->getAll(),
			wp_remote_retrieve_body( $response ),
		];
	}
}
