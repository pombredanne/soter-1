<?php

namespace SSNepenthe\Soter;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Package {
	protected $slug;
	protected $type;
	protected $version;

	public function __construct( $slug, $type, $version ) {
		$this->slug = (string) $slug;
		$this->type = (string) $type;
		$this->version = (string) $version;
	}

	public function get_slug() {
		return $this->slug;
	}

	public function get_type() {
		return $this->type;
	}

	public function get_version() {
		return $this->version;
	}
}
