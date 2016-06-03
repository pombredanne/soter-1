<?php

$options = get_option( 'soter_settings', [] );
$email = isset( $options['email_address'] ) ? $options['email_address'] : '';
$default = get_bloginfo( 'admin_email' );

?><input class="something" id="soter_settings_email_address" name="soter_settings[email_address]" placeholder="<?php echo esc_attr( $default ); ?>" type="email" value="<?php echo esc_attr( $email ); ?>">

<?php unset( $options, $email, $default ); ?>
