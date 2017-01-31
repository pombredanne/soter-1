<?php
/**
 * List option type.
 *
 * @package soter
 */

namespace SSNepenthe\Soter\Options;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * This class creates a wrapper for a list setting value.
 */
class List_Option {
	/**
	 * The actual setting data.
	 *
	 * @var array
	 */
	protected $data = [];

	/**
	 * The settings key used to save and retrieve from database.
	 *
	 * @var string
	 */
	protected $key;

	/**
	 * Class constructor.
	 *
	 * @param string $key The setting key.
	 */
	public function __construct( $key ) {
		$this->key = (string) $key;
	}

	/**
	 * Add a value to the setting data.
	 *
	 * @param mixed $value The value to add.
	 */
	public function add( $value ) {
		$this->data[] = $value;
	}

	/**
	 * Get all setting data.
	 *
	 * @return array
	 */
	public function all() {
		return $this->data;
	}

	/**
	 * Check whether a given value exists in the setting data.
	 *
	 * @param  mixed $value The value to search for.
	 *
	 * @return bool
	 */
	public function contains( $value ) {
		return false !== $this->search( $value );
	}

	/**
	 * Fetch the initial data from the database.
	 */
	public function init() {
		$this->data = get_option( $this->key, [] );
	}

	/**
	 * Check whether the setting is empty.
	 *
	 * @return bool
	 */
	public function is_empty() {
		return empty( $this->data );
	}

	/**
	 * Remove a value from the setting data.
	 *
	 * @param  string $value The value to remove.
	 */
	public function remove( $value ) {
		$key = $this->search( $value );

		if ( false !== $key ) {
			unset( $this->data[ $key ] );
			$this->data = array_values( $this->data );
		}
	}

	/**
	 * Empty the setting data.
	 *
	 * @return array
	 */
	public function reset() {
		return $this->data = [];
	}

	/**
	 * Persist the current option state to the database.
	 *
	 * @return bool
	 */
	public function save() {
		return update_option( $this->key, $this->data );
	}

	/**
	 * Search for a given value within the setting data.
	 *
	 * @param  mixed $value The value to search for.
	 *
	 * @return bool|int
	 */
	public function search( $value ) {
		return array_search( $value, $this->data, true );
	}
}
