<?php

namespace SSNepenthe\Soter\Console\Output;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Output {
	/**
	 * SymfonyStyle instance.
	 *
	 * @var Symfony\Component\Console\Style\SymfonyStyle
	 */
	protected $io;

	/**
	 * Symfony OutputInterface instance.
	 *
	 * @var Symfony\Component\Console\Output\OutputInterface
	 */
	protected $output;

	/**
	 * Set up our object.
	 *
	 * @param InputInterface  $input  Symfony InputInterface.
	 * @param OutputInterface $output Symfony OutputInterface.
	 */
	public function __construct( InputInterface $input, OutputInterface $output ) {
		$this->io = new SymfonyStyle( $input, $output );
		$this->output = $output;
	}

	/**
	 * Display the results of the check command.
	 *
	 * @param  string $lock     Path to lock file.
	 * @param  array  $messages Array of messages to be printed.
	 */
	public function display( $lock, $messages ) {
		$this->io->text( sprintf( 'Checked file: %s', $lock ) ); // Some color?
		$this->io->newLine();

		if ( ! empty( $messages['vulnerable'] ) ) {
			$this->io->warning( sprintf(
				'%s %s known vulnerabilites.',
				count( $messages['vulnerable'] ),
				1 < count( $messages['vulnerable'] ) ? 'packages have' : 'package has'
			) );

			foreach ( $messages['vulnerable'] as $package => $details ) {
				$this->io->section( sprintf( '%s (%s)', $package, $details['version'] ) );
				$this->io->listing( $details['advisories'] );
			}
		}

		if ( ! empty( $messages['unknown'] ) ) {
			$this->io->caution( sprintf(
				'%s %s may be vulnerable but must be verified manually.',
				count( $messages['unknown'] ),
				1 < count( $messages['unknown'] ) ? 'packages' : 'package'
			) );

			foreach ( $messages['unknown'] as $package => $details ) {
				$this->io->section( sprintf( '%s (%s)', $package, $details['version'] ) );
				$this->io->listing( $details['advisories'] );
			}
		}

		if (
			empty( $messages['unknown'] ) &&
			empty( $messages['vulnerable'] ) &&
			! $this->output->isVerbose()
		) {
			$this->io->success( 'No packages have known vulnerabilities!' );
		}

		if ( $this->output->isVerbose() ) {
			if ( ! empty( $messages['ok'] ) ) {
				$this->io->success( sprintf(
					'%s %s no known vulnerabilities.',
					count( $messages['ok'] ),
					1 < count( $messages['ok'] ) ? 'packages have' : 'package has'
				) );

				foreach ( $messages['ok'] as $package => $details ) {
					$this->io->section( sprintf( '%s (%s)', $package, $details['version'] ) );
					$this->io->listing( $details['advisories'] );
				}
			}
		}

		if ( ! empty( $messages['error'] ) ) {
			$this->io->error( sprintf(
				'An error was encountered checking %s %s.',
				count( $messages['error'] ),
				1 < count( $messages['error'] ) ? 'packages' : 'package'
			) );

			foreach ( $messages['error'] as $package => $details ) {
				$this->io->section( sprintf( '%s (%s)', $package, $details['version'] ) );
				$this->io->listing( $details['advisories'] );
			}
		}
	}
}
