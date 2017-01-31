<?php

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

?><div class="notice notice-warning">
	<p>
		<?php echo esc_html( $count ) ?> <?php echo esc_html( $label ) ?> detected.
		<a href="<?php echo esc_url( admin_url( 'options-general.php?page=soter' ) ) ?>">
			Click here for the full report.
		</a>
	</p>
</div>
