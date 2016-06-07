<?php

namespace SSNepenthe\Soter\Options;

class Settings {
	const OPTION_KEY = 'soter_settings';

	protected $container;
	protected $whitelist = [
		'enable_email',
		'email_address',
		'ignored_plugins',
		'ignored_themes',
	];

	public function __construct() {
		$this->container = wp_parse_args( get_option( self::OPTION_KEY, [] ), [
			'enable_email' => false,
			'email_address' => '',
			'ignored_plugins' => [],
			'ignored_themes' => [],
		] );
	}

	public function __get( $key ) {
		if ( array_key_exists( $key, $this->container ) ) {
			return $this->container[ $key ];
		}

		return null;
	}

	public function __set( $key, $value ) {
		if ( in_array( $key, $this->whitelist ) ) {
			$sanitize_method = sprintf( 'sanitize_%s', $key );

			$value = call_user_func( [ $this, $sanitize_method ], $value );

			$this->container[ $key ] = $value;
		}
	}

	public function save() {
		return update_option( self::OPTION_KEY, $this->container );
	}

	public function sanitize( array $values ) {
		$sanitized = [];

		$sanitized['enable_email'] = $this->sanitize_enable_email(
			isset( $values['enable_email'] ) ? $values['enable_email'] : null
		);

		$sanitized['email_address'] = $this->sanitize_email_address(
			isset( $values['email_address'] ) ? $values['email_address'] : null
		);

		$sanitized['ignored_plugins'] = $this->sanitize_ignored_plugins(
			isset( $values['ignored_plugins'] ) ? $values['ignored_plugins'] : null
		);

		$sanitized['ignored_themes'] = $this->sanitize_ignored_themes(
			isset( $values['ignored_themes'] ) ? $values['ignored_themes'] : null
		);

		return $sanitized;
	}

	protected function sanitize_enable_email( $value = null ) {
		if ( is_null( $value ) ) {
			return false;
		}

		return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
	}

	protected function sanitize_email_address( $value = null ) {
		if ( is_null( $value ) ) {
			return '';
		}

		return sanitize_email( $value );
	}

	protected function sanitize_ignored_plugins( $value = null ) {
		if ( is_null( $value ) ) {
			return [];
		}

		$value = (array) $value;

		// Array of installed plugin slugs.
		$plugins = array_map( function( $value ) {
			// Does WP use directory separator or is it still / on windows?
			list( $slug, $basename ) = explode( DIRECTORY_SEPARATOR, $value );

			return $slug;
		}, array_keys( get_plugins() ) );

		return array_intersect( $plugins, $value );
	}

	protected function sanitize_ignored_themes( $value = null ) {
		if ( is_null( $value ) ) {
			return [];
		}

		$value = (array) $value;

		// Array of installed theme slugs.
		$themes = array_map( function( $value ) {
			return $value->stylesheet;
		}, array_values( wp_get_themes() ) );

		return array_intersect( $themes, $value );
	}
}
