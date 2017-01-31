<?php
/**
 * HTTP Client Interface.
 *
 * @package soter
 */

namespace SSNepenthe\Soter\Http;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Simple HTTP GET client interface.
 */
interface Http_Interface {
	/**
	 * Send a GET request to a given endpoint.
	 */
	public function get( $endpoint );
}
