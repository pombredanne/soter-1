<?php
/**
 * Options_Store class.
 *
 * @package soter
 */

namespace Soter\Options;

/**
 * Defines the options store class.
 */
class Options_Store {
	/**
	 * Option prefix.
	 *
	 * @var string
	 */
	protected $prefix;

	/**
	 * Class constructor.
	 *
	 * @param string $prefix Option prefix.
	 */
	public function __construct( $prefix = '' ) {
		$this->prefix = (string) $prefix;
	}

	/**
	 * Add an option entry.
	 *
	 * @param string $key   Option key.
	 * @param mixed  $value Option value.
	 *
	 * @return boolean
	 */
	public function add( $key, $value ) {
		return add_option( $this->option_key( $key ), $value );
	}

	/**
	 * Delete an option entry.
	 *
	 * @param  string $key Option key.
	 *
	 * @return boolean
	 */
	public function delete( $key ) {
		return delete_option( $this->option_key( $key ) );
	}

	/**
	 * Get an option entry.
	 *
	 * @param  string $key     Option key.
	 * @param  mixed  $default Default value.
	 *
	 * @return mixed
	 */
	public function get( $key, $default = null ) {
		$value = get_option( $this->option_key( $key ) );

		if ( false === $value ) {
			return $default;
		}

		return $value;
	}

	/**
	 * Set an option value.
	 *
	 * @param string $key   Option key.
	 * @param mixed  $value Option value.
	 *
	 * @return boolean
	 */
	public function set( $key, $value ) {
		return update_option( $this->option_key( $key ), $value );
	}

	/**
	 * Generate the actual option key.
	 *
	 * @param  string $key Option key.
	 *
	 * @return string
	 */
	protected function option_key( $key ) {
		$key = (string) $key;

		if ( ! $this->prefix ) {
			return $key;
		}

		return "{$this->prefix}_{$key}";
	}
}
