<?php

namespace SSNepenthe\Soter\Console;

use SSNepenthe\Soter\Config;
use SSNepenthe\Soter\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CheckWordPressCommand extends Command {
	public function configure() {
		$this->setName( 'check:wordpress' )
			->setDescription(
				'Check a single version of WordPress core for security vulnerabilities.'
			)
			->addArgument(
				'version',
				InputArgument::REQUIRED,
				'WordPress version.'
			);
	}

	public function execute( InputInterface $input, OutputInterface $output ) {
		$io = new SymfonyStyle( $input, $output );
		$version = $input->getArgument( 'version' );
		$client = Container::get( 'client' );

		if ( in_array( $version, Config::get( 'package.ignored' ) ) ) {
			$continue = $io->choice(
				'This package has been flagged to be ignored in your config.' .
				' Would you like to check it anyway?',
				[ 'no', 'yes' ]
			);

			if ( 'no' === $continue ) {
				die(1);
			}
		}

		$response = $client->check_wordpress( $version );

		if ( $response->is_error() ) {
			$io->error( sprintf( 'HTTP Error %s' ), $response->status_code() );

			die(1);
		}

		$advisories = $response->advisories_by_version( $version );

		if ( empty( $advisories ) ) {
			$io->success(
				'This version of WordPress has no known vulnerabilites.'
			);
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
