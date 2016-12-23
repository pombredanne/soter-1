<?php

namespace SSNepenthe\Soter;

use SSNepenthe\Soter\Options\Results;

class Vulnerable_Full_Admin_Notice_Notifier implements Notifier_Interface {
	protected $data = [];
	protected $template;

	public function __construct( Template $template ) {
		$this->template = $template;
	}

	public function init() {
		add_action( 'admin_notices', [ $this, 'notify' ] );
	}

	public function notify() {
		if ( 'settings_page_soter' !== get_current_screen()->id ) {
			return;
		}

		if ( ! current_user_can( 'update_plugins' ) ) {
			return;
		}

		if ( empty( $this->data ) ) {
			return;
		}

		$data = [ 'messages' => $this->data ];
		$data['count'] = count( $data['messages'] );
		$data['label'] = 1 < $data['count'] ? 'vulnerabilities' : 'vulnerability';

		$this->template->output( 'notifications/full-admin-notice', $data );
	}

	public function set_data( array $data ) {
		$this->data = $data;
	}
}
