<?php

namespace SSNepenthe\Soter\Options;

class Results {
	const OPTION_KEY = 'soter_results';

	protected $messages;

	public function __construct() {
		$this->messages = get_option( self::OPTION_KEY, [] );
	}

	public function messages() {
		return $this->messages;
	}

	public function save() {
		return update_option( self::OPTION_KEY, $this->messages );
	}

	public function set_from_vulnerabilities_array( array $vulnerabilities ) {
		$this->messages = [];

		if ( empty( $vulnerabilities ) ) {
			return;
		}

		foreach ( $vulnerabilities as $vulnerability ) {
			$message = [
				'title' => $vulnerability->title,
				'meta' => [],
			];

			if ( ! is_null( $vulnerability->published_date ) ) {
				$message['meta'][] = sprintf(
					'Published %s',
					esc_html( $vulnerability->published_date->format( 'd M Y' ) )
				);
			}

			if ( isset( $vulnerability->references->url ) ) {
				foreach ( $vulnerability->references->url as $url ) {
					$parsed = wp_parse_url( $url );

					$link_text = isset( $parsed['host'] ) ?
						$parsed['host'] :
						'Link';

					$message['meta'][] = sprintf(
						'<a href="%s" target="_blank">%s</a>',
						esc_url( $url ),
						esc_html( $link_text )
					);
				}
			}

			$message['meta'][] = sprintf(
				'<a href="https://wpvulndb.com/vulnerabilities/%s" target="_blank">wpvulndb.com</a>',
				$vulnerability->id
			);

			if ( is_null( $vulnerability->fixed_in ) ) {
				$message['meta'][] = '<span style="color: #a00;">Not fixed yet</span>';
			} else {
				$message['meta'][] = sprintf(
					'Fixed in v%s',
					$vulnerability->fixed_in
				);
			}

			$this->messages[] = $message;
		}
	}
}
