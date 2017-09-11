<?php
/**
 * Template for the html email setting input.
 *
 * @package soter
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

?><fieldset>
	<legend class="screen-reader-text">
		<span>Email Type</span>
	</legend>

	<label>
		<input
			<?php checked( 'text' === $type ); ?>
			id="soter_email_type_text"
			name="soter_email_type"
			type="radio"
			value="text"
		>
		Text
	</label>

	<br>

	<label>
		<input
			<?php checked( 'text' === $type, false ); ?>
			id="soter_email_type_html"
			name="soter_email_type"
			type="radio"
			value="html"
		>
		HTML
	</label>
</fieldset>
