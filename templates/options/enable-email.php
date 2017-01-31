<?php

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

?><fieldset>
	<label>
		<input<?php checked( $enabled, true ) ?>
			id="soter_settings_enable_email"
			name="soter_settings[enable_email]"
			type="checkbox"
			value="1"
		>
		Enable email notifications?
	</label>
	<p class="description">
		By default, an admin notice is shown when a vulnerability has been detected. Check this box to also receive an email notification.
	</p>
</fieldset>
