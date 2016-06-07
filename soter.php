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

function soter_settings_init() {
	$settings = new SSNepenthe\Soter\Options\Page;
	$settings->init();
}
add_action( 'admin_menu', 'soter_settings_init', 9 );

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
		printf( '<h2>%s %s detected!</h2>', $count, $count_text );

		foreach ( $results->messages() as $message ) {
			printf( '<p><strong>%s</strong></p>', $message['title'] );
			printf( '<p>%s</p>', implode( ' | ', $message['meta'] ) );
		}
	}

	echo '</div>';
}
add_action( 'admin_notices', 'soter_admin_notices' );

function soter_cron_init() {
	if ( ! defined( 'DOING_CRON' ) || ! DOING_CRON ) {
		return;
	}

	$results = new SSNepenthe\Soter\Options\Results;
	$settings = new SSNepenthe\Soter\Options\Settings;

	$checker = new SSNepenthe\Soter\Checker;
	$vulnerabilities = $checker->check();

	$results->set_from_vulnerabilities_array( $vulnerabilities );
	$results->save();

	/**
	 * @todo MAILER!
	 */

	// $mailer = new Mailer( $vulnerabilities, $settings );
	// $mailer->maybe_send_notification();

	// if ( $settings->enable_email ) {
	// 	$email = empty( $settings->email_address ) ?
	// 		get_bloginfo( 'admin_email' ) :
	// 		$settings->email_address;

	// 	wp_mail( $email, 'Vulnerabilities Detected!', '' );
	// }
}
add_action( 'SSNepenthe\\Soter\\run_check', 'soter_cron_init' );

function soter_activation() {
	wp_schedule_event( time(), 'twicedaily', 'SSNepenthe\\Soter\\run_check' );
}
register_activation_hook( __FILE__, 'soter_activation' );

function soter_deactivation() {
	wp_clear_scheduled_hook( 'SSNepenthe\\Soter\\run_check' );
}
register_deactivation_hook( __FILE__, 'soter_deactivation' );

function soter_uninstall() {
	delete_option( 'soter_settings' );
	delete_option( 'soter_results' );
}
register_uninstall_hook( __FILE__, 'soter_uninstall' );

unset( $soter_autoloader );
