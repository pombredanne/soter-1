<?php
/**
 * Registers the check command.
 *
 * @package soter
 */

namespace SSNepenthe\Soter\Console\Command;

use RuntimeException;
use SSNepenthe\Soter\Checker;
use SSNepenthe\Soter\Console\Output\Output;
use SSNepenthe\Soter\Http\CurlClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This class registers the 'check' command with our symfony/console app.
 */
class CheckCommand extends Command {
	/**
	 * Sets up this symfony console command.
	 *
	 * @todo Add the following options (all set to VALUE_NONE):
	 *       with-ok (o), with-errors (e), with-cache (c), clear-cache (null).
	 *       Alternatively look into using the verbosity flags for deciding on what to report.
	 *
	 * @return void
	 */
	public function configure() {
		$this->setName( 'check' )
			->setDescription(
				'Check a composer.lock file against the WPVulnDB API.'
			)
			->addArgument(
				'lock',
				InputArgument::OPTIONAL,
				'Path to your composer.lock file (default is <cwd>/composer.lock).',
				getcwd() . '/composer.lock'
			);
	}

	/**
	 * Functionality of this Symfony Console command.
	 *
	 * @param InputInterface  $input Symfony/console input interface.
	 * @param OutputInterface $output Symfony/console output interface.
	 *
	 * @throws  RuntimeException If there is a problem with the passed lock file.
	 *
	 * @return void
	 */
	public function execute( InputInterface $input, OutputInterface $output ) {
		$lock = filter_var( $input->getArgument( 'lock' ), FILTER_SANITIZE_STRING );

		if ( is_dir( $lock ) ) {
			$lock = trailingslashit( $lock ) . 'composer.lock';
		}

		$lock = realpath( $lock );

		if ( ! $lock || ! is_file( $lock ) ) {
			throw new RuntimeException( 'The supplied lock file does not exist.' );
		}

		$checker = new Checker( $lock, new CurlClient( 'https://wpvulndb.com/api/v2/' ) );
		$messages = $checker->check();

		$output = new Output( $input, $output );
		$output->display( $lock, $messages );
	}
}
