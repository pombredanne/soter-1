<?php

namespace SSNepenthe\Soter\Console;

use SSNepenthe\Soter\Container;
use SSNepenthe\Soter\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CacheClearCommand extends Command {
	public function configure() {
		$this->setName( 'cache:clear' )
			->setDescription(
				'Clear the cache.'
			);
	}

	public function execute( InputInterface $input, OutputInterface $output ) {
		$io = new SymfonyStyle( $input, $output );

		$cache = Container::get( 'cache' )->flushAll();

		$io->success('Cache cleared successfully!');
	}
}
