<?php

namespace SSNepenthe\Soter\Console\Output;

use WP_CLI;
use WP_CLI\Utils;

class WPCLIOutput {
	public function display( $lock, $messages ) {
		WP_CLI::line( sprintf( 'Checked file: %s', $lock ) );
		WP_CLI::line();

		if ( ! empty( $messages['vulnerable'] ) ) {
			WP_CLI::warning( sprintf( // warning
				'%s %s known vulnerabilites.',
				count( $messages['vulnerable'] ),
				1 < count( $messages['vulnerable'] ) ? 'packages have' : 'package has'
			) );

			foreach ( $messages['vulnerable'] as $package => $details ) {
				WP_CLI::log( sprintf( '%s (%s)', $package, $details['version'] ) ); // section

				foreach ( $details['advisories'] as $advisory ) {
					WP_CLI::line( $advisory ); // listing
				}

				WP_CLI::line();
			}
		}

		if ( ! empty( $messages['unknown'] ) ) {
			WP_CLI::warning( sprintf(
				'%s %s may be vulnerable but must be verified manually.',
				count( $messages['unknown'] ),
				1 < count( $messages['unknown'] ) ? 'packages' : 'package'
			) ); // caution

			foreach ( $messages['unknown'] as $package => $details ) {
				WP_CLI::log( sprintf( '%s (%s)', $package, $details['version'] ) ); // section

				foreach ( $details['advisories'] as $advisory ) {
					WP_CLI::line( $advisory ); // listing
				}

				WP_CLI::line();
			}
		}

		// Check if is debug first?

		if ( ! empty( $messages['ok'] ) ) {
			WP_CLI::success( sprintf(
				'%s %s no known vulnerabilities.',
				count( $messages['ok'] ),
				1 < count( $messages['ok'] ) ? 'packages have' : 'package has'
			) );

			foreach ( $messages['ok'] as $package => $details ) {
				WP_CLI::log( sprintf( '%s (%s)', $package, $details['version'] ) ); // section

				foreach ( $details['advisories'] as $advisory ) {
					WP_CLI::line( $advisory ); // listing
				}

				WP_CLI::line();
			}
		}

		if ( ! empty( $messages['error'] ) ) {
			WP_CLI::warning( sprintf(
				'An error was encountered checking %s %s.',
				count( $messages['error'] ),
				1 < count( $messages['error'] ) ? 'packages' : 'package'
			) ); // WP_CLI::error exits...

			foreach ( $messages['error'] as $package => $details ) {
				WP_CLI::log( sprintf( '%s (%s)', $package, $details['version'] ) ); // section

				foreach ( $details['advisories'] as $advisory ) {
					WP_CLI::line( $advisory ); // listing
				}

				WP_CLI::line();
			}
		}
	}
}
