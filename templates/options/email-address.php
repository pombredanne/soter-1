<?php
/**
 * Template for the email address setting input.
 *
 * @package soter
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

?><input
	id="soter_settings_email_address"
	name="soter_settings[email_address]"
	placeholder="<?php echo esc_attr( $default ) ?>"
	type="email"
	value="<?php echo esc_attr( $current ) ?>"
>
