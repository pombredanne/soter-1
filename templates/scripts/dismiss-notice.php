<?php
/**
 * Inline script for dismissible admin notice.
 *
 * @package soter
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

?>jQuery( 'document' ).ready( function() {
	jQuery( '.js-soter-notice.is-dismissible' ).on( 'click', '.notice-dismiss', function() {
		jQuery.post( <?php echo wp_json_encode( $url ) ?>, {
			meta: {
				// Divide by 1000 to match PHP time(), plus 12 hours in seconds.
				soter_notice_dismissed: Math.round( +new Date / 1000 ) + ( 12 * 60 * 60 )
			},
			_wpnonce: <?php echo wp_json_encode( $nonce ) ?>,
		} );
	} );
} );
