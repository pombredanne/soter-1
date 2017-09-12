<?php
/**
 * Run scheduled site checks.
 *
 * @package soter
 */

namespace Soter;

use Soter_Core\Checker;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * This class hooks the checker to our WP-Cron hook.
 */
class Check_Site_Job {
	/**
	 * Checker instance.
	 *
	 * @var Checker
	 */
	protected $checker;

	/**
	 * Options manager instance.
	 *
	 * @var Options_Manager
	 */
	protected $options;

	/**
	 * Class constructor.
	 *
	 * @param Checker         $checker Checker instance.
	 * @param Options_Manager $options Options manager instance.
	 */
	public function __construct( Checker $checker, Options_Manager $options ) {
		$this->checker = $checker;
		$this->options = $options;
	}

	/**
	 * Run the site check.
	 *
	 * @return void
	 */
	public function run() {
		try {
			$vulnerabilities = $this->checker->check_site( $this->options->ignored_packages );

			do_action( 'soter_check_complete', $vulnerabilities );

			$this->options->get_store()->set( 'last_scan_hash', $vulnerabilities->hash() );
		} catch ( \RuntimeException $e ) {
			// Gross?
			$options = _soter_instance( 'options_manager' );
			$use_html = 'html' === $options->email_type;

			wp_mail(
				// @todo Should this go straight to admin email instead?
				$options->email_address,
				'HTTP error while checking ' . get_bloginfo( 'name' ) . ' for vulnerabilities',
				$use_html
					? $this->render_html_error_email( $e )
					: $this->render_text_error_email( $e ),
				$use_html ? [ 'Content-type: text/html' ] : []
			);
		}
	}

	/**
	 * Render the contents of the HTML error notification.
	 *
	 * @param  \RuntimeException      $exception Exception instance.
	 * @param  CssToInlineStyles|null $inliner   Style inliner.
	 *
	 * @return string
	 */
	public function render_html_error_email( $exception, CssToInlineStyles $inliner = null ) {
		$plates = _soter_instance( 'plates' );
		$html = $plates->render( 'emails/html/error.php', [
			'message' => $exception->getMessage(),
		] );
		$css = $plates->render( 'emails/style.css' );

		return ( $inliner ?: new CssToInlineStyles() )->convert( $html, $css );
	}

	/**
	 * Render the contents of the text error notification.
	 *
	 * @param  \RuntimeException $exception Exception instance.
	 *
	 * @return string
	 */
	public function render_text_error_email( $exception ) {
		return _soter_instance( 'plates' )->render( 'emails/text/error.php', [
			'message' => $exception->getMessage(),
		] );
	}
}
