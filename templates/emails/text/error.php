<?php
/**
 * Template for error email notification.
 *
 * Don't mess with the spacing/indentation!
 *
 * Note: Since any site can filter an email's content type before sending, it seems
 * that we can never be certain that a text email is actually being sent with a text
 * content type. With that in mind, all values are escaped under the assumption that
 * this message has been sent with an HTML content type.
 *
 * @package soter
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

?>There was an HTTP error while checking <?php echo esc_html( $site_name ); ?> for vulnerabilities. We've included some details to help you fix the problem.

Security Digest for <?php echo esc_html( $site_name ); ?> ( <?php echo esc_html( $site_url ); ?> )

******************
Error!
******************

A recent scan by the Soter plugin could not be completed due to an HTTP error with the message:

<?php echo esc_html( $message ); ?>


Please notify your site admin.

Scan performed by the Soter Plugin ( <?php echo esc_url( $plugin_url ); ?> ) against the WPScan Vulnerability Database ( https://wpvulndb.com/ ) API.

DISCLAIMER: Soter does not verify the integrity of individual packages on your site - It checks installed packages by name and version against a list of known vulnerabilities.
