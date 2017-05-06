<?php
/**
 * Adds the security command to WP-CLI.
 *
 * @package soter
 */

namespace SSNepenthe\Soter\Commands;

use WP_CLI;
use SSNepenthe\Soter\Checker;
use function WP_CLI\Utils\format_items;
use function WP_CLI\Utils\make_progress_bar;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Check core, plugins and themes for security vulnerabilities against the WPScan
 * Vulnerability Database API.
 */
class Security_Command {
	/**
	 * Checker instance.
	 *
	 * @var Checker
	 */
	protected $checker;

	/**
	 * Class constructor.
	 *
	 * @param Checker $checker Checker instance.
	 */
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
	 *   - yml
	 * ---
	 *
	 * [--fields=<fields>]
	 * : Comma separated list of fields to show. Valid fields include id, title, created_at, updated_at, published_date, vuln_type, fixed_in.
	 *
	 * @subcommand check-plugin
	 *
	 * @param  array $args       Positional args.
	 * @param  array $assoc_args Associative args.
	 */
	public function check_plugin( $args, $assoc_args ) {
		$plugin = $args[0];
		$version = isset( $args[1] ) ? $args[1] : null;

		$response = $this->checker->get_client()->plugins( $plugin );

		if ( is_wp_error( $response ) ) {
			WP_CLI::error( $response->get_error_message() );
		}

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
	 *   - yml
	 * ---
	 *
	 * [--fields=<fields>]
	 * : Comma separated list of fields to show. Valid fields include id, title, created_at, updated_at, published_date, vuln_type, fixed_in.
	 *
	 * @subcommand check-theme
	 *
	 * @param  array $args       Positional args.
	 * @param  array $assoc_args Associative args.
	 */
	public function check_theme( $args, $assoc_args ) {
		$theme = $args[0];
		$version = isset( $args[1] ) ? $args[1] : null;

		$response = $this->checker->get_client()->themes( $theme );

		if ( is_wp_error( $response ) ) {
			WP_CLI::error( $response->get_error_message() );
		}

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
	 *   - yml
	 * ---
	 *
	 * [--fields=<fields>]
	 * : Comma separated list of fields to show. Valid fields include id, title, created_at, updated_at, published_date, vuln_type, fixed_in.
	 *
	 * @alias check-wp
	 * @subcommand check-wordpress
	 *
	 * @param  array $args       Positional args.
	 * @param  array $assoc_args Associative args.
	 */
	public function check_wordpress( $args, $assoc_args ) {
		$version = str_replace( '.', '', $args[0] );

		$response = $this->checker->get_client()->wordpresses( $version );

		if ( is_wp_error( $response ) ) {
			WP_CLI::error( $response->get_error_message() );
		}

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
	 *   - yml
	 * ---
	 *
	 * [--fields=<fields>]
	 * : Comma separated list of fields to show. Valid fields include id, title, created_at, updated_at, published_date, vuln_type, fixed_in.
	 *
	 * @subcommand check-site
	 *
	 * @param  array $_          Unused positional args.
	 * @param  array $assoc_args Associative args.
	 */
	public function check_site( array $_, array $assoc_args ) {
		$package_count = $this->checker->get_package_count();

		$progress = make_progress_bar(
			sprintf( 'Checking %s packages', $package_count ),
			$package_count
		);

		$ticker = function() use ( $progress ) {
			$progress->tick();
		};

		add_action( 'soter_check_package_complete', $ticker );

		$vulnerabilities = $this->checker->check_site();

		remove_action( 'soter_check_package_complete', $ticker );

		$progress->finish();

		$this->display_results( $vulnerabilities, $assoc_args );
	}

	/**
	 * Display the results of an individual check.
	 *
	 * @param  SSNepenthe\Soter\WPScan\Vulnerability[] $vulnerabilities List of vulnerabilities.
	 * @param  array                                   $assoc_args      Associative args.
	 */
	protected function display_results( array $vulnerabilities, array $assoc_args ) {
		$format = isset( $assoc_args['format'] ) ?
			$assoc_args['format'] :
			'standard';

		if ( 'yml' === $format ) {
			$format = 'yaml';
		}

		$fields = isset( $assoc_args['fields'] ) ?
			$assoc_args['fields'] :
			'title,published_date,fixed_in';

		if ( 'standard' === $format ) {
			$this->display_standard_results( $vulnerabilities );
		} else {
			if ( 'ids' === $format ) {
				$vulnerabilities = array_map( function( $vuln ) {
					return $vuln->id;
				}, $vulnerabilities );
			} else {
				$vulnerabilities = array_map( function( $vuln ) {
					return $vuln->get_data();
				}, $vulnerabilities );
			}

			$fields = explode( ',', $fields );

			$allowed = [
				'id',
				'title',
				'created_at',
				'updated_at',
				'published_date',
				'vuln_type',
				'fixed_in',
			];

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

			format_items( $format, $vulnerabilities, $fields );
		}
	}

	/**
	 * Display the results when --format=standard.
	 *
	 * @param  SSNepenthe\Soter\WPScan\Vulnerability[] $vulnerabilities List of vulnerabilities.
	 */
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
