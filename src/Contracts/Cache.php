<?php

namespace SSNepenthe\Soter\Contracts;

interface Cache {
	public function contains( $id );
	public function fetch( $id );
	public function flush();
	public function save( $id, $data, $lifetime );
}
