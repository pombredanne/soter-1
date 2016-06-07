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

$soter_autoloader = plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

if ( file_exists( $soter_autoloader ) ) {
	require_once $soter_autoloader;
}

if ( defined( 'WP_CLI' ) ) {
	WP_CLI::add_command(
		'security',
		'SSNepenthe\\Soter\\Command\\SecurityCommand'
	);
}

/**
 * Verify PHP version and disable plugin if not sufficient.
 */
function soter_php_version_check() {
	$php_version = phpversion();

	if ( version_compare( $php_version, '5.4', '>=' ) ) {
		return;
	}

	add_action( 'admin_notices', function() use ( $php_version ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );

		echo '<div class="notice notice-error">';
		printf(
			'<p>The Soter plugin requires PHP version 5.4 or greater. You are currently running PHP version %s.</p>',
			esc_html( $php_version )
		);
		echo '</div>';
	} );
}
add_action( 'plugins_loaded', 'soter_php_version_check' );

/**
 * Initialize the plugins settings page.
 */
function soter_settings_init() {
	$settings = new SSNepenthe\Soter\Options\Page;
	$settings->init();
}
add_action( 'admin_menu', 'soter_settings_init', 9 );

/**
 * Display checker results via admin notices.
 */
function soter_admin_notices() {
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

	if ( 'settings_page_soter' !== get_current_screen()->id ) {
		printf(
			'<p>%s %s detected. <a href="%s">Click here for the full report.</a></p>',
			esc_html( $count ),
			esc_html( $count_text ),
			esc_url( admin_url( 'options-general.php?page=soter' ) )
		);
	} else {
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
					// @todo
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

			// Already escaped.
			printf( '<p>%s</p>', implode( ' | ', $message['meta'] ) ); // WPCS: XSS ok.
		}
	}

	echo '</div>';
}
add_action( 'admin_notices', 'soter_admin_notices' );

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

	$mailer = new SSNepenthe\Soter\Mailers\WPMail(
		$vulnerabilities,
		$settings
	);
	$mailer->maybe_send();
}
add_action( 'SSNepenthe\\Soter\\run_check', 'soter_cron_init' );

/**
 * Schedule cron event on plugin activation.
 */
function soter_activation() {
	wp_schedule_event( time(), 'twicedaily', 'SSNepenthe\\Soter\\run_check' );
}
register_activation_hook( __FILE__, 'soter_activation' );

/**
 * Clear scheduled cron event on plugin deactivation.
 */
function soter_deactivation() {
	wp_clear_scheduled_hook( 'SSNepenthe\\Soter\\run_check' );
}
register_deactivation_hook( __FILE__, 'soter_deactivation' );

/**
 * Delete plugin options entries on plugin uninstallation.
 */
function soter_uninstall() {
	delete_option( 'soter_settings' );
	delete_option( 'soter_results' );
}
register_uninstall_hook( __FILE__, 'soter_uninstall' );

unset( $soter_autoloader );
