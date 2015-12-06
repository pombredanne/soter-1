<?php

namespace SSNepenthe\Soter;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\TransferException;
use SSNepenthe\ComposerUtilities\ComposerLock;
use SSNepenthe\Soter\WPVulnDB\ApiResponse;

class Checker
{
	protected $client;
	protected $lock;
	protected $messages = [
		'error' => [],
		'ok' => [],
		'vulnerable' => [],
	];
	protected $packages;

	public function __construct( $lock ) {
		$this->client = new Client(
			[
				'base_uri' => 'https://wpvulndb.com/api/v2/',
				'headers'  => [
					'User-Agent' => 'SSNepenthe/Soter:v0.1.0 - https://github.com/ssnepenthe/soter',
				],
			]
		);

		$this->lock = new ComposerLock( $lock );

		$this->packages = $this->wordpress_packages();
	}

	public function check() {
		foreach ( $this->packages as $package ) {
			if (
				'wpackagist-' !== substr( $package->name, 0, 11 ) &&
				'wordpress-core' !== $package->type
			) {
				continue;
			}

			list( $endpoint, $vendor, $slug ) = $this->get_route_info( $package );

			try {
				$response = $this->fetch( $endpoint . '/' . $slug );

				if ( $response->is_vulnerable( $package->version ) ) {
					$this->messages['vulnerable'][] = $package->name;
				} else {
					$this->messages['ok'][] = $package->name;
				}
			} catch ( ServerException $e ) {
				$this->messages['error'][] = sprintf(
					'Server error while checking %s',
					$package->name
				);
			} catch ( ClientException $e ) {
				$this->messages['error'][] = sprintf(
					'Received %s error while checking %s',
					$e->getResponse()->getStatusCode(),
					$package->name
				);
			} catch ( TransferException $e ) {
				$this->messages['error'][] = sprintf(
					'Error while checking %s',
					$package->name
				);
			}
		}

		return $this->messages;
	}

	protected function fetch( $route ) {
		$response = $this->client->get( $route );

		return new ApiResponse( (string) $response->getBody() );
	}

	protected function get_route_info( $package ) {
		list( $vendor, $name ) = explode( '/', $package->name );

		switch ( $package->type ) {
			case 'wordpress-plugin':
				$endpoint = 'plugins';
				$slug = $name;
				break;
			case 'wordpress-theme':
				$endpoint = 'themes';
				$slug = $name;
				break;
			case 'wordpress-core':
				$endpoint = 'wordpresses';
				$slug = str_replace( '.', '', $package->version );
				break;
		}

		return [ $endpoint, $vendor, $slug ];
	}

	protected function wordpress_packages() {
		$packages = array_merge(
			$this->lock->packages(),
			$this->lock->devPackages()
		);

		$packages = array_filter(
			$packages,
			[ $this, 'is_wordpress_package' ]
		);

		return $packages;
	}

	protected function is_wordpress_package( $package ) {
		if ( 'wordpress-plugin' === $package->type ) {
			return true;
		}

		if ( 'wordpress-theme' === $package->type ) {
			return true;
		}

		if ( 'wordpress-core' === $package->type ) {
			return true;
		}

		// WPVulnDB does not have any mu-plugins so no need to include them.

		return false;
	}
}
