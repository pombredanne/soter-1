<?php

namespace SSNepenthe\Soter\Console;

use SSNepenthe\Soter\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ConfigRemoveCommand extends Command {
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
