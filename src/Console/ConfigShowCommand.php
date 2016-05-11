<?php
/**
 * Config:remove command.
 *
 * @package soter
 */

namespace SSNepenthe\Soter\Console;

use SSNepenthe\Soter\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * This class defines the check:theme command.
 */
class ConfigShowCommand extends Command {
	/**
	 * Set the name and description for this command.
	 */
	public function configure() {
		$this->setName( 'config:show' )
			->setDescription(
				'Show your current config.'
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
