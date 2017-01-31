<?php

namespace SSNepenthe\Soter\Cache;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class WP_Transient_Cache implements Cache_Interface {
	protected $prefix;

	/**
	 * Prior to WP 4.4, max transient ID length is 45 characters. MD5 accounts
	 * for 32 of those, this ensures our generated ID maxes out at 45.
	 */
	public function __construct( $prefix ) {
		$this->prefix = substr( (string) $prefix, 0, 12 ) . '_';
	}

	public function contains( $key ) {
		return false !== get_transient( $this->generate_id( $key ) );
	}

	public function fetch( $key ) {
		return get_transient( $this->generate_id( $key ) );
	}

	public function save( $key, $data, $lifetime = 0 ) {
		return set_transient( $this->generate_id( $key ), $data, $lifetime );
	}

	protected function generate_id( $key ) {
		return $this->prefix . hash( 'md5', (string) $key );
	}
}
