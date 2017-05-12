<?php

namespace Soter\Options;

class Options_Store {
	protected $prefix;

	public function __construct( $prefix = '' ) {
		$this->prefix = $prefix;
	}

	public function add( $key, $value ) {
		return add_option( $this->option_key( $key ), $value );
	}

	public function delete( $key ) {
		return delete_option( $this->option_key( $key ) );
	}

	public function get( $key, $default = null ) {
		$value = get_option( $this->option_key( $key ) );

		if ( false === $value ) {
			return $default;
		}

		return $value;
	}

	public function set( $key, $value ) {
		return update_option( $this->option_key( $key ), $value );
	}

	protected function option_key( $key ) {
		if ( ! $this->prefix ) {
			return $key;
		}

		return "{$this->prefix}_{$key}";
	}
}
