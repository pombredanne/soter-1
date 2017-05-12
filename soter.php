<?php
/**
 * This plugin checks your site for vulnerabilities against the WPScan vulnerabilities database API.
 *
 * @package soter
 */

/**
 * Plugin Name: Soter
 * Plugin URI: https://github.com/ssnepenthe/soter
 * Description: This plugin checks your site for vulnerabilities against the WPScan vulnerabilities database API.
 * Version: 0.4.0
 * Author: Ryan McLaughlin
 * Author URI: https://github.com/ssnepenthe
 * License: GPL-2.0
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:
 * Domain Path:
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Require a file (once) if it exists.
 *
 * @param  string $file The file to check and require.
 *
 * @return void
 */
function _soter_require_if_exists( $file ) {
	if ( file_exists( $file ) ) {
		require_once $file;
	}
}

function _soter_init() {
	$checker = new WP_Requirements\Plugin_Checker( 'Soter', __FILE__ );

	// Short array syntax.
	$checker->php_at_least( '5.4' );

	if ( $checker->requirements_met() ) {
		$plugin = new Soter\Plugin( [
			'dir' => plugin_dir_path( __FILE__ ),
			'file' => __FILE__,
			'name' => 'Soter',
			'prefix' => 'soter',
			'url' => 'https://github.com/ssnepenthe/soter',
			'version' => '0.4.0',
		] );

		$providers = [
			new Soter\Listeners\Listeners_Provider,
			new Soter\Notices\Notices_Provider,
			new Soter\Options\Options_Provider,
			new Soter\Tasks\Tasks_Provider,
			new Soter\Views\View_Provider,
			new Soter\Plugin_Provider,
			new Soter\Soter_Core_Provider,
		];

		foreach ( $providers as $provider ) {
			$plugin->register( $provider );
		}

		add_action( 'plugins_loaded', [ $plugin, 'boot' ] );
	} else {
		$checker->deactivate_and_notify();
	}
}

_soter_require_if_exists( __DIR__ . '/vendor/autoload.php' );
_soter_init();
