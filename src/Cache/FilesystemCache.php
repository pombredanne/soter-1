<?php

namespace SSNepenthe\Soter\Cache;

use Doctrine\Common\Cache\FilesystemCache as FileCache;
use SSNepenthe\Soter\Contracts\Cache;

class FilesystemCache implements Cache {
	protected $client;

	public function __construct( $directory = '.cache' ) {
		$this->client = new FileCache( $directory );
	}

	public function contains( $id ) {
		return $this->client->contains( $id );
	}

	public function fetch( $id ) {
		return $this->client->fetch( $id );
	}

	public function flush() {
		return $this->client->flushAll();
	}

	public function save( $id, $data, $lifetime = 0 ) {
		return $this->client->save( $id, $data, $lifetime );
	}
}