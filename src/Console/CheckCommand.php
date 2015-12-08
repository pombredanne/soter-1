<?php
/**
 * Registers the check command.
 *
 * @package soter
 */

namespace SSNepenthe\Soter\Console;

use SSNepenthe\Soter\Checker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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
				'Path to your composer.lock file.',
				getcwd() . '/composer.lock'
			);
	}

	/**
	 * Functionality of this Symfony Console command.
	 *
	 * @param InputInterface  $input Symfony/console input interface.
	 * @param OutputInterface $output Symfony/console output interface.
	 *
	 * @return void
	 */
	public function execute( InputInterface $input, OutputInterface $output ) {
		$lock = $input->getArgument( 'lock' );

		$io = new SymfonyStyle( $input, $output );

		$io->text( sprintf( 'Checking %s...', $lock ) );
		$io->newLine();

		$checker = new Checker( $lock );
		$messages = $checker->check();

		if ( ! empty( $messages ) ) {
			$io->table( [ 'Package Name', 'Status', 'Message' ], $messages );
		}
	}
}
