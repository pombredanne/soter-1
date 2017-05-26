<?php
/**
 * Slack_Notifier class.
 *
 * @package soter
 */

namespace Soter;

use Soter_Core\Vulnerability_Interface;

/**
 * Defines the Slack notifier class.
 */
class Slack_Notifier {
	/**
	 * Options manager instance.
	 *
	 * @var Options_Manager
	 */
	protected $options;

	/**
	 * HTTP user agent string.
	 *
	 * @var string
	 */
	protected $user_agent;

	/**
	 * Class constructor.
	 *
	 * @param Options_Manager $options    Options manager instance.
	 * @param string          $user_agent HTTP user agent string.
	 */
	public function __construct( Options_Manager $options, $user_agent ) {
		$this->options = $options;
		$this->user_agent = (string) $user_agent;
	}

	/**
	 * Handle the Slack notification.
	 *
	 * @param  Vulnerability_Interface[] $vulnerabilities List of vulnerabilities.
	 * @param  boolean                   $has_changed     Whether the status has changed since the last scan.
	 *
	 * @return void
	 */
	public function notify( $vulnerabilities, $has_changed ) {
		if (
			! $this->options->slack_enabled()
			|| ! $this->options->slack_url()
			|| empty( $vulnerabilities )
			|| ( ! $has_changed && ! $this->options->should_nag() )
		) {
			return;
		}

		// If an array only has one object, do_action() passes that object by itself
		// instead of the original array. Let's put it back in an array.
		if ( $vulnerabilities instanceof Vulnerability_Interface ) {
			$vulnerabilities = [ $vulnerabilities ];
		}

		$vuln_count = count( $vulnerabilities );
		$text = sprintf(
			'%s %s detected on %s. <%s|Please update your site.>',
			$vuln_count,
			1 === $vuln_count ? 'vulnerability' : 'vulnerabilities',
			get_bloginfo( 'name' ),
			admin_url( 'update-core.php' )
		);

		wp_remote_post( $this->options->slack_url(), [
			'body' => wp_json_encode( [
				'attachments' => [
					[
						'color' => 'danger',
						'fallback' => $text,
						'fields' => $this->build_attachment_fields(
							$vulnerabilities
						),
						'pretext' => $text,
					],
				],
			] ),
			'user-agent' => $this->user_agent,
		] );
	}

	/**
	 * Generates the fields array for the message attachment.
	 *
	 * @param  Vulnerability_Interface[] $vulnerabilities List of vulnerabilities.
	 *
	 * @return array
	 */
	protected function build_attachment_fields( $vulnerabilities ) {
		$fields = [];

		foreach ( $vulnerabilities as $vulnerability ) {
			$fixed_in = $vulnerability->fixed_in
				? 'Fixed in v' . $vulnerability->fixed_in
				: 'Not fixed yet';
			$wpvdb_url = "https://wpvulndb.com/vulnerabilities/{$vulnerability->id}";

			$fields[] = [
				'title' => $vulnerability->title,
				'value' => "{$fixed_in} - <{$wpvdb_url}|More info>",
			];
		}

		return $fields;
	}
}
