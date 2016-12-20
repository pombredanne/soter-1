<?php

namespace SSNepenthe\Soter;

use SSNepenthe\Soter\Options\Results;

class Abbreviated_Admin_Notice_Notification {
	public function init() {
		add_action( 'admin_notices', [ $this, 'notify' ] );
	}

	public function notify() {
		if ( 'settings_page_soter' === get_current_screen()->id ) {
			return;
		}

		if ( ! current_user_can( 'update_plugins' ) ) {
			return;
		}

		$results = new Results;

		if ( empty( $results->messages() ) ) {
			return;
		}

		$count = count( $results->messages() );
		$count_text = 1 < $count ? 'vulnerabilities' : 'vulnerability';

		echo '<div class="notice notice-warning">';

		printf(
			'<p>%s %s detected. <a href="%s">Click here for the full report.</a></p>',
			esc_html( $count ),
			esc_html( $count_text ),
			esc_url( admin_url( 'options-general.php?page=soter' ) )
		);

		echo '</div>';
	}
}
