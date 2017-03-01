<?php
/**
 * Registers user meta for the REST API.
 *
 * @package soter
 */

namespace SSNepenthe\Soter;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * This class registers plugin-specific user meta for use in the REST API.
 */
class Register_User_Meta {
	/**
	 * Hooks the class in to WordPress.
	 */
	public function init() {
		add_action( 'init', [ $this, 'register_user_meta' ] );
	}

	/**
	 * Registers the soter_notice_dismissed user meta key.
	 */
	public function register_user_meta() {
		register_meta( 'user', 'soter_notice_dismissed', [
			'type' => 'integer',
			'single' => true,
			'show_in_rest' => true,
		] );
	}
}
