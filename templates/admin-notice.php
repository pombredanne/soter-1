<?php
/**
 * Template for a basic admin notice.
 *
 * @package soter
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

?><div class="notice notice-<?php echo esc_attr( $type ); ?>">
	<p><?php echo esc_html( $message ); ?></p>
</div>
