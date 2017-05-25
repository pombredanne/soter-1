<?php
/**
 * Template for the full settings page.
 *
 * @package soter
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

?><div class="wrap">
	<h1><?php echo esc_html( $title ) ?></h1>

	<form action="options.php" method="POST">

		<?php settings_fields( $group ) ?>
		<?php do_settings_sections( $page ) ?>
		<?php submit_button() ?>

	</form>
</div>
