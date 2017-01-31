<?php
/**
 * WPScan API client.
 *
 * @package soter
 */

namespace SSNepenthe\Soter\WPScan;

use SSNepenthe\Soter\Http\Http_Interface;
use SSNepenthe\Soter\Cache\Cache_Interface;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * The actual WPScan API client implementation.
 */
class Api_Client {
	const BASE_URL = 'https://wpvulndb.com/api/v2/';

	/**
	 * Cache provider.
	 */
	protected $cache;

	/**
	 * Http client.
	 */
	protected $http;

	/**
	 * Constructor.
	 */
	public function __construct( Http_Interface $http, Cache_Interface $cache ) {
		$this->http = $http;
		$this->cache = $cache;
	}

	/**
	 * Make a request to the plugins endpoint.
	 */
	public function plugins( $slug ) {
		return $this->get_and_cache( 'plugins/' . $slug );
	}

	/**
	 * Make a request to the themes endpoint.
	 */
	public function themes( $slug ) {
		return $this->get_and_cache( 'themes/' . $slug );
	}

	/**
	 * Make a request to the WordPresses endpoint.
	 */
	public function wordpresses( $slug ) {
		return $this->get_and_cache( 'wordpresses/' . $slug );
	}

	/**
	 * Retrieve response from cache if it exists otherwise make a GET request.
	 */
	protected function get_and_cache( $endpoint ) {
		$url = self::BASE_URL . (string) $endpoint;
		$cache_key = 'api_response_' . $url;

		if ( $this->cache->contains( $cache_key ) ) {
			list( $status, $headers, $body ) = $this->cache->fetch( $cache_key );

			return new Response( $status, $headers, $body );
		}

		$response = $this->http->get( $url );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$this->cache->save( $cache_key, $response, HOUR_IN_SECONDS );

		list( $status, $headers, $body ) = $response;

		return new Response( $status, $headers, $body );
	}
}
