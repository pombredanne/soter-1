<?php

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

?><div class="notice notice-error">
	<p>
		Soter configuration error: All notification channels have been disabled. Please visit the <a href="<?php echo esc_url( admin_url( 'options-general.php?page=soter' ) ) ?>">Soter settings page</a> and enable one or more channels.
	</p>
</div>
