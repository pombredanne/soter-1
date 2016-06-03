<?php

$options = get_option( 'soter_settings' );
$ignored = isset( $options['ignored_themes'] ) ? $options['ignored_themes'] : [];
$themes = get_themes();
$count = count( $themes );
$counter = 0;

?><fieldset>

	<?php foreach ( $themes as $name => $object ) : ?>

		<?php $counter++; ?>

		<label>
			<input <?php checked( in_array( $object->stylesheet, $ignored ) ); ?> class="something" id="<?php printf( 'soter_settings_%s', esc_attr( $object->stylesheet ) ); ?>" name="soter_settings[ignored_themes][]" type="checkbox" value="<?php echo esc_attr( $object->stylesheet ) ?>">
			<?php echo esc_html( $name ); ?>
		</label>

		<?php if ( $counter < $count ) : ?>
			<br>
		<?php endif; ?>

	<?php endforeach; ?>

	<p class="description">Select any themes that should be ignored by the security checker (i.e. custom themes).</p>

</fieldset>

<?php unset( $options, $ignored, $themes, $count, $counter ); ?>
