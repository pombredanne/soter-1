<?php

namespace SSNepenthe\Soter;

use SSNepenthe\Soter\Options\Results;

class Full_Admin_Notice_Notification {
	protected $results;

	public function __construct( Results $results ) {
		$this->results = $results;
	}

	public function init() {
		add_action( 'admin_notices', [ $this, 'notify' ] );
	}

	public function notify() {
		if ( 'settings_page_soter' !== get_current_screen()->id ) {
			return;
		}

		if ( ! current_user_can( 'update_plugins' ) ) {
			return;
		}

		if ( empty( $this->results->messages() ) ) {
			return;
		}

		$count = count( $this->results->messages() );
		$count_text = 1 < $count ? 'vulnerabilities' : 'vulnerability';

		echo '<div class="notice notice-warning">';

		printf(
			'<h2>%s %s detected!</h2>',
			esc_html( $count ),
			esc_html( $count_text )
		);

		foreach ( $this->results->messages() as $message ) {
			$message['links'] = array_map( function( $key, $value ) {
				return sprintf(
					'<a href="%s" target="_blank">%s</a>',
					esc_url( $key ),
					esc_html( $value )
				);
			}, array_keys( $message['links'] ), $message['links'] );

			$message['meta'] = array_map( function( $value ) {
				$value = esc_html( $value );

				if ( false !== strpos( $value, 'Not fixed' ) ) {
					// @todo No inline styles.
					$value = sprintf(
						'<span style="color: #a00;">%s</span>',
						$value
					);
				}

				return $value;
			}, $message['meta'] );

			$message['meta'] = array_merge(
				$message['meta'],
				$message['links']
			);

			printf(
				'<p><strong>%s</strong></p>',
				esc_html( $message['title'] )
			);

			printf( '<p>%s</p>', implode( ' | ', $message['meta'] ) ); // WPCS: XSS ok.
		}

		echo '</div>';
	}
}
