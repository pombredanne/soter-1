<?php

namespace SSNepenthe\Soter;

class Settings {
	protected $plugin_dir;

	public function __construct( $plugin_dir ) {
		$this->plugin_dir = $plugin_dir;
	}

	/**
	 * Maps 'render_*' methods to 'include_*_template' methods.
	 *
	 * @param  string $name Method name.
	 * @param  array  $args Method arguments.
	 *
	 * @return mixed
	 */
	public function __call( $name, $args ) {
		if ( 'render' !== substr( $name, 0, 6 ) ) {
			return call_user_func_array( [ $this, $name ], $args );
		}

		if ( 'render_field_' === substr( $name, 0, 13 ) ) {
			return $this->include_field_template(
				str_replace( '_', '-', substr( $name, 13 ) )
			);
		} elseif ( 'render_page_' === substr( $name, 0, 12 ) ) {
			return $this->include_page_template(
				str_replace( '_', '-', substr( $name, 12 ) )
			);
		} elseif ( 'render_section_' === substr( $name, 0, 15 ) ) {
			return $this->include_section_template(
				str_replace( '_', '-', substr( $name, 15 ) )
			);
		} else {
			return call_user_func_array( [ $this, $name ], $args );
		}
	}

	public function init() {
		add_action( 'admin_init', [ $this, 'admin_init' ] );
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );
	}

	public function admin_init() {
		register_setting(
			'soter_settings_group',
			'soter_settings',
			[ $this, 'sanitize' ]
		);

		add_settings_section(
			'soter_main',
			'Main Settings',
			[ $this, 'render_section_main' ],
			'soter'
		);

		$this->add_field( 'Enable Email' );
		$this->add_field( 'Email Address' );
		$this->add_field( 'Ignored Plugins' );
		$this->add_field( 'Ignored Themes' );
	}

	public function admin_menu() {
		add_options_page(
			'Soter Page Title',
			'Security',
			'manage_options',
			'soter',
			[ $this, 'render_page_soter' ]
		);
	}

	public function sanitize( array $input ) {
		// Array of installed plugin slugs.
		$plugins = array_map( function( $value ) {
			// Does WP use directory separator or is it still / on windows?
			list( $slug, $basename ) = explode( DIRECTORY_SEPARATOR, $value );

			return $slug;
		}, array_keys( get_plugins() ) );

		// Array of installed theme slugs.
		$themes = array_map( function( $value ) {
			return $value->stylesheet;
		}, array_values( get_themes() ) );

		$sanitized = [];

		$sanitized['enable_email'] = isset( $input['enable_email'] ) ?
			true :
			false;

		$sanitized['email_address'] = sanitize_email( $input['email_address'] );

		$sanitized['ignored_plugins'] = isset( $input['ignored_plugins'] ) ?
			array_intersect( $plugins, $input['ignored_plugins'] ) :
			[];

		$sanitized['ignored_themes'] = isset( $input['ignored_themes'] ) ?
			array_intersect( $themes, $input['ignored_themes'] ) :
			[];

		return $sanitized;
	}

	protected function include_page_template( $name ) {
		$name = 'page-' . $name;

		$this->include_template( $name );
	}

	protected function include_section_template( $name ) {
		$name = 'section-' . $name;

		$this->include_template( $name );
	}

	protected function include_field_template( $name ) {
		$name = 'field-' . $name;

		$this->include_template( $name );
	}

	protected function include_template( $name ) {
		$path = $this->plugin_dir . 'templates/settings/' . $name . '.php';

		if ( file_exists( $path ) ) {
			include $path;
		}
	}

	protected function add_field( $title ) {
		$id = strtolower( preg_replace( '/[^A-Za-z0-9]/', '_', $title ) );

		add_settings_field(
			sprintf( 'soter_%s', $id ),
			$title,
			[ $this, sprintf( 'render_field_%s', $id ) ],
			'soter',
			'soter_main'
		);
	}
}
