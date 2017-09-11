<?php
/**
 * Template for a boolean (enable/disable) setting input.
 *
 * @package soter
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

?><fieldset>
	<legend class="screen-reader-text">
		<span><?php echo esc_html( $label ); ?></span>
	</legend>

	<label>
		<input
			<?php checked( $checked ); ?>
			id="<?php echo esc_attr( $setting ); ?>"
			name="<?php echo esc_attr( $setting ); ?>"
			type="checkbox"
			value="1"
		>
		<?php echo esc_html( $label ); ?>
	</label>
</fieldset>
