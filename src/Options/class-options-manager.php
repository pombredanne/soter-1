<?php

namespace Soter\Options;

class Options_Manager {
	protected $store;

	public function __construct( Options_Store $store ) {
		$this->store = $store;
	}

	public function email_address() {
		$current = $this->store->get( 'email_address' );

		// Might be null or an empty string...
		if ( ! $current ) {
			$current = get_bloginfo( 'admin_email' );
		}

		return (string) $current;
	}

	public function email_type() {
		return (string) $this->store->get( 'email_type', 'text' );
	}

	public function enable_email() {
		return (bool) $this->store->get( 'enable_email', false );
	}

	public function enable_notices() {
		return (bool) $this->store->get( 'enable_notices', true );
	}

	public function get_store() {
		return $this->store;
	}

	public function ignored_packages() {
		return array_merge( $this->ignored_plugins(), $this->ignored_themes() );
	}

	public function ignored_plugins() {
		return (array) $this->store->get( 'ignored_plugins', [] );
	}

	public function ignored_themes() {
		return (array) $this->store->get( 'ignored_themes', [] );
	}

	public function installed_version() {
		return (string) $this->store->get( 'installed_version', '' );
	}

	public function vulnerabilities() {
		return (array) $this->store->get( 'vulnerabilities', [] );
	}

	public function register_settings() {
		register_setting( 'soter_group', 'soter_email_address', [
			'default' => get_bloginfo( 'admin_email' ),
			'sanitize_callback' => [ $this, 'sanitize_email_address' ],
			'show_in_rest' => true,
		] );

		register_setting( 'soter_group', 'soter_email_type', [
			'default' => 'text',
			'sanitize_callback' => [ $this, 'sanitize_email_type' ],
			'show_in_rest' => true,
		] );

		register_setting( 'soter_group', 'soter_enable_email', [
			'default' => false,
			'sanitize_callback' => [ $this, 'sanitize_boolean' ],
			'show_in_rest' => true,
		] );

		register_setting( 'soter_group', 'soter_enable_notices', [
			'default' => true,
			'sanitize_callback' => [ $this, 'sanitize_boolean' ],
			'show_in_rest' => true,
		] );

		register_setting( 'soter_group', 'soter_ignored_plugins', [
			'default' => [],
			'sanitize_callback' => [ $this, 'sanitize_ignored_plugins' ],
			'show_in_rest' => true,
		] );

		register_setting( 'soter_group', 'soter_ignored_themes', [
			'default' => [],
			'sanitize_callback' => [ $this, 'sanitize_ignored_themes' ],
			'show_in_rest' => true,
		] );

		register_setting( 'soter_group', 'soter_installed_version', [
			'default' => '',
			'sanitize_callback' => [ $this, 'sanitize_installed_version' ],
			'show_in_rest' => true,
		] );

		register_setting( 'soter_group', 'soter_vulnerabilities', [
			'default' => [],
			'sanitize_callback' => [ $this, 'sanitize_vulnerabilities' ],
			'show_in_rest' => true,
		] );
	}

	public function sanitize_boolean( $value ) {
		return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
	}

	public function sanitize_email_address( $value ) {
		// Allow user to unset by providing an empty string.
		if ( ! $value ) {
			return '';
		}

		$new_value = sanitize_email( $value );

		if ( ! $new_value ) {
			add_settings_error(
				'soter_email_address',
				'invalid_soter_email_address',
				sprintf(
					'The email address provided [%s] does not appear to be valid.',
					esc_html( $value )
				)
			);

			return $this->email_address();
		}

		return $new_value;
	}

	public function sanitize_email_type( $value ) {
		$new_value = '';

		if ( in_array( $value, [ 'html', 'text' ], true ) ) {
			$new_value = $value;
		}

		if ( ! $new_value ) {
			add_settings_error(
				'soter_email_type',
				'invalid_soter_email_type',
				sprintf(
					'Email type must be one of "text" or "html" - "%s" given.',
					esc_html( $value )
				)
			);

			return $this->email_type();
		}

		return $new_value;
	}

	public function sanitize_ignored_plugins( $value ) {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$value = (array) $value;
		$valid_slugs = array_map( function( $file ) {
			if ( false === strpos( $file, '/' ) ) {
				$slug = basename( $file, '.php' );
			} else {
				$slug = dirname( $file );
			}

			return $slug;
		}, array_keys( get_plugins() ) );
		$invalid_slugs = array_diff( $value, $valid_slugs );

		if ( ! empty( $invalid_slugs ) ) {
			add_settings_error(
				'soter_ignored_plugins',
				'invalid_soter_ignored_plugins',
				sprintf(
					'Only currently installed plugins can be ignored. The following invalid plugin slugs were provided: %s',
					implode( ', ', array_map( 'esc_html', $invalid_slugs ) )
				)
			);

			return $this->ignored_plugins();
		}

		return $value;
	}

	public function sanitize_ignored_themes( $value ) {
		$value = (array) $value;
		$valid_slugs = array_values( wp_list_pluck(
			wp_get_themes(),
			'stylesheet'
		) );
		$invalid_slugs = array_diff( $value, $valid_slugs );

		if ( ! empty( $invalid_slugs ) ) {
			add_settings_error(
				'soter_ignored_plugins',
				'invalid_soter_ignored_plugins',
				sprintf(
					'Only currently installed themes can be ignored. The following invalid theme slugs were provided: %s',
					implode( ', ', array_map( 'esc_html', $invalid_slugs ) )
				)
			);

			return $this->ignored_themes();
		}

		return $value;
	}

	public function sanitize_installed_version( $value ) {
		if ( (bool) preg_match( '/[^\d\.]/', $value ) ) {
			add_settings_error(
				'soter_installed_version',
				'invalid_soter_installed_version',
				'Installed version can only contain the following characters: 0123456789.'
			);

			return $this->installed_version();
		}

		return $value;
	}

	public function sanitize_vulnerabilities( $value ) {
		$value = (array) $value;
		$valid = true;

		foreach ( $value as $id ) {
			if ( ! is_numeric( $value ) ) {
				$valid = false;
				break;
			}
		}

		if ( ! $valid ) {
			add_settings_error(
				'soter_vulnerabilities',
				'invalid_soter_vulnerabilities',
				'Vulnerabilities must be an array of numeric IDs.'
			);
		}

		return array_map( 'intval', $value );
	}

	public function set_email_address( $value ) {
		return $this->store->set( 'email_address', $value );
	}

	public function set_email_type( $value ) {
		return $this->store->set( 'email_type', $value );
	}

	public function set_enable_email( $value ) {
		return $this->store->set( 'enable_email', $value );
	}

	public function set_enable_notices( $value ) {
		return $this->store->set( 'enable_notices', $value );
	}

	public function set_ignored_plugins( $value ) {
		return $this->store->set( 'ignored_plugins', $value );
	}

	public function set_ignored_themes( $value ) {
		return $this->store->set( 'ignored_themes', $value );
	}

	public function set_installed_version( $value ) {
		return $this->store->set( 'installed_version', $value );
	}

	public function set_vulnerabilities( $value ) {
		return $this->store->set( 'vulnerabilities', $value );
	}
}
