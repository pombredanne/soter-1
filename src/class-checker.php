<?php
/**
 * Checks all plugins, themes and core against WPScan vulnerabilities database API.
 *
 * @package soter
 */

namespace SSNepenthe\Soter;

use Closure;
use WP_Theme;
use SSNepenthe\Soter\WPScan\Api_Client;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Check all plugins themes and core.
 */
class Checker {
	protected $plugin_cache;
	protected $theme_cache;
	protected $wordpress_cache;

	/**
	 * WPScan API Client.
	 */
	protected $client;

	/**
	 * List of callbacks to fire after each package is checked.
	 */
	protected $post_package_check_callbacks = [];

	protected $ignored_plugins;
	protected $ignored_themes;

	/**
	 * Constructor.
	 */
	public function __construct(
		array $ignored_plugins,
		array $ignored_themes,
		Api_Client $client
	) {
		$this->client = $client;
		$this->ignored_plugins = $ignored_plugins;
		$this->ignored_themes = $ignored_themes;
	}

	public function add_post_package_check_callback( Closure $callback ) {
		$this->post_package_check_callbacks[] = $callback;
	}

	public function check_plugins() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return $this->check_packages( $this->get_plugins() );
	}

	public function check_site() {
		return $this->check_packages( $this->get_packages() );
	}

	public function check_themes() {
		return $this->check_packages( $this->get_themes() );
	}

	public function check_wordpress() {
		return $this->check_packages( $this->get_wordpress() );
	}

	public function get_client() {
		return $this->client;
	}

	public function get_package_count() {
		return count( $this->get_packages() );
	}

	public function get_packages() {
		return array_merge(
			$this->get_plugins(),
			$this->get_themes(),
			$this->get_wordpress()
		);
	}

	public function get_plugin_count() {
		return count( $this->get_plugins() );
	}

	public function get_plugins() {
		if ( is_null( $this->plugin_cache ) ) {
			$plugins = get_plugins();

			$this->plugin_cache = array_values( array_filter( array_map(
				function( $file, $plugin ) {
					list( $slug, $basename ) = explode( DIRECTORY_SEPARATOR, $file );

					return new Package( $slug, 'plugins', $plugin['Version'] );
				},
				array_keys( $plugins ),
				$plugins
			), function( Package $plugin ) {
				return ! in_array(
					$plugin->get_slug(),
					$this->ignored_plugins,
					true
				);
			} ) );
		}

		return $this->plugin_cache;
	}

	public function get_theme_count() {
		return count( $this->get_themes() );
	}

	public function get_themes() {
		if ( is_null( $this->theme_cache ) ) {
			$this->theme_cache = array_values( array_filter( array_map(
				function( WP_Theme $theme ) {
					return new Package(
						$theme->stylesheet,
						'themes',
						$theme->get( 'Version' )
					);
				},
				wp_get_themes()
			),
			function( Package $theme ) {
				return ! in_array(
					$theme->get_slug(),
					$this->ignored_themes,
					true
				);
			} ) );
		}

		return $this->theme_cache;
	}

	public function get_wordpress_count() {
		return count( $this->get_wordpress() );
	}

	public function get_wordpress() {
		if ( is_null( $this->wordpress_cache ) ) {
			$version = get_bloginfo( 'version' );
			$slug = str_replace( '.', '', $version );

			$this->wordpress_cache = [
				new Package( $slug, 'wordpresses', $version ),
			];
		}

		return $this->wordpress_cache;
	}

	protected function check_package( Package $package ) {
		$client_method = $package->get_type();

		$response = $this->client->{$client_method}( $package->get_slug() );

		if ( is_wp_error( $response ) ) {
			return [];
		}

		$vulnerabilities = $response->vulnerabilities_by_version(
			$package->get_version()
		);

		do_action( 'soter_check_package_complete', $package, $vulnerabilities );

		return $vulnerabilities;
	}

	protected function check_packages( array $packages ) {
		$vulnerabilities = [];

		foreach ( $packages as $package ) {
			$vulnerabilities = array_merge(
				$vulnerabilities,
				$this->check_package( $package )
			);

			$this->do_post_package_check_callbacks();
		}

		$vulnerabilities = array_unique( $vulnerabilities );

		do_action( 'soter_check_packages_complete', $vulnerabilities );

		return $vulnerabilities;
	}

	protected function do_post_package_check_callbacks() {
		foreach ( $this->post_package_check_callbacks as $callback ) {
			$callback();
		}
	}
}
