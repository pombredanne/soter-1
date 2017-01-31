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
	 * Hooks the notice in to WordPress.
	 */
	public function init() {
		add_action( 'admin_notices', [ $this, 'print_notice' ] );
	}

	/**
	 * Prints the actual notice.
	 */
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
