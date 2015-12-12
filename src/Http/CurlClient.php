<?php

namespace SSNepenthe\Soter\Http;

use RuntimeException;
use SSNepenthe\Soter\Contracts\Http;

class CurlClient implements Http {
	public function __construct( $base_url, $user_agent = null ) {
		if ( ! function_exists( 'curl_init' ) ) {
			throw new RuntimeException( 'cURL is required to use the cURL client' );
		}

		$this->base_url = $base_url;
		$this->user_agent = is_null( $user_agent ) ?
			'Soter Security Checker - v0.1.0 - https://github.com/ssnepenthe/soter' :
			$user_agent;
	}

	public function get( $endpoint = '' ) {
		// Check for trailing slash before concat???
		$url = $this->base_url . $endpoint;

		if ( false === $curl = curl_init() ) {
			throw new RuntimeException('Unable to create a cURL handle.');
		}

		curl_setopt( $curl, CURLOPT_FAILONERROR, false );
		curl_setopt( $curl, CURLOPT_HEADER, true );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, 10 );
		curl_setopt( $curl, CURLOPT_TIMEOUT, 10 );
		curl_setopt( $curl, CURLOPT_URL, $url );
		curl_setopt( $curl, CURLOPT_USERAGENT, $this->user_agent );
		curl_setopt( $curl, CURLOPT_HTTPHEADER, [ 'Accept: application/json' ] );

		$response = curl_exec( $curl );

		if ( false === $response ) {
			$error = curl_error( $curl );
			curl_close( $curl );

			throw new RuntimeException( sprintf( 'cURL Error: %s', $error ) );
		}

		$headers_size = curl_getinfo( $curl, CURLINFO_HEADER_SIZE );
		$headers = substr( $response, 0, $headers_size );
		$body = substr( $response, $headers_size );
		$status_code = curl_getinfo( $curl, CURLINFO_HTTP_CODE );

		curl_close( $curl );

		if ( 404 === $status_code ) {
			throw new RuntimeException( sprintf( 'The specified package/version does not exist at %s (HTTP 404)', $url ) );
		}

		if ( 200 !== $status_code ) {
			throw new RuntimeException( sprintf( 'Unknown error (HTTP %s)', $status_code ) );
		}

		return [ $headers, $body ];
	}
}
