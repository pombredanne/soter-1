<?php

namespace SSNepenthe\Soter;

use SSNepenthe\Soter\Options\Results;

class Abbreviated_Admin_Notice_Notification {
	protected $results;
	protected $template;

	public function __construct( List_Option $results, Template $template ) {
		$this->results = $results;
		$this->template = $template;
	}

	public function init() {
		add_action( 'admin_notices', [ $this, 'notify' ] );
	}

	public function notify() {
		if ( 'settings_page_soter' === get_current_screen()->id ) {
			return;
		}

		if ( ! current_user_can( 'update_plugins' ) ) {
			return;
		}

		if ( $this->results->is_empty() ) {
			return;
		}

		$data = [
			'count' => count( $this->results->all() ),
		];

		$data['label'] = 1 < $data['count'] ? 'vulnerabilities' : 'vulnerability';

		$this->template->output( 'notifications/abbreviated-admin-notice', $data );
	}
}
