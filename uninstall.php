<?php
/**
 * The plugin uninstall script.
 *
 * @package soter
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

/**
 * Require a file (once) if it exists.
 *
 * Needed because the main plugin file isn't loaded on uninstall. Wrapped in a
 * conditional check just to be safe.
 *
 * @param  string $file The file to check and require.
 *
 * @return void
 */
if ( ! function_exists( '_soter_require_if_exists' ) ) {
	function _soter_require_if_exists( $file ) {
		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
}

/**
 * The plugin uninstaller.
 *
 * @return void
 */
function _soter_uninstall() {
	$options = [
		'soter_email_address',
		'soter_email_type',
		'soter_ignored_plugins',
		'soter_ignored_themes',
		'soter_installed_version',
		'soter_last_scan_hash',
		'soter_should_nag',
	];

	foreach ( $options as $option ) {
		delete_option( $option );
	}

	( new Soter_Core\WP_Transient_Cache( $GLOBALS['wpdb'], 'soter' ) )->flush();
}

_soter_require_if_exists( __DIR__ . '/vendor/autoload.php' );
_soter_uninstall();
