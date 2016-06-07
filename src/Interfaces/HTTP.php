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
}
