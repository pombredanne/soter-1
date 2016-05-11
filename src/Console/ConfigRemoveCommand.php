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
class ConfigRemoveCommand extends Command {
	/**
	 * Set the name and description for this command.
	 */
	public function configure() {
		$this->setName( 'config:remove' )
			->setDescription(
				'Remove an entry from an addable option in your config.'
			)
			->addArgument(
				'property',
				InputArgument::REQUIRED,
				'The config property to set.'
			)
			->addArgument(
				'value',
				InputArgument::REQUIRED,
				'The value(s) to set.'
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
		$value = $input->getArgument( 'value' );
		$changed = false;

		if ( Config::is_addable( $property ) ) {
			Config::remove( $property, $value );

			$changed = true;

			$io->title( sprintf( '%s modified!', $property ) );
			$io->listing( Config::get( $property ) );
		} else {
			$io->error( sprintf(
				'%s is not an addable property.',
				$property
			) );
		}

		if ( $changed ) {
			Config::save();
		}
	}
}
