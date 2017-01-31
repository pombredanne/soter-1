<?php
/**
 * Sends email notifications when appropriate.
 *
 * @package soter
 */

namespace SSNepenthe\Soter\Listeners;

use SSNepenthe\Soter\Views\Template;
use SSNepenthe\Soter\WPScan\Vulnerability;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * This class creates and sends email notifications after a site scan if the site is
 * found to be vulnerable and the admin has enabled email notiications.
 */
class Send_Vulnerable_Packages_Email {
	/**
	 * To email address.
	 *
	 * @var string
	 */
	protected $email_address;

	/**
	 * Whether or not notifications are enabled.
	 *
	 * @var bool
	 */
	protected $enable_email;

	/**
	 * Whether or not the email type is set to html.
	 *
	 * @var bool
	 */
	protected $html_email;

	/**
	 * Template instance.
	 *
	 * @var Template
	 */
	protected $template;

	/**
	 * Class constructor.
	 *
	 * @param Template $template      Template instance.
	 * @param boolean  $enable_email  Whether or not notifications are enabled.
	 * @param boolean  $html_email    Whether or not to send html-type emails.
	 * @param string   $email_address The to email address.
	 */
	public function __construct(
		Template $template,
		$enable_email = false,
		$html_email = false,
		$email_address = ''
	) {
		$this->template = $template;
		$this->enable_email = (bool) $enable_email;
		$this->html_email = (bool) $html_email;
		$this->email_address = empty( $email_address )
			? get_bloginfo( 'admin_email' )
			: (string) $email_address;
	}

	/**
	 * Hooks the notification functionality in to WordPress.
	 */
	public function init() {
		add_action( 'soter_check_packages_complete', [ $this, 'send_email' ] );
	}

	/**
	 * Sends the actual email when appropriate.
	 *
	 * @param  Vulnerability[] $vulnerabilities List of vulnerabilities detected.
	 */
	public function send_email( array $vulnerabilities ) {
		if ( empty( $vulnerabilities ) ) {
			return;
		}

		if ( ! $this->enable_email ) {
			return;
		}

		$count = count( $vulnerabilities );
		$site_name = get_bloginfo( 'name' );
		$subject = sprintf(
			'[%s] %s %s detected',
			$site_name,
			$count, 1 < $count ? 'vulnerabilities' : 'vulnerability'
		);
		$headers = [];
		$template = 'emails/text-vulnerable';
		$action_url = admin_url( 'update-core.php' );

		if ( $this->html_email ) {
			$headers[] = 'Content-type: text/html';
			$template = 'emails/html-vulnerable';
		}

		$messages = [];

		foreach ( $vulnerabilities as $vulnerability ) {
			$messages[] = $this->generate_vuln_summary( $vulnerability );
		}

		wp_mail(
			$this->email_address,
			$subject,
			$this->template->render(
				$template,
				compact( 'action_url', 'count', 'messages', 'site_name' )
			),
			$headers
		);
	}

	/**
	 * Generates a summary of a vulnerability for use in the template data.
	 *
	 * @param  Vulnerability $vulnerability A vulnerability thats needs summarizing.
	 *
	 * @return array
	 */
	protected function generate_vuln_summary( Vulnerability $vulnerability ) {
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
