<?php

namespace SSNepenthe\Soter\WPVulnDB;

use SSNepenthe\ComposerUtilities\WordPressPackage;

Class ApiRequest {
	protected $endpoint;
	protected $path;
	protected $slug;

	public function __construct( WordPressPackage $package ) {
		$this->path = $this->get_path( $package );
		$this->slug = $this->get_slug( $package );

		$this->endpoint = sprintf( '%s/%s', $this->path, $this->slug );
	}

	public function endpoint() {
		return $this->endpoint;
	}

	public function path () {
		return $this->path;
	}

	public function slug () {
		return $this->slug;
	}

	protected function get_path( WordPressPackage $package ) {
		if ( $package->is_wp_plugin() ) {
			return 'plugins';
		}

		if ( $package->is_wp_core() ) {
			return 'wordpresses';
		}

		if ( $package->is_wp_theme() ) {
			return 'themes';
		}

		// Should never be the case, but just to be safe...
		return '';
	}

	protected function get_slug( WordPressPackage $package ) {
		if ( $package->is_wp_core() ) {
			return str_replace( '.', '', $package->version() );
		}

		list( $vendor, $name ) = explode( '/', $package->name() );

		return $name;
	}
}
