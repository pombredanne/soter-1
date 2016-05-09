<?php

namespace SSNepenthe\Soter\Console\Command;

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

		$cache = new \SSNepenthe\Soter\Cache\FilesystemCache;
		$cache->flush();

		$io->success('Cache cleared successfully!');
	}
}
