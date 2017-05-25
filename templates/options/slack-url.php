<?php
/**
 * Template for the Slack webhook URL input.
 *
 * @package soter
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

?><input
	class="large-text"
	id="soter_slack_url"
	name="soter_slack_url"
	placeholder="<?php echo esc_attr( $placeholder ) ?>"
	type="text"
	value="<?php echo esc_attr( $value ) ?>"
>
