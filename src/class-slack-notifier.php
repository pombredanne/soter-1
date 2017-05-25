<?php

namespace Soter;

use Soter\Options\Options_Manager;
use Soter_Core\Vulnerability_Interface;

class Slack_Notifier {
	protected $options;
	protected $user_agent;

	public function __construct( Options_Manager $options, $user_agent ) {
		$this->options = $options;
		$this->user_agent = (string) $user_agent;
	}

	public function notify( $vulnerabilities, $has_changed ) {
		if (
			empty( $vulnerabilities )
			|| ( ! $has_changed && ! $this->options->should_nag() )
			|| ! $this->options->slack_url()
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
				'attachments' => [ [
					'color' => 'danger',
					'fallback' => $text,
					'fields' => $this->build_attachment_fields( $vulnerabilities ),
					'pretext' => $text,
				] ],
			] ),
			'user-agent' => $this->user_agent,
		] );
	}

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
