<?php
/**
 * Adds the security command to WP-CLI.
 *
 * @package soter
 */

namespace SSNepenthe\Soter;

use WP_CLI;

/**
 * Check core, plugins and themes for security vulnerabilities against the WPScan
 * Vulnerability Database API.
 */
class Security_Command {
	protected $checker;

	public function __construct( Checker $checker ) {
		$this->checker = $checker;
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
	 * [--format=<format>]
	 * : Results output format.
	 * ---
	 * default: standard
	 * options:
	 *   - count
	 *   - csv
	 *   - ids
	 *   - json
	 *   - standard
	 *   - table
	 *   - yaml
	 * ---
	 *
	 * [--fields=<fields>]
	 * : Comma separated list of fields to show. Valid fields include id, title, created_at, updated_at, published_date, vuln_type, fixed_in.
	 *
	 * @subcommand check-plugin
	 *
	 * @param array $args Positional args.
	 */
	public function check_plugin( $args, $assoc_args ) {
		$plugin = $args[0];
		$version = isset( $args[1] ) ? $args[1] : null;

		$response = $this->checker->get_client()->plugins( $plugin );
		$vulnerabilities = $response->vulnerabilities_by_version( $version );

		$this->display_results( $vulnerabilities, $assoc_args );
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
	 * [--format=<format>]
	 * : Results output format.
	 * ---
	 * default: standard
	 * options:
	 *   - count
	 *   - csv
	 *   - ids
	 *   - json
	 *   - standard
	 *   - table
	 *   - yaml
	 * ---
	 *
	 * [--fields=<fields>]
	 * : Comma separated list of fields to show. Valid fields include id, title, created_at, updated_at, published_date, vuln_type, fixed_in.
	 *
	 * @subcommand check-theme
	 *
	 * @param array $args Positional args.
	 */
	public function check_theme( $args, $assoc_args ) {
		$theme = $args[0];
		$version = isset( $args[1] ) ? $args[1] : null;

		$response = $this->checker->get_client()->themes( $theme );
		$vulnerabilities = $response->vulnerabilities_by_version( $version );

		$this->display_results( $vulnerabilities, $assoc_args );
	}

	/**
	 * Check a version of WordPress for vulnerabilities.
	 *
	 * ## OPTIONS
	 *
	 * <version>
	 * : The version of WordPress to check.
	 *
	 * [--format=<format>]
	 * : Results output format.
	 * ---
	 * default: standard
	 * options:
	 *   - count
	 *   - csv
	 *   - ids
	 *   - json
	 *   - standard
	 *   - table
	 *   - yaml
	 * ---
	 *
	 * [--fields=<fields>]
	 * : Comma separated list of fields to show. Valid fields include id, title, created_at, updated_at, published_date, vuln_type, fixed_in.
	 *
	 * @alias check-wp
	 * @subcommand check-wordpress
	 *
	 * @param array $args Positional args.
	 */
	public function check_wordpress( $args, $assoc_args ) {
		$version = $args[0];

		$response = $this->checker->get_client()->wordpresses( $version );
		$vulnerabilities = $response->vulnerabilities_by_version();

		$this->display_results( $vulnerabilities, $assoc_args );
	}

	/**
	 * Check a full site for vulnerabilities.
	 *
	 * ## OPTIONS
	 *
	 * [--format=<format>]
	 * : Results output format.
	 * ---
	 * default: standard
	 * options:
	 *   - count
	 *   - csv
	 *   - ids
	 *   - json
	 *   - standard
	 *   - table
	 *   - yaml
	 * ---
	 *
	 * [--fields=<fields>]
	 * : Comma separated list of fields to show. Valid fields include id, title, created_at, updated_at, published_date, vuln_type, fixed_in.
	 *
	 * @subcommand check-site
	 *
	 * @todo Add progress bar.
	 */
	public function check_site( array $_, array $assoc_args ) {
		$vulnerabilities = $this->checker->check();

		$this->display_results( $vulnerabilities, $assoc_args );
	}

	/**
	 * @todo Reconsider how to handle the case where no vulnerabilities are found
	 *       using WP-CLI formatter.
	 */
	protected function display_results( array $vulnerabilities, array $assoc_args ) {
		$format = isset( $assoc_args['format'] ) ?
			$assoc_args['format'] :
			'standard';

		$fields = isset( $assoc_args['fields'] ) ?
			$assoc_args['fields'] :
			'title,published_date,fixed_in';

		if ( 'standard' === $format ) {
			// @todo Should this honor the fields param?
			$this->display_standard_results( $vulnerabilities );
		} else {
			if ( 'ids' === $format ) {
				$vulnerabilities = array_map( function( $vuln ) {
					return $vuln->id;
				}, $vulnerabilities );
			} else {
				$vulnerabilities = array_map( function( $vuln ) {
					return $vuln->to_array();
				}, $vulnerabilities );
			}

			$fields = explode( ',', $fields );

			/**
			 * @todo Should also allow 'references', but need to check out the API
			 *       more closely and decide on best way to flatten this field.
			 *       It is an object of arrays, not sure if it is guaranteed to be
			 *       set, and *at least* the following are valid keys:
			 *       url, cve, osvdb, secunia.
			 */
			$allowed = [ 'id', 'title', 'created_at', 'updated_at', 'published_date',
				'vuln_type', 'fixed_in' ];

			$invalid = array_values( array_diff( $fields, $allowed ) );

			// Mimics WP-CLI default behavior for invalid params.
			if ( ! empty( $invalid ) ) {
				$message = 'Parameter errors:' . "\n";

				if ( 1 === count( $invalid ) ) {
					$message .= $invalid[0] . ' is not a valid fields value';
				} else {
					$message .= implode( ', ', $invalid ) . ' are not valid fields values';
				}

				WP_CLI::error( $message );
			}

			WP_CLI\Utils\format_items( $format, $vulnerabilities, $fields );
		}
	}

	protected function display_standard_results( array $vulnerabilities ) {
		if ( empty( $vulnerabilities ) ) {
			WP_CLI::log( $this->success( $this->banner(
				'No vulnerabilities detected!',
				'SUCCESS'
			) ) );

			return;
		}

		$count = count( $vulnerabilities );

		WP_CLI::log( $this->warning( $this->banner( sprintf(
			'%s %s detected',
			$count,
			1 < $count ? 'vulnerabilities' : 'vulnerability'
		), 'WARNING' ) ) );

		foreach ( $vulnerabilities as $vulnerability ) {
			WP_CLI::log( $this->title( $vulnerability->title ) );

			if ( ! is_null( $vulnerability->published_date ) ) {
				WP_CLI::log( sprintf(
					'Published %s',
					$vulnerability->published_date->format( 'd F Y' )
				) );
			}

			if ( isset( $vulnerability->references->url ) ) {
				foreach ( $vulnerability->references->url as $url ) {
					WP_CLI::log( $url );
				}
			}

			WP_CLI::log( sprintf(
				'https://wpvulndb.com/vulnerabilities/%s',
				$vulnerability->id
			) );

			if ( is_null( $vulnerability->fixed_in ) ) {
				WP_CLI::log( $this->warning( 'Not fixed yet' ) );
			} else {
				WP_CLI::log( sprintf(
					'Fixed in v%s',
					$vulnerability->fixed_in
				) );
			}

			WP_CLI::log( '' );
		}
	}

	/**
	 * Creates a multi-line banner for given string.
	 *
	 * @param  string $text   String to bannerify.
	 * @param  string $prefix Text to prefix to $text.
	 *
	 * @return string
	 */
	protected function banner( $text, $prefix = null ) {
		if ( ! is_null( $prefix ) ) {
			$text = sprintf( '%s: %s', $prefix, $text );
		}

		return sprintf( "\n\n  %s\n", $text );
	}

	/**
	 * Colorize a string with a green background.
	 *
	 * @param  string $text Text to colorize.
	 *
	 * @return string
	 */
	protected function success( $text ) {
		return WP_CLI::colorize(
			'%2' . $text . '%n' . "\n"
		);
	}

	/**
	 * Colorize a string blue.
	 *
	 * @param  string $text Text to colorize.
	 *
	 * @return string
	 */
	protected function title( $text ) {
		return WP_CLI::colorize( '%B' . $text . '%n' );
	}

	/**
	 * Colorize a string with a red background.
	 *
	 * @param  string $text Text to colorize.
	 *
	 * @return string
	 */
	protected function warning( $text ) {
		return WP_CLI::colorize(
			'%1' . $text . '%n' . "\n"
		);
	}
}
