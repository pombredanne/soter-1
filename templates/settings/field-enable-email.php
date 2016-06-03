<?php

$options = get_option( 'soter_settings', [] );
$enabled = isset( $options['enable_email' ] ) ? $options['enable_email'] : false;

?><fieldset>
	<label>
		<input <?php checked( $enabled ); ?>class="something" id="soter_settings_enable_email" name="soter_settings[enable_email]" type="checkbox" value="1">
		Enable email notifications?
	</label>

	<p class="description">By default, an admin notice is shown when a vulnerability has been detected. Check this box to also receive an email notification.</p>
</fieldset>

<?php unset( $options, $enabled ); ?>
