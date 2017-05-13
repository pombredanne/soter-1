<?php
/**
 * Template for the enable email setting input.
 *
 * @package soter
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

?><fieldset>
	<legend class="screen-reader-text">
		<span>
			Notification types
		</span>
	</legend>

	<label>
		<input
			<?php checked( $email_checked ) ?>
			id="soter_enable_email"
			name="soter_enable_email"
			type="checkbox"
			value="1"
		>
		Enable email notifications?
	</label>

	<br>

	<label>
		<input
			<?php checked( $notices_checked ) ?>
			id="soter_enable_notices"
			name="soter_enable_notices"
			type="checkbox"
			value="1"
		>
		Enable admin notices?
	</label>
</fieldset>
