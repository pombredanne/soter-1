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

_soter_uninstall();
