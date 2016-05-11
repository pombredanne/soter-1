<?php

namespace SSNepenthe\Soter\Console;

use SSNepenthe\Soter\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ConfigShowCommand extends Command {
	public function configure() {
		$this->setName( 'config:show' )
			->setDescription(
				'Show your current config.'
			);
	}

	public function execute( InputInterface $input, OutputInterface $output ) {
		$io = new SymfonyStyle( $input, $output );

		$io->title( 'Cache Directory (cache.directory)' );
		$io->block( Config::get( 'cache.directory' ) );

		$io->title( 'Cache TTL (cache.ttl)' );
		$io->block( Config::get( 'cache.ttl' ) );

		$io->title( 'User Agent (http.useragent)' );
		$io->block( Config::get( 'http.useragent' ) );

		$io->title( 'Ignored Packages (package.ignored)' );
		$io->listing( Config::get( 'package.ignored' ) );
	}
}
