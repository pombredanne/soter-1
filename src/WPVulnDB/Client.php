<?php

namespace SSNepenthe\Soter\WPVulnDB;

use Doctrine\Common\Cache\CacheProvider;
use SSNepenthe\Soter\Config;
use SSNepenthe\Soter\Contracts\Http;

class Client {
	protected $http_client;
	protected $cache_provider;

	public function __construct( Http $client, CacheProvider $provider ) {
		$this->cache_provider = $provider;
		$this->http_client = $client;
		$this->http_client->set_url_root( 'https://wpvulndb.com/api/v2/' );
		$this->http_client->set_user_agent( Config::get( 'http.useragent' ) );
	}

	public function check_plugin( $slug ) {
		$response = $this->get_and_cache( sprintf( 'plugins/%s', $slug ) );

		return new Response( $response, $slug );
	}

	public function check_theme( $slug ) {
		$response = $this->get_and_cache( sprintf( 'themes/%s', $slug ) );

		return new Response( $response, $slug );
	}

	public function check_wordpress( $version ) {
		$slug = str_replace( '.', '', $version );
		$response = $this->get_and_cache(
			sprintf( 'wordpresses/%s', $slug )
		);

		return new Response( $response, $version );
	}

	protected function get_and_cache( $endpoint ) {
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
