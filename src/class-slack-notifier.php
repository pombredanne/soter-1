<?php
/**
 * Slack_Notifier class.
 *
 * @package soter
 */

namespace Soter;

use Soter_Core\Vulnerabilities;

/**
 * Defines the Slack notifier class.
 */
class Slack_Notifier implements Notifier_Interface {
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
	 * Check whether this notifier is currently enabled.
	 *
	 * @return boolean
	 */
	public function is_enabled() {
		return $this->options->slack_enabled && $this->options->slack_url;
	}

	/**
	 * Prepare and send a notification.
	 *
	 * @param  Vulnerabilities $vulnerabilities List of vulnerabilities.
	 *
	 * @return void
	 */
	public function notify( Vulnerabilities $vulnerabilities ) {
		$vuln_count = $vulnerabilities->count();
		$text = sprintf(
			'%s %s detected on %s. <%s|Please update your site.>',
			$vuln_count,
			1 === $vuln_count ? 'vulnerability' : 'vulnerabilities',
			get_bloginfo( 'name' ),
			admin_url( 'update-core.php' )
		);

		wp_remote_post( $this->options->slack_url, [
			'body' => wp_json_encode( [
				'attachments' => [
					[
						'color' => 'danger',
						'fallback' => $text,
						'fields' => $this->build_attachment_fields( $vulnerabilities ),
						'pretext' => $text,
					],
				],
			] ),
			'headers' => [
				'Content-type' => 'application/json',
			],
			'user-agent' => $this->user_agent,
		] );
	}

	/**
	 * Generates the fields array for the message attachment.
	 *
	 * @param  Vulnerabilities $vulnerabilities List of vulnerabilities.
	 *
	 * @return array
	 */
	protected function build_attachment_fields( Vulnerabilities $vulnerabilities ) {
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
