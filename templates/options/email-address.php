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
	id="soter_email_address"
	name="soter_email_address"
	placeholder="<?php echo esc_attr( $placeholder ) ?>"
	type="email"
	value="<?php echo esc_attr( $value ) ?>"
>
