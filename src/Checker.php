<?php

namespace SSNepenthe\Soter;

use SSNepenthe\Soter\WPVulnDB\Client;
use SSNepenthe\Soter\Options\Settings;

class Checker {
	protected $client;
	protected $settings;
	protected $vulnerabilities;

	public function __construct(
		Client $client = null,
		Settings $settings = null
	) {
		$this->client = is_null( $client ) ? new Client : $client;
		$this->settings = is_null( $settings ) ? new Settings : $settings;
	}

	public function check() {
		$this->vulnerabilities = [];

		$this->check_installed_plugins();
		$this->check_installed_themes();
		$this->check_current_wordpress_version();

		return $this->vulnerabilities;
	}

	protected function check_installed_plugins() {
		$plugins = array_filter(
			get_plugins(),
			[ $this, 'plugin_filter' ],
			ARRAY_FILTER_USE_KEY
		);

		foreach ( $plugins as $file => $headers ) {
			list( $slug, $basename ) = explode( DIRECTORY_SEPARATOR, $file );

			$response = $this->client->plugins( $slug );

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

	protected function check_installed_themes() {
		$themes = array_filter( wp_get_themes(), [ $this, 'filter_themes' ] );

		foreach ( soter_get_themes() as $name => $object ) {
			$response = $this->client->themes( $object->stylesheet );

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

	protected function check_current_wordpress_version() {
		$response = $this->client->wordpresses( get_bloginfo( 'version' ) );

		$vulnerabilities = $response->vulnerabilities_by_version();

		if ( ! empty( $vulnerabilities ) ) {
			$this->vulnerabilities = array_merge(
				$this->vulnerabilities,
				$vulnerabilities
			);
		}
	}

	protected function plugin_filter( $key ) {
		list( $slug, $basename ) = explode( DIRECTORY_SEPARATOR, $key );

		return ! in_array( $slug, $this->settings->ignored_plugins );
	}

	protected function theme_filter( $theme ) {
		return ! in_array(
			$theme->stylesheet,
			$this->settings->ignored_themes
		);
	}
}
