<?php
/**
 * Check:theme command.
 *
 * @package soter
 */

namespace SSNepenthe\Soter\Console;

use SSNepenthe\Soter\Config;
use SSNepenthe\Soter\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * This class defines the check:theme command.
 */
class CheckThemeCommand extends Command {
	/**
	 * Set the name and description for this command.
	 */
	public function configure() {
		$this->setName( 'check:theme' )
			->setDescription(
				'Check a single theme for security vulnerabilities.'
			)
			->addArgument(
				'slug',
				InputArgument::REQUIRED,
				'Theme slug.'
			)
			->addArgument(
				'version',
				InputArgument::OPTIONAL,
				'Theme version.'
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
		$slug = $input->getArgument( 'slug' );
		$version = $input->getArgument( 'version' );
		$client = Container::get( 'client' );

		if ( in_array( $slug, Config::get( 'package.ignored' ), true ) ) {
			$continue = $io->choice(
				'This package has been flagged to be ignored in your config. Would you like to check it anyway?',
				[ 'no', 'yes' ]
			);

			if ( 'no' === $continue ) {
				die( 1 );
			}
		}

		$response = $client->check_theme( $slug );

		if ( $response->is_error() ) {
			$io->error( sprintf( 'HTTP Error %s' ), $response->status_code() );

			die( 1 );
		}

		$advisories = $response->advisories_by_version( $version );

		if ( empty( $advisories ) ) {
			$io->success( 'Theme has no known vulnerabilites.' );
		} else {
			$count = count( $advisories );

			$io->warning( sprintf(
				'%s known %s.',
				$count, 1 === $count ? 'vulnerability' : 'vulnerabilities'
			) );

			$io->block( $advisories );
		}
	}
}
