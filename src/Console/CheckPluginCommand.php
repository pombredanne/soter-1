<?php

namespace SSNepenthe\Soter\Console;

use SSNepenthe\Soter\Config;
use SSNepenthe\Soter\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CheckPluginCommand extends Command {
	public function configure() {
		$this->setName( 'check:plugin' )
			->setDescription(
				'Check a single plugin for security vulnerabilities.'
			)
			->addArgument(
				'slug',
				InputArgument::REQUIRED,
				'Plugin slug.'
			)
			->addArgument(
				'version',
				InputArgument::OPTIONAL,
				'Plugin version.'
			);
	}

	public function execute( InputInterface $input, OutputInterface $output ) {
		$io = new SymfonyStyle( $input, $output );
		$slug = $input->getArgument( 'slug' );
		$version = $input->getArgument( 'version' );
		$client = Container::get( 'client' );

		if ( in_array( $slug, Config::get( 'package.ignored' ) ) ) {
			$continue = $io->choice(
				'This package has been flagged as ignored in your config.' .
				' Would you like to check it anyway?',
				[ 'no', 'yes' ]
			);

			if ( 'no' === $continue ) {
				die(1);
			}
		}

		$response = $client->check_plugin( $slug );

		if ( $response->is_error() ) {
			$io->error( sprintf( 'HTTP Error %s', $response->status_code() ) );

			die(1);
		}

		$advisories = $response->advisories_by_version( $version );

		if ( empty( $advisories ) ) {
			$io->success( 'Plugin has no known vulnerabilites.' );
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
