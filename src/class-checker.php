<?php
/**
 * Checks all plugins, themes and core against WPVulnDB.
 *
 * @package soter
 */

namespace SSNepenthe\Soter;

use SSNepenthe\Soter\WPVulnDB\Client;

/**
 * Check all plugins themes and core.
 */
class Checker {
	/**
	 * WPVulnDB Client.
	 *
	 * @var Client
	 */
	protected $client;

	protected $has_run = false;

	/**
	 * Plugin settings object
	 *
	 * @var Map_Option
	 */
	protected $settings;

	/**
	 * Array of vulnerability objects.
	 *
	 * @var array
	 */
	protected $vulnerabilities = [];

	/**
	 * Constructor.
	 *
	 * @param Client     $client   WPVulnDB Client.
	 * @param Map_Option $settings Plugin settings object.
	 */
	public function __construct( Client $client, Map_Option $settings ) {
		$this->client = $client;
		$this->settings = $settings;
	}

	/**
	 * Check all plugins, themes and core.
	 *
	 * @return array
	 */
	public function check() {
		$this->vulnerabilities = [];
		$this->has_run = true;

		$this->check_installed_plugins();
		$this->check_installed_themes();
		$this->check_current_wordpress_version();

		return $this->vulnerabilities;
	}

	public function get_client() {
		return $this->client;
	}

	public function get_messages() {
		if ( ! $this->has_run ) {
			$this->check();
		}

		if ( empty( $this->vulnerabilities ) ) {
			return [];
		}

		$messages = [];

		foreach ( $this->vulnerabilities as $vulnerability ) {
			$message = [
				'title' => $vulnerability->title,
				'meta' => [],
				'links' => [],
			];

			if ( ! is_null( $vulnerability->published_date ) ) {
				$message['meta'][] = sprintf(
					'Published %s',
					$vulnerability->published_date->format( 'd M Y' )
				);
			}

			if ( isset( $vulnerability->references->url ) ) {
				foreach ( $vulnerability->references->url as $url ) {
					$parsed = wp_parse_url( $url );

					$host = isset( $parsed['host'] ) ?
						$parsed['host'] :
						$url;

					$message['links'][ $url ] = $host;
				}
			}

			$message['links'][ sprintf(
				'https://wpvulndb.com/vulnerabilities/%s',
				$vulnerability->id
			) ] = 'wpvulndb.com';

			if ( is_null( $vulnerability->fixed_in ) ) {
				$message['meta'][] = 'Not fixed yet';
			} else {
				$message['meta'][] = sprintf(
					'Fixed in v%s',
					$vulnerability->fixed_in
				);
			}

			$messages[] = $message;
		}

		return $messages;
	}

	/**
	 * Check plugins.
	 */
	protected function check_installed_plugins() {
		$plugins = array_filter( get_plugins(), function( $key ) {
			list( $slug, $basename ) = explode( DIRECTORY_SEPARATOR, $key );

			return ! in_array(
				$slug,
				$this->settings->get( 'ignored_plugins', [] ),
				true
			);
		}, ARRAY_FILTER_USE_KEY );

		foreach ( $plugins as $file => $headers ) {
			list( $slug, $basename ) = explode( DIRECTORY_SEPARATOR, $file );

			$response = $this->client->plugins( $slug );

			if ( is_wp_error( $response ) ) {
				continue;
			}

			$vulnerabilities = $response->vulnerabilities_by_version(
				$headers['Version']
			);

			if ( empty( $vulnerabilities ) ) {
				continue;
			}

			$this->vulnerabilities = array_merge(
				$this->vulnerabilities,
				$vulnerabilities
			);
		}
	}

	/**
	 * Check themes.
	 */
	protected function check_installed_themes() {
		$themes = array_filter( wp_get_themes(), function( $theme ) {
			return ! in_array(
				$theme->stylesheet,
				$this->settings->get( 'ignored_themes', [] ),
				true
			);
		} );

		foreach ( $themes as $name => $object ) {
			$response = $this->client->themes( $object->stylesheet );

			if ( is_wp_error( $response ) ) {
				continue;
			}

			$vulnerabilities = $response->vulnerabilities_by_version(
				$object->version
			);

			if ( empty( $vulnerabilities ) ) {
				continue;
			}

			$this->vulnerabilities = array_merge(
				$this->vulnerabilities,
				$vulnerabilities
			);
		}
	}

	/**
	 * Check core.
	 */
	protected function check_current_wordpress_version() {
		$response = $this->client->wordpresses( get_bloginfo( 'version' ) );

		if ( is_wp_error( $response ) ) {
			return;
		}

		$vulnerabilities = $response->vulnerabilities_by_version();

		if ( ! empty( $vulnerabilities ) ) {
			$this->vulnerabilities = array_merge(
				$this->vulnerabilities,
				$vulnerabilities
			);
		}
	}
}
