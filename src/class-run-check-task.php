<?php

namespace SSNepenthe\Soter;

use SSNepenthe\Soter\Checker;
use SSNepenthe\Soter\Mailers\WP_Mail;
use SSNepenthe\Soter\Options\Results;
use SSNepenthe\Soter\Options\Settings;

class Run_Check_Task {
	const HOOK = 'SSNepenthe\\Soter\\run_check';

	protected $checker;
	protected $results;
	protected $settings;

	public function __construct(
		Checker $checker,
		List_Option $results,
		Map_Option $settings
	) {
		$this->checker = $checker;
		$this->results = $results;
		$this->settings = $settings;
	}

	public function init() {
		add_action( self::HOOK, [ $this, 'run_task' ] );
	}

	public function run_task() {
		if ( ! defined( 'DOING_CRON' ) || ! DOING_CRON ) {
			return;
		}

		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$vulnerabilities = $this->checker->check();

		$this->set_results_from_vulnerabilities( $vulnerabilities );

		$mailer = new WP_Mail(
			$vulnerabilities,
			$this->settings
		);
		$mailer->maybe_send();
	}

	protected function set_results_from_vulnerabilities( array $vulnerabilities ) {
		if ( empty( $vulnerabilities ) ) {
			return;
		}

		foreach ( $vulnerabilities as $vulnerability ) {
			$message = [
				'title' => $vulnerability->title,
				'meta' => [],
				'links' => [],
			];

			if ( ! is_null( $vulnerability->published_date ) ) {
				$message['meta'][] = sprintf(
					'Published %s',
					$vulnerability->published_date->format( 'd M Y' )
				);
			}

			if ( isset( $vulnerability->references->url ) ) {
				foreach ( $vulnerability->references->url as $url ) {
					$parsed = wp_parse_url( $url );

					$host = isset( $parsed['host'] ) ?
						$parsed['host'] :
						$url;

					$message['links'][ $url ] = $host;
				}
			}

			$message['links'][ sprintf(
				'https://wpvulndb.com/vulnerabilities/%s',
				$vulnerability->id
			) ] = 'wpvulndb.com';

			if ( is_null( $vulnerability->fixed_in ) ) {
				$message['meta'][] = 'Not fixed yet';
			} else {
				$message['meta'][] = sprintf(
					'Fixed in v%s',
					$vulnerability->fixed_in
				);
			}

			$this->results->add( $message );
		}

		$this->results->save();
	}
}
