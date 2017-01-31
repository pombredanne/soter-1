<?php

namespace SSNepenthe\Soter\Notices;

use SSNepenthe\Soter\Views\Template;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Vulnerable_Site_Abbreviated {
	protected $template;
	protected $vuln_ids;

	public function __construct( Template $template, array $vuln_ids = [] ) {
		$this->template = $template;
		$this->vuln_ids = array_map( 'absint', $vuln_ids );
	}

	public function init() {
		add_action( 'admin_notices', [ $this, 'print_notice' ] );
	}

	public function print_notice() {
		if ( 'settings_page_soter' === get_current_screen()->id ) {
			return;
		}

		if ( ! current_user_can( 'update_plugins' ) ) {
			return;
		}

		if ( empty( $this->vuln_ids ) ) {
			return;
		}

		$count = count( $this->vuln_ids );
		$label = 1 < $count ? 'vulnerabilities' : 'vulnerability';

		$this->template->output(
			'notices/vulnerable-site-abbreviated',
			compact( 'count', 'label' )
		);
	}
}
