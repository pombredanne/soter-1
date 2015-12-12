<?php

namespace SSNepenthe\Soter\Console\Command;

use SSNepenthe\Soter\Checker;
use SSNepenthe\Soter\Console\Output\WPCLIOutput;
use SSNepenthe\Soter\Http\WPClient;
use WP_CLI;

/**
 * WordPress Security Checks.
 */
class WPCLICheckCommand {
	/**
	 * Checks your Composer dependencies against the wpvulndb.com API.
	 *
	 * ## OPTIONS
	 *
	 * <lock>
	 * : Path to the composer.lock file for your project.
	 *
	 * ## EXAMPLES
	 *
	 *     wp soter /vagrant/composer.lock
	 *
	 * @synopsis <lock>
	 */
	public function check( $args, $assoc_args ) {
		$lock = isset( $args[0] ) ?
			filter_var( $args[0], FILTER_SANITIZE_STRING ) :
			getcwd() . '/composer.lock';

		if ( is_dir( $lock ) ) {
			$lock = trailingslashit( $lock ) . 'composer.lock';
		}

		$lock = realpath( $lock );

		if ( ! $lock || ! is_file( $lock ) ) {
			WP_CLI::error( 'The supplied lock file does not exist.' );
		}

		if ( 'composer.lock' !== substr( $lock, -13 ) ) {
			WP_CLI::error( 'The supplied file should be a composer.lock file.' );
		}

		$checker = new Checker( $lock, new WPClient( 'https://wpvulndb.com/api/v2/' ) );
		$messages = $checker->check();

		$output = new WPCLIOutput;
		$output->display( $lock, $messages );
	}
}
