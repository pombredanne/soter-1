<?php

$options = get_option( 'soter_settings' );
$ignored = isset( $options['ignored_plugins'] ) ? $options['ignored_plugins'] : [];
$plugins = get_plugins();
$count = count( $plugins );
$counter = 0;

?><fieldset>

	<?php foreach ( $plugins as $file => $data ) : ?>

		<?php $counter++; ?>

		<?php list( $slug, $basename ) = explode( DIRECTORY_SEPARATOR, $file ); ?>

		<label>
			<input <?php checked( in_array( $slug, $ignored ) ); ?> class="something" id="<?php printf( 'soter_settings_%s', esc_attr( $slug ) ); ?>" name="soter_settings[ignored_plugins][]" type="checkbox" value="<?php echo esc_attr( $slug ) ?>">
			<?php echo esc_html( $data['Name'] ); ?>
		</label>

		<?php if ( $counter < $count ) : ?>
			<br>
		<?php endif; ?>

	<?php endforeach; ?>

	<p class="description">Select any plugins that should be ignored by the security checker (i.e. custom plugins).</p>

</fieldset>

<?php unset( $options, $ignored, $plugins, $count, $counter ); ?>
