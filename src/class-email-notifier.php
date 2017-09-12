<?php
/**
 * Sends email notifications when appropriate.
 *
 * @package soter
 */

namespace Soter;

use League\Plates\Engine;
use Soter_Core\Vulnerabilities;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * This class creates and sends email notifications after a site scan.
 */
class Email_Notifier implements Notifier_Interface {
	/**
	 * Options manager instance.
	 *
	 * @var Options_Manager
	 */
	protected $options;

	/**
	 * Template engine instance.
	 *
	 * @var Engine
	 */
	protected $template;

	/**
	 * Class constructor.
	 *
	 * @param Engine          $template Template engine instance.
	 * @param Options_Manager $options  Options manager instance.
	 */
	public function __construct( Engine $template, Options_Manager $options ) {
		$this->template = $template;
		$this->options = $options;
	}

	/**
	 * Check whether this notifier is currently enabled.
	 *
	 * @return boolean
	 */
	public function is_enabled() {
		return $this->options->email_enabled;
	}

	/**
	 * Prepare and send a notification.
	 *
	 * @param Vulnerabilities $vulnerabilities List of vulnerabilities.
	 *
	 * @return void
	 */
	public function notify( Vulnerabilities $vulnerabilities ) {
		$use_html = 'html' === $this->options->email_type;

		wp_mail(
			$this->options->email_address,
			'Vulnerabilities detected on ' . get_bloginfo( 'name' ),
			$use_html
				? $this->render_html_email( $vulnerabilities )
				: $this->render_text_email( $vulnerabilities ),
			$use_html ? [ 'Content-type: text/html' ] : []
		);
	}

	public function render_html_email(
		Vulnerabilities $vulnerabilities,
		CssToInlineStyles $inliner = null
	) {
		$html = $this->template->render( 'emails/html/vulnerable.php', [
			'action_url' => admin_url( 'update-core.php' ),
			'vulnerabilities' => $vulnerabilities,
		] );
		$css = $this->template->render( 'emails/style.css' );

		return ( $inliner ?: new CssToInlineStyles() )->convert( $html, $css );
	}

	public function render_text_email( Vulnerabilities $vulnerabilities ) {
		return $this->template->render( 'emails/text/vulnerable.php', [
			'action_url' => admin_url( 'update-core.php' ),
			'vulnerabilities' => $vulnerabilities,
		] );
	}
}
