<?php
/**
 * Registers user meta for the REST API.
 *
 * @package soter
 */

namespace Soter;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * This class registers plugin-specific user meta for use in the REST API.
 */
class Register_User_Meta {
	/**
	 * Registers the soter_notice_dismissed user meta key.
	 */
	public function register() {
		register_meta( 'user', 'soter_notice_dismissed', [
			'type' => 'integer',
			'single' => true,
			'show_in_rest' => true,
		] );
	}
}
