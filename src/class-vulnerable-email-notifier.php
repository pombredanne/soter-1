<?php

namespace SSNepenthe\Soter;

class Vulnerable_Email_Notifier implements Notifier_Interface {
	protected $data = [];
	protected $settings;
	protected $template;

	public function __construct( Map_Option $settings, Template $template ) {
		$this->settings = $settings;
		$this->template = $template;
	}

	public function set_data( array $data ) {
		$this->data = $data;
	}

	public function notify() {
		if ( empty( $this->data ) ) {
			return;
		}

		if ( ! $this->settings->get( 'enable_email', false ) ) {
			return;
		}

		$to = $this->settings->get( 'email_address', '' );

		if ( empty( $to ) ) {
			$to = get_bloginfo( 'admin_email' );
		}

		$count = count( $this->data );
		$site_name = get_bloginfo( 'name' );
		$subject = sprintf(
			'[%s] %s %s detected',
			$site_name,
			$count, 1 < $count ? 'vulnerabilities' : 'vulnerability'
		);
		$headers = [];
		$template = 'emails/text-vulnerable';

		if ( $this->settings->get( 'html_email', false ) ) {
			$headers[] = 'Content-type: text/html';
			$template = 'emails/html-vulnerable';
		}

		wp_mail( $to, $subject, $this->template->render( $template, [
			'action_url' => admin_url( 'update-core.php' ),
			'count' => $count,
			'messages' => $this->data,
			'site_name' => $site_name,
		] ), $headers );
	}
}
