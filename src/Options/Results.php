<?php
/**
 * Wrapper for the results entry in the options table.
 *
 * @package soter
 */

namespace SSNepenthe\Soter\Options;

/**
 * This class provides convenience methods for setting and saving the results of
 * a security check to the options table.
 */
class Results {
	const OPTION_KEY = 'soter_results';

	/**
	 * Security messages.
	 *
	 * @var array
	 */
	protected $messages;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->messages = get_option( self::OPTION_KEY, [] );
	}

	/**
	 * Messages getter.
	 *
	 * @return array
	 */
	public function messages() {
		return $this->messages;
	}

	/**
	 * Save the message array to DB.
	 *
	 * @return bool
	 */
	public function save() {
		return update_option( self::OPTION_KEY, $this->messages );
	}

	/**
	 * Set the messages array from a vulnerabilities array.
	 *
	 * @param array $vulnerabilities Array of vulnerability objects.
	 */
	public function set_from_vulnerabilities_array( array $vulnerabilities ) {
		$this->messages = [];

		if ( empty( $vulnerabilities ) ) {
			return;
		}

		foreach ( $vulnerabilities as $vulnerability ) {
			$message = [
				'title' => $vulnerability->title,
				'meta' => [],
				'links' => [],
			];

			if ( ! is_null( $vulnerability->published_date ) ) {
				$message['meta'][] = sprintf(
					'Published %s',
					$vulnerability->published_date->format( 'd M Y' )
				);
			}

			if ( isset( $vulnerability->references->url ) ) {
				foreach ( $vulnerability->references->url as $url ) {
					$parsed = wp_parse_url( $url );

					$host = isset( $parsed['host'] ) ?
						$parsed['host'] :
						$url;

					$message['links'][ $url ] = $host;
				}
			}

			$message['links'][ sprintf(
				'https://wpvulndb.com/vulnerabilities/%s',
				$vulnerability->id
			) ] = 'wpvulndb.com';

			if ( is_null( $vulnerability->fixed_in ) ) {
				$message['meta'][] = 'Not fixed yet';
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
