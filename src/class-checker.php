<?php
/**
 * Checks all plugins, themes and core against WPVulnDB.
 *
 * @package soter
 */

namespace SSNepenthe\Soter;

use SSNepenthe\Soter\WPVulnDB\Client;
use SSNepenthe\Soter\Options\Settings;

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

	/**
	 * Plugin settings object
	 *
	 * @var Settings
	 */
	protected $settings;

	/**
	 * Array of vulnerability objects.
	 *
	 * @var array
	 */
	protected $vulnerabilities;

	/**
	 * Constructor.
	 *
	 * @param Client|null   $client   WPVulnDB Client.
	 * @param Settings|null $settings Plugin settings object.
	 */
	public function __construct(
		Client $client = null,
		Settings $settings = null
	) {
		$this->client = is_null( $client ) ? new Client : $client;
		$this->settings = is_null( $settings ) ? new Settings : $settings;
	}

	/**
	 * Check all plugins, themes and core.
	 *
	 * @return array
	 */
	public function check() {
		$this->vulnerabilities = [];

		$this->check_installed_plugins();
		$this->check_installed_themes();
		$this->check_current_wordpress_version();

		return $this->vulnerabilities;
	}

	/**
	 * Check plugins.
	 */
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

	/**
	 * Check themes.
	 */
	protected function check_installed_themes() {
		$themes = array_filter( wp_get_themes(), [ $this, 'theme_filter' ] );

		foreach ( $themes as $name => $object ) {
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

	/**
	 * Check core.
	 */
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

	/**
	 * Filter out ignored plugins from plugin array.
	 *
	 * @param  string $key Plugin file.
	 *
	 * @return bool
	 */
	protected function plugin_filter( $key ) {
		list( $slug, $basename ) = explode( DIRECTORY_SEPARATOR, $key );

		return ! in_array( $slug, $this->settings->ignored_plugins, true );
	}

	/**
	 * Filter out ignored themes from themes array.
	 *
	 * @param  WP_Theme $theme Theme object.
	 *
	 * @return bool
	 */
	protected function theme_filter( $theme ) {
		return ! in_array(
			$theme->stylesheet,
			$this->settings->ignored_themes,
			true
		);
	}
}
