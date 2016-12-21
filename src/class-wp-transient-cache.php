<?php

namespace SSNepenthe\Soter;

use SSNepenthe\Soter\Interfaces\Cache;

class WP_Transient_Cache implements Cache {
	protected $prefix;

	/**
	 * Prior to WP 4.4, max transient ID length if 45 characters. MD5 accounts
	 * for 32 of those, this ensures our generated ID maxes out at 45.
	 */
	public function __construct( $prefix ) {
		$this->prefix = substr( $prefix, 0, 12 ) . '_';
	}

	public function contains( $id ) {
		return false !== get_transient( $this->generate_id( $id ) );
	}

	public function fetch( $id ) {
		return get_transient( $this->generate_id( $id ) );
	}

	public function save( $id, $data, $lifetime = 0 ) {
		return set_transient( $this->generate_id( $id ), $data, $lifetime );
	}

	protected function generate_id( $id ) {
		return $this->prefix . hash( 'md5', $id );
	}
}
