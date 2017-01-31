<?php

namespace SSNepenthe\Soter\Options;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Map_Option {
	protected $data = [];
	protected $key;

	public function __construct( $key ) {
		$this->key = (string) $key;
	}

	public function all() {
		return $this->data;
	}

	public function forget( $key ) {
		unset( $this->data[ $key ] );
	}

	public function get( $key, $default = null ) {
		if ( $this->has( $key ) ) {
			return $this->data[ $key ];
		}

		return $default;
	}

	public function has( $key ) {
		return array_key_exists( $key, $this->data );
	}

	public function init() {
		$this->data = get_option( $this->key, [] );
	}

	public function is_empty() {
		return empty( $this->data );
	}

	public function reset() {
		return $this->data = [];
	}

	public function save() {
		return update_option( $this->key, $this->data );
	}

	public function set( $key, $value ) {
		$this->data[ $key ] = $value;
	}
}
