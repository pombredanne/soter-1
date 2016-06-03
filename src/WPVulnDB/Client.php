<?php
/**
 * WPVulnDB client.
 *
 * @package soter
 */

namespace SSNepenthe\Soter\WPVulnDB;

/**
 * The actual WPVulnDB client implementation.
 */
class Client {
	const HTTP_URL_ROOT = 'https://wpvulndb.com/api/v2';
	const PACKAGE_NAME = 'WPVulnDB PHP Client';
	const PACKAGE_URL = 'https://github.com/ssnepenthe/wpvulndb-client';
	const PACKAGE_VERSION = '0.1.0';

	/**
	 * Cache provider.
	 *
	 * @var CacheInterface
	 */
	protected $cache;

	/**
	 * Cache lifetime in seconds.
	 *
	 * @var int
	 */
	protected $cache_lifetime;

	/**
	 * Http client.
	 *
	 * @var HttpInterface
	 */
	protected $http;

	/**
	 * Constructor.
	 *
	 * @param HttpInterface  $http           Http client.
	 * @param CacheInterface $cache          Cache provider.
	 * @param int            $cache_lifetime Cache entry lifetime.
	 */
	public function __construct(
		HttpInterface $http,
		CacheInterface $cache,
		$cache_lifetime = 60 * 60 * 24
	) {
		$this->cache = $cache;
		$this->cache_lifetime = $cache_lifetime;
		$this->http = $http;

		if ( ! $this->http->get_url_root() ) {
			$this->http->set_url_root( self::HTTP_URL_ROOT );
		}

		if ( ! $this->http->get_user_agent() ) {
			$this->http->set_user_agent( sprintf(
				'%s - %s - %s',
				self::PACKAGE_NAME,
				self::PACKAGE_VERSION,
				self::PACKAGE_URL
			) );
		}
	}

	/**
	 * Make a request to the plugin endpoint.
	 *
	 * @param  string $slug Plugin slug.
	 *
	 * @return Response
	 *
	 * @throws  \InvalidArgumentException When slug is not a string.
	 */
	public function plugin( $slug ) {
		if ( ! is_string( $slug ) ) {
			throw new \InvalidArgumentException( sprintf(
				'The slug parameter is required to be string, was: %s',
				gettype( $slug )
			) );
		}

		return $this->get_and_cache( sprintf( 'plugins/%s', $slug ), $slug );
	}

	/**
	 * Make a request to the theme endpoint.
	 *
	 * @param  string $slug Theme slug.
	 *
	 * @return Response
	 *
	 * @throws  \InvalidArgumentException When slug is not a string.
	 */
	public function theme( $slug ) {
		if ( ! is_string( $slug ) ) {
			throw new \InvalidArgumentException( sprintf(
				'The slug parameter is required to be string, was: %s',
				gettype( $slug )
			) );
		}

		return $this->get_and_cache( sprintf( 'themes/%s', $slug ), $slug );
	}

	/**
	 * Make a request to the WordPress endpoint.
	 *
	 * @param  string $version WordPress version.
	 *
	 * @return Response
	 *
	 * @throws  \InvalidArgumentException When version is not a string.
	 */
	public function wordpress( $version ) {
		if ( ! is_string( $version ) ) {
			throw new \InvalidArgumentException( sprintf(
				'The version parameter is required to be string, was: %s',
				gettype( $version )
			) );
		}

		$slug = str_replace( '.', '', $version );

		return $this->get_and_cache(
			sprintf( 'wordpresses/%s', $slug ),
			$version
		);
	}

	/**
	 * Retrieve response from cache if it exists otherwise make a GET request.
	 *
	 * @param  string $endpoint      Request endpoint.
	 * @param  string $root_property The theme/plugin slug or WordPress version.
	 *
	 * @return Response
	 */
	protected function get_and_cache( $endpoint, $root_property ) {
		if ( $this->cache->contains( $endpoint ) ) {
			$response = $this->cache->fetch( $endpoint );
		} else {
			$response = $this->http->get( $endpoint );

			$this->cache->save( $endpoint, $response, $this->cache_lifetime );
		}

		return new Response( $response, $root_property );
	}
}
