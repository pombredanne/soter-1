<?php

namespace SSNepenthe\Soter\Options;

class Page {
	protected $settings;

	public function __construct( Settings $settings = null ) {
		$this->settings = is_null( $settings ) ?
			new Settings :
			$settings;
	}

	public function init() {
		add_action( 'admin_init', [ $this, 'admin_init' ] );
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );
	}

	public function admin_init() {
		register_setting(
			'soter_settings_group',
			'soter_settings',
			[ $this->settings, 'sanitize' ]
		);

		add_settings_section(
			'soter_main',
			'Main Settings',
			[ $this, 'render_section_main' ],
			'soter'
		);

		add_settings_field(
			'soter_enable_email',
			'Enable Email',
			[ $this, 'render_enable_email' ],
			'soter',
			'soter_main'
		);

		add_settings_field(
			'soter_email_address',
			'Email Address',
			[ $this, 'render_email_address' ],
			'soter',
			'soter_main'
		);

		add_settings_field(
			'soter_ignored_plugins',
			'Ignored Plugins',
			[ $this, 'render_ignored_plugins' ],
			'soter',
			'soter_main'
		);

		add_settings_field(
			'soter_ignored_themes',
			'Ignored Themes',
			[ $this, 'render_ignored_themes' ],
			'soter',
			'soter_main'
		);
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

	public function render_email_address() {
		printf(
			'<input class="something" id="soter_settings_email_address" name="soter_settings[email_address]" placeholder="%s" type="email" value="%s">',
			esc_attr( get_bloginfo( 'admin_email' ) ),
			esc_attr( $this->settings->email_address )
		);
	}

	public function render_enable_email() {
		$output = [
			'<fieldset>',
				'<label>',
					sprintf( '<input%s class="something" id="soter_settings_enable_email" name="soter_settings[enable_email]" type="checkbox" value="1">', checked( $this->settings->enable_email, true, false ) ),
					'Enable email notifications?',
				'</label>',
				'<p class="description">By default, an admin notice is shown when a vulnerability has been detected. Check this box to also receive an email notification.</p>',
			'</fieldset>',
		];

		echo implode( '', $output );
	}

	public function render_ignored_plugins() {
		$plugins = get_plugins();
		$plugin_count = count( $plugins );
		$counter = 0;

		$output = [];

		$output[] = '<fieldset>';

		foreach ( $plugins as $file => $data ) {
			$counter++;

			list( $slug, $basename ) = explode( DIRECTORY_SEPARATOR, $file );

			$output[] = '<label>';

			$output[] = sprintf(
				'<input%1$s class="something" id="soter_settings_%2$s" name="soter_settings[ignored_plugins][]" type="checkbox" value="%2$s">',
				checked( in_array( $slug, $this->settings->ignored_plugins ), true, false ),
				esc_attr( $slug )
			);

			$output[] = esc_html( $data['Name'] );

			$output[] = '</label>';

			if ( $counter < $plugin_count ) {
				$output[] = '<br>';
			}
		}

		$output[] = '<p class="description">Select any plugins that should be ignored by the security checker (i.e. custom plugins).</p>';

		$output[] = '</fieldset>';

		echo implode( '', $output );
	}

	public function render_ignored_themes() {
		$themes = wp_get_themes();
		$theme_count = count( $themes );
		$counter = 0;

		$output = [];

		$output[] = '<fieldset>';

		foreach ( $themes as $name => $object ) {
			$counter++;

			$output[] = '<label>';

			$output[] = sprintf(
				'<input%1$s class="something" id="soter_settings_%2$s" name="soter_settings[ignored_themes][]" type="checkbox" value="%2$s">',
				checked( in_array( $object->stylesheet, $this->settings->ignored_themes ), true, false ),
				esc_attr( $object->stylesheet )
			);

			$output[] = esc_html( $name );

			$output[] = '</label>';

			if ( $counter < $theme_count ) {
				$output[] = '<br>';
			}
		}

		$output[] = '<p class="description">Select any themes that should be ignored by the security checker (i.e. custom themes).</p>';

		$output[] = '</fieldset>';

		echo implode( '', $output );
	}

	public function render_page_soter() {
		echo '<div class="wrap">';
		echo '<h1>Soter Configuration</h1>';
		echo '<form action="options.php" method="post">';

		settings_fields( 'soter_settings_group' );

		do_settings_sections( 'soter' );

		echo '<p class="submit">';
		echo '<input class="button button-primary" id="submit" name="submit" type="submit" value="Save Changes">';
		echo '</p>';
		echo '</form>';
		echo '</div>';
	}

	public function render_section_main() {
		echo '<p>The main settings for the Soter Security Checker plugin.</p>';
	}
}
