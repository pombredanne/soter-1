<?php
/**
 * Register the 'soter check' command with WP-CLI
 *
 * @package soter
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

WP_CLI::add_command( 'soter', 'SSNepenthe\\Soter\\Console\\Command\\WPCLICheckCommand' );
