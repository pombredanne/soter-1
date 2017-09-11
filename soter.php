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
 * Initializes the plugin.
 *
 * @return void
 */
function _soter_init() {
	static $initialized = false;

	if ( $initialized ) {
		return;
	}

	$checker = new WP_Requirements\Plugin_Checker( 'Soter', __FILE__ );

	// Short array syntax.
	$checker->php_at_least( '5.4' );

	// Register_setting() with args array.
	$checker->wp_at_least( '4.7' );

	if ( ! $checker->requirements_met() ) {
		$checker->deactivate_and_notify();

		return;
	}

	$plugin = _soter_instance();

	register_activation_hook( $plugin['file'], [ $plugin, 'activate' ] );
	register_deactivation_hook( $plugin['file'], [ $plugin, 'deactivate' ] );

	add_action( 'plugins_loaded', [ $plugin, 'boot' ] );

	$initialized = true;
}

/**
 * Gets the plugin instance.
 *
 * @return Metis\Container
 */
function _soter_instance( $id = null ) {
	static $instance = null;

	if ( null !== $instance ) {
		return null === $id ? $instance : $instance[ $id ];
	}

	$instance = new Metis\Container( [
		'dir' => plugin_dir_path( __FILE__ ),
		'file' => __FILE__,
		'name' => 'Soter',
		'prefix' => 'soter',
		'url' => 'https://github.com/ssnepenthe/soter',
		'version' => '0.4.0',
	] );

	$instance->register( new Soter\Plugin_Provider );

	return _soter_instance( $id );
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
_soter_init();
