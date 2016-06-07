<?php

namespace SSNepenthe\Soter\Command;

use SSNepenthe\Soter\Checker;
use SSNepenthe\Soter\WPVulnDB\Client;
use SSNepenthe\Soter\Formatters\WPCLI\Text;

/**
 *
 */
class SecurityCommand {
	/**
	 * Check a plugin for vulnerabilities.
	 *
	 * ## OPTIONS
	 *
	 * <slug>
	 * : The plugin slug to check.
	 *
	 * [<version>]
	 * : The plugin version to check.
	 * ---
	 * default: null
	 * ---
	 *
	 * @subcommand check-plugin
	 */
	public function check_plugin( $args ) {
		$plugin = $args[0];
		$version = isset( $args[1] ) ? $args[1] : null;

		$client = new Client;
		$response = $client->plugins( $plugin );
		$vulnerabilities = $response->vulnerabilities_by_version( $version );

		$formatter = new Text;
		$formatter->display_results( $vulnerabilities );
	}

	/**
	 * Check a theme for vulnerabilities.
	 *
	 * ## OPTIONS
	 *
	 * <slug>
	 * : The theme slug to check.
	 *
	 * [<version>]
	 * : The theme version to check.
	 * ---
	 * default: null
	 * ---
	 *
	 * @subcommand check-theme
	 */
	public function check_theme( $args ) {
		$theme = $args[0];
		$version = isset( $args[1] ) ? $args[1] : null;

		$client = new Client;
		$response = $client->themes( $theme );
		$vulnerabilities = $response->vulnerabilities_by_version( $version );

		$formatter = new Text;
		$formatter->display_results( $vulnerabilities );
	}

	/**
	 * Check a version of WordPress for vulnerabilities.
	 *
	 * ## OPTIONS
	 *
	 * <version>
	 * : The version of WordPress to check.
	 *
	 * @alias check-wp
	 * @subcommand check-wordpress
	 */
	public function check_wordpress( $args ) {
		$version = $args[0];

		$client = new Client;
		$response = $client->wordpresses( $version );
		$vulnerabilities = $response->vulnerabilities_by_version();

		$formatter = new Text;
		$formatter->display_results( $vulnerabilities );
	}

	/**
	 * Check a full site for vulnerabilities.
	 *
	 * @subcommand check-site
	 *
	 * @todo Add progress bar.
	 */
	public function check_site() {
		$client = new Client;

		$checker = new Checker( $client );

		$vulnerabilities = $checker->check();

		$formatter = new Text;
		$formatter->display_results( $vulnerabilities );
	}

	/**
	 * @subcommand ignore-plugin
	 */
	public function ignore_plugin() {}

	/**
	 * @subcommand ignore-theme
	 */
	public function ignore_theme() {}

	/**
	 * @subcommand unignore-plugin
	 */
	public function unignore_plugin() {}

	/**
	 * @subcommand unignore-theme
	 */
	public function unignore_theme() {}
}
