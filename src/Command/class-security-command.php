<?php
/**
 * Adds the security command to WP-CLI.
 *
 * @package soter
 */

namespace SSNepenthe\Soter\Command;

use SSNepenthe\Soter\Checker;
use SSNepenthe\Soter\Interfaces\Formatter;

/**
 * Check core, plugins and themes for security vulnerabilities against the WPVulnDB API.
 */
class Security_Command {
	protected $checker;
	protected $formatter;

	public function __construct( Checker $checker, Formatter $formatter ) {
		$this->checker = $checker;
		$this->formatter = $formatter;
	}

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
	 *
	 * @param array $args Positional args.
	 */
	public function check_plugin( $args ) {
		$plugin = $args[0];
		$version = isset( $args[1] ) ? $args[1] : null;

		$response = $this->checker->get_client()->plugins( $plugin );
		$vulnerabilities = $response->vulnerabilities_by_version( $version );

		$this->formatter->display_results( $vulnerabilities );
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
	 *
	 * @param array $args Positional args.
	 */
	public function check_theme( $args ) {
		$theme = $args[0];
		$version = isset( $args[1] ) ? $args[1] : null;

		$response = $this->checker->get_client()->themes( $theme );
		$vulnerabilities = $response->vulnerabilities_by_version( $version );

		$this->formatter->display_results( $vulnerabilities );
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
	 *
	 * @param array $args Positional args.
	 */
	public function check_wordpress( $args ) {
		$version = $args[0];

		$response = $this->checker->get_client()->wordpresses( $version );
		$vulnerabilities = $response->vulnerabilities_by_version();

		$this->formatter->display_results( $vulnerabilities );
	}

	/**
	 * Check a full site for vulnerabilities.
	 *
	 * @subcommand check-site
	 *
	 * @todo Add progress bar.
	 */
	public function check_site() {
		$vulnerabilities = $this->checker->check();

		$this->formatter->display_results( $vulnerabilities );
	}
}
