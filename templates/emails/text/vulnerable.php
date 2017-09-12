<?php
/**
 * Template for vulnerable site text email notification.
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

?>Vulnerabilities were detected on <?php echo esc_html( $site_name ); ?>. We've included some details to help you fix the problem.

Security Digest for <?php echo esc_html( $site_name ); ?> ( <?php echo esc_html( $site_url ); ?> )

******************
Vulnerabilities Detected!
******************

A recent scan by the Soter plugin flagged one or more vulnerabilities on your site.

Please ensure WordPress as well as all plugins and themes are up-to-date from your dashboard.


Go To Dashboard ( <?php echo esc_url( $action_url ); ?> )


For reference, here are the details of the detected vulnerabilities:


<?php foreach ( $vulnerabilities as $vulnerability ) : ?>
 * Title: <?php
	// @todo This feels so wrong...
	echo esc_html( str_replace(
		[ '<', '>', '=' ],
		[ 'less than', 'greater than', ' or equal to' ],
		$vulnerability->title
	) );
?>

<?php if ( $vulnerability->vuln_type ) : ?>
 * Type: <?php echo esc_html( $vulnerability->vuln_type ); ?>

<?php endif; ?>
<?php if ( $vulnerability->published_date ) : ?>
 * Published: <?php echo esc_html( $vulnerability->published_date->format( 'd M Y' ) ); ?>

<?php endif; ?>
 * Fixed In: <?php echo esc_html( $vulnerability->fixed_in ? "v{$vulnerability->fixed_in}" : 'NOT YET FIXED' ); ?>

 * More Info: https://wpvulndb.com/vulnerabilities/<?php echo esc_html( $vulnerability->id ); ?>


<?php endforeach ?>

Scan performed by the Soter Plugin ( <?php echo esc_url( $plugin_url ); ?> ) against the WPScan Vulnerability Database ( https://wpvulndb.com/ ) API.

DISCLAIMER: Soter does not verify the integrity of individual packages on your site - It checks installed packages by name and version against a list of known vulnerabilities.
