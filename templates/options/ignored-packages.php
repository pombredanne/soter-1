<?php
/**
 * Template for the ignored plugins/themes setting input.
 *
 * @package soter
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

?><fieldset>
	<?php foreach ( $packages as $package ) : ?>
		<label>
			<input<?php checked( in_array( $package['slug'], $ignored_packages, true ), true ) ?>
				id="soter_settings_<?php echo esc_attr( $package['slug'] ) ?>"
				name="soter_settings[ignored_<?php echo esc_attr( $type ) ?>][]"
				type="checkbox"
				value="<?php echo esc_attr( $package['slug'] ) ?>"
			>
			<?php echo esc_html( $package['name'] ) ?>
		</label>

		<br>
	<?php endforeach ?>

	<p class="description">
		Select any <?php echo esc_html( $type ) ?> that should be ignored by the security checker (i.e. custom <?php echo esc_html( $type ) ?>).
	</p>
</fieldset>
