<?php
/**
 * Template for the notification frequency setting input.
 *
 * @package soter
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

?><fieldset>
	<legend class="screen-reader-text">
		<span>Notification Frequency</span>
	</legend>

	<label>
		<input
			<?php checked( $should_nag ); ?>
			id="soter_should_nag_yes"
			name="soter_should_nag"
			type="radio"
			value="yes"
		>
		Send notifications after every scan where vulnerabilities are found.
	</label>

	<br>

	<label>
		<input
			<?php checked( $should_nag, false ); ?>
			id="soter_should_nag_no"
			name="soter_should_nag"
			type="radio"
			value="no"
		>
		Only send notifications after scans where the vulnerability status has changed.
	</label>
</fieldset>
