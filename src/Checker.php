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
	protected $messages = [];

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
	}

	public function check() {
		foreach ( $this->wordpress_packages() as $package ) {
			list( $endpoint, $vendor, $slug ) = $this->get_route_info( $package );

			try {
				$response = $this->fetch( $endpoint, $slug );

				if ( $response->is_vulnerable( $package->version() ) ) {
					$this->messages[] = [
						'package' => $package->name(),
						'status' => 'VULNERABLE',
						'message' => implode( "\n", $response->vulnerabilities( $package->version() ) ),
					];
				} else {
					$this->messages[] = [
						'package' => $package->name(),
						'status' => 'SAFE',
						'message' => '',
					];
				}
			} catch ( ServerException $e ) {
				$this->messages[] = [
					'package' => $package->name(),
					'status' => 'ERROR',
					'message' => sprintf( 'Server error while checking %s', $package->name() ),
				];
			} catch ( ClientException $e ) {
				$this->messages[] = [
					'package' => $package->name(),
					'status' => 'ERROR',
					'message' => sprintf(
						'Received %s error while checking %s',
						$e->getResponse()->getStatusCode(),
						$package->name()
					),
				];
			} catch ( TransferException $e ) {
				$this->messages[] = [
					'package' => $package->name(),
					'status' => 'ERROR',
					'message' => sprintf( 'Error while checking %s', $package->name() ),
				];
			}
		}

		return $this->messages;
	}

	protected function fetch( $endpoint, $slug ) {
		$response = $this->client->get( $endpoint . '/' . $slug );

		return new ApiResponse( (string) $response->getBody() );
	}

	protected function get_route_info( $package ) {
		list( $vendor, $name ) = explode( '/', $package->name() );

		switch ( $package->type() ) {
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
				$slug = str_replace( '.', '', $package->version() );
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
		if ( $package->isWpackagistPackage() || $package->isWPCorePackage() ) {
			return true;
		}

		return false;
	}
}
