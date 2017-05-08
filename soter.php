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

_soter_require_if_exists( __DIR__ . '/vendor/autoload.php' );

$soter_checker = new WP_Requirements\Plugin_Checker( 'Soter', __FILE__ );

// Variadic functions, argument unpacking, use function.
$soter_checker->php_at_least( '5.6' );

if ( $soter_checker->requirements_met() ) {
	$soter_plugin = new SSNepenthe\Soter\Plugin( [
		'dir' => plugin_dir_path( __FILE__ ),
		'file' => __FILE__,
		'prefix' => 'soter',
		'url' => 'https://github.com/ssnepenthe/soter',
		'version' => '0.4.0',
	] );
	$soter_plugin->init();

	register_activation_hook( __FILE__, [ $soter_plugin, 'activate' ] );
	register_deactivation_hook( __FILE__, [ $soter_plugin, 'deactivate' ] );
} else {
	$soter_checker->deactivate_and_notify();
}

unset( $soter_checker, $soter_plugin );
