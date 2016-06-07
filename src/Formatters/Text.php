<?php

namespace SSNepenthe\Soter\Formatters;

use WP_CLI;
use SSNepenthe\Soter\Interfaces\Formatter;

class Text implements Formatter {
	public function display_results( array $vulnerabilities ) {
		if ( empty( $vulnerabilities ) ) {
			WP_CLI::log( $this->success( $this->banner(
				'No vulnerabilities detected!',
				'SUCCESS'
			) ) );

			return;
		}

		$count = count( $vulnerabilities );

		WP_CLI::log( $this->warning( $this->banner( sprintf(
			"%s %s detected",
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

	protected function banner( $text, $prefix = null ) {
		if ( ! is_null( $prefix ) ) {
			$text = sprintf( '%s: %s', $prefix, $text );
		}

		return sprintf( "\n\n  %s\n", $text );
	}

	protected function success( $text ) {
		return WP_CLI::colorize(
			'%2' . $text . '%n' . "\n"
		);
	}

	protected function title( $text ) {
		return WP_CLI::colorize( '%B' . $text . '%n' );
	}

	protected function warning( $text ) {
		return WP_CLI::colorize(
			'%1' . $text . '%n' . "\n"
		);
	}
}
