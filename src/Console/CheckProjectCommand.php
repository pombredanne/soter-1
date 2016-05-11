<?php

namespace SSNepenthe\Soter\Console;

use SSNepenthe\ComposerUtilities\WordPress\Lock;
use SSNepenthe\Soter\Config;
use SSNepenthe\Soter\Container;
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
				'Path to your composer.lock file.',
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
		$client = Container::get( 'client' );
		$vulnerabilities = [];
		$packages = $lock->wordpress_packages();

		$io->progressStart( count( $packages ) );

		foreach ( $packages as $package ) {
			list( $vendor, $name ) = explode( '/', $package->name() );

			if ( in_array( $name, Config::get( 'package.ignored' ) ) ) {
				$io->progressAdvance();
				continue;
			}

			$type = str_replace( 'wordpress-', '', $package->type() );

			if ( 'muplugin' === $type ) {
				$type = 'plugin';
			}

			if ( 'core' === $type ) {
				$type = 'wordpress';
			}

			$method = sprintf( 'check_%s', $type );

			$response = $client->$method( $name );
			if ( $response->is_error() ) {
				$io->progressAdvance();
				continue;
			}

			if ( ! empty(
				$response->vulnerabilities_by_version( $package->version() )
			) ) {
				$vulnerabilities[ $name ] = $response->advisories_by_version(
					$package->version()
				);
			}
			$io->progressAdvance();
		}

		$io->progressFinish();

		if ( $count = count( $vulnerabilities ) ) {
			$io->warning( sprintf(
				'%s %s found.',
				$count, 1 === $count ? 'vulnerability' : 'vulnerabilities'
			) );

			foreach ( $vulnerabilities as $name => $vulns ) {
				$io->title( $name );
				$io->listing( $vulns );
			}
		} else {
			$io->success( 'No vulnerabilities found!' );
		}
	}
}
