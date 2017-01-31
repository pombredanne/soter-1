<?php

namespace SSNepenthe\Soter\Options;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class List_Option {
	protected $data = [];
	protected $key;

	public function __construct( $key ) {
		$this->key = (string) $key;
	}

	public function add( $value ) {
		$this->data[] = $value;
	}

	public function all() {
		return $this->data;
	}

	public function contains( $value ) {
		return false !== $this->search( $value );
	}

	public function init() {
		$this->data = get_option( $this->key, [] );
	}

	public function is_empty() {
		return empty( $this->data );
	}

	public function remove( $value ) {
		$key = $this->search( $value );

		if ( false !== $key ) {
			unset( $this->data[ $key ] );
			$this->data = array_values( $this->data );
		}
	}

	public function reset() {
		return $this->data = [];
	}

	public function save() {
		return update_option( $this->key, $this->data );
	}

	public function search( $value ) {
		return array_search( $value, $this->data, true );
	}
}
