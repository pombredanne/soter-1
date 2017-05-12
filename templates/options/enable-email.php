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
	<label>
		<input
			<?php checked( $enabled ) ?>
			id="soter_enable_email"
			name="soter_enable_email"
			type="checkbox"
			value="1"
		>
		Enable email notifications?
	</label>
	<p class="description">
		By default, an admin notice is shown when a vulnerability has been detected. Check this box to also receive an email notification.
	</p>
</fieldset>
