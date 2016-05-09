<?php

namespace SSNepenthe\Soter\Console\Command;

use SSNepenthe\Soter\Cache\FilesystemCache;
use SSNepenthe\Soter\Http\CurlClient;
use SSNepenthe\Soter\WPVulnDB\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CheckWordPressCommand extends Command {
	public function configure() {
		$this->setName( 'check:wordpress' )
			->setDescription(
				'Check WordPress core against the WPVulnDB API.'
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

		try {
			$http = new CurlClient;
			$cache = new FilesystemCache;
			$client = new Client( $http, $cache );

			$response = $client->check_wordpress( $version );
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
		} catch ( \Exception $e ) {
			$io->warning( $e->getMessage() );
		}
	}
}
