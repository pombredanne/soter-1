<?php
/**
 * HTTP client interface.
 *
 * @package soter
 */

namespace SSNepenthe\Soter\Contracts;

/**
 * This interface defines the required methods for an HTTP client.
 */
interface Http {
	/**
	 * Send a GET request.
	 *
	 * @param  string $endpoint Endpoint to send request to.
	 *
	 * @return array            Status code at index 0, body at index 1
	 */
	public function get( $endpoint );

	/**
	 * Set the URL root that all requests are sent to.
	 *
	 * @param string $url_root The URL root.
	 */
	public function set_url_root( $url_root );

	/**
	 * Set the user agent to be used for all requests.
	 *
	 * @param string $user_agent User agent string.
	 */
	public function set_user_agent( $user_agent );
}
