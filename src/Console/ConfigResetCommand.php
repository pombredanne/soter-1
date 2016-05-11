<?php
/**
 * Config:remove command.
 *
 * @package soter
 */

namespace SSNepenthe\Soter\Console;

use SSNepenthe\Soter\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * This class defines the check:theme command.
 */
class ConfigResetCommand extends Command {
	/**
	 * Set the name and description for this command.
	 */
	public function configure() {
		$this->setName( 'config:reset' )
			->setDescription(
				'Reset a previously set entry to default in your config.'
			)
			->addArgument(
				'property',
				InputArgument::REQUIRED,
				'The config property to set.'
			);
	}

	/**
	 * The command functionality.
	 *
	 * @param  InputInterface  $input  Symfony console input Interface.
	 * @param  OutputInterface $output Symfony console output Interface.
	 */
	public function execute( InputInterface $input, OutputInterface $output ) {
		$io = new SymfonyStyle( $input, $output );

		$property = $input->getArgument( 'property' );

		Config::reset( $property );
		Config::save();

		$io->title( sprintf( '%s has been reset!', $property ) );
	}
}
