<?php
/**
 * Abbreviated vulnerable site notice.
 *
 * @package soter
 */

namespace SSNepenthe\Soter\Notices;

use SSNepenthe\Soter\Views\Template;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * This class adds an abbreviated admin notice informing users when their site is
 * vulnerable and how many vulnerabilities were detected.
 */
class Vulnerable_Site_Abbreviated {
	/**
	 * Template instance.
	 *
	 * @var Template
	 */
	protected $template;

	/**
	 * List of vulnerability IDs that affect the site in its current state.
	 *
	 * @var int[]
	 */
	protected $vuln_ids;

	/**
	 * Class constructor.
	 *
	 * @param Template $template Template instance.
	 * @param int[]    $vuln_ids List of vulnerability IDs that affect the site.
	 */
	public function __construct( Template $template, array $vuln_ids = [] ) {
		$this->template = $template;
		$this->vuln_ids = array_map( 'absint', $vuln_ids );
	}

	/**
	 * Prints the notice dismissal javascript.
	 */
	public function print_dismiss_notice_script() {
		if ( ! $this->should_print_notice() ) {
			return;
		}

		$data = [
			'url' => esc_url_raw( get_rest_url() ) . 'wp/v2/users/me/',
			'nonce' => wp_create_nonce( 'wp_rest' ),
		];

		wp_add_inline_script(
			'jquery-core',
			$this->template->render( 'scripts/dismiss-notice', $data )
		);
	}

	/**
	 * Determine whether or not this notice should be printed.
	 *
	 * @return bool
	 */
	protected function should_print_notice() {
		if ( 'settings_page_soter' === get_current_screen()->id ) {
			return false;
		}

		if ( ! current_user_can( 'update_plugins' ) ) {
			return false;
		}

		if ( empty( $this->vuln_ids ) ) {
			return false;
		}

		$until = get_user_meta(
			get_current_user_id(),
			'soter_notice_dismissed',
			true
		);

		if ( $until && time() <= $until ) {
			return false;
		}

		return true;
	}

	/**
	 * Prints the actual notice.
	 */
	public function print_notice() {
		if ( ! $this->should_print_notice() ) {
			return;
		}

		$count = count( $this->vuln_ids );
		$label = 1 === $count ? 'vulnerability' : 'vulnerabilities';

		$this->template->output(
			'notices/vulnerable-site-abbreviated',
			compact( 'count', 'label' )
		);
	}
}
