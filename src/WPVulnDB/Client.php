<?php
/**
 * WPVulnDB v2 API client.
 *
 * @package soter.
 */

namespace SSNepenthe\Soter\WPVulnDB;

use Doctrine\Common\Cache\CacheProvider;
use SSNepenthe\Soter\Config;
use SSNepenthe\Soter\Contracts\Http;

/**
 * This class defines a simple client for interacting with WPVulnDB (v2) API.
 */
class Client {
	/**
	 * HTTP client instance.
	 *
	 * @var Http
	 */
	protected $http_client;

	/**
	 * Doctrine cache provider instance.
	 *
	 * @var CacheProvider
	 */
	protected $cache_provider;

	/**
	 * Client constructor.
	 *
	 * @param Http          $client   HTTP client.
	 * @param CacheProvider $provider Cache provider.
	 */
	public function __construct( Http $client, CacheProvider $provider ) {
		$this->cache_provider = $provider;
		$this->http_client = $client;
		$this->http_client->set_url_root( 'https://wpvulndb.com/api/v2/' );
		$this->http_client->set_user_agent( Config::get( 'http.useragent' ) );
	}

	/**
	 * Make a GET request to the plugin endpoint.
	 *
	 * @param  string $slug Plugin slug.
	 *
	 * @return Response     HTTP response object.
	 *
	 * @throws \InvalidArgumentException When $slug is not a string.
	 */
	public function check_plugin( $slug ) {
		if ( ! is_string( $slug ) ) {
			throw new \InvalidArgumentException( sprintf(
				'The slug parameter is required to be string, was: %s',
				gettype( $slug )
			) );
		}

		$response = $this->get_and_cache( sprintf( 'plugins/%s', $slug ) );

		return new Response( $response, $slug );
	}

	/**
	 * Make a GET request to the theme endpoint.
	 *
	 * @param  string $slug Theme slug.
	 *
	 * @return Response     HTTP response object.
	 *
	 * @throws \InvalidArgumentException When $slug is not a string.
	 */
	public function check_theme( $slug ) {
		if ( ! is_string( $slug ) ) {
			throw new \InvalidArgumentException( sprintf(
				'The slug parameter is required to be string, was: %s',
				gettype( $slug )
			) );
		}

		$response = $this->get_and_cache( sprintf( 'themes/%s', $slug ) );

		return new Response( $response, $slug );
	}

	/**
	 * Make a GET request to the wordpresses endpoint.
	 *
	 * @param  string $version WordPress version.
	 *
	 * @return Response        HTTP response object.
	 *
	 * @throws \InvalidArgumentException When $slug is not a string.
	 */
	public function check_wordpress( $version ) {
		if ( ! is_string( $version ) ) {
			throw new \InvalidArgumentException( sprintf(
				'The version parameter is required to be string, was: %s',
				gettype( $version )
			) );
		}

		$slug = str_replace( '.', '', $version );
		$response = $this->get_and_cache(
			sprintf( 'wordpresses/%s', $slug )
		);

		return new Response( $response, $version );
	}

	/**
	 * Send a GET request and cache the response.
	 *
	 * @param  string $endpoint Endpoint for request to be sent to.
	 *
	 * @return array            Response code at index 0, body at index 1.
	 *
	 * @throws \InvalidArgumentException When $endpoint is not a string.
	 */
	protected function get_and_cache( $endpoint ) {
		if ( ! is_string( $endpoint ) ) {
			throw new \InvalidArgumentException( sprintf(
				'The endpoint parameter is required to be string, was: %s',
				gettype( $endpoint )
			) );
		}

		if ( $this->cache_provider->contains( $endpoint ) ) {
			return $this->cache_provider->fetch( $endpoint );
		}

		$response = $this->http_client->get( $endpoint );
		$this->cache_provider->save(
			$endpoint,
			$response,
			Config::get( 'cache.ttl' )
		);

		return $response;
	}
}
