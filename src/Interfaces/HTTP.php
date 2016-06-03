<?php
/**
 * HTTP Client Interface.
 *
 * @package soter
 */

namespace SSNepenthe\Soter\Interfaces;

/**
 * Simple HTTP GET client interface.
 */
interface HTTP {
	/**
	 * Send a GET request to a given endpoint.
	 *
	 * @param  string $endpoint Appended to $url_root to create the URL.
	 *
	 * @return array            API response array:
	 *         					[0] status code,
	 *                          [1] headers array,
	 *                          [2] response body.
	 */
	public function get( $endpoint );

	/**
	 * URL root getter.
	 *
	 * @return string
	 */
	public function get_url_root();

	/**
	 * User agent string getter.
	 *
	 * @return string
	 */
	public function get_user_agent();

	/**
	 * Set the API URL root.
	 *
	 * @param string $url URL root.
	 */
	public function set_url_root( $url );

	/**
	 * Set the user agent.
	 *
	 * @param string $ua User agent.
	 */
	public function set_user_agent( $ua );
}
