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

class CheckPluginCommand extends Command {
	public function configure() {
		$this->setName( 'check:plugin' )
			->setDescription(
				'Check a single plugin against the WPVulnDB API.'
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

		try {
			$http = new CurlClient;
			$cache = new FilesystemCache;
			$client = new Client( $http, $cache );

			$response = $client->check_plugin( $slug );
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
		} catch ( \Exception $e ) {
			$io->warning( $e->getMessage() );
		}
	}
}
