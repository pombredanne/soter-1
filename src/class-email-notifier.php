<?php
/**
 * Sends email notifications when appropriate.
 *
 * @package soter
 */

namespace Soter;

use League\Plates\Engine;
use Soter_Core\Vulnerability_Interface;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * This class creates and sends email notifications after a site scan.
 */
class Email_Notifier {
	/**
	 * Options manager instance.
	 *
	 * @var Options_Manager
	 */
	protected $options;

	/**
	 * Template engine instance.
	 *
	 * @var Engine
	 */
	protected $template;

	/**
	 * Class constructor.
	 *
	 * @param Engine          $template Template engine instance.
	 * @param Options_Manager $options  Options manager instance.
	 */
	public function __construct( Engine $template, Options_Manager $options ) {
		$this->template = $template;
		$this->options = $options;
	}

	/**
	 * Handle the notification.
	 *
	 * @param Vulnerability_Interface[] $vulnerabilities List of vulnerabilities.
	 * @param boolean                   $has_changed     Whether the status has changed since last scan.
	 */
	public function notify( $vulnerabilities, $has_changed ) {
		if (
			empty( $vulnerabilities )
			|| ( ! $has_changed && ! $this->options->should_nag() )
		) {
			return;
		}

		// If an array only has one object, do_action() passes that object by itself
		// instead of the original array. Let's put it back in an array.
		if ( $vulnerabilities instanceof Vulnerability_Interface ) {
			$vulnerabilities = [ $vulnerabilities ];
		}

		$this->send_email( $vulnerabilities );
	}

	/**
	 * Send the actual email.
	 *
	 * @param  Vulnerability_Interface[] $vulnerabilities List of vulnerabilities.
	 *
	 * @return void
	 */
	protected function send_email( array $vulnerabilities ) {
		$count = count( $vulnerabilities );
		$label = 1 === $count ? 'vulnerability' : 'vulnerabilities';
		$site_name = get_bloginfo( 'name' );
		$headers = [];
		$template = 'emails/text-vulnerable';
		$action_url = admin_url( 'update-core.php' );

		if ( 'html' === $this->options->email_type() ) {
			$headers[] = 'Content-type: text/html';
			$template = 'emails/html-vulnerable';
		}

		$messages = [];

		foreach ( $vulnerabilities as $vulnerability ) {
			$messages[] = $this->generate_vuln_summary( $vulnerability );
		}

		wp_mail(
			$this->options->email_address(),
			"[{$site_name}] {$count} {$label} detected",
			$this->template->render(
				$template,
				compact( 'action_url', 'count', 'label', 'messages', 'site_name' )
			),
			$headers
		);
	}

	/**
	 * Generates a summary of a vulnerability for use in the template data.
	 *
	 * @param  Vulnerability_Interface $vulnerability Vulnerability instance.
	 *
	 * @return array
	 */
	protected function generate_vuln_summary(
		Vulnerability_Interface $vulnerability
	) {
		$summary = [
			'title' => $vulnerability->title,
			'meta' => [],
			'links' => [],
		];

		if ( ! is_null( $vulnerability->published_date ) ) {
			$summary['meta'][] = sprintf(
				'Published %s',
				$vulnerability->published_date->format( 'd M Y' )
			);
		}

		if (
			! is_null( $vulnerability->references )
			&& isset( $vulnerability->references['url'] )
		) {
			foreach ( $vulnerability->references['url'] as $url ) {
				$parsed = wp_parse_url( $url );

				$host = isset( $parsed['host'] ) ?
					$parsed['host'] :
					$url;

				$summary['links'][ $url ] = $host;
			}
		}

		$summary['links'][ sprintf(
			'https://wpvulndb.com/vulnerabilities/%s',
			$vulnerability->id
		) ] = 'wpvulndb.com';

		if ( is_null( $vulnerability->fixed_in ) ) {
			$summary['meta'][] = 'Not fixed yet';
		} else {
			$summary['meta'][] = sprintf(
				'Fixed in v%s',
				$vulnerability->fixed_in
			);
		}

		return $summary;
	}
}
