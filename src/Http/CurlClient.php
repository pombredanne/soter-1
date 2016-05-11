<?php

namespace SSNepenthe\Soter\Http;

use SSNepenthe\Soter\Contracts\Http;

class CurlClient implements Http {
	protected $url_root = null;
	protected $user_agent = null;

	public function __construct() {
		if ( ! function_exists( 'curl_init' ) ) {
			throw new \RuntimeException(
				'cURL is required to use the cURL client'
			);
		}
	}

	public function set_user_agent( $user_agent ) {
		if ( ! is_string( $user_agent ) ) {
			throw new \InvalidArgumentException( sprintf(
				'The user_agent parameter is required to be string, was: %s',
				gettype( $user_agent )
			) );
		}

		return $this->user_agent = $user_agent;
	}

	public function set_url_root( $url_root ) {
		if ( ! is_string( $url_root ) ) {
			throw new \InvalidArgumentException( sprintf(
				'The url_root parameter is required to be string, was: %s',
				gettype( $url_root )
			) );
		}

		if ( ! $url_root = filter_var( $url_root, FILTER_VALIDATE_URL ) ) {
			throw new \RuntimeException(
				'The provided URL root does not appear to be valid'
			);
		}

		return $this->url_root = rtrim( $url_root, '/\\' );
	}

	public function get( $endpoint = '' ) {
		if ( is_null( $this->url_root ) ) {
			throw new \RuntimeException(
				'You must call set_url_root() before making a GET request'
			);
		}

		if ( is_null( $this->user_agent ) ) {
			throw new \RuntimeException(
				'You must call set_user_agent() before making a GET request'
			);
		}

		if ( ! is_string( $endpoint ) ) {
			throw new \InvalidArgumentException( sprintf(
				'The endpoint parameter is required to be string, was: %s',
				gettype( $endpoint )
			) );
		}

		$endpoint = ltrim( $endpoint, '/\\' );

		$url = sprintf( '%s/%s', $this->url_root, $endpoint );

		if ( ! $curl = curl_init() ) {
			throw new \RuntimeException( 'Unable to create a cURL handle' );
		}

		curl_setopt( $curl, CURLOPT_FAILONERROR, false );
		curl_setopt( $curl, CURLOPT_HEADER, true );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, 10 );
		curl_setopt( $curl, CURLOPT_TIMEOUT, 10 );
		curl_setopt( $curl, CURLOPT_URL, $url );
		curl_setopt( $curl, CURLOPT_USERAGENT, $this->user_agent );
		curl_setopt(
			$curl,
			CURLOPT_HTTPHEADER,
			[ 'Accept: application/json' ]
		);

		$response = curl_exec( $curl );

		if ( false === $response ) {
			$error = curl_error( $curl );
			curl_close( $curl );

			throw new \RuntimeException( sprintf( 'cURL Error: %s', $error ) );
		}

		$headers_size = curl_getinfo( $curl, CURLINFO_HEADER_SIZE );
		$body = substr( $response, $headers_size );
		$status_code = curl_getinfo( $curl, CURLINFO_HTTP_CODE );

		curl_close( $curl );

		return [ $status_code, $body ];
	}
}
