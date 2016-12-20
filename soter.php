<?php
/**
 * This plugin checks your site for vulnerabilities against the WPVulnDB API.
 *
 * @package soter
 */

/**
 * Plugin Name: Soter
 * Plugin URI: https://github.com/ssnepenthe/soter
 * Description: This plugin checks your site for vulnerabilities against the WPVulnDB API.
 * Version: 0.3.0
 * Author: ssnepenthe
 * Author URI: https://github.com/ssnepenthe
 * License: GPL-2.0
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:
 * Domain Path:
 */

$soter_dir = plugin_dir_path( __FILE__ );
$soter_basename = plugin_basename( __FILE__ );
$soter_autoloader = $soter_dir . 'vendor/autoload.php';

if ( file_exists( $soter_autoloader ) ) {
	require_once $soter_autoloader;
}

if ( defined( 'WP_CLI' ) ) {
	WP_CLI::add_command(
		'security',
		'SSNepenthe\\Soter\\Command\\Security_Command'
	);
}

// The checker class itself requires PHP 5.3 for namespace support. Since Composer
// requires 5.3.2 I plan on leaving this as-is.
$soter_checker = new SSNepenthe\Soter\Requirements_Checker(
	'Soter',
	$soter_basename
);

// For use of short array syntax.
$soter_checker->set_min_php( '5.4' );

if ( $soter_checker->requirements_met() ) {
	add_action( 'admin_menu', 'soter_settings_init', 9 );
	add_action( 'admin_notices', 'soter_abbreviated_admin_notice' );
	add_action( 'admin_notices', 'soter_full_admin_notice' );
	add_action( 'SSNepenthe\\Soter\\run_check', 'soter_cron_init' );

	register_activation_hook( __FILE__, 'soter_activation' );
	register_deactivation_hook( __FILE__, 'soter_deactivation' );
	register_uninstall_hook( __FILE__, 'soter_uninstall' );
} else {
	add_action( 'admin_init', [ $soter_checker, 'deactivate' ] );
	add_action( 'admin_notices', [ $soter_checker, 'notify' ] );
}

/**
 * Initialize the plugins settings page.
 */
function soter_settings_init() {
	$settings = new SSNepenthe\Soter\Options\Page;
	$settings->init();
}

/**
 * Displays the abbreviated admin notice on all pages except plugin settings.
 */
function soter_abbreviated_admin_notice() {
	if ( 'settings_page_soter' === get_current_screen()->id ) {
		return;
	}

	if ( ! current_user_can( 'update_plugins' ) ) {
		return;
	}

	$results = new SSNepenthe\Soter\Options\Results;

	if ( empty( $results->messages() ) ) {
		return;
	}

	$count = count( $results->messages() );
	$count_text = 1 < $count ? 'vulnerabilities' : 'vulnerability';

	echo '<div class="notice notice-warning">';

	printf(
		'<p>%s %s detected. <a href="%s">Click here for the full report.</a></p>',
		esc_html( $count ),
		esc_html( $count_text ),
		esc_url( admin_url( 'options-general.php?page=soter' ) )
	);

	echo '</div>';
}

/**
 * Displays the full admin notice on the plugin settings page.
 */
function soter_full_admin_notice() {
	if ( 'settings_page_soter' !== get_current_screen()->id ) {
		return;
	}

	if ( ! current_user_can( 'update_plugins' ) ) {
		return;
	}

	$results = new SSNepenthe\Soter\Options\Results;

	if ( empty( $results->messages() ) ) {
		return;
	}

	$count = count( $results->messages() );
	$count_text = 1 < $count ? 'vulnerabilities' : 'vulnerability';

	echo '<div class="notice notice-warning">';

	printf(
		'<h2>%s %s detected!</h2>',
		esc_html( $count ),
		esc_html( $count_text )
	);

	foreach ( $results->messages() as $message ) {
		$message['links'] = array_map( function( $key, $value ) {
			return sprintf(
				'<a href="%s" target="_blank">%s</a>',
				esc_url( $key ),
				esc_html( $value )
			);
		}, array_keys( $message['links'] ), $message['links'] );

		$message['meta'] = array_map( function( $value ) {
			$value = esc_html( $value );

			if ( false !== strpos( $value, 'Not fixed' ) ) {
				// @todo No inline styles.
				$value = sprintf(
					'<span style="color: #a00;">%s</span>',
					$value
				);
			}

			return $value;
		}, $message['meta'] );

		$message['meta'] = array_merge(
			$message['meta'],
			$message['links']
		);

		printf(
			'<p><strong>%s</strong></p>',
			esc_html( $message['title'] )
		);

		printf( '<p>%s</p>', implode( ' | ', $message['meta'] ) ); // WPCS: XSS ok.
	}

	echo '</div>';
}

/**
 * Check site via cron task.
 */
function soter_cron_init() {
	if ( ! defined( 'DOING_CRON' ) || ! DOING_CRON ) {
		return;
	}

	if ( ! function_exists( 'get_plugin_data' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	$results = new SSNepenthe\Soter\Options\Results;
	$settings = new SSNepenthe\Soter\Options\Settings;

	$checker = new SSNepenthe\Soter\Checker;
	$vulnerabilities = $checker->check();

	$results->set_from_vulnerabilities_array( $vulnerabilities );
	$results->save();

	$mailer = new SSNepenthe\Soter\Mailers\WP_Mail(
		$vulnerabilities,
		$settings
	);
	$mailer->maybe_send();
}

/**
 * Schedule cron event on plugin activation.
 */
function soter_activation() {
	wp_schedule_event( time(), 'twicedaily', 'SSNepenthe\\Soter\\run_check' );
}

/**
 * Clear scheduled cron event on plugin deactivation.
 */
function soter_deactivation() {
	wp_clear_scheduled_hook( 'SSNepenthe\\Soter\\run_check' );
}

/**
 * Delete plugin options entries on plugin uninstallation.
 */
function soter_uninstall() {
	delete_option( 'soter_settings' );
	delete_option( 'soter_results' );
}

unset( $soter_autoloader, $soter_basename, $soter_checker, $soter_dir );
