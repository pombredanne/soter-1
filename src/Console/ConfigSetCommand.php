<?php

namespace SSNepenthe\Soter\Console;

use SSNepenthe\Soter\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ConfigSetCommand extends Command {
	public function configure() {
		$this->setName( 'config:set' )
			->setDescription(
				'Set an option in your config.'
			)
			->addArgument(
				'property',
				InputArgument::REQUIRED,
				'The config property to set.'
			)
			->addArgument(
				'value',
				InputArgument::IS_ARRAY | InputArgument::REQUIRED,
				'The value(s) to set.'
			)
			->addOption(
				'overwrite',
				'o',
				InputOption::VALUE_NONE,
				'Overwrite the config value instead of appending to it.'
			);
	}

	public function execute( InputInterface $input, OutputInterface $output ) {
		$io = new SymfonyStyle( $input, $output );

		$property = $input->getArgument( 'property' );
		$value = $input->getArgument( 'value' );
		$changed = false;

		if ( Config::is_addable( $property ) ) {
			if ( $input->getOption( 'overwrite' ) ) {
				Config::reset( $property );
			}

			Config::add_many( $property, $value );

			$changed = true;

			$io->title( sprintf( '%s set!', $property ) );
			$io->listing( Config::get( $property ) );
		}

		if ( Config::is_settable( $property ) ) {
			Config::set( $property, $value[0] );

			$changed = true;

			$io->title( sprintf( '%s set!', $property ) );
			$io->block( Config::get( $property ) );
		}

		if ( $changed ) {
			Config::save();
		}
	}
}
