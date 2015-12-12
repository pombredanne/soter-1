<?php
/**
 * Simple Http Client interface.
 *
 * @package soter
 */

namespace SSNepenthe\Soter\Contracts;

interface Http {
	/**
	 * Retrieve the response body for a given API endpoint.
	 *
	 * @param  string $endpoint API endpoint
	 *
	 * @return string JSON reponse
	 */
	public function get( $endpoint );
}
