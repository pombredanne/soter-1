<?php
/**
 * Base HTTP client implementation.
 *
 * @package soter
 */

namespace SSNepenthe\Soter\HTTP;

use SSNepenthe\Soter\Interfaces\HTTP;

/**
 * This class defines the setters and validation methods for HTTP clients.
 */
abstract class BaseClient implements HTTP {
	/**
	 * API URL root.
	 *
	 * @var string
	 */
	protected $url_root;

	/**
	 * User agent string.
	 *
	 * @var string
	 */
	protected $user_agent;

	/**
	 * Constructor.
	 *
	 * @param string $url_root   API URL root.
	 * @param string $user_agent User agent string.
	 */
	public function __construct( $url_root = null, $user_agent = null ) {
		if ( ! is_null( $url_root ) ) {
			$this->set_url_root( $url_root );
		}

		if ( ! is_null( $user_agent ) ) {
			$this->set_user_agent( $user_agent );
		}
	}

	/**
	 * User agent getter.
	 *
	 * @return string
	 */
	public function get_user_agent() {
		return $this->user_agent;
	}

	/**
	 * URL root getter.
	 *
	 * @return string
	 */
	public function get_url_root() {
		return $this->url_root;
	}

	/**
	 * Set the user agent.
	 *
	 * @param string $user_agent User agent string.
	 *
	 * @throws  \InvalidArgumentException When user_agent is not a string.
	 */
	public function set_user_agent( $user_agent ) {
		if ( ! is_string( $user_agent ) ) {
			throw new \InvalidArgumentException( sprintf(
				'The user_agent parameter is required to be string, was: %s',
				gettype( $user_agent )
			) );
		}

		$this->user_agent = $user_agent;
	}

	/**
	 * Set the URL root.
	 *
	 * @param string $url_root URL root.
	 *
	 * @throws  \InvalidArgumentException When url_root is not a string.
	 * @throws  \RuntimeException When url_root does not avalidate as URL.
	 */
	public function set_url_root( $url_root ) {
		if ( ! is_string( $url_root ) ) {
			throw new \InvalidArgumentException( sprintf(
				'The url_root parameter is required to be string, was: %s',
				gettype( $url_root )
			) );
		}

		if ( ! $url_root = filter_var( $url_root, FILTER_VALIDATE_URL ) ) {
			throw new \RuntimeException(
				'The provided URL root does not appear to be valid'
			);
		}

		$this->url_root = rtrim( $url_root, '/\\' );
	}

	/**
	 * Validate the args required for making a GET request.
	 *
	 * @param  string $endpoint Request endpoint.
	 *
	 * @throws  \RuntimeException When url_root is not set.
	 * @throws  \RuntimeException When user_agent is not set.
	 * @throws  \InvalidArgumentException When endpoint is not a string.
	 */
	protected function validate_get_args( $endpoint ) {
		if ( ! isset( $this->url_root ) ) {
			throw new \RuntimeException(
				'You must call set_url_root() before making a GET request'
			);
		}

		if ( ! isset( $this->user_agent ) ) {
			throw new \RuntimeException(
				'You must call set_user_agent() before making a GET request'
			);
		}

		if ( ! is_string( $endpoint ) ) {
			throw new \InvalidArgumentException( sprintf(
				'The endpoint parameter is required to be string, was: %s',
				gettype( $endpoint )
			) );
		}
	}

	/**
	 * Make a GET request.
	 *
	 * @param  string $endpoint Request endpoint.
	 *
	 * @return array            Response array:
	 *                          [0] status code,
	 *                          [1] headers array,
	 *                          [2] response body.
	 */
	abstract public function get( $endpoint );
}
