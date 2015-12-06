<?php

namespace SSNepenthe\Soter\Console;

use SSNepenthe\Soter\Checker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CheckCommand extends Command {
	public function configure() {
		$this->setName( 'check' )
			->setDescription(
				'Check a composer.lock file against the WPVulnDB API.'
			)
			->addArgument(
				'lock',
				InputArgument::REQUIRED,
				'Path to your composer.lock file.'
			)
			// ->addOption(
			// 	'with-ok',
			// 	'o',
			// 	InputOption::VALUE_NONE,
			// 	'Include safe packages in the output.'
			// )
			// ->addOption(
			// 	'with-errors',
			// 	'e',
			// 	InputOption::VALUE_NONE,
			// 	'Include errors in the output.'
			// )
			;
	}

	public function execute( InputInterface $input, OutputInterface $output ) {
		$lock = $input->getArgument( 'lock' );

		// Check out the verbosity flags instead...
		// $show_errors = $input->getOption( 'with-errors' );
		// $show_ok = $input->getOption( 'with-ok' );

		$output->writeln( '<info>Checking ' . $lock . '...</info>' );

		$checker = new Checker( $lock );
		$messages = $checker->check();

		if ( ! empty( $messages ) ) {
			$table = new Table( $output );

			$table->setHeaders( [ 'Package Name', 'Status', 'Message' ] )
				->setRows( $messages )
				->render();
		}
	}
}
