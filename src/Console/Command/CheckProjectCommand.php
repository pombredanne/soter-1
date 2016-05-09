<?php

namespace SSNepenthe\Soter\Console\Command;

use SSNepenthe\ComposerUtilities\WordPress\Lock;
use SSNepenthe\Soter\Cache\FilesystemCache;
use SSNepenthe\Soter\Http\CurlClient;
use SSNepenthe\Soter\WPVulnDB\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CheckProjectCommand extends Command {
	public function configure() {
		$this->setName( 'check:project' )
			->setDescription(
				'Check a composer.lock file against the WPVulnDB API.'
			)
			->addArgument(
				'lock',
				InputArgument::OPTIONAL,
				'Path to your composer.lock file (default is {cwd}/composer.lock).',
				getcwd() . '/composer.lock'
			);
	}

	public function execute( InputInterface $input, OutputInterface $output ) {
		$path = $input->getArgument( 'lock' );

		if ( is_dir( $path ) ) {
			$path = rtrim( $path, '/\\' ) . '/composer.lock';
		}

		$path = realpath( $path );

		if ( ! $path || ! is_file( $path ) ) {
			throw new \RuntimeException(
				'The supplied lock file does not exist.'
			);
		}

		$io = new SymfonyStyle( $input, $output );
		$lock = new Lock( $path );

		try {
			$http = new CurlClient;
			$cache = new FilesystemCache;
			$client = new Client( $http, $cache );

			foreach ( $lock->core_packages() as $package ) {
				try {
					$response = $client->check_wordpress( $package->version() );
					$advisories = $response->advisories_by_version(
						$package->version()
					);

					if ( empty( $advisories ) ) {
						$io->success(
							'This version of WordPress has no known vulnerabilites.'
						);
					} else {
						$count = count( $advisories );

						$io->warning( sprintf(
							'%s known %s in this version of WordPress.',
							$count, 1 === $count ? 'vulnerability' : 'vulnerabilities'
						) );

						$io->block( $advisories );
					}
				} catch ( \Exception $e ) {
					$io->warning( $e->getMessage() );
				}
			}

			foreach ( $lock->plugin_packages() as $package ) {
				list( $vendor, $name ) = explode( '/', $package->name() );

				try {
					$response = $client->check_plugin( $name );
					$advisories = $response->advisories_by_version(
						$package->version()
					);

					if ( empty( $advisories ) ) {
						$io->success(
							sprintf( 'The %s plugin has no known vulnerabilites.', $name )
						);
					} else {
						$count = count( $advisories );

						$io->warning( sprintf(
							'%s known %s in the %s plugin.',
							$count,
							1 === $count ? 'vulnerability' : 'vulnerabilities',
							$name
						) );

						$io->block( $advisories );
					}
				} catch ( \Exception $e ) {
					$io->warning( $e->getMessage() );
				}
			}

			foreach ( $lock->mu_plugin_packages() as $package ) {
				list( $vendor, $name ) = explode( '/', $package->name() );

				try {
					$response = $client->check_plugin( $name );
					$advisories = $response->advisories_by_version(
						$package->version()
					);

					if ( empty( $advisories ) ) {
						$io->success(
							sprintf( 'The %s plugin has no known vulnerabilites.', $name )
						);
					} else {
						$count = count( $advisories );

						$io->warning( sprintf(
							'%s known %s in the %s plugin.',
							$count,
							1 === $count ? 'vulnerability' : 'vulnerabilities',
							$name
						) );

						$io->block( $advisories );
					}
				} catch ( \Exception $e ) {
					$io->warning( $e->getMessage() );
				}
			}

			foreach ( $lock->theme_packages() as $package ) {
				list( $vendor, $name ) = explode( '/', $package->name() );

				try {
					$response = $client->check_theme( $name );
					$advisories = $response->advisories_by_version(
						$package->version()
					);

					if ( empty( $advisories ) ) {
						$io->success(
							sprintf( 'The %s theme has no known vulnerabilites.', $name )
						);
					} else {
						$count = count( $advisories );

						$io->warning( sprintf(
							'%s known %s in the %s theme.',
							$count,
							1 === $count ? 'vulnerability' : 'vulnerabilities',
							$name
						) );

						$io->block( $advisories );
					}
				} catch ( \Exception $e ) {
					$io->warning( $e->getMessage() );
				}
			}
		} catch ( \Exception $e ) {
			$io->warning( $e->getMessage() );
		}
	}
}
