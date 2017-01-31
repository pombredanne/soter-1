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

$soter_dir = plugin_dir_path( __FILE__ );
$soter_basename = plugin_basename( __FILE__ );
$soter_autoloader = $soter_dir . 'vendor/autoload.php';

if ( file_exists( $soter_autoloader ) ) {
	require_once $soter_autoloader;
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
	$soter_plugin = new SSNepenthe\Soter\Plugin( __FILE__ );
	$soter_plugin->init();

	register_activation_hook( __FILE__, [ $soter_plugin, 'activate' ] );
	register_deactivation_hook( __FILE__, [ $soter_plugin, 'deactivate' ] );
} else {
	add_action( 'admin_init', [ $soter_checker, 'deactivate' ] );
	add_action( 'admin_notices', [ $soter_checker, 'notify' ] );
}

unset(
	$soter_autoloader,
	$soter_basename,
	$soter_checker,
	$soter_dir,
	$soter_plugin
);
