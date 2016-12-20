<?php
/**
 * Handles sending email notifications when enabled.
 *
 * @package soter
 */

namespace SSNepenthe\Soter\Mailers;

use SSNepenthe\Soter\Options\Settings;
use SSNepenthe\Soter\Interfaces\Mailer;

/**
 * This class formats and sends email notification when enabled by the user.
 *
 * @todo The use of esc_html() throughout this class really is not appropriate.
 *       Need to revisit escaping and sanitizing in general for email.
 */
class WP_Mail implements Mailer {
	/**
	 * Plugin settings object.
	 *
	 * @var Settings
	 */
	protected $settings;

	/**
	 * Vulnerabilities array.
	 *
	 * @var array
	 */
	protected $vulnerabilities;

	/**
	 * Constructor.
	 *
	 * @param array    $vulnerabilities Array of vulnerability objects.
	 * @param Settings $settings        Plugin settings object.
	 */
	public function __construct( array $vulnerabilities, Settings $settings ) {
		$this->settings = $settings;
		$this->vulnerabilities = $vulnerabilities;
	}

	/**
	 * Send notification if user has enabled.
	 */
	public function maybe_send() {
		if ( empty( $this->vulnerabilities ) ) {
			return;
		}

		if ( $this->settings->enable_email ) {
			$this->send();
		}
	}

	/**
	 * Send the notification.
	 */
	protected function send() {
		$email = $this->settings->email_address;

		$to = empty( $email ) ? get_bloginfo( 'admin_email' ) : $email;

		$count = count( $this->vulnerabilities );
		$count_text = 1 < $count ? 'vulnerabilities' : 'vulnerability';

		// @todo Figure out best practice when it comes to escaping for email.
		$subject = sprintf(
			'%s: %s %s detected',
			esc_html( get_bloginfo( 'name' ) ),
			esc_html( $count ),
			esc_html( $count_text )
		);

		$message = $this->prepare_message();

		wp_mail( $to, $subject, $message );
	}

	/**
	 * Prepare the notification for sending.
	 *
	 * @return string
	 */
	protected function prepare_message() {
		$message = "The following vulnerabilities were detected:\n\n";

		foreach ( $this->vulnerabilities as $vulnerability ) {
			$message .= sprintf(
				"%s\n",
				esc_html( $vulnerability->title )
			);

			if ( ! is_null( $vulnerability->published_date ) ) {
				$message .= sprintf(
					"Published %s\n",
					esc_html(
						$vulnerability->published_date->format( 'd M Y' )
					)
				);
			}

			if ( isset( $vulnerability->references->url ) ) {
				$message .= "More info:\n";

				foreach ( $vulnerability->references->url as $url ) {
					$message .= sprintf(
						"%s\n",
						esc_html( $url )
					);
				}
			}

			$message .= sprintf(
				"https://wpvulndb.com/vulnerabilities/%s\n",
				esc_html( $vulnerability->id )
			);

			if ( is_null( $vulnerability->fixed_in ) ) {
				$message .= "NOT FIXED YET\n";
			} else {
				$message .= sprintf(
					"Fixed in v%s\n",
					esc_html( $vulnerability->fixed_in )
				);
			}

			$message .= "\n";
		}

		// Strip any html tags.
		return wp_kses( $message, [] );
	}
}
